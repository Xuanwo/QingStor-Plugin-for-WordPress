<?php

use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

function qingstor_get_service() {
    $qingstor_options = get_option('qingstor-options');
    if ($qingstor_options == false) {
        return NULL;
    }
    $config = new Config($qingstor_options['access_key'], $qingstor_options['secret_key']);
    $service = new QingStor($config);

    $response = $service->listBuckets();
    if ($response->statusCode >= 300 || $response->statusCode < 200) {
        return NULL;
    } else {
        return $service;
    }
}

add_action('admin_notices', 'qingstor_admin');
function qingstor_admin() {
}
