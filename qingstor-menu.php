<?php

add_action('admin_menu', 'qingstor_settings_menu');
function qingstor_settings_menu() {
    add_menu_page(
        'QingStor',
        'QingStor',
        'manage_options',
        'qingstor'
    );
    add_submenu_page(
        'qingstor',
        'QingStor',
        'QingStor 设置',
        'manage_options',
        'qingstor',
        'qingstor_settings_page'
    );
    add_submenu_page(
        'qingstor',
        '上传设置',
        '上传设置',
        'manage_options',
        'qingstor'.'-upload',
        'qingstor_upload_setting_page'
    );
    add_submenu_page(
        'qingstor',
        '备份全站',
        '备份全站',
        'manage_options',
        'qingstor'.'-backup',
        'qingstor_backup_site_page'
    );
}

function qingstor_settings_page() {
    $options = get_option('qingstor-options');
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['access_key'])) {
            $options['access_key'] = qingstor_test_input($_POST['access_key']);
        }
        if (!empty($_POST['secret_key'])) {
            $options['secret_key'] = qingstor_test_input($_POST['secret_key']);
        }
        if (!empty($_POST['bucket_name'])) {
            $options['bucket_name'] = $_POST['bucket_name'];
            // 设置存储空间策略
            qingstor_bucket_init();
        }
    }
    update_option('qingstor-options', $options);
    $qingstor_access = $options['access_key'];
    $qingstor_secret = $options['secret_key'];
    $qingstor_bucket = $options['bucket_name'];
    require_once 'qingstor-menu-pages/qingstor-setting-page.php';
}

function qingstor_upload_setting_page() {
    $options = get_option('qingstor-options');
    if (empty($options['upload_type'])) {
        $options['upload_type'] = "jpg|jpeg|png|gif|mp3|doc|pdf|ppt|pps";
    } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['upload_type'])) {
            $options['upload_type'] = qingstor_test_input($_POST['upload_type']);
        }
        if (!empty($_POST['prefix'])) {
            $options['prefix'] = qingstor_test_input($_POST['prefix']);
            $options['media_dir'] = 'Media/' . $options['prefix'];
        }
    }
    update_option('qingstor-options', $options);
    $qingstor_upload_type = $options['upload_type'];
    $qingstor_prefix = $options['prefix'];
    require_once 'qingstor-menu-pages/qingstor-upload-setting-page.php';
}

function qingstor_backup_site_page() {
    require_once 'qingstor-menu-pages/qingstor-backup-site-page.php';
}
