/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.harno_theme_search_results = {
    attach: function (context, settings) {
      $(document, context).each( function() {
        //tõsta pealkirja vastuste koguarv
        var search_total = 0;
        if ($('#search_result_total_span').length) {
          search_total = $('#search_result_total_span').html();
        }
        $('h1').html(Drupal.t('Search results') + ' (' + search_total + ')');

        //millised sisutüübi filtri kastid ära peita
        if($('#search_page_url_span').length) {
          var url = $('#search_page_url_span').text();
          $.ajax({
            url: url,
            cache: false,
            success: function (response) {
              var parsedResponse = $.parseHTML(response);
              for (var o = 1; o <= 8; o++) {
                if ($(parsedResponse).find('#search_result_' + o + '_span').length) {
                  $('#search-item-' + o + '-span').fadeIn();
                }
              }
            }
          });
        }
        else {
          var hide_parent = 1;
          for (var o = 1; o <= 8; o++) {
            if ($('#search_result_' + o + '_span').length) {
              $('#search-item-' + o + '-span').fadeIn();
              hide_parent = 0;
            }
          }
          if (hide_parent) {
            $('.filters-top').fadeOut();
          }
        }
        //mobiilis sisutüübi filtri duubeldamine
        var clone_html = '';
        $('.filters-top input:checked').each(function () {
          var input_value = $(this).val();
          var $clone = $('#search-item-' + input_value + '-span').clone();
          $clone.find(':checked').prop('checked', false).addClass('search_type_mobile').attr('id', 'search-item-mobile-'+ input_value).removeAttr('onchange').attr('name', '');
          $clone.find('.btn-tag').addClass('btn-tag-remove');
          $clone.find('label').attr('for', 'search-item-mobile-'+ input_value );
          clone_html = clone_html + $clone.html();
        });
        $('.mobile-filters-output').html(clone_html);

        //mobiili X filtril klikkides muudame algse filtri väärtust ja trigerdame selle muutuse, mis omakorda trigerdab vormi submiti.
        $( '.search_type_mobile' ).on( "click", function() {
          var search_type_id = $(this).val();
          $("input[name='search_type[" + search_type_id + "]']").prop('checked', false).change();
        });

        //eemaldada sisutüübi filter, kui otsingusõna muutub
        $("input[name='keys']").on("change", function() {
          for (var o = 1; o <= 8; o++) {
            if ($('#search-item-' + o).length) {
              $('#search-item-' + o).prop('checked', false);
            }
          }
        });
      });

      //vormi submit kui on valitud filter
      $( '.search_type_checkbox' ).on( "click", function() {
        if ( !$('.filters-wrapper').hasClass('modal-open') ) {
          $('.search-submit-btn').click();
        }
      });
      //vormi submit kui klikitakse mobiilis valmis nuppu
      $( '.filters-ready' ).on( "click", function(event) {
        event.preventDefault();
        $('body').removeClass('modal-open');
        $('.search-submit-btn').click();
      });
      //vormi submit enteri vajutamisel peab enne tühjendama filtrid ja siis trigerdama ajaxi
      $( "input[name='keys']" ).keypress(function(event) {
        if (event.keyCode == 13) {
          event.preventDefault();
          $(this).change();
          $('.search-submit-btn').click();
        }
      });

      $( document ).ajaxComplete(function() {
        $wpm.initialize();
      });
    }
  };

})(jQuery, Drupal);
