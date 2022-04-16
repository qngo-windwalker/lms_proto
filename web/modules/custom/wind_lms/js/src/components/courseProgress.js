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
  // Note: Stupid Javascript:  0 / 0 equal NaN
  let percentage = props.numerator == 0 ? 0 : Math.floor((props.numerator / props.total) * 100);
  return (
    <div className={`d-flex flex-wrap`}>
        <div className="progress mt-2 flex-grow-1">
          <div className="progress-bar bg-success" role="progressbar" style={{width: percentage + '%'}} aria-valuenow={percentage} aria-valuemin="0" aria-valuemax="100" />
        </div>
        <div className={`mt-1 text-right d-block`} style={{minWidth: '50px'}}>
          {percentage}%
        </div>
    </div>
  );
}
