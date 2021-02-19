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
        this.setState({
          ajaxRespondData : res.data,
          certificateUploadedFiles : res.data.files,
          fetchingData : false
        });
      });
  }

  render(){
    let courseData = this.props['course-data'];

    if(courseData.certificateLink != 'N/A'){
      return (
        <>
          <span className="mr-2" dangerouslySetInnerHTML={{__html: courseData.certificateLink}}></span>
        </>
      )
    }

    if (this.state.fetchingData) {
      return(
        <div className="spinner-border text-secondary" role="status">
          <span className="sr-only">Loading...</span>
        </div>
      );
    }

    // The Add button will open the Side Modal. @see ./sideModalContentCourseCertUpload.js
    return (
      <>
        {this.state.certificateUploadedFiles.length
          ? <a className="btn btn-outline-secondary btn-sm" href={this.openModalURN}><i className="fas fa-file mr-1"></i>View</a>
          : <a className="btn btn-outline-secondary btn-sm" href={this.openModalURN}><i className="fas fa-plus-circle mr-1"></i> Add </a>
        }
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
