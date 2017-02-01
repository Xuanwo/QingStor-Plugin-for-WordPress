<?php

final class QingStorUpload
{
    private static $instance;

    public function __construct()
    {
        add_action('add_attachment', array($this, 'add_attachment'));
        add_filter('the_content', array($this, 'the_content'));
        add_filter('wp_calculate_image_srcset', array($this, 'calculate_image_srcset'));
    }

    public static function get_instance()
    {
        if (! (self::$instance instanceof QingStorUpload)) {
            self::$instance = new QingStorUpload();
        }
        return self::$instance;
    }

    /**
     * 上传单个文件
     * @param        $bucket
     * @param array $local_remote_path "本地绝对路径 => Bucket 绝对路径" 形式的数组
     */
    public function upload_file($local_remote_path) {
        if (empty($bucket = qingstor_get_bucket())) {
            return;
        }

        foreach ($local_remote_path as $local_path => $remote_path) {
            if (file_exists($local_path)) {
                $bucket->putObject($remote_path, array('body' => file_get_contents($local_path)));
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
        $media_dir = get_option('qingstor-options')['media_dir'];

        if (empty($media_dir) || empty($bucket)) {
            return;
        }

        $files = array();
        // 原图|上传文件
        $files[$wp_upload_dir['basedir'] . '/' . $data['file']] = $media_dir . '/' . $data['file'];

        // 缩略图
        if (isset($data['sizes']) && count($data['sizes']) > 0) {
            foreach ($data['sizes'] as $key => $thumb_data) {
                $files[$wp_upload_dir['basedir'] . '/' . substr($data['file'], 0, 8) . $thumb_data['file']] =
                $media_dir . '/' . substr($data['file'], 0, 8) . $thumb_data['file'];
            }
        }
    }

    /**
     * 匹配文章中的 Media 文件
     * @param String $data
     * @return array
     */
    public function preg_match_all($data)
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
            return 'https://' . $options['bucket_name'] . '.pek3a.qingstor.com/' . $options['media_dir'] . $object;
        }
        return $url;
    }

    // 钩子函数，上传文件时，自动同步到 QingStor Bucket
    public function add_attachment($post_ID)
    {
        $wp_upload_dir = wp_get_upload_dir();
        $attach_url = wp_get_attachment_url($post_ID);
        $file_path = $wp_upload_dir['path'] . '/' . wp_basename($attach_url);
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
        $matches = $this->preg_match_all($content);

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
