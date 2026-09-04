/**
 * Portal BIP - Standardized Searchable Select Component
 * Enhances native <select class="searchable-select"> into a modern, searchable custom dropdown.
 * Supports both Single Select and Multi-Select with live filter by Unit & real-time search.
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

    function destroySearchableSelect(select) {
        if (!select) return;
        var wrapper = select.nextElementSibling;
        if (wrapper && wrapper.classList.contains('searchable-select-wrapper')) {
            wrapper.remove();
        }
        select.removeAttribute('data-searchable-initialized');
        select.style.display = '';
    }

    function refreshSearchableSelect(select) {
        if (!select) return;
        var currentVals = [];
        Array.from(select.options).forEach(function(opt) {
            if (opt.selected) currentVals.push(opt.value);
        });
        destroySearchableSelect(select);
        Array.from(select.options).forEach(function(opt) {
            opt.selected = currentVals.includes(opt.value);
        });
        select.setAttribute('data-searchable-initialized', 'true');
        buildSearchableSelect(select);
    }

    function setValue(select, val) {
        if (!select) return;
        var valArray = Array.isArray(val) ? val.map(String) : [String(val)];
        Array.from(select.options).forEach(function(opt) {
            opt.selected = valArray.includes(String(opt.value));
        });
        var wrapper = select.nextElementSibling;
        if (wrapper && wrapper.classList.contains('searchable-select-wrapper')) {
            if (wrapper._updateTrigger) wrapper._updateTrigger();
            if (wrapper._renderOptions) wrapper._renderOptions('');
        }
    }

    function filterByUnit(select, unitName) {
        if (!select) return;
        if (unitName) {
            select.setAttribute('data-active-unit', unitName.trim().toUpperCase());
        } else {
            select.removeAttribute('data-active-unit');
        }
        
        // Trigger re-render of options if initialized
        var wrapper = select.nextElementSibling;
        if (wrapper && wrapper.classList.contains('searchable-select-wrapper') && wrapper._renderOptions) {
            wrapper._renderOptions('');
            wrapper._updateTrigger();
        }
    }

    function buildSearchableSelect(select) {
        // Hide original select (keep for form submission)
        select.style.display = 'none';

        var isMultiple = select.multiple || select.getAttribute('data-multiple') === 'true' || select.hasAttribute('multiple');
        var placeholder = select.getAttribute('data-placeholder') || (select.options[0] ? select.options[0].text : '-- Pilih --');
        var allowClear = select.getAttribute('data-allow-clear') === 'true';
        var isSmall = select.getAttribute('data-size') === 'sm' || select.classList.contains('text-xs');

        // Wrapper container
        var wrapper = document.createElement('div');
        wrapper.className = 'searchable-select-wrapper relative w-full';

        // Trigger button
        var trigger = document.createElement('button');
        trigger.type = 'button';
        var triggerPadding = isSmall ? 'px-3 py-2 text-xs rounded-xl' : 'px-4 py-2.5 text-sm rounded-xl';
        trigger.className = 'searchable-select-trigger w-full ' + triggerPadding + ' bg-white border border-slate-300 hover:border-primary-400 text-left flex items-center justify-between shadow-xs focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer';

        var triggerContent = document.createElement('div');
        triggerContent.className = 'flex items-center gap-2 min-w-0 flex-1';

        var chevron = document.createElement('div');
        chevron.className = 'text-slate-400 shrink-0 ml-1.5 transition-transform duration-200';
        chevron.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>';

        trigger.appendChild(triggerContent);
        trigger.appendChild(chevron);

        // Dropdown Panel
        var dropdown = document.createElement('div');
        dropdown.className = 'searchable-select-dropdown hidden absolute left-0 right-0 top-full mt-1.5 z-50 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden transition-all duration-150 min-w-[240px]';

        // Search Input Header
        var searchHeader = document.createElement('div');
        searchHeader.className = 'p-2 border-b border-slate-100 bg-slate-50/70 relative space-y-1.5';

        var searchWrapper = document.createElement('div');
        searchWrapper.className = 'relative flex items-center';

        var searchIcon = document.createElement('span');
        searchIcon.className = 'absolute left-2.5 text-slate-400 pointer-events-none';
        searchIcon.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>';

        var searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = select.getAttribute('data-search-placeholder') || 'Ketik untuk mencari...';
        searchInput.className = 'w-full pl-8 pr-7 py-1.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-colors';

        var clearSearchBtn = document.createElement('button');
        clearSearchBtn.type = 'button';
        clearSearchBtn.className = 'absolute right-2 text-slate-400 hover:text-slate-600 hidden text-sm font-bold p-1';
        clearSearchBtn.innerHTML = '&times;';

        searchWrapper.appendChild(searchIcon);
        searchWrapper.appendChild(searchInput);
        searchWrapper.appendChild(clearSearchBtn);
        searchHeader.appendChild(searchWrapper);

        // Multi-select Quick Actions (Pilih Semua / Batal)
        if (isMultiple) {
            var multiActions = document.createElement('div');
            multiActions.className = 'flex items-center justify-between text-[11px] px-1 text-slate-500 font-semibold';
            multiActions.innerHTML = `
                <button type="button" class="btn-select-all hover:text-primary-600 transition-colors cursor-pointer">+ Pilih Semua</button>
                <button type="button" class="btn-deselect-all hover:text-rose-600 transition-colors cursor-pointer">Batal Semua</button>
            `;
            searchHeader.appendChild(multiActions);

            multiActions.querySelector('.btn-select-all').addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var visibleOpts = optionsList.querySelectorAll('.searchable-option:not(.hidden)');
                visibleOpts.forEach(function(el) {
                    var v = el.getAttribute('data-value');
                    var targetOpt = Array.from(select.options).find(o => String(o.value) === String(v));
                    if (targetOpt && targetOpt.value !== '') targetOpt.selected = true;
                });
                renderOptions(searchInput.value);
                updateTrigger();
                var evt = new Event('change', { bubbles: true });
                select.dispatchEvent(evt);
            });

            multiActions.querySelector('.btn-deselect-all').addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                Array.from(select.options).forEach(function(opt) {
                    opt.selected = false;
                });
                renderOptions(searchInput.value);
                updateTrigger();
                var evt = new Event('change', { bubbles: true });
                select.dispatchEvent(evt);
            });
        }

        // Options List Container
        var optionsList = document.createElement('div');
        optionsList.className = 'searchable-select-options max-h-56 overflow-y-auto custom-scrollbar p-1.5 space-y-0.5 text-xs';

        // Empty state
        var emptyState = document.createElement('div');
        emptyState.className = 'p-5 text-center text-slate-400 hidden';
        emptyState.innerHTML = '<svg class="w-6 h-6 mx-auto mb-1 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg><p class="text-xs font-medium text-slate-500">Tidak ditemukan</p>';

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
            var activeUnit = (select.getAttribute('data-active-unit') || '').toUpperCase().trim();
            var visibleCount = 0;

            Array.from(select.options).forEach(function(opt) {
                var val = opt.value;
                var text = opt.text;
                if (!val && isMultiple) return; // Skip empty placeholder in multi-select

                var image = opt.getAttribute('data-image');
                var subtext = opt.getAttribute('data-subtext');
                var badge = opt.getAttribute('data-badge');
                var unit = (opt.getAttribute('data-unit') || '').toUpperCase().trim();
                var isSelected = opt.selected;

                // Unit Filter Matching
                if (activeUnit && activeUnit !== 'SEMUA' && activeUnit !== 'SEMUA UNIT' && activeUnit !== 'YAYASAN' && val !== '') {
                    if (unit && unit !== 'SEMUA' && unit !== 'SEMUA UNIT' && unit !== 'UMUM') {
                        if (unit !== activeUnit && !unit.includes(activeUnit) && !activeUnit.includes(unit)) {
                            return;
                        }
                    }
                }

                // Text search filter
                var fullSearch = (text + ' ' + (subtext || '') + ' ' + (badge || '') + ' ' + (unit || '')).toLowerCase();
                if (filter && !fullSearch.includes(filter)) {
                    return;
                }

                visibleCount++;

                var item = document.createElement('div');
                item.className = 'searchable-option px-2.5 py-2 rounded-xl cursor-pointer flex items-center justify-between gap-2.5 transition-colors ' + 
                    (isSelected ? 'bg-primary-50/80 text-primary-900 font-semibold border border-primary-200/60' : 'text-slate-700 hover:bg-slate-50');
                item.setAttribute('data-value', val);

                var itemLeft = document.createElement('div');
                itemLeft.className = 'flex items-center gap-2 min-w-0 flex-1';

                // Multi-select Checkbox Box
                if (isMultiple) {
                    var chkBox = document.createElement('div');
                    chkBox.className = 'w-4 h-4 rounded-md border shrink-0 flex items-center justify-center transition-all ' + 
                        (isSelected ? 'bg-primary-600 border-primary-600 text-white' : 'border-slate-300 bg-white');
                    chkBox.innerHTML = isSelected ? '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>' : '';
                    itemLeft.appendChild(chkBox);
                }

                // Avatar / Thumbnail
                if (image) {
                    var img = document.createElement('img');
                    img.src = image;
                    img.alt = text;
                    img.className = 'w-6 h-6 rounded-full object-cover border border-slate-200 shrink-0';
                    itemLeft.appendChild(img);
                } else if (val && !badge && !unit && !isMultiple) {
                    var initial = document.createElement('div');
                    initial.className = 'w-6 h-6 rounded-full bg-primary-100 text-primary-700 font-bold text-[10px] flex items-center justify-center shrink-0';
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
                    subEl.className = 'text-[10px] text-slate-400 truncate';
                    subEl.textContent = subtext;
                    textWrapper.appendChild(subEl);
                }

                itemLeft.appendChild(textWrapper);
                item.appendChild(itemLeft);

                // Right Badge or Unit Tag
                var itemRight = document.createElement('div');
                itemRight.className = 'flex items-center gap-1 shrink-0';

                if (unit && unit !== 'SEMUA' && unit !== 'SEMUA UNIT') {
                    var unitBadge = document.createElement('span');
                    unitBadge.className = 'px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-50 text-sky-700 border border-sky-200/60';
                    unitBadge.textContent = unit;
                    itemRight.appendChild(unitBadge);
                }

                if (badge) {
                    var badgeEl = document.createElement('span');
                    badgeEl.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-slate-100 text-slate-600';
                    badgeEl.textContent = badge;
                    itemRight.appendChild(badgeEl);
                }

                if (!isMultiple && isSelected && val) {
                    var check = document.createElement('span');
                    check.className = 'text-primary-600 font-bold ml-0.5';
                    check.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>';
                    itemRight.appendChild(check);
                }

                item.appendChild(itemRight);

                // Click event
                item.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (isMultiple) {
                        opt.selected = !opt.selected;
                        var evt = new Event('change', { bubbles: true });
                        select.dispatchEvent(evt);
                        renderOptions(searchInput.value);
                        updateTrigger();
                    } else {
                        selectOption(val);
                        closeDropdown();
                    }
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
            triggerContent.innerHTML = '';

            if (isMultiple) {
                var selectedOpts = Array.from(select.options).filter(o => o.selected && o.value !== '');
                var count = selectedOpts.length;

                if (count === 0) {
                    var span = document.createElement('span');
                    span.className = 'text-slate-400 truncate text-xs';
                    span.textContent = placeholder;
                    triggerContent.appendChild(span);
                    return;
                }

                if (count === 1) {
                    var singleTag = document.createElement('div');
                    singleTag.className = 'flex items-center gap-1.5 min-w-0';
                    singleTag.innerHTML = `
                        <span class="px-2 py-0.5 rounded-lg bg-indigo-50 border border-indigo-200/80 text-indigo-800 font-bold text-xs truncate">
                            ${selectedOpts[0].text}
                        </span>
                    `;
                    triggerContent.appendChild(singleTag);
                } else {
                    var multiTag = document.createElement('div');
                    multiTag.className = 'flex items-center gap-1.5 min-w-0';
                    multiTag.innerHTML = `
                        <span class="px-2 py-0.5 rounded-lg bg-indigo-600 text-white font-extrabold text-[11px] shrink-0 shadow-xs">
                            ${count} Kelas
                        </span>
                        <span class="font-bold text-slate-800 text-xs truncate">
                            ${selectedOpts[0].text}, +${count - 1} lainnya
                        </span>
                    `;
                    triggerContent.appendChild(multiTag);
                }
                return;
            }

            // Single select display
            var selectedOpt = select.options[select.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) {
                var span = document.createElement('span');
                span.className = 'text-slate-400 truncate text-xs';
                span.textContent = placeholder;
                triggerContent.appendChild(span);
                return;
            }

            var text = selectedOpt.text;
            var image = selectedOpt.getAttribute('data-image');
            var badge = selectedOpt.getAttribute('data-badge');
            var unit = selectedOpt.getAttribute('data-unit');

            if (image) {
                var img = document.createElement('img');
                img.src = image;
                img.alt = text;
                img.className = 'w-5 h-5 rounded-full object-cover border border-slate-200 shrink-0';
                triggerContent.appendChild(img);
            }

            var label = document.createElement('span');
            label.className = 'font-semibold text-slate-800 truncate text-xs';
            label.textContent = text;
            triggerContent.appendChild(label);

            if (unit && unit !== 'SEMUA' && unit !== 'SEMUA UNIT') {
                var ub = document.createElement('span');
                ub.className = 'px-1.5 py-0.5 rounded text-[9px] font-bold bg-sky-50 text-sky-700 border border-sky-200/60 shrink-0';
                ub.textContent = unit;
                triggerContent.appendChild(ub);
            }

            if (badge) {
                var b = document.createElement('span');
                b.className = 'px-1.5 py-0.5 rounded text-[9px] font-mono font-bold bg-primary-50 text-primary-700 ml-1 shrink-0';
                b.textContent = badge;
                triggerContent.appendChild(b);
            }
        }

        function selectOption(val) {
            select.value = val;
            var event = new Event('change', { bubbles: true });
            select.dispatchEvent(event);
            updateTrigger();
        }

        function openDropdown() {
            // Close other open dropdowns
            document.querySelectorAll('.searchable-select-dropdown').forEach(function(d) {
                if (d !== dropdown) {
                    d.classList.add('hidden');
                    var otherChevron = d.parentNode.querySelector('.searchable-select-trigger svg');
                    if (otherChevron) otherChevron.style.transform = 'rotate(0deg)';
                    d.parentNode.style.zIndex = '';
                    var otherRow = d.closest('.repeater-row, tr');
                    if (otherRow) otherRow.style.zIndex = '';
                }
            });

            wrapper.style.zIndex = '100';
            var parentRow = wrapper.closest('.repeater-row, tr');
            if (parentRow) parentRow.style.zIndex = '100';

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
            wrapper.style.zIndex = '';
            var parentRow = wrapper.closest('.repeater-row, tr');
            if (parentRow) parentRow.style.zIndex = '';
        }

        function toggleDropdown() {
            if (dropdown.classList.contains('hidden')) {
                openDropdown();
            } else {
                closeDropdown();
            }
        }

        // Attach internal helpers to wrapper
        wrapper._renderOptions = renderOptions;
        wrapper._updateTrigger = updateTrigger;

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
            } else if (e.key === 'Enter' && !isMultiple) {
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

    // Expose global initializer & methods
    window.SearchableSelect = {
        init: initSearchableSelects,
        refresh: refreshSearchableSelect,
        destroy: destroySearchableSelect,
        setValue: setValue,
        filterByUnit: filterByUnit
    };

    // Auto initialize on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        initSearchableSelects();
    });

})();
