<?php global $Element ?>

<!DOCTYPE html>
<html lang="en">
<head>

    <title><?= $Element->get('config', 'siteTitle') ?> - <?= $Element->site('title') ?></title>

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
    <meta name="title" content="<?= $Element->get('config', 'siteTitle') ?> - <?= $Element->site('title') ?>"/>
    <meta name="description" content="<?= $Element->site('description') ?>">
    <meta name="keywords" content="<?= $Element->site('keywords') ?>">
    <meta property="og:url" content="<?= $this->url() ?>"/>
    <meta property="og:type" content="website"/>
    <meta property="og:site_name" content="<?= $Element->get('config', 'siteTitle') ?>"/>
    <meta property="og:title" content="<?= $Element->site('title') ?>"/>
    <meta name="twitter:site" content="<?= $this->url() ?>"/>
    <meta name="twitter:title" content="<?= $Element->get('config', 'siteTitle') ?> - <?= $Element->site('title') ?>"/>
    <meta name="twitter:description" content="<?= $Element->site('description') ?>"/>

</head>

<body>

<?= $Element->options() ?>

<?= $Element->notice() ?>

<div class="card">
    <div class="section login">
        <div class="container">
            <div class="row full-height justify-content-center">
                <div class="col-12 text-center align-self-center py-5">
                    <div class="section pb-5 pt-5 pt-sm-2 text-center">
                        <input class="checkbox" id="reg-log" name="reg-log" type="checkbox"/>
                        <label for="reg-log"></label>
                        <div class="card-3d-wrap mx-auto">
                            <div class="card-3d-wrapper">
                                <div class="card-front">
                                    <div class="center-wrap">
                                        <div class="section text-center">
                                            <div class="form-group">
                                                <label for="username"></label><input autocomplete="off"
                                                                                     class="form-style" id="username"
                                                                                     name="username"
                                                                                     placeholder="Your Username"
                                                                                     type="text"> <i
                                                        class="input-icon uil uil-at"></i>
                                            </div>
                                            <div class="form-group mt-2">
                                                <label for="password"></label><input autocomplete="off"
                                                                                     class="form-style" id="password"
                                                                                     name="password"
                                                                                     placeholder="Your Password"
                                                                                     type="password"> <i
                                                        class="input-icon uil uil-lock-alt"></i>
                                            </div>
                                            <p><a class="btn mt-4" href="#" id="signin-btn">submit</a>
                                            </p>
                                            <br/>
                                            <p class="mb-0 mt-4 text-center">
                                                <a class="link" href="javascript:void(0)" id="resetPass">Forgot your
                                                    password?</a></p></div>
                                    </div>
                                </div>
                                <div class="card-back ">
                                    <div class="center-wrap ">
                                        <div class="section text-center ">
                                            <div class="form-group">
                                                <label for="newusername"></label><input autocomplete="off"
                                                                                        class="form-style "
                                                                                        id="newusername"
                                                                                        name="newusername"
                                                                                        placeholder="Your Username"
                                                                                        type="text"> <i
                                                        class="input-icon uil uil-at"></i>
                                            </div>
                                            <div class="form-group">
                                                <label for="newemail"></label><input autocomplete="off"
                                                                                     class="form-style" id="newemail"
                                                                                     name="newemail"
                                                                                     placeholder="Your Email"
                                                                                     type="email"> <i
                                                        class="input-icon uil uil-user"></i>
                                            </div>
                                            <div class="form-group">
                                                <label for="newpass"></label><input autocomplete="off"
                                                                                    class="form-style" id="newpass"
                                                                                    name="newpass"
                                                                                    placeholder="Your Password"
                                                                                    type="password"> <i
                                                        class="input-icon uil uil-lock-alt"></i>
                                            </div>
                                            <a class="btn mt-4" href="javascript:void(0)" id="signup-btn">submit</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<br/>
<p><a class="don" href="https://donPabloNow.com/" target="_blank">@donPaboNow</a>
</p>
<div id="intro">
    <div id="app">
        <div class="quadrant-top-left">
            <div class="welcome-component" data-message-farewell="See you later,{$user}."
                 data-message-welcome="Welcome,{$user}!"
                 data-region="cid10_chi_lobby"></div>
        </div>
        <div class="quadrant-top-right">
            <div class="connected-users" data-message="{$num}connected users nearby" data-region="cid10_chi_lobby">
                <h3>2 connected users nearby</h3>
            </div>
            <div class="coffee-brewed"
                 data-message-brewed="The last cup of coffee was brewed at{$time}.<br/> Better get some before it's gone."
                 data-message-depleted="There's no more coffee as of{$time}.<br/> Someone should brew some more."
                 data-state="coffeeBrewed">The
                last cup of coffee was brewed at 9:37am.
                <br>Better get some before it's gone.
            </div>
        </div>
        <div class="quadrant-bottom-left">
            <div class="conference-rooms" data-empty-verbiage="empty" data-full-verbiage="occupied"
                 data-message="{$room}is currently{$status}.">
                <h3 data-room-name="Conference room 1" id="cid10_chi_conf_1">Conference room 1 is currently vacant.</h3>
                <h3 data-room-name="Conference room 2" id="cid10_chi_conf_2">Conference room 2 is currently
                    occupied.</h3>
            </div>
        </div>
        <div id="cube">
            <div class="top"></div>
            <div class="left"></div>
            <div class="right"></div>
            <div class="front"></div>
            <div class="back"></div>
            <div class="bottom"></div>
        </div>
        <div id="background"></div>
    </div>
    <section class="nav">
        <h1>Element Dashboard</h1>
        <h3 class="span loader"><span class="m">C</span> <span class="m">O</span> <span class="m">N</span> <span
                    class="m">N</span> <span class="m">E</span> <span class="m">C</span> <span class="m">T</span> <span
                    class="m"></span> <span class="m"> </span> <span class="m">[</span> <span class="m">t</span> <span
                    class="m">o</span> <span class="m">]</span> <span class="m"> </span> <span class="m"> </span> <span
                    class="m">E</span> <span class="m">V</span> <span class="m">E</span> <span class="m">Y</span> <span
                    class="m">T</span> <span class="m">H</span> <span class="m">I</span> <span class="m">N</span> <span
                    class="m">G</span></h3>
        <div class="nav-container">
            <a class="nav-tab" href="#intro">DASHBOARD</a>
            <a class="nav-tab" href="#tab-graphql">TASKS</a>
            <a class="nav-tab" href="#tab-next">CHAT</a>
            <a class="nav-tab" href="#tab-typescript">CALENDAR</a>
            <a class="nav-tab" href="#tab-deno">FUN</a>
            <?= $Element->navigation() ?>
            <span class="nav-tab-slider"></span>
        </div>
    </section>
    <main class="main">
        <?= $Element->site('content') ?>
        <section class="slider" id="tab-pwa">
        </section>
        <section class="slider" id="tab-graphql">
        </section>
        <section class="slider" id="tab-next">
            <h1>CHAT</h1>
        </section>
        <section class="slider" id="tab-deno">
            <h1>GAME</h1>
        </section>
    </main>
</div>
<article class="starwars">
    <audio preload="auto">
        <source src="https://s.cdpn.io/1202/Star_Wars_original_opening_crawl_1977.ogg" type="audio/ogg"/>
        <source src="https://s.cdpn.io/1202/Star_Wars_original_opening_crawl_1977.mp3" type="audio/mpeg"/>
    </audio>
    <div class="animation">
        <section class="intro">A long time ago, in a galaxy far,
            <br>far away....
        </section>
        <section class="titles">
            <?= $Element->widget('subside') ?>
            <div contenteditable="true" spellcheck="false">
                <p>It is a period of civil war. Rebel spaceships, striking from a hidden base, have won their first
                    victory against the evil Galactic Empire.</p>
                <p>During the battle, Rebel spies managed to steal secret plans to the Empire's ultimate weapon, the
                    DEATH STAR, an armored space station with enough power to destroy an entire planet.</p>
                <p>Pursued by the Empire's sinister agents, Prfilesess Leia races home aboard her starship, custodian of
                    the stolen plan that can save her people and restore freedom to the galaxy....</p>
            </div>
        </section>
        <section class="logo">
            <img alt="" src="files/element/img/wars.svg"/>
        </section>
    </div>
</article>
<div class="overlays" style="--start: 45;">
    <div class="overlay" id="overlay_element"></div>
    <div class="overlay" id="overlay_stars"></div>
    <div class="overlay overlay_sun" id="overlay_sun">
        <div class="overlay_sun-sun" id="overlay_sun-sun"></div>
    </div>
    <div class="overlay overlay_sun" id="overlay_sun2">
        <div class="overlay_sun-sun" id="overlay_sun2-sun"></div>
    </div>
    <div class="overlay overlay_sun" id="overlay_sun3">
        <div class="overlay_sun-sun" id="overlay_sun3-sun"></div>
    </div>
    <div class="overlay" id="overlay_floor">
        <div id="overlay_floor-color"></div>
        <div id="overlay_floor-grid"></div>
        <div id="overlay_floor-horizon"></div>
        <div id="overlay_floor-horizon2"></div>
    </div>
    <div class="overlay overlay_mountain" id="overlay_mountain">
        <div class="overlay_mountain-mountain" id="overlay_mountain-mountain"></div>
    </div>
    <div class="overlay" id="overlay_overlay"></div>
</div>

<footer class="wrapper style2">
    <div class="inner">
        <?= $Element->footer() ?>

    </div>
</footer>

<script defer="defer" type="application/javascript" src="<?= $Element->resource('js/script.js') ?>"></script>
<?= $Element->js() ?>
</body>
</html>
