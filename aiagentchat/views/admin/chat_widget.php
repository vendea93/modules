<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$isClientContext = (defined('CUSTOMER_AREA') && CUSTOMER_AREA === true)
    || (function_exists('is_client_logged_in') && is_client_logged_in());

$isAdminContext = !$isClientContext && (
        (defined('ADMIN_AREA') && ADMIN_AREA === true)
        || (function_exists('is_staff_logged_in') && is_staff_logged_in())
    );

$widgetBaseUrl = $isClientContext ? site_url('aiagentchat/aiagentchat_client') : admin_url('aiagentchat');
$listAssignedChatsUrl = $widgetBaseUrl . '/widget_assigned_chats';
$startSessionUrl = $widgetBaseUrl . '/start_session';
$refreshSessionUrl = $widgetBaseUrl . '/refresh_session';

$bubbleIconClass = $isClientContext
    ? (get_option('aiagentchat_bubble_chat_icon_client') ?: 'fa fa-commenting')
    : (get_option('aiagentchat_bubble_chat_icon_admin') ?: 'fa fa-commenting');

$bubbleCssJson = $isClientContext
    ? (get_option('aiagentchat_bubble_chat_css_json_client') ?: '')
    : (get_option('aiagentchat_bubble_chat_css_json_admin') ?: '');

$csrfData = get_csrf_for_ajax();
?>

<link rel="preload" href="https://cdn.platform.openai.com/deployments/chatkit/chatkit.js" as="script"/>
<script defer src="https://cdn.platform.openai.com/deployments/chatkit/chatkit.js"></script>
<link rel="stylesheet" href="<?php echo module_dir_url(AIAGENTCHAT_MODULE_NAME, 'assets/css/widget.css'); ?>"/>

<div id="agentchat-launcher"
     data-context="<?php echo $isAdminContext ? 'admin' : 'client'; ?>"
     data-config="<?php echo html_escape($bubbleCssJson); ?>"
     aria-live="polite">
    <button class="agentchat-fab" id="agentchat-open" type="button" title="<?= _l('agentchat_open_chat'); ?>">
        <i class="<?php echo html_escape($bubbleIconClass); ?>" aria-hidden="true"></i>
        <span class="sr-only"><?= _l('agentchat_open_chat'); ?></span>
    </button>
</div>

<div id="agentchat-backdrop" aria-hidden="true"></div>

<div id="agentchat-flyout" role="dialog" aria-label="<?= _l('agentchat'); ?>" aria-modal="true" aria-hidden="true">
    <div class="ac-header">
        <div class="ac-title">
            <button id="agentchat-toggle-list" class="btn ac-toggle-list" type="button"
                    aria-label="<?= _l('agentchat_choose_chat'); ?>">
                <i class="fa fa-bars" aria-hidden="true"></i>
            </button>
            <div class="ac-brand">
                <span class="ac-brand-badge"><i class="fa fa-robot" aria-hidden="true"></i></span>
                <span class="ac-brand-text"><?= _l('agentchat'); ?></span>
            </div>
        </div>
        <div class="ac-actions">
            <button id="agentchat-min" class="btn" type="button" title="<?= _l('agentchat_minimize'); ?>"><i
                        class="fa fa-minus"></i></button>
            <button id="agentchat-close" class="btn" type="button" title="<?= _l('close'); ?>"><i
                        class="fa fa-times"></i></button>
        </div>
    </div>

    <div class="ac-body">
        <aside class="ac-sidebar" id="agentchat-sidebar" aria-label="<?= _l('agentchat_choose_chat'); ?>">
            <?php
            if ($isAdminContext && empty(get_option('aiagentchat_openai_api_key'))) {
            ?>
                <div class="ac-sidebar-head col-md-12">
                    <div class="alert alert-danger">
                        Missing OpenAI API Key. <a href="<?php echo admin_url(AIAGENTCHAT_MODULE_NAME . '/settings'); ?>">Configure</a>
                    </div>
                </div>
            <?php
            }
            ?>
            <div class="ac-sidebar-head">
                <div class="ac-search">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input type="text" id="agentchat-search" class="form-control"
                           placeholder="<?= _l('agentchat_search_chats'); ?>"/>
                </div>
            </div>

            <div class="ac-list-loader hide" id="agentchat-list-loader" aria-hidden="true">
                <div class="ac-skel-card"></div>
                <div class="ac-skel-card"></div>
                <div class="ac-skel-card"></div>
            </div>

            <div class="ac-list" id="agentchat-list" role="listbox"
                 aria-label="<?= _l('agentchat_assigned_chats'); ?>"></div>

            <div class="ac-empty hide" id="agentchat-empty" role="status" aria-live="polite">
                <div class="ac-empty-art">
                    <div class="ac-empty-ring"></div>
                    <div class="ac-empty-badge"><i class="fa fa-comments-o" aria-hidden="true"></i></div>
                </div>
                <h5 class="ac-empty-title"><?= _l('agentchat_no_chats_title'); ?></h5>
                <p class="ac-empty-sub"><?= _l('agentchat_no_chats_sub'); ?></p>
                <div class="ac-empty-actions">
                    <button type="button" class="btn btn-primary" id="agentchat-empty-refresh">
                        <i class="fa fa-refresh" aria-hidden="true"></i>
                        <span><?= _l('agentchat_no_chats_refresh'); ?></span>
                    </button>
                    <button type="button" class="btn btn-default" id="agentchat-empty-close">
                        <i class="fa fa-times" aria-hidden="true"></i>
                        <span><?= _l('agentchat_no_chats_close'); ?></span>
                    </button>
                </div>
            </div>
        </aside>

        <section class="ac-chat">
            <div class="ac-chat-placeholder" id="agentchat-placeholder">
                <i class="fa fa-comments-o" aria-hidden="true"></i>
                <div class="title"><?= _l('agentchat_select_chat_title'); ?></div>
                <div class="sub"><?= _l('agentchat_select_chat_sub'); ?></div>
            </div>

            <div class="ac-chat-loader hide" id="agentchat-chat-loader" aria-hidden="true">
                <div class="ac-spinner"></div>
            </div>

            <openai-chatkit id="agentchat-chatkit" style="height:100%;width:100%;display:none"></openai-chatkit>
        </section>
    </div>
</div>

<?php $ctx = $isAdminContext ? 'admin' : 'client'; ?>

<script>
    (function () {
        'use strict';
        (function attachToBody() {
            ['agentchat-launcher', 'agentchat-backdrop', 'agentchat-flyout'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el && el.parentNode !== document.body) document.body.appendChild(el);
            });
        })();

        var contextKey = '<?php echo $ctx; ?>';
        var endpoints = {
            listAssignedChatsUrl: '<?php echo $listAssignedChatsUrl; ?>',
            startSessionUrl: '<?php echo $startSessionUrl; ?>',
            refreshSessionUrl: '<?php echo $refreshSessionUrl; ?>'
        };

        var csrfName = '<?php echo $csrfData["token_name"]; ?>';
        var csrfHash = '<?php echo $csrfData["hash"]; ?>';

        var $backdrop = $('#agentchat-backdrop');
        var $flyout = $('#agentchat-flyout');
        var $launcher = $('#agentchat-launcher');
        var $openBtn = $('#agentchat-open');
        var $sidebar = $('#agentchat-sidebar');
        var $toggleListBtn = $('#agentchat-toggle-list');

        var $listEl = $('#agentchat-list');
        var $listLoaderEl = $('#agentchat-list-loader');
        var $emptyEl = $('#agentchat-empty');
        var $searchEl = $('#agentchat-search');
        var $placeholder = $('#agentchat-placeholder');
        var $chatLoader = $('#agentchat-chat-loader');

        var chatkitEl = document.getElementById('agentchat-chatkit');

        var flyoutOpened = false;
        var listPrimed = false;
        var currentChatId = null;
        var chatList = [];

        var sessionCache = {};
        var EXPIRY_BUFFER_MS = 60 * 1000;

        function cacheSession(chatId, clientSecret, expiresAtIso) {
            var ts = Date.now() + 15 * 60 * 1000;
            if (expiresAtIso) {
                var parsed = Date.parse(expiresAtIso);
                if (!isNaN(parsed)) ts = parsed;
            }
            sessionCache[String(chatId)] = {client_secret: clientSecret, expires_at: ts};
        }

        function getCachedSession(chatId) {
            var entry = sessionCache[String(chatId)];
            if (!entry) return null;
            var valid = entry.client_secret && (entry.expires_at - Date.now() > EXPIRY_BUFFER_MS);
            return valid ? entry : null;
        }

        function callStartSession(chatId) {
            var payload = {};
            payload[csrfName] = csrfHash;
            return $.ajax({
                url: endpoints.startSessionUrl + '/' + encodeURIComponent(chatId),
                method: 'POST',
                dataType: 'json',
                data: payload
            }).then(function (res) {
                if (res && res[csrfName]) csrfHash = res[csrfName];
                if (!res || !res.client_secret) throw new Error('No client_secret');
                cacheSession(chatId, res.client_secret, res.expires_at);
                return res.client_secret;
            });
        }

        function callRefreshSession(chatId) {
            var payload = {};
            payload[csrfName] = csrfHash;
            return $.ajax({
                url: endpoints.refreshSessionUrl + '/' + encodeURIComponent(chatId),
                method: 'POST',
                dataType: 'json',
                data: payload
            }).then(function (res) {
                if (res && res[csrfName]) csrfHash = res[csrfName];
                if (!res || !res.client_secret) throw new Error('No client_secret');
                cacheSession(chatId, res.client_secret, res.expires_at);
                return res.client_secret;
            });
        }

        function getClientSecretForChat(chatId) {
            var cached = getCachedSession(chatId);
            if (cached) return Promise.resolve(cached.client_secret);
            var stale = sessionCache[String(chatId)];
            return (stale ? callRefreshSession(chatId) : callStartSession(chatId));
        }

        function openFlyout() {
            if (flyoutOpened) return;
            flyoutOpened = true;
            $flyout.addClass('open').attr('aria-hidden', 'false');
            $backdrop.addClass('open').attr('aria-hidden', 'false');
            $launcher.hide();
            if (!listPrimed) {
                fetchAssignedChats();
            }
            setTimeout(function () {
                var el = document.getElementById('agentchat-search');
                if (el) el.focus();
            }, 60);
        }

        function closeFlyout() {
            if (!flyoutOpened) return;
            flyoutOpened = false;
            $flyout.removeClass('open').attr('aria-hidden', 'true');
            $backdrop.removeClass('open').attr('aria-hidden', 'true');
            $launcher.show();
            $sidebar.removeClass('show');
        }

        $('#agentchat-close, #agentchat-min, #agentchat-backdrop').on('click', closeFlyout);
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') closeFlyout();
        });
        $toggleListBtn.on('click', function () {
            $sidebar.toggleClass('show');
        });

        function applyBubbleStyle() {
            var raw = $launcher.attr('data-config') || '';
            var btn = document.getElementById('agentchat-open');
            var size = 60, radius = 18, textColor = '#fff', iconColor = null;
            var boxShadow = '0 16px 36px rgba(0,0,0,.22)';
            var gradientStart = getComputedStyle(document.documentElement).getPropertyValue('--ac-accent').trim() || '#6d5dfc';
            var gradientEnd = '#06b6d4';
            var solidBackground = null, borderColor = null, borderWidth = 0;
            if (raw) {
                try {
                    var cfg = JSON.parse(raw);
                    if (cfg.size) size = parseInt(cfg.size, 10) || size;
                    if (cfg.radius) radius = parseInt(cfg.radius, 10) || radius;
                    if (cfg.color) textColor = String(cfg.color);
                    if (cfg.iconColor) iconColor = String(cfg.iconColor);
                    if (cfg.shadow || cfg.boxShadow) boxShadow = String(cfg.shadow || cfg.boxShadow);
                    if (cfg.borderColor) borderColor = String(cfg.borderColor);
                    if (cfg.borderWidth != null) borderWidth = parseInt(cfg.borderWidth, 10) || 0;
                    gradientStart = cfg.gradientStart || gradientStart;
                    gradientEnd = cfg.gradientEnd || gradientEnd;
                    solidBackground = cfg.background || solidBackground;
                } catch (_) {
                    if (raw.indexOf(':') !== -1) {
                        raw.split(';').forEach(function (rule) {
                            var p = rule.split(':');
                            if (p.length < 2) return;
                            btn.style[p[0].trim()] = p.slice(1).join(':').trim();
                        });
                        clampBubbleIntoView();
                        return;
                    }
                }
            }
            btn.style.width = size + 'px';
            btn.style.height = size + 'px';
            btn.style.borderRadius = radius + 'px';
            btn.style.color = textColor;
            btn.style.boxShadow = boxShadow;
            btn.style.border = borderWidth ? (borderWidth + 'px solid ' + (borderColor || 'transparent')) : '0';
            btn.style.background = solidBackground ? solidBackground : ('linear-gradient(135deg,' + gradientStart + ',' + gradientEnd + ')');
            if (iconColor) {
                var i = btn.querySelector('i');
                if (i) i.style.color = iconColor;
            }
            clampBubbleIntoView();
        }

        var dragState = {
            pointerDown: false,
            isDragging: false,
            startX: 0,
            startY: 0,
            origLeft: 0,
            origTop: 0,
            suppressNextClick: false
        };
        var DRAG_THRESHOLD = 6;

        function posStorageKey() {
            return 'aiagentchat_bubble_pos_' + contextKey;
        }

        function saveBubblePos(left, top) {
            try {
                localStorage.setItem(posStorageKey(), JSON.stringify({left: left, top: top}));
            } catch (_) {
                document.cookie = posStorageKey() + '=' + encodeURIComponent(JSON.stringify({
                    left: left,
                    top: top
                })) + '; path=/; max-age=' + (60 * 60 * 24 * 365);
            }
        }

        function loadBubblePos() {
            try {
                var s = localStorage.getItem(posStorageKey());
                if (s) return JSON.parse(s);
            } catch (_) {
            }
            var m = document.cookie.match(new RegExp('(?:^|; )' + posStorageKey() + '=([^;]*)'));
            if (m) {
                try {
                    return JSON.parse(decodeURIComponent(m[1]));
                } catch (_) {
                }
            }
            return null;
        }

        function clearBubblePos() {
            try {
                localStorage.removeItem(posStorageKey());
            } catch (_) {
            }
            document.cookie = posStorageKey() + '=; path=/; max-age=0';
        }

        function clampBubbleIntoView() {
            var node = $launcher[0];
            if (!node) return;
            var pos = loadBubblePos();
            if (pos && typeof pos.left === 'number' && typeof pos.top === 'number') {
                node.style.left = pos.left + 'px';
                node.style.top = pos.top + 'px';
                node.style.right = 'auto';
                node.style.bottom = 'auto';
            } else {
                node.style.left = '';
                node.style.top = '';
                node.style.right = '';
                node.style.bottom = '';
            }
            var rect = node.getBoundingClientRect(), vw = window.innerWidth, vh = window.innerHeight;
            var off = (rect.right < 0) || (rect.bottom < 0) || (rect.left > vw) || (rect.top > vh) || rect.left > vw - 40 || rect.top > vh - 40;
            if (off) {
                clearBubblePos();
                node.style.left = '';
                node.style.top = '';
                node.style.right = '';
                node.style.bottom = '';
            }
        }

        function onPointerDown(evt) {
            var e = evt.originalEvent || evt;
            dragState.pointerDown = true;
            dragState.isDragging = false;
            var px = e.clientX ?? (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            var py = e.clientY ?? (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
            dragState.startX = px;
            dragState.startY = py;
            var rect = $launcher[0].getBoundingClientRect();
            dragState.origLeft = rect.left;
            dragState.origTop = rect.top;
        }

        function onPointerMove(evt) {
            if (!dragState.pointerDown) return;
            var e = evt.originalEvent || evt;
            var px = e.clientX ?? (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            var py = e.clientY ?? (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
            var dx = px - dragState.startX, dy = py - dragState.startY;
            if (!dragState.isDragging && (Math.abs(dx) > DRAG_THRESHOLD || Math.abs(dy) > DRAG_THRESHOLD)) dragState.isDragging = true;
            if (!dragState.isDragging) return;
            var node = $launcher[0], rect = node.getBoundingClientRect(), vw = window.innerWidth,
                vh = window.innerHeight;
            var newLeft = Math.max(4, Math.min(Math.max(4, vw - rect.width - 4), dragState.origLeft + dx));
            var newTop = Math.max(4, Math.min(Math.max(4, vh - rect.height - 4), dragState.origTop + dy));
            node.style.left = newLeft + 'px';
            node.style.top = newTop + 'px';
            node.style.right = 'auto';
            node.style.bottom = 'auto';
        }

        function onPointerUp() {
            if (!dragState.pointerDown) return;
            if (dragState.isDragging) {
                var rect = $launcher[0].getBoundingClientRect();
                saveBubblePos(rect.left, rect.top);
                dragState.suppressNextClick = true;
                setTimeout(function () {
                    dragState.suppressNextClick = false;
                }, 200);
            }
            dragState.pointerDown = false;
            dragState.isDragging = false;
        }

        $launcher.on('mousedown touchstart pointerdown', onPointerDown);
        $(window).on('mousemove touchmove pointermove', onPointerMove);
        $(window).on('mouseup touchend pointerup touchcancel pointercancel', onPointerUp);
        $(window).on('resize orientationchange', function () {
            setTimeout(clampBubbleIntoView, 50);
        });

        $openBtn.on('click', function (e) {
            if (dragState.suppressNextClick) {
                e.preventDefault();
                return false;
            }
            openFlyout();
        });

        function escapeHtml(s) {
            return String(s || '').replace(/[&<>'"]/g, function (c) {
                return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'})[c];
            });
        }

        function parseAccentFromSettings(json) {
            try {
                var s = JSON.parse(json || '{}');
                return s?.theme?.color?.accent?.primary || null;
            } catch (_) {
                return null;
            }
        }

        function setAccentCssVariables(hex) {
            if (!hex || typeof hex !== 'string') return;
            var h = hex.trim();
            if (!/^#?[0-9a-f]{6}$/i.test(h)) return;
            if (h[0] !== '#') h = '#' + h;
            var r = parseInt(h.slice(1, 3), 16), g = parseInt(h.slice(3, 5), 16), b = parseInt(h.slice(5, 7), 16);
            document.documentElement.style.setProperty('--ac-accent', h);
            document.documentElement.style.setProperty('--ac-accent-08', 'rgba(' + r + ',' + g + ',' + b + ',.12)');
        }

        function sanitizeChatKitIconName(raw) {
            if (typeof raw !== 'string') return null;
            var s = raw.trim().toLowerCase();
            var map = {
                'fa-bolt': 'bolt',
                'fa fa-bolt': 'bolt',
                'bolt': 'bolt',
                'fa-bug': 'bug',
                'fa fa-bug': 'bug',
                'bug': 'bug',
                'fa-star': 'star',
                'fa fa-star': 'star',
                'star': 'star',
                'fa-cog': 'settings-cog',
                'fa fa-cog': 'settings-cog',
                'settings': 'settings-cog',
                'settings-cog': 'settings-cog',
                'fa-comments': 'chat',
                'fa fa-comments': 'chat',
                'fa-commenting': 'chat',
                'fa fa-commenting': 'chat',
                'chat': 'chat'
            };
            if (map[s]) return map[s];
            if (/^[a-z0-9-]{2,}$/.test(s)) return s;
            return null;
        }

        function normalizeChatKitOptions(rawUI, chatId) {
            var out = {};
            if (!rawUI || typeof rawUI !== 'object') return out;
            if (rawUI.theme) out.theme = JSON.parse(JSON.stringify(rawUI.theme));
            if (rawUI.header) {
                var h = {};
                if (typeof rawUI.header.enabled !== 'undefined') h.enabled = !!rawUI.header.enabled;
                if (rawUI.header.leftAction) {
                    var ic = sanitizeChatKitIconName(rawUI.header.leftAction.icon);
                    if (ic) {
                        h.leftAction = {
                            icon: ic, onClick: function () {
                                document.dispatchEvent(new CustomEvent('agentchat:leftAction', {detail: {chatId: chatId}}));
                            }
                        };
                    }
                }
                if (Object.keys(h).length) out.header = h;
            }
            if (rawUI.composer && typeof rawUI.composer.placeholder === 'string') {
                out.composer = {placeholder: rawUI.composer.placeholder};
            }
            if (rawUI.startScreen) {
                var s = {};
                if (typeof rawUI.startScreen.greeting === 'string') s.greeting = rawUI.startScreen.greeting;
                if (Array.isArray(rawUI.startScreen.prompts)) {
                    var cleaned = rawUI.startScreen.prompts.map(function (p) {
                        var label = (p && (p.label || p.name)) ? String(p.label || p.name).trim() : '';
                        var prompt = (p && p.prompt) ? String(p.prompt).trim() : '';
                        if (!label || !prompt) return null;
                        var ic = p && typeof p.icon === 'string' ? sanitizeChatKitIconName(p.icon) : null;
                        var obj = {label: label, prompt: prompt};
                        if (ic) obj.icon = ic;
                        return obj;
                    }).filter(Boolean);
                    if (cleaned.length) s.prompts = cleaned;
                }
                if (Object.keys(s).length) out.startScreen = s;
            }
            if (rawUI.history && typeof rawUI.history.enabled !== 'undefined') {
                out.history = {enabled: !!rawUI.history.enabled};
            }
            if (typeof rawUI.locale === 'string' && rawUI.locale.trim()) {
                out.locale = rawUI.locale.trim();
            }
            return out;
        }

        function showListLoader(show) {
            $listLoaderEl.toggleClass('hide', !show);
            $listEl.toggleClass('hide', show);
            $emptyEl.addClass('hide');
        }

        function showChatPlaceholder() {
            $('#agentchat-chatkit').hide();
            $placeholder.show();
        }

        function revealChat() {
            $placeholder.hide();
            $('#agentchat-chatkit').show();
            if (window.matchMedia('(max-width: 768px)').matches) {
                $sidebar.removeClass('show');
            }
        }

        function chatListItemTemplate(chat) {
            return $(`
      <div class="ac-list-item" role="option" data-id="${chat.id}">
        <div class="ac-dot" style="background:${chat.accent || 'var(--ac-accent)'}"></div>
        <div class="ac-text">${escapeHtml(chat.name || ('#' + chat.id))}</div>
      </div>
    `).on('click', function () {
                selectChat(chat.id);
            });
        }

        function renderList(filter) {
            $listEl.empty();
            var filtered = chatList.filter(function (c) {
                if (!filter) return true;
                var q = (filter || '').toLowerCase();
                return (c.name || '').toLowerCase().indexOf(q) >= 0;
            });
            if (!filtered.length) {
                $emptyEl.removeClass('hide');
                return;
            }
            $emptyEl.addClass('hide');
            filtered.forEach(function (c) {
                var $item = chatListItemTemplate(c);
                if (String(c.id) === String(currentChatId)) $item.addClass('active');
                $listEl.append($item);
            });
        }

        function fetchAssignedChats() {
            showListLoader(true);
            return $.ajax({url: endpoints.listAssignedChatsUrl, method: 'GET', dataType: 'json'})
                .then(function (res) {
                    if (res && res[csrfName]) csrfHash = res[csrfName];
                    var items = Array.isArray(res?.chats) ? res.chats : [];
                    chatList = items.map(function (it) {
                        return {
                            id: it.id,
                            name: it.chat_name,
                            accent: parseAccentFromSettings(it.settings_json),
                            settings_json: it.settings_json || null
                        };
                    });
                    listPrimed = true;
                    renderList('');
                    $searchEl.val('');
                    currentChatId = null;
                    showListLoader(false);
                    showChatPlaceholder();
                })
                .fail(function (xhr) {
                    console.error('[agentchat] failed to load chats', xhr?.responseText);
                    chatList = [];
                    renderList('');
                    showListLoader(false);
                    showChatPlaceholder();
                });
        }

        $searchEl.on('input', function () {
            renderList($(this).val());
        });

        async function selectChat(chatId) {
            currentChatId = chatId;
            $listEl.find('.ac-list-item').removeClass('active');
            $listEl.find('.ac-list-item[data-id="' + chatId + '"]').addClass('active');
            try {
                var selected = chatList.find(function (c) {
                    return String(c.id) === String(chatId);
                });
                var accent = selected && selected.accent ? selected.accent : null;
                if (accent) setAccentCssVariables(accent);
            } catch (_) {
            }
            $chatLoader.removeClass('hide');
            try {
                await customElements.whenDefined('openai-chatkit');
                var parent = chatkitEl.parentNode;
                chatkitEl.style.display = 'none';
                parent.removeChild(chatkitEl);
                chatkitEl = document.createElement('openai-chatkit');
                chatkitEl.id = 'agentchat-chatkit';
                chatkitEl.style.cssText = 'height:100%;width:100%';
                parent.appendChild(chatkitEl);
                var row = chatList.find(function (c) {
                    return String(c.id) === String(chatId);
                });
                var options = {
                    theme: {colorScheme: 'light'},
                    api: {
                        getClientSecret: function () {
                            return getClientSecretForChat(chatId);
                        }
                    }
                };
                try {
                    if (row && row.settings_json) {
                        var ui = JSON.parse(row.settings_json);
                        var normalized = normalizeChatKitOptions(ui, chatId);
                        Object.assign(options, normalized);
                    }
                } catch (err) {
                    console.warn('[agentchat] invalid settings_json, using defaults', err);
                }
                options.theme = options.theme || {};
                options.theme.colorScheme = 'light';
                chatkitEl.setOptions(options);
                chatkitEl.addEventListener('chatkit.log', function (e) {
                    console.debug('[chatkit.log]', e.detail?.name, e.detail?.data);
                });
                chatkitEl.addEventListener('chatkit.error', function (e) {
                    console.error('[chatkit.error]', e.detail?.error);
                });
                revealChat();
            } catch (err) {
                console.error('[agentchat] init error', err);
                showChatPlaceholder();
            } finally {
                $chatLoader.addClass('hide');
            }
        }

        $(document).on('click', '#agentchat-empty-refresh', function () {
            fetchAssignedChats();
        });
        $(document).on('click', '#agentchat-empty-close', function () {
            $('#agentchat-close').trigger('click');
        });

        function clientPrecheckAssignments() {
            return $.ajax({url: endpoints.listAssignedChatsUrl, method: 'GET', dataType: 'json'})
                .then(function (res) {
                    if (res && res[csrfName]) csrfHash = res[csrfName];
                    var items = Array.isArray(res?.chats) ? res.chats : [];
                    if (!items.length) {
                        $('#agentchat-launcher,#agentchat-backdrop,#agentchat-flyout').remove();
                        return;
                    }
                    chatList = items.map(function (it) {
                        return {
                            id: it.id,
                            name: it.chat_name,
                            accent: parseAccentFromSettings(it.settings_json),
                            settings_json: it.settings_json || null
                        };
                    });
                    renderList('');
                    $searchEl.val('');
                    listPrimed = true;
                    var firstAccent = chatList.length ? chatList[0].accent : null;
                    if (firstAccent) setAccentCssVariables(firstAccent);
                })
                .fail(function (xhr) {
                    console.warn('[agentchat] precheck failed; leaving bubble visible', xhr?.responseText);
                });
        }

        applyBubbleStyle();
        clampBubbleIntoView();
        if (contextKey === 'client') clientPrecheckAssignments();
        if (!(window.isSecureContext || /(^|\.)(localhost|127\.0\.0\.1)$/i.test(location.hostname))) {
            console.warn('[agentchat] For best results, open over HTTPS or localhost.');
        }
    })();
</script>

