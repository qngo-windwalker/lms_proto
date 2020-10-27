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

import UserCourseTable from '../components/userCourseTable';

ReactDOM.render(<UserCourseTable />, document.getElementById('react-container'));
