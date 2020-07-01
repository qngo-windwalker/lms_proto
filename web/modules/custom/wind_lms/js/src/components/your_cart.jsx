
'use strict';
import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";

import { connect } from 'react-redux'
import { updateProduct } from  '../actions/productsActions';
import { updateUser } from  '../actions/userActions';

const mapStateToProps = state => ({
  products: state.products,
  user: state.user
});

// To allow automatically dispatch Redux Action to the Redux Store
const mapActionsToProps = {
  onUpdateProduct : updateProduct,
  onUpdateUser : updateUser

};

class YourCart extends React.Component {
  constructor(props) {
    super(props);
    this.state = { liked: false };
  }

  render() {
    console.log(this.props);
    return (
      <HashRouter>

        <div>
          <h4 className="d-flex justify-content-between align-items-center mb-3">
            <span className="text-muted">Your cart</span>
            <span className="badge badge-secondary badge-pill">3</span>
          </h4>
          <ul className="list-group mb-3">
            <li className="list-group-item d-flex justify-content-between lh-condensed">
              <div>
                <h6 className="my-0"><Link to="/interested">Interested</Link></h6>
                <small className="text-muted">{this.props.products[0].name}</small>
              </div>
              <span className="text-muted">$12</span>
            </li>

            <li className="list-group-item d-flex justify-content-between lh-condensed">
              <div>
                <h6 className="my-0"><Link to="/elearning-type">eLearning Type</Link></h6>
                <small className="text-muted">Brief description</small>
              </div>
              <span className="text-muted">$12</span>
            </li>
            <li className="list-group-item d-flex justify-content-between lh-condensed">
              <div>
                <h6 className="my-0"><Link to="/training-length">Training Length</Link></h6>
                <small className="text-muted">Brief description</small>
              </div>
              <span className="text-muted">$8</span>
            </li>
            <li className="list-group-item d-flex justify-content-between lh-condensed">
              <div>
                <h6 className="my-0"><Link to="/content">Content</Link></h6>
                <small className="text-muted">Brief description</small>
              </div>
              <span className="text-muted">$5</span>
            </li>
            <li className="list-group-item d-flex justify-content-between">
              <span>Total (USD)</span>
              <strong>$20</strong>
            </li>
          </ul>
        </div>
      </HashRouter>
    );
  }
}


// wrap YourCart in connect and pass in mapStateToProps
export default connect(mapStateToProps, mapActionsToProps)(YourCart);