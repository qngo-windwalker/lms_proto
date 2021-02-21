'use strict';

import React, { useEffect, useState  } from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";
import axios from 'axios';
import FileUpload from "./fileUpload";

export default class SideModalContentCourseCertUpload extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ajaxRespondData : null,
      courseVerifyStatus : false,
      isFileVerified : false
    };
    this.allowFileVerification = this.doesCurrentUserHasAdminAccess();
    this.onFileVerificationSwitchChange = this.onFileVerificationSwitchChange.bind(this);
  }

  componentDidMount() {
    this.load(`/jsonapi/node/certificate?filter[field_activity.nid]=${this.props.match.params.nid}&filter[field_learner.uid]=${this.props.match.params.uid}`)
  }

  async load(url) {
    axios.get(url)
      .then(res => {
        console.log(res.data);
        let newState = {
          ajaxRespondData : res.data,
        };

        if (res.data.data.length) {
          newState.isFileVerified = res.data.data[0].attributes.field_completion_verified
        }
        this.setState(newState);
      });
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
    if (!this.state.ajaxRespondData) {
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
          <h3 className="modal-title">Verification for <small className="text-muted"><NodeTitle nid={match.params.nid}/></small></h3>
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
    ) {
      return true
    }

    return false;
  }
}

function NodeTitle(props) {
  const [error, setError] = useState(null);
  const [isLoaded, setIsLoaded] = useState(false);
  const [respondData, setRespondData] = useState({});

  let query = `query {
      course(id: ${props.nid}){
        id
        title
      }
    }`;

  // Note: the empty deps array [] means
  // this useEffect will run once
  // similar to componentDidMount()
  useEffect(() => {
    fetch(`/graphql/?query=${query}`)
      .then(res => res.json())
      .then(
        (result) => {
          setIsLoaded(true);
          setRespondData(result.data);
        },
        // Note: it's important to handle errors here
        // instead of a catch() block so that we don't swallow
        // exceptions from actual bugs in components.
        (error) => {
          setIsLoaded(true);
          setError(error);
        }
      )
  }, [])

  if (error) {
    return <div>Error: {error.message}</div>;
  } else if (!isLoaded) {
    return <div>Loading...</div>;
  } else {
    return (
      <>
        {respondData.hasOwnProperty('course') && respondData.course.title}
      </>
    );
  }
}
