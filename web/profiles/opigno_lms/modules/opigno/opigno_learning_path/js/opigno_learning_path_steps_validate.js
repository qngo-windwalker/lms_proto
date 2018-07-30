(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.opignoLearningPathStepsValidate = {
    attach: function (context, settings) {
      // Hide button 'next' if 'title' is empty.
      // $('#group-learning-path-edit-form #edit-label-0-value').once().keyup(function () {
      //   if (!$(this).val()) {
      //     $('.d-flex a.btn').css({
      //       'pointer-events': 'none',
      //       'opacity': 0.6
      //     });
      //   }
      //   else {
      //     $('.d-flex a.btn').css({
      //       'pointer-events': 'all',
      //       'opacity': 1
      //     });
      //   }
      // })
    },
  };
}(jQuery, Drupal, drupalSettings));
