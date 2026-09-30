<div class="_header-section admin-hh-style">
	<span class="_content-title _content-color-a"><img class="img-50" src="{{CONFIG_THEME_PATH}}/image/icon-color/shield.png"> @administration@</span>
</div>

{{CMS_UPDATE_NOTICE_BOX}}
<style>.gps-dashboard-info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:15px;margin-bottom:15px}.gps-dashboard-info-grid #gps-cms-news-box{display:{{GPS_NEWS_DISPLAY}};order:{{GPS_NEWS_ORDER}};max-height:300px;overflow-y:auto}.gps-dashboard-info-grid #gps-cms-news-box button{display:none!important}.gps-dashboard-info-grid .general-box{margin-bottom:0!important}</style>
<div class="gps-dashboard-info-grid">
{{GPS_PRO_DASHBOARD_PANEL}}
<script>function gpsCheckProUpdate(b){var s=document.getElementById('gps-update-check-status');b.disabled=true;s.textContent='Checking license and signed update manifest…';fetch('/assets/requests/admin/cms-update-check.php',{method:'POST',credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){s.textContent=d.message||(d.ok?'Update check complete.':'Update check failed.');if(d.ok&&d.update_available)setTimeout(function(){location.reload()},700)}).catch(function(){s.textContent='Could not contact the protected update API.'}).then(function(){b.disabled=false})}document.addEventListener('DOMContentLoaded',function(){var b=document.getElementById('cmsUpdateNowBtn');if(b)b.addEventListener('click',function(){if(!confirm('Install this signed CMS update now?'))return;b.disabled=true;b.textContent='Updating…';fetch('/assets/requests/admin/cms-update-run.php?action=run',{method:'POST',credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){alert(d.ok?(d.message||'CMS update complete.'):(d.error||'CMS update failed.'));if(d.ok)location.reload()}).catch(function(){alert('CMS update request failed.')}).then(function(){b.disabled=false})})})</script>
<script>document.addEventListener('DOMContentLoaded',function(){if({{GPS_AUTO_UPDATE_CHECK}})fetch('/assets/requests/admin/cms-update-check.php',{method:'POST',credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){if(d.ok&&d.update_available)location.reload()}).catch(function(){})})</script>

<div id="gps-cms-news-box"></div><script>(function(){var key='gps_cms_pro_news_v1',week=604800000,now=Date.now(),cached=null;function render(d){if(!d||!d.items||!d.items[0])return;var n=d.items[0],b=document.getElementById('gps-cms-news-box');b.innerHTML='<div class="general-box" style="margin-bottom:15px;padding:16px 20px;background:#24364f;border-left:4px solid #00bcd4;color:#fff;"><strong style="color:#00d4ff;">CMS PRO News · '+(n.version||'')+'</strong><div style="margin-top:7px;font-weight:bold">'+n.title+'</div><div style="margin-top:5px;color:#dbeafe;white-space:pre-line">'+n.body+'</div><a href="'+n.cta_url+'" target="_blank" rel="noopener" style="display:inline-block;margin-top:10px;color:#ffd34d">Read more</a><button type="button" onclick="localStorage.removeItem(\'gps_cms_pro_news_v1\');location.reload()" style="float:right">Refresh news now</button></div>'}try{cached=JSON.parse(localStorage.getItem(key)||'null')}catch(e){}if(cached&&cached.data)render(cached.data);if(!cached||!cached.checked_at||now-cached.checked_at>=week)fetch('https://api.gameportalscript.com/cms-news.php',{cache:'no-store'}).then(function(r){return r.json()}).then(function(d){if(d.items&&d.items[0]){localStorage.setItem(key,JSON.stringify({checked_at:now,data:d}));render(d)}}).catch(function(){})})();</script>
</div>

<div class="general-box" style="margin-bottom:15px;padding:20px;background:#2f3545;border-left:4px solid #00bcd4;">
	<h3 style="margin:0 0 10px 0;color:#fff;">🚀 Quick Start Autopost</h3>

	<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin:15px 0;">
		<div style="background:#252b38;padding:12px;border-radius:6px;color:#fff;">
			Active Links:<br>
			<b style="color:#2ecc71;">{{AUTOPOST_ACTIVE_LINKS}}</b>
		</div>

		<div style="background:#252b38;padding:12px;border-radius:6px;color:#fff;">
			Last Published:<br>
			<b style="color:#f1c40f;">{{AUTOPOST_LAST_PUBLISH}}</b>
		</div>

		<div style="background:#252b38;padding:12px;border-radius:6px;color:#fff;">
			Games Today:<br>
			<b style="color:#00bcd4;">{{AUTOPOST_GAMES_TODAY}}</b>
		</div>

		<div style="background:#252b38;padding:12px;border-radius:6px;color:#fff;">
			Total Games:<br>
			<b style="color:#00bcd4;">{{AUTOPOST_TOTAL_GAMES}}</b>
		</div>
		<div style="background:#252b38;padding:12px;border-radius:6px;color:#fff;">
			Total Game Plays:<br>
			<b style="color:#9b59b6;">{{ADMIN_STATS_GAMES}}</b>
		</div>
	</div>

	<p style="color:#ddd;">Enable Autopost Links, copy the generated URL, then add it to FreeCronJob.</p>

	<button type="button" id="enableAutopostCronBtn" class="btn-p btn-p1">
		Enable Autopost + Add Cron
	</button>
	<a href="https://www.freecronjob.com.es/dashboard.php" target="_blank" class="btn-p btn-p1">Open Cron Dashboard</a>
	<button type="button" onclick="document.getElementById('autopostVideoBox').style.display='block'" class="btn-p btn-p1">Watch Tutorial</button>

	<div id="autopostVideoBox" style="display:none;margin-top:20px;">
		<video controls style="width:100%;max-width:720px;border-radius:8px;background:#000;">
			<source src="/gm-content/autopost-tutorial.mp4" type="video/mp4">
		</video>
	</div>
</div>
<div class="general-box stats-box-container _yt10 _yb10">
	{{NEWS}}
</div>



