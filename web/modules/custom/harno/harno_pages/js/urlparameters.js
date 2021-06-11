$.fn.formFilter = function () {


  $(this).each(function () {
    $.formFilter.init($(this));
  });

};


$.fn.formFilter = function () {
  $.formFilter.initialize($(this));
};

$.formFilter = {
  options: {},
  templates: {},
  values: {},

  containers: {
    activeFilters: ".gallery-filter-form"
  },
  initialize: function (form) {
    this.options.form = form;
    this.options.inputs = this.options.form.find(".js-range-slider, input[type='radio'],input[type='text'], input[type='checkbox'], select");
    this.bindFilters();
    this.bindHashChange();
    this.restoreCheckedStatus();
  },
  bindFilters: function () {
    var self = this;
    self.options.inputs.on("input", function (e) {
      e.preventDefault();
      var name = $(this).attr('name');
      var val = $(this).val();
      var relations = {
        'gallerySearch': '#edit-gallerysearchmobile',
        'gallerySearchMobile': '#edit-gallerysearch',
        'newsSearch': '#edit-newssearchmobile',
        'newsSearchMobile': '#edit-newssearch',
        'departments': '#worker-department',
        'positions': '#worker-position',
        'contactsSearchMobile': '#edit-contactssearchmobile',
        'contactsSearch': '#edit-contactssearch',
        'article_type': '#article_type_mobile',
        'article_type_mobile': '#article_type_mobile',
      }

      console.log(name);
      console.log(val);
      if (relations[name]) {
        self.options.form.find(relations[name]).val(val);
      }

      if($("input[id~='article_type']")){
        var toFind = '#edit-article-type-mobile-'+$(this)[0].value;
        var boxToCheck = self.options.form.find(toFind);
        boxToCheck.prop('checked',$(this)[0].checked);
      }
      if($("input[id~='article_type_mobile']")){
        var toFind = '#edit-article-type-'+$(this)[0].value;
        var boxToCheck = self.options.form.find(toFind);
        boxToCheck.prop('checked',$(this)[0].checked);
      }
      self.pushURL();
    });
    self.options.inputs.on("change", function (e) {

      e.preventDefault();

      if ($(this).attr('name') === 'sort') {
        self.options.form.find('#sort-text').text($(this).next('label').text().toLowerCase().trim());
      }
      self.pushURL();
    });

    self.bindHashChange();
  },

  bindDeleteFilter: function () {
    var self = this;

    $(self.containers.activeFilters).find('a').unbind("click").bind("click", function (e) {
      e.preventDefault();
    });

    $(self.containers.activeFilters).find('.delete-filter').unbind("click").bind("click", function (e) {
      e.preventDefault();

      var rel = $(this).attr('rel');

      self.options.inputs.filter('[value="' + rel + '"]').trigger('click');
    });

    $(self.containers.activeFilters).find('.delete-all').unbind("click").bind("click", function (e) {
      e.preventDefault();

      var inputs = self.options.inputs.filter(":checked");

      inputs.trigger('click');
    });
  },

  pushURL: function (from, to) {
    var self = this;
    var inputs = self.options.inputs.filter(":checked, [type='text'], input[type='checkbox'], select");

    var hash = '';
    var hashArray = {};

    if (from || to) {
      var priceRange = "min_price=" + from + "&max_price=" + to
      hashArray.min = from
      hashArray.max = to
    }

    inputs.each(function () {
      var input = $(this);
      var name = input.attr("name");
      var value = input.val() ? input.val().replace(';', '-') : undefined;

      if (value) {
        if (hashArray[name]) {
          hashArray[name] = hashArray[name] + "," + value;
        } else {
          hashArray[name] = value;
        }
      }

    });

    for (var i in hashArray) {
      if (hash !== "") {
        hash += "&"
      }
      hash += i + "=" + hashArray[i];
    };

    window.history.replaceState(undefined, undefined, '?' + hash);
    $(window).trigger('querychange');

  },

  hashChangeEvent() {
    var hash = window.location.search;
    this.options.hashArray = this.getParameters(hash);
    this.options.hash = hash;
    this.restoreCheckedStatus();
  },

  bindHashChange: function () {
    var self = this;

    $(window).on("querychange", function (e) {
      e.preventDefault();
      self.hashChangeEvent();
    });
  },

  restoreCheckedStatus() {
    var self = this;
    var hash = window.location.search;
    this.options.hashArray = this.getParameters(hash);
    self.options.inputs.each(function () {
      var input = $(this);
      var name = input.attr("name");
      var value = input.val();

      var filterValues = self.options.hashArray[name];
      if (filterValues) {
        //if (filterValues && (self.options.hashArray[name] == value)) {
        filterValues = filterValues.split(',');
        filterValues.forEach(function (item) {
          if (item === value) {
            input.prop("checked", true);
            input.parent().addClass("active").addClass('is-focused');

          }
        });
      } else {
        input.prop("checked", false);
        input.parent().removeClass("active");
      }
    });

    this.options.inputs.filter('[name="sort"]:checked').each(function () {
      self.options.form.find('#sort-text').text($(this).next('label').text().toLowerCase().trim());
    });
  },
  getParameters(hash) {
    params = {}
    var keyValuePairs = hash.substr(1).split('&');
    for (x in keyValuePairs) {
      var split = keyValuePairs[x].split('=', 2);
      params[split[0]] = (split[1]) ? decodeURI(split[1]) : "";
    }
    return params;
  }
}
$.fn.filterRefresh = function(){
  $("span[data-remove-item]").on('click',function(){
  var yearToRemove = $(this).attr("data-remove-item");
    console.log(yearToRemove);
  $("input[name='years["+yearToRemove+"]']").trigger("click");
  $("input[name='["+yearToRemove+"]']").trigger("click");
  // $("input[name='"+yearToRemove+"']").trigger("click");
  //Remove from active filter bar
  //$(this.parentElement).remove();

});
}
$(document).ready(function () {
  $("span[data-remove-item]").on('click',function(){
    var yearToRemove = $(this).attr("data-remove-item");
    console.log(yearToRemove);
    $("input[name='years["+yearToRemove+"]']").trigger("click");
    // $("input[name='["+yearToRemove+"]']").trigger("click");
    // const url = new URL(window.location.href)
    // const urlObj = new URL(url);
    // const params = urlObj.searchParams
    // const $checks = $(':checkbox')
    // // on page load check the ones that exist un url
    // params.forEach((val, key) => $checks.filter('[name="' + key + '"]').prop('checked', true));
    //
    // $checks.change(function(){
    //   // append when checkbox gets checked and delete when unchecked
    //   if(this.checked){
    //     params.append(this.name, 'true')
    //   }else{
    //     params.delete('departments');
    //     params.delete(this.name);
    //   }
    //   window.location = urlObj.href;
    //
    // })
    //Remove from active filter bar
    //$(this.parentElement).remove();

  });
  $(window).on('load', function () {

    $.fn.formFilter = function () {
      $(this).each(function () {
        $.formFilter.init($(this));
      });
    };
  })
  $('[role="filter"]').formFilter();
});
