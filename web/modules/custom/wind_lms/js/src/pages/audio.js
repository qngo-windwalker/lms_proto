
'use strict';
import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";


export default class AudioPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = { liked: false };
  }

  render() {
    return(
      <>
        <div className="mb-3">
          <label htmlFor="address">Do you require audio voice over?  </label>
          <div className="custom-control custom-radio">
            <input id="credit" name="paymentMethod" type="radio" className="custom-control-input" checked required />
            <label className="custom-control-label" htmlFor="credit">Yes</label>
          </div>
          <div className="custom-control custom-radio">
            <input id="debit" name="paymentMethod" type="radio" className="custom-control-input" required />
            <label className="custom-control-label" htmlFor="debit">No </label>
          </div>
        </div>

        <div className="mb-3">
          <label htmlFor="address">Do you require custom animation? (use example) </label>
          <div className="custom-control custom-radio">
            <input id="credit" name="paymentMethod" type="radio" className="custom-control-input" checked required />
            <label className="custom-control-label" htmlFor="credit">Yes If “Yes” how many minutes of animation? </label>
          </div>
          <div className="custom-control custom-radio">
            <input id="debit" name="paymentMethod" type="radio" className="custom-control-input" required />
            <label className="custom-control-label" htmlFor="debit">No </label>
          </div>
        </div>

         <div className="mb-3">
          <label htmlFor="address">Are you interested in Video services? (use example) </label>
          <div className="custom-control custom-radio">
            <input id="credit" name="paymentMethod" type="radio" className="custom-control-input" checked required />
            <label className="custom-control-label" htmlFor="credit">Yes  If “Yes” how many minutes of video? </label>
          </div>
          <div className="custom-control custom-radio">
            <input id="debit" name="paymentMethod" type="radio" className="custom-control-input" required />
            <label className="custom-control-label" htmlFor="debit">No </label>
          </div>
      </div>  
      </>
    );
  }
}

