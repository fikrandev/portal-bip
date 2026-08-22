/**
 * Portal BIP - Searchable Select Component
 * Enhances native <select class="searchable-select"> into a modern, searchable custom dropdown.
 */

(function() {
    'use strict';

    function initSearchableSelects(rootElement) {
        var selects = (rootElement || document).querySelectorAll('select.searchable-select:not([data-searchable-initialized])');

        selects.forEach(function(select) {
            select.setAttribute('data-searchable-initialized', 'true');
            buildSearchableSelect(select);
        });
    }

    function buildSearchableSelect(select) {
        // Hide original select (keep for form submission)
        select.style.display = 'none';

        var placeholder = select.getAttribute('data-placeholder') || (select.options[0] ? select.options[0].text : '-- Pilih --');
        var allowClear = select.getAttribute('data-allow-clear') === 'true';

        // Wrapper container
        var wrapper = document.createElement('div');
        wrapper.className = 'searchable-select-wrapper relative w-full';

        // Trigger button
        var trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'searchable-select-trigger w-full px-4 py-2.5 bg-white border border-slate-300 hover:border-primary-400 rounded-xl text-left flex items-center justify-between shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-sm cursor-pointer';

        var triggerContent = document.createElement('div');
        triggerContent.className = 'flex items-center gap-2.5 min-w-0 flex-1';

        var chevron = document.createElement('div');
        chevron.className = 'text-slate-400 shrink-0 ml-2 transition-transform duration-200';
        chevron.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>';

        trigger.appendChild(triggerContent);
        trigger.appendChild(chevron);

        // Dropdown Panel
        var dropdown = document.createElement('div');
        dropdown.className = 'searchable-select-dropdown hidden absolute left-0 right-0 top-full mt-1.5 z-50 bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden transition-all duration-150';

        // Search Input Header
        var searchHeader = document.createElement('div');
        searchHeader.className = 'p-2.5 border-b border-slate-100 bg-slate-50/70 relative';

        var searchWrapper = document.createElement('div');
        searchWrapper.className = 'relative flex items-center';

        var searchIcon = document.createElement('span');
        searchIcon.className = 'absolute left-3 text-slate-400 pointer-events-none';
        searchIcon.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>';

        var searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = select.getAttribute('data-search-placeholder') || 'Ketik untuk mencari nama/NIY...';
        searchInput.className = 'w-full pl-9 pr-8 py-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors';

        var clearSearchBtn = document.createElement('button');
        clearSearchBtn.type = 'button';
        clearSearchBtn.className = 'absolute right-2.5 text-slate-400 hover:text-slate-600 hidden text-sm font-bold p-1';
        clearSearchBtn.innerHTML = '&times;';

        searchWrapper.appendChild(searchIcon);
        searchWrapper.appendChild(searchInput);
        searchWrapper.appendChild(clearSearchBtn);
        searchHeader.appendChild(searchWrapper);

        // Options List Container
        var optionsList = document.createElement('div');
        optionsList.className = 'searchable-select-options max-h-64 overflow-y-auto custom-scrollbar p-1.5 space-y-0.5 text-xs';

        // Empty state
        var emptyState = document.createElement('div');
        emptyState.className = 'p-6 text-center text-slate-400 hidden';
        emptyState.innerHTML = '<svg class="w-8 h-8 mx-auto mb-1 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg><p class="text-xs font-medium text-slate-500">Tidak ada hasil ditemukan</p>';

        dropdown.appendChild(searchHeader);
        dropdown.appendChild(optionsList);
        dropdown.appendChild(emptyState);

        wrapper.appendChild(trigger);
        wrapper.appendChild(dropdown);

        // Insert wrapper next to original select
        select.parentNode.insertBefore(wrapper, select.nextSibling);

        // Populate options
        function renderOptions(filterText) {
            optionsList.innerHTML = '';
            var filter = (filterText || '').toLowerCase().trim();
            var visibleCount = 0;

            Array.from(select.options).forEach(function(opt) {
                var val = opt.value;
                var text = opt.text;
                var image = opt.getAttribute('data-image');
                var subtext = opt.getAttribute('data-subtext');
                var badge = opt.getAttribute('data-badge');
                var isSelected = opt.selected;

                // Match filter
                var fullSearch = (text + ' ' + (subtext || '') + ' ' + (badge || '')).toLowerCase();
                if (filter && !fullSearch.includes(filter)) {
                    return;
                }

                visibleCount++;

                var item = document.createElement('div');
                item.className = 'searchable-option px-3 py-2.5 rounded-xl cursor-pointer flex items-center justify-between gap-3 transition-colors ' + 
                    (isSelected ? 'bg-primary-50 text-primary-900 font-semibold' : 'text-slate-700 hover:bg-slate-50');
                item.setAttribute('data-value', val);

                var itemLeft = document.createElement('div');
                itemLeft.className = 'flex items-center gap-2.5 min-w-0 flex-1';

                // Avatar / Thumbnail
                if (image) {
                    var img = document.createElement('img');
                    img.src = image;
                    img.alt = text;
                    img.className = 'w-7 h-7 rounded-full object-cover border border-slate-200 shrink-0';
                    itemLeft.appendChild(img);
                } else if (val) {
                    var initial = document.createElement('div');
                    initial.className = 'w-7 h-7 rounded-full bg-primary-100 text-primary-700 font-bold text-[11px] flex items-center justify-center shrink-0';
                    initial.textContent = text.trim().charAt(0).toUpperCase();
                    itemLeft.appendChild(initial);
                }

                // Text labels
                var textWrapper = document.createElement('div');
                textWrapper.className = 'min-w-0 flex-1';

                var titleEl = document.createElement('div');
                titleEl.className = 'truncate text-slate-800 ' + (isSelected ? 'font-bold text-primary-900' : 'font-medium');
                titleEl.textContent = text;
                textWrapper.appendChild(titleEl);

                if (subtext) {
                    var subEl = document.createElement('div');
                    subEl.className = 'text-[11px] text-slate-400 truncate';
                    subEl.textContent = subtext;
                    textWrapper.appendChild(subEl);
                }

                itemLeft.appendChild(textWrapper);
                item.appendChild(itemLeft);

                // Right Badge or Checkmark
                var itemRight = document.createElement('div');
                itemRight.className = 'flex items-center gap-1.5 shrink-0';

                if (badge) {
                    var badgeEl = document.createElement('span');
                    badgeEl.className = 'px-2 py-0.5 rounded-md text-[10px] font-mono bg-slate-100 text-slate-600';
                    badgeEl.textContent = badge;
                    itemRight.appendChild(badgeEl);
                }

                if (isSelected && val) {
                    var check = document.createElement('span');
                    check.className = 'text-primary-600 font-bold';
                    check.innerHTML = '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>';
                    itemRight.appendChild(check);
                }

                item.appendChild(itemRight);

                // Click event
                item.addEventListener('click', function(e) {
                    e.stopPropagation();
                    selectOption(val);
                    closeDropdown();
                });

                optionsList.appendChild(item);
            });

            if (visibleCount === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        // Update trigger display
        function updateTrigger() {
            var selectedOpt = select.options[select.selectedIndex];
            triggerContent.innerHTML = '';

            if (!selectedOpt || !selectedOpt.value) {
                var span = document.createElement('span');
                span.className = 'text-slate-400 truncate';
                span.textContent = placeholder;
                triggerContent.appendChild(span);
                return;
            }

            var text = selectedOpt.text;
            var image = selectedOpt.getAttribute('data-image');
            var subtext = selectedOpt.getAttribute('data-subtext');
            var badge = selectedOpt.getAttribute('data-badge');

            if (image) {
                var img = document.createElement('img');
                img.src = image;
                img.alt = text;
                img.className = 'w-6 h-6 rounded-full object-cover border border-slate-200 shrink-0';
                triggerContent.appendChild(img);
            } else {
                var initial = document.createElement('div');
                initial.className = 'w-6 h-6 rounded-full bg-primary-100 text-primary-700 font-bold text-[10px] flex items-center justify-center shrink-0';
                initial.textContent = text.trim().charAt(0).toUpperCase();
                triggerContent.appendChild(initial);
            }

            var label = document.createElement('span');
            label.className = 'font-semibold text-slate-800 truncate';
            label.textContent = text;
            triggerContent.appendChild(label);

            if (badge) {
                var b = document.createElement('span');
                b.className = 'px-2 py-0.5 rounded-md text-[10px] font-mono bg-primary-50 text-primary-700 font-medium ml-1 shrink-0';
                b.textContent = badge;
                triggerContent.appendChild(b);
            }
        }

        function selectOption(val) {
            select.value = val;
            // Dispatch change event to original select
            var event = new Event('change', { bubbles: true });
            select.dispatchEvent(event);
            updateTrigger();
            renderOptions(searchInput.value);
        }

        function openDropdown() {
            // Close other open searchable selects first
            document.querySelectorAll('.searchable-select-dropdown').forEach(function(d) {
                if (d !== dropdown) {
                    d.classList.add('hidden');
                    var otherChevron = d.parentNode.querySelector('.searchable-select-trigger svg');
                    if (otherChevron) otherChevron.style.transform = 'rotate(0deg)';
                }
            });

            dropdown.classList.remove('hidden');
            chevron.querySelector('svg').style.transform = 'rotate(180deg)';
            trigger.classList.add('border-primary-500', 'ring-2', 'ring-primary-500/20');
            searchInput.value = '';
            clearSearchBtn.classList.add('hidden');
            renderOptions('');

            setTimeout(function() {
                searchInput.focus();
            }, 50);
        }

        function closeDropdown() {
            dropdown.classList.add('hidden');
            chevron.querySelector('svg').style.transform = 'rotate(0deg)';
            trigger.classList.remove('border-primary-500', 'ring-2', 'ring-primary-500/20');
        }

        function toggleDropdown() {
            if (dropdown.classList.contains('hidden')) {
                openDropdown();
            } else {
                closeDropdown();
            }
        }

        // Event Listeners
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleDropdown();
        });

        searchInput.addEventListener('input', function() {
            var val = searchInput.value;
            if (val.length > 0) {
                clearSearchBtn.classList.remove('hidden');
            } else {
                clearSearchBtn.classList.add('hidden');
            }
            renderOptions(val);
        });

        clearSearchBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            searchInput.value = '';
            clearSearchBtn.classList.add('hidden');
            renderOptions('');
            searchInput.focus();
        });

        // Click outside to close
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                closeDropdown();
            }
        });

        // Keyboard navigation
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDropdown();
                trigger.focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                var firstOption = optionsList.querySelector('.searchable-option');
                if (firstOption) {
                    var val = firstOption.getAttribute('data-value');
                    selectOption(val);
                    closeDropdown();
                    trigger.focus();
                }
            }
        });

        // Sync when original select value changes programmatically
        select.addEventListener('change', function() {
            updateTrigger();
        });

        // Initial render
        updateTrigger();
        renderOptions('');
    }

    // Expose global initializer
    window.SearchableSelect = {
        init: initSearchableSelects
    };

    // Auto initialize on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        initSearchableSelects();
    });

})();
