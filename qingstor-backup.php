<?php

final class QingStorBackup
{
    private static $instance;

    public function __construct()
    {
        add_action('qingstor_schedule_hook', array($this, 'backup'));
    }

    public static function get_instance()
    {
        if (! (self::$instance instanceof QingStorBackup)) {
            self::$instance = new QingStorBackup();
        }
        return self::$instance;
    }

    // 导出数据库，压缩 WordPress 目录和至临时文件夹，备份至 QingStor Bucket，然后删除临时目录
    public static function backup()
    {
        if (! self::is_backup_possible()) {
            return;
        }
        if (empty($bucket = qingstor_get_bucket())) {
            return;
        }
        $options = get_option('qingstor-options');
        $backup_path = self::get_backup_path();

        // 使用 zip 备份 WordPress 目录(ABSPATH)
        $cwd = getcwd();
        chdir(ABSPATH);
        $command = 'zip -r ' . $backup_path['zip_path'] . ' .' ;
        exec($command, $output, $retvar1);
        // 使用 mysqldump 备份数据库
        unset($output);
        $mysql_connect_args = self::get_mysql_connect_args();
        $command = 'mysqldump ' . $mysql_connect_args . ' > ' . $backup_path['database_path'];
        exec($command, $output, $retvar2);
        // 添加 mysql 备份到 zip 文件中
        chdir($backup_path['backup_dir']);
        unset($output);
        $command = 'zip -m ' . $backup_path['zip_path'] . ' ' . wp_basename($backup_path['database_path']);
        exec($command, $output, $retvar3);
        chdir($cwd);

        // 确保三个备份命令都成功（返回 0）时，上传到 QingStor Bucket
        if (! $retvar1 && ! $retvar2 && ! $retvar3) {
            $bucket_zip_path = $options['website_dir'] . '/' . wp_basename($backup_path['zip_path']);
            $bucket->putObject($bucket_zip_path, array('body' => file_get_contents($backup_path['zip_path'])));
        }
        // 删除临时目录及文件
        if (file_exists($backup_path['backup_dir'])) {
            self::deldir($backup_path['backup_dir']);
        }
    }

    // 获取 mysqldump 所必须的参数，包括用户名，密码和 wordpress 数据库名
    public function get_mysql_connect_args() {
        global $wpdb;

        $args = '';
        $args .= '-u ' . escapeshellarg($wpdb->dbuser);
        if ($wpdb->dbpassword) {
            $args .= ' -p' . escapeshellarg($wpdb->dbpassword);
        }
        $args .= ' ' . $wpdb->dbname;

        return $args;
    }

    // 递归地删除一个文件夹
    public function deldir($dirname) {
        $dir = opendir($dirname);
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..') {
                $fullpath = "$dirname/$file";
                if (is_dir($fullpath)) {
                    $this->deldir($fullpath);
                } else {
                    unlink($fullpath);
                }
            }
        }
        closedir($dir);
        rmdir($dirname);
    }

    // 产生一定长度的随机字符串，用于生成本地备份的临时目录
    public function generate_rand_string($length = 8) {
        static $chars = 'qwertyuiopasdfghjklzxcvbnm0123456789';
        $str = '';

        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    // 返回本地备份的临时目录
    public function get_backup_path()
    {
        $options = get_option('qingstor-options');
        $backup_dir = $options['backup_dir'];
        if (! isset($backup_dir) || ! file_exists($backup_dir)) {
            $rand_suffix = $this->generate_rand_string();
            $wp_content_dir = defined(WP_CONTENT_DIR) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
            $options['backup_dir'] = $backup_dir =  "$wp_content_dir/QingStor-Backup-$rand_suffix";
            mkdir($backup_dir);
            chmod($backup_dir, 0777);
            update_option('qingstor-options', $options);
        }
        $zip_path = $backup_dir . '/wordpress-' . current_time('Y-m-d-H-i') . '.zip';
        $database_path = $backup_dir . '/database-' . current_time('Y-m-d-H-i') . '.sql';
        return array('zip_path' => $zip_path, 'database_path' => $database_path, 'backup_dir' => $backup_dir);
    }

    public function schedule_hook_run()
    {
        self::backup();
    }

    public function zip_cmd_test()
    {
        exec('zip --version', $output, $return_var);
        if ($return_var != 0) {
            return false;
        }
        return true;
    }

    public function mysqldump_cmd_test()
    {
        exec('mysqldump --version', $output, $return_var);
        if ($return_var != 0) {
            return false;
        }
        return true;
    }

    /**
     * 检查 WordPress 安装目录是否可读，以及 zip 和 mysqldump 命令是否可用
     * @return bool
     */
    function is_backup_possible()
    {
        if (! wp_is_writable(WP_CONTENT_DIR)) {
            return false;
        }
        if (! is_readable(ABSPATH)) {
            return false;
        }
        if (! $this->zip_cmd_test()) {
            return false;
        }
        if (! $this->mysqldump_cmd_test()) {
            return false;
        }
        return true;
    }
}

QingStorBackup::get_instance();
