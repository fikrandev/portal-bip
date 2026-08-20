/**
 * Portal BIP - Main Application JavaScript
 * Handles sidebar, dropdowns, CSRF, and global interactions
 */

(function() {
    'use strict';

    // ── Sidebar Toggle ─────────────────────────────
    window.toggleSidebar = function() {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebar-overlay');
        var menuBtn = document.getElementById('btn-mobile-menu');
        
        if (!sidebar || !overlay) return;
        
        var isOpen = !sidebar.classList.contains('-translate-x-full');
        
        if (isOpen) {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            if (menuBtn) menuBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }
    };

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
            var overlay = document.getElementById('sidebar-overlay');
            
            // Close mobile sidebar if open
            if (sidebar && !sidebar.classList.contains('-translate-x-full') && window.innerWidth < 1024) {
                window.toggleSidebar();
            }
            
            // Close user dropdown if open
            var menu = document.getElementById('user-dropdown-menu');
            if (menu && !menu.classList.contains('hidden')) {
                window.toggleUserDropdown();
            }
        }
    });

    // ── Window Resize Handler ──────────────────────
    var resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            // Reset sidebar state on desktop
            if (window.innerWidth >= 1024) {
                var sidebar = document.getElementById('sidebar');
                var overlay = document.getElementById('sidebar-overlay');
                if (sidebar) sidebar.classList.remove('-translate-x-full');
                if (overlay) overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }, 250);
    });

    // ── Smooth Page Load ───────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('transition-page');
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
