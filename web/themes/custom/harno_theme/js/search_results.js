/**
 * @file
 * Global utilities.
 *
 */
(function ($, Drupal) {

  'use strict';

  Drupal.behaviors.harno_theme_search_results = {
    attach: function (context, settings) {
      $(document, context).once('harno_theme_search_results').each( function() {
        //tõsta pealkirja vastuste koguarv
        var search_total = 0;
        if ($('#search_result_total_span').length) {
          search_total = $('#search_result_total_span').html();
        }
        $('h1').append(' (' + search_total + ')');

        //millised sisutüübi filtri kastid ära peita
        if($('#search_page_url_span').length) {
          var url = $('#search_page_url_span').text();
          $.ajax({
            url: url,
            cache: false,
            success: function (response) {
              var parsedResponse = $.parseHTML(response);
              for (var o = 1; o <= 8; o++) {
                if (!$(parsedResponse).find('#search_result_' + o + '_span').length) {
                  $('#search-item-' + o + '-span').remove();
                }
              }
            }
          });
        }
        else {
          var hide_parent = 1;
          for (var o = 1; o <= 8; o++) {
            if ($('#search_result_' + o + '_span').length) {
              hide_parent = 0;
            } else {
              $('#search-item-' + o + '-span').remove();
            }
          }
          if (hide_parent) {
            $('.filters-top, .mobile-filters').remove();
          }
        }
        //mobiilis sisutüübi filtri duubeldamine
        $('.filters-top input:checked').each(function () {
          var input_value = $(this).val();
          var $clone = $('#search-item-' + input_value + '-span').clone();
          $clone.find(':checked').prop('checked', false).addClass('search_type_mobile').attr('id', 'search-item-mobile-'+ input_value).removeAttr('onchange').attr('name', '');
          $clone.find('.btn-tag').addClass('btn-tag-remove');
          $clone.find('label').attr('for', 'search-item-mobile-'+ input_value );
          $('.mobile-filters-output').append($clone);
        });
        $( '.search_type_mobile' ).on( "click", function() {
          var search_type_id = $(this).val();
          $("input[name='search_type[" + search_type_id + "]']").prop('checked', false).change();
        });
        //eemaldada sisutüübi filter, kui otsingusõna muutub
        $('#edit-keys').change(function() {
          for (var o = 1; o <= 8; o++) {
            if ($('#search-item-' + o).length) {
              $('#search-item-' + o).prop('checked', false);
            }
          }
        });
      });
    }
  };

})(jQuery, Drupal);
