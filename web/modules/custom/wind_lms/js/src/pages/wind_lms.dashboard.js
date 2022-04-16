'use strict';

import React, {Component} from 'react';
import ReactDOM from "react-dom";
import {
  Switch,
  HashRouter,
  useLocation,
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
            <HashRouter path="/">
              <DashboardPanel/>
            </HashRouter>
          </Switch>

          <LocationConsole />
        </HashRouter>
      </>
    );
  }
}

function LocationConsole() {
  const location = useLocation();
  // console.log( '%clocation.pathname : ' + location.pathname, 'color: green');
  return (<> </>);
}

ReactDOM.render(<Dashboard/>, document.getElementById('react-container'));
