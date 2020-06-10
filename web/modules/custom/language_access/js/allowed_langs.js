(function ($) {
  Drupal.behaviors.allowedLangs = {
    attach: function (context, settings) {

      // Get the languages to unset.
      var langs_to_unset = drupalSettings.language_access.langs_to_unset;

      // Hide each language to unset from menu.
      $.each(langs_to_unset, function (index, value) {
        $("li.menu-item").find("[hreflang='" + value + "']").hide();
      })
    }
  };
})(jQuery);
