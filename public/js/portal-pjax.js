/**
 * Portal BIP - jQuery PJAX / SPA Router (All Desktop Modules)
 * Menghilangkan full-page reload & modal loading blokir antar modul menggunakan jQuery AJAX,
 * dilengkapi smart prefetch, slim progress bar, DOMContentLoaded capture, dan cache invalidation otomatis.
 */

(function(window, document, $) {
    'use strict';

    if (!$) return;

    var PREFETCH_TTL_MS = 15000;
    var BACK_CACHE_TTL_MS = 300000;
    var MAX_CACHE_ENTRIES = 25;

    var prefetchCache = new Map();
    var backCache = new Map();
    var inFlightRequests = new Map();
    var loadedExternalLibs = new Set();
    var viewIntervals = [];
    var pjaxDomReadyQueue = [];
    var activeNavXhr = null;
    var activePrefetchXhr = null;
    var hoverTimer = null;
    var currentNavId = 0;
    var progressTimer = null;

    var CORE_SCRIPT_SKIP = [
        'jquery-3.',
        'sweetalert2',
        '@tailwindcss/browser',
        '/public/js/app.js',
        '/public/js/modal-helper.js',
        '/public/js/notif.js',
        '/public/js/searchable-select.js',
        '/public/js/portal-pjax.js'
    ];

    var CACHE_ONCE_LIBS = [
        'cdn.jsdelivr.net/',
        'unpkg.com/',
        'cdnjs.cloudflare.com/',
        'jsbarcode',
        'html5-qrcode',
        'chart.js',
        'chart.umd',
        'leaflet',
        'cropper'
    ];

    // ── Intercept DOMContentLoaded & View Intervals ─────────────────────────────
    var origDocAddEventListener = document.addEventListener.bind(document);
    document.addEventListener = function(type, listener, options) {
        if (type === 'DOMContentLoaded' && document.readyState !== 'loading') {
            if (typeof listener === 'function') {
                if (window.__portalPjaxHydrating) {
                    pjaxDomReadyQueue.push(listener);
                } else {
                    setTimeout(function() {
                        try {
                            listener.call(document, new Event('DOMContentLoaded'));
                        } catch (err) {
                            console.warn('[PortalRouter DOMContentLoaded]', err);
                        }
                    }, 0);
                }
            }
            return;
        }
        return origDocAddEventListener(type, listener, options);
    };

    var origSetInterval = window.setInterval.bind(window);
    window.setInterval = function() {
        var id = origSetInterval.apply(window, arguments);
        if (window.__portalPjaxHydrating || window.__portalFlushingReady) {
            viewIntervals.push(id);
        }
        return id;
    };

    function clearViewIntervals() {
        while (viewIntervals.length > 0) {
            var id = viewIntervals.pop();
            try { clearInterval(id); } catch (e) {}
        }
    }

    // ── Seed Already-Loaded Stylesheets & External Libs ─────────────────────────
    function seedLoadedAssets() {
        $('link[rel="stylesheet"][href]').each(function() {
            var href = this.href;
            if (href) loadedExternalLibs.add('css:' + href.split('#')[0]);
        });
        $('script[src]').each(function() {
            var src = this.src;
            if (src) loadedExternalLibs.add('js:' + src.split('#')[0]);
        });
    }

    function isCoreScript(src) {
        if (!src) return false;
        var lower = src.toLowerCase();
        for (var i = 0; i < CORE_SCRIPT_SKIP.length; i++) {
            if (lower.indexOf(CORE_SCRIPT_SKIP[i]) !== -1) return true;
        }
        return false;
    }

    function isCacheOnceLib(src) {
        if (!src) return false;
        var lower = src.toLowerCase();
        for (var i = 0; i < CACHE_ONCE_LIBS.length; i++) {
            if (lower.indexOf(CACHE_ONCE_LIBS[i]) !== -1) return true;
        }
        return false;
    }

    // ── URL Helpers ─────────────────────────────────────────────────────────────
    function normalizeUrl(rawUrl) {
        try {
            var u = new URL(rawUrl, window.location.origin);
            u.hash = '';
            return u.toString();
        } catch (e) {
            return String(rawUrl || '');
        }
    }

    function isDownloadOrPrintUrl(urlStr, el) {
        if (el && (el.hasAttribute('download') || el.getAttribute('data-download') === 'true')) {
            return true;
        }
        if (!urlStr) return false;
        var lower = String(urlStr).toLowerCase();
        return lower.indexOf('/download') !== -1 ||
               lower.indexOf('download=') !== -1 ||
               lower.indexOf('/export') !== -1 ||
               lower.indexOf('export=') !== -1 ||
               lower.indexOf('export-') !== -1 ||
               lower.indexOf('cetak-') !== -1 ||
               lower.indexOf('/cetak') !== -1 ||
               lower.indexOf('download-contoh') !== -1 ||
               lower.indexOf('unduh') !== -1 ||
               lower.indexOf('/logout') !== -1 ||
               lower.indexOf('/login') !== -1 ||
               lower.endsWith('.csv') || lower.indexOf('.csv?') !== -1 ||
               lower.endsWith('.xlsx') || lower.indexOf('.xlsx?') !== -1 ||
               lower.endsWith('.xls') || lower.indexOf('.xls?') !== -1 ||
               lower.endsWith('.pdf') || lower.indexOf('.pdf?') !== -1 ||
               lower.endsWith('.zip') || lower.indexOf('.zip?') !== -1;
    }

    function isSeparatePwaPath(pathname) {
        var p = String(pathname || '').toLowerCase();
        return p.indexOf('/mobile/') !== -1 ||
               p.endsWith('/mobile') ||
               p.indexOf('/mobile-sarpras') !== -1;
    }

    function isEligibleLink(el) {
        if (!el || el.nodeName !== 'A') return false;
        var hrefAttr = el.getAttribute('href');
        if (!hrefAttr) return false;
        var trimmed = hrefAttr.trim();
        if (!trimmed || trimmed.charAt(0) === '#' ||
            trimmed.indexOf('javascript:') === 0 ||
            trimmed.indexOf('tel:') === 0 ||
            trimmed.indexOf('mailto:') === 0) {
            return false;
        }

        var target = (el.getAttribute('target') || '').toLowerCase();
        if (target && target !== '_self') return false;
        if (el.hasAttribute('data-no-pjax') || el.hasAttribute('data-no-spa') ||
            el.closest('[data-no-pjax]') || el.closest('[data-no-spa]')) {
            return false;
        }

        try {
            var u = new URL(el.href, window.location.origin);
            if (u.origin !== window.location.origin) return false;
            if (isSeparatePwaPath(u.pathname)) return false;
            if (isDownloadOrPrintUrl(u.toString(), el)) return false;
            if (u.pathname === window.location.pathname && u.search === window.location.search && u.hash) {
                return false;
            }
            return true;
        } catch (err) {
            return false;
        }
    }

    function isEligibleForm(form) {
        if (!form || form.nodeName !== 'FORM') return false;
        if (form._pjaxBypassSubmit) return false;

        var target = (form.getAttribute('target') || '').toLowerCase();
        if (target && target !== '_self') return false;
        if (form.hasAttribute('data-no-pjax') || form.hasAttribute('data-no-spa') ||
            form.closest('[data-no-pjax]') || form.closest('[data-no-spa]')) {
            return false;
        }

        var action = form.getAttribute('action') || window.location.href;
        try {
            var u = new URL(action, window.location.origin);
            if (u.origin !== window.location.origin) return false;
            if (isSeparatePwaPath(u.pathname)) return false;
            if (isDownloadOrPrintUrl(u.toString(), form)) return false;
            return true;
        } catch (err) {
            return false;
        }
    }

    // ── Slim Top Progress Bar ───────────────────────────────────────────────────
    function startProgressBar() {
        var $bar = $('#portal-pjax-bar');
        if (!$bar.length) return;
        clearInterval(progressTimer);
        $bar.stop(true, true).removeClass('is-loading').css({ width: '0%', opacity: 1 }).addClass('is-loading');
        var width = 18;
        $bar.css('width', width + '%');
        progressTimer = origSetInterval(function() {
            width += (88 - width) * 0.18;
            if (width >= 88) width = 88;
            $bar.css('width', width.toFixed(1) + '%');
        }, 120);
    }

    function finishProgressBar() {
        clearInterval(progressTimer);
        var $bar = $('#portal-pjax-bar');
        if (!$bar.length) return;
        $bar.css('width', '100%');
        setTimeout(function() {
            $bar.removeClass('is-loading').css({ opacity: 0 });
            setTimeout(function() {
                if (!$bar.hasClass('is-loading')) $bar.css('width', '0%');
            }, 200);
        }, 120);
    }

    // ── Top-Level let/const -> var Normalizer (Brace & String Safe) ─────────────
    function normalizeTopLevelDeclarations(code) {
        if (!code || (code.indexOf('const') === -1 && code.indexOf('let') === -1)) {
            return code;
        }

        var out = [];
        var len = code.length;
        var depth = 0;
        var tplStack = [];
        var state = 'normal'; // normal | sq | dq | tpl | linecomment | blockcomment | regex
        var prevNonWs = ';';
        var i = 0;

        while (i < len) {
            var ch = code.charAt(i);
            var next = i + 1 < len ? code.charAt(i + 1) : '';

            if (state === 'linecomment') {
                out.push(ch);
                if (ch === '\n') state = 'normal';
                i++;
                continue;
            }
            if (state === 'blockcomment') {
                out.push(ch);
                if (ch === '*' && next === '/') {
                    out.push('/');
                    state = 'normal';
                    i += 2;
                    continue;
                }
                i++;
                continue;
            }
            if (state === 'sq' || state === 'dq' || state === 'regex') {
                out.push(ch);
                if (ch === '\\' && i + 1 < len) {
                    out.push(next);
                    i += 2;
                    continue;
                }
                if ((state === 'sq' && ch === "'") ||
                    (state === 'dq' && ch === '"') ||
                    (state === 'regex' && ch === '/')) {
                    state = 'normal';
                    prevNonWs = ')';
                }
                i++;
                continue;
            }
            if (state === 'tpl') {
                out.push(ch);
                if (ch === '\\' && i + 1 < len) {
                    out.push(next);
                    i += 2;
                    continue;
                }
                if (ch === '$' && next === '{') {
                    out.push('{');
                    tplStack.push(depth);
                    depth++;
                    state = 'normal';
                    prevNonWs = '{';
                    i += 2;
                    continue;
                }
                if (ch === '`') {
                    state = 'normal';
                    prevNonWs = ')';
                }
                i++;
                continue;
            }

            // state === 'normal'
            if (ch === '/' && next === '/') {
                out.push('//');
                state = 'linecomment';
                i += 2;
                continue;
            }
            if (ch === '/' && next === '*') {
                out.push('/*');
                state = 'blockcomment';
                i += 2;
                continue;
            }
            if (ch === "'") {
                out.push(ch);
                state = 'sq';
                i++;
                continue;
            }
            if (ch === '"') {
                out.push(ch);
                state = 'dq';
                i++;
                continue;
            }
            if (ch === '`') {
                out.push(ch);
                state = 'tpl';
                i++;
                continue;
            }
            if (ch === '/' && '(=,:[!&|?{};\n'.indexOf(prevNonWs) !== -1) {
                out.push(ch);
                state = 'regex';
                i++;
                continue;
            }

            if (ch === '{') {
                depth++;
                prevNonWs = '{';
                out.push(ch);
                i++;
                continue;
            }
            if (ch === '}') {
                if (depth > 0) depth--;
                if (tplStack.length > 0 && tplStack[tplStack.length - 1] === depth) {
                    tplStack.pop();
                    state = 'tpl';
                    prevNonWs = ')';
                } else {
                    prevNonWs = '}';
                }
                out.push(ch);
                i++;
                continue;
            }

            // Replace top-level `const` / `let` at brace depth 0
            if (depth === 0 && (ch === 'c' || ch === 'l')) {
                var prevChar = i > 0 ? code.charAt(i - 1) : '';
                var isBoundaryBefore = !prevChar || !/[a-zA-Z0-9_$.]/.test(prevChar);
                if (isBoundaryBefore) {
                    if (ch === 'c' && code.substr(i, 5) === 'const') {
                        var afterConst = code.charAt(i + 5);
                        if (afterConst && /[\s{\[]/.test(afterConst)) {
                            out.push('var');
                            prevNonWs = 'r';
                            i += 5;
                            continue;
                        }
                    } else if (ch === 'l' && code.substr(i, 3) === 'let') {
                        var afterLet = code.charAt(i + 3);
                        if (afterLet && /[\s{\[]/.test(afterLet)) {
                            out.push('var');
                            prevNonWs = 'r';
                            i += 3;
                            continue;
                        }
                    }
                }
            }

            if (!/\s/.test(ch)) {
                prevNonWs = ch;
            } else if (ch === '\n' && prevNonWs !== ',' && prevNonWs !== '.' && prevNonWs !== '=') {
                prevNonWs = '\n';
            }
            out.push(ch);
            i++;
        }

        return out.join('');
    }

    // ── Extract Styles & Scripts from HTML Container ────────────────────────────
    function extractContainerPayload(containerEl) {
        if (!containerEl) return { html: '', scripts: [], styles: [] };
        var clone = containerEl.cloneNode(true);
        var scripts = [];
        var styles = [];

        $(clone).find('link[rel="stylesheet"]').each(function() {
            if (this.href) styles.push(this.href);
            this.remove();
        });

        $(clone).find('script').each(function() {
            scripts.push({
                src: this.getAttribute('src') || '',
                type: this.getAttribute('type') || '',
                code: this.textContent || ''
            });
            this.remove();
        });

        return {
            html: clone.innerHTML,
            scripts: scripts,
            styles: styles
        };
    }

    function parseHtmlDocument(htmlText, responseUrl) {
        if (!htmlText || typeof htmlText !== 'string') return null;
        var parser = new DOMParser();
        var doc = parser.parseFromString(htmlText, 'text/html');

        var shellWrapper = doc.getElementById('portal-shell-wrapper');
        var mainEl = doc.getElementById('main-content');
        if (!shellWrapper || !mainEl) return null;

        var headStyles = [];
        $(doc.head).find('link[rel="stylesheet"][href]').each(function() {
            if (this.href) headStyles.push(this.href);
        });

        var sidebarSlotEl = doc.getElementById('portal-sidebar-slot');
        var sidebarPayload = sidebarSlotEl
            ? extractContainerPayload(sidebarSlotEl)
            : { html: '', scripts: [], styles: [] };

        var mainPayload = extractContainerPayload(mainEl);

        var extraJsEl = doc.getElementById('portal-extra-js');
        var extraPayload = extraJsEl
            ? extractContainerPayload(extraJsEl)
            : { html: '', scripts: [], styles: [] };

        var topbarLeftEl = doc.getElementById('portal-topbar-left');
        var guruSlotEl = doc.getElementById('portal-topbar-guru-slot');
        var csrfMetaEl = doc.querySelector('meta[name="csrf-token"]');

        return {
            finalUrl: responseUrl || '',
            title: doc.title || document.title,
            csrfToken: csrfMetaEl ? csrfMetaEl.getAttribute('content') : '',
            wrapperClass: shellWrapper.className || '',
            sidebarHtml: sidebarPayload.html,
            sidebarTitle: $.trim($(doc.getElementById('sidebar')).find('h1').first().text() || ''),
            topbarLeftHtml: topbarLeftEl ? topbarLeftEl.innerHTML : null,
            guruSlotHtml: guruSlotEl ? guruSlotEl.innerHTML : null,
            mainHtml: mainPayload.html,
            extraHtml: extraPayload.html,
            styles: headStyles.concat(sidebarPayload.styles, mainPayload.styles, extraPayload.styles),
            scripts: sidebarPayload.scripts.concat(mainPayload.scripts, extraPayload.scripts),
            timestamp: Date.now()
        };
    }

    // ── Asset Loaders ───────────────────────────────────────────────────────────
    function loadMissingStylesheets(hrefs) {
        var promises = [];
        $.each(hrefs || [], function(_, rawHref) {
            if (!rawHref) return;
            var clean = rawHref.split('#')[0];
            var key = 'css:' + clean;
            if (loadedExternalLibs.has(key)) return;
            loadedExternalLibs.add(key);

            var p = new Promise(function(resolve) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = rawHref;
                var done = false;
                var finish = function() {
                    if (!done) { done = true; resolve(); }
                };
                link.onload = finish;
                link.onerror = finish;
                setTimeout(finish, 2000);
                document.head.appendChild(link);
            });
            promises.push(p);
        });
        return Promise.all(promises);
    }

    function executeScriptsSequentially(scripts) {
        var idx = 0;

        function next() {
            if (idx >= scripts.length) {
                return Promise.resolve();
            }
            var item = scripts[idx++];

            if (item.type && item.type !== 'text/javascript' && item.type !== 'application/javascript' && item.type !== 'module') {
                return next();
            }

            if (item.src) {
                if (isCoreScript(item.src)) {
                    return next();
                }

                var absSrc = item.src;
                try { absSrc = new URL(item.src, window.location.origin).toString(); } catch (e) {}
                var cleanSrc = absSrc.split('#')[0];
                var libKey = 'js:' + cleanSrc;

                if (isCacheOnceLib(cleanSrc) && loadedExternalLibs.has(libKey)) {
                    return next();
                }

                return new Promise(function(resolve) {
                    var s = document.createElement('script');
                    s.src = absSrc;
                    if (item.type) s.type = item.type;
                    var finished = false;
                    var done = function() {
                        if (!finished) {
                            finished = true;
                            if (isCacheOnceLib(cleanSrc)) loadedExternalLibs.add(libKey);
                            resolve();
                        }
                    };
                    s.onload = done;
                    s.onerror = done;
                    setTimeout(done, 5000);
                    document.body.appendChild(s);
                }).then(next);
            }

            if (item.code && $.trim(item.code) !== '') {
                try {
                    var safeCode = normalizeTopLevelDeclarations(item.code);
                    var inlineScript = document.createElement('script');
                    if (item.type) inlineScript.type = item.type;
                    inlineScript.textContent = safeCode;
                    document.body.appendChild(inlineScript);
                    inlineScript.parentNode.removeChild(inlineScript);
                } catch (err) {
                    console.warn('[PortalRouter Inline Script]', err);
                }
            }
            return next();
        }

        return next();
    }

    // ── Loading Modal Helper for Data & Filter Navigation ───────────────────────
    function showDataLoadingModal(targetUrl, options) {
        if (!window.LoadingModal || typeof window.LoadingModal.show !== 'function') return 0;
        options = options || {};
        var isFilter = !!options.isFilter || /[?&](filter|search|q|jenjang|unit|kelas|tanggal|bulan|tahun|status|tab|page)=/i.test(String(targetUrl || ''));
        if (isFilter) {
            window.LoadingModal.show('Memuat Data...', 'Sedang memfilter data yang dipilih...');
        } else {
            window.LoadingModal.show(
                options.loadingTitle || 'Memuat Halaman...',
                options.loadingSub || 'Sedang mengambil data, mohon tunggu sebentar...'
            );
        }
        return Date.now();
    }

    function hideLoadingModalSmooth(startedAt) {
        if (!window.LoadingModal || typeof window.LoadingModal.hide !== 'function') return;
        if (!startedAt) {
            window.LoadingModal.hide();
            return;
        }
        var elapsed = Date.now() - startedAt;
        var remaining = Math.max(0, 120 - elapsed);
        if (remaining > 0) {
            setTimeout(function() {
                if (window.LoadingModal && typeof window.LoadingModal.hide === 'function') {
                    window.LoadingModal.hide();
                }
            }, remaining);
        } else {
            window.LoadingModal.hide();
        }
    }

    // ── Pre-Swap Cleanup & Post-Swap Hydration ──────────────────────────────────
    function cleanupBeforeSwap() {
        clearViewIntervals();

        // Close mobile sidebar drawer & user dropdown if open
        if (typeof window.toggleSidebar === 'function' && window.innerWidth < 1024) {
            window.toggleSidebar(false);
        }
        var $userMenu = $('#user-dropdown-menu');
        if ($userMenu.length && !$userMenu.hasClass('hidden')) {
            window.toggleUserDropdown && window.toggleUserDropdown();
        }

        // Remove floating dropdown menus moved onto document.body (e.g. SPMB table menus)
        $('body > [id^="action-menu-"]').remove();

        // Reset scroll lock / non-loading modals
        document.body.style.overflow = '';
        if (window.ButtonLoader && typeof window.ButtonLoader.stopAll === 'function') {
            window.ButtonLoader.stopAll();
        }
        if (window.Swal && typeof window.Swal.isVisible === 'function' && window.Swal.isVisible()) {
            try { window.Swal.close(); } catch (e) {}
        }

        $(document).trigger('portal:pjax:before');
    }

    function hydrateParsedPage(parsed, options) {
        options = options || {};
        cleanupBeforeSwap();

        // 1. Update document title & CSRF meta
        if (parsed.title) document.title = parsed.title;
        if (parsed.csrfToken) {
            $('meta[name="csrf-token"]').attr('content', parsed.csrfToken);
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': parsed.csrfToken } });
        }

        // 2. Preserve sidebar scroll if staying within the same module sidebar
        var prevSidebarTitle = $.trim($('#sidebar h1').first().text() || '');
        var prevSidebarScroll = $('#sidebar .overflow-y-auto').scrollTop() || 0;

        // 3. Swap sidebar slot & shell wrapper margins
        $('#portal-sidebar-slot').html(parsed.sidebarHtml || '');
        if (parsed.wrapperClass) {
            $('#portal-shell-wrapper').attr('class', parsed.wrapperClass);
        }
        if (prevSidebarTitle && prevSidebarTitle === parsed.sidebarTitle) {
            $('#sidebar .overflow-y-auto').scrollTop(prevSidebarScroll);
        }

        // 4. Update topbar title/breadcrumbs & Portal Guru button
        if (parsed.topbarLeftHtml !== null) {
            $('#portal-topbar-left').html(parsed.topbarLeftHtml);
        }
        if (parsed.guruSlotHtml !== null) {
            $('#portal-topbar-guru-slot').html(parsed.guruSlotHtml);
        }

        // 5. Clear desktop search when moving across distinct paths
        if (options.clearSearch) {
            $('#global-search').val('');
        }

        // 6. Swap main content with fast GPU enter animation
        var $main = $('#main-content');
        $main.removeClass('portal-pjax-enter').html(parsed.mainHtml || '');
        void $main[0].offsetWidth;
        $main.addClass('portal-pjax-enter');

        $('#portal-extra-js').html(parsed.extraHtml || '');

        if (options.scrollToTop !== false) {
            window.scrollTo({ top: 0, behavior: 'instant' });
        }

        // 7. Load stylesheets, execute scripts sequentially, then run queued DOMContentLoaded handlers
        pjaxDomReadyQueue = [];
        window.__portalPjaxHydrating = true;

        return loadMissingStylesheets(parsed.styles)
            .then(function() {
                return executeScriptsSequentially(parsed.scripts);
            })
            .then(function() {
                window.__portalPjaxHydrating = false;
                window.__portalFlushingReady = true;

                while (pjaxDomReadyQueue.length > 0) {
                    var cb = pjaxDomReadyQueue.shift();
                    try {
                        cb.call(document, new Event('DOMContentLoaded'));
                    } catch (err) {
                        console.warn('[PortalRouter DOMReady]', err);
                    }
                }
                window.__portalFlushingReady = false;

                if (window.SearchableSelect && typeof window.SearchableSelect.init === 'function') {
                    window.SearchableSelect.init(document.getElementById('main-content'));
                }
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    window.lucide.createIcons();
                }
                if (typeof window.handleDeviceResolution === 'function') {
                    window.handleDeviceResolution();
                }

                hideLoadingModalSmooth(options.modalStartedAt || 0);
                if (window.ButtonLoader && typeof window.ButtonLoader.stopAll === 'function') {
                    window.ButtonLoader.stopAll();
                }

                $(document).trigger('portal:pjax:complete', [{ url: parsed.finalUrl || window.location.href }]);
            });
    }

    // ── jQuery AJAX Transport (Omits X-Requested-With, Tracks Final Redirect URL)
    function createPjaxXhr() {
        var xhr = new window.XMLHttpRequest();
        var origSetRequestHeader = xhr.setRequestHeader;
        xhr.setRequestHeader = function(name, value) {
            if (String(name).toLowerCase() === 'x-requested-with') return;
            return origSetRequestHeader.call(this, name, value);
        };
        xhr.addEventListener('loadend', function() {
            try {
                xhr._finalResponseUrl = xhr.responseURL || '';
            } catch (e) {}
        });
        return xhr;
    }

    function trimCacheMap(map) {
        while (map.size > MAX_CACHE_ENTRIES) {
            var firstKey = map.keys().next().value;
            map.delete(firstKey);
        }
    }

    function invalidateCache() {
        prefetchCache.clear();
        backCache.clear();
    }

    function fetchPageViaJQuery(targetUrl, options) {
        options = options || {};
        var norm = normalizeUrl(targetUrl);

        if (inFlightRequests.has(norm)) {
            return inFlightRequests.get(norm);
        }

        var dfd = $.Deferred();
        var jqXHR = $.ajax({
            url: targetUrl,
            type: 'GET',
            dataType: 'text',
            global: false,
            xhr: createPjaxXhr,
            headers: {
                'X-Portal-Pjax': '1',
                'Accept': 'text/html,application/xhtml+xml'
            }
        });

        jqXHR.done(function(htmlText, textStatus, xhrObj) {
            inFlightRequests.delete(norm);
            var contentType = (xhrObj.getResponseHeader('Content-Type') || '').toLowerCase();
            var contentDisp = (xhrObj.getResponseHeader('Content-Disposition') || '').toLowerCase();
            var finalUrl = xhrObj._finalResponseUrl || targetUrl;

            if ((contentType && contentType.indexOf('text/html') === -1) ||
                contentDisp.indexOf('attachment') !== -1 ||
                finalUrl.indexOf('/login') !== -1) {
                dfd.reject({ fallbackHardRedirect: finalUrl });
                return;
            }

            var parsed = parseHtmlDocument(htmlText, finalUrl);
            if (!parsed) {
                dfd.reject({ fallbackHardRedirect: finalUrl });
                return;
            }

            prefetchCache.set(norm, parsed);
            backCache.set(norm, parsed);
            trimCacheMap(prefetchCache);
            trimCacheMap(backCache);
            dfd.resolve(parsed);
        }).fail(function(xhrObj, textStatus) {
            inFlightRequests.delete(norm);
            if (textStatus === 'abort') {
                dfd.reject({ aborted: true });
                return;
            }
            dfd.reject({ fallbackHardRedirect: targetUrl });
        });

        var promise = dfd.promise();
        promise._jqXHR = jqXHR;
        inFlightRequests.set(norm, promise);
        return promise;
    }

    // ── Public Navigation & Form Submission ─────────────────────────────────────
    function navigate(targetUrl, options) {
        options = options || {};
        var norm = normalizeUrl(targetUrl);
        var prevPath = window.location.pathname;
        var nextPath = prevPath;
        try { nextPath = new URL(targetUrl, window.location.origin).pathname; } catch (e) {}

        var navId = ++currentNavId;

        if (activePrefetchXhr && activePrefetchXhr.abort) {
            try { activePrefetchXhr.abort(); } catch (e) {}
            activePrefetchXhr = null;
        }
        if (activeNavXhr && activeNavXhr.abort) {
            try { activeNavXhr.abort(); } catch (e) {}
            activeNavXhr = null;
        }

        // Check fresh hover-prefetch cache or popstate cache
        var now = Date.now();
        var cached = null;
        if (!options.forceReload) {
            var pEntry = prefetchCache.get(norm);
            if (pEntry && (now - pEntry.timestamp) < PREFETCH_TTL_MS) {
                cached = pEntry;
            } else if (options.isPopstate) {
                var bEntry = backCache.get(norm);
                if (bEntry && (now - bEntry.timestamp) < BACK_CACHE_TTL_MS) {
                    cached = bEntry;
                }
            }
        }

        if (cached) {
            var finalCachedUrl = cached.finalUrl || targetUrl;
            if (options.pushState !== false) {
                window.history.pushState({ pjaxUrl: finalCachedUrl }, cached.title || '', finalCachedUrl);
            }
            return hydrateParsedPage(cached, {
                scrollToTop: options.scrollToTop !== false,
                clearSearch: prevPath !== nextPath,
                modalStartedAt: 0
            });
        }

        var modalStartedAt = showDataLoadingModal(targetUrl, options);

        startProgressBar();
        var reqPromise = fetchPageViaJQuery(targetUrl);
        activeNavXhr = reqPromise._jqXHR || null;

        return reqPromise.then(function(parsed) {
            activeNavXhr = null;
            if (navId !== currentNavId) return;
            finishProgressBar();

            var resolvedUrl = parsed.finalUrl || targetUrl;
            if (options.pushState !== false) {
                window.history.pushState({ pjaxUrl: resolvedUrl }, parsed.title || '', resolvedUrl);
            }
            return hydrateParsedPage(parsed, {
                scrollToTop: options.scrollToTop !== false,
                clearSearch: prevPath !== nextPath,
                modalStartedAt: modalStartedAt
            });
        }, function(err) {
            activeNavXhr = null;
            if (err && err.aborted) return;
            finishProgressBar();
            if (window.LoadingModal && typeof window.LoadingModal.hide === 'function') {
                window.LoadingModal.hide();
            }
            if (navId !== currentNavId) return;
            window.location.href = (err && err.fallbackHardRedirect) ? err.fallbackHardRedirect : targetUrl;
        });
    }

    function submitForm(form, submitter) {
        if (!isEligibleForm(form)) {
            form._pjaxBypassSubmit = true;
            form.submit();
            form._pjaxBypassSubmit = false;
            return;
        }

        var method = String($(form).attr('method') || 'GET').toUpperCase();
        var actionAttr = $(form).attr('action') || window.location.href;
        var resolvedAction = actionAttr;
        try { resolvedAction = new URL(actionAttr, window.location.origin).toString(); } catch (e) {}

        if (method === 'GET') {
            var params = new URLSearchParams();
            var pairs = $(form).serializeArray();
            $.each(pairs, function(_, pair) {
                params.append(pair.name, pair.value);
            });
            if (submitter && submitter.name) {
                params.append(submitter.name, submitter.value || '');
            }
            var targetUrlObj = new URL(resolvedAction, window.location.origin);
            targetUrlObj.search = params.toString();
            return navigate(targetUrlObj.toString(), { isFilter: true });
        }

        // POST form submission via jQuery AJAX (FormData) - Button Loading Only (no blocking modal)
        invalidateCache();
        if (window.LoadingModal && typeof window.LoadingModal.hide === 'function') {
            window.LoadingModal.hide();
        }
        var navId = ++currentNavId;
        var formData;
        try {
            formData = submitter ? new FormData(form, submitter) : new FormData(form);
        } catch (err) {
            formData = new FormData(form);
            if (submitter && submitter.name) {
                formData.append(submitter.name, submitter.value || '');
            }
        }

        var activeBtn = submitter
            || form._pjaxTriggerBtn
            || window.__lastActionBtn
            || form.querySelector('button[type="submit"], input[type="submit"], button:not([type="button"])');
        form._pjaxTriggerBtn = null;
        if (activeBtn && window.ButtonLoader) {
            window.ButtonLoader.start(activeBtn, null, resolvedAction);
        }

        startProgressBar();

        $.ajax({
            url: resolvedAction,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'text',
            global: false,
            xhr: createPjaxXhr,
            headers: {
                'X-Portal-Pjax': '1',
                'Accept': 'text/html,application/xhtml+xml'
            }
        }).done(function(htmlText, textStatus, xhrObj) {
            finishProgressBar();
            if (window.LoadingModal && typeof window.LoadingModal.hide === 'function') {
                window.LoadingModal.hide();
            }
            if (navId !== currentNavId) {
                if (activeBtn && window.ButtonLoader) window.ButtonLoader.stop(activeBtn);
                return;
            }

            var finalUrl = xhrObj._finalResponseUrl || resolvedAction;
            var contentType = (xhrObj.getResponseHeader('Content-Type') || '').toLowerCase();
            var contentDisp = (xhrObj.getResponseHeader('Content-Disposition') || '').toLowerCase();

            if (finalUrl.indexOf('/login') !== -1 || contentDisp.indexOf('attachment') !== -1) {
                if (activeBtn && window.ButtonLoader) window.ButtonLoader.stop(activeBtn);
                window.location.href = finalUrl;
                return;
            }

            if (contentType.indexOf('text/html') !== -1) {
                var parsed = parseHtmlDocument(htmlText, finalUrl);
                if (parsed) {
                    window.history.pushState({ pjaxUrl: finalUrl }, parsed.title || '', finalUrl);
                    hydrateParsedPage(parsed, { scrollToTop: true, clearSearch: false });
                    return;
                }
                if (activeBtn && window.ButtonLoader) window.ButtonLoader.stop(activeBtn);
                // Response was standalone HTML (e.g. print view returned by POST) - render safely without re-POSTing
                document.open();
                document.write(htmlText);
                document.close();
                return;
            }

            if (activeBtn && window.ButtonLoader) window.ButtonLoader.stop(activeBtn);
            window.location.href = finalUrl;
        }).fail(function() {
            finishProgressBar();
            if (activeBtn && window.ButtonLoader) window.ButtonLoader.stop(activeBtn);
            if (window.LoadingModal && typeof window.LoadingModal.hide === 'function') {
                window.LoadingModal.hide();
            }
            // Fallback: submit natively if AJAX transport failed
            form._pjaxBypassSubmit = true;
            form.submit();
            form._pjaxBypassSubmit = false;
        });
    }

    // ── Prefetch on Hover / Touchstart (Resource-Saving Guard) ──────────────────
    function canPrefetch() {
        if (document.hidden) return false;
        var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (conn) {
            if (conn.saveData) return false;
            if (/2g/.test(String(conn.effectiveType || '').toLowerCase())) return false;
        }
        return true;
    }

    function schedulePrefetch(href) {
        if (!canPrefetch()) return;
        var norm = normalizeUrl(href);
        if (activeNavXhr) return;
        var cached = prefetchCache.get(norm);
        if (cached && (Date.now() - cached.timestamp) < PREFETCH_TTL_MS) return;
        if (inFlightRequests.has(norm)) return;

        clearTimeout(hoverTimer);
        hoverTimer = setTimeout(function() {
            if (activeNavXhr) return;
            if (activePrefetchXhr && activePrefetchXhr.abort) {
                try { activePrefetchXhr.abort(); } catch (e) {}
            }
            var p = fetchPageViaJQuery(href);
            activePrefetchXhr = p._jqXHR || null;
            p.always(function() {
                if (activePrefetchXhr === p._jqXHR) activePrefetchXhr = null;
            });
        }, 65);
    }

    // ── Hook Programmatic form.submit() ─────────────────────────────────────────
    var origFormSubmit = HTMLFormElement.prototype.submit;
    HTMLFormElement.prototype.submit = function() {
        if (isEligibleForm(this)) {
            submitForm(this, null);
            return;
        }
        return origFormSubmit.apply(this, arguments);
    };

    // ── Event Bindings (jQuery) ─────────────────────────────────────────────────
    function init() {
        if (window.__portalRouterInitialized) return;
        window.__portalRouterInitialized = true;

        seedLoadedAssets();

        // Intercept internal link clicks
        $(document).on('click.portalPjax', 'a[href]', function(e) {
            if (e.isDefaultPrevented()) return;
            if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (!isEligibleLink(this)) return;

            e.preventDefault();
            navigate(this.href);
        });

        // Hover & touchstart prefetching
        $(document).on('mouseenter.portalPjax touchstart.portalPjax', 'a[href]', function() {
            if (!isEligibleLink(this)) return;
            schedulePrefetch(this.href);
        });

        $(document).on('mouseleave.portalPjax', 'a[href]', function() {
            clearTimeout(hoverTimer);
        });

        // Intercept form submissions (GET filters & eligible POST actions)
        $(document).on('submit.portalPjax', 'form', function(e) {
            if (e.isDefaultPrevented()) return;
            if (!isEligibleForm(this)) return;

            e.preventDefault();
            var submitter = e.originalEvent && e.originalEvent.submitter ? e.originalEvent.submitter : null;
            submitForm(this, submitter);
        });

        // Invalidate prefetch cache whenever any background AJAX POST succeeds
        $(document).on('ajaxSuccess.portalPjax', function(_e, _xhr, settings) {
            if (settings && String(settings.type || '').toUpperCase() === 'POST') {
                invalidateCache();
            }
        });

        // Browser Back / Forward navigation
        $(window).on('popstate.portalPjax', function() {
            if (!isSeparatePwaPath(window.location.pathname)) {
                navigate(window.location.href, {
                    pushState: false,
                    scrollToTop: false,
                    isPopstate: true
                });
            }
        });
    }

    $(document).ready(init);

    window.PortalRouter = {
        init: init,
        navigate: navigate,
        submitForm: submitForm,
        reload: function() {
            invalidateCache();
            return navigate(window.location.href, { pushState: false, forceReload: true, scrollToTop: false });
        },
        invalidateCache: invalidateCache,
        isEligibleLink: isEligibleLink,
        isEligibleForm: isEligibleForm
    };

})(window, document, window.jQuery);
