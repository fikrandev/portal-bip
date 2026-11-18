/**
 * Portal BIP - Sarpras Mobile jQuery PJAX Router
 * Menghilangkan reload & splash screen antar halaman di modul mobile-sarpras menggunakan jQuery AJAX.
 */

(function(window, document, $) {
    'use strict';

    if (!$) return;

    var pageCache = new Map();
    var inFlight = new Map();
    var loadedScripts = new Set();
    var domReadyQueue = [];
    var CACHE_TTL_MS = 10000;
    var MAX_CACHE_ENTRIES = 15;
    var progressTimer = null;
    var activeNavXhr = null;
    var activeUrl = null;
    var hoverTimer = null;

    // Intercept DOMContentLoaded after initial load
    var origAddEvent = document.addEventListener.bind(document);
    document.addEventListener = function(type, listener, options) {
        if (type === 'DOMContentLoaded' && document.readyState !== 'loading') {
            if (typeof listener === 'function') {
                if (window.__sarprasPjaxHydrating) {
                    domReadyQueue.push(listener);
                } else {
                    setTimeout(function() {
                        try { listener.call(document, new Event('DOMContentLoaded')); } catch (e) {}
                    }, 0);
                }
            }
            return;
        }
        return origAddEvent(type, listener, options);
    };

    function seedLoadedScripts() {
        $('script[src]').each(function() {
            if (this.src) loadedScripts.add(this.src.split('#')[0]);
        });
    }

    function normalizeUrl(rawUrl) {
        try {
            var u = new URL(rawUrl, window.location.origin);
            u.hash = '';
            return u.origin + u.pathname.replace(/\/+$/, '') + u.search;
        } catch (e) {
            return String(rawUrl || '');
        }
    }

    function isEligibleLink(el) {
        if (!el || el.nodeName !== 'A') return false;
        var href = el.getAttribute('href');
        if (!href) return false;
        var t = href.trim();
        if (!t || t.charAt(0) === '#' || t.indexOf('javascript:') === 0 || t.indexOf('tel:') === 0 || t.indexOf('mailto:') === 0) {
            return false;
        }
        if (el.hasAttribute('data-no-spa') || el.hasAttribute('data-no-pjax') || el.hasAttribute('download')) return false;
        var target = (el.getAttribute('target') || '').toLowerCase();
        if (target && target !== '_self') return false;

        try {
            var u = new URL(el.href, window.location.origin);
            if (u.origin !== window.location.origin) return false;
            var p = u.pathname.toLowerCase();
            if (p.indexOf('/logout') !== -1 || p.indexOf('/login') !== -1) return false;
            return p.indexOf('/mobile-sarpras') !== -1;
        } catch (e) {
            return false;
        }
    }

    function isEligibleForm(form) {
        if (!form || form.nodeName !== 'FORM') return false;
        if (form._sarprasBypassSubmit) return false;
        if (form.hasAttribute('data-no-spa') || form.hasAttribute('data-no-pjax')) return false;
        var target = (form.getAttribute('target') || '').toLowerCase();
        if (target && target !== '_self') return false;

        var action = form.getAttribute('action') || window.location.href;
        try {
            var u = new URL(action, window.location.origin);
            if (u.origin !== window.location.origin) return false;
            var p = u.pathname.toLowerCase();
            if (p.indexOf('/logout') !== -1 || p.indexOf('/login') !== -1) return false;
            return p.indexOf('/mobile-sarpras') !== -1;
        } catch (e) {
            return false;
        }
    }

    function startProgressBar() {
        clearTimeout(progressTimer);
        var $bar = $('#spa-progress-bar');
        if (!$bar.length) return;
        progressTimer = setTimeout(function() {
            $bar.removeClass('is-finishing').addClass('is-loading');
        }, 35);
    }

    function finishProgressBar() {
        clearTimeout(progressTimer);
        var $bar = $('#spa-progress-bar');
        if (!$bar.length) return;
        if ($bar.hasClass('is-loading')) {
            $bar.removeClass('is-loading').addClass('is-finishing');
            setTimeout(function() { $bar.removeClass('is-finishing'); }, 240);
        }
    }

    function normalizeTopLevelCode(code) {
        if (!code) return '';
        return code.replace(/^(\s{0,4})(let|const)\s+/gm, '$1var ');
    }

    function parseHtmlDocument(htmlText, finalUrl) {
        if (!htmlText) return null;
        var parser = new DOMParser();
        var doc = parser.parseFromString(htmlText, 'text/html');
        var contentEl = doc.getElementById('sarpras-pjax-content');
        if (!contentEl) return null;

        var clone = contentEl.cloneNode(true);
        var scripts = [];
        $(clone).find('script').each(function() {
            scripts.push({
                src: this.getAttribute('src') || '',
                code: this.textContent || ''
            });
            this.remove();
        });

        var headerLeftEl = doc.getElementById('sarpras-header-left');
        var bottomNavEl = doc.getElementById('sarpras-bottom-nav');
        var flashEl = doc.getElementById('sarpras-flash-data');

        return {
            finalUrl: finalUrl || '',
            title: doc.title || 'Sarpras Mobile',
            contentHtml: clone.innerHTML,
            headerLeftHtml: headerLeftEl ? headerLeftEl.innerHTML : null,
            bottomNavHtml: bottomNavEl ? bottomNavEl.innerHTML : null,
            flash: flashEl ? {
                success: flashEl.getAttribute('data-success') || '',
                error: flashEl.getAttribute('data-error') || '',
                warning: flashEl.getAttribute('data-warning') || '',
                info: flashEl.getAttribute('data-info') || ''
            } : null,
            scripts: scripts,
            timestamp: Date.now()
        };
    }

    function executeScriptsSequentially(scripts) {
        var idx = 0;
        function next() {
            if (idx >= scripts.length) return Promise.resolve();
            var item = scripts[idx++];
            if (item.src) {
                var absSrc = item.src;
                try { absSrc = new URL(item.src, window.location.origin).toString(); } catch (e) {}
                var clean = absSrc.split('#')[0];
                if (loadedScripts.has(clean)) return next();

                return new Promise(function(resolve) {
                    var s = document.createElement('script');
                    s.src = absSrc;
                    var done = false;
                    var fin = function() {
                        if (!done) {
                            done = true;
                            loadedScripts.add(clean);
                            resolve();
                        }
                    };
                    s.onload = fin;
                    s.onerror = fin;
                    setTimeout(fin, 4000);
                    document.body.appendChild(s);
                }).then(next);
            }
            if (item.code && $.trim(item.code) !== '') {
                try {
                    var el = document.createElement('script');
                    el.textContent = normalizeTopLevelCode(item.code);
                    document.body.appendChild(el);
                    el.parentNode.removeChild(el);
                } catch (err) {
                    console.warn('[SarprasRouter Script]', err);
                }
            }
            return next();
        }
        return next();
    }

    function stopActiveScannerIfAny() {
        try {
            if (window.html5Qrcode && window.isScanning && typeof window.html5Qrcode.stop === 'function') {
                window.html5Qrcode.stop().catch(function() {});
                window.isScanning = false;
            }
        } catch (e) {}
    }

    function createCleanXhr() {
        var xhr = new window.XMLHttpRequest();
        var origSet = xhr.setRequestHeader;
        xhr.setRequestHeader = function(k, v) {
            if (String(k).toLowerCase() === 'x-requested-with') return;
            return origSet.call(this, k, v);
        };
        xhr.addEventListener('loadend', function() {
            try { xhr._finalResponseUrl = xhr.responseURL || ''; } catch (e) {}
        });
        return xhr;
    }

    function hydrateParsed(parsed, options) {
        options = options || {};
        stopActiveScannerIfAny();
        $(document).trigger('sarpras:pjax:before');

        if (parsed.title) document.title = parsed.title;
        if (parsed.headerLeftHtml !== null) {
            $('#sarpras-header-left').html(parsed.headerLeftHtml);
        }
        if (parsed.bottomNavHtml !== null) {
            $('#sarpras-bottom-nav').html(parsed.bottomNavHtml);
        }

        var $content = $('#sarpras-pjax-content');
        $content.removeClass('sarpras-pjax-enter').html(parsed.contentHtml);
        void $content[0].offsetWidth;
        $content.addClass('sarpras-pjax-enter');

        if (options.scrollToTop !== false) {
            var mainEl = document.getElementById('main-content');
            if (mainEl) mainEl.scrollTop = 0;
        }

        domReadyQueue = [];
        window.__sarprasPjaxHydrating = true;

        return executeScriptsSequentially(parsed.scripts).then(function() {
            window.__sarprasPjaxHydrating = false;
            while (domReadyQueue.length > 0) {
                var cb = domReadyQueue.shift();
                try { cb.call(document, new Event('DOMContentLoaded')); } catch (err) {}
            }
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
            if (parsed.flash && typeof window.showToast === 'function') {
                if (parsed.flash.success) window.showToast(parsed.flash.success, 'success');
                if (parsed.flash.error) window.showToast(parsed.flash.error, 'error');
                if (parsed.flash.warning) window.showToast(parsed.flash.warning, 'warning');
                if (parsed.flash.info) window.showToast(parsed.flash.info, 'info');
            }
            $(document).trigger('sarpras:pjax:complete', [{ url: parsed.finalUrl }]);
        });
    }

    function fetchPage(targetUrl) {
        var norm = normalizeUrl(targetUrl);
        if (inFlight.has(norm)) return inFlight.get(norm);

        var dfd = $.Deferred();
        var jqXHR = $.ajax({
            url: targetUrl,
            type: 'GET',
            dataType: 'text',
            global: false,
            xhr: createCleanXhr,
            headers: {
                'X-Portal-Pjax': '1',
                'Accept': 'text/html,application/xhtml+xml'
            }
        });

        jqXHR.done(function(htmlText, _status, xhrObj) {
            inFlight.delete(norm);
            var finalUrl = xhrObj._finalResponseUrl || targetUrl;
            if (finalUrl.indexOf('/login') !== -1) {
                dfd.reject({ fallbackUrl: finalUrl });
                return;
            }
            var parsed = parseHtmlDocument(htmlText, finalUrl);
            if (!parsed) {
                dfd.reject({ fallbackUrl: finalUrl });
                return;
            }
            pageCache.set(norm, parsed);
            while (pageCache.size > MAX_CACHE_ENTRIES) {
                var oldestKey = pageCache.keys().next().value;
                pageCache.delete(oldestKey);
            }
            dfd.resolve(parsed);
        }).fail(function(_xhr, textStatus) {
            inFlight.delete(norm);
            if (textStatus === 'abort') {
                dfd.reject({ aborted: true });
            } else {
                dfd.reject({ fallbackUrl: targetUrl });
            }
        });

        var p = dfd.promise();
        p._jqXHR = jqXHR;
        inFlight.set(norm, p);
        return p;
    }

    function navigate(targetUrl, options) {
        options = options || {};
        var norm = normalizeUrl(targetUrl);
        activeUrl = norm;

        if (activeNavXhr && activeNavXhr.abort) {
            try { activeNavXhr.abort(); } catch (e) {}
            activeNavXhr = null;
        }

        var cached = !options.forceReload ? pageCache.get(norm) : null;
        if (cached && (Date.now() - cached.timestamp) < CACHE_TTL_MS) {
            var resolved = cached.finalUrl || targetUrl;
            if (options.pushState !== false) {
                window.history.pushState({ sarprasUrl: resolved }, cached.title || '', resolved);
            }
            return hydrateParsed(cached, options);
        }

        startProgressBar();
        var req = fetchPage(targetUrl);
        activeNavXhr = req._jqXHR || null;

        return req.then(function(parsed) {
            activeNavXhr = null;
            finishProgressBar();
            if (activeUrl !== norm) return;
            var finalUrl = parsed.finalUrl || targetUrl;
            if (options.pushState !== false) {
                window.history.pushState({ sarprasUrl: finalUrl }, parsed.title || '', finalUrl);
            }
            return hydrateParsed(parsed, options);
        }, function(err) {
            activeNavXhr = null;
            if (err && err.aborted) return;
            finishProgressBar();
            window.location.href = (err && err.fallbackUrl) ? err.fallbackUrl : targetUrl;
        });
    }

    function submitForm(form, submitter) {
        if (!isEligibleForm(form)) {
            form._sarprasBypassSubmit = true;
            form.submit();
            form._sarprasBypassSubmit = false;
            return;
        }

        var method = String($(form).attr('method') || 'GET').toUpperCase();
        var actionAttr = $(form).attr('action') || window.location.href;
        var resolvedAction = new URL(actionAttr, window.location.origin).toString();

        if (method === 'GET') {
            var params = new URLSearchParams();
            $.each($(form).serializeArray(), function(_, pair) {
                params.append(pair.name, pair.value);
            });
            if (submitter && submitter.name) params.append(submitter.name, submitter.value || '');
            var targetObj = new URL(resolvedAction, window.location.origin);
            targetObj.search = params.toString();
            return navigate(targetObj.toString());
        }

        pageCache.clear();
        var formData;
        try {
            formData = submitter ? new FormData(form, submitter) : new FormData(form);
        } catch (err) {
            formData = new FormData(form);
            if (submitter && submitter.name) formData.append(submitter.name, submitter.value || '');
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
            xhr: createCleanXhr,
            headers: {
                'X-Portal-Pjax': '1',
                'Accept': 'text/html,application/xhtml+xml'
            }
        }).done(function(htmlText, _status, xhrObj) {
            finishProgressBar();
            var finalUrl = xhrObj._finalResponseUrl || resolvedAction;
            var parsed = parseHtmlDocument(htmlText, finalUrl);
            if (parsed) {
                window.history.pushState({ sarprasUrl: finalUrl }, parsed.title || '', finalUrl);
                hydrateParsed(parsed, { scrollToTop: true });
                return;
            }
            window.location.href = finalUrl;
        }).fail(function() {
            finishProgressBar();
            form._sarprasBypassSubmit = true;
            form.submit();
            form._sarprasBypassSubmit = false;
        });
    }

    function init() {
        if (window.__sarprasRouterInit) return;
        window.__sarprasRouterInit = true;
        seedLoadedScripts();

        $(document).on('click.sarprasPjax', 'a[href]', function(e) {
            if (e.isDefaultPrevented() || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            if (!isEligibleLink(this)) return;
            e.preventDefault();
            navigate(this.href);
        });

        function canPrefetch() {
            if (document.hidden) return false;
            var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            if (conn) {
                if (conn.saveData) return false;
                if (/2g/.test(String(conn.effectiveType || '').toLowerCase())) return false;
            }
            return true;
        }

        $(document).on('touchstart.sarprasPjax mouseenter.sarprasPjax', 'a[href]', function() {
            if (!canPrefetch()) return;
            if (!isEligibleLink(this)) return;
            var href = this.href;
            var norm = normalizeUrl(href);
            var c = pageCache.get(norm);
            if (c && (Date.now() - c.timestamp) < CACHE_TTL_MS) return;
            clearTimeout(hoverTimer);
            hoverTimer = setTimeout(function() {
                fetchPage(href);
            }, 60);
        });

        $(document).on('submit.sarprasPjax', 'form', function(e) {
            if (e.isDefaultPrevented()) return;
            if (!isEligibleForm(this)) return;
            e.preventDefault();
            var submitter = e.originalEvent && e.originalEvent.submitter ? e.originalEvent.submitter : null;
            submitForm(this, submitter);
        });

        $(window).on('popstate.sarprasPjax', function() {
            if (window.location.pathname.indexOf('/mobile-sarpras') !== -1) {
                navigate(window.location.href, { pushState: false, scrollToTop: false });
            }
        });
    }

    $(document).ready(init);

    window.SarprasRouter = {
        init: init,
        navigate: navigate,
        reload: function() {
            pageCache.clear();
            return navigate(window.location.href, { pushState: false, forceReload: true, scrollToTop: false });
        }
    };

})(window, document, window.jQuery);
