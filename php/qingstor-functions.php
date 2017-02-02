<?php

use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

define('QS_CLIERR', 1);
define('QS_SRVERR', 2);
define('QS_OK', 3);

/**
 * 测试 QingStor SDK 返回值
 * @param $response
 * @return int
 */
function qingstor_http_status($response)
{
    if ($response->statusCode >= 500) {
        return QS_SRVERR;
    } elseif ($response->statusCode >= 400) {
        return QS_CLIERR;
    } else {
        return QS_OK;
    }
}

/**
 * 检查 access key 和 secret key，若正确，返回 service
 * @return null|QingStor
 */
function qingstor_get_service()
{
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
function qingstor_get_bucket()
{
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
function qingstor_bucket_init()
{
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
                        "resource" => array($options['bucket_name'] . '/' . $options['upload_prefix'] . '*'),
                    )
                )
            )
        );
    }
}

// 测试设置的 prefix，如果开头有 '/'，则去掉，如果结尾没有 '/'，则添加
function qingstor_test_prefix($prefix) {
    return ltrim(rtrim($prefix, '/') . '/', '/');
}

function qingstor_test_url($url) {
    return rtrim($url, '/') . '/';
}

/**
 * 表单输入验证
 * @param String $data
 * @return String string
 */
function qingstor_test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// 添加文本域
add_action('init', 'qingstor_load_textdomain');
function qingstor_load_textdomain()
{
    load_plugin_textdomain('qingstor', false, dirname(plugin_basename(__FILE__)));
}

function qingstor_activation()
{
    $options = array(
        'upload_types'  => 'jpg|jpeg|png|gif|mp3|doc|pdf|ppt|pps',
        'upload_prefix' => 'wordpress/uploads/',
        'backup_prefix' => 'wordpress/backup/',
        'schedule_recurrence' => array(
            'start_day_month' => '1',
            'start_hours'     => '3',
            'start_minutes'   => '0',
        ),
        'bucket_url'    => 'https://bucket-name.pek3a.qingstor.com/',
        'backup_num'    => '7'
    );
    update_option('qingstor-options', $options);
}

function qingstor_deactivation()
{
    QingStorBackup::get_instance()->clear_schedule();
    delete_option('qingstor-options');
}
