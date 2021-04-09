import ReactDOM from "react-dom";
import React, {Component, useEffect, useState} from 'react';
import _ from 'lodash';
import axios from "axios";
import ReactDOMServer from "react-dom/server";
import Certificate from "./certificate";
import {useHistory, useParams} from "react-router-dom";
import TableRowDetail from "./tableRowDetail";
import courseProgress from "./courseProgress";
import CourseProgress from "./courseProgress";

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
        this.parseJson(res.data);
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

  parseJson(resData) {
    _.forEach(resData.userData, function(value, index) {
      // Add rowId attribute for datatable
      resData.userData[index].rowId = 'uid-' + value.user.uid;
    })
    this.initDataTable(resData.userData);
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
      {
        title: 'Overall Progress',
        data: function ( row, type, val, meta ) {
          let courseTotal = row.courses.length;
          let completed = 0;
          _.forEach(row.courses, function(course){
            if (course.isCompleted) {
              completed++;
            }
          });
          // let percentage = Math.abs(completed/courseTotal) * 100;
          let percentage = Math.floor((completed / courseTotal) * 100)
          return `<div class="progress mt-2">
                    <div class="progress-bar bg-success" role="progressbar" style="width: ${percentage}%" aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100">
                        <span class="d-none" aria-hidden="true">${percentage}%</span>
                    </div>
                </div>`;
        }
      },
      {
        title: 'Operations',
        data: function ( row, type, val, meta ) {
          let markup = '<a class="btn btn-sm btn-outline-secondary courses-info" href="node/\' . $nid . \'">View Course</a>';

          return markup;
        },
        orderable: false,
        className : "td-action"
      }
    ];

    let $datatable = $(this.refs.main).DataTable({
      rowId : 'rowId',
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
      "lengthMenu": [ 3, 25, 50, 75, 100 ],
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
    var tr = $this.closest('tr');
    var row = dt.row( tr );

    if ( row.child.isShown() ) {
      this.hideDetailRow(tr, row, $this);
    } else {
      this.showDetailRow(tr, row, $this);
    }
  }

  hideDetailRow(tr, row, $btn) {
    $btn.removeClass('active');
    tr.removeClass( 'details' );
    row.child.hide();
  }

  showDetailRow(tr, row, $btn) {
    $btn.addClass('active')
    tr.addClass( 'details' );

    // create the row with HTML
    let user = row.data().user;
    // Create a new container for host ReactJS component
    let newDiv = document.createElement("div");
    newDiv.setAttribute('id', `reactjs-container-uid-${user.uid}`);
    // Add the new container to the new <tr />
    row.child(newDiv).show();

    // Render React comp in the new container.
    ReactDOM.render(
      <TableRowDetail data={row.data()} onClose={(e) => { this.hideDetailRow(tr, row, $btn) }}>
        <UserCourseTable data={row.data()} />
      </TableRowDetail>, newDiv
    );
  }
}

// Display a table of all the courses belong to a user.
function UserCourseTable(props) {
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
            <UserCourseTableTDs course={obj} user={props.data.user} />
          </tr>
        );
      })}
      </tbody>
    </table>
  );
}

function UserCourseTableTDs(props){
  let initialCertComplVeriStatus =  false;
  // Override completion will set course to COMPLETED even if learner has NOT taken the course.
  if (props.course.certificateNode) {
    if (props.course.certificateNode.field_completion_verified == '1' || props.course.certificateNode.field_completion_verified == 'true')   {
      initialCertComplVeriStatus = true;
    }
  }

  // Define variable and it's setFunction
  const [certComplVeriStatus, setCertComplVeriStatus] = useState(initialCertComplVeriStatus);

  let getCoursePackageProgressText = (course) => {
    let allPackageStatuses = _.map(course.package_files, function(item){
      return item.course_data.progress;
    });
    // Creates a duplicate-free version of an array. @see https://lodash.com/docs/4.17.15#uniq
    allPackageStatuses = _.uniq(allPackageStatuses);
    if(allPackageStatuses.length == 1){
      // Make text green only if status is 'completed.
      return allPackageStatuses[0];
    }

    // If one out of a bunch has an 'Incomplete' status.
    if(_.includes(allPackageStatuses, 'incomplete')){
      return 'incomplete';
    }

    // At this point, incomlete with cover these scenarios:
    // ["completed", "Not Started"]
    return 'incomplete';
  };

  let onCertChange = (e) => {
    console.log(e);
    if(e.ajaxRespondData.hasOwnProperty('field_completion_verified')){
      let newValue = e.ajaxRespondData.field_completion_verified == '1' ? true : false;
      if(newValue != certComplVeriStatus){
        setCertComplVeriStatus(newValue);
      }
    } else {
      let newValue = false;
      if(newValue != certComplVeriStatus){
        setCertComplVeriStatus(newValue);
      }
    }
  };

  return(
    <>
      <td className="mb-3">{props.course.title}</td>
      <td className="mb-3"><CourseProgress overrideCompletion={certComplVeriStatus} courseProgress={getCoursePackageProgressText(props.course)} courseData={props.course} /></td>
      <td className="mb-3"><Certificate onChange={(e) => onCertChange(e)} user={props.user} course-data={props.course} /></td>
    </>
  )
}
