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
        'QingStor 其他设置',
        '其他设置',
        'manage_options',
        'qingstor'.'-other',
        'qingstor_other_settings_page'
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
        if (!empty($_POST['prefix'])) {
            $options['prefix'] = qingstor_test_input($_POST['prefix']);
            $options['media_files_dir'] = 'Media/' . $options['prefix'] . 'uploads/';
        }
        if (!empty($_POST['bucket_name']) && !empty(qingstor_get_bucket($_POST['bucket_name']))) {
            $options['bucket_name'] = $_POST['bucket_name'];
        }
    }
    update_option('qingstor-options', $options);
    $qingstor_access = $options['access_key'];
    $qingstor_secret = $options['secret_key'];
    $qingstor_bucket = $options['bucket_name'];
    $qingstor_prefix = $options['prefix'];
    require 'qingstor-setting-page.php';
}

function qingstor_other_settings_page() {
    ?>
    <div class="wrap">
        <h2>其他设置</h2>
    </div>
    <?php
}
