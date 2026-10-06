


<div id="header" class="fix-top">
    <div class="head-inner">
        <div class="menu-mobile">
            <i class="material-icons-outlined material-symbols-outlined">menu</i>
            <i class="material-icons-outlined material-symbols-outlined fn-hide">close</i>
        </div>
        <a href="{{CONFIG_SITE_URL}}/" class="pg-brand" aria-label="{{CONFIG_SITE_NAME}}">
            <span class="pg-brand-logo" aria-hidden="true">
                <svg viewBox="0 0 48 48" role="img" focusable="false">
                    <path class="pg-logo-shape" d="M24 2C11.85 2 4 10.1 4 22.7c0 12.5 7.6 23.3 20 23.3 11.6 0 20-9.1 20-22.2C44 11 36.2 2 24 2Z"></path>
                    <path class="pg-logo-cut" d="M18 13v24h6V29h3.3c6.2 0 9.7-3.1 9.7-8.2S33.5 13 27.3 13H18Zm6 5h3c2.6 0 4 1 4 3s-1.4 3-4 3h-3v-6Z"></path>
                    <circle class="pg-logo-dot" cx="34.5" cy="10.5" r="3.2"></circle>
                </svg>
            </span>
        </a>
        <div class="search-form">
            <form id="search-data-form" method="GET" action="{{CONFIG_SITE_URL}}/search" autocomplete="off">
                <input type="text" class="txt fn-left search-input" id="Search-InArea" name="q" placeholder="What are you playing today?" aria-label="What are you playing today?">
                <input type="submit" class="btn" value="" aria-label="Search">
            </form>
        </div>
        <div class="other-btn">
            <div class="menu-btn new-games">
                <i class="new"></i>
                <div><div>New Games</div><div class="more"><span>&</span> more</div></div>
                <i class="arrow-down"></i>
                <div class="new-games-pop-up fn-hide">
                    <a href="{{CONFIG_SITE_URL}}/new-games"><i class="material-icons-outlined material-symbols-outlined">new_releases</i>New Games</a>
                    <a href="{{CONFIG_SITE_URL}}/popular"><i class="material-icons-outlined material-symbols-outlined">star</i>Popular Games</a>
                    <a href="{{CONFIG_SITE_URL}}/featured-games"><i class="material-icons-outlined material-symbols-outlined">auto_graph</i>Featured Games</a>
                    <a href="{{CONFIG_SITE_URL}}/played-games"><i class="material-icons-outlined material-symbols-outlined">play_circle</i>Played Games</a>
                </div>
            </div>
            <a class="menu-btn" href="{{CONFIG_SITE_URL}}/blogs"><i class="blog"></i><div>Blog</div></a>
        </div>
        <div class="right-btn-mobile"><div class="search-btn" aria-label="Search"></div></div>
    </div>
</div>
<div class="h-head"></div>
<nav class="poki-top-nav" aria-label="Game categories">
    <div class="poki-top-nav-inner">
        <a href="{{CONFIG_SITE_URL}}/popular">Popular Games</a>
        {{CATEGORIES_LIST_2}}
        <a href="{{CONFIG_SITE_URL}}/categories">All Categories</a>
    </div>
</nav>
<div class="new-games-pop-up-mobile fn-hide">
    <div class="new-games-btns">
        <a href="{{CONFIG_SITE_URL}}/new-games"><i class="material-icons-outlined material-symbols-outlined">new_releases</i>New Games</a>
        <a href="{{CONFIG_SITE_URL}}/best-games"><i class="material-icons-outlined material-symbols-outlined">star</i>Best Games</a>
        <a href="{{CONFIG_SITE_URL}}/featured-games"><i class="material-icons-outlined material-symbols-outlined">auto_graph</i>Featured Games</a>
        <a href="{{CONFIG_SITE_URL}}/played-games"><i class="material-icons-outlined material-symbols-outlined">play_circle</i>Played Games</a>
    </div>
    <div class="home-categories">{{CATEGORIES_LIST_2}}<a href="{{CONFIG_SITE_URL}}/categories">More Categories</a></div>
    <div class="home-tags">{{TAGS_LIST}}<a href="{{CONFIG_SITE_URL}}/tags">More Tags</a></div>
</div>