
(function ($, window, Drupal) {

  'use strict';

  let columns = [
    {
      title: 'Username',
      // width: 120,
      className : "first-child",
      data: function ( row, type, val, meta ) {
        return `<a href="/user/${row.user.uid}">${row.user.username}</a>` ;
      },
    },
    {
      title: 'Email',
      className : "mail-col",
      data: function ( row, type, val, meta ) {
        return `<a href="mailto:${row.user.mail}">${row.user.mail}</a>` ;
      },
    },
    {
      title: 'Full Name',
      data: 'user.fullName'
    },
    {
      title: 'Team',
      data:  function ( row, type, val, meta ) {
        let team = '';
        for (let i = 0; i < row.user.field_team.length; i++) {
          team += ` <span style="white-space: nowrap"> ${row.user.field_team[i].label}</span>`;
        }
        return team;
      },
    },
    {
      title: 'User Status',
      data:  function ( row, type, val, meta ) {
        return (row.user.status) ? '<span class="text-success">&#9679;</span>  Active' : '<span class="text-danger">&#9679;</span> Inactive';
      }
    },
    {
      title: 'Course',
      data:  function ( row, type, val, meta ) {
        return `<a href="/node/${row.course.nid}">${row.course.title}</a>` ;
      }
    },
    {
      title: 'Completed',
      data: 'course.isCompleted'
    },
    {
      title: 'Course Category',
      data:  function ( row, type, val, meta ) {
        let cat = '';
        for (let i = 0; i < row.course.field_category.length; i++) {
          cat += ` <span style="white-space: nowrap"> ${row.course.field_category[i].label}</span>`;
        }
        return cat;
      },
    },
    // {
    //   title: 'Operations',
    //   data: 'action',
    //   orderable: false,
    //   className : "td-action"
    // }
  ];

  Drupal.WindLMSAdminUserProgresses = Drupal.WindLMSAdminUserProgresses || {};
  Drupal.WindLMSAdminUserProgresses.initialized = false;

  Drupal.WindLMSAdminUserProgresses.init = function(Drupal, settings){


    Drupal.WindLMSAdminUserProgresses.$table = $(settings.windLMS.datatableElementId).DataTable({
      ajax : {
        url : settings.windLMS.datatableURL,
      },
      initComplete: function(settings, json){
        // Add some magic.
      },
      paging : false,
      columns: columns,
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
  Drupal.behaviors.ChNavAdminUserLicenses = {
    // Todo: Find out why attach() is calling 3 times by Drupal.
    attach: function (context, settings) {
      if (!Drupal.WindLMSAdminUserProgresses.initialized) {
        Drupal.WindLMSAdminUserProgresses.init(Drupal, settings);
        Drupal.WindLMSAdminUserProgresses.initialized = true;
      }
    }
  };

})(jQuery, window, Drupal);
