<?php
/*
Plugin Name: QingStor
Plugin URI:  https://github.com/yungkcx/QingStor-Plugin-for-WordPress
Description: QingStor 青云对象存储服务 WordPress 插件。
Author:      yungkcx
Author URI:  http://yungkcx.github.io
License:     GPL2
Version:     0.1
*/

define('QS_CLIERR', 1);
define('QS_SRVERR', 2);
define('QS_OK', 3);

require_once 'vendor/autoload.php';
require_once 'qingstor-functions.php';
require_once 'qingstor-upload.php';
require_once 'qingstor-menu.php';
require_once 'qingstor-backup.php';
