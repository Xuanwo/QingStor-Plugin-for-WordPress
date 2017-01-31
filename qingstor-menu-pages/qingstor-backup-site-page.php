<div class="wrap">
    <h2>备份全站</h2>
    <div>
        <form method="POST" action="">
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['backup'] || $_POST['schedule'])) {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                    <p><strong><?php echo $_POST['backup'] ? '已备份。' : '已设置定时备份。' ?></strong></p>
                </div>
                <?php
            }
            ?>
            <p class="submit">
                <input id="backup" name="backup" class="button button-primary" value="立即备份" type="submit">
            </p>
        </form>
    </div>
    <hr/>
    <div>
        <h3>定时备份</h3>
        <form method="POST" action="">
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
                        <label for="start_day">开始日期</label>
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
                        <input id="start_day_of_month" min="1" max="31" step="1" value="1" name="schedule_recurrence[start_day_of_month]" type="number">
                    </td>
                </tr>
                <tr id="start_time" style="display: table-row">
                    <th scope="row">
                        <label for="start_hours">开始时间</label>
                    </th>
                    <td>
                        <span class="field-group">
                            <label for="">
                                <input id="start_hours" min="0" max="23" step="1" value="0" name="schedule_recurrence[start_hours]" type="number">
                                Hours
                            </label>
                            <label for="start_minutes">
                                <input id="start_minutes" min="0" max="59" step="1" value="0" name="schedule_recurrence[start_minutes]" type="number">
                                Minutes
                            </label>
                        </span>
                        <p class="description">24 小时制。记得检查 WordPress 常规设置里的时区。</p>
                    </td>
                </tr>
<!--                <tr>-->
<!--                    <th scope="row">-->
<!--                        <label for="backup_num">保存备份的最大数量</label>-->
<!--                    </th>-->
<!--                    <td>-->
<!--                        <input type="number" min="1" step="1" id="backup_num" value="10" name="backup_num">-->
<!--                    </td>-->
<!--                </tr>-->
<!--                <tr>-->
<!--                    <th scope="row">-->
<!--                        <input type="checkbox" id="sendmail" name="sendmail" value="okokok">-->
<!--                        <label for="mail">发送邮件至</label>-->
<!--                    </th>-->
<!--                    <td>-->
<!--                        <input type="email" name="email" id="email">-->
<!--                    </td>-->
<!--                </tr>-->
                </tbody>
            </table>
            <p class="submit">
                <input id="schedule" name="schedule" type="submit" class="button button-primary" value="定时备份">
            </p>
        </form>
    </div>
</div>
