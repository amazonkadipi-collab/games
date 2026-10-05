<script language="javascript">
    var PageType = "";
    var ids = "";
</script>

<div id="content" class="fn-clear poki-public-page poki-game-page">
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
                        <div class="gamePlay-button gamePlay-button-mobile">
                            <div class="play-icon">
                                <i class="fa fa-play" aria-hidden="true"></i>
                            </div>
                            <div class="play-text">Play Now!</div>
                        </div>
                    </div>
                </div>
                <div id="pre-count">
                    <font lib="game-loading">Game loading..</font>
                    <div id="pre-count-num">25</div>
                </div>
                <div id="game-preloading"></div>
                <div id="game-preloader"></div>
                <div id="game-box">
                </div>

                <div id="adsContainer">
                    <div id="adContainer"></div>
                    <video id="videoElement"></video>
                </div>

                <div class="close-fullscreen" onclick="location.reload();return false;">
                    <i class="fa-solid fa-chevron-left"></i>
                    <img src="/templates/poki-like/image/poki-circle-logo.png" class="poki-circle-logo" alt="poki-circle-logo">
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
        

<div class="flex items-center w-full bg-white p-3 rounded-b-xl">
    
 

    <div class="flex items-center gap-3 ml-auto">
        
        <div class="play-info-blue">
            <i class="fa-solid fa-gamepad"></i>
            <span>{{PLAY_GAME_PLAYS}} plays</span>
        </div>

        <button class="vote-btn" data-type="like" data-id="{{PLAY_GAME_ID}}">
            <i class="fa-regular fa-thumbs-up"></i>
            <span>{{PLAY_GAME_LIKES}}</span>
        </button>

        <button class="vote-btn" data-type="dislike" data-id="{{PLAY_GAME_ID}}">
            <i class="fa-regular fa-thumbs-down"></i>
            <span>{{PLAY_GAME_DISLIKES}}</span>
        </button>

        <button class="vote-btn" data-type="favorite" data-id="{{PLAY_GAME_ID}}">
            <i class="fa-regular fa-heart"></i>
        </button>

         
    </div>
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
{{PLAY_SIDEBAR_WIDGETS}}

<div class="game-name-mobile">
    {{PLAY_GAME_NAME}}
</div>
{{TAGS_LIST_GRID}}
<div id="game-bottom" class="bgs poki-game-content">
        <h1 class="pl game-title" style="font-size: 25px !important;">{{PLAY_GAME_NAME}}</h1>
        <div id="play-game-desc" class="d-text" style="margin-top:15px;font-size:14px;position:relative;">
            {{PLAY_GAME_DESC}}
        </div>
        <div class="game-tags">
            {{PLAY_GAME_TAGS}}
        </div>
    </div>
    

<div class="tags-walkthrough-container">
    <div class="game-walkthrough bgs fn-clear">
        <p>Play {{PLAY_GAME_NAME}} {{PLAY_GAME_WALKTHROUGH}}</p>
        {{PLAY_GAME_VIDEO_BLOCK}}
    </div>
</div>
</div>

</div>
</div>

<!-- Legacy Flash/U3D polling removed: modern embeds load on user interaction. -->

{{FOOTER_CONTENT}}

<script type="text/javascript">
    var PreGameAdURL = "{{ADS_VIDEO}}";

    function getcookie(name) {
        var cookie_start = document.cookie.indexOf(name);
        var cookie_end = document.cookie.indexOf(";", cookie_start);
        return cookie_start == -1 ? '' : unescape(document.cookie.substring(cookie_start + name.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length)));
    }

    function setcookie(cookieName, cookieValue, seconds, path, domain, secure) {
        var expires = new Date();
        expires.setTime(expires.getTime() + seconds);
        document.cookie = escape(cookieName) + '=' + escape(cookieValue) +
            (expires ? '; expires=' + expires.toGMTString() : '') +
            (path ? '; path=' + path : '/') +
            (domain ? '; domain=' + domain : '') +
            (secure ? '; secure' : '');
    }

    function ClearPlayedGames() {
        setcookie("lastplayedgames", "", -360000, "/");
        return false;
    }

    function PlayedGames(game_id) {
        var playedgames = getcookie("playedgames");
        if (playedgames.indexOf("," + game_id + ",") > -1) {
            playedgames = playedgames.replace("," + game_id + ",", '');
        } else {
            if (playedgames == "" || playedgames == ",") {
                playedgames = "," + game_id + ",";
            } else {
                playedgames = "," + game_id + "," + playedgames;
            }
        }
        setcookie("playedgames", playedgames, 25920000000, "/");
    }
    window.addEventListener('load', function () {
        PlayedGames({{PLAY_GAME_ID}});
    }, { once: true });

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

<script src="{{CONFIG_THEME_PATH}}/js/libs/jquery.show-more.js" defer></script>
<script>
	if (window.innerWidth <= 768) {
		$('#play-game-desc').showMore({
			minheight: 145,
			maxWidth: "100%",
		});
	}
var cat = "{{CATEGORYID}}";
</script>

 <style>
/* FIX: keep title left, buttons right */
.flex.items-center.w-full.bg-white.p-3.rounded-b-xl {
    display: flex;
    align-items: center;
}

/* THIS is the important part */
.flex.items-center.gap-3.ml-auto {
    margin-left: auto !important; /* pushes buttons to far right */
    display: flex;
    align-items: center;
    gap: 12px;
}

/* make sure no parent is centering */
.game-zoom-info {
    margin-right: auto;
}

/* optional spacing */
.play-info-blue {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #4da3ff;
    font-weight: 700;
    font-size: 12px;
}

.vote-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    border: 1px solid #f0f2f5;
    border-radius: 14px;
    padding: 8px 14px;
    color: #4da3ff;
    cursor: pointer;
    font-weight: 700;
}
.vote-btn i {
    font-size: 18px;
}
.vote-btn[data-type="favorite"] {
    padding: 10px 15px;
}
</style>


<script>
function formatNumber(num) {
    num = parseInt(num, 10);
    if (isNaN(num)) return '0';
    if (num >= 1000000000) return (num / 1000000000).toFixed(1).replace('.0', '') + 'B';
    if (num >= 1000000) return (num / 1000000).toFixed(1).replace('.0', '') + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1).replace('.0', '') + 'K';
    return num.toString();
}

function showPopup(message) {
    const popup = document.createElement('div');
    popup.className = 'vote-popup';
    popup.textContent = message;
    document.body.appendChild(popup);

    setTimeout(function () {
        popup.classList.add('show');
    }, 10);

    setTimeout(function () {
        popup.classList.remove('show');
        setTimeout(function () {
            popup.remove();
        }, 250);
    }, 2000);
}

function getBtn(type) {
    return document.querySelector('.vote-btn[data-type="' + type + '"]');
}

function setBtnCount(type, value) {
    var btn = getBtn(type);
    if (!btn) return;

    var span = btn.querySelector('span');
    if (!span) {
        span = document.createElement('span');
        btn.appendChild(span);
    }

    span.innerText = formatNumber(value || 0);
}

function setCounts(data) {
    var playCount = document.getElementById('play-count');
    if (playCount) {
        playCount.innerText = formatNumber(data.plays || 0) + ' plays';
    }

    setBtnCount('like', data.likes || 0);
    setBtnCount('dislike', data.dislikes || 0);

    if (typeof data.favorites !== 'undefined') {
        setBtnCount('favorite', data.favorites || 0);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var firstBtn = document.querySelector('.vote-btn');
    if (!firstBtn) return;

    var gameId = firstBtn.getAttribute('data-id');

    fetch('/ajax_vote.php?action=get_counts&game_id=' + encodeURIComponent(gameId))
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.status === 'ok') {
                setCounts(data);
            }
        })
        .catch(function (error) {
            console.log('COUNT LOAD ERROR:', error);
        });

    document.querySelectorAll('.vote-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            var button = this;
            var type = button.getAttribute('data-type');
            var gameId = button.getAttribute('data-id');

            fetch('/ajax_vote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'type=' + encodeURIComponent(type) + '&game_id=' + encodeURIComponent(gameId)
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.status === 'ok') {
                    setCounts(data);
                    button.style.opacity = '0.5';
                    button.style.pointerEvents = 'none';
                    showPopup(data.message || 'Saved');
                } else if (data.status === 'exists') {
                    button.style.opacity = '0.5';
                    button.style.pointerEvents = 'none';
                    showPopup(data.message || 'Already voted');
                } else {
                    showPopup(data.message || 'Vote error');
                }
            })
            .catch(function (error) {
                console.log('FETCH ERROR:', error);
                showPopup('Request failed');
            });
        });
    });
});
</script>
