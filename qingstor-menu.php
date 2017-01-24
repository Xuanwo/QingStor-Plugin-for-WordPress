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
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $qingstor_options = get_option('qingstor-options');
        if (!empty($_POST['access_key'] && !empty($_POST['secret_key']))) {
            $qingstor_options['access_key'] = qingstor_test_input($_POST['access_key']);
            $qingstor_options['secret_key'] = qingstor_test_input($_POST['secret_key']);
        }
        if (isset($_POST['ok'])) {
            $qingstor_options['ok'] = true;
        } else {
            $qingstor_options['ok'] = false;
        }
        if (!empty($_POST['bucket_name'])) {
            if (!empty(qingstor_get_bucket($_POST['bucket_name']))) {
                if ($qingstor_options['bucket_name'] != $_POST['bucket_name']) {
                    $qingstor_options['bucket_name'] = $_POST['bucket_name'];
                    qingstor_bucket_init();
                }
            }
        }
        update_option('qingstor-options', $qingstor_options);
    }
    ?>
    <div class="wrap">
        <form method="POST" action="">
            <div>
                <h2>QingStor 设置</h2>
                <?php
                $service = qingstor_get_service();
                echo "<div><p>连接状态：";
                if ($service == QS_CLIERR) {
                    echo "无法连接 QingStor 服务，请填写正确的 ACCESS KEY 和 SECRET KEY 并保存。";
                } else if ($service == QS_SRVERR) {
                    echo "QingStor 对象存储系统暂时不可用，请稍后重试。";
                } else {
                    echo "已连接 QingStor 服务!";
                }
                ?>
                <p>*以下三项均需要先在 <a target="_blank" href="https://console.qingcloud.com/">QingCloud 控制台</a>创建。</p>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label  for="access">ACCESS KEY</label>
                        </th>
                        <td>
                            <input id="access" class="type-text regular-text" name="access_key" type="text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="secret">SECRET KEY</label>
                        </th>
                        <td>
                            <input id="secret" class="type-text regular-text" name="secret_key" type="text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="bucket">Bucket 名称</label>
                        </th>
                        <td>
                            <input id="bucket" class="type-text regular-text" name="bucket_name" type="text">
                            <p>当前 Bucket：<?php echo get_option('qingstor-options')['bucket_name']; ?></p>
                            <p><strong>注意：该 Bucket 应该仅用于 WordPress，请尽可能确保 Bucket 为空，否则可能丢失数据。</strong></p>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <p class="submit">
                    <input id="submit" class="button button-primary" name="submit" value="保存更改" type="submit">
                </p>
            </div>
        </form>
    </div>
    <?php
}

function qingstor_other_settings_page() {
    ?>
    <div class="wrap">
        <h2>其他设置</h2>
    </div>
    <?php
}
