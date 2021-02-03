'use strict';

import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";
import axios from 'axios';
import UserCourseTable from './userCourseTable';
import AllUserProgressTable from './allUserProgressTable';
import CurriculumTable from "./curriculumTable";

export default class DashboardPanel extends React.Component {
  constructor(props) {
    super(props);
    this.state = { currentUser : null };
  }

  componentDidMount() {
    axios.get(`/wind/json/current-user`)
      .then(res => {
        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({
          currentUser : res.data
        });
      });
  }

  render() {
    return(
      <>
        <UserCourseTable />
        {this.getAllUsersProgressTable()}
        {this.getCurriculumTable()}
      </>
    );
  }

  getAllUsersProgressTable() {
    if(!this.state.currentUser){
      return null;
    }

    if(this.state.currentUser.roles.includes('administrator') || this.state.currentUser.roles.includes('manager')) {
      return <AllUserProgressTable />;
    }

    return null;
  }

  getCurriculumTable() {
    if(!this.state.currentUser){
      return null;
    }

    if(this.state.currentUser.roles.includes('administrator') || this.state.currentUser.roles.includes('manager')) {
      return <CurriculumTable />;
    }

    return null;
  }
}

