(function ($, Drupal) {
    
    "use strict";
    
    Drupal.behaviors.initFaqCustomFaqPage = {
        attach: function() {
            $('.topic:not(.topic:first-child)').hide();
            
            $('.topic-link').click(function (event) {
               event.preventDefault();
               var target = $(this).data('target');
               $('.topic').hide();
               $('#topic-'+target).show();
            });
        }
    }
    
    
})(jQuery, Drupal);