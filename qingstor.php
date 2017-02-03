<?php
/*
Plugin Name: QingStor 青云对象存储
Plugin URI:  https://github.com/yungkcx/QingStor-Plugin-for-WordPress
Description: QingStor Plugin for WordPress. Backup function requires `zip' and `mysqldump' program.
Text Domain: qingstor
Version:     0.2
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

// 添加文本域
add_action('init', 'qingstor_load_textdomain');
function qingstor_load_textdomain()
{
    load_plugin_textdomain('qingstor', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
