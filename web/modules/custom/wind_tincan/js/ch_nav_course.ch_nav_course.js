
;(function ($, Drupal, drupalSettings) {
  'use strict';

  /**
   * Provide the initiation of the datatable on the manager page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the block settings summaries.
   */
  Drupal.behaviors.chNavCourseCourse = {
    attach: function (context, settings) {
      console.log('attached');
      // Listen to the unload event. Some users click "Next" or go to a different page, expecting
      // their data to be saved. We try to commit the data for them, hoping ot will get stored.
      $(window).bind('beforeunload', function() {
        console.log(window);
      });

    }
  };

})(jQuery, Drupal, drupalSettings);
