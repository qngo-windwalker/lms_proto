
(function ($, window, Drupal) {

  'use strict';

  Drupal.windPageReport = Drupal.windPageReport || {};

  Drupal.windPageReport.init = function(Drupal, settings){
    Drupal.windPageReport.$userTable = $('#learner-status-tbl').DataTable({
      ajax : {
        url : 'datatable/user-progress',
        // data : settings.wind.managePage
      },
      initComplete: function(settings, json){
        // Add some magic.
      },
      paging : false,
      columns: [
        {},
        {},
        {data: "emailLink"},
        {data: "progress"}
      ],
      rowId : 'rowId',
      dom: 'Bfrtip',
      buttons: ['csv', 'pdf', 'print']
    });
  }

  /**
   * Provide the initiation of the datatable on the manager page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the block settings summaries.
   */
  Drupal.behaviors.windPageReport = {
    attach: function (context, settings) {
      Drupal.windPageReport.init(Drupal, settings);
    }
  };

})(jQuery, window, Drupal);
