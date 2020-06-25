
(function ($, window, Drupal) {

  'use strict';

  Drupal.ChNavCourseAdminUserProgresses = Drupal.ChNavCourseAdminUserProgresses || {};
  Drupal.ChNavCourseAdminUserProgresses.initialized = false;

  Drupal.ChNavCourseAdminUserProgresses.init = function(Drupal, settings){
    Drupal.ChNavCourseAdminUserProgresses.$table = $(settings.ch_nav.datatableElementId).DataTable({
      ajax : {
        url : settings.ch_nav.datatableURL,
      },
      initComplete: function(settings, json){
        // Add some magic.
      },
      paging : false,
      columns: [
        { data: 'username'},
        { data: 'mail'},
        { data: 'fullName'},
        { data: 'licenseLink'},
        { data: 'field_paid'},
        { data: 'field_clearinghouse_role'},
        { data: 'field_enroll_date'},
        { data: 'courseTitle'},
        { data: 'courseProgress'},
        { data: 'stored_date'},
      ],
      rowId : 'rowUid',
      // dom: 'Bfrtip',
      // buttons: ['csv', 'pdf', 'print'],
      /**
       * @see Row grouping: https://datatables.net/release-datatables/examples/advanced_init/row_grouping.html
       * @param settings
       */
      drawCallback: function (settings) {
        // Save it for documentation on how to modify table rows.
        // var api = this.api();
        // var rows = api.rows({page: 'current'}).nodes();

        // For every row, add another row underneath it.
        // rows.each(function(row, i){
        // });
      }
    });
  }

  /**
   * Provide the initiation of the datatable on the client page.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the behavior for the block settings summaries.
   */
  Drupal.behaviors.ChNavAdminUserLicenses = {
    // Todo: Find out why attach() is calling 3 times by Drupal.
    attach: function (context, settings) {
      if (!Drupal.ChNavCourseAdminUserProgresses.initialized) {
        Drupal.ChNavCourseAdminUserProgresses.init(Drupal, settings);
        Drupal.ChNavCourseAdminUserProgresses.initialized = true;
      }
    }
  };

})(jQuery, window, Drupal);
