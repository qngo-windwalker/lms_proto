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
import { createPortal } from "react-dom";

export default class SideModalContentUser extends React.Component {
  constructor(props) {
    super(props);
    this.state = { user : null };
  }

  componentDidMount() {
    let match = this.props.match;
    this.load(`/wl-json/user/${match.params.id}`)
  }

  async load(url) {
    axios.get(url)
      .then(res => {
        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({
          user : res.data
        });
      });
  }

  render() {
    // let params = new URLSearchParams(this.props.location.search);
    // console.log(params.get("login") );
    return (
      <>
        <div className="modal-header">
          <h3 className="modal-title">{this.getUserValue('full_name')}</h3>
          {/*<button type="button" className="close" onClick={back}  data-dismiss="modal" aria-label="Close">*/}
          {/*  <span aria-hidden="true">&times;</span>*/}
          {/*</button>*/}
        </div>

        <div className="modal-body">
          <ul className="list-group list-group-flush mb-3">
            {this.getListItemTag('Username', 'username')}
            {this.getListItemTag('Email', 'mail')}
            <li className="list-group-item d-flex justify-content-between lh-sm">
              <div>
                <h6 className="my-0">Status</h6>
              </div>
              { this.getUserValue('status') ? <span className="text-success">&#9679; Active</span> : <span className="text-danger">&#9679; Inactive</span>  }
            </li>
          </ul>

          <h4>Assigned Course</h4>
          <ul className="list-group list-group-flush mb-3">
            {this.state.user && this.state.user.user_courses.map((obj, index) => {
              return (
                <li className="list-group-item d-flex justify-content-between lh-sm" key={index}>
                  <div>
                    {obj.data.title}
                  </div>
                </li>
              );
            })}
          </ul>
        </div>
      </>
    );
  }

  getListItemTag(label, dataKey) {
    return (
      <li className="list-group-item d-flex justify-content-between lh-sm">
        <div>
          <h6 className="my-0">{label}</h6>
        </div>
        <span className="text-muted">{this.getUserValue(dataKey)}</span>
      </li>
    );
  }

  getUserValue(key) {
    if (!this.state.user) {
      return '';
    }

    if (!this.state.user.hasOwnProperty(key)) {
      return '';
    }

    return this.state.user[key];
  }

}
