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