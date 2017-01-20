<?php
/**
 * Created by PhpStorm.
 * User: yungkc
 * Date: 17-1-20
 * Time: 上午11:58
 */

function qingstor_menu() {
    add_menu_page(
        'QingStor Settings Page',
        'Menu Example Settings',
        'manage_options',
        'qingstor-menu',
        'qingstor_menu_page'
    );
}

function qingstor_menu_page() {
    ?>
    <div class="wrap">
        <form method="POST">
            <table>
                <tbody>
                <tr>
                    <th scope="row">
                        <label>ACCESS KEY</label>
                    </th>
                    <td>
                        <input id="access" name="access_key" type="text">
                    </td>
                    <th scope="row">
                        <label>SECRET KEY</label>
                    </th>
                    <td>
                        <input id="secret" name="secret_key" type="text">
                    </td>
                </tr>
                </tbody>
            </table>
            <input id="submit" class="button button-primary" name="submit" value="保存更改" type="submit">
        </form>
    </div>
    <?php
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['access_key'] && !empty($_POST['secret_key']))) {
        update_option('access_key', $_POST['access_key']);
        update_option('secret_key', $_POST['secret_key']);
    }
}
