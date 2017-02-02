<?php
/*
Plugin Name: QingStor
Plugin URI:  https://github.com/yungkcx/QingStor-Plugin-for-WordPress
Description: QingStor 青云对象存储服务 WordPress 插件。
Text Domain: qingstor
Version:     0.1
Author:      yungkcx
Author URI:  http://yungkcx.github.io
*/

require_once 'vendor/autoload.php';
require_once 'php/qingstor-functions.php';
require_once 'php/qingstor-upload.php';
require_once 'php/qingstor-menu.php';
require_once 'php/qingstor-backup.php';

register_activation_hook(__FILE__, 'qingstor_activation');
register_deactivation_hook(__FILE__, 'qingstor_deactivation');
