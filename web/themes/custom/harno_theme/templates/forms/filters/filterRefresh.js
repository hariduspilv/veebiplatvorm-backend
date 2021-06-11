$(document).ready(function () {
  $("span[data-remove-item]").on('click',function(){
    var yearToRemove = $(this).attr("data-remove-item");
    $("input[name='years["+yearToRemove+"]']").trigger("click");
    //Remove from active filter bar
    //$(this.parentElement).remove();

  });
});
$.fn.filterRefresh = function(){
  $("span[data-remove-item]").on('click',function(){
    var yearToRemove = $(this).attr("data-remove-item");
    $("input[name='years["+yearToRemove+"]']").trigger("click");
    //Remove from active filter bar
    //$(this.parentElement).remove();

  });
}
