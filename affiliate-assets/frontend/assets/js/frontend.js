/**
 * Frontend JavaScript for AffiliateAssets
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Tab switching functionality
        $('.aa-tab-btn').on('click', function() {
            var tabId = $(this).data('tab');
            
            $('.aa-tab-btn').removeClass('active');
            $(this).addClass('active');
            
            $('.aa-tab-panel').removeClass('active');
            $('#' + tabId).addClass('active');
        });
        
        // Copy referral link functionality
        $('.aa-copy-btn').on('click', function() {
            var targetId = $(this).data('copy-target');
            var $input = $('#' + targetId);
            
            $input.select();
            
            try {
                document.execCommand('copy');
                
                var $btn = $(this);
                var originalText = $btn.text();
                $btn.text(aaFrontend.i18n.copied);
                
                setTimeout(function() {
                    $btn.text(originalText);
                }, 2000);
            } catch (err) {
                console.error('Failed to copy text: ', err);
                alert(aaFrontend.i18n.error);
            }
        });
        
        // Form submission with AJAX (optional enhancement)
        $('.aa-settings-form').on('submit', function(e) {
            // Let the form submit normally for now
            // Can be enhanced with AJAX in Phase 2
        });
    });

})(jQuery);
