(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.opignoLearningPathAchievements = {
    attach: function (context, settings) {
      var $details_show = $('.lp_details_show', context);
      var $details_hide = $('.lp_details_hide', context);

      $details_show.once('click').click(function (e) {
        e.preventDefault();

        var $parent = $(this).parent('.lp_wrapper');

        if (!$parent) {
          return false;
        }

        $parent.find('.lp_details').show();
        $parent.find('.lp_details_show').hide();
        $parent.find('.lp_details_hide').show();

        return false;
      });

      $details_hide.once('click').click(function (e) {
        e.preventDefault();

        var $parent = $(this).parents('.lp_wrapper');

        if (!$parent) {
          return false;
        }

        var $details = $parent.find('.lp_details');
        var height = $details.height();

        $details.hide();
        $parent.find('.lp_details_show').show();
        $parent.find('.lp_details_hide').hide();

        window.scrollBy(0, -height);

        return false;
      });

      var $module_row = $('.lp_course_steps tr', context);
      $module_row.once('click').click(function (e) {
        e.preventDefault();

        var $panels = $('.lp_module_panel', context);
        $panels.hide();

        var $this = $(this);
        var $wrapper = $this.parents('.lp_course_steps_wrapper');
        var id = $this.attr('data-module-id');
        var $panel = $wrapper.find('.lp_module_panel[data-module-id="' + id + '"]');
        $panel.show();

        return false;
      });

      var $module_step = $('.lp_step_content_module .lp_step_summary_clickable', context);
      $module_step.once('click').click(function (e) {
        e.preventDefault();

        var $panels = $('.lp_module_panel', context);
        $panels.hide();

        var $this = $(this);
        var $wrapper = $this.parents('.lp_step_summary_wrapper');
        var id = $this.attr('data-module-id');
        var $panel = $wrapper.find('.lp_module_panel[data-module-id="' + id + '"]');
        $panel.show();

        return false;
      });

      var $module_panel_close = $('.lp_module_panel_close', context);
      $module_panel_close.once('click').click(function (e) {
        e.preventDefault();

        var $panel = $(this).parents('.lp_module_panel');
        $panel.hide();

        return false;
      });
    },
  };
}(jQuery, Drupal, drupalSettings));
