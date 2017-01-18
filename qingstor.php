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

require 'vendor/autoload.php';

use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

function qingstor_hello_test() {
    global $QINGSTOR_ACCESS_KEY;
    global $QINGSTOR_SECRET_KEY;
    $config = new Config($QINGSTOR_ACCESS_KEY, $QINGSTOR_SECRET_KEY);
    $service = new QingStor($config);
}

add_action('admin_notices', 'qingstor_hello_test');

?>
