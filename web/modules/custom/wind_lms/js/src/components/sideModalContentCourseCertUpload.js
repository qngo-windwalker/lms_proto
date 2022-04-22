'use strict';

import React, { useEffect, useState  } from "react";
import axios from 'axios';
import FileUpload from "./fileUpload";

export default class SideModalContentCourseCertUpload extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ajaxRespondData : null,
      courseVerifyStatus : false,
      isFileVerified : false,
      error: null,
      isLoaded: false,
    };
    this.allowFileVerification = this.doesCurrentUserHasAdminAccess();
    this.onFileVerificationSwitchChange = this.onFileVerificationSwitchChange.bind(this);
  }

  componentDidMount() {
    this.load(`/wind-lms/api/course/${this.props.match.params.nid}/user/${this.props.match.params.uid}/cert`)
  }

  async load(url) {
    try {
      const response = await fetch(url);
      let result = await response.json();
      this.setState({
        ajaxRespondData : result,
        isFileVerified : this.getFileVerification(result)
      });
      console.log(result);
    } catch (error) {
      console.log(error);
      this.setState({
        error
      });
    }
    this.setState({
      isLoaded: true
    });
  }

  getFileVerification(ajaxRespondData) {
    if(!ajaxRespondData.hasOwnProperty('certificate') || !ajaxRespondData.certificate.hasOwnProperty('field_completion_verified')){
      return false;
    }

    return ajaxRespondData.certificate.field_completion_verified == '1' ? true : false;
  }

  onFileVerificationSwitchChange(e){
    let match = this.props.match;
    // @see https://reactjs.org/docs/forms.html
    const target = e.target;
    const value = target.type === 'checkbox' ? target.checked : target.value;
    const name = target.name;

    this.setState({
      [name]: value
    });

    // Save the data on the server
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

  render() {
    const { error, isLoaded, ajaxRespondData } = this.state;
    if (error) {
      return <div>Error: {error.message}</div>;
    } else if (!isLoaded) {
      return (
        <div className="spinner-border text-primary" role="status">
          <span className="sr-only">Loading...</span>
        </div>
      );
    }

    let match = this.props.match;

    return (
      <>
        <div className="modal-header">
          <h3 className="modal-title">Verification for <small className="text-muted">{ajaxRespondData.course.title}</small></h3>
        </div>

        <div className="modal-body">
          <FileUpload postURL={`course/${match.params.nid}/user/${match.params.uid}/cert/upload`} />

          <h4>Verify Status</h4>
          <ul className={`list-group mb-4`}>
            <li className="list-group-item d-flex justify-content-between align-items-center">
              {this.state.isFileVerified ? <span className="text-success">Completion Verified</span> : <span className="text-warning">Need Verification</span>}
              <div className="custom-control custom-switch custom-switch-md text-success">
                <input id="isFileVerified" name="isFileVerified" className="custom-control-input" type="checkbox" disabled={!this.allowFileVerification} checked={this.state.isFileVerified} onChange={this.onFileVerificationSwitchChange} />
                <label className="custom-control-label mt-0" htmlFor="isFileVerified"></label>
              </div>
            </li>
          </ul>
        </div>
      </>
    );
  }

  doesCurrentUserHasAdminAccess() {
    if(!this.props.currentUser){
      return false;
    }

    if(this.props.currentUser.roles.includes('administrator')
      || this.props.currentUser.roles.includes('company_admin')
      || this.props.currentUser.roles.includes('manager')
    ) {
      return true
    }

    return false;
  }
}
