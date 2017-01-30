<div class="wrap">
    <h2>QingStor 设置</h2>
    <form method="POST" action="">
        <div>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                    <p>
                        <strong>设置已保存。</strong>
                    </p>
                </div>
                <?php
            }
            ?>
            <p>*以下项目需要在 <a target="_blank" href="https://console.qingcloud.com/access_keys/">QingCloud 控制台</a>手动创建。</p>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label  for="access">ACCESS KEY</label>
                    </th>
                    <td>
                        <input id="access" class="type-text regular-text" name="access_key" type="text" value="<?php echo $qingstor_access; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="secret">SECRET KEY</label>
                    </th>
                    <td>
                        <input id="secret" class="type-text regular-text" name="secret_key" type="text" value="<?php echo $qingstor_secret; ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="bucket">Bucket 名称</label>
                    </th>
                    <td>
                        <input id="bucket" class="type-text regular-text" name="bucket_name" type="text" value="<?php echo $qingstor_bucket; ?>">
                        <p class="describe"><strong>注意：该 Bucket 应该仅用于 WordPress，请尽可能确保 Bucket 为空，否则可能丢失数据。</strong></p>
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
