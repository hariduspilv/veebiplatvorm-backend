!function ($) {
  $(document).ready(function () {
    $.fn.modal = function(){
      $(this).each(function(){
        var main = $(this);
        var href = main.attr('href');
        var html, overlay;
        var visible = false;

        main.on('click', function(e){
          e.preventDefault();
          openOverlay();
          getData();

        });

        function getData(newHref) {

          var tmpHref = newHref ? newHref : href;

          xhr = $.ajax({
            dataType: "html",
            url: tmpHref,
            cache: false,
            success: function(response){
              html = $(response).find("[data-modal]")[0].outerHTML;
              appendOverlay();
            }
          });
        }

        function openOverlay() {
          var modal = $('.contact-modal');

          var output = 	'<div class="overlay" data-close="true">';
          output+= 	'</div><!--/overlay-->';

          $('body').append(overlay = $(output));
          $('body').addClass('modal-open');


          overlay.fadeIn(250, function(){

          });

          overlay.on('click', function(e){
            if($(e.target).is('[data-close]')){
              closeOverlay();
            }
          });

          $(window).on('keyup.modal', function(e){
            if(e.keyCode == 27){
              closeOverlay();
              $(window).off("keydup.modal");
            }
          });
        }

        function closeOverlay() {

          visible = false;
          html = "";

          setTimeout(function(){
            overlay.fadeOut(250, function(){
              $("body").removeClass("modal-open");
              overlay.remove();
            });
          }, 250);
        }

        function appendOverlay() {
          var output = '';

          output+= '<div class="overlay-inner">'
          output+= html;
          output+= '</div><!--/overlay-inner-->';

          overlay.html(output);

          var closeBtn = overlay.find('.btn-close');
          var firstItem = overlay.find('.contact-modal__header').children().first();
          var lastItem = overlay.find('.contact-modal__body').children().last();
          closeBtn.focus();

          tabFocusTrap(firstItem, lastItem, closeBtn)
        }

        function tabFocusTrap(firstItem, lastItem, close){
          $(document).on('keyup', function(e){
            lastItem.on('keyup', function(e){
              if(e.keyCode == 9 || e.which == 9) {
                e.preventDefault();
                close.focus();
              }
            });

            firstItem.on('keyup', function(e){
              if(e.keyCode == 9 || e.which == 9 && e.keyCode == 16 || e.which == 16) {
                e.preventDefault();
                lastItem.focus();
              }
            });
          });
        }
      });
    }
  });
}(window.jQuery);
