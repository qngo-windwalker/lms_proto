
'use strict';
import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";


export default class GraphicsPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = { liked: false };
  }

  render() {
    return(
      <div className="mb-3">
        <label htmlFor="address">Do you require custom graphics? </label>
        <div className="custom-control custom-radio">
          <input id="credit" name="paymentMethod" type="radio" className="custom-control-input" checked required />
          <label className="custom-control-label" htmlFor="credit">Yes, I need graphics to be created  
            <span className="text-muted">(If yes what type)</span>
          </label>
        </div>
        <div className="custom-control custom-radio">
          <input id="debit" name="paymentMethod" type="radio" className="custom-control-input" required />
          <label className="custom-control-label" htmlFor="debit">No, I will provide all graphics </label>
        </div>
      </div>

    );
  }
}

