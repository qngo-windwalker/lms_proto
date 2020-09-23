import React, {Component} from 'react';
import {
  BrowserRouter,
  Switch,
  Route,
  HashRouter,
  withRouter,
  Link
} from "react-router-dom";
import { connect } from 'react-redux'
import { updateProduct } from  '../actions/productsActions';
import { updateUser } from  '../actions/userActions';
import axios from 'axios';

export default class UserMenu extends Component{
  constructor(props) {
    super(props);
    this.state = { isLogin: false };
  };

  isEnglishMode() {
    let pathname = window.location.pathname;
    // if we are on 'es' spanish mode
    if(pathname.split('/')[1] == 'es'){
      return false;
    }
    return true;
  }

  componentDidMount() {
    axios.get(`/user/login_status?_format=json`)
      .then(res => {
        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({ isLogin : res.data == 1 ? true : false });
      });
  }

  render(){
    if (this.state.isLogin) {
      return (
        <>
          <div className="user-block ml-3 dropdown">
            <a href="/user/1" className="nav-link d-flex align-items-center" data-toggle="dropdown" aria-expanded="false">
              {this.isEnglishMode() ? 'Hi' : 'Hola'},  &nbsp; <i className="fas fa-user-circle"> </i> &nbsp; <i className="fas fa-sort-down"> </i>
            </a>
            <div className="dropdown-menu dropdown-menu-right"
                 x-placement="bottom-end"
                 // style={{marginRight: spacing + 'em'}}
                 style={{position: 'absolute', transform: 'translate3d(122px, 36px, 0px', top: '0px', left: '0px', willChange: 'transform'}}>
              <div className="info list-group list-group-flush">
                <p className="list-group-item"><strong className="text-uppercase">admin</strong></p>
                <a className="list-group-item" href="user/1"><span>User profile</span></a>
                <a className="list-group-item" href="/user/1/edit"><span>Settings</span></a>
                <hr />
                <a className="list-group-item list-group-item-action" href="/user/logout"><span>Logout</span></a>
              </div>
            </div>
          </div>
        </>
      );
    }
    return (
      <>
        <a className="nav-link"  href="/user">Login</a>
      </>
    );
  }
}
