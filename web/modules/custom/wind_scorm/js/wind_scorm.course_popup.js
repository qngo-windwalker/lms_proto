
(function ($, window, Drupal) {

  'use strict';

  function popup(href){
    var day = new Date();
    var id = day.getTime();
    var screenHeight = screen.height >= 768 ? 700 : screen.height;
    var params = ['toolbar=no', 'scrollbars=no', 'location=no', 'statusbar=no', 'menubar=no', 'directories=no', 'titlebar=no', 'toolbar=no', 'resizable=1', 'height=' + screenHeight, 'width=1024'
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
  Drupal.behaviors.windScromCourse = {
    attach: function (context, settings) {

      // Using once to prevent code from executing multiple time: https://drupal.stackexchange.com/a/91610
      $('a.wind-scorm-popup-link').once('wind_scorm').click(function (event) {
        let href = $(event.currentTarget).attr('data-coure-href');
        popup(href);
      });

    }
  };


})(jQuery, window, Drupal);
