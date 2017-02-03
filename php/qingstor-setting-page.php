<div class="wrap">
    <h1>QingStor <?php _e('Settings', 'qingstor'); ?></h1>
    <form method="POST" action="">
        <div id="basic_settings">
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                    <p>
                        <strong><?php _e('Settings saved.', 'qingstor'); ?></strong>
                    </p>
                </div>
                <?php
            }
            ?>
            <p>*<?php _e('The following three items need to be created at ', 'qingstor'); ?><a target="_blank" href="https://console.qingcloud.com/access_keys/"><?php _e('QingCloud Console', 'qingstor'); ?></a><?php _e('.', 'qingstor'); ?></p>
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
                        <label for="bucket">Bucket <?php _e('Name', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input id="bucket" class="type-text regular-text" name="bucket_name" type="text" value="<?php echo $qingstor_bucket; ?>">
                        <p class="description"><strong><?php _e('Notice: The Bucket should be only used for WordPress. Please make sure the Bucket is empty.', 'qingstor'); ?></strong></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div id="upload_settings">
            <h2><?php _e('Upload Settings', 'qingstor'); ?></h2>
            <a href="?page=qingstor&upload_uploads=1"><?php _e('Upload the directory wp-content/uploads/', 'qingstor'); ?></a>
            <div>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="upload"><?php _e('File Types', 'qingstor'); ?></label>
                        </th>
                        <td>
                            <input id="upload" class="type-text regular-text" type="text" name="upload_types" value="<?php echo $qingstor_upload_types; ?>">
                            <p class="description"><?php _e('File suffixes to upload.', 'qingstor'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="upload_prefix"><?php _e('Prefix', 'qingstor'); ?></label>
                        </th>
                        <td>
                            <input id="upload_prefix" class="type-text regular-text" name="upload_prefix" type="text" value="<?php echo $qingstor_upload_prefix; ?>">
                            <p class="description"><?php _e('Media Files will be uploaded to the directory.', 'qingstor'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="bucket_url">Bucket URL</label>
                        </th>
                        <td>
                            <input id="bucket_url" class="type-text regular-text" name="bucket_url" type="text" value="<?php echo $qingstor_bucket_url; ?>">
                            <p class="description"><?php _e('Bucket URL. If there is a CDN, please fill in according to the actual situation. (Should add http:// or https://)', 'qingstor'); ?></p>

                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="replace_url"><?php _e('Automaticlly Replace the Media Files ', 'qingstor'); ?>URL</label>
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
            <h2 class="title"><?php _e('Backup WordPress', 'qingstor'); ?></h2>
            <a href="?page=qingstor&once_backup=1"><?php _e('Backup Now', 'qingstor'); ?></a>
            <table class="form-table">
                <tbody>
                <tr>
                    <th>
                        <label for="backup_prefix"><?php _e('Backup Prefix', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input id="backup_prefix" name="backup_prefix" type="text" class="text regular-text" value="<?php echo $qingstor_backup_prefix; ?>">
                        <p class="description"><?php _e('Backups will be stored in the directory.', 'qingstor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="schedule_type"><?php _e('Scheduled Backup', 'qingstor'); ?></label>
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
                            <option onclick="action(0, 0, 0)" value="manually">Manually Only</option>
                            <option onclick="action(0, 0, 0)" value="hourly">Once Hourly</option>
                            <option onclick="action(0, 0, 1)" value="twicedaily">Twice Daily</option>
                            <option onclick="action(0, 0, 1)" value="daily">Once Daily</option>
                            <option onclick="action(1, 0, 1)" selected="selected" value="weekly">Once Weekly</option>
                            <option onclick="action(1, 0, 1)" value="fortnightly">Once Every Two Weeks</option>
                            <option onclick="action(0, 1, 1)" value="monthly">Once Monthly</option>
                        </select>
                    </td>
                </tr>
                <tr id="start_day" style="display: table-row">
                    <th scope="row">
                        <label for="start_day_of_week"><?php _e('Start Day', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <select id="start_day_of_week" name="schedule_recurrence[start_day_of_week]">
                            <option value="monday"><?php _e('Monday', 'qingstor'); ?></option>
                            <option value="tuesday"><?php _e('Tuesday', 'qingstor'); ?></option>
                            <option value="wednesday"><?php _e('Wednesday', 'qingstor'); ?></option>
                            <option value="thursday"><?php _e('Thursday', 'qingstor'); ?></option>
                            <option value="friday"><?php _e('Friday', 'qingstor'); ?></option>
                            <option value="saturday"><?php _e('Saturday', 'qingstor'); ?></option>
                            <option value="sunday"><?php _e('Sunday', 'qingstor'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr id="start_date" style="display: none">
                    <th scope="row">
                        <label for="start_day_of_month"><?php _e('Start Day of Month', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input id="start_day_of_month" min="1" max="31" step="1" value="<?php echo $qingstor_recurrence['start_day_of_month']; ?>" name="schedule_recurrence[start_day_of_month]" type="number">
                    </td>
                </tr>
                <tr id="start_time" style="display: table-row">
                    <th scope="row">
                        <label for="start_hours"><?php _e('Start Time', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <span class="field-group">
                            <label for="">
                                <input id="start_hours" min="0" max="23" step="1" value="<?php echo $qingstor_recurrence['start_hours']; ?>" name="schedule_recurrence[start_hours]" type="number">
                                <?php _e('Hours', 'qingstor'); ?>
                            </label>
                            <label for="start_minutes">
                                <input id="start_minutes" min="0" max="59" step="1" value="<?php echo $qingstor_recurrence['start_minutes']; ?>" name="schedule_recurrence[start_minutes]" type="number">
                                <?php _e('Minutes', 'qingstor'); ?>
                            </label>
                        </span>
                        <p class="description"><?php _e('24-hour format.', 'qingstor'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="backup_num"><?php _e('Number of backups to store', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input type="number" min="1" step="1" id="backup_num" value="<?php echo $qingstor_nbackup; ?>" name="backup_num">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <input type="checkbox" id="sendmail" name="sendmail" value="sendmail">
                        <label for="mailaddr"><?php _e('Email to ', 'qingstor'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="mailaddr" id="mailaddr" value="<?php echo $qingstor_mail; ?>">
                        <p class="description"><?php _e('If it cannot send a mail, check your PHP mail settings.', 'qingstor'); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <p class="submit">
            <input id="submit" class="button button-primary" name="submit" value="<?php _e('Save Changes', 'qingstor'); ?>" type="submit">
        </p>
    </form>
</div>
