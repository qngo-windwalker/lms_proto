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

export default class SideModalContentCourseCertUpload extends React.Component {
  constructor(props) {
    super(props);
    this.state = { ajaxRespondData : null };
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
      return (<p>Loading...</p>);
    }

    let match = this.props.match;

    return (
      <>
        <div className="modal-header">
          <h3 className="modal-title">Upload Certificate for <small className="text-muted">{this.getCourseData('title')}</small></h3>
        </div>

        <div className="modal-body">
          <FileUpload postURL={`course/${match.params.nid}/user/${match.params.uid}/cert/upload`} />
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

}
