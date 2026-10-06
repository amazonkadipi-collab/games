<link rel="stylesheet" href="{{CONFIG_SITE_URL}}/templates/poki-like/css/home-redesign.css?ver=20261005" media="all">
<script language="javascript">var PageType ="{{NEW_GAME_PAGE}}"; var ids ="{{NEW_GAME_IDS}}";</script>
<div class="content poki-home-content">
    <section class="arcade-home-hero" aria-labelledby="home-title">
        <div class="arcade-hero-main">
            <div class="arcade-hero-copy">
                <p class="arcade-kicker">PLAY FREE • INSTANT BROWSER ARCADE</p>
                <h1 id="home-title">Play something<br>brilliant.</h1>
                <p>Fast games, fresh drops and hidden gems for every kind of player. No downloads, no waiting — just pick a mood and press play.</p>
                <form class="arcade-search" method="GET" action="{{CONFIG_SITE_URL}}/search" role="search">
                    <label class="sr-only" for="home-game-search">Search games</label>
                    <input id="home-game-search" type="search" name="q" placeholder="Search games, genres, themes..." aria-label="Search games, genres or themes" autocomplete="off">
                    <button type="submit">Find a game</button>
                </form>
                <div class="arcade-hero-links" aria-label="Quick game discovery">
                    <a href="{{CONFIG_SITE_URL}}/popular">Popular right now</a>
                    <a href="{{CONFIG_SITE_URL}}/new-games">Fresh drops</a>
                    <a href="{{CONFIG_SITE_URL}}/all-games">Explore the arcade</a>
                </div>
            </div>
            <div class="arcade-hero-side" aria-hidden="true">
                <span class="hero-orb hero-orb-a"></span><span class="hero-orb hero-orb-b"></span>
                <div class="hero-stack hero-stack-one"></div><div class="hero-stack hero-stack-two"></div><div class="hero-stack hero-stack-three"></div>
                <span class="hero-play">▶</span>
            </div>
        </div>
    </section>
    <div class="arcade-trust-row" aria-label="PlayGrid benefits">
        <span><b>01</b> Instant play</span><span><b>02</b> Curated picks</span><span><b>03</b> Works on every screen</span><span><b>04</b> New games every week</span>
    </div>
    {{NEW_GAMES}}
    {{DISCOVERY_PAGINATION}}
</div>

{{FOOTER_CONTENT}}
