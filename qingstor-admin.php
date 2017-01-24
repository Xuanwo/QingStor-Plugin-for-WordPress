<?php

use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

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

function qingstor_bucket_init($name) {
    $bucket = qingstor_get_bucket($name);
    $bucket->putPolicy(    // 设置存储空间策略为所有用户可 Get Objects
        array(
            "statement" => array(
                array(
                    "id" => "allow all client to get objects",
                    "user" => "*",
                    "action" => array("get_object"),
                    "effect" => "allow",
                    "resource" => array($name."/*"),
//                        "condition" => array(
//                            "string_like" => array(
//                                "Referer" => array()
//                            )
//                        )
                )
            )
        )
    );
    if (!empty($dir = get_option('qingstor-options')['media_files_dir'])) {
        $bucket->putObject($dir);
    }
}

function qingstor_get_bucket($name) {
    $service = qingstor_get_service();
    if (empty($service)) {
        return NULL;
    }
    $bucket = $service->Bucket($name, 'pek3a');
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

add_action('admin_notices', 'qingstor_admin');
function qingstor_admin() {
}
