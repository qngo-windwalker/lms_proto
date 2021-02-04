import React, {Component} from 'react';
import axios from "axios";
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

export default class CurriculumTable extends Component{
  constructor(props) {
    super(props);
    this.state = { tableRow: [] };

    this.newRowMoreInfoClick = this.newRowMoreInfoClick.bind(this)
  };

  componentDidMount() {
    let url = new URL(window.location.href);
    let testParam = url.searchParams.get('test') ? 'test=true' : '';
    let langParam = this.isEnglishMode() ? 'en' : 'es';
    this.getRecords(`/wl-datatable/curriculum`);
  }

  componentDidUpdate(){
    // this.addPopupClickEvent();
  }

  async getRecords(url) {
    axios.get(url)
      .then(res => {
        this.initDataTable(res.data);
      })
      .catch(function (error) {
        if (error.response) {
          // The request was made and the server responded with a status code
          // that falls out of the range of 2xx
          console.log(error.response.data);
          console.log(error.response.status);
          console.log(error.response.headers);
        } else if (error.request) {
          // The request was made but no response was received
          // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
          // http.ClientRequest in node.js
          console.log(error.request);
        } else {
          // Something happened in setting up the request that triggered an Error
          console.log('Error', error.message);
        }
        console.log(error.config);
      });
  }

  isEnglishMode() {
    let pathname = window.location.pathname;
    // if we are on 'es' spanish mode
    if(pathname.split('/')[1] == 'es'){
      return false;
    }
    return true;
  }

  render(){
    return (
      <div className="section">
        <h3 className="mb-3">{this.isEnglishMode() ? 'Curriculum' : 'Plan de estudios'}</h3>
        <table id="curriculum-tbl" ref="main" className="table table-curriculum responsive-enabled mb-5" data-striping="1" />
      </div>
    );
  }

  /**
   * @see https://stackoverflow.com/a/847196
   * @param unix_timestamp
   * @returns {string}
   */
  unixTimestampToTime(unix_timestamp){
    let a = new Date(unix_timestamp * 1000);
    let months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    let year = a.getFullYear();
    let month = months[a.getMonth()];
    let date = a.getDate();
    let hour = a.getHours();
    let min = a.getMinutes();
    let sec = a.getSeconds();
    let time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
    let ddmmyy = date + ' ' + month + ' ' + year ;
    return ddmmyy;
  }

  initDataTable(data) {
    console.log(data);
    let columns = [
      {
        title: 'Title',
        // width: 120,
        data: 'title',
        className : "first-child"
      },
      {
        title: 'Course',
        // width: 120,
        data: 'course',
        className : "first-child"
      },
      {
        title: 'Status',
        data: 'status'
      },
      {
        title: 'Action',
        data: 'action',
        orderable: false,
        className : "td-action"
      }
    ];
    let dt = $(this.refs.main).DataTable({
      // ajax : {
      //   url : url,
      // },
      // Add attribute Id to <tr />, 'DT_RowId' must be a property of the items in Json data object array
      rowId : 'DT_RowId',
      data: data.data,
      columns: columns,
      // columnDefs: [ {
      //   "targets": 3,
      //   "data": function ( row, type, val, meta ) {
      //     console.log(row)
      //     console.log(type)
      //     console.log(val)
      //     return ;
      //   }
      // } ],
      ordering: true,
      // Todo: Find out why it's tot working. @see https://datatables.net/examples/advanced_init/length_menu.html
      "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
      paging : true,
      // Dom positioning: https://datatables.net/examples/basic_init/dom.html
      // f - Filtering input
      // t - The Table!
      // p - Pagination
      dom: 'Bfrtip',
      // @see https://datatables.net/extensions/buttons/examples/initialisation/export.html
      buttons: [
        // {
        //   extend: 'excelHtml5',
        //   autoFilter: true,
        //   sheetName: 'User Progress data'
        // },
        'excelHtml5',
        'csvHtml5',
        'pdfHtml5',
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

    //@see https://datatables.net/examples/server_side/row_details.html
    // Array to track the ids of the details displayed rows
    // this.detailRows = [];

    $('#curriculum-tbl tbody').on( 'click', 'tr td.td-action a', (e) => this.newRowMoreInfoClick(e, dt));

    //@see https://datatables.net/examples/server_side/row_details.html
    // On each draw, loop over the `detailRows` array and show any child rows
    // dt.on( 'draw', function () {
    //   $.each( this.detailRows, function ( i, id ) {
    //     $('#'+id+' td.details-control').trigger( 'click' );
    //   } );
    // } );
  }

  /**
   * @param e
   * @param dt
   * @see https://datatables.net/examples/api/row_details.html
   */
  newRowMoreInfoClick(e, dt) {
    console.log('clicked');
    let $this = $(e.currentTarget);
    // Only applies to the menu button. The View and Edit buttons should behave normally
    if ($this.hasClass('anchor-info')) {
      e.preventDefault();
    }

    // On/Off
    $this.hasClass('active') ? $this.removeClass('active') : $this.addClass('active');

    var tr = $this.closest('tr');
    var row = dt.row( tr );
    // var idx = $.inArray( tr.attr('id'), this.detailRows );

    if ( row.child.isShown() ) {
      tr.removeClass( 'details' );
      row.child.hide();

      // Remove from the 'open' array
      // this.detailRows.splice( idx, 1 );
    } else {
      tr.addClass( 'details' );
      row.child( this.format( row.data() ) ).show();

      // Add to the 'open' array
      // if ( idx === -1 ) {
      //   this.detailRows.push( tr.attr('id') );
      // }
    }
  }

  format (rowData) {
    return 'Full name: The child row can contain any data you wish, including links, images, inner tables etc.';
  }

  onDataTableInitComplete(settings, json){
    // Add some magic.
    $('#curriculum-tbl thead').addClass('thead-light');
  }
}
