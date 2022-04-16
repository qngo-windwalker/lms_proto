import React, {Component} from 'react';
import ReactDOMServer from "react-dom/server";
import {Spinner, StatusCircle} from "./GUI";
import Utility from "../modules/utility";
import {ProgressBar} from "./courseProgress";

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

export default class DashboardTeamTable extends Component{
  constructor(props) {
    super(props);
    this.state = {
      items: [],
      isError: false,
      isLoaded: false,
    };

    this.newRowMoreInfoClick = this.newRowMoreInfoClick.bind(this)
    this.getMemberColumnContent = this.getMemberColumnContent.bind(this)
    this.getProgressColumnContent = this.getProgressColumnContent.bind(this)
  };

  componentDidMount() {
    this.getRecords(`/wl-json/team`);
  }

  async getRecords(url) {
    try {
      const response = await fetch(url);
      let result = await response.json();
      this.setState({
        isLoaded: true,
        items: result
      });
      this.initDataTable(result);
    } catch (error) {
      console.log(error);
      this.setState({
        isError: true,
        isLoaded: true
      });
    }
  }

  render(){
    return (
      <div className="section">
        <h3 className="mb-3">{Utility.isEnglishMode() ? 'Team' : 'Equipo'}</h3>
        <table id="team-tbl" ref="main" className="table table-curriculum responsive-enabled mb-5" data-striping="1" />
        <div className="clear-both">
          <a className="btn btn-primary " href="/admin/structure/taxonomy/manage/user_team/add?destination=/dashboard"><i className="fas fa-plus-circle mr-1"></i> Add Team</a>
          <a className="btn btn-primary ml-3" href="/admin/structure/taxonomy/manage/user_team/overview?destination=/dashboard"><i className="fas fa-plus-circle mr-1"></i> View Team List</a>
        </div>
      </div>
    );
  }

  getMemberColumnContent(row, type, val, meta){
    return Utility.getAllActiveUsersInTeam(row.tid, this.props.data.userData).length;
  }

  getProgressColumnContent(row, type, val, meta){
    let teamCompleted = 0;
    let teamUsers = Utility.getAllActiveUsersInTeam(row.tid, this.props.data.userData);
    if (row.tid == 89) {
      console.log(teamUsers);
    }
    // If this team has member
    if (teamUsers.length) {
      // Loop thru members
      for (const userData of teamUsers){
        let userOverProgress = Utility.getUserOverallCourseProgress(userData.courses);
        // If this user completed all of their courses.
        if (userOverProgress.completePercentage == 1) {
          teamCompleted ++;
        }
      }
    }
    let progressBar = ReactDOMServer.renderToString(<ProgressBar numerator={teamCompleted} total={teamUsers.length} />);
    return `<div id="react-container-uid--">${progressBar}</div>`;
  }

  initDataTable(data) {
    let columns = [
      {
        title: 'Name',
        // width: 120,
        className : "first-child align-middle",
        data: function ( row, type, val, meta ) {
          return `<a href="/team/${row.tid}">${row.name}</a>` ;
        },
      },
      {
        title: 'Member ',
        className : "member-col",
        data: this.getMemberColumnContent
      },
      {
        title: 'Progress ',
        className : "progress-col",
        data: this.getProgressColumnContent
      },
      {
        title: 'Operations',
        orderable: false,
        className : "td-action",
        data: (row, type, val, meta) => {
          let markup = ReactDOMServer.renderToString(
            <div className={`btn-group`}>
              <a className="btn btn-sm btn-outline-secondary" href={`/taxonomy/term/${row.tid}/edit`}>Edit</a>
            </div>
          );

          return markup;
        },
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
        {
          title: 'Team',
          exportOptions: {
            columns: [0,1,2,3,] // To exclude Operation column
          },
          extend: 'excelHtml5',
          //   autoFilter: true,
          //   sheetName: 'User Progress data'
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

        // {
        //   extend: 'excelHtml5',
        //   sheetName: 'User Progress data'
        // },
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
    let learnerConent;
    if (rowData.field_learner_access == '0' && rowData.learners_data.length) {
      learnerConent = (
        <ul className="list-unstyled">
          {rowData['learners_data'].map((obj, index) => {
            return (
              <li className="mb-3" key={index} data-uid={obj.uid}>
                <a href={`/user/${obj.uid}`}>{obj.full_name}</a>
              </li>
            );
          })}
        </ul>
      );
    }

    let teamContent;
    if (rowData.field_learner_access == '0' && rowData.field_user_team.length) {
      teamContent = (
        <ul className="list-unstyled">
          {rowData['field_user_team'].map((obj, index) => {
            return (
              <li className="mb-3" key={index} data-tid={obj.tid}>
                {obj.label}
              </li>
            );
          })}
        </ul>
      );
    }

    let availToAll;
    if(rowData.field_learner_access == '1'){
      availToAll = true;
    }

    // return 'Full name: The child row can contain any data you wish, including links, images, inner tables etc.';
    return ReactDOMServer.renderToString(
      <div className="row-extra-info row">
        <div className="col-md-6">
          <h5>Package File</h5>
          <ul className="list-unstyled">
            {rowData['courses_data'].map((obj, index) => {
              return (<li className="mb-3" key={index} data-uid={obj.uid}>{obj.title}</li>);
            })}
          </ul>
        </div>
        <div className="col-md-6">
          <h5> Availability </h5>
          {availToAll
            ? 'Available to All Learners'
            :
            <div>
              <div>{learnerConent}</div>
              <div>{teamContent}</div>
            </div>
          }
        </div>
      </div>
    );
  }

  onDataTableInitComplete(settings, json){
    // Add some magic.
    $('#courses-tbl thead').addClass('thead-light');
  }
}
