import React, {Component} from 'react';
import ReactDOMServer from "react-dom/server";
import axios from "axios";
import Certificate from "./certificate";
import _ from 'lodash';
import {CourseProgress} from "./courseProgress";

export default class CurrentUserCourseTable extends Component{
	constructor(props) {
    super(props);
    this.state = { tableRow: [], user : null, loadCompleted : false };
    this.courseClickHandler = this.courseClickHandler.bind(this);
  };

  componentDidMount() {
    let url = new URL(window.location.href);
    let testParam = url.searchParams.get('test') ? 'test=true' : '';
    let langParam = this.isEnglishMode() ? 'en' : 'es';
    // @see WindLMSJsonController.php
    axios.get(`/wind-lms/json/dashboard?lang=${langParam}&${testParam}`)
      .then(res => {
        this.parseJson(res.data);
        this.addPopupClickEvent();
      });
  }

  componentDidUpdate(){
    this.addPopupClickEvent();
  }

  addPopupClickEvent(){
    const buttons = document.querySelectorAll('a.wind-scorm-popup-link');
    for (const button of buttons) {
      // Javascript doesn't have hasEventListener function.
      // Add and attribute to make sure we only attach click handler 1 time.
      if(!button.hasAttribute('data-listener-attached')){
        // Add click event handler to modules from search result.
        button.addEventListener('click', (e) => this.courseClickHandler(e));
        button.setAttribute('data-listener-attached', 'click');
      }
    }
  }

  courseClickHandler(e){
    let elem = e.currentTarget;
    e.preventDefault();
    if(elem.hasAttribute('data-course-href')){
      let href = elem.getAttribute('data-course-href');
      this.popup(href);
    }
  }

  popup(href){
    var day = new Date();
    var id = day.getTime();
    var screenHeight = screen.height >= 768 ? 985 : screen.height;
    var params = ['toolbar=no', 'scrollbars=no', 'location=no', 'statusbar=no', 'menubar=no', 'directories=no', 'titlebar=no', 'toolbar=no', 'resizable=1', 'height=' + screenHeight, 'width=1254'
      //            'fullscreen=yes' // only works in IE, but here for completeness
    ].join(',');

    let popupWin = window.open(href, "window" + id, params);
    // Reload the page when user closes the course to view newly course progess
    let timer = setInterval(function() {
      if(popupWin.closed) {
        clearInterval(timer);
        location.reload();
      }
    }, 700);
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
        <h3 className="mb-3">{this.isEnglishMode() ? 'My Training' : 'Mi Entrenamiento'}</h3>
        { (!this.state.tableRow.length && this.state.loadCompleted) ? <p className="text-align-center my-5">You do not have any course assigned to you.</p> : this.getTable()}
			</div>
		);
	}

  getTable() {
    return (
      <table className="table responsive-enabled mb-5" data-striping="1">
        <thead className="thead-light">
        <tr>
          <th>{this.isEnglishMode() ? 'Name' : 'Nombre'}</th>
          <th>{this.isEnglishMode() ? 'Status' : 'Estado'}</th>
          <th>{this.isEnglishMode() ? 'Certificate' : 'Certificado'}</th>
        </tr>
        </thead>
        <tbody>
        {this.state.tableRow.map((obj, index) => {
          return obj.Comp;
          // return this.rendertBodyRow(obj.name, obj.rateFormatted, obj.hours, obj.priceCalculatedFormatted, index);
        })}
        </tbody>
      </table>
    )
  }

  rendertBodyRow(dataObj, key, user){
    return(
      <tr key={key} data-nid={dataObj.data['nid']}>
        <td>{this.getColumnNameContent(dataObj)}</td>
        <td className="text-capitalize"><CourseProgress overrideCompletion={this.getCourseProgressOverrideCompletion(dataObj)} courseProgress={this.getCourseProgress(dataObj)} /></td>
        <td><Certificate user={user} course-data={dataObj.data} onChange={(e) => this.onCertChange(e)} /></td>
      </tr>
    );
  }

  getColumnNameContent(dataObj) {
    if(dataObj.data.type == 'curriculum'){
      return (<CurriculumNameColumn title={dataObj.data.title} courses={dataObj.data.courses} />)
    }

    if(dataObj.data.type == 'course'){
      return (<CourseNameColumn title={dataObj.data.title} nid={dataObj.data.nid} packages={dataObj.data.package_files} />)
    }
  }

  getCourseProgress(dataObj) {
    if(dataObj.data.type == 'curriculum'){
      return '';
    }
    // If course node only has 1 zip file
    if(dataObj.data['package_files'].length == 1) {
      return dataObj.data['package_files'][0]['course_data']['progress'];
    }

    let statusCollection = [];
    _.forEach(dataObj.data['package_files'], (obj) => {
      // Only add if it doesnt exist
      if (!statusCollection.includes(obj['course_data']['progress'])) {
        statusCollection.push(obj['course_data']['progress'])
      }
    });

    if (statusCollection.length == 1) {
      return statusCollection[0];
    }

    // If there's more than one type of status
    if (statusCollection.length > 1) {
      return 'Incomplete';
    }
  }

  getCourseProgressOverrideCompletion(dataObj) {
    if(dataObj.data.hasOwnProperty('certificateNode') && dataObj.data.certificateNode.field_completion_verified == '1'){
      return true;
    }
    return false;
  }

  onCertChange(e) {
    if(e.ajaxRespondData.hasOwnProperty('field_completion_verified')){
      let newValue = e.ajaxRespondData.field_completion_verified == '1' ? true : false;
      this.processChangedValue(newValue, e);
    } else {
      this.processChangedValue(false, e);
    }
  }

  processChangedValue(newValue, e){

  }

  getColumnStatusCurriculumnContent(dataObj) {
    // If this course has more than 1 zip files, render the zip course as a list item.
    if(dataObj.data['package_files'].length > 1){
      return ReactDOMServer.renderToString(
        <>
          <h4> &nbsp; </h4>
          <ul className="list-unstyled">
            {dataObj.data['package_files'].map((obj, index) => {
              return (<li className="mb-3" key={index} dangerouslySetInnerHTML={{__html: obj['course_data']['progress']}}></li>);
            })}
          </ul>
        </>
      );
    }

    // If course node only has 1 zip file
    return dataObj.data['package_files'][0]['course_data']['progress'];
  }

  parseJson(data) {
    let user = {uid: data.uid, username: data.username, name: data.name};
    let collection = [];
    for(var i = 0; i < data.user_courses.length; i++){
      let Comp = this.rendertBodyRow(data.user_courses[i], i, user);
      // If user has not made selected an option to this item, then skip it.
      collection.push({Comp : Comp});
    }
    // const posts = res.data.data.children.map(obj => obj.data);
    this.setState({
      tableRow : collection,
      user : user,
      loadCompleted: true
    });
  }
}

class CurriculumNameColumn extends React.Component {
  render() {
    return (
      <>
        <h5>{this.props.title}</h5>
        <ul className="list-unstyled ml-3">
          {this.props.courses.map((obj, index) => {
            return (<li className="mb-3"  key={index}><CourseNameColumn title={obj.data.title} packages={obj.data.package_files} /></li>);
          })}
        </ul>
      </>
    );
  }
}

class CourseNameColumn extends React.Component {
  render() {
    // ILT course does NOT have any package
    if (this.props.packages.length == 0 && this.props.hasOwnProperty('nid')) {
      return <ActivityLink className="wind-scorm-popup-link d-flex" title={this.props.title} data-course-href={`course/${this.props.nid}`} href={`course/${this.props.nid}`} />
    }

    // If course has 1 zip file, make the title as a link
    // In case when a course node is created but zip file is no available. Show the title so it's easier to troubleshoot.
    if (this.props.packages.length == 1) {
      let zipPackage = this.props.packages[0];

      if(zipPackage.type == 'tincan'){
        return <ActivityLink className="wind-scorm-popup-link d-flex" title={this.props.title} data-course-href={zipPackage.activity_link['data-course-href']} href={zipPackage.activity_link.url} />
      }

      if(zipPackage.type == 'scorm'){
        return <ActivityLink className="wind-scorm-popup-link" title={this.props.title} data-course-href={zipPackage.activity_link.url} href={zipPackage.activity_link.url} />
      }
    }

    // If this course has more than 1 zip files, render the zip course as a list item.
    return (
      <>
        <h5>{this.props.title}</h5>
        <ul className="list-unstyled ml-3">
          {this.props.packages.map((obj, index) => {
            let zipPackage = obj;
            return (
              <li className="mt-4" key={index}>
                <ActivityLink className="wind-scorm-popup-link" progress={zipPackage.course_data.progress}  title={this.props.title} data-course-href={zipPackage.activity_link.url} href={zipPackage.activity_link.url} />
              </li>
            );
          })}
        </ul>
      </>
    );
  }
}

class ActivityLink extends React.Component {
  render() {

    let progressBadge;

    // Only applies to multiple zip files course.
    if(this.props.hasOwnProperty('progress')){
      if (this.props.progress == 'completed') {
        progressBadge = <span className={`ml-2 text-success`} title={this.props.progress}><i className="fas fa-check mr-1"></i></span>;
      } else {
        // progressBadge = <span className={`badge badge-pill badge-outline badge-secondary ml-2 text-capitalize`}><i className="fas fa-exclamation-circle"></i>{this.props.progress}</span>;
      }

    }
    return (
      <>
        <a className="wind-scorm-popup-link" data-course-href={this.props['data-course-href']} href={this.props.href}>
          <i className="fas fa-external-link-alt align-self-center pr-1"></i>
          {this.props.title}
        </a>
        {progressBadge}
      </>
    );
  }
}
