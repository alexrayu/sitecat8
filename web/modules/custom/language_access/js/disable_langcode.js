(function ($) {
  Drupal.behaviors.disableLangcode = {
    attach: function (context, settings) {

      // True if there is either an uploaded image or file.
      if ($('.node-page-form .field--type-image table.entities-list .field').length !== 0 || $('.node-page-form .field--type-file table.entities-list .field').length !== 0) {

        // Get language field form and select.
        var languageForm = $('.node-page-form .field--type-language .js-form-type-language-select');
        var languageSelect = $('.node-page-form .field--type-language select');

        // Make sure the form isnt disabled yet.
        if (!languageForm.hasClass('form-disabled')) {

          // Disable langcode field.
          languageForm.addClass('form-disabled');
          languageSelect.attr('disabled', 'disabled');
        }
      }
    }
  };
})(jQuery);