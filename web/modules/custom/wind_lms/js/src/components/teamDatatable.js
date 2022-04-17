import React, {Component} from 'react';
import ReactDOMServer from "react-dom/server";
import {Spinner, ExportCSV} from "./GUI";
import Utility from "../modules/utility";
import {ProgressBar} from "./courseProgress";
import ReactDOM from "react-dom";

const $ = require('jquery');
$.DataTable = require('datatables.net');
// Datatable with Bootstrap v4
// Note: 'datatables.net-bs4' caused Datatable export button to disappear. Commented out for now
// require( 'datatables.net-bs4');
// Needed for DataTable export buttons
require( 'datatables.net-buttons' )();
require( 'datatables.net-buttons/js/buttons.colVis.js' )();
// Note: modules/jszip.min works, but the NPM version jszip does NOT work.
require("../modules/jszip.min");
require("pdfmake");
require("../modules/vfs_fonts");
//Print view button
require( 'datatables.net-buttons/js/buttons.print.js' )();
// Export to Excel button
require( 'datatables.net-buttons/js/buttons.html5.js' )();

/**
 * @see https://medium.com/@ashish_dev/datatables-net-with-react-js-custom-delete-buttons-912bc2755474
 */

export default class TeamDatatable extends Component{
  constructor(props) {
    super(props);
    console.log(props);
    this.state = {
      items: [],
      isError: false,
      isLoaded: false,
    };

    // this.newRowMoreInfoClick = this.newRowMoreInfoClick.bind(this);
    // this.getMemberColumnContent = this.getMemberColumnContent.bind(this);
    // this.getProgressColumnContent = this.getProgressColumnContent.bind(this);
    // this.getOperationsContent = this.getOperationsContent.bind(this);
  };

  componentDidMount() {
    this.$el = $(this.el);
    this.$el.DataTable({
      data: this.props.data,
      columns: this.props.columns,
      // columnDefs: [
      //   {
      //     targets: [5],
      //     width: 180,
      //     className: "center",
      //     createdCell: (td, cellData, rowData) =>
      //       ReactDOM.render(
      //         <div id={rowData.tid} onClick={() => {this.props.deleteRow(rowData.tid); }}>
      //           Delete
      //         </div>, td ),
      //   },
      // ],
      ordering: true,
      // Todo: Find out why it's tot working. @see https://datatables.net/examples/advanced_init/length_menu.html
      // "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
      paging : true,
      // Dom positioning: https://datatables.net/examples/basic_init/dom.html
      // f - Filtering input
      // t - The Table!
      // p - Pagination
      // dom: 'Bfrtip',
      dom: '<"data-table-wrapper"t>',
      buttons: [
        {
          title: 'Team',
          exportOptions: {
            columns: [0,1,2,] // To exclude Operation column
          },
          extend: 'excelHtml5',
          //   autoFilter: true,
          //   sheetName: 'Worksheet 1'
        },
        {
          title: 'Team',
          exportOptions: {
            columns: [0,1,2,] // To exclude Operation column
          },
          extend: 'csvHtml5',
        },
        {
          title: 'Team',
          exportOptions: {
            columns: [0,1,2,] // To exclude Operation column
          },
          autoFilter: true,
          extend: 'pdfHtml5',
        },
      ],
      initComplete: this.onDataTableInitComplete,
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

  componentWillUnmount() {
    $(".data-table-wrapper").find("table").DataTable().destroy(true);
  }

  reloadTableData(data){
    const table = $('.data-table-wrapper').find('table').DataTable();
    table.clear();
    table.rows.add(data);
    table.draw();
  }

  shouldComponentUpdate(nextProps, nextState){
    if (nextProps.data.length !== this.props.data.length) {
      this.reloadTableData(nextProps.data);
    }
    return false;
  }

  onDataTableInitComplete(settings, json){
    // Add some magic.
    $('#team-tbl thead').addClass('thead-light');
  }

  render() {
    return (
      <div>
        <table id="team-tbl" className="table table-curriculum responsive-enabled mb-5" data-striping="1"
               ref={(el) => (this.el = el)}
        />
      </div>
    );
  }
}
