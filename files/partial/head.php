<!DOCTYPE html>
<html lang="en">
<head>

    <title><?= $Element->get('config', 'title') ?> - <?= $Element->site('title') ?></title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css" rel="stylesheet">
    <link href="<?= $Element->resource('css/styles.css') ?>" rel="stylesheet">
    <link href="<?= $Element->resource('img/apple-touch-icon.png') ?>" rel="apple-touch-icon" sizes="180x180">
    <link href="<?= $Element->resource('img/favicon-32x32.png') ?>" rel="icon" sizes="32x32" type="image/png">
    <link href="<?= $Element->resource('img/favicon-16x16.png') ?>" rel="icon" sizes="16x16" type="image/png">
    <link href="<?= $Element->resource('manifest.json') ?>" rel="manifest">
    <?= $Element->css() ?>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="title" content="<?= $Element->get('config', 'title') ?> - <?= $Element->site('title') ?>"/>
    <meta name="description" content="<?= $Element->site('description') ?>">
    <meta name="keywords" content="<?= $Element->site('keywords') ?>">
    <meta property="og:url" content="<?= $this->url() ?>"/>
    <meta property="og:type" content="website"/>
    <meta property="og:site_name" content="<?= $Element->get('config', 'title') ?>"/>
    <meta property="og:title" content="<?= $Element->site('title') ?>"/>
    <meta name="twitter:site" content="<?= $this->url() ?>"/>
    <meta name="twitter:title" content="<?= $Element->get('config', 'title') ?> - <?= $Element->site('title') ?>"/>
    <meta name="twitter:description" content="<?= $Element->site('description') ?>"/>

</head>

<body>