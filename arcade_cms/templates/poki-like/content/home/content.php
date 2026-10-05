<script language="javascript">var PageType ="{{NEW_GAME_PAGE}}"; var ids ="{{NEW_GAME_IDS}}";</script>
<div class="content poki-home-content">
    <section class="arcade-home-hero" aria-labelledby="home-title">
        <div class="arcade-hero-main">
            <div class="arcade-hero-copy">
                <p class="arcade-kicker">PLAY FREE • NO DOWNLOAD</p>
                <h1 id="home-title">Find your next game.</h1>
                <p>Jump straight into fast, fun browser games. Explore what is trending, discover fresh releases, and play on any screen.</p>
                <form class="arcade-search" method="GET" action="{{CONFIG_SITE_URL}}/search" role="search">
                    <label class="sr-only" for="home-game-search">Search games</label>
                    <input id="home-game-search" type="search" name="q" placeholder="Search games, genres, themes..." aria-label="Search games, genres or themes" autocomplete="off">
                    <button type="submit">Search games</button>
                </form>
                <div class="arcade-hero-links">
                    <a href="{{CONFIG_SITE_URL}}/popular">Popular games</a>
                    <a href="{{CONFIG_SITE_URL}}/new-games">New games</a>
                    <a href="{{CONFIG_SITE_URL}}/all-games">Browse all</a>
                </div>
            </div>
            <div class="arcade-hero-side" aria-hidden="true">
                <span class="hero-orb hero-orb-a"></span><span class="hero-orb hero-orb-b"></span>
                <div class="hero-stack hero-stack-one"></div><div class="hero-stack hero-stack-two"></div><div class="hero-stack hero-stack-three"></div>
                <span class="hero-play">▶</span>
            </div>
        </div>
    </section>
    {{NEW_GAMES}}
    {{DISCOVERY_PAGINATION}}
</div>

{{FOOTER_CONTENT}}