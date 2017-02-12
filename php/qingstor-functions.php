<?php

use QingStor\SDK\Service\QingStor;
use QingStor\SDK\Config;

define('QS_CLIERR', 1);
define('QS_SRVERR', 2);
define('QS_OK', 3);
define('QS_MAX_STRLEN', 2048);
define('QS_MAX_KEYLEN', 40);

/**
 * Test the returned statusCode of QingStor SDK.
 * @param $response
 * @return int
 */
function qingstor_http_status($response)
{
    if ($response->statusCode >= 500) {
        return QS_SRVERR;
    } elseif ($response->statusCode >= 400) {
        return QS_CLIERR;
    } else {
        return QS_OK;
    }
}

/**
 * Test access key and secret key，return service if OK, else return null.
 * @return null|QingStor
 */
function qingstor_get_service()
{
    $qingstor_options = get_option('qingstor-options');
    if (empty($qingstor_options)) {
        return NULL;
    }
    $config = new Config($qingstor_options['access_key'], $qingstor_options['secret_key']);
    $service = new QingStor($config);

    $res = $service->listBuckets();
    if (qingstor_http_status($res) != QS_OK) {
        return NULL;
    }
    return $service;
}

/**
 * Test bucket_name on QingStor，reuturn bucket if the Bucket is exists, else return null.
 * @return null|\QingStor\SDK\Service\Bucket
 */
function qingstor_get_bucket()
{
    $service = qingstor_get_service();
    if (empty($service)) {
        return NULL;
    }
    $options = get_option('qingstor-options');
    $bucket = $service->Bucket($options['bucket_name'], 'pek3a');
    $res = $bucket->head();
    if (qingstor_http_status($res) != QS_OK) {
        return NULL;
    }
    return $bucket;
}

// Set policy of the Bucket.
function qingstor_bucket_init()
{
    if (!empty($bucket = qingstor_get_bucket())) {
        $options = get_option('qingstor-options');
        $bucket->putPolicy(
            array(
                "statement" => array(
                    array(
                        "id" => "allow all users to get object",
                        "user" => "*",
                        "action" => array("get_object"),
                        "effect" => "allow",
                        "resource" => array($options['bucket_name'] . '/' . $options['upload_prefix'] . '*'),
                    )
                )
            )
        );
    }
}

// Test input of <form>.
function qingstor_test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return strlen($data) > QS_MAX_STRLEN ? substr($data, 0, QS_MAX_STRLEN) : $data;
}

function qingstor_test_key($key)
{
    $key = qingstor_test_input($key);
    if (strlen($key) > QS_MAX_KEYLEN) {
        $key = '';
    }
    return $key;
}

function qingstor_test_bucket_name($name)
{
    $name = qingstor_test_input($name);
    if (strlen($name) > 63 || strlen($name) < 6) {
        $name = 'bucket-name';
    }
    return $name;
}

function qingstor_test_prefix($prefix) {
    return ltrim(rtrim(qingstor_test_input($prefix), '/') . '/', '/');
}

function qingstor_test_url($url) {
    return rtrim(qingstor_test_input($url), '/') . '/';
}

function qingstor_test_num($num, $min, $max)
{
    $num = intval($num);
    if (! $num || $num > $max || $num < $min) {
        return $min;
    }
    return $num;
}

function qingstor_test_email($email)
{
    $email = sanitize_email($email);
    if (is_email($email)) {
        return $email;
    }
    return '';
}

// Get URL of current page for redirect.
function qingstor_get_page_url()
{
    $pageURL = 'http';

    if ($_SERVER["HTTPS"] == "on")
    {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    $this_page = $_SERVER["REQUEST_URI"];

    if (strpos($this_page, "?") !== false)
    {
        $this_pages = explode("?", $this_page);
        $this_page = reset($this_pages);
    }

    if ($_SERVER["SERVER_PORT"] != "80")
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $this_page;
    }
    else
    {
        $pageURL .= $_SERVER["SERVER_NAME"] . $this_page;
    }
    return $pageURL;
}

// After `Upload the directory wp-content/uploads/' or `Backup Now'.
function qingstor_redirect()
{
    $url = qingstor_get_page_url() . '?page=qingstor';
    echo "<script language='javascript' type='text/javascript'>";
    echo "window.location.href='$url'";
    echo "</script>";
}

function qingstor_activation()
{
    $options = array(
        'upload_types'  => 'jpg|jpeg|png|gif|mp3|doc|pdf|ppt|pps',
        'upload_prefix' => 'wordpress/uploads/',
        'backup_prefix' => 'wordpress/backup/',
        'schedule_recurrence' => array(
            'start_day_month' => '1',
            'start_hours'     => '3',
            'start_minutes'   => '0',
        ),
        'bucket_url'    => 'https://bucket-name.pek3a.qingstor.com/',
        'backup_num'    => '7'
    );
    update_option('qingstor-options', $options);
}

function qingstor_deactivation()
{
    QingStorBackup::get_instance()->clear_schedule();
    delete_option('qingstor-options');
}
