=== QingStor 对象存储 ===

Contributors:       yungkcx
Tags:               wordpress, Backup，QingStor
Requires at least:  4.5
Tested up to:       4.7
Stable tag:         trunk
License:            GPLv2 or later
License URI:        http://www.gnu.org/licenses/gpl-2.0.html

QingStor 对象存储服务 WordPress 插件，支持定时备份，自动同步媒体库。

== Description ==

当你设置好插件的各项参数并启用后：
1. 向媒体库上传文件时，会自动上传到设置好的 QingStor Bucket
2. 开启 `自动替换资源文件 URL`，插件会在文章渲染时自动替换资源文件的 URL 为 Bucket 地址
3. 定时备份的邮件通知依赖 PHP email 的相关设置
4. 备份功能需要安装有 zip 和 mysqldump 程序，可分别在终端使用 `zip --version` 和 `mysqldump --version` 命令检查

== Installation ==

1. 上传插件到 `/wp-content/plugins/` 目录。
2. 进入本插件目录，在终端下运行 `composer install`（需要安装有 composer）。
3. 在后台插件菜单激活该插件
4. 在 `设置`-`QingStor` 里设置好各项各项参数即可

== Changelog ==

= 0.3 =
* 修复了 Media 文件不能同步的问题
* 不再自动设置 Bucket 的 Policy

= 0.2 =
* 初始版本
