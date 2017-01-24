<?php
/**
 * @package QingStor
 * @version 0.1
 */
/*
Plugin Name: QingStor
Plugin URI: https://github.com/yungkcx/QingStor-Plugin-for-WordPress
Description: QingStor plugin for WordPress
Author: yungkcx
Version: 0.1
Author URI: http://yungkcx.github.io
*/

define('QS_CLIERR', 400);
define('QS_SRVERR', 500);
define('QS_OK', 200);

require_once 'vendor/autoload.php';
require_once 'qingstor-admin.php';
require_once 'qingstor-menu.php';
