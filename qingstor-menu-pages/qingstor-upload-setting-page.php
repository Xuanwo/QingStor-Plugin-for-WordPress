<div class="wrap">
    <h2>上传设置</h2>
    <form method="POST" action="">
        <div>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                    <p>
                        <strong>设置已保存。</strong>
                    </p>
                    <button class="notice-dismiss" type="button">
                        <span class="screen-reader-text">忽略此通知。</span>
                    </button>
                </div>
                <?php
            }
            ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="upload">文件类型</label>
                    </th>
                    <td>
                        <input id="upload" class="type-text regular-text" type="text" name="upload_type" value="<?php echo $qingstor_upload_type; ?>">
                        <p class="describe">要上传的文件后缀。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="prefix">指定前缀</label>
                    </th>
                    <td>
                        <input id="prefix" class="type-text regular-text" name="prefix" type="text" value="<?php echo $qingstor_prefix; ?>">
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