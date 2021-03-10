'use strict';

import React, {Component} from 'react';
import ReactDOM from "react-dom";
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
import DashboardPanel from '../components/dashboardPanel';

export default class CourseNode extends React.Component {

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
              <div className="spinner-border text-primary" role="status">
                <span className="sr-only">Loading...</span>
              </div>
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
  console.log( '%clocation.pathname : ' + location.pathname, 'color: green');
  return (<> </>);
}

ReactDOM.render(<CourseNode/>, document.getElementById('react-container'));
