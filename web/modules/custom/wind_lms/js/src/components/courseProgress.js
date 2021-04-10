import React, { Component } from "react";

export class CourseProgress extends Component {

  constructor(props) {
    super(props);
  };

  render() {
    // If learner did NOT complete the course, but manager has marked as completed
    let completeByOverrideMsg;
    if (this.props.courseProgress != 'completed' && this.props.overrideCompletion) {
      completeByOverrideMsg = <span className="text-success">Completed by verification</span>;
    }

    // Display to course progress from status of course package if manager has NOT verified
    let senario3;
    if (this.props.courseProgress != 'completed' && !this.props.overrideCompletion) {
      senario3 = <span>{this.props.courseProgress}</span>;
    }

    return (
      <div className="text-capitalize">
        {this.props.courseProgress == 'completed' && <span className="text-success">{this.props.courseProgress}</span> }
        {completeByOverrideMsg }
        {senario3 }
      </div>
    );
  }
}

export function ProgressBar(props){
  let percentage = Math.floor((props.numerator / props.total) * 100);
  return (
    <div className="progress mt-2">
      <div className="progress-bar bg-success" role="progressbar" style={{width: percentage + '%'}} aria-valuenow={percentage} aria-valuemin="0" aria-valuemax="100">
        <span className="d-none" aria-hidden="true">{percentage}%</span>
      </div>
    </div>
  );
}
