(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.opignoLearningPathTrainingContent = {
    attach: function (context, settings) {
      var $step_show = $('.lp_step_show', context);
      var $step_hide = $('.lp_step_hide', context);

      $step_show.once('click').click(function (e) {
        e.preventDefault();

        var $parent = $(this).parent('.lp_step');

        if (!$parent) {
          return false;
        }

        $parent.find('.lp_step_details_wrapper').show();
        $parent.find('.lp_step_show').hide();
        $parent.find('.lp_step_hide').show();

        return false;
      });

      $step_hide.once('click').click(function (e) {
        e.preventDefault();

        var $parent = $(this).parent('.lp_step');

        if (!$parent) {
          return false;
        }

        $parent.find('.lp_step_details_wrapper').hide();
        $parent.find('.lp_step_show').show();
        $parent.find('.lp_step_hide').hide();

        return false;
      });
    },
  };
}(jQuery, Drupal, drupalSettings));
