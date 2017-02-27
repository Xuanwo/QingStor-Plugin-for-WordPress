<?php
/*
Plugin Name: WP-QingStor
Plugin URI:  https://github.com/yungkcx/QingStor-Plugin-for-WordPress
Description: QingStor Plugin for WordPress. The Backup function requires `zip' and `mysqldump' program.
Text Domain: qingstor
Version:     0.3
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

// Add textdomain.
add_action('init', 'qingstor_load_textdomain');
function qingstor_load_textdomain()
{
    load_plugin_textdomain('qingstor', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
