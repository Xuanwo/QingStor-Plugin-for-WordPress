<?php

use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

add_action('media_upload_{$tab}');

function qingstor_http_status($res) {
    if ($res->statusCode >= 500) {
        return QS_SRVERR;
    } else if ($res->statusCode >= 400) {
        return QS_CLIERR;
    } else {
        return QS_OK;
    }
}

function qingstor_get_service() {
    $qingstor_options = get_option('qingstor-options');
    if ($qingstor_options == false) {
        return NULL;
    }
    $config = new Config($qingstor_options['access_key'], $qingstor_options['secret_key']);
    $service = new QingStor($config);

    $res = $service->listBuckets();
    if (($status = qingstor_http_status($res)) != QS_OK) {
        return $status;
    } else {
        return $service;
    }
}

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

// 上传文件
function qingstor_upload($file) {
    $wp_upload_dir = wp_get_upload_dir();
    $file_path = $wp_upload_dir['base_dir'] . '/' . $file;
    $bucket = qingstor_get_bucket();
    $media_dir = get_option('qingstor-options')['media_files_dir'];
    $remote_file_path = $media_dir . $wp_upload_dir['subdir'] . $file;

    if (empty($media_dir)) {
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

add_action('save_post', 'qingstor_save_post');
function qingstor_save_post($post_id, $post) {
    global $wpdb;

    if ($post->post_status == 'publish') {
        // 匹配 <img> <src>
        $p = '/<img.*[\s]src=[\"|\'](.*)[\"|\'].*>/iU';
        $num = preg_match_all($p, $post->post_content, $matches);

        if ($num) {
            // 脚本执行不限制时
            set_time_limit(0);

            foreach ($matches[1] as $src) {
            }
        }
    }
}
