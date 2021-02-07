'use strict';

import React, { useEffect, useState  } from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  useLocation, useHistory, useParams,
  HashRouter,
  Link
} from "react-router-dom";
import axios from 'axios';

export default class Certificate extends React.Component {

  constructor(props) {
    super(props);
    this.state = { tableRow: [] };
  };

  // componentDidUpdate(){
  //
  // }

  render(){
    let courseData = this.props['course-data'];
    return (
      <>
        { courseData.certificateLink == 'N/A'
          ? <a className="btn btn-outline-secondary btn-sm" href={`#/course/${courseData.nid}/cert/upload`}>
            <i className="fas fa-file-upload mr-1"></i> Upload
        </a>
          : <span dangerouslySetInnerHTML={{__html: courseData.certificateLink}}></span>
        }
      </>
    );
  }

}
