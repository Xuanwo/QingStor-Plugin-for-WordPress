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
            $qingstor_options['access_key'] = $_POST['access_key'];
            $qingstor_options['secret_key'] = $_POST['secret_key'];
        }
        if (isset($_POST['ok'])) {
            $qingstor_options['ok'] = true;
        } else {
            $qingstor_options['ok'] = false;
        }
        update_option('qingstor-options', $qingstor_options);
    }
    ?>
    <div class="wrap">
        <form method="POST" action="">
            <div id="tab-key">
                <h2>QingStor 设置</h2>
                <?php
                if (empty(qingstor_get_service())) {
                    echo "<div><p>连接状态：无法连接 QingStor 服务，请填写正确的 ACCESS KEY 和 SECRET KEY 并保存。</p></div>";
                } else {
                    echo "<div><p>连接状态：已连接 QingStor 服务!</p></div>";
                }
                ?>
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
                            <label for="ok">OK</label>
                        </th>
                        <td>
                            <input id="ok" name="ok" class="checkbox" type="checkbox">详细说明
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
