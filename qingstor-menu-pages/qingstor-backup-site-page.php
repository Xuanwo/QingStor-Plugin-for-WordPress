<div class="wrap">
    <h2>备份全站</h2>
    <div>
        <form method="POST" action="">
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['backup']) {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                    <p><strong>已备份。</strong></p>
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
            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['backup']) {
                ?>
                <div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
                    <p><strong>设置已保存。</strong></p>
                </div>
                <?php
            }
            ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="backup_schedule_type">定时备份</label>
                    </th>
                    <td>
                        <select id="backup_schedule_type" name="backup_schedule_type">
                            <option value="manually">仅手动备份</option>
                            <option value="hourly">每小时一次</option>
                            <option value="twicedaily">每天两次</option>
                            <option value="daily">每天一次</option>
                            <option value="weekly">每周一次</option>
                            <option value="fortnightly">两周一次</option>
                            <option value="monthly">每月一次</option>
                        </select>
                    </td>
                </tr>
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
