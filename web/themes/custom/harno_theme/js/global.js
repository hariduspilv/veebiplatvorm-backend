
(function ($,Drupal) {
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
}(jQuery, Drupal));

(function ($, window, Drupal, drupalSettings) {

  'use strict';
  /**
   * Prepare the Ajax request before it is sent.
   *
   * @param {XMLHttpRequest} xmlhttprequest
   * @param {object} options
   * @param {object} options.extraData
   */
  Drupal.Ajax.prototype.beforeSend = function (xmlhttprequest, options) {
    // For forms without file inputs, the jQuery Form plugin serializes the
    // form values, and then calls jQuery's $.ajax() function, which invokes
    // this handler. In this circumstance, options.extraData is never used. For
    // forms with file inputs, the jQuery Form plugin uses the browser's normal
    // form submission mechanism, but captures the response in a hidden IFRAME.
    // In this circumstance, it calls this handler first, and then appends
    // hidden fields to the form to submit the values in options.extraData.
    // There is no simple way to know which submission mechanism will be used,
    // so we add to extraData regardless, and allow it to be ignored in the
    // former case.
    if (this.$form) {
      options.extraData = options.extraData || {};

      // Let the server know when the IFRAME submission mechanism is used. The
      // server can use this information to wrap the JSON response in a
      // TEXTAREA, as per http://jquery.malsup.com/form/#file-upload.
      options.extraData.ajax_iframe_upload = '1';

      // The triggering element is about to be disabled (see below), but if it
      // contains a value (e.g., a checkbox, textfield, select, etc.), ensure
      // that value is included in the submission. As per above, submissions
      // that use $.ajax() are already serialized prior to the element being
      // disabled, so this is only needed for IFRAME submissions.
      var v = $.fieldValue(this.element);
      if (v !== null) {
        options.extraData[this.element.name] = v;
      }
    }

    // Disable the element that received the change to prevent user interface
    // interaction while the Ajax request is in progress. ajax.ajaxing prevents
    // the element from triggering a new request, but does not prevent the user
    // from changing its value.
    $(this.element).prop('disabled', true);

    if (!this.progress || !this.progress.type) {
      return;
    }

    var progressIndicatorMethod = 'setProgressIndicator' + this.progress.type.slice(0, 1).toUpperCase() + this.progress.type.slice(1).toLowerCase();
    if (progressIndicatorMethod in this && typeof this[progressIndicatorMethod] === 'function') {
      this[progressIndicatorMethod].call(this);
    }
  };

  /**
   * Overrides the throbber progress indicator.
   */
  Drupal.Ajax.prototype.setProgressIndicatorThrobber = function () {
    // 'ajax-progress' class removed near 'ajax'progress'throbber' to align loader to the centre
    this.progress.element = $('<div class="row"><div class="col-12 md-12 sm-12"><div class=" ajax-progress-throbber"><div class="ajax-loader"><div class="spinner-wrapper"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div><!--/spinner-border--> </div><!--/spinner-wrapper--></div></div></div></div>');
    $('article').before(this.progress.element);
  };

})(jQuery, this, Drupal, drupalSettings);
