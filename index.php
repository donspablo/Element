<?php

namespace Element\inc;
session_start();
define('BUILD', '1.0.0');
mb_internal_encoding('UTF-8');
$_ENV['APP_DIR'] = dirname(__FILE__);


// Bootstrap
foreach (scandir($_ENV['APP_DIR'] . '/inc/') as $config)
    (strlen($config) > 3) ? require($_ENV['APP_DIR'] . '/inc/' . $config) : null;