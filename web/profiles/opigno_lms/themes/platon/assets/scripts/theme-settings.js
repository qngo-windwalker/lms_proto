/**
 * @file
 * Define theme settings form JS logic.
 */
(function($, Drupal, window, undefined) {

  // Drupal.settings.platon = Drupal.settings.platon || {};

  Drupal.behaviors.platonThemeSettings = {

    attach: function(context, settings) {

      // Prevent ajax callbacks reload
      if ($('#platon-css-editor').length) {
        return;
      }

      var $cssEditor = $('textarea[name="platon_css_override_content"]', context);
      if ($cssEditor.length && window.ace !== undefined) {

        // Hide the actual textarea and remove the grippie.
        $cssEditor.hide();
        $cssEditor.parent().find('div.grippie').hide();

        // Create a CSS editor for Ace.
        $cssEditor.after('<div id="platon-css-editor" style="min-height: 400px;">' + $cssEditor.val() + '</div>');
        var $aceEditor = $('#platon-css-editor');

        // Initialize the Ace editor and set it to CSS mode.
        var editor = ace.edit('platon-css-editor');
        require("ace/edit_session").EditSession.prototype.$useWorker = false;

        editor.setTheme('ace/theme/monokai');
        editor.getSession().setMode('ace/mode/css');
        editor.getSession().setUseSoftTabs(true);
        editor.getSession().setTabSize(2);
        editor.getSession().on('change', function(e) {
          $cssEditor.val(editor.getValue());
        });
      }
    }
  };
})(jQuery, Drupal, window);
