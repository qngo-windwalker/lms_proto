function syntaxHighlight(json) {
  if (typeof json != 'string') {
    json = JSON.stringify(json, undefined, 2);
  }
  json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
    var cls = 'number';
    if (/^"/.test(match)) {
      if (/:$/.test(match)) {
        cls = 'key';
      } else {
        cls = 'string';
      }
    } else if (/true|false/.test(match)) {
      cls = 'boolean';
    } else if (/null/.test(match)) {
      cls = 'null';
    }
    return '<span class="' + cls + '">' + match + '</span>';
  });
}

(function ($, window, Drupal) {
  'use strict';

  function getData( row, type, val, meta ) {
    return `<pre><code>${syntaxHighlight(row.tincan_statements)}</code></pre>`;
  }

  let columns = [
    {
      title: 'Statement Id',
      // width: 120,
      className : "first-child",
      data: 'id',
    },
    {
      title: 'Timestamp',
      data: 'timestamp',
    },
    {
      title: 'Result',
      data: function(row, type, val, meta){
        return `<pre><code>${syntaxHighlight(row.result)}</code></pre>`;
      }
    },
    {
      title: 'Object',
      data: function(row, type, val, meta){
        return `<pre><code>${syntaxHighlight(row.object)}</code></pre>`;
      }
    },
  ];

  Drupal.ChNavAdminTincanUserCourse = Drupal.ChNavAdminTincanUserCourse || {};
  Drupal.ChNavAdminTincanUserCourse.initialized = false;
  Drupal.ChNavAdminTincanUserCourse.init = function(Drupal, settings){
    console.log(settings.ch_nav_course.datatableData);
    Drupal.ChNavAdminTincanUserCourse.$dataTable = $('#tincan-user-course-tbl').DataTable({
      // Add attribute Id to <tr />, 'DT_RowId' must be a property of the items in Json data object array
      // rowId : 'DT_RowId',
      data: settings.ch_nav_course.datatableData,
      columns: columns,
      ordering: true,
      // Todo: Find out why it's tot working. @see https://datatables.net/examples/advanced_init/length_menu.html
      // "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
      paging : true,
      // Dom positioning: https://datatables.net/examples/basic_init/dom.html
      // f - Filtering input
      // t - The Table!
      // p - Pagination
      dom: 'Bfrtip',
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
  Drupal.behaviors.ChNavAdminTincanUserCourseBehaviors = {
    // Todo: Find out why attach() is calling 3 times by Drupal.
    attach: function (context, settings) {
      if (!Drupal.ChNavAdminTincanUserCourse.initialized) {
        Drupal.ChNavAdminTincanUserCourse.init(Drupal, settings);
        Drupal.ChNavAdminTincanUserCourse.initialized = true;
      }
    }
  };
})(jQuery, window, Drupal);
