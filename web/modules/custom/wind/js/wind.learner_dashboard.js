
(function ($, window, Drupal) {

  'use strict';

  function popup(href){
    var day = new Date();
    var id = day.getTime();
    var screenHeight = screen.height >= 768 ? 900 : screen.height;
    var params = ['toolbar=no', 'scrollbars=no', 'location=no', 'statusbar=no', 'menubar=no', 'directories=no', 'titlebar=no', 'toolbar=no', 'resizable=1', 'height=' + screenHeight, 'width=1224'
      //            'fullscreen=yes' // only works in IE, but here for completeness
    ].join(',');
    window.open(href, "window" + id, params);
  }

  /**
   * Provide the initiation of the datatable on the manager page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the block settings summaries.
   */
  Drupal.behaviors.windLearnerDashboard = {
    attach: function (context, settings) {

      $('a.wind-scorm-popup-link').click(function (event) {
        event.preventDefault();
        var href = $(event.currentTarget).attr('href');
        popup(href);
      });

    }
  };

})(jQuery, window, Drupal);
