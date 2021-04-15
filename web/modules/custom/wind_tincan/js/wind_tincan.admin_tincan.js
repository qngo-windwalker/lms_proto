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

  function getCourse( row, type, val, meta ) {
    let markup = '<table><thead><th>Title</th><th>Progress</th><th>Tincan Statements</th></thead><tbody>';
    if (!row.courses) {
      return 'No data';
    }
    for (let i = 0; i < row.courses.length; i++) {
      let course = row.courses[i];
      console.log(course.tincan_statements);
      let courseIdEncoded = btoa(course.tincan_course_id);
      let statementRecords = course.tincan_statements.length + ' records';
      markup += `<tr><td><a href="/admin/tincan/${row.user.uid}/course/${courseIdEncoded}">${course.title}</a></td><td>${course.progress}</td><td>${statementRecords}</td></tr>`;
    }
    markup += '</tbody></table>';
    return markup;
  }

  function getUser( row, type, val, meta ) {
    let markup = '<ul style="list-style: none; line-height: 3rem; magin: 0; padding: 0">';
    markup += '<li><span>Username:</span ><span style="float: right">' + row.user.username +  '</span></li>';
    markup += '<li><span>Uid:</span ><span style="float: right">' + row.user.uid +  '</span></li>';
    markup += '<li><span>Agent Id:</span ><span style="float: right">' + row.user.agentId +  '</span></li>';
    markup += '</ul>';
    return markup;
  }

  let columns = [
    {
      title: 'User',
      // width: 120,
      className : "first-child",
      data: getUser,
    },
    {
      title: 'Courses',
      // width: 120,
      className : "first-child",
      data: getCourse,
    },
  ];

  Drupal.WindTincanAdminTincan = Drupal.WindTincanAdminTincan || {};
  Drupal.WindTincanAdminTincan.initialized = false;
  Drupal.WindTincanAdminTincan.init = function(Drupal, settings){
    console.log(settings.wind_tincan.datatableData);
    Drupal.WindTincanAdminTincan.$dataTable = $('#tincan-tbl').DataTable({
      // Add attribute Id to <tr />, 'DT_RowId' must be a property of the items in Json data object array
      // rowId : 'DT_RowId',
      data: settings.wind_tincan.datatableData,
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
  Drupal.behaviors.WindTincanAdminTincanBehaviors = {
    // Todo: Find out why attach() is calling 3 times by Drupal.
    attach: function (context, settings) {
      if (!Drupal.WindTincanAdminTincan.initialized) {
        Drupal.WindTincanAdminTincan.init(Drupal, settings);
        Drupal.WindTincanAdminTincan.initialized = true;
      }
    }
  };

})(jQuery, window, Drupal);
