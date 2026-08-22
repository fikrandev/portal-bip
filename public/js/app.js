/**
 * Portal BIP - Main Application JavaScript
 * Handles responsive sidebar, dropdowns, CSRF, global search, and interactions
 */

(function() {
    'use strict';

    // ── Helper: Get or Create Sidebar Overlay ──────────
    function getSidebarOverlay() {
        var overlay = document.getElementById('sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sidebar-overlay';
            overlay.className = 'fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300 cursor-pointer';
            overlay.setAttribute('aria-hidden', 'true');
            overlay.onclick = function() {
                window.toggleSidebar(false);
            };
            document.body.appendChild(overlay);
        }
        return overlay;
    }

    // ── Sidebar Toggle Function ───────────────────────
    window.toggleSidebar = function(forceState) {
        var sidebar = document.getElementById('sidebar');
        var overlay = getSidebarOverlay();
        var menuBtn = document.getElementById('btn-mobile-menu');
        
        if (!sidebar) return;
        
        // Check current mobile visibility state
        var isCurrentlyOpen = !sidebar.classList.contains('-translate-x-full');
        var shouldOpen = (typeof forceState === 'boolean') ? forceState : !isCurrentlyOpen;
        
        if (shouldOpen) {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            
            overlay.classList.remove('hidden');
            // Trigger smooth opacity fade-in
            setTimeout(function() {
                overlay.classList.remove('opacity-0');
                overlay.classList.add('opacity-100');
            }, 10);

            if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
            if (window.innerWidth < 1024) {
                document.body.style.overflow = 'hidden';
            }
        } else {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0');
            setTimeout(function() {
                if (sidebar.classList.contains('-translate-x-full')) {
                    overlay.classList.add('hidden');
                }
            }, 250);

            if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    };

    // ── Window Resize & Resolution Change Handler ─────
    function handleDeviceResolution() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        var menuBtn = document.getElementById('btn-mobile-menu');
        
        if (!sidebar) return;

        if (window.innerWidth >= 1024) {
            // Desktop Mode (>= 1024px)
            sidebar.classList.remove('-translate-x-full', 'translate-x-0');
            if (overlay) {
                overlay.classList.add('hidden');
                overlay.classList.remove('opacity-100');
                overlay.classList.add('opacity-0');
            }
            document.body.style.overflow = '';
            if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
        } else {
            // Mobile/Tablet Mode (< 1024px)
            // If overlay is not actively visible, ensure sidebar starts collapsed
            var isOverlayActive = overlay && !overlay.classList.contains('hidden') && overlay.classList.contains('opacity-100');
            if (!isOverlayActive) {
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                document.body.style.overflow = '';
                if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
            }
        }
    }

    var resizeDebounce;
    window.addEventListener('resize', function() {
        clearTimeout(resizeDebounce);
        resizeDebounce = setTimeout(handleDeviceResolution, 100);
    });
    window.addEventListener('orientationchange', function() {
        setTimeout(handleDeviceResolution, 150);
    });

    // ── User Dropdown Toggle ───────────────────────
    window.toggleUserDropdown = function() {
        var menu = document.getElementById('user-dropdown-menu');
        var arrow = document.getElementById('user-dropdown-arrow');
        var btn = document.getElementById('btn-user-dropdown');
        
        if (!menu) return;
        
        var isHidden = menu.classList.contains('hidden');
        
        if (isHidden) {
            menu.classList.remove('hidden');
            setTimeout(function() {
                menu.classList.remove('opacity-0', 'scale-95');
                menu.classList.add('opacity-100', 'scale-100');
            }, 10);
            if (arrow) arrow.style.transform = 'rotate(180deg)';
            if (btn) btn.setAttribute('aria-expanded', 'true');
        } else {
            menu.classList.add('opacity-0', 'scale-95');
            menu.classList.remove('opacity-100', 'scale-100');
            setTimeout(function() { menu.classList.add('hidden'); }, 200);
            if (arrow) arrow.style.transform = 'rotate(0deg)';
            if (btn) btn.setAttribute('aria-expanded', 'false');
        }
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        var container = document.getElementById('user-dropdown-container');
        var menu = document.getElementById('user-dropdown-menu');
        
        if (container && menu && !container.contains(e.target) && !menu.classList.contains('hidden')) {
            window.toggleUserDropdown();
        }
    });

    // ── CSRF Token Injection for jQuery AJAX ───────
    $(document).ready(function() {
        var csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        if (csrfToken) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        }
    });

    // ── Keyboard Navigation ────────────────────────
    document.addEventListener('keydown', function(e) {
        // Escape key closes modals, dropdowns, and mobile sidebar
        if (e.key === 'Escape') {
            var sidebar = document.getElementById('sidebar');
            
            // Close mobile sidebar if open
            if (sidebar && !sidebar.classList.contains('-translate-x-full') && window.innerWidth < 1024) {
                window.toggleSidebar(false);
            }
            
            // Close user dropdown if open
            var menu = document.getElementById('user-dropdown-menu');
            if (menu && !menu.classList.contains('hidden')) {
                window.toggleUserDropdown();
            }
        }
    });

    // ── Smooth Page Load ───────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('transition-page');
        handleDeviceResolution();
    });

    // ── Global Search ──────────────────────────────
    function handleSearch(query) {
        query = query.toLowerCase().trim();
        
        // 1. Filter Sidebar
        var sidebarLinks = document.querySelectorAll('#sidebar nav a');
        sidebarLinks.forEach(function(link) {
            var text = link.textContent.toLowerCase();
            var li = link.closest('li');
            if (!li) return;
            
            if (text.includes(query)) {
                li.style.display = '';
            } else {
                li.style.display = 'none';
            }
        });
        
        // Hide empty groups in sidebar
        var sidebarGroups = document.querySelectorAll('#sidebar nav p');
        sidebarGroups.forEach(function(groupLabel) {
            var ul = groupLabel.nextElementSibling;
            if (ul && ul.tagName === 'UL') {
                var hasVisible = Array.from(ul.querySelectorAll('li')).some(li => li.style.display !== 'none');
                groupLabel.style.display = hasVisible ? '' : 'none';
                ul.style.display = hasVisible ? '' : 'none';
            }
        });

        // 2. Filter Dashboard Cards (if on dashboard)
        var dashCards = document.querySelectorAll('.mb-8 .grid > a, #modules-grid > a');
        dashCards.forEach(function(card) {
            var text = card.textContent.toLowerCase();
            if (text.includes(query)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Hide empty groups in dashboard
        var dashGroups = document.querySelectorAll('.mb-8');
        dashGroups.forEach(function(group) {
            var grid = group.querySelector('.grid');
            if (grid) {
                var hasVisible = Array.from(grid.querySelectorAll(':scope > a')).some(a => a.style.display !== 'none');
                group.style.display = hasVisible ? '' : 'none';
            }
        });
    }

    var globalSearch = document.getElementById('global-search');
    if (globalSearch) {
        globalSearch.addEventListener('input', function(e) {
            handleSearch(e.target.value);
            var mobileSearch = document.getElementById('module-search-mobile');
            if (mobileSearch && mobileSearch.value !== e.target.value) {
                mobileSearch.value = e.target.value;
            }
        });
    }

    var mobileSearch = document.getElementById('module-search-mobile');
    if (mobileSearch) {
        mobileSearch.addEventListener('input', function(e) {
            handleSearch(e.target.value);
            if (globalSearch && globalSearch.value !== e.target.value) {
                globalSearch.value = e.target.value;
            }
        });
    }

})();
