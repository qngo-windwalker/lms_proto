
'use strict';
import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";


export default class ElearningTypePage extends React.Component {
  constructor(props) {
    super(props);
    this.state = { liked: false };
  }

  render() {
    return(
      <div className="mb-3">
          <label htmlFor="elearning-type">What type of elearning formatte do you prefer?</label>
          <select className="custom-select d-block w-100" id="country" required>
            <option value="">Choose...</option>
            <option>Rise</option>
            <option>Storyline</option>
            <option>Captivate</option>
            <option>Fully Customized</option>
          </select>
      </div>
    );
  }
}

