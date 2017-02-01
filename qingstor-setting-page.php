<div class="wrap">
    <h1>QingStor 设置</h1>
    <form method="POST" action="">
        <div id="basic_settings">
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
            <p>*以下三项需要在 <a target="_blank" href="https://console.qingcloud.com/access_keys/">QingCloud 控制台</a>手动创建。</p>
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
                        <p class="description"><strong>注意：该 Bucket 应该仅用于 WordPress，请尽可能确保 Bucket 为空，否则可能丢失数据。</strong></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div id="upload_settings">
            <h2>上传设置</h2>
            <div>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="upload">文件类型</label>
                        </th>
                        <td>
                            <input id="upload" class="type-text regular-text" type="text" name="upload_types" value="<?php echo $qingstor_upload_types; ?>">
                            <p class="description">要上传的文件后缀。</p>
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
                    <tr>
                        <th>
                            <input class="checkbox" type="checkbox" id="replace_url" name="replace_url" value="true" <?php echo $qingstor_replace_url ? "checked='checked'" : ''; ?>>
                        </th>
                        <td>
                            <label for="replace_url">页面渲染时自动替换资源文件的 URL</label>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="backup_site">
            <h2 class="title">备份全站</h2>
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['backup'] || $_POST['schedule'])) {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                    <p><strong><?php echo $_POST['backup'] ? '已备份。' : '已设置定时备份。' ?></strong></p>
                </div>
                <?php
            }
            ?>
            <a href="?page=qingstor&once_backup=1">立即备份</a>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="schedule_type">定时备份</label>
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
                        <label for="start_day_of_week">开始日期</label>
                    </th>
                    <td>
                        <select id="start_day_of_week" name="schedule_recurrence[start_day_of_week]">
                            <option value="monday">星期一</option>
                            <option value="tuesday">星期二</option>
                            <option value="wednesday">星期三</option>
                            <option value="thursday">星期四</option>
                            <option value="friday">星期五</option>
                            <option value="saturday">星期六</option>
                            <option value="sunday">星期日</option>
                        </select>
                    </td>
                </tr>
                <tr id="start_date" style="display: none">
                    <th scope="row">
                        <label for="start_day_of_month">开始日期</label>
                    </th>
                    <td>
                        <input id="start_day_of_month" min="1" max="31" step="1" value="<?php echo $qingstor_recurrence['start_day_of_month']; ?>" name="schedule_recurrence[start_day_of_month]" type="number">
                    </td>
                </tr>
                <tr id="start_time" style="display: table-row">
                    <th scope="row">
                        <label for="start_hours">开始时间</label>
                    </th>
                    <td>
                        <span class="field-group">
                            <label for="">
                                <input id="start_hours" min="0" max="23" step="1" value="<?php echo $qingstor_recurrence['start_hours']; ?>" name="schedule_recurrence[start_hours]" type="number">
                                Hours
                            </label>
                            <label for="start_minutes">
                                <input id="start_minutes" min="0" max="59" step="1" value="<?php echo $qingstor_recurrence['start_minutes']; ?>" name="schedule_recurrence[start_minutes]" type="number">
                                Minutes
                            </label>
                        </span>
                        <p class="description">24 小时制。记得检查 WordPress 常规设置里的时区。</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="backup_num">保存备份的最大数量</label>
                    </th>
                    <td>
                        <input type="number" min="1" step="1" id="backup_num" value="<?php echo $qingstor_nbackup; ?>" name="backup_num">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <input type="checkbox" id="sendmail" name="sendmail" value="sendmail">
                        <label for="mailaddr">发送邮件至</label>
                    </th>
                    <td>
                        <input type="email" name="mailaddr" id="mailaddr" value="<?php echo $qingstor_mail; ?>">
                        <p class="description">如果未能发送，请检查 PHP 邮件发送相关设置。</p>
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
