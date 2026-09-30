<div id="header" class="fix-top">

    <div class="head-inner fn-clear">
        <h1 class="logo">
            <a href="{{CONFIG_SITE_URL}}/" class="hide-text">Play Best Free Online Games</a>
        </h1>

        <div class="menu">
            <ul class="menu-ul fn-clear">

                <li class="submenu">
                    <a href="{{CONFIG_SITE_URL}}/categories" class=""><i class="fa-solid fa-gamepad" aria-hidden="true"></i><span>Category</span></a>
                    <div class="menupopup fn-hide">
                        <ul class="cate-list">
                            {{CATEGORIES_LIST_2}}
                            <li><a href="{{CONFIG_SITE_URL}}/categories" class="">More Categories &gt;&gt;</a></li>
                        </ul>
                    </div>
                </li>

                <li>
                    <a class="tips" href="{{CONFIG_SITE_URL}}/new-games"><i class="fa-solid fa-bolt" aria-hidden="true"></i><span>New</span></a>
                    <div class="tooltip fn-hide">
                        <span class="arrow"></span>
                        <div class="tip-txt">New Games</div>
                    </div>
                </li>
                <li>
                    <a class="tips" href="{{CONFIG_SITE_URL}}/best-games"><i class="fa-solid fa-crown" aria-hidden="true"></i><span>Best</span></a>
                    <div class="tooltip fn-hide">
                        <span class="arrow"></span>
                        <div class="tip-txt">Best Games</div>
                    </div>
                </li>
                <li>
                    <a href="{{CONFIG_SITE_URL}}/featured-games"><i class="fa-solid fa-star" aria-hidden="true"></i><span>Featured</span></a>
                    <div class="tooltip featip fn-hide">
                        <span class="arrow"></span>
                        <div class="tip-txt">Featured Games</div>
                    </div>
                </li>

                <li>
                    <a href="{{CONFIG_SITE_URL}}/played-games"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i><span>Played</span></a>
                    <div class="tooltip featip fn-hide">
                        <span class="arrow"></span>
                        <div class="tip-txt">Played Games</div>
                    </div>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span>Search</span></a>
                    <div class="menupopup fn-hide">
                        <div class="search-form">
                            <form id="search-data-form" method="POST" autocomplete="off">
                                <input type="text" class="txt fn-left search-input" id="Search-InArea" name="search_parameter" type="text" placeholder="@search_games@">
                                <input type="submit" class="btn" value="GO" id="search">
                            </form>
                        </div>
                    </div>
                </li>
                <li>
                    <a href="{{CONFIG_SITE_URL}}/blogs"><i class="fa-solid fa-newspaper" aria-hidden="true"></i><span>Blog</span></a>
                    <div class="tooltip featip fn-hide">
                        <span class="arrow"></span>
                        <div class="tip-txt">Blog Post</div>
                    </div>
                </li>

            </ul>
        </div>

        <button type="button" class="kizi-mobile-menu-toggle" aria-expanded="false" aria-controls="kizi-mobile-menu" aria-label="Open menu">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>
    </div>

</div>

<nav id="kizi-mobile-menu" class="kizi-mobile-menu" aria-label="Mobile navigation" hidden>
    <a href="{{CONFIG_SITE_URL}}/categories"><i class="fa-solid fa-gamepad" aria-hidden="true"></i><span>Categories</span></a>
    <a href="{{CONFIG_SITE_URL}}/new-games"><i class="fa-solid fa-bolt" aria-hidden="true"></i><span>New Games</span></a>
    <a href="{{CONFIG_SITE_URL}}/best-games"><i class="fa-solid fa-crown" aria-hidden="true"></i><span>Best Games</span></a>
    <a href="{{CONFIG_SITE_URL}}/featured-games"><i class="fa-solid fa-star" aria-hidden="true"></i><span>Featured</span></a>
    <a href="{{CONFIG_SITE_URL}}/played-games"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i><span>Played</span></a>
    <a href="{{CONFIG_SITE_URL}}/blogs"><i class="fa-solid fa-newspaper" aria-hidden="true"></i><span>Blog</span></a>
    <a href="{{CONFIG_SITE_URL}}/search"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span>Search</span></a>
    <div class="gps-kizi-mobile-tools" aria-label="Language and theme"></div>
</nav>

<div class="h-head"></div>

<script>
(function () {
    var toggle = document.querySelector('.kizi-mobile-menu-toggle');
    var menu = document.getElementById('kizi-mobile-menu');
    var media = window.matchMedia('(max-width: 960px)');

    function setMenu(open) {
        if (!toggle || !menu) { return; }
        menu.hidden = !open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        var icon = toggle.querySelector('i');
        if (icon) {
            icon.className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        }
    }

    function placeDisplayTools() {
        var target = document.querySelector(media.matches ? '.gps-kizi-mobile-tools' : '.gps-kizi-footer-tools');
        if (!target) { return; }
        var theme = document.querySelector('.gps-cvp-theme-menu');
        var language = document.getElementById('gps-translate-root');
        [theme, language].forEach(function (tool) {
            if (tool && tool.parentNode !== target) { target.appendChild(tool); }
        });
        var languageLabel = document.querySelector('#gps-translate-toggle span:last-child');
        if (languageLabel) { languageLabel.textContent = 'Language'; }
    }

    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            setMenu(toggle.getAttribute('aria-expanded') !== 'true');
        });
        document.addEventListener('click', function (event) {
            if (!menu.hidden && !menu.contains(event.target) && !toggle.contains(event.target)) { setMenu(false); }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') { setMenu(false); }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', placeDisplayTools);
    } else {
        placeDisplayTools();
    }
    window.addEventListener('load', placeDisplayTools);
    window.addEventListener('resize', placeDisplayTools);
})();
</script>
