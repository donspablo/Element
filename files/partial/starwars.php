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