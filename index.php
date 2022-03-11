<?php

namespace Element\inc;
session_start();
define('BUILD', '1.0.0');
mb_internal_encoding('UTF-8');
$GLOBALS['APP_DIR'] = dirname(__FILE__);


// Bootstrap
foreach (scandir($GLOBALS['APP_DIR'] . '/inc/') as $config)
    (strlen($config) > 3) ? require($GLOBALS['APP_DIR'] . '/inc/' . $config) : null;