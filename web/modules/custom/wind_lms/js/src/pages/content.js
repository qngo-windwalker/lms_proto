
'use strict';
import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";


export default class ContentPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = { liked: false };
  }

  render() {
    return(
      <div className="mb-3">
          <label htmlFor="address2">Do you have existing content that you need converted?</label>
          <div className="custom-control custom-radio">
            <input id="credit" name="paymentMethod" type="radio" className="custom-control-input" required />
            <label className="custom-control-label" htmlFor="credit">Yes 
              <span className="text-muted"> ( if yes Select the type of content best aligns with what you have –select all that apply )</span>
            </label>
          </div>
          <div className="custom-control custom-radio">
            <input id="debit" name="paymentMethod" type="radio" className="custom-control-input" required />
            <label className="custom-control-label" htmlFor="debit">No, I need assistance sourcing content 
              <span className="text-muted"> ( NOTE: Windwalker are not SMEs but can assist in identifying them for you. ) </span>
            </label>
          </div>
      </div>  
    );
  }
}

