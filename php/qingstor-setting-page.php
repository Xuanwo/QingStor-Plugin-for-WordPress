<div class="wrap">
    <h1>QingStor <?php _e('设置', 'qingstor'); ?></h1>
    <form method="POST" action="">
        <div id="basic_settings">
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                    <p>
                        <strong><?php _e('设置已保存。', 'qingstor'); ?></strong>
                    </p>
                </div>
                <?php
            }
            ?>
            <p>*<?php echo __('以下三项需要在', 'qingstor'); ?> <a target="_blank" href="https://console.qingcloud.com/access_keys/"><?php _e('QingCloud 控制台', 'qingstor'); ?></a><?php _e('手动创建。', 'qingstor'); ?></p>
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
                        <label for="bucket">Bucket <?php _e('名称', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input id="bucket" class="type-text regular-text" name="bucket_name" type="text" value="<?php echo $qingstor_bucket; ?>">
                        <p class="description"><strong><?php _e('注意：该 Bucket 应该仅用于 WordPress，请尽可能确保 Bucket 为空，否则可能丢失数据。', 'qingstor'); ?></strong></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div id="upload_settings">
            <h2>上传设置</h2>
            <a href="?page=qingstor&upload_uploads=1"><?php _e('上传 wp-content/uploads/ 目录', 'qingstor'); ?></a>
            <div>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="upload"><?php _e('文件类型', 'qingstor'); ?></label>
                        </th>
                        <td>
                            <input id="upload" class="type-text regular-text" type="text" name="upload_types" value="<?php echo $qingstor_upload_types; ?>">
                            <p class="description"><?php _e('要上传的文件后缀。', 'qingstor'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="upload_prefix"><?php _e('指定前缀', 'qingstor'); ?></label>
                        </th>
                        <td>
                            <input id="upload_prefix" class="type-text regular-text" name="upload_prefix" type="text" value="<?php echo $qingstor_upload_prefix; ?>">
                            <p class="description"><?php _e('Media 文件将上传到该目录。', 'qingstor'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="bucket_url">Bucket URL</label>
                        </th>
                        <td>
                            <input id="bucket_url" class="type-text regular-text" name="bucket_url" type="text" value="<?php echo $qingstor_bucket_url; ?>">
                            <p class="description"><?php _e('Bucket 的 URL。如果开启了 CDN，请填写 CDN 加速地址(注意添加 http:// 或 https://)。', 'qingstor'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="replace_url"><?php _e('页面渲染时自动替换资源文件的', 'qingstor'); ?> URL</label>
                        </th>
                        <td>
                            <input class="checkbox" type="checkbox" id="replace_url" name="replace_url" value="true" <?php echo $qingstor_replace_url ? "checked='checked'" : ''; ?>>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="backup_site">
            <h2 class="title"><?php _e('备份 WordPress', 'qingstor'); ?></h2>
            <a href="?page=qingstor&once_backup=1"><?php _e('立即备份', 'qingstor'); ?></a>
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="backup_prefix"><?php _e('指定前缀', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input id="backup_prefix" name="backup_prefix" type="text" class="text regular-text" value="<?php echo $qingstor_backup_prefix; ?>">
                        <p class="description"><?php _e('备份文件将保存到该目录下。', 'qingstor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="schedule_type"><?php _e('定时备份', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <script>
                            function action(day, date, time) {
                                document.getElementById('start_day').style.display = day ? 'table-row' : 'none';
                                document.getElementById('start_date').style.display = date ? 'table-row' : 'none';
                                document.getElementById('start_time').style.display = time ? 'table-row' : 'none';
                            }
                        </script>
                        <select id="schedule_type" name="schedule_recurrence[schedule_type]">
                            <option onclick="action(0, 0, 0)" value="manually">仅手动备份</option>
                            <option onclick="action(0, 0, 0)" value="hourly">每小时一次</option>
                            <option onclick="action(0, 0, 1)" value="twicedaily">每天两次</option>
                            <option onclick="action(0, 0, 1)" value="daily">每天一次</option>
                            <option onclick="action(1, 0, 1)" selected="selected" value="weekly">每周一次</option>
                            <option onclick="action(1, 0, 1)" value="fortnightly">两周一次</option>
                            <option onclick="action(0, 1, 1)" value="monthly">每月一次</option>
                        </select>
                    </td>
                </tr>
                <tr id="start_day" style="display: table-row">
                    <th scope="row">
                        <label for="start_day_of_week"><?php _e('开始日期', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <select id="start_day_of_week" name="schedule_recurrence[start_day_of_week]">
                            <option value="monday"><?php _e('星期一', 'qingstor'); ?></option>
                            <option value="tuesday"><?php _e('星期二', 'qingstor'); ?></option>
                            <option value="wednesday"><?php _e('星期三', 'qingstor'); ?></option>
                            <option value="thursday"><?php _e('星期四', 'qingstor'); ?></option>
                            <option value="friday"><?php _e('星期五', 'qingstor'); ?></option>
                            <option value="saturday"><?php _e('星期六', 'qingstor'); ?></option>
                            <option value="sunday"><?php _e('星期日', 'qingstor'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr id="start_date" style="display: none">
                    <th scope="row">
                        <label for="start_day_of_month"><?php _e('开始日期', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input id="start_day_of_month" min="1" max="31" step="1" value="<?php echo $qingstor_recurrence['start_day_of_month']; ?>" name="schedule_recurrence[start_day_of_month]" type="number">
                    </td>
                </tr>
                <tr id="start_time" style="display: table-row">
                    <th scope="row">
                        <label for="start_hours"><?php _e('开始时间', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <span class="field-group">
                            <label for="">
                                <input id="start_hours" min="0" max="23" step="1" value="<?php echo $qingstor_recurrence['start_hours']; ?>" name="schedule_recurrence[start_hours]" type="number">
                                <?php _e('时', 'qingstor'); ?>
                            </label>
                            <label for="start_minutes">
                                <input id="start_minutes" min="0" max="59" step="1" value="<?php echo $qingstor_recurrence['start_minutes']; ?>" name="schedule_recurrence[start_minutes]" type="number">
                                <?php _e('分', 'qingstor'); ?>
                            </label>
                        </span>
                        <p class="description"><?php _e('24 小时制。记得检查 WordPress 常规设置里的时区。', 'qingstor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="backup_num"><?php _e('保存备份的最大数量', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input type="number" min="1" step="1" id="backup_num" value="<?php echo $qingstor_nbackup; ?>" name="backup_num">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <input type="checkbox" id="sendmail" name="sendmail" value="sendmail">
                        <label for="mailaddr"><?php _e('发送邮件至', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="mailaddr" id="mailaddr" value="<?php echo $qingstor_mail; ?>">
                        <p class="description"><?php _e('如果未能发送，请检查 PHP 邮件发送相关设置。', 'qingstor'); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <p class="submit">
            <input id="submit" class="button button-primary" name="submit" value="保存更改" type="submit">
        </p>
    </form>
</div>
