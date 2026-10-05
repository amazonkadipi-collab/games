<style>
:root{--ui-bg:#f7f8fa;--ui-card:#fff;--ui-text:#172033;--ui-muted:#687386;--ui-accent:#00a4ff;--ui-border:#e6e9ee;--ui-radius:12px;--ui-max:1280px}
html,body{width:100%;max-width:100%;overflow-x:hidden!important}
body{background:var(--ui-bg)!important;background-image:none!important;color:var(--ui-text)!important;font-family:"Open Sans",Arial,sans-serif!important}
#header{height:64px!important;background:#fff!important;box-shadow:0 1px 8px rgba(18,32,51,.07)!important;position:fixed!important;top:0!important;left:0!important;right:0!important;z-index:1000!important}
.h-head{height:64px!important}
.head-inner{height:64px!important;max-width:var(--ui-max)!important;margin:0 auto!important;padding:0 14px!important;gap:14px!important;display:flex!important;align-items:center!important}
.logo{flex:0 0 auto!important}.logo a{height:64px!important;width:132px!important;background-size:92px!important}
.search-form{flex:1 1 520px!important;max-width:620px!important;margin:0 auto!important}
.search-form form{height:42px!important;display:flex!important}
.search-form .search-input{width:100%!important;height:42px!important;border:1px solid var(--ui-border)!important;border-radius:12px 0 0 12px!important;background:#fff!important;box-shadow:none!important;padding:0 14px!important;outline:0!important;font-size:14px!important}
.search-form .search-input:focus{border-color:#b9dff1!important}
.search-form .btn{width:48px!important;height:42px!important;border:1px solid var(--ui-accent)!important;border-left:0!important;border-radius:0 12px 12px 0!important;background:var(--ui-accent)!important}
.other-btn{display:flex!important;gap:8px!important;align-items:center!important}
.menu-btn{height:40px!important;border:1px solid var(--ui-border)!important;background:#fff!important;border-radius:10px!important;padding:0 11px!important;color:var(--ui-text)!important;box-shadow:none!important}
.menu-btn:hover{background:#f4fbff!important;color:var(--ui-accent)!important;text-decoration:none!important}
.new-games-pop-up{top:48px!important;right:0!important;left:auto!important;width:190px!important}
.right-btn-mobile{display:none!important}
.menu-mobile{display:none!important}
.poki-top-nav{position:fixed;z-index:999;top:64px;left:0;right:0;height:44px;background:#fff;border-bottom:1px solid #edf0f3;overflow-x:auto;overflow-y:hidden;white-space:nowrap;scrollbar-width:none}
.poki-top-nav::-webkit-scrollbar{display:none}
.poki-top-nav-inner{max-width:var(--ui-max);height:44px;margin:0 auto;padding:0 14px;display:flex;align-items:center;gap:6px}
.poki-top-nav a{display:inline-flex;align-items:center;height:32px;padding:0 10px;border-radius:9px;color:#536074;font-size:13px;font-weight:600;text-decoration:none;flex:0 0 auto}
.poki-top-nav a:hover{background:#f3f6f9;color:#079eea}
.gamemonetize-container{max-width:var(--ui-max)!important;margin:0 auto!important;padding:126px 14px 42px!important;min-height:0!important}
.content{max-width:var(--ui-max)!important;margin:0 auto!important;padding:0!important}
#content{max-width:100%!important;margin:0 auto!important}
.game-list-grid-container{display:grid!important;grid-template-columns:repeat(6,minmax(0,1fr))!important;grid-auto-flow:row!important;grid-auto-rows:auto!important;grid-template-areas:none!important;gap:16px!important;width:100%!important;max-width:100%!important;padding:0!important}
.game-list-grid-container>*{grid-area:auto!important;min-width:0!important}
.post{position:relative!important;display:block!important;width:100%!important;aspect-ratio:auto!important;background:#fff!important;border:0!important;border-radius:12px!important;box-shadow:none!important;overflow:hidden!important}
.post .game-item{display:flex!important;flex-direction:column!important;width:100%!important;background:#fff!important;border:0!important;border-radius:12px!important;overflow:hidden!important;text-decoration:none!important;box-shadow:0 1px 0 rgba(18,32,51,.03)!important}
.post img{display:block!important;width:100%!important;height:auto!important;aspect-ratio:4/3!important;min-height:0!important;object-fit:cover!important;border-radius:12px!important}
.post .post-name{position:static!important;display:block!important;width:100%!important;height:38px!important;padding:7px 5px 0!important;margin:0!important;box-sizing:border-box!important;background:#fff!important;color:#263247!important;text-align:left!important;font-family:"Open Sans",Arial,sans-serif!important;font-size:13px!important;font-weight:600!important;line-height:1.25!important;overflow:hidden!important;text-overflow:ellipsis!important;white-space:nowrap!important;border-radius:0!important}
.post a:hover .post-name{display:block!important;background:#fff!important;color:#079eea!important;padding:7px 5px 0!important}
.post:hover{transform:none!important}
.post:hover .game-item{transform:translateY(-2px)!important}
.section-title{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:0 0 12px}
.section-title h2{font-size:20px;line-height:1.2;margin:0;color:#152033;font-weight:750}
.section-title a{font-size:13px;color:#079eea;text-decoration:none;font-weight:650}
.poki-section{margin:0 0 28px}
.poki-section + .poki-section{margin-top:24px}
.poki-game-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:16px}
.poki-game-grid .post{margin:0}
.poki-category-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}
.poki-category-grid .taglink{display:flex!important;align-items:center!important;gap:10px!important;min-width:0!important;padding:9px!important;background:#fff!important;border:1px solid #edf0f3!important;border-radius:11px!important;color:#2a3447!important;text-decoration:none!important;box-shadow:none!important}
.poki-category-grid .taglink img{width:42px!important;height:42px!important;aspect-ratio:1/1!important;object-fit:cover!important;border-radius:9px!important;flex:0 0 auto!important}
.poki-category-grid .taginfo{min-width:0!important;padding:0!important}
.poki-category-grid .taginfo .name{margin:0!important;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px;font-weight:650}
.poki-search-title{font-size:24px;font-weight:750;margin:0 0 16px;color:#152033}
.poki-page-intro{max-width:900px;margin:0 0 18px;color:#687386;font-size:14px;line-height:1.6}
.poki-seo-copy{margin:28px 0 0;padding:20px;background:#fff;border:1px solid #edf0f3;border-radius:14px;color:#5f6b7d;line-height:1.65;font-size:14px}
.home-search-container,#search-left{display:none!important}
.hide-text{position:absolute!important;left:-10000px!important;width:1px!important;height:1px!important;overflow:hidden!important}
.bottomtext{max-width:var(--ui-max)!important;margin:24px auto 50px!important;background:#fff!important;border:1px solid #edf0f3!important;border-radius:14px!important;box-shadow:none!important}
@media (min-width:1100px) and (max-width:1199px){.game-list-grid-container,.poki-game-grid{grid-template-columns:repeat(5,minmax(0,1fr))!important}.poki-category-grid{grid-template-columns:repeat(5,minmax(0,1fr))}}
@media (min-width:900px) and (max-width:1099px){.game-list-grid-container,.poki-game-grid{grid-template-columns:repeat(4,minmax(0,1fr))!important}.poki-category-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
@media (min-width:600px) and (max-width:899px){.game-list-grid-container,.poki-game-grid{grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:12px!important}.poki-category-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media (max-width:599px){
#header{height:58px!important}.h-head{height:58px!important}.head-inner{height:58px!important;padding:0 10px!important;gap:8px!important}
.logo a{height:58px!important;width:106px!important;background-size:80px!important}.search-form{display:none!important}.other-btn{display:none!important}.menu-mobile{display:flex!important;align-items:center!important;font-size:22px!important;color:#4d5a70!important;cursor:pointer!important;order:3!important;margin-left:auto!important}.right-btn-mobile{display:flex!important;order:2!important}.right-btn-mobile .search-btn{width:38px;height:38px;border-radius:10px;background:#f2f5f8}
.poki-top-nav{top:58px;height:40px}.poki-top-nav-inner{height:40px;padding:0 10px}.poki-top-nav a{height:30px;font-size:12px;padding:0 9px}
.gamemonetize-container{padding:108px 10px 26px!important}
.game-list-grid-container,.poki-game-grid{grid-template-columns:repeat(3,minmax(0,1fr))!important;gap:8px!important}
.post .game-item{border-radius:9px!important}.post img{border-radius:9px!important}.post .post-name{height:34px;font-size:11px;padding:6px 3px 0!important}
.poki-category-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.poki-category-grid .taglink{padding:8px!important}.poki-category-grid .taglink img{width:36px!important;height:36px!important}
.section-title h2{font-size:18px}.poki-section{margin-bottom:22px}
}
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
            <form id="search-data-form" method="GET" action="{{CONFIG_SITE_URL}}/search" autocomplete="off">
                <input type="text" class="txt fn-left search-input" id="Search-InArea" name="search_parameter" placeholder="@search_games@" aria-label="@search_games@">
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
                    <a href="{{CONFIG_SITE_URL}}/best-games"><i class="material-icons-outlined material-symbols-outlined">star</i>Best Games</a>
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