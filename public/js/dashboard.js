/**
 * Portal BIP - Dashboard JavaScript
 * Module card search, filter, and hover effects
 */

(function ($) {
    'use strict';

    if (!$) return;

    function initDashboardInteractions() {
        var $searchInputs = $('#global-search, #module-search-mobile');

        // Gunakan event delegation namespaced agar tidak menumpuk saat PJAX navigasi bolak-balik
        $(document).off('.portalDashboard');

        $(document).on('input.portalDashboard', '#global-search, #module-search-mobile', function () {
            var $this = $(this);
            var val = $this.val();
            var query = String(val || '').toLowerCase().trim();

            $searchInputs.not(this).val(val);

            $('#modules-grid .module-card').each(function () {
                var $card = $(this);
                var name = $card.find('h3').text().toLowerCase();
                var desc = $card.find('p').text().toLowerCase();

                if (!query || name.indexOf(query) !== -1 || desc.indexOf(query) !== -1) {
                    $card.removeClass('hidden');
                } else {
                    $card.addClass('hidden');
                }
            });

            $('#modules-grid .dashboard-group-section').each(function() {
                var $group = $(this);
                var hasVisible = $group.find('.module-card:not(.hidden)').length > 0;
                $group.toggleClass('hidden', !hasVisible);
            });
        });

        $(document).on('click.portalDashboard', '#modules-grid .module-card', function () {
            var $card = $(this);
            $card.css('transform', 'scale(0.98)');
            setTimeout(function () {
                $card.css('transform', '');
            }, 150);
        });
    }

    $(initDashboardInteractions);

})(window.jQuery);
