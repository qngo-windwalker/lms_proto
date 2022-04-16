'use strict';

import React, {Component} from 'react';
import ReactDOM from "react-dom";
import {
  Switch,
  HashRouter,
  useHistory,
  useLocation,
  Link
} from "react-router-dom";
import DashboardPanel from '../components/dashboardPanel';
import axios from "axios";
import DashboardTeamTable from "../components/dashboardTeamTable";

export default class TeamPage extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      isLoaded: false,
      currentUser : null,
      error: null,
    };
  }

  componentDidMount() {
    this.load(`/wl-json/currentuser`);
  }

  async load(url) {
    axios.get(url)
      .then(res => {
        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({
          isLoaded : true,
          currentUser : res.data
        });
      }).catch( (error) => {

        this.setState({
          isLoaded : true,
          error
        });
      }
    );
  }

  doesCurrentUserHasManagerAccess() {
    if(!this.state.currentUser){
      return false;
    }

    if(this.state.currentUser.roles.includes('administrator')
      || this.state.currentUser.roles.includes('company_admin')
      || this.state.currentUser.roles.includes('manager')
    ) {
      return true
    }

    return false;
  }

  render() {
    return (
      <>
        <HashRouter>
          <Switch>
            <HashRouter path="/">
              {this.doesCurrentUserHasManagerAccess() && <DashboardTeamTable currentUser={this.state.currentUser} />}
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

ReactDOM.render(<TeamPage/>, document.getElementById('react-container'));
