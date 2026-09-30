$(function () {

    function gpsLicenseDebug(message) {
        $('#gps-license-debug').text(message || '').toggle(!!message);
    }

    function gpsWaitLabel(seconds) {
        var minutes = Math.max(1, Math.ceil(Number(seconds || 0) / 60));
        var hours = Math.floor(minutes / 60);
        var rest = minutes % 60;
        return hours > 0 ? hours + 'h ' + rest + 'm' : minutes + 'm';
    }

    function gpsLicenseRefreshCooldown(seconds) {
        var button = $('#gps-license-refresh');
        seconds = Math.max(0, Number(seconds || 0));
        button.prop('disabled', seconds > 0);
        button.html(seconds > 0
            ? '<i class="fa fa-clock-o"></i> Refresh in ' + gpsWaitLabel(seconds)
            : '<i class="fa fa-refresh"></i> Refresh license');
    }

    var gpsLicenseActivationTimer = 0;
    function gpsLicenseActivationCooldown(seconds) {
        var button = $('#gps-license-activate');
        seconds = Math.max(0, Number(seconds || 0));
        if (!button.length || seconds <= 0) { return false; }
        window.clearInterval(gpsLicenseActivationTimer);
        button.data('gps-cooldown', true).prop('disabled', true);
        function draw() {
            if (seconds <= 0) {
                window.clearInterval(gpsLicenseActivationTimer);
                button.removeData('gps-cooldown').prop('disabled', false).text('Activate PRO');
                return;
            }
            button.text('Try in ' + gpsWaitLabel(seconds));
        }
        draw();
        gpsLicenseActivationTimer = window.setInterval(function () { seconds -= 60; draw(); }, 60000);
        return true;
    }

    function gpsHtml(value) {
        return $('<div>').text(String(value == null ? '' : value)).html();
    }

    function gpsLoadGoogleLoginControls() {
        var card = $('#gps-google-login-card');
        if (!card.length || card.data('gps-loading')) { return; }
        card.data('gps-loading', true);
        $.ajax({url: Ajaxrequest() + '?t=admin&a=google-login-config', dataType: 'json', timeout: 15000})
            .done(function (config) {
                if (!config.ok) { return; }
                card.data('gps-admin-emails', config.admin_emails || '');
                card.css({padding: 0, overflow: 'hidden'}).html('<form id="gps-google-login-form">' +
                    '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;">' +
                    '<label style="display:flex;align-items:center;gap:10px;color:#fff;font-weight:700;cursor:pointer;"><input type="checkbox" name="google_oauth_enabled" value="1" style="position:static;"' + (config.enabled ? ' checked' : '') + '> <span>Login with Google<small class="gps-google-status" style="display:block;color:#9fb0ca;font-weight:400;">' + (config.enabled ? 'Enabled' : 'Disabled') + '</small></span></label>' +
                    '<button type="button" class="gps-google-config-toggle" style="padding:7px 11px;background:transparent;border:1px solid #ffd34d;border-radius:6px;color:#ffd34d;cursor:pointer;">Configure</button></div>' +
                    '<div class="gps-google-config-fields" style="display:none;padding:0 14px 14px;border-top:1px solid rgba(255,255,255,.1);">' +
                    '<div style="margin:12px 0;color:#a9b8d0;">Authorized redirect URI:<br><code style="color:#70ff7a;word-break:break-all;">' + gpsHtml(config.callback_url) + '</code></div>' +
					'<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;"><button type="button" class="gps-google-copy-callback" data-callback="' + gpsHtml(config.callback_url) + '" style="padding:8px 12px;background:#344054;border:1px solid #667085;border-radius:6px;color:#fff;cursor:pointer;">Copy callback URL</button><a href="https://gameportalscript.com/blog/how-to-set-up-google-login-gameportalscript" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:8px 12px;background:#6f42c1;border:1px solid #8b5cf6;border-radius:6px;color:#fff;font-weight:700;text-decoration:none;">View setup tutorial &#8599;</a></div>' +
                    '' +
                    '<label style="display:block;color:#cbd5e1;margin-bottom:5px;">Google Client ID</label><input type="text" name="google_oauth_client_id" value="' + gpsHtml(config.client_id) + '" style="position:static!important;width:100%;box-sizing:border-box;padding:9px;margin-bottom:10px;background:#252b38;border:1px solid rgba(255,255,255,.16);border-radius:6px;color:#fff;">' +
                    '<label style="display:block;color:#cbd5e1;margin-bottom:5px;">Google Client Secret</label><input type="password" name="google_oauth_client_secret" value="" placeholder="' + (config.has_secret ? 'Saved - leave blank to keep it' : 'Enter Google Client Secret') + '" autocomplete="new-password" style="position:static!important;width:100%;box-sizing:border-box;padding:9px;margin-bottom:10px;background:#252b38;border:1px solid rgba(255,255,255,.16);border-radius:6px;color:#fff;">' +
                    '<button type="submit" class="btn-p btn-p1" style="padding:8px 14px;">Save</button><div id="gps-google-login-message" style="margin-top:8px;color:#cbd5e1;"></div></div></form>' +
					'');
            })
            .always(function () {
                var fields = card.find('.gps-google-config-fields');
                if (!fields.find('[name="google_oauth_admin_emails"]').length) {
                    fields.find('button[type="submit"]').before('<label style="display:block;color:#cbd5e1;margin-bottom:5px;">Allowed Admin Google emails</label><textarea name="google_oauth_admin_emails" rows="3" placeholder="owner@gmail.com&#10;friend@gmail.com" style="position:static!important;width:100%;box-sizing:border-box;padding:9px;margin-bottom:5px;background:#252b38;border:1px solid rgba(255,255,255,.16);border-radius:6px;color:#fff;resize:vertical;">' + gpsHtml(card.data('gps-admin-emails') || '') + '</textarea><small style="display:block;margin-bottom:10px;color:#9fb0ca;">One email per line or separated by commas. Only these verified Google accounts open CMS Admin; all other Google users remain players.</small>');
                }
                if (!card.find('.gps-google-admin-link').length) { fields.append('<a class="gps-google-admin-link" href="/google-login.php?admin_link=1" style="display:inline-block;margin:12px 0 0;padding:9px 13px;background:#fff;color:#111;border-radius:6px;font-weight:700;text-decoration:none;">Connect this Google account to Admin</a><small style="display:block;margin-top:6px;color:#9fb0ca;">Use this once while signed in with your CMS administrator password.</small>'); }
                card.data('gps-loading', false);
            });
    }

    function gpsSyncProPage(data) {
        var label = $('#gps-pro-page-license-label');
        if (!label.length) { return; }
        var features = Array.isArray(data.features) ? data.features : [];
        var googleIncluded = features.indexOf('google_login') !== -1 || features.indexOf('all_pro_features') !== -1;
        if (data.ok) {
            label.text('PRO Active');
            $('#gps-pro-page-license-description').text('Licensed updates are included' + (data.expires_at ? ' until ' + String(data.expires_at).slice(0, 10) : '') + '.');
            $('.gps-pro-feature-check').prop('checked', true);
            $('.gps-pro-feature-status').text('Included automatically with this installed PRO release');
            if (googleIncluded) { gpsLoadGoogleLoginControls(); }
        } else {
            label.text(data.state === 'expired' ? 'PRO Expired' : 'Free CMS');
            $('#gps-pro-page-license-description').text(data.state === 'expired' ? 'Your installed features remain available. Renew to receive new releases.' : 'Activate PRO from the Dashboard to unlock premium features.');
        }
    }

    function gpsLicenseStatus() {
        var activationBox = $('#gps-license-activation-box');
        var initialMenuLabel = $('#gps-pro-menu-label');
        initialMenuLabel.text('Checking licenseâ€¦');
        $('#gps-pro-panel-title').html('<i class="fa fa-crown"></i> Checking GamePortalScript licenseâ€¦');
        activationBox.css('visibility', 'hidden');

        $.ajax({
            url: Ajaxrequest() + '?t=admin&a=license-status',
            dataType: 'json',
            timeout: 15000
        })
              .done(function (data) {
                gpsSyncProPage(data);
                var text = data.state === 'expired'
                    ? 'License expired. Renew PRO to continue receiving CMS, SEO, optimization and design updates.'
                    : (data.ok
                        ? 'PRO Active: ' + (data.plan || 'Premium') + (data.expires_at ? ' · updates included until ' + String(data.expires_at).slice(0, 10) : '')
                        : 'Inactive. Enter a valid GamePortalScript license key to enable PRO updates.');
                $('#gps-license-status').text(text);
                if (data.ok) {
                    if (!activationBox.length) { activationBox = $('#gps-license-status').parent(); }
                    var menuLabel = $('#gps-pro-menu-label');
                    if (!menuLabel.length) {
                        menuLabel = $('li._4lf span').filter(function () { return $(this).text().indexOf('Upgrade to PRO') !== -1; });
                    }
                    var menuItem = $('#gps-pro-menu-item');
                    if (!menuItem.length) { menuItem = menuLabel.closest('li'); }
                    if ($('#gps-license-key').length) {
                        activationBox.html(
                            '<div id="gps-license-status" style="margin:7px 0;color:#dbeafe;"></div>' +
                            '<button id="gps-license-refresh" type="button" class="btn-p btn-p1"><i class="fa fa-refresh"></i> Check license now</button>' +
                            '<div id="gps-license-debug" style="display:none;margin-top:7px;color:#ff918a;white-space:pre-wrap;word-break:break-word;"></div>'
                        );
                        $('#gps-license-status').text(text);
                    }
                    menuLabel.text('PRO Active').css('color', '#ffd34d');
                    menuItem.css({
                        background: 'linear-gradient(90deg,#3b2f0c,#211d0d)',
                        borderLeftColor: '#b88914'
                    });
                    $('#gps-pro-panel-title').html('<i class="fa fa-crown"></i> GamePortalScript PRO is active');
                    var activeMessage = 'Your premium CMS tools and the latest licensed updates are unlocked.';
                    if (data.expires_at) {
                        var expiryValue = String(data.expires_at);
                        var expiryDate = new Date(expiryValue.replace(' ', 'T') + (expiryValue.indexOf('Z') === -1 ? 'Z' : ''));
                        if (!isNaN(expiryDate.getTime())) {
                            var daysRemaining = Math.max(0, Math.ceil((expiryDate.getTime() - Date.now()) / 86400000));
                            activeMessage += ' License expires ' + expiryDate.toLocaleDateString(undefined, {
                                year: 'numeric', month: 'short', day: 'numeric', timeZone: 'UTC'
                            }) + ' · ' + daysRemaining + ' day' + (daysRemaining === 1 ? '' : 's') + ' remaining.';
                        }
                    }
                    $('#gps-pro-panel-text').text(activeMessage);
                } else if (data.state === 'expired') {
                    $('#gps-pro-menu-label').text('PRO Expired').css('color', '#ff9f43');
                    $('#gps-pro-panel-title').html('<i class="fa fa-crown"></i> Your GamePortalScript PRO license has expired');
                    $('#gps-pro-panel-text').text('Your installed CMS and existing features remain available. Renew PRO to receive new updates and releases.');
                } else {
                    $('#gps-pro-menu-label').text('Upgrade to PRO').css('color', '#ffd34d');
                    $('#gps-pro-panel-title').html('<i class="fa fa-crown"></i> Grow your game portal with GamePortalScript PRO');
                }
                activationBox.css('visibility', 'visible');
                gpsLicenseDebug(data.last_error ? 'Debug: ' + data.last_error : '');
                gpsLicenseRefreshCooldown(data.refresh_retry_after || 0);
            })
            .fail(function () {
                $('#gps-license-status').text('Could not read license status.');
                $('#gps-pro-menu-label').text('License status unavailable');
                $('#gps-pro-panel-title').html('<i class="fa fa-crown"></i> License status unavailable');
                activationBox.css('visibility', 'visible');
                gpsLicenseDebug('Debug: license-status request failed.');
            });
    }

    $('#gps-license-activate').click(function () {
        var button = $(this);
        var key = $('#gps-license-key').val();
        button.prop('disabled', true);
        $('#gps-license-status').text('Activating license…');
        $.ajax({
            url: Ajaxrequest() + '?t=admin&a=license-activate',
            type: 'POST',
            data: {license_key: key},
            dataType: 'json',
            timeout: 30000
        })
            .done(function (data) {
                if (data.ok) {
                    $('#gps-license-key').val('');
                    Toast.success(data.message || 'License activated.');
                    window.setTimeout(function () { window.location.reload(); }, 500);
                  } else {
                      Toast.error(data.message || 'License activation failed.');
                      gpsLicenseDebug('Debug' + (data.code ? ' [' + data.code + ']' : '') + ': ' + (data.message || 'License activation failed.'));
                      gpsLicenseActivationCooldown(data.retry_after || 0);
                  }
              })
              .fail(function (xhr) {
                  var response = xhr.responseJSON || {};
                  var message = response.message || xhr.responseText || 'License activation failed.';
                  Toast.error(message);
                  gpsLicenseDebug('Debug: ' + message);
                  gpsLicenseActivationCooldown(response.retry_after || 0);
              })
              .always(function () {
                  if (!button.data('gps-cooldown')) { button.prop('disabled', false); }
              });
    });

    $(document).on('click', '.gps-google-config-toggle', function () {
        var form = $(this).closest('form');
        form.find('.gps-google-config-fields').stop(true, true).slideToggle(120);
        form.find('code').css({display:'inline-block',marginTop:'5px',padding:'5px 8px',background:'#fff',color:'#000',border:'1px solid #d0d5dd',borderRadius:'5px',fontWeight:'700',wordBreak:'break-all'});
    });
    $(document).on('click', '.gps-google-copy-callback', function () {
        var button = $(this), value = String(button.attr('data-callback') || '');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(value).then(function () { button.text('Copied'); window.setTimeout(function () { button.text('Copy callback URL'); }, 1400); });
        }
    });

    $(document).on('change', '#gps-google-login-form [name="google_oauth_enabled"]', function () {
        var form = $(this).closest('form');
        var needsCredentials = !form.find('[name="google_oauth_client_id"]').val() || form.find('[name="google_oauth_client_secret"]').attr('placeholder').indexOf('Enter') === 0;
        if (this.checked && needsCredentials) {
            form.find('.gps-google-config-fields').stop(true, true).slideDown(120);
            $('#gps-google-login-message').text('Add Google credentials, then save.').css('color', '#ffd34d');
            return;
        }
        form.trigger('submit');
    });

    $(document).on('submit', '#gps-google-login-form', function (event) {
        event.preventDefault();
        var form = $(this);
        var button = form.find('button[type="submit"]');
        button.prop('disabled', true);
        $.ajax({url: Ajaxrequest() + '?t=admin&a=google-login-config', type: 'POST', data: form.serialize(), dataType: 'json', timeout: 15000})
            .done(function (data) {
                $('#gps-google-login-message').text(data.message || (data.ok ? 'Saved.' : 'Could not save.')).css('color', data.ok ? '#8ff0bd' : '#ffb7c0');
                if (data.ok) {
                    form.find('.gps-google-status').text(form.find('[name="google_oauth_enabled"]').is(':checked') ? 'Enabled' : 'Disabled');
                    form.find('.gps-google-config-fields').slideUp(120);
                }
            })
            .fail(function (xhr) { var data = xhr.responseJSON || {}; $('#gps-google-login-message').text(data.message || 'Google Login settings could not be saved.').css('color', '#ffb7c0'); })
            .always(function () { button.prop('disabled', false); });
    });

    $(document).on('click', '#gps-license-refresh', function () {
        var button = $(this);
        button.prop('disabled', true);
        $('#gps-license-status').text('Refreshing license from GamePortalScript…');
        $.ajax({
            url: Ajaxrequest() + '?t=admin&a=license-refresh',
            type: 'POST',
            dataType: 'json',
            timeout: 30000
        })
            .done(function (data) {
                if (data.ok) {
                    Toast.success(data.message || 'License refreshed.');
                    window.setTimeout(function () { window.location.reload(); }, 500);
                } else {
                    $('#gps-license-status').text(data.message || 'License refresh failed.');
                    gpsLicenseDebug('Debug' + (data.code ? ' [' + data.code + ']' : '') + ': ' + (data.message || 'License refresh failed.'));
                    gpsLicenseRefreshCooldown(data.retry_after || 0);
                    if (/^license_(expired|suspended|revoked)$/.test(String(data.code || ''))) {
                        Toast.error(data.message || 'License is no longer active. Switching to Free/Demo.');
                        window.setTimeout(function () { window.location.reload(); }, 700);
                    }
                }
            })
            .fail(function (xhr) {
                var response = xhr.responseJSON || {};
                var message = response.message || xhr.responseText || 'License refresh failed.';
                $('#gps-license-status').text('License refresh failed.');
                gpsLicenseDebug('Debug: ' + message);
                gpsLicenseRefreshCooldown(response.retry_after || 0);
            })
            .always(function () {
                button.prop('disabled', false);
            });
    });

    var gpsNewsRefreshButton = $('#gps-cms-news-box button').filter(function () {
        return $(this).text().indexOf('Refresh news') !== -1;
    });
    var gpsNewsRefreshKey = 'gps_cms_news_manual_refresh_at_v1';
    var gpsNewsRefreshCooldown = 10800000;
    var gpsNewsLastRefresh = 0;
    try { gpsNewsLastRefresh = Number(localStorage.getItem(gpsNewsRefreshKey) || 0); } catch (e) {}
    var gpsNewsRetry = Math.max(0, Math.ceil((gpsNewsLastRefresh + gpsNewsRefreshCooldown - Date.now()) / 1000));
    gpsNewsRefreshButton.removeAttr('onclick').prop('disabled', gpsNewsRetry > 0).text(
        gpsNewsRetry > 0 ? 'News refresh in ' + gpsWaitLabel(gpsNewsRetry) : 'Refresh news now'
    ).on('click', function () {
        if (gpsNewsRetry > 0) { return; }
        try {
            localStorage.setItem(gpsNewsRefreshKey, String(Date.now()));
            localStorage.removeItem('gps_cms_pro_news_v1');
        } catch (e) {}
        window.location.reload();
    });

    if ($('#gps-license-status').length || $('#gps-pro-menu-item').length) {
        gpsLicenseStatus();
    }

    // Click event handler for the generateSitemapButton
    $('#generateSitemapButton').click(function () {
        $(this).attr('disabled', true);
        $(this).addClass('disable');
        $.ajax({
            url: Ajaxrequest() + '?t=admin&a=generatesitemap',
            type: 'POST', // HTTP method for the request
            success: function (data) {
                if (data.status == 200) {
                    Toast.success(data.success_message);
                } else {
                    Toast.error(data.error_message);
                }
                $('#generateSitemapButton').attr('disabled', false);
                $('#generateSitemapButton').removeClass('disable');
            }
        });
    });

    $(document).ready(function () {
        $(document).on('click', '.game_type-import', function () {
            var __rEi = $(this);
            __addgame_showImport(__rEi);
        });

        function __addgame_showImport(game_type_import) {
            var __It1 = $('#game_import0');
            var __It0 = $('#game_import1');
            var __ValIt = $('.game_type-import--val');

            if (game_type_import.hasClass('i-t-1')) {
                __It0.show();
                __It1.hide();
                __ValIt.attr('value', 1);
            } else if (game_type_import.hasClass('i-t-0')) {
                __It1.show();
                __It0.hide();
                __ValIt.attr('value', 0);
            }

        }

        $(document).on('click', '.game_state-E', function () {
            var __rHs = $(this);
            __addgame_State(__rHs);
        });

        function __addgame_State(__sId) {
            var __Vals1 = $('.game_published--val');
            var __Vals2 = $('.game_featured--val');

            if (__sId.hasClass('s-t-P')) {
                if (__Vals1.val() == 1) {
                    __Vals1.attr('value', 0);
                } else {
                    __Vals1.attr('value', 1);
                }
            } else if (__sId.hasClass('s-t-F')) {
                if (__Vals2.val() == 1) {
                    __Vals2.attr('value', 0);
                } else {
                    __Vals2.attr('value', 1);
                }
            }
        }

        $(document).on('click', '.report-btn-action', function () {
            var __rHs = $(this);
            __rp_actEx(__rHs);
        });

        function __rp_actEx($bThis) {
            var _actTy = $bThis.attr('data-rp-action');
            var _rp_Inf = $bThis.attr('data-rp-id');
            if (_actTy == 1) {
                var _rp_Usr = $bThis.attr('data-user');
                $.ajax({
                    url: Ajaxrequest() + '?t=admin&a=act_report',
                    type: 'POST',
                    data: "uid=" + _rp_Usr + "&rp_id=" + _rp_Inf,
                    success: function () {
                        $('.report-r' + _rp_Inf).slideToggle(200, function () {
                            $(this).remove();
                        });
                    }
                });
            } else if (_actTy == 2) {
                $.ajax({
                    url: Ajaxrequest() + '?t=admin&a=act_report',
                    type: 'POST',
                    data: "rp_id=" + _rp_Inf,
                    success: function () {
                        $('.report-r' + _rp_Inf).slideToggle(200, function () {
                            $(this).remove();
                        });
                    }
                });
            }
        }
    });

    /* ADD GAME */
    var addgame_bar = $('.addgame_bar');
    var addprogress = $('.addgame_progress');

    $('#addgame-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=addgame',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addgame-header');
            addgame_btn = form_h.find('#addgame-btn');
            addgame_btn.attr('disabled', true);
            var ag_pV = '0%';
            addgame_bar.width(ag_pV)
        },
        uploadProgress: function (event, position, total, percentComplete) {
            addprogress.show()
            var ag_pV = percentComplete + '%';
            addgame_bar.width(ag_pV)
            //console.log(ag_pV, position, total);
        },
        success: function (data) {
            if (data.status == 200) {
                addprogress.hide()
                addgame_bar.width('0%')
                document.getElementById('addgame-form').reset();
                Toast.success(data.success_message);
            }
            else {
                addprogress.hide()
                addgame_bar.width('0%')
                Toast.error(data.error_message);
            }
            addgame_btn.attr('disabled', false);
        }
    });


    /* EDIT GAME */
    var editgame_bar = $('.editgame_bar');
    $('#addgame-btn2').click(function () {
        $('#addgame-btn').trigger('click');
        tinymce.triggerSave();
    });
    $('#addgame-btn').click(function () {
        tinymce.triggerSave();
    });
    $('#editgame-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=editgame',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.editgame-header');
            addgame_btn = form_h.find('#addgame-btn');
            addgame_btn.attr('disabled', true);
            var eg_pV = '0%';
            editgame_bar.width(eg_pV)
            eg_m_uploadInput = $('#_-2m-f');
            eg_f_uploadInput = $('#_-3f-f');
        },
        uploadProgress: function (event, position, total, percentComplete) {
            var eg_pV = percentComplete + '%';
            editgame_bar.width(eg_pV)
            //console.log(eg_pV, position, total);
        },
        success: function (data) {
            if (data.status == 200) {
                $('#editgame_image').attr('src', data.game_img);
                $('#editgame_name').text(data.game_name);
                eg_m_uploadInput.replaceWith(eg_m_uploadInput.val('').clone(true));
                eg_f_uploadInput.replaceWith(eg_f_uploadInput.val('').clone(true));

                Toast.success(data.success_message);
                location.reload();
            }
            else {
                Toast.error(data.error_message);
            }
            addgame_btn.attr('disabled', false);
        }
    });

    /* MANAGE GAMES */
    $(document).ready(function () {
        $(document).on('click', '#mg--published', function () {
            var _mg_eXid = $(this).attr('data-game');
            var _mg_eXpb = $('.mg_p-' + _mg_eXid).attr('data-pb');
            __ajaxManageStatusGame(_mg_eXid, _mg_eXpb, 1);
        });

        $(document).on('click', '#mg--featured', function () {
            var _mg_eXid = $(this).attr('data-game');
            var _mg_eXpb = $('.mg_f-' + _mg_eXid).attr('data-ft');
            __ajaxManageStatusGame(_mg_eXid, _mg_eXpb, 2);
        });

        $(document).on('click', '#mg--delete', function () {
            var _mg_eXid = $(this).attr('data-game');
            __ajaxManageStatusGame(_mg_eXid, 0, 3);
        });

        function __ajaxManageStatusGame(gid, ss, tp) {
            if (tp == 1) {
                $.ajax({
                    url: Ajaxrequest() + '?t=admin&a=mg_published',
                    type: 'POST',
                    data: "gid=" + gid,
                    success: function () {
                        var _pd4 = $('.mg_p-' + gid);
                        if (ss == 1) {
                            _pd4.removeClass('pub-active');
                            _pd4.attr('data-pb', '0');
                        } else {
                            _pd4.addClass('pub-active');
                            _pd4.attr('data-pb', '1');
                        }
                    }
                });
            } else if (tp == 2) {
                $.ajax({
                    url: Ajaxrequest() + '?t=admin&a=mg_featured',
                    type: 'POST',
                    data: "gid=" + gid,
                    success: function () {
                        var _ft0 = $('.mg_f-' + gid);
                        if (ss == 1) {
                            _ft0.removeClass('feat-active');
                            _ft0.attr('data-ft', '0');
                        } else {
                            _ft0.addClass('feat-active');
                            _ft0.attr('data-ft', '1');
                        }
                    }
                });
            } else if (tp == 3) {
                $.ajax({
                    url: Ajaxrequest() + '?t=admin&a=mg_delete',
                    type: 'POST',
                    data: "gid=" + gid,
                    success: function () {
                        var _dlt9 = $('.__mg-' + gid);
                        _dlt9.slideToggle(200, function () {
                            $(this).remove();
                        });
                    }
                });
            }
        }
    });

    /* MANAGE CATEGORIES */

    /* ADD GAME */
    var addcategory_bar = $('.addcategory_bar');
    var addcategoryprogress = $('.addcategory_progress');

    $('#addcategory-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=addcategory',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addcategory-header');
            addcategory_btn = form_h.find('#addcategory-btn');
            addcategory_btn.attr('disabled', true);
            var ag_pV = '0%';
            addcategory_bar.width(ag_pV)
        },
        uploadProgress: function (event, position, total, percentComplete) {
            addcategoryprogress.show()
            var ag_pV = percentComplete + '%';
            addcategory_bar.width(ag_pV)
            //console.log(ag_pV, position, total);
        },
        success: function (data) {
            if (data.status == 200) {
                addcategoryprogress.hide()
                addcategory_bar.width('0%')
                document.getElementById('addcategory-form').reset();
                Toast.success(data.success_message);
            }
            else {
                addcategoryprogress.hide()
                addcategory_bar.width('0%')
                Toast.error(data.error_message);
            }
            addcategory_btn.attr('disabled', false);
        }
    });

    $('#editcategory-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=editcategory',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addcategory-header');
            addcategory_btn = form_h.find('#addcategory-btn');
            addcategory_btn.attr('disabled', true);
            var ag_pV = '0%';
            addcategory_bar.width(ag_pV)
        },
        uploadProgress: function (event, position, total, percentComplete) {
            addcategoryprogress.show()
            var ag_pV = percentComplete + '%';
            addcategory_bar.width(ag_pV)
            //console.log(ag_pV, position, total);
        },
        success: function (data) {
            if (data.status == 200) {
                addcategoryprogress.hide()
                addcategory_bar.width('0%')
                Toast.success(data.success_message);
            }
            else {
                addcategoryprogress.hide()
                addcategory_bar.width('0%')
                Toast.error(data.error_message);
            }
            addcategory_btn.attr('disabled', false);
        }
    });

    $(document).ready(function () {
        $(document).on('click', '#mc--delete', function () {
            var _mc_eXcid = $(this).attr('data-category');
            __dltCtgr7(_mc_eXcid);
        });

        function __dltCtgr7(cid) {
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=mc_delete',
                type: 'POST',
                data: "cid=" + cid,
                success: function () {
                    var _cdlt3 = $('.__mc-' + cid);
                    _cdlt3.slideToggle(200, function () {
                        $(this).remove();
                    });
                }
            });
        }
    });

    /* MANAGE TAGS */
    $('#addtags-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=addtags',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addtags-header');
            addtags_btn = form_h.find('#addtags-btn');
            addtags_btn.attr('disabled', true);
        },
        success: function (data) {
            if (data.status == 200) {
                document.getElementById('addtags-form').reset();
                Toast.success(data.success_message);
            }
            else {
                Toast.error(data.error_message);
            }
            addtags_btn.attr('disabled', false);
        }
    });

    $('#edittags-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=edittags',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addtags-header');
            addtags_btn = form_h.find('#addtags-btn');
            addtags_btn.attr('disabled', true);
        },
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
                location.reload();
            }
            else {
                Toast.error(data.error_message);
            }
            // addtest_btn.attr('disabled', false);
        }
    });

    $(document).ready(function () {
        $(document).on('click', '#mc-tags-delete', function () {
            var _mc_eXcid = $(this).attr('data-tags');
            __dltCtgr7(_mc_eXcid);
        });

        function __dltCtgr7(cid) {
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=mc_tags_delete',
                type: 'POST',
                data: "cid=" + cid,
                success: function () {
                    var _cdlt3 = $('.__mc-' + cid);
                    _cdlt3.slideToggle(200, function () {
                        $(this).remove();
                    });
                }
            });
        }

        $(document).on('click', '.clickToCopy', function (e) {
            e.preventDefault();
            var link = $(this).attr('href');
            var name = $(this).attr('data-name');
            link = `<a href="${link}">${name}</a>`;
            navigator.clipboard.writeText(link);
        });

        $(document).on('click', '.clickToCopyText', function (e) {
            e.preventDefault();
            var text = $(this).attr('data-text');
            navigator.clipboard.writeText(text);
        });

        $(document).on('click', '.rewriteText', function (e) {
            e.preventDefault();
            var rewriteTemplate = $(this).attr('data-text');
            var rewriteMethod = $(this).attr("data-method");
            var originalDescription = $(".eg_description").val();

            if (rewriteMethod === undefined) {
                rewriteMethod = "rewritechatgpt";
            }
            $('.rewriteText').addClass('btn-w');
            $('.rewriteText').css('pointer-events', 'none');
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=' + rewriteMethod,
                type: 'POST',
                data: `game_id=${$('.eg_id').val()}&text=${rewriteTemplate}&originalText=${originalDescription}`,
                beforeSend: function () {
                    Toast.info('Rewriting, please wait...', '', {
                        displayDuration: 0
                    });
                },
                success: function (response) {
                    if (response.status == 200) {
                        Toast.success('Success');
                        $('.rewriteResultBox').show();
                        $('.rewriteResult').text(response.rewrite_result);
                        $('.rewriteResultBox .clickToCopyText').attr("data-text", response.rewrite_result);
                        $('.rewriteResult').text(response.rewrite_result);
                        if (rewriteMethod === "rewritechatgpt") {
                            $('.rewriteOpenAI').remove();
                        }
                    } else {
                        Toast.error(response.error_message);
                    }
                },
                complete: function () {
                    $('.toast-info').remove();
                    $('.rewriteText').removeClass('btn-w');
                    $('.rewriteText').css('pointer-events', 'all');
                }
            });
        });

        $(document).on('click', '.rewriteTextCategory', function (e) {
            e.preventDefault();
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=chatgptrewritecategory',
                type: 'POST',
                data: `cat_id=${$('.ec_id').val()}`,
                success: function (response) {
                    // console.log(response);
                }
            });
        });

        $(document).on('click', '.rewriteTextTags', function (e) {
            e.preventDefault();
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=chatgptrewritetags',
                type: 'POST',
                data: `tag_id=${$('.ec_id').val()}`,
                success: function (response) {
                    // console.log(response);
                }
            });
        });

        $(document).on('click', '.rewriteTextFooter', function (e) {
            e.preventDefault();
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=chatgptrewritefooter',
                type: 'POST',
                data: `footer_id=${$('.id').val()}`,
                success: function (response) {
                    // console.log(response);
                }
            });
        });

        $(document).on('click', '.linksEnableDisable', function (e) {
            e.preventDefault();
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=linksenabledisable',
                type: 'POST',
                data: `links_id=${$(this).attr('data-id')}`,
                success: function () {
                    location.reload();
                }
            });
        });

        $(document).on('click', '#enableAutopostCronBtn', function (e) {
            e.preventDefault();

            var btn = $(this);
            btn.attr('disabled', true).addClass('disable');

            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=linksenabledisable',
                type: 'POST',
                    dataType: 'json',
                    data: 'auto_cron=1',
                    success: function (response) {
                    btn.attr('disabled', false).removeClass('disable');

                    if (response.status == 200 && response.cron_url) {
                        window.open(response.cron_url, '_blank');
                    } else {
                        Toast.error(response.error_message || 'Could not enable autopost cron.');
                    }
                },
                error: function () {
                    btn.attr('disabled', false).removeClass('disable');
                    Toast.error('Request failed. Please try again.');
                }
            });
        });

        $(document).on('click', '#uploadGameDescriptionButton', function (e) {
            e.preventDefault();
            var file_data = $('#gameDescriptionFile').prop('files')[0];
            var form_data = new FormData();
            form_data.append('file', file_data);

            Toast.info("Upload in progres, please wait.");

            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=gamedescriptionupload',
                dataType: 'text',
                cache: false,
                contentType: false,
                processData: false,
                type: 'POST',
                data: form_data,
                success: function (data) {
                    // location.reload();
                    data = JSON.parse(data);
                    if (data.status == 200) {
                        Toast.success(data.success_message);
                    } else {
                        Toast.error(data.error_message);
                    }
                    console.log(data.status);
                },
                error: function () {
                    console.log('error upload');
                }
            });
        });

        $(document).on('click', '.downloadGameDescriptionButton', function (e) {
            location.href = '/admin/gameDescriptionDownload';
        });
    });

    /* MANAGE FOOTER DESCRIPTION */
    $('#editfooterdescription-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=editfooterdescription',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addfooterdescription-header');
            addfooterdescription_btn = form_h.find('#addfooterdescription-btn');
            addfooterdescription_btn.attr('disabled', true);
        },
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
                location.reload();
            }
            else {
                Toast.error(data.error_message);
            }
        }
    });

    /* MANAGE BLOGS */
    var addblog_bar = $('.addblog_bar');
    var addblogprogress = $('.addblog_progress');

    $('#addblog-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=addblog',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addblog-header');
            addblog_btn = form_h.find('#addblog-btn');
            addblog_btn.attr('disabled', true);
        },
        success: function (data) {
            if (data.status == 200) {
                location.href = data.redirect_url;
                Toast.success(data.success_message);
            }
            else {
                Toast.error(data.error_message);
            }
            addblog_btn.attr('disabled', false);
        }
    });

    $('#editblog-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=editblog',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addblog-header');
            addblog_btn = form_h.find('#addblog-btn');
            addblog_btn.attr('disabled', true);
            var ag_pV = '0%';
            addblog_bar.width(ag_pV)
        },
        uploadProgress: function (event, position, total, percentComplete) {
            addblogprogress.show()
            var ag_pV = percentComplete + '%';
            addblog_bar.width(ag_pV)
            //console.log(ag_pV, position, total);
        },
        success: function (data) {
            if (data.status == 200) {
                addblogprogress.hide()
                addblog_bar.width('0%')
                Toast.success(data.success_message);
                location.reload();
            }
            else {
                addblogprogress.hide()
                addblog_bar.width('0%')
                Toast.error(data.error_message);
            }
            addblog_btn.attr('disabled', false);
        }
    });

    $(document).ready(function () {
        $(document).on('click', '#mc-blog-delete', function () {
            var _mc_eXcid = $(this).attr('data-blog');
            __dltCtgr7(_mc_eXcid);
        });

        function __dltCtgr7(cid) {
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=mc_blogs_delete',
                type: 'POST',
                data: "cid=" + cid,
                success: function () {
                    var _cdlt3 = $('.__mc-' + cid);
                    _cdlt3.slideToggle(200, function () {
                        $(this).remove();
                    });
                }
            });
        }
    });
    /* MANAGE SETTING */
    $('#adminsetting-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=setting',
        type: 'POST',
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
            }
            else {
                Toast.error(data.error_message);
            }
        }
    });

    /* MANAGE USERS */
    $('#edituser-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=edituser',
        type: 'POST',
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
            } else {
                Toast.error(data.error_message);
            }
        }
    });

    /* SEARCH USERS TO EDIT */
    $('#search-useredit-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=searchUserEdit',
        type: 'POST',
        success: function (data) {
            if (data.status == 200) {
                window.location = data.redirect_url;
            } else {
                Toast.error(data.error_message);
            }
        },
        error: function () {
            console.log('Connection failed!');
        }
    });

    /* MANAGE ADS AREA */
    $('#adsArea-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=manageAdsArea',
        type: 'POST',
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
            }
        }
    });

    /* MANAGE ADS AREA */
    $('#adsTxtArea-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=manageAdsTxtArea',
        type: 'POST',
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
            }
        }
    });

    /* MANAGE CHATGPT */
    $('#chatgptArea-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=chatgpt',
        type: 'POST',
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
            }
        }
    });

    /* MANAGE LINKS */
    $('#updatelink-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=linksupdate',
        type: 'POST',
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
                location.reload();
            }
            else {
                Toast.error(data.error_message);
            }
        }
    });

    /* MANAGE SLIDERS */
    var addslider_bar = $('.addslider_bar');
    var addsliderprogress = $('.addslider_progress');

    $('#addslider-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=slidersadd',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addslider-header');
            addslider_btn = form_h.find('#addslider-btn');
            addslider_btn.attr('disabled', true);
            var ag_pV = '0%';
            addslider_bar.width(ag_pV)
        },
        success: function (data) {
            if (data.status == 200) {
                addsliderprogress.hide()
                addslider_bar.width('0%')
                document.getElementById('addslider-form').reset();
                location.href = data.href;
                Toast.success(data.success_message);
            }
            else {
                addsliderprogress.hide()
                addslider_bar.width('0%')
                Toast.error(data.error_message);
            }
            addslider_btn.attr('disabled', false);
        }
    });

    $('#editslider-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=slidersedit',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addslider-header');
            addslider_btn = form_h.find('#addslider-btn');
            addslider_btn.attr('disabled', true);
        },
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
                location.reload();
            }
            else {
                Toast.error(data.error_message);
            }
            // addtest_btn.attr('disabled', false);
        }
    });

    $(document).ready(function () {
        $(document).on('click', '#mc-slider-delete', function () {
            __dltCtgr7($(this).attr('data-sliders'));
        });
        function __dltCtgr7(cid) {
            console.log(cid);
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=slidersdelete',
                type: 'POST',
                data: "cid=" + cid,
                success: function () {
                    var _cdlt3 = $('.__mc-' + cid);
                    _cdlt3.slideToggle(200, function () {
                        $(this).remove();
                    });
                }
            });
        }
    });

    /* MANAGE SIDEBAR */
    var addsidebar_bar = $('.addsidebar_bar');
    var addsidebarprogress = $('.addsidebar_progress');

    $('#addsidebar-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=sidebaradd',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addsidebar-header');
            addsidebar_btn = form_h.find('#addsidebar-btn');
            addsidebar_btn.attr('disabled', true);
            var ag_pV = '0%';
            addsidebar_bar.width(ag_pV)
        },
        success: function (data) {
            if (data.status == 200) {
                addsidebarprogress.hide()
                addsidebar_bar.width('0%')
                document.getElementById('addsidebar-form').reset();
                location.href = data.href;
                Toast.success(data.success_message);
            }
            else {
                addsidebarprogress.hide()
                addsidebar_bar.width('0%')
                Toast.error(data.error_message);
            }
            addsidebar_btn.attr('disabled', false);
        }
    });

    $('#editsidebar-form').ajaxForm({
        url: Ajaxrequest() + '?t=admin&a=sidebaredit',
        type: 'POST',
        beforeSend: function () {
            form_h = $('.addsidebar-header');
            addsidebar_btn = form_h.find('#addsidebar-btn');
            addsidebar_btn.attr('disabled', true);
        },
        success: function (data) {
            if (data.status == 200) {
                Toast.success(data.success_message);
                location.reload();
            }
            else {
                Toast.error(data.error_message);
            }
            // addtest_btn.attr('disabled', false);
        }
    });

    $('#enableDisableSidebar').click(function (e) {
        e.preventDefault();

        $.ajax({
            url: Ajaxrequest() + '?t=admin&a=sidebarenabledisable',
            type: 'POST',
            success: function (data) {
                if (typeof data.success_message !== 'undefined' && data.success_message) {
                    window.location.reload()
                }

                if (typeof data.error_message !== 'undefined') {
                    Toast.error(data.error_message);
                }
            },
            error: function () {
                Toast.error('Undefined error.');
            }
        });
    });


    $(document).ready(function () {
        $(document).on('click', '#mc-sidebar-delete', function () {
            var _mc_eXcid = $(this).attr('data-sidebar');
            __dltCtgr7(_mc_eXcid);
        });

        function __dltCtgr7(cid) {
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=sidebarsdelete',
                type: 'POST',
                data: "cid=" + cid,
                success: function () {
                    var _cdlt3 = $('.__mc-' + cid);
                    _cdlt3.slideToggle(200, function () {
                        $(this).remove();
                    });
                }
            });
        }
    });

    /*******************\
       MANAGE FEED DATA  
    \*******************/

    $(document).ready(function () {

        $('#install-games-catalog').on('click', function () {
            var self = $(this);

            install_games_catalog(1);
        });

        function install_games_catalog(page) {
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=install-games-catalog&page=' + page,
                type: 'POST',
                success: function (data) {
                    if (typeof data.next_page !== 'undefined') {
                        install_games_catalog(data.next_page)
                        $('#install-games-catalog').attr('disabled', true);
                        $('#install-games').attr('disabled', true);
                        $('.installing-message').html('<div class="installing-games-alert">' + data.games_procesing_message + '</div>');
                    } else {
                        if (typeof data.reload_success !== 'undefined' && data.reload_success) {
                            window.location.reload()
                        }

                        if (typeof data.error_message !== 'undefined') {
                            Toast.error(data.error_message);
                        }

                        $('#install-games-catalog').attr('disabled', false);
                        $('#install-games').attr('disabled', false);
                        $('.installing-message').html('');
                    }
                },
                error: function () {
                    $('#install-games-catalog').attr('disabled', false);
                    $('#install-games').attr('disabled', false);
                    $('.installing-message').html('Conection fail!');
                }
            });
        }

        $('#install-games').on('click', function () {
            var self = $(this);

            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=install-games',
                type: 'POST',
                beforeSend: function () {
                    $('#install-games').attr('disabled', true);
                    $('#install-games-download').attr('disabled', true);
                    $('#install-games-catalog').attr('disabled', true);
                    Toast.info('Loading games, please wait...', '', {
                        displayDuration: 0
                    });
                },
                success: function (data) {
                    if (typeof data.message !== 'undefined') {
                        $('#install-games').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        Toast.success(data.message, '', {
                            displayDuration: 5000
                        });
                    } else {
                        if (typeof data.error_message !== 'undefined') {
                            Toast.error(data.error_message);
                        }

                        $('#install-games').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        $('.installing-message').html('');
                    }
                },
                error: function () {
                    $('#install-games').attr('disabled', false);
                    $('#install-games-download').attr('disabled', false);
                    $('#install-games-catalog').attr('disabled', false);
                    $('.installing-message').html('Conection fail!');
                }
            });
        });

        $('#install-games-download').on('click', function () {
            var self = $(this);

            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=install-games-download',
                type: 'POST',
                beforeSend: function () {
                    $('#install-games').attr('disabled', true);
                    $('#install-games-download').attr('disabled', true);
                    $('#install-games-catalog').attr('disabled', true);
                    Toast.info('Loading games, please wait...', '', {
                        displayDuration: 0
                    });
                },
                success: function (data) {
                    if (typeof data.message !== 'undefined') {
                        $('#install-games').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        Toast.success(data.message, '', {
                            displayDuration: 5000
                        });
                    } else {
                        if (typeof data.error_message !== 'undefined') {
                            Toast.error(data.error_message);
                        }

                        $('#install-games').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        $('.installing-message').html('');
                    }
                },
                error: function () {
                    $('#install-games').attr('disabled', false);
                    $('#install-games-download').attr('disabled', false);
                    $('#install-games-catalog').attr('disabled', false);
                    $('.installing-message').html('Conection fail!');
                }
            });
        });

        // PUBLISH ALL GAMES
        $(document).on('click', '#publish-all-games', function () {
            var btn_Sts_dsd = $(this);
            __pblAllGames(btn_Sts_dsd);
        });

        function __pblAllGames($btnSts) {
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=publish_all_games',
                type: 'POST',
                beforeSend: function () {
                    $btnSts.attr('disabled', true);
                },
                success: function (data) {
                    $btnSts.attr('disabled', false);
                    if (data.status == 200) {
                        Toast.success(data.success_message);
                    }
                },
                error: function () {
                    $btnSts.attr('disabled', false);
                    console.log('Connection failed!');
                }
            });
        }

        $('#install-games-100').on('click', function () {
            var self = $(this);

            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=install-games-100',
                type: 'POST',
                beforeSend: function () {
                    $('#install-games-100').attr('disabled', true);
                    $('#install-games-download').attr('disabled', true);
                    $('#install-games-catalog').attr('disabled', true);
                    Toast.info('Loading games, please wait...', '', {
                        displayDuration: 0
                    });
                },
                success: function (data) {
                    if (typeof data.message !== 'undefined') {
                        $('#install-games-100').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        Toast.success(data.message, '', {
                            displayDuration: 5000
                        });
                        location.reload();
                    } else {
                        if (typeof data.error_message !== 'undefined') {
                            Toast.error(data.error_message);
                        }

                        $('#install-games-100').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        $('.installing-message').html('');
                    }
                },
                error: function () {
                    $('#install-games-100').attr('disabled', false);
                    $('#install-games-download').attr('disabled', false);
                    $('#install-games-catalog').attr('disabled', false);
                    $('.installing-message').html('Conection fail!');
                }
            });
        });

        $('#install-games-1000').on('click', function () {
            var self = $(this);

            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=install-games-1000',
                type: 'POST',
                beforeSend: function () {
                    $('#install-games-1000').attr('disabled', true);
                    $('#install-games-download').attr('disabled', true);
                    $('#install-games-catalog').attr('disabled', true);
                    Toast.info('Loading games, please wait...', '', {
                        displayDuration: 0
                    });
                },
                success: function (data) {
                    if (typeof data.message !== 'undefined') {
                        $('#install-games-1000').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        Toast.success(data.message, '', {
                            displayDuration: 5000
                        });
                        location.reload();
                    } else {
                        if (typeof data.error_message !== 'undefined') {
                            Toast.error(data.error_message);
                        }

                        $('#install-games-1000').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        $('.installing-message').html('');
                    }
                },
                error: function () {
                    $('#install-games-1000').attr('disabled', false);
                    $('#install-games-download').attr('disabled', false);
                    $('#install-games-catalog').attr('disabled', false);
                    $('.installing-message').html('Conection fail!');
                }
            });
        });

        $('#install-games-custom').on('click', function () {
            var self = $(this);
            var customValue = $('#install-games-custom-value').val();
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=install-games-custom',
                data: {
                    customValue: customValue
                },
                type: 'POST',
                beforeSend: function () {
                    $('#install-games-custom-value').attr('disabled', true);
                    $('#install-games-custom').attr('disabled', true);
                    $('#install-games-download').attr('disabled', true);
                    $('#install-games-catalog').attr('disabled', true);
                    Toast.info('Loading games, please wait...', '', {
                        displayDuration: 0
                    });
                },
                success: function (data) {
                    if (typeof data.message !== 'undefined') {
                        $('#install-games-custom-value').attr('disabled', false);
                        $('#install-games-custom').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        Toast.success(data.message, '', {
                            displayDuration: 5000
                        });
                        window.location.reload();
                    } else {
                        if (typeof data.error_message !== 'undefined') {
                            Toast.error(data.error_message);
                        }

                        $('#install-games-custom-value').attr('disabled', false);
                        $('#install-games-custom').attr('disabled', false);
                        $('#install-games-download').attr('disabled', false);
                        $('#install-games-catalog').attr('disabled', false);
                        $('.installing-message').html('');
                    }
                },
                error: function () {
                    $('#install-games-custom-value').attr('disabled', false);
                    $('#install-games-custom').attr('disabled', false);
                    $('#install-games-download').attr('disabled', false);
                    $('#install-games-catalog').attr('disabled', false);
                    $('.installing-message').html('Conection fail!');
                }
            });
        });

        $('#save-custom-feed').on('click', function () {
            var customGameFeed = $('#custom_game_feed_url').val();
            $.ajax({
                url: Ajaxrequest() + '?t=admin&a=update-custom-game-feed',
                data: {
                    customGameFeed: customGameFeed
                },
                type: 'POST',
                beforeSend: function () {
                    $('#save-custom-feed').attr('disabled', true);
                    Toast.info('Saving feed, please wait...', '', {
                        displayDuration: 0
                    });
                },
                success: function (data) {
                    if (typeof data.message !== 'undefined') {
                        Toast.success(data.message, '', {
                            displayDuration: 5000
                        });
                    } else {
                        if (typeof data.error_message !== 'undefined') {
                            Toast.error(data.error_message);
                        }
                    }
                    $('#save-custom-feed').attr('disabled', false);
                },
            });
        });
    });

    $('[data-href]').click(function () {
        // Ambil nilai 'data-href'
        var url = $(this).data('href');

        // Jika nilai url tidak kosong, lakukan navigasi
        if (url) {
            window.location.href = url;
        }
    });
});
