'use strict';

import React, { useEffect, useState  } from "react";

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
    // The Add button will open the Side Modal. @see ./sideModalContentCourseCertUpload.js
    return (
      <>
        { courseData.certificateLink == 'N/A'
          ? <span>N/A</span>
          // <a className="btn btn-outline-secondary btn-sm" href={`#/course/${courseData.nid}/user/${this.props.user.uid}/cert/upload`}>
          //   <i className="fas fa-plus-circle mr-1"></i> Add
          // </a>
          : <span dangerouslySetInnerHTML={{__html: courseData.certificateLink}}></span>
        }
      </>
    );
  }

}
