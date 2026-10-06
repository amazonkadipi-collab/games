<link rel="stylesheet" href="{{CONFIG_SITE_URL}}/templates/poki-like/css/game-redesign.css?ver=20261005" media="all">
<script language="javascript">
    var PageType = "";
    var ids = "";
</script>

{{PLAY_GAME_SCHEMA}}
<div id="content" class="fn-clear poki-public-page poki-game-page">
    {{PLAY_GAME_BREADCRUMB}}
    <div class="play-game-list-grid-container poki-game-player-section">
    <div class="game-container game-col">
        <div class="game-info">
        <div id="loader_container">
            <div id="preloader_box"></div>
        </div>
        <div id="gameDiv">
            <div id="ava-game_container" class="game-box" data-norate="1">

                <div id="gamePlay-content" oncontextmenu="return false" style="position: relative;">
                    <img class="gamePlay-bg" src="{{PLAY_GAME_IMAGE}}" alt="{{PLAY_GAME_NAME}}" fetchpriority="high" decoding="async">
                    <div class="gamePlay-icon-btn">
                        <div class="gamePlay-icon" style="background-image: url({{PLAY_GAME_IMAGE}});background-size: 160px;background-position-x: 50%;background-position-y: 50%;"></div>
                        <div class="gamePlay-title">{{PLAY_GAME_NAME}}</div>
                        <button type="button" class="gamePlay-button">Play Now!</button>
                        <button type="button" class="gamePlay-button gamePlay-button-mobile">
                            <span class="play-icon" aria-hidden="true"><i class="fa fa-play"></i></span>
                            <span class="play-text">Play Now!</span>
                        </button>
                    </div>
                </div><div id="game-preloading"></div>
                <div id="game-preloader"></div>
                <div id="game-box"></div>

                <div id="adsContainer">
                    <div id="adContainer"></div>
                    <video id="videoElement"></video>
                </div>

                <div class="close-fullscreen" onclick="location.reload();return false;">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    <span class="pg-fullscreen-back">Back</span>
                </div>
            </div>
        </div>
    </div>
    <div class="game-zoom poki-game-meta">
        <!-- <a href="/" class="game-zoom-logo hide-text">Play Best Online Games</a> -->
        <div class="game-zoom-info">
            <div class="game-image" style="background-image: url({{PLAY_GAME_IMAGE}});"></div>
            <p>{{PLAY_GAME_NAME}}</p>
        </div>
        

<div class="pg-game-quick-meta" aria-label="Game information">
    <span><i class="fa-solid fa-gamepad" aria-hidden="true"></i><strong>{{PLAY_GAME_PLAYS}}</strong> plays</span>
    <span><i class="fa-solid fa-layer-group" aria-hidden="true"></i>{{PLAY_GAME_CATEGORY_NAME}}</span>
</div>
        <div>
            <div class="game-zoom-btn">
                <a href="#" id="gameFull" title="Play game fullscreen" onclick="GameFullscreen();return false;"></a>
            </div>
        </div>
        <!-- <div>
            <a href="#" id="gameReplay" title="Replay this game" onclick="ReplayGame();return false;"></a>
        </div> -->
    </div>
</div>


<div class="game-name-mobile">
    {{PLAY_GAME_NAME}}
</div>
<div id="game-bottom" class="bgs poki-game-content">
        <h1 class="pl game-title" style="font-size: 25px !important;">{{PLAY_GAME_NAME}}</h1>
        <div class="game-tags">
            {{PLAY_GAME_TAGS}}
        </div>
        <div class="pg-game-details" aria-label="Game details">
            <a href="{{CONFIG_SITE_URL}}/category/{{PLAY_GAME_CATEGORY_URL}}"><strong>Category:</strong> {{PLAY_GAME_CATEGORY_NAME}}</a>
            <span><strong>Rating:</strong> {{PLAY_GAME_RATING}}</span>
            <span><strong>Added:</strong> {{PLAY_GAME_DATE}}</span>
        </div>
    </div>
    

<section class="pg-game-info-grid" aria-label="Game information">
    <div class="pg-info-card">
        <h2>About this game</h2>
        <div class="pg-info-copy">{{PLAY_GAME_DESC}}</div>
    </div>
    <div class="pg-info-card pg-controls-card">
        <h2>How to play</h2>
        {{PLAY_GAME_CONTROLS_BLOCK}}
    </div>
</section>

<section class="pg-related-section" aria-labelledby="pg-related-heading">
    <div class="pg-section-heading">
        <div><span class="pg-section-kicker">KEEP PLAYING</span><h2 id="pg-related-heading">More Games to Play</h2></div>
        <a href="{{CONFIG_SITE_URL}}/category/{{PLAY_GAME_CATEGORY_URL}}">More {{PLAY_GAME_CATEGORY_NAME}}</a>
    </div>
    <div class="pg-related-grid">{{PLAY_RELATED_GAMES}}</div>
</section>

</div>
</div>
</div>
</div>
</div>

<!-- Legacy Flash/U3D polling removed: modern embeds load on user interaction. -->

{{FOOTER_CONTENT}}

<script type="text/javascript">
    var PreGameAdURL = "{{ADS_VIDEO}}";

    window.setTimeout(function() {
        __upGame_rx8({{PLAY_GAME_ID}})
    }, 2000);
    var descriptionURL = "{{DESCRIPTION_URL}}";
    var iframe = '{{PLAY_GAME_EMBED}}';

    function SkipAdAndShowGame() {
        $("#adsContainer").hide();
        $("#gamePlay-content").hide();
        $("#game-box").html(iframe);
        $("#game-box iframe, #game-box embed, #game-box object, #game-box canvas").css({
            width: "100%",
            height: "100%",
            display: "block",
            border: "0"
        });
        if (window.innerWidth < 768) {
            $("html, body").css({
                width: "100%",
                maxWidth: "100%",
                overflowX: "hidden"
            });
            $(".close-fullscreen").hide();
        }
    }

    $(document).ready(function() {
        // Poki-style player-first flow: keep the poster visible until Play.
        $("#game-box").empty();
        $(".gamePlay-button").off("click.gameStart").on("click.gameStart", function(e) {
            e.preventDefault();
            SkipAdAndShowGame();
        });
    });

    window.addEventListener('load', function () {
        $('.ad300').eq(0).show();
        $('.adsmall').eq(0).show();
    }, { once: true });
</script>

{{IMA_SDK}}

<!-- <div id="BackTop"></div> -->
</div>
<script>
var cat = "{{CATEGORYID}}";
</script>



