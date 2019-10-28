
(function ($, window, Drupal) {

  'use strict';

  Drupal.DatatableSuportTicket = Drupal.DatatableSuportTicket || {};
  Drupal.DatatableSuportTicket.initialized = false;

  Drupal.DatatableSuportTicket.init = function(Drupal, settings){
    Drupal.DatatableSuportTicket.$table = $(settings.wind_help.datatableElementId).DataTable({
      ajax : {
        url : settings.wind_help.datatableURL,
      },
      initComplete: function(settings, json){
        // Add some magic.
      },
      paging : false,
      columns: [
        { data: 'issueId'},
        { data: 'customfield_10113'},
        { data: 'status'},
        { data: 'description'},
        { data: 'createdDate'},
        { data: 'operations'}
      ],
      rowId : 'issue-' + 'issueId',
      createdRow : function(row, data, dataIndex){
        $(row).attr('data-jira-org-id', data.jiraOrgId);
      },
      // dom: 'Bfrtip',
      // buttons: ['csv', 'pdf', 'print']
      /**
       * @see Row grouping: https://datatables.net/release-datatables/examples/advanced_init/row_grouping.html
       * @param settings
       */
      drawCallback: function (settings) {
        var api = this.api();
        var rows = api.rows({page: 'current'}).nodes();

        // For every row, add another row underneath it.
        rows.each(function(row, i){
          var rowJsonData = this.data()[i];
          var $row = $(row);
          $row.addClass('standard-row');
          $row.after('<tr id="info-row-' + rowJsonData.nid + '" class="info-row"><td colspan="55"></td>')
        });

        $('a.anchor-info').click(function(evnt){
          evnt.preventDefault();
          var $this = $(this);
          var nid = $this.attr('data-nid');
          if ($this.hasClass('active')) {
            $this.removeClass('active');
            $this.parent().parent().parent().removeClass('active');
            $('#info-row-' + nid).removeClass('active');
            // $('#info-row-' + nid + ' *').hide('fast');
          } else {
            $this.addClass('active');
            $this.parent().parent().parent().addClass('active');
            $('#info-row-' + nid).addClass('active');
            // $('#info-row-' + nid + ' *').show('slow');
          }
        });
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
  Drupal.behaviors.WindHelpConnectorDatatableCourseOrg = {
    // Todo: Find out why attach() is calling 3 times by Drupal.
    attach: function (context, settings) {
      if (!Drupal.DatatableSuportTicket.initialized) {
        Drupal.DatatableSuportTicket.init(Drupal, settings);
        Drupal.DatatableSuportTicket.initialized = true;
      }
    }
  };

})(jQuery, window, Drupal);
