=== WP-QingStor ===

Contributors:       yungkcx
Tags:               wordpress, Backup，QingStor
Requires at least:  4.5
Tested up to:       4.7
Stable tag:         trunk
License:            GPLv2 or later
License URI:        http://www.gnu.org/licenses/gpl-2.0.html

QingStor Plugin for WordPress, support scheduled backup and auto sync Media Library.

== Description == 

After setting:
1. Auto sync to QingStor Bucket when uploading Media files to WordPress Media Library.
2. After selecting `Automatically Replace the Media Files URL`, the plugin will auto replace the local URL of Media files with QingStor Bucket URL when the article is rendering.
3. Email notification of Scheduled Backup depends on PHP email settings.
4. The backup function requires `zip` and `mysqldump` command.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Enter the QingStor plugin directory and run `composer install`.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Use the Settings->QingStor screen to configure the plugin.

== Changelog ==

= 0.3 =
* Fixed the problem that the Media files could not be synchronized
* The Policy of the Bucket is no longer automatically set

= 0.2 =
* Initial Version
