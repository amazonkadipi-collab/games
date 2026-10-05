<div class="content poki-home-content">
    <section class="poki-home-hero" aria-labelledby="home-title">
        <div class="poki-home-hero-copy">
            <p class="poki-eyebrow">FREE BROWSER GAMES</p>
            <h1 id="home-title">What are you playing today?</h1>
            <p>Play instantly on desktop, tablet or mobile. Discover popular, new and multiplayer browser games — no download required.</p>
        </div>
        <form class="poki-hero-search" method="GET" action="{{CONFIG_SITE_URL}}/search" role="search">
            <input type="search" name="q" placeholder="Search games, genres or themes" aria-label="Search games, genres or themes" autocomplete="off">
            <button type="submit">Search</button>
        </form>
    </section>
    {{NEW_GAMES}}
    {{DISCOVERY_PAGINATION}}
</div>

{{FOOTER_CONTENT}}