/**
 * @file
 *  Related JavaScript.
 */

(function($) {
  Drupal.behaviors.aspambot = {
    attach: function(context, settings) {
      var $countriesSelect = $('#settings-form #edit-countries', context);
      if ($countriesSelect.length && $.fn.multiSelect) {
        $countriesSelect.multiSelect();
      }
    }
  };
})(jQuery);
