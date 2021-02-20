'use strict';

import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";
import axios from 'axios';
import { createPortal } from "react-dom";
import FileUpload from "./fileUpload";
import {toast} from "react-toastify";

export default class SideModalContentCourseCertUpload extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ajaxRespondData : null,
      courseVerifyStatus : false
    };
    this.allowFileVerification = this.doesCurrentUserHasAdminAccess();
  }

  componentDidMount() {
    let match = this.props.match;
    let query = `query {
      course(id: ${match.params.nid}){
        id
        title
      }
    }`;
    this.load(`/graphql/?query=${query}`)
  }

  async load(url) {
    axios.get(url)
      .then(res => {
        this.setState({
          ajaxRespondData : res.data
        });
      });
  }

  render() {
    if (!this.state.ajaxRespondData) {
      return (
        <div className="spinner-border text-primary" role="status">
          <span className="sr-only">Loading...</span>
        </div>
      );
    }
    let match = this.props.match;

    let onFileVerificationSwitchChange = e => {
      let value = e.currentTarget.checked;
      this.setState({
        courseVerifyStatus : value
      });
      axios.get(`course/${match.params.nid}/user/${match.params.uid}/cert/verify?field_completion_verified=${value}`)
        .then(res => {
          if(res.statusText != 'OK'){
            console.log( '%c' +'Ajax error.', 'color: #ff0000');
          }

          if(res.statusText == 'OK' && res.data.hasOwnProperty('error')){
            console.log( '%c' +'The server returned an error.', 'color: #ff0000');
            console.log( '%c' +res.data.message, 'color: #ff0000');
          }
        });
    }

    return (
      <>
        <div className="modal-header">
          <h3 className="modal-title">Verification for <small className="text-muted">{this.getCourseData('title')}</small></h3>
        </div>

        <div className="modal-body">
          <FileUpload postURL={`course/${match.params.nid}/user/${match.params.uid}/cert/upload`} />

          <h4>Verify Status</h4>
          <ul className={`list-group mb-4`}>
            <li className="list-group-item d-flex justify-content-between align-items-center">
              {this.state.courseVerifyStatus ? <span className="text-success">Completion Verified</span> : <span className="text-warning">Need Manager Verification</span>}
              <div className="custom-control custom-switch custom-switch-md text-success">
                <input id="fileVerificationSwitch" className="custom-control-input" type="checkbox" disabled={!this.allowFileVerification} onChange={onFileVerificationSwitchChange} />
                <label className="custom-control-label mt-0" htmlFor="fileVerificationSwitch"></label>
              </div>
            </li>
          </ul>
        </div>
      </>
    );
  }

  getCourseData(property) {
    if (!this.state.ajaxRespondData) {
      return '';
    }

    if (this.state.ajaxRespondData.hasOwnProperty('errors') && this.state.ajaxRespondData.errors.length) {
      console.log('%c' + this.state.ajaxRespondData.errors[0].message, 'color: #ff0000');
      return '';
    }

    // Unable to find node with a certain id
    if (!this.state.ajaxRespondData.data.course) {
      return '';
    }

    if (!this.state.ajaxRespondData.data.course.hasOwnProperty(property)) {
      return '';
    }

    return this.state.ajaxRespondData.data.course[property];
  }

  doesCurrentUserHasAdminAccess() {
    if(!this.props.currentUser){
      return false;
    }

    if(this.props.currentUser.roles.includes('administrator')
      || this.props.currentUser.roles.includes('company_admin')
    ) {
      return true
    }

    return false;
  }
}
