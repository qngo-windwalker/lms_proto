
'use strict';
import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";


export default class TrainingLengthPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = { liked: false };
  }

  render() {
    return(
      <div className="mb-3">
          <label htmlFor="trainging-length">How long is your training?</label>
          <select className="custom-select d-block w-100" id="country" required>
            <option value="">Choose...</option>
            <option>3 - 7 minutes</option>
            <option>10 - 30 minutes</option>
            <option>30 - 60 minutes</option>
            <option>60+ minutes</option>
          </select>
      </div>  
    );
  }
}

