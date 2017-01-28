<?php

use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

/**
 * 测试 QingStor SDK 返回值
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
 * 上传指定 Media 文件
 * @param $data
 */
function qingstor_upload($data) {
    $wp_upload_dir = wp_get_upload_dir();
    $bucket = qingstor_get_bucket();
    $media_dir = get_option('qingstor-options')['media_dir'];

    if (empty($media_dir) || empty($bucket)) {
        return;
    }
    // 上传原图|上传文件
    $file_path = $wp_upload_dir['basedir'] . '/' . $data['file'];
    if (file_exists($file_path)) {
        $remote_file_path = $media_dir . '/' . $data['file'];
        $bucket->putObject($remote_file_path, array('body' => file_get_contents($file_path)));
    }
    // 上传缩略图
    if (isset($data['sizes']) && count($data['sizes']) > 0) {
        foreach ($data['sizes'] as $key => $thumb_data) {
            $thumb_path = $wp_upload_dir['basedir'] . '/' . substr($data['file'], 0, 8) . $thumb_data['file'];
            $remote_thumb_path = $media_dir . '/' . substr($data['file'], 0, 8) . $thumb_data['file'];

            if (file_exists($thumb_path)) {
                $bucket->putObject($remote_thumb_path, array('body' => file_get_contents($thumb_path)));
            }
        }
    }
}

/**
 * 匹配文章中的 Media 文件
 * @param String $data
 * @return array
 */
function qingstor_preg_match_all($data) {
    $options = get_option('qingstor-options');
    $p = '/[\"|\'](http.*\.(' . $options['upload_type'] . ')).*[\"|\'].*[>|\]]/iU';
    $num = preg_match_all($p, $data, $matches);

    return $num ? $matches[1] : array();
}

/**
 * 获取文件在 QingStor Bucket 上对应的 URL
 * @param $object
 * @return string
 */
function qingstor_get_object_url($object) {
    $options = get_option('qingstor-options');
    return 'https://' . $options['bucket_name'] . '.pek3a.qingstor.com/' . $options['media_dir'] . $object;
}

// 支持中文的 basename() 函数
function qingstor_basename($path) {
    return preg_replace('/^.+[\\\\\\/]/', '', $path);
}

// 上传文件时，自动同步到 QingStor Bucket
add_action('add_attachment', 'qingstor_add_attachment');
function qingstor_add_attachment($post_ID) {
    $wp_upload_dir = wp_get_upload_dir();
    $attach_url = wp_get_attachment_url($post_ID);
    $file_path = $wp_upload_dir['path'] . '/' . qingstor_basename($attach_url);
    $file_type = wp_check_filetype($file_path);

    if (strstr($file_type, 'image') == false) {
        // 非图片文件
        $data = array('file' => end(explode($wp_upload_dir['basedir'] . '/', $file_path)));
    } else {
        $data = wp_generate_attachment_metadata($post_ID, $file_path);
    }
    qingstor_upload($data);
}

// 在页面渲染时，替换资源文件路径的域名
add_filter('the_content', 'qingstor_the_content');
function qingstor_the_content($content) {
    $wp_upload_dir = wp_get_upload_dir();
    $matches = qingstor_preg_match_all($content);

    foreach ($matches as $url) {
        $bucket_url = qingstor_get_object_url(end(explode($wp_upload_dir['baseurl'], $url)));
        $content = str_replace($url, $bucket_url, $content);
    }
    return $content;
}

//  设置 srcset，防止一些 QingStor Bucket 的图片无法在文章中显示
add_filter('wp_calculate_image_srcset', 'qingstor_calculate_image_srcset', 99, 2);
function qingstor_calculate_image_srcset($src) {
    $wp_upload_dir = wp_get_upload_dir();

    foreach ($src as $key => &$value) {
        if (strstr($value['url'], $wp_upload_dir['baseurl']) != false) {
            $bucket_url = qingstor_get_object_url(end(explode($wp_upload_dir['baseurl'], $value['url'])));
            $value['url'] = $bucket_url;
        }
    }
    return $src;
}
