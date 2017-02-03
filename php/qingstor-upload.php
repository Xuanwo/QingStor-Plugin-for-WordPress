<?php

final class QingStorUpload
{
    private static $instance;

    public function __construct()
    {
        add_action('add_attachment', array($this, 'add_attachment'));
        add_filter('the_content', array($this, 'the_content'));
        add_filter('wp_calculate_image_srcset', array($this, 'calculate_image_srcset'));
        add_action('qingstor_scheduled_upload_hook', array($this, 'upload_files'));
    }

    public static function get_instance()
    {
        if (! (self::$instance instanceof QingStorUpload)) {
            self::$instance = new QingStorUpload();
        }
        return self::$instance;
    }

    // 获取 $dirname 下所有文件的（包括子文件中的文件）本地路径和远端路径
    public function get_files_local_and_remote($dirname, $basedir, $prefix) {
        $dir = opendir($dirname);
        $files = array();
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..') {
                $fullpath = "$dirname/$file";
                if (is_dir($fullpath)) {
                    $files = array_merge($files, $this->get_files_local_and_remote($fullpath, $basedir, $prefix));
                } else {
                    $files[$fullpath] = $prefix . ltrim(ltrim($fullpath, $basedir), '/');
                }
            }
        }
        closedir($dir);
        return $files;
    }

    // 上传 wp-content/uploads/ 的所有文件
    public function upload_uploads() {
        $options = get_option('qingstor-options');
        $basedir = rtrim(wp_get_upload_dir()['basedir'], '/');
        $files   = $this->get_files_local_and_remote($basedir, $basedir, $options['upload_prefix']);
        $this->scheduled_upload_files($files);
    }

    /**
     * 使用定时事件后台上传文件的驱动函数
     * @param array $local_remote_path "本地绝对路径 => Bucket 绝对路径" 形式的数组
     */
    public function scheduled_upload_files($local_remote_path) {
        wp_schedule_single_event(time() + 1, 'qingstor_scheduled_upload_hook', array($local_remote_path));
    }

    /**
     * 上传多个文件
     * @param array $local_remote_path "本地绝对路径 => Bucket 绝对路径" 形式的数组
     */
    public function upload_files($local_remote_path) {
        define('MB', 1024*1024);
        define('GB', MB*1024);
        define('TB', GB*1024);
        set_time_limit(0);
        if (empty($bucket = qingstor_get_bucket())) {
            return;
        }

        foreach ($local_remote_path as $local_path => $remote_path) {
            if (file_exists($local_path)) {
                // 如果小于 5GB 直接上传，否则使用 分段上传，不支持 50TB 以上的文件
                if (($size = filesize($local_path)) < GB*5) {
                    $bucket->putObject($remote_path, array('body' => file_get_contents($local_path)));
                } elseif ($size < TB*50) {
                    $offset = 0;
                    $step   = 5*GB;
                    $nparts = 0;
                    // 使用文件的前 512 字节计算 md5 作为 etag
                    $etag   = md5(file_get_contents($local_path, null, null, 0, 512));

                    $res = $bucket->initiateMultipartUpload($remote_path);
                    if (qingstor_http_status($res) != QS_OK) {
                        return;
                    }
                    $upload_id = $res->{'upload_id'};
                    // 如果可能产生小于 4MB 的块，则加上 5GB，然后分成两块优先处理
                    if ($size % (5*GB) < 4*MB) {
                        $tmp_step = (int)((($size % (5*GB)) + 5*GB) / 2 + 1);
                        for ($i = 0; $i < 2; $i++) {
                            $bucket->uploadMultipart(
                                $remote_path,
                                array(
                                    'upload_id' => $upload_id,
                                    'part_number' => $nparts,
                                    'body' => file_get_contents($local_path, null, null, $offset, $tmp_step)
                                )
                            );
                            $offset += $tmp_step;
                            $nparts++;
                        }
                    }
                    // 剩下的块应该是 5GB 的整数倍
                    while ($content = file_get_contents($local_path, null, null, $offset, $step)) {
                        $bucket->uploadMultipart(
                            $remote_path,
                            array(
                                'upload_id' => $upload_id,
                                'part_number' => $nparts,
                                'body' => $content
                            )
                        );
                        $offset += $step;
                        $nparts++;
                    }
                    // 完成分段上传
                    $res = $bucket->listMultipart(
                        $remote_path,
                        array(
                            'upload_id' => $upload_id
                        )
                    );
                    $object_parts = $res->{'object_parts'};
                    $bucket->completeMultipartUpload(
                        $remote_path,
                        array(
                            'upload_id' => $upload_id,
                            'etag' => $etag,
                            'object_parts' => $object_parts
                        )
                    );
                }
            }
        }
    }

    /**
     * 上传指定 Media 文件
     * @param array $data 有 'file' 键的关联数组
     */
    public function upload_data($data)
    {
        $wp_upload_dir = wp_get_upload_dir();
        $bucket = qingstor_get_bucket();
        $upload_prefix = get_option('qingstor-options')['upload_prefix'];

        if (empty($upload_prefix) || empty($bucket)) {
            return;
        }

        // 原图|上传文件
        $files[$wp_upload_dir['basedir'] . '/' . $data['file']] = $upload_prefix . $data['file'];

        // 缩略图
        if (isset($data['sizes']) && count($data['sizes']) > 0) {
            foreach ($data['sizes'] as $key => $thumb_data) {
                $files[$wp_upload_dir['basedir'] . '/' . substr($data['file'], 0, 8) . $thumb_data['file']] =
                    $upload_prefix . substr($data['file'], 0, 8) . $thumb_data['file'];
            }
        }

        $this->scheduled_upload_files($files);
    }

    /**
     * 匹配文章中的 Media 文件
     * @param String $data
     * @return array
     */
    public function preg_match_all_url($data)
    {
        $options = get_option('qingstor-options');
        $p = '/=[\"|\'](https?:\/\/[^\s]*\.(' . $options['upload_types'] . '))[\"|\'| ]/iU';
        $num = preg_match_all($p, $data, $matches);

        return $num ? $matches[1] : array();
    }

    /**
     * 获取文件在 QingStor Bucket 上对应的 URL
     * @param $object
     * @return string
     */
    public function get_object_url($url)
    {
        $wp_upload_dir = wp_get_upload_dir();
        $options = get_option('qingstor-options');
        if (strstr($url, $wp_upload_dir['baseurl']) != false) {
            $object = end(explode($wp_upload_dir['baseurl'], $url));
            return $options['bucket_url'] . $options['upload_prefix'] . ltrim($object, '/');
        }
        return $url;
    }

    // 钩子函数，上传文件时，自动同步到 QingStor Bucket
    public function add_attachment($post_ID)
    {
        $wp_upload_dir = wp_get_upload_dir();
        $attach_url = wp_get_attachment_url($post_ID);
        $file_path = $wp_upload_dir['basedir'] . '/' . ltrim(ltrim($attach_url, $wp_upload_dir['baseurl']), '/');
        $file_type = wp_check_filetype($file_path);

        if (strstr($file_type['type'], 'image') == false) {
            // 非图片文件
            $data = array('file' => end(explode($wp_upload_dir['basedir'] . '/', $file_path)));
        } else {
            $data = wp_generate_attachment_metadata($post_ID, $file_path);
        }
        $this->upload_data($data);
    }


    // 钩子函数，在页面渲染时，替换资源文件路径的域名
    public function the_content($content)
    {
        if (! get_option('qingstor-options')['replace_url']) {
            return $content;
        }
        $matches = $this->preg_match_all_url($content);

        foreach ($matches as $url) {
            $bucket_url = $this->get_object_url($url);
            $content = str_replace($url, $bucket_url, $content);
        }
        return $content;
    }

    //钩子函数，设置 srcset，防止一些 QingStor Bucket 的图片无法在文章中显示
    public function calculate_image_srcset($src)
    {
        if (! get_option('qingstor-options')['replace_url']) {
            return $src;
        }
        $wp_upload_dir = wp_get_upload_dir();

        foreach ($src as $key => &$value) {
            if (strstr($value['url'], $wp_upload_dir['baseurl']) != false) {
                $bucket_url = $this->get_object_url($value['url']);
                $value['url'] = $bucket_url;
            }
        }
        return $src;
    }
}

QingStorUpload::get_instance();
