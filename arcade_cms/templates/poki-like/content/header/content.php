<style>
/* Clean, fast arcade UI — loaded inline so the first paint does not wait for another stylesheet. */
:root{--ui-bg:#f6f8fc;--ui-card:#fff;--ui-text:#152238;--ui-muted:#64748b;--ui-accent:#009cff;--ui-border:#e5eaf1;--ui-radius:14px}
html,body{width:100%;max-width:100%;overflow-x:hidden!important}
body{background:var(--ui-bg)!important;background-image:none!important;color:var(--ui-text)!important;font-family:"Open Sans",Arial,sans-serif!important}
#header{height:68px!important;background:rgba(255,255,255,.98)!important;box-shadow:0 1px 8px rgba(15,23,42,.08)!important}
.h-head{height:68px!important}
.head-inner{height:68px!important;max-width:1400px!important;padding:0 16px!important;gap:12px!important}
.logo a{height:68px!important;width:150px!important;background-size:100px!important}
.search-form{flex:1 1 520px!important;max-width:560px!important;margin:0 auto!important}
.search-form form{height:44px!important;display:flex!important}
.search-form .search-input{width:100%!important;height:44px!important;border:1px solid var(--ui-border)!important;border-radius:12px 0 0 12px!important;background:#fff!important;box-shadow:none!important;padding:0 14px!important;outline:0!important}
.search-form .btn{width:48px!important;height:44px!important;border:0!important;border-radius:0 12px 12px 0!important;background:#009cff!important}
.other-btn{gap:8px!important}
.menu-btn{height:42px!important;margin-left:0!important;border:1px solid var(--ui-border)!important;background:#fff!important;border-radius:11px!important;padding:0 12px!important;box-shadow:none!important}
.menu-btn:hover{background:#f5f8fc!important;color:var(--ui-accent)!important;text-decoration:none!important}
.gamemonetize-container{max-width:1400px!important;margin:0 auto!important;padding:84px 16px 36px!important;min-height:0!important}
.content{max-width:1400px!important;margin:0 auto!important;padding:0!important}
.game-list-grid-container{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(150px,1fr))!important;grid-auto-flow:row!important;grid-auto-rows:auto!important;grid-template-areas:none!important;gap:14px!important;width:100%!important}
.game-list-grid-container>*{grid-area:auto!important;min-width:0!important}
.game-list-grid-container img{width:100%!important;height:auto!important;aspect-ratio:1/1!important;object-fit:cover!important}
.post,.game-post,.post-item,.game-item{background:#fff!important;border:1px solid var(--ui-border)!important;border-radius:var(--ui-radius)!important;box-shadow:0 2px 10px rgba(15,23,42,.06)!important;overflow:hidden!important}
.post:hover,.game-post:hover,.post-item:hover,.game-item:hover{transform:translateY(-1px)!important;box-shadow:0 5px 16px rgba(15,23,42,.10)!important}
.hide-text{position:absolute!important;left:-10000px!important;width:1px!important;height:1px!important;overflow:hidden!important}
.new-games-pop-up,.new-games-pop-up-mobile{box-shadow:0 10px 30px rgba(15,23,42,.14)!important;border:1px solid var(--ui-border)!important;border-radius:12px!important;background:#fff!important}
@media (min-width:1200px){.game-list-grid-container{grid-template-columns:repeat(8,minmax(0,1fr))!important}}
@media (min-width:900px) and (max-width:1199px){.game-list-grid-container{grid-template-columns:repeat(6,minmax(0,1fr))!important}}
@media (min-width:600px) and (max-width:899px){.game-list-grid-container{grid-template-columns:repeat(4,minmax(0,1fr))!important}}
@media (max-width:599px){#header{height:58px!important}.h-head{height:58px!important}.head-inner{height:58px!important;padding:0 10px!important}.logo a{height:58px!important;width:112px!important;background-size:84px!important}.search-form{display:none!important}.other-btn{display:none!important}.right-btn-mobile{display:flex!important}.gamemonetize-container{padding:68px 10px 24px!important}.content{width:100%!important}.game-list-grid-container{grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:8px!important}.post,.game-post,.post-item,.game-item{border-radius:10px!important}.menu-mobile{margin-right:4px!important}}
</style>

<div id="header" class="fix-top">

    <div class="head-inner">
        <div class="menu-mobile">
            <i class="material-icons-outlined material-symbols-outlined">menu</i>
            <i class="material-icons-outlined material-symbols-outlined fn-hide">close</i>
        </div>
        <div class="logo">
            <a href="{{CONFIG_SITE_URL}}/" class="hide-text">Play Best Free Online Games</a>
        </div>
        <div class="search-form">
            <form id="search-data-form" method="POST" autocomplete="off">
                <input type="text" class="txt fn-left search-input" id="Search-InArea" name="search_parameter" placeholder="@search_games@">
                <input type="submit" class="btn" value="" id="search" aria-label="search-button">
            </form>
        </div>
        <div class="other-btn">
            <div class="menu-btn new-games">
                <i class="new"></i>
                <div>
                    <div>New Games</div>
                    <div class="more"><span>&</span> more</div>
                </div>
                <i class="arrow-down"></i>
                <div class="new-games-pop-up fn-hide">
                    <a href="{{CONFIG_SITE_URL}}/new-games"><i class="material-icons-outlined material-symbols-outlined">new_releases</i>New Games</a>
                    <a href="{{CONFIG_SITE_URL}}/best-games"><i class="material-icons-outlined material-symbols-outlined">star</i>Best Games</a>
                    <a href="{{CONFIG_SITE_URL}}/featured-games"><i class="material-icons-outlined material-symbols-outlined">auto_graph</i>Featured Games</a>
                    <a href="{{CONFIG_SITE_URL}}/played-games"><i class="material-icons-outlined material-symbols-outlined">play_circle</i>Played Games</a>
                </div>
            </div>
            <a class="menu-btn" href="{{CONFIG_SITE_URL}}/blogs"><i class="blog"></i><div>Blog</div></a>
        </div>
        <div class="right-btn-mobile"><div class="search-btn"></div></div>
    </div>

</div>
<div class="h-head"></div>
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