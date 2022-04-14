/**
 * https://stackoverflow.com/a/6078873
 * @param unix_timestamp
 * @returns {string}
 */
function timeConverter(UNIX_timestamp){
  var a = new Date(UNIX_timestamp * 1000);
  var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes();
  var sec = a.getSeconds();
  var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
  return time;
}

(function ($, window, Drupal) {
  'use strict';

  let columns = [
    {
      title: 'Id',
      // width: 120,
      className : "first-child",
      data: function(row, type, val, meta){
        return `<a href="/admin/tincan/statement/${row.id}?destination=${window.location.pathname}">${row.id}</a>`;
      }
    },
    {
      title: 'Statement Id',
      data: 'statementId'
    },
    {
      title: 'StatementJsonTimestamp',
      data: function(row, type, val, meta){
        return `${timeConverter(row.statementJsonTimestamp)}<pre>${row.statementJsonTimestamp}</pre>`;
      }
    },
    {
      title: 'Verb',
      data: function(row, type, val, meta){
        return row.verb.hasOwnProperty('en-US') ? row.verb['en-US'] : '';
      }
    },
    {
      title: 'Result',
      data: function(row, type, val, meta){
        return `<pre><code>${JSON.stringify(row.result, null, 2)}</code></pre>`;
      }
    }
  ];

  Drupal.TincanUserNode = Drupal.TincanUserNode || {};
  Drupal.TincanUserNode.initialized = false;

  Drupal.TincanUserNode.init = function(Drupal, settings){
    console.log(settings.wind_tincan.datatableData);
    Drupal.TincanUserNode.$dataTable = $('#tincan-user-course-tbl').DataTable({
      // Add attribute Id to <tr />, 'DT_RowId' must be a property of the items in Json data object array
      // rowId : 'DT_RowId',
      data: settings.wind_tincan.datatableData.tincanStatement,
      columns: columns,
      ordering: true,
      select: true,
      // Todo: Find out why it's tot working. @see https://datatables.net/examples/advanced_init/length_menu.html
      // "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
      paging : true,
      // Dom positioning: https://datatables.net/examples/basic_init/dom.html
      // f - Filtering input
      // t - The Table!
      // p - Pagination
      // dom: 'Bfrtip',
      // @see https://datatables.net/extensions/buttons/examples/initialisation/export.html
      // initComplete: this.onDataTableInitComplete,
      /**
       * @see Row grouping:
       *   https://datatables.net/release-datatables/examples/advanced_init/row_grouping.html
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
  Drupal.behaviors.TincanUserNode = {
    // Todo: Find out why attach() is calling 3 times by Drupal.
    attach: function (context, settings) {
      if (!Drupal.TincanUserNode.initialized) {
        Drupal.TincanUserNode.init(Drupal, settings);
        Drupal.TincanUserNode.initialized = true;
      }
    }
  };
})(jQuery, window, Drupal);
