/**
 * @file
 * Preview for the Bartik theme.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  drupalSettings.color.themePath = 'themes/platon';

  Drupal.color = {
    imgChanged: false,
    initialized: false,

    callback: function (context, settings, $form, farb, height, width) {
      if (!this.imgChanged) {
        if (drupalSettings.color.logo == null) {
          $('#preview-user-account-information-picture img').attr('src', drupalSettings.path.baseUrl + drupalSettings.color.themePath + '/logo.png');
        }
        else {
          $('#preview-logo img').attr('src', drupalSettings.color.logo);
        }
        $('#preview-user-account-information-picture img').attr('src', drupalSettings.path.baseUrl + drupalSettings.color.themePath + '/dist/images/anonymous-account.png');
        this.imgChanged = true;
      }

      // This should be handled by Color, but seems to break with Platon (??)
      // Implement the scheme select change ourselves.
      // @todo Figure out why Color is not updating the form and correct it.
      if (!this.initialized) {
        $('select[name="scheme"]').change(function() {
          var value = $(this).val();
          if (value != '') {
            for (var color in drupalSettings.color.schemes[value]) {
              $('#palette input[name="palette[' + color + ']"]', $form).val(drupalSettings.color.schemes[value][color]).css({
                backgroundColor: drupalSettings.color.schemes[value][color],
                'color': farb.RGBToHSL(farb.unpack(drupalSettings.color.schemes[value][color]))[2] > 0.5 ? '#000' : '#fff'
              });
            }

            // Trigger change event.
            Drupal.color.callback(context, settings, $form, farb, height, width);
          }
          else {
            // Remove the background image.
            $('#preview-header').css('background-image', 'none');
          }
        });
        this.initialized = true;
      }

      var elements = {
        white: {
          color: [],
          background: []
        },
        very_light_gray: {
          color: [],
          background: ['#preview-main']
        },
        light_gray: {
          color: [],
          background: ['#preview-content']
        },
        medium_gray: {
          color: [],
          background: ['#preview-sidebar']
        },
        dark_gray: {
          color: [],
          background: []
        },
        light_blue: {
          color: [],
          background: ['.tabs a.inactive']
        },
        dark_blue: {
          color: ['a.preview-link'],
          background: ['.tabs a.active']
        },
        deep_blue: {
          color: [],
          background: ['#preview-header', '#preview-footer']
        },
        leaf_green: {
          color: [],
          background: ['a.action-element']
        },
        blood_red: {
          color: [],
          background: ['a.danger-element']
        }
      };

      for (var color in elements) {
        var colorHex = $('#palette input[name="palette[' + color + ']"]', $form).val();
        for (var i = 0, len = elements[color].color.length; i < len; i++) {
          $(elements[color].color[i], $form).css('color', colorHex);
        }
        for (var i = 0, len = elements[color].background.length; i < len; i++) {
          $(elements[color].background[i], $form).css('background-color', colorHex);
        }
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
