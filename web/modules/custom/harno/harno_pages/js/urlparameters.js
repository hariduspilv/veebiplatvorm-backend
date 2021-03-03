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
    this.options.inputs = this.options.form.find(".js-range-slider, input[type='radio'],input[type='text'], input[type='checkbox']");
    this.bindFilters();
    this.bindHashChange();
    this.restoreCheckedStatus();
  },
  bindFilters: function () {
    var self = this;
    self.options.inputs.on("input", function (e) {
      e.preventDefault();
      if ($(this).attr('name') === 'gallerySearch') {
        self.options.form.find('#edit-gallerysearchmobile').val($(this).val());
      }
      if ($(this).attr('name') === 'gallerySearchMobile') {
        self.options.form.find('#edit-gallerysearch').val($(this).val());
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
    var inputs = self.options.inputs.filter(":checked, [type='text']");

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
      var value = input.val().replace(';', '-');
      if (hashArray[name]) {
        hashArray[name] = hashArray[name] + "," + value;
      } else {
        hashArray[name] = value;
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
  //Remove from active filter bar
  //$(this.parentElement).remove();

});
}
$(document).ready(function () {
  $("span[data-remove-item]").on('click',function(){
    var yearToRemove = $(this).attr("data-remove-item");
    console.log(yearToRemove);
    $("input[name='years["+yearToRemove+"]']").trigger("click");
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
