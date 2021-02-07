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

export default class SideModalContentCourseCertUpload extends React.Component {
  constructor(props) {
    super(props);
    this.state = { ajaxRespondData : null };
  }

  componentDidMount() {
    let match = this.props.match;
    let query = `query {
      course(id: ${match.params.id}){
        id
        title
      }
    }`;
    this.load(`/graphql/?query=${query}`)
  }

  async load(url) {
    axios.get(url)
      .then(res => {
        console.log(res.data);
        this.setState({
          ajaxRespondData : res.data
        });
      });
  }

  render() {
    return (
      <>
        <div className="modal-header">
          <h3 className="modal-title">Upload Certificate for <small className="text-muted">{this.getCourseData('title')}</small></h3>
        </div>

        <div className="modal-body">
          <h4>Upload</h4>

        </div>
      </>
    );
  }

  getCourseData(property) {
    if (!this.state.ajaxRespondData) {
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
