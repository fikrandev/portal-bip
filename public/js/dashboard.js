/**
 * Portal BIP - Dashboard JavaScript
 * Module card search, filter, and hover effects
 */

(function() {
    'use strict';

    $(document).ready(function() {
        
        // ── Module Search / Filter ─────────────────
        var searchInputs = $('#global-search, #module-search-mobile');
        
        searchInputs.on('input', function() {
            var query = $(this).val().toLowerCase().trim();
            
            // Sync both search inputs
            searchInputs.not(this).val($(this).val());
            
            $('#modules-grid .module-card').each(function() {
                var card = $(this);
                var name = card.find('h3').text().toLowerCase();
                var desc = card.find('p').text().toLowerCase();
                
                if (!query || name.indexOf(query) !== -1 || desc.indexOf(query) !== -1) {
                    card.removeClass('hidden').css({
                        opacity: 1,
                        transform: 'translateY(0)'
                    });
                } else {
                    card.addClass('hidden');
                }
            });
        });

        // ── Card Mouse Tracking (Ripple Effect) ────
        $('.module-card').on('mousemove', function(e) {
            var rect = this.getBoundingClientRect();
            var x = ((e.clientX - rect.left) / rect.width * 100).toFixed(1);
            var y = ((e.clientY - rect.top) / rect.height * 100).toFixed(1);
            this.style.setProperty('--mouse-x', x + '%');
            this.style.setProperty('--mouse-y', y + '%');
        });

        // ── Card Click Ripple ──────────────────────
        $('.module-card').on('click', function(e) {
            var card = $(this);
            card.css('transform', 'scale(0.98)');
            setTimeout(function() {
                card.css('transform', '');
            }, 150);
        });
    });

})();
