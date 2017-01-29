<?php

final class QingStorBackup
{
    private static $instance;

    public function __construct()
    {
        add_action('qingstor_schedule_hook', array($this, 'schedule_hook_run'));
    }

    public static function get_instance()
    {
        if (! (self::$instance instanceof QingStorBackup)) {
            self::$instance = new QingStorBackup();
        }
        return self::$instance;
    }

    public function schedule_hook_run()
    {
        if (! $this->is_backup_possible()) {
            return;
        }
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
