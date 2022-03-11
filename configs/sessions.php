<?php


namespace Element\config;

ini_set('session.cookie_httponly', true);
ini_set('session.session.use_only_cookies', true);
ini_set('session.cookie_lifetime', false);
ini_set('session.cookie_secure', true);


if (!isset($_SESSION)) session_start();


if ((isset($_SESSION['st']['userId'])) && ($_SESSION['st']['userId'] !== '')) {

    $st_userId = $_SESSION['st']['userId'];
    $st_username = $_SESSION['st']['userName'];
    $st_useremail = $_SESSION['st']['userEmail'];
} else {
    $st_userId = $st_username = $st_useremail = '';
}
