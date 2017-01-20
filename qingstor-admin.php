<?php
/**
 * @package QingStor
 * @version 0.1
 */
/*
Plugin Name: QingStor
Plugin URI: http://example.org/
Description: QingStor plugin for WordPress
Author: yungkcx
Version: 0.1
Author URI: http://yungkcx.github.io
*/

require_once 'vendor/autoload.php';
require_once 'qingstor-menu.php';

use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

function qingstor_admin() {
    global $QINGSTOR_ACCESS_KEY;
    global $QINGSTOR_SECRET_KEY;

    $qingstor['access_key'] = get_option('access_key');
    $qingstor['secret_key'] = get_option('secret_key');
    if ($qingstor['access_key'] == false || $qingstor['secret_key'] == false) {
        return;
    }
    $config = new Config($qingstor['access_key'], $qingstor['secret_key']);
    $service = new QingStor($config);

//    $response = $service->listBuckets();
    $bucket = $service->Bucket('php-bucket', 'pek3a');
    $response = $bucket->put();
    if ($response->statusCode >= 300 || $response->statusCode < 200) {
        echo "<div><p>$response->message</p></div>";
    } else {
        echo "Success!<br>";
    }
}

add_action('admin_notices', 'qingstor_admin');
add_action('admin_menu', 'qingstor_menu');
