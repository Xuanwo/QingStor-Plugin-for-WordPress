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

// 设置 Bucket 的存储空间策略为“允许所有用户读 Media 文件夹”
function qingstor_bucket_init() {
    if (!empty($bucket = qingstor_get_bucket())) {
        $options = get_option('qingstor-options');
        $bucket->putPolicy(
            array(
                "statement" => array(
                    array(
                        "id" => "allow all users to get object",
                        "user" => "*",
                        "action" => array("get_object"),
                        "effect" => "allow",
                        "resource" => array($options['bucket_name'] . '/Media/*'),
                    )
                )
            )
        );
    }
}

/**
 * 表单输入验证
 *
 * @param String $data
 * @return String string
 */
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

/**
 * 匹配文章中的 Media 文件
 *
 * @param String $data
 * @return array
 */
function qingstor_preg_match_all($data) {
    $options = get_option('qingstor-options');
    $p = '/[<|\[].*=[\"|\'](.*\.(' . $options['upload_type'] . ')).*[\"|\'].*[>|\]]/iU';
    $num = preg_match_all($p, $data, $matches);

    return $num ? $matches[1] : array();
}

// 发布/更新文章时，自动上传文章中的本地 Media 文件
add_action('save_post', 'qingstor_save_post', 10, 2);
function qingstor_save_post($post_id, $post) {
    if ($post->post_status == 'publish') {
        $matches = qingstor_preg_match_all($post->post_content);

        $wp_upload_dir = wp_get_upload_dir();
        // 脚本执行不限制时
        set_time_limit(0);
        foreach ($matches as $src) {
            qingstor_upload(end(explode($wp_upload_dir['baseurl'], $src)));
        }
    }
}

// 在页面渲染时，替换资源文件路径的域名
add_filter('the_content', 'qingstor_the_content');
function qingstor_the_content($content) {
    $options = get_option('qingstor-options');
    $wp_upload_dir = wp_get_upload_dir();
    $matches = qingstor_preg_match_all($content);

    foreach ($matches as $src) {
        $bucket_src = 'https://' . $options['bucket_name'] . '.pek3a.qingstor.com/' . $options['media_files_dir'] . end(explode($wp_upload_dir['baseurl'], $src));
        $content = str_replace($src, $bucket_src, $content);
    }
    return $content;
}
