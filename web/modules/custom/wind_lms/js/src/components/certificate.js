'use strict';

import React, { useEffect, useState  } from "react";
import axios from "axios";
import {toast} from "react-toastify";

export default class Certificate extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      ajaxRespondData: null,
      certificateUploadedFiles : [],
      fetchingData : false
    };

    let courseData = this.props['course-data'];
    this.getFilePath = `course/${courseData.nid}/user/${this.props.user.uid}/cert/upload?getAllFiles=true`;
    this.openModalURN = `#/course/${courseData.nid}/user/${this.props.user.uid}/cert/upload`;

    this.modalOpened = false;
    this.onWindowHashchange = this.onWindowHashchange.bind(this);
  };

  componentDidMount() {
    window.addEventListener("hashchange", this.onWindowHashchange);

    // Gets all of the certificate nodes
    this.load(this.getFilePath);
  }

  async load(url) {
    this.setState({
      fetchingData : true
    });
    axios.get(url)
      .then(res => {

        if (this.state.ajaxRespondData != null && this.props.hasOwnProperty('onChange')) {
          this.props.onChange({
            ajaxRespondData : res.data,
            original : {
              ajaxRespondData : this.state.ajaxRespondData
            }
          });
        }

        this.setState({
          ajaxRespondData : res.data,
          certificateUploadedFiles : res.data.files,
          fetchingData : false
        });

      });
  }

  // TODO: Find out why render twice on the User Progress table?
  render(){
    let courseData = this.props['course-data'];

    // console.log(courseData);
    if (this.state.fetchingData) {
      return(
        <div className="spinner-border text-secondary" role="status">
          <span className="sr-only">Loading...</span>
        </div>
      );
    }

    // The button will open the Side Modal. @see ./sideModalContentCourseCertUpload.js
    let button;
    let verifiedElem;
    let fileElem;
    // If leaner has not completed the course via SCORM or Tincan
    // and certificate node exist
    if (courseData.certificateLink == 'N/A' && this.state.ajaxRespondData) {

      // 'certificate_nid' means certificate node has been created for this course with this learner
      if(this.state.ajaxRespondData.hasOwnProperty('certificate_nid')){

        if(this.state.ajaxRespondData.files.length){
          fileElem = <span className="badge badge-pill badge-secondary" ><i className={`fas fa-file mr-1`}></i> File Uploaded </span>;
        }

        // If admin has verified the certificate information
        // or admin verified learner completed ILT course
        if(this.state.ajaxRespondData.hasOwnProperty('field_completion_verified') && this.state.ajaxRespondData.field_completion_verified == '1'){
          verifiedElem = <span className="badge badge-pill badge-outline badge-success"><i className="fas fa-check-circle  mr-1"></i>Verified</span>;
        }
      }

      // If there's any propety that has been created.
      if (fileElem != null || verifiedElem != null) {
        button = <a className="btn btn-outline-secondary btn-sm" href={this.openModalURN}><i className="fas fa-pen mr-1"></i> Edit </a>;
      }

      // If leaner has NOT uploaded any file
      // If admin has NOT verify, even if ILT course
      if (fileElem == null && verifiedElem == null) {
        button = <a className="btn btn-outline-secondary btn-sm" href={this.openModalURN}><i className="fas fa-plus-circle mr-1"></i> Add </a>;
      }
    }

    return (
      <>
        {courseData.certificateLink != 'N/A' && <span className="mr-2" dangerouslySetInnerHTML={{__html: courseData.certificateLink}}></span> }
        {button}
        {fileElem}
        {verifiedElem}
      </>
    );

  }

  onWindowHashchange(e) {
    let dashboardURL = window.location.origin + window.location.pathname; // Ex: http://lms-proto.lndo.site/dashboard
    // Remove the URL from the URI to get the URN(hash sign and after)
    let oldHashURN = e.oldURL.replace(dashboardURL, '');
    // If the user closed out the modal.
    if (oldHashURN == this.openModalURN ) {
      // Reload the data to check if there's any changes.
      this.load(this.getFilePath);
      this.modalOpened = false;
    }
  }

}

// @see https://developer.mozilla.org/en-US/docs/Web/API/WindowEventHandlers/onhashchange#polyfill_for_event.newurl_and_event.oldurl
// Let this snippet run before your hashchange event binding code
if (!window.HashChangeEvent)(function(){
  var lastURL = document.URL;
  window.addEventListener("hashchange", function(event){
    Object.defineProperty(event, "oldURL", {enumerable:true,configurable:true,value:lastURL});
    Object.defineProperty(event, "newURL", {enumerable:true,configurable:true,value:document.URL});
    lastURL = document.URL;
  });
}());
