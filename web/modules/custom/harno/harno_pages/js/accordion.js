!function ($) {
  $(document).ready(function () {
    $.fn.accordion = function () {
      $(this).each(function () {
        var main = $(this);

        var accordionTitle = main.find('.accordion__title');
        accordionTitle.on('click', function () {
          var el = $(this);
          var accordionParent = el.parent();
          var accordionButton = el.find('.btn-accordion');
          accordionParent.toggleClass('active');

          if (accordionParent.hasClass('active')) {
            accordionButton.attr('aria-expanded', 'true')
          } else {
            accordionButton.attr('aria-expanded', 'false')
          }
        });
      });
    }
  });
}(window.jQuery);
