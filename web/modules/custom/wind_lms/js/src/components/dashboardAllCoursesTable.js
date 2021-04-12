import React, {Component} from 'react';
import axios from "axios";
import ReactDOMServer from "react-dom/server";
import _ from 'lodash';
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

export default class DashboardAllCoursesTable extends Component{
  constructor(props) {
    super(props);
    this.state = { tableRow: [] };

    this.newRowMoreInfoClick = this.newRowMoreInfoClick.bind(this)
  };

  componentDidMount() {
    let url = new URL(window.location.href);
    let testParam = url.searchParams.get('test') ? 'test=true' : '';
    let langParam = this.isEnglishMode() ? 'en' : 'es';
    this.getRecords(`/wl-datatable/courses`);
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
        <h3 className="mb-3">{this.isEnglishMode() ? 'Courses' : 'Cursos'}</h3>
        <table id="courses-tbl" ref="main" className="table table-curriculum responsive-enabled mb-5" data-striping="1" />
        <div className="clear-both">
          <a className="btn btn-primary " href="/node/add/course?destination=/dashboard"><i className="fas fa-plus-circle mr-1"></i> Add Course</a>
        </div>
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
    let columns = [
      {
        title: 'Title',
        // width: 120,
        className : "first-child",
        data: function ( row, type, val, meta ) {
          return `<a href="/node/${row.nid}">${row.title}</a>` ;
        },
      },
      {
        title: 'Learners ',
        data: function ( row, type, val, meta ) {
          return (row.field_learner_access == '1') ? 'Avail to All' : row.learners_data.length + ' Enrolled' ;
        },
        className : "learner-col"
      },
      {
        title: 'Category',
        data: function ( row, type, val, meta ) {
          let markup = '';
          _.forEach(row.category_data, (term) => {
            markup += '<span class="badge badge-pill badge-outline badge-secondary mr-1 mb-1" data-tid="' + term.tid + '">' + term.label + '</span>';
          });
          return markup;
        }
      },
      {
        title: 'Status',
        data:  function ( row, type, val, meta ) {
          return (row.status) ? '<span class="text-success">&#9679;</span>  Active' : '<span class="text-danger">&#9679;</span> Inactive';
        }
      },
      {
        title: 'Operations',
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

    $('#courses-tbl tbody').on( 'click', 'tr td.td-action a.anchor-info', (e) => this.newRowMoreInfoClick(e, dt));

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
    e.preventDefault();
    let $this = $(e.currentTarget);

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
    // return 'Full name: The child row can contain any data you wish, including links, images, inner tables etc.';
    return ReactDOMServer.renderToString(
      <div className="row-extra-info row">
        <div className="col-md-6">
          <h6>Package File</h6>
          <ul className="list-unstyled">
            {rowData['courses_data'].map((obj, index) => {
              return (<li className="mb-3" key={index} data-uid={obj.uid}>{obj.title}</li>);
            })}
          </ul>
        </div>
        <div className="col-md-6">
          <h6> Learner </h6>
          <ul className="list-unstyled">
            {rowData['learners_data'].map((obj, index) => {
              return (<li className="mb-3" key={index} data-uid={obj.uid}>{obj.full_name}</li>);
            })}
          </ul>
        </div>
      </div>
    );
  }

  onDataTableInitComplete(settings, json){
    // Add some magic.
    $('#courses-tbl thead').addClass('thead-light');
  }
}
