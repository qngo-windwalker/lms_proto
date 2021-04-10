import React, { Component } from "react";
import {
  BrowserRouter,
  Switch,
  Route,
  HashRouter,
  withRouter,
  useHistory,
  useLocation,
  Link
} from "react-router-dom";

export default class TableRowDetail extends Component {
  render() {
    return (
      <div className="row-detail-content container-fluid">
        <div className="header">
          <div className="row flex-nowrap justify-content-between align-items-center">
            <div className="col-4 pt-1">
            </div>

            <div className="col-4 text-center"></div>

            <div className="col-4 d-flex justify-content-end align-items-center">
              <button type="button" className="close" onClick={this.props.onClose} data-dismiss="row-detail" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
          </div>
        </div>

        <div className="content">
          <div className="row">
            <div className="col-md-12 p-3">
              {this.props.children}
            </div>
          </div>
        </div>

      </div>
    );
  }
}

