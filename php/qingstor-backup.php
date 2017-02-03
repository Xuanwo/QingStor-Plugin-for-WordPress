<?php

final class QingStorBackup
{
    private static $instance;

    public function __construct()
    {
        add_action('qingstor_scheduled_backup_hook', array($this, 'backup'));
        add_action('qingstor_once_backup_hook', array($this, 'backup'));
        add_filter('cron_schedules', array($this, 'more_reccurences'));
    }

    public static function get_instance()
    {
        if (! (self::$instance instanceof QingStorBackup)) {
            self::$instance = new QingStorBackup();
        }
        return self::$instance;
    }

    public function scheduled_backup($recurrence) {
        $this->clear_schedule();
        if ($recurrence['schedule_type'] === 'manually') {
            return;
        }
        if (! key_exists($recurrence['schedule_type'], wp_get_schedules())) {
            return;
        }

        switch ($recurrence['schedule_type']) {
            case 'monthly':
                $time_str = $recurrence['start_day_of_month'] . ' ' . $recurrence['start_hours'] . ':' . $recurrence['start_minutes'];
                break;
            case 'weekly':
            case 'fortnightly':
                $time_str = $recurrence['start_day_of_week'] . ' ' . $recurrence['start_hours'] . ':' . $recurrence['start_minutes'];
                break;
            case 'daily':
            case 'twicedaily':
                $time_str = $recurrence['start_hours'] . ':' . $recurrence['start_minutes'];
                break;
            case 'hourly':
                $time_str = 'now';
                break;
            default:
                return;
        }
        $timestamp = strtotime($time_str) - ($time_str === 'now' ? 0 : get_option('gmt_offset') * HOUR_IN_SECONDS);
        wp_schedule_event($timestamp, $recurrence['schedule_type'], 'qingstor_scheduled_backup_hook');
    }

    public function clear_schedule() {
        wp_clear_scheduled_hook('qingstor_scheduled_bakcup_hook');
        wp_clear_scheduled_hook('qingstor_once_backup_hook');
    }

    // 一秒后触发的单次任务，用于立即备份
    public function once_bakcup() {
        wp_schedule_single_event(time() + 1, 'qingstor_once_backup_hook');
    }

    // 导出数据库，压缩 WordPress 目录和至临时文件夹，备份至 QingStor Bucket，然后删除临时目录
    public function backup()
    {
        set_time_limit(0);
        if (! $this->is_backup_possible()) {
            return;
        }
        $options = get_option('qingstor-options');
        $backup_path = $this->get_backup_path();

        // 使用 zip 备份 WordPress 目录(ABSPATH)
        $cwd = getcwd();
        chdir(ABSPATH);
        $command = 'zip -r ' . $backup_path['zip_path'] . ' .' ;
        exec($command, $output, $retvar1);
        // 使用 mysqldump 备份数据库
        unset($output);
        $mysql_connect_args = $this->get_mysql_connect_args();
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
            $this->check_backups_num();

            $bucket_zip_path = $options['backup_prefix'] . wp_basename($backup_path['zip_path']);
            $files[$backup_path['zip_path']] = $bucket_zip_path;
            QingStorUpload::get_instance()->upload_files($files);
            // 上传成功后，发送邮件通知
            $this->send_mail($bucket_zip_path);
        }
        // 删除临时目录及文件
        if (file_exists($backup_path['backup_dir'])) {
            $this->deldir($backup_path['backup_dir']);
        }
    }

    // 检查备份文件的数量，如果超过设置的最大数量，则删除最早的部分
    public function check_backups_num() {
        if (empty($bucket = qingstor_get_bucket())) {
            return;
        }
        $options = get_option('qingstor-options');
        $res = $bucket->listObjects(array('prefix' => $options['backup_prefix']));
        if (qingstor_http_status($res) == QS_OK) {
            $files = $res->{'keys'};
            $backups = array();
            foreach ($files as $f) {
                if (strstr($f['mime_type'], 'application/zip') && preg_match('/wordpress-\d{4}-\d{2}-\d{2}-\d{2}-\d{2}\.zip/iU', $f['key'])) {
                    $backups[] = $f['key'];
                }
            }
            rsort($backups);
            while (count($backups) >= $options['backup_num']) {
                $bucket->deleteObject(end($backups));
                array_pop($backups);
            }
        }
    }

    // 钩子函数，添加一些定时任务用到的频率
    function more_reccurences() {
        return array(
            'weekly' => array('interval' => 604800, 'display' => 'Once Weekly'),
            'fortnightly' => array('interval' => 1209600, 'display' => 'Once Every Two Weeks'),
            'monthly' => Array ( 'interval' => 2592000, 'display' => 'Once Monthly')
        );
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
            $wp_content_dir = defined(WP_CONTENT_DIR) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
            $rand_suffix = $this->generate_rand_string();
            $backup_dir =  "$wp_content_dir/QingStor-Backup-$rand_suffix";
            $options['backup_dir'] = $backup_dir;
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
        $this->backup();
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

    public function send_mail($filename) {
        if (! ($to = get_option('qingstor-options')['mailaddr'])) {
            return;
        }

        $message = 'QingStor backup ' . $filename . ' at ' . date('Y/m/d H:i', current_time('timestamp'));
        $headers = 'Form: wordpress@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])) . "\n";
        @wp_mail($to, get_bloginfo('name') . ' ' . 'WordPress backup', $message, $headers);
    }
}

QingStorBackup::get_instance();
