<link rel="stylesheet" href="{{CONFIG_SITE_URL}}/templates/poki-like/css/game-redesign.css?ver=20261006" media="all">
<script language="javascript">
    var PageType = "";
    var ids = "";
</script>

{{PLAY_GAME_SCHEMA}}
<div id="content" class="fn-clear poki-public-page poki-game-page">
    {{PLAY_GAME_BREADCRUMB}}

    <!-- PLAYER FIRST: the game is the first primary action on every device. -->
    <section class="pg-player-first" aria-label="Game player">
        <div class="game-container game-col">
            <div class="game-info">
                <div id="loader_container"><div id="preloader_box"></div></div>
                <div id="gameDiv">
                    <div id="ava-game_container" class="game-box" data-norate="1">
                        <div id="gamePlay-content" oncontextmenu="return false" style="position:relative;">
                            <img class="gamePlay-bg" src="{{PLAY_GAME_IMAGE}}" alt="{{PLAY_GAME_NAME}}" fetchpriority="high" decoding="async">
                            <div class="gamePlay-icon-btn" role="button" tabindex="0" aria-label="Play {{PLAY_GAME_NAME}}">
                                <div class="gamePlay-icon" style="background-image:url({{PLAY_GAME_IMAGE}});background-size:cover;background-position:center;"></div>
                                <div class="gamePlay-title">{{PLAY_GAME_NAME}}</div>
                                <button type="button" class="gamePlay-button" aria-label="Play {{PLAY_GAME_NAME}}">Play</button>
                            </div>
                        </div>
                        <div id="game-preloading"></div>
                        <div id="game-preloader"></div>
                        <div id="game-box"></div>
                        <div id="adsContainer"><div id="adContainer"></div><video id="videoElement"></video></div>
                        <div class="close-fullscreen" onclick="location.reload();return false;">
                            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                            <span class="pg-fullscreen-back">Back</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- IDENTITY + REAL METADATA -->
    <section class="pg-game-identity" aria-labelledby="game-title">
        <h1 id="game-title">{{PLAY_GAME_NAME}}</h1>
        <div class="pg-game-quick-meta" aria-label="Game information">
            {{PLAY_GAME_PLAYS_META}}
            <a href="{{CONFIG_SITE_URL}}/category/{{PLAY_GAME_CATEGORY_URL}}">
                <i class="fa-solid fa-layer-group" aria-hidden="true"></i>{{PLAY_GAME_CATEGORY_NAME}}
            </a>
        </div>
    </section>

    {{PLAY_GAME_ABOUT_BLOCK}}
    {{PLAY_GAME_CONTROLS_BLOCK}}

    <!-- REAL RELATED GAMES -->
    <section class="pg-related-section" aria-labelledby="pg-related-heading">
        <div class="pg-section-heading">
            <div><span class="pg-section-kicker">KEEP PLAYING</span><h2 id="pg-related-heading">More Games to Play</h2></div>
            <a href="{{CONFIG_SITE_URL}}/category/{{PLAY_GAME_CATEGORY_URL}}">More {{PLAY_GAME_CATEGORY_NAME}}</a>
        </div>
        <div class="pg-related-grid">{{PLAY_RELATED_GAMES}}</div>
    </section>

    {{PLAY_MORE_CATEGORY_SECTION}}
</div>

{{FOOTER_CONTENT}}

<script type="text/javascript">
    var PreGameAdURL = "{{ADS_VIDEO}}";
    window.setTimeout(function() { __upGame_rx8({{PLAY_GAME_ID}}); }, 2000);
    var descriptionURL = "{{DESCRIPTION_URL}}";
    var iframe = '{{PLAY_GAME_EMBED}}';

    var gameHasStarted = false;

    function SkipAdAndShowGame() {
        var shell = document.getElementById("ava-game_container");
        var gameBox = document.getElementById("game-box");
        var poster = document.getElementById("gamePlay-content");
        var ads = document.getElementById("adsContainer");
        if (!gameBox || !shell || gameHasStarted) return;

        gameHasStarted = true;
        if (ads) ads.style.display = "none";
        if (poster) poster.style.display = "none";

        gameBox.innerHTML = iframe;

        var playable = gameBox.querySelector("iframe, embed, object, canvas");
        if (!playable) {
            gameHasStarted = false;
            if (poster) poster.style.display = "";
            return;
        }

        playable.style.width = "100%";
        playable.style.height = "100%";
        playable.style.display = "block";
        playable.style.border = "0";
        playable.style.maxWidth = "100%";
        playable.style.maxHeight = "100%";
        playable.style.position = "absolute";
        playable.style.inset = "0";
        playable.style.zIndex = "5";
        playable.style.pointerEvents = "auto";
        playable.style.touchAction = "auto";
        playable.setAttribute("allow", "autoplay; fullscreen; gamepad; accelerometer; gyroscope; picture-in-picture");
        playable.setAttribute("allowfullscreen", "");
        if (playable.tagName === "IFRAME") {
            playable.setAttribute("scrolling", "no");
            playable.setAttribute("title", document.getElementById("game-title")?.textContent?.trim() || "Game");
        }

        shell.classList.add("pg-game-playing");
        document.documentElement.style.maxWidth = "100%";
        document.body.style.maxWidth = "100%";
        document.documentElement.style.overflowX = "hidden";
        document.body.style.overflowX = "hidden";
    }

    function startGame(e) {
        if (e) e.stopPropagation();
        SkipAdAndShowGame();
    }

    document.addEventListener("DOMContentLoaded", function() {
        var playButton = document.querySelector(".gamePlay-button");
        var playSurface = document.querySelector(".gamePlay-icon-btn");
        if (!playButton || !playSurface) return;

        /* One canonical mobile/desktop path:
           pointerup gives touch devices a reliable activation without
           cancelling the browser's click synthesis or iframe focus. */
        if (window.PointerEvent) {
            playButton.addEventListener("pointerup", startGame, {passive:true});
        } else {
            playButton.addEventListener("touchend", startGame, {passive:true});
        }

        playButton.addEventListener("click", startGame, {passive:true});

        playSurface.addEventListener("keydown", function(e) {
            if (e.key === "Enter" || e.key === " ") startGame(e);
        });
    });
</script>

{{IMA_SDK}}

<script>
var cat = "{{CATEGORYID}}";
</script>
