// PRO installer integration, distributed and versioned through Other Fixes.
(function () {
    'use strict';
    if (window.gpsDependencyInstaller) return;
    window.gpsDependencyInstaller = true;
    var running = false;
    document.addEventListener('click', async function (event) {
        var button = event.target.closest('#gps-install-all-pro-features');
        if (!button) return;
        var label = document.getElementById('gps-pro-page-license-label');
        if (!label || label.textContent.trim() !== 'PRO Active') return;
        event.preventDefault();
        event.stopImmediatePropagation();
        if (running) return;
        running = true;
        button.disabled = true;
        var status = document.getElementById('gps-install-all-status');
        var seen = new Set(), completed = 0;
        try {
            for (var step = 0; step < 30; step++) {
                var response = await fetch('/index.php?p=admin&section=pro', {credentials: 'same-origin', cache: 'no-store'});
                if (!response.ok) throw new Error('Could not refresh the installation list.');
                var page = new DOMParser().parseFromString(await response.text(), 'text/html');
                var freshLabel = page.getElementById('gps-pro-page-license-label');
                if (!freshLabel || freshLabel.textContent.trim() !== 'PRO Active') throw new Error('Administrator login and active PRO are required.');
                var pending = Array.from(page.querySelectorAll('.gps-install-pro-feature:not(:disabled)'));
                var next = pending.find(function (item) { return item.dataset.feature === 'other_fixes'; }) || pending[0];
                if (!next) {
                    button.textContent = 'All features installed';
                    status.textContent = 'Installation completed. Reloading…';
                    window.location.reload();
                    return;
                }
                var feature = next.dataset.feature;
                if (['menu_design', 'professional_showcase', 'language'].includes(feature)) throw new Error('Bundled modules still require an Other Fixes update. No standalone package was requested.');
                var signature = feature + ':' + next.textContent.trim();
                if (seen.has(signature)) throw new Error('Installation made no progress for ' + feature + '. Please retry after checking its status.');
                seen.add(signature);
                button.textContent = 'Installing… (' + completed + ' completed)';
                status.style.color = '#b9c7dd';
                status.textContent = 'Installing ' + feature.replace(/_/g, ' ') + '…';
                var body = new URLSearchParams({feature: feature});
                response = await fetch('/assets/requests/admin/pro-feature-install.php', {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'}, body: body.toString()});
                var result = await response.json();
                if (!response.ok || !result.ok) throw new Error(result.message || 'Installation failed.');
                completed++;
            }
            throw new Error('Installation safety limit reached.');
        } catch (error) {
            button.textContent = 'Continue installation';
            status.textContent = 'Stopped: ' + error.message;
            status.style.color = '#ff9b9b';
        } finally {
            running = false;
            button.disabled = false;
        }
    }, true);
})();
