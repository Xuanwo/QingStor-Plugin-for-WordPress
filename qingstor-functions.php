<?php

use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

/**
 * 测试 QingStor SDK 返回值
 *
 * @param $response
 * @return int
 */
function qingstor_http_status($response) {
    if ($response->statusCode >= 500) {
        return QS_SRVERR;
    } else if ($response->statusCode >= 400) {
        return QS_CLIERR;
    } else {
        return QS_OK;
    }
}

/**
 * 检查 access key 和 secret key，若正确，返回 service
 *
 * @return null|QingStor
 */
function qingstor_get_service() {
    $qingstor_options = get_option('qingstor-options');
    if (empty($qingstor_options)) {
        return NULL;
    }
    $config = new Config($qingstor_options['access_key'], $qingstor_options['secret_key']);
    $service = new QingStor($config);

    $res = $service->listBuckets();
    if (qingstor_http_status($res) != QS_OK) {
        return NULL;
    }
    return $service;
}

/**
 * 检查目前的 bucket_name 指向的 bucket，若存在，返回 bucket
 *
 * @return null|\QingStor\SDK\Service\Bucket
 */
function qingstor_get_bucket() {
    $service = qingstor_get_service();
    if (empty($service)) {
        return NULL;
    }
    $options = get_option('qingstor-options');
    $bucket = $service->Bucket($options['bucket_name'], 'pek3a');
    $res = $bucket->head();
    if (qingstor_http_status($res) != QS_OK) {
        return NULL;
    }
    return $bucket;
}

function qingstor_test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * 上传指定文件
 *
 * @param String $filename
 */
function qingstor_upload($filename) {
    $wp_upload_dir = wp_get_upload_dir();
    $file_path = $wp_upload_dir['basedir'] . $filename;
    $bucket = qingstor_get_bucket();
    $media_dir = get_option('qingstor-options')['media_files_dir'];
    $remote_file_path = $media_dir . $filename;

    if (empty($media_dir) || empty($bucket)) {
        return;
    }
    if (file_exists($file_path)) {
        $bucket->putObject(
            $remote_file_path,
            array(
                'body' => file_get_contents($file_path)
            )
        );
    }
}

// 保存文章时，自动上传文章中的本地 Media 文件
add_action('save_post', 'qingstor_save_post', 10, 2);
function qingstor_save_post($post_id, $post) {
    if ($post->post_status == 'publish') {
        $qingstor_options = get_option('qingstor-options');
        $p = '/[<|\[].*[\s](src|mp3)=[\"|\'](.*\.(' . $qingstor_options['upload_type'] . ')).*[\"|\'].*[>|\]]/iU';
        $num = preg_match_all($p, $post->post_content, $matches);

        if ($num) {
            $wp_upload_dir = wp_get_upload_dir();
            // 脚本执行不限制时
            set_time_limit(0);
            foreach ($matches[2] as $src) {
                qingstor_upload(end(explode($wp_upload_dir['baseurl'], $src)));
            }
        }
    }
}

// 在页面渲染时，替换资源文件路径的域名
add_filter('the_content', 'qingstor_the_content');
function qingstor_the_content($content) {
    $qingstor_options = get_option('qingstor-options');
    $num = preg_match_all($p, $content, $matches);

    if ($num) {
        $wp_upload_dir = wp_get_upload_dir();
        foreach ($matches[2] as $src) {
            $bucket_src = 'https://' . $qingstor_options['bucket_name'] . '.pek3a.qingstor.com/' . $qingstor_options['media_files_dir'] . end(explode($wp_upload_dir['baseurl'], $src));
            $content = str_replace($src, $bucket_src, $content);
        }
    }
    return $content;
}
