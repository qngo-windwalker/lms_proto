import ReactDOM from "react-dom";
import React, {Component, useEffect, useState} from 'react';
import _ from 'lodash';
import axios from "axios";
import ReactDOMServer from "react-dom/server";
import Certificate from "./certificate";
import {useHistory, useParams} from "react-router-dom";
const $ = require('jquery');
$.DataTable = require('datatables.net');
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

export default class DashboardAllUserProgressTable extends Component{
  constructor(props) {
    super(props);
    this.state = { tableRow: [] };
  };

  componentDidMount() {
    let url = new URL(window.location.href);
    let testParam = url.searchParams.get('test') ? 'test=true' : '';
    let langParam = this.isEnglishMode() ? 'en' : 'es';
    this.getRecords(`/wl-json/all-users-progress/?lang=${langParam}&${testParam}`);
  }

  componentDidUpdate(){
    // this.addPopupClickEvent();
  }

  async getRecords(url) {
    axios.get(url)
      .then(res => {
        this.initDataTable(res.data.userData);
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
        <h3 className="mb-3">{this.isEnglishMode() ? 'User Progress' : 'Progreso De Los Usuarios'}</h3>
        <table id="user-progress-tbl" ref="main" className="table table-user-progress responsive-enabled mb-5" data-striping="1" />
        <div className="clear-both">
          <a className="btn btn-primary " href="/admin/people/create?destination=/dashboard"><i className="fas fa-plus-circle mr-1"></i> Add User</a>
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
    let self = this;
    let columns = [
      {
        title: 'Username',
        // width: 120,
        data: function ( row, type, val, meta ) {
          return '<a href="#/user/' + row.user.uid + '">' + row.user.username + '</a>';
        },
        className : "first-child"
      },
      {
        title: 'Email',
        data: 'user.mail'
      },
      {
        title: 'User Status',
        data: function ( row, type, val, meta ) {
          return (row.user.status) ? '<span class="text-success">&#9679;</span>  Active' : '<span class="text-danger">&#9679;</span> Inactive';
        }
      },
      {
        title: 'Team',
        data: function ( row, type, val, meta ) {
          let markup = '';
          _.forEach(row.user.field_team, (term) => {
            markup += '<span class="badge badge-pill badge-outline badge-secondary mr-1 mb-1" data-tid="' + term.tid + '">' + term.label + '</span>';
          });
          return markup;
        }
      },
      // {
      //   title: 'Certificate',
      //   className : "text-capitalize",
      //   data: function ( row, type, val, meta ) {
      //     // Create a container so we can attach ReactJS component later. @see onDataTableInitComplete()
      //     return `<div id="cert-reactjs-container-course-nid-${row.course_nid}-uid-${row.uid}"></div>`;
      //   }
      // }
      {
        title: 'Operations',
        data: function ( row, type, val, meta ) {
          let markup = '<a class="btn btn-sm btn-outline-secondary courses-info" href="node/\' . $nid . \'">User Course</a>';

          return markup;
        },
        orderable: false,
        className : "td-action"
      }
    ];

    let $datatable = $(this.refs.main).DataTable({
      rowId : 'rowUid',
      data: data,
      columns: columns,
      // columnDefs: [ {
      //   "targets": 3,
      //   "data": function ( row, type, val, meta ) {
      //     return ;
      //   }
      // } ],
      ordering: true,
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
      },
      // rowCallback : function ( row, data ) {
      // }
    });

    // Fires on every page and fires after 'initComplete'
    // @see https://datatables.net/reference/event/draw
    // $datatable.on('draw', function (e, settings) {
    //   // var info = $datatable.page.info();
    //   _.forEach(settings.json.data, function(row) {
    //     let user ={
    //       uid : row.uid
    //     }
    //     let courseData = {
    //       nid : row.course_nid,
    //       certificateLink : row.certificateLink
    //     }
    //     let elem = document.getElementById(`cert-reactjs-container-course-nid-${row.course_nid}-uid-${row.uid}`);
    //     if (!elem) {
    //       return;
    //     }
    //     ReactDOM.render(
    //       <>
    //         <Certificate user={user} course-data={courseData} />
    //       </>,
    //       elem
    //     );
    //   });
    // } );

    $('#user-progress-tbl tbody').on( 'click', 'tr td.td-action a.courses-info', (e) => this.newRowMoreInfoClick(e, $datatable));
  }

  // Only fires on the first page
  onDataTableInitComplete(settings, json){
    // Add some magic.
    $('#user-progress-tbl thead').addClass('thead-light');
  }

  /**
   * Create new row underneath the row with clicked button.
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
      // create the row with HTML
      row.child( this.format( row.data() ) ).attr('id', 'value').show();

      // Once the row is created, inject ReactJS component to each of the course's certificate <div /> with a specific ID
      let user = row.data().user;
      _.forEach( row.data().courses, function(row) {
        let elem = document.getElementById(`cert-reactjs-container-course-nid-${row.nid}-uid-${user.uid}`);
        if (!elem) {
          return;
        }
        ReactDOM.render(
          <>
            <Certificate user={user} course-data={row} />
          </>,
          elem
        );
      });

      // Add to the 'open' array
      // if ( idx === -1 ) {
      //   this.detailRows.push( tr.attr('id') );
      // }
    }
  }

  format (rowData) {
    // return 'Full name: The child row can contain any data you wish, including links, images, inner tables etc.';
    return ReactDOMServer.renderToString(
      <div className="row-extra-info container-fluid">
        <div className="row">
          <div className="col-md-12 p-3">
            <UserCourseTable data={rowData} />
          </div>
        </div>
      </div>
    );
  }
}

function UserCourseTable(props) {
  let getProgressOutput = (row, type, val, meta) => {
    let allPackageStatuses = _.map(row.package_files, function(item){
      return item.course_data.progress;
    });
    // Creates a duplicate-free version of an array. @see https://lodash.com/docs/4.17.15#uniq
    allPackageStatuses = _.uniq(allPackageStatuses);
    if(allPackageStatuses.length == 1){
      // Make text green only if status is 'completed.
      return allPackageStatuses[0] == 'completed' ? '<span class="text-success">' + allPackageStatuses[0] + '</span>' : allPackageStatuses[0];
    }

    // If one out of a bunch has an 'Incomplete' status.
    if(_.includes(allPackageStatuses, 'incomplete')){
      return 'incomplete';
    }

    // At this point, incomlete with cover these scenarios:
    // ["completed", "Not Started"]
    return 'incomplete';
  }

  return (
    <table className="table table-borderless no-bottom-border">
      <thead>
        <tr>
          <th>Title</th>
          <th>Progress</th>
          <th>Certificate</th>
        </tr>
      </thead>
      <tbody>
      {props.data['courses'].map((obj, index) => {
        return (
          <tr key={index}>
            <td className="mb-3">{obj.title}</td>
            <td className="mb-3 text-capitalize" dangerouslySetInnerHTML={{__html: getProgressOutput(obj)}}></td>
            <td className="mb-3"><div id={`cert-reactjs-container-course-nid-${obj.nid}-uid-${props.data.user.uid}`}></div></td>
          </tr>
        );
      })}
      </tbody>
    </table>
  );
}
