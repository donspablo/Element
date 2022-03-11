<?php

namespace Element\inc;

foreach (@file($GLOBALS['APP_DIR'] . '/.env') as $line) {

    if (substr($line, 0, 4) === '#')
        continue;

    list($name, $value) = explode('=', $line, 2);
    $GLOBALS[$name] = $value;
}

$GLOBALS['SITE_URL'] = ($GLOBALS['SITE_URL'] === 'default') ? "https://" . @$_SERVER['HTTP_HOST'] : $GLOBALS['SITE_URL'];
$GLOBALS['SITE_EMAIL'] = $GLOBALS['SITE_EMAIL'];

//error_reporting(($GLOBALS['DEBUG'] !== 'true') ? 0 : 1);

//date_default_timezone_set($GLOBALS['TIME_ZONE']);

ini_set('SMTP', $GLOBALS['SMTP']);
ini_set('smtp_port', $GLOBALS['smtp_port']);
ini_set('username', $GLOBALS['username']);
ini_set('password', $GLOBALS['password']);
ini_set('sendmail_from', $GLOBALS['sendmail_from']);



//if (isset($_GET['signin'])) new signin();
