/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.harno_theme = {
    attach: function (context, settings) {

    }
  };

})(jQuery, Drupal);

(function ($) {
  'use strict';
  Drupal.behaviors.messageclose = {
    attach: function (context) {
      $('.btn-notification-close').click(function (event) {
        event.preventDefault();
        $(this).parent().parent().parent().fadeOut('slow', function () {
          $(this).remove();
        });
      });
    }
  };
}(jQuery));
