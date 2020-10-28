'use strict';

import React, {Component} from 'react';
import ReactDOM from "react-dom";
import {
  BrowserRouter,
  Switch,
  Route,
  HashRouter,
  withRouter,
  Link
} from "react-router-dom";
import DashboardPanel from '../components/dashboardPanel';

export default class Dashboard extends React.Component {

  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return (
      <>
        <HashRouter>
          <Switch>
            <HashRouter exact path="/">
              <DashboardPanel/>
            </HashRouter>

          </Switch>
        </HashRouter>
      </>
    );
  }
}

ReactDOM.render(<Dashboard/>, document.getElementById('react-container'));
