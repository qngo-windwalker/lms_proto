
(function ($, window, Drupal) {

  'use strict';

  Drupal.WindTincanAdminUserProgresses = Drupal.WindTincanAdminUserProgresses || {};
  Drupal.WindTincanAdminUserProgresses.initialized = false;

  Drupal.WindTincanAdminUserProgresses.init = function(Drupal, settings){
    Drupal.WindTincanAdminUserProgresses.$table = $(settings.windTincan.datatableElementId).DataTable({
      ajax : {
        url : settings.windTincan.datatableURL,
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
      // Dom positioning: https://datatables.net/examples/basic_init/dom.html
      // f - Filtering input
      // t - The Table!
      // p - Pagination
      dom: 'Bfrtip',
      // @see https://datatables.net/extensions/buttons/examples/initialisation/export.html
      buttons: [
        {
          extend: 'excelHtml5',
          autoFilter: true,
          sheetName: 'Exported data'
        }
      ],
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
      if (!Drupal.WindTincanAdminUserProgresses.initialized) {
        Drupal.WindTincanAdminUserProgresses.init(Drupal, settings);
        Drupal.WindTincanAdminUserProgresses.initialized = true;
      }
    }
  };

})(jQuery, window, Drupal);
