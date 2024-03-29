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
    this.handleUserMenuClick = this.handleUserMenuClick.bind(this);
    this.state = {
      isLoggedIn: false,
      isUserMenuActive: false,
      currentUser : null
    };
  };

  handleUserMenuClick(e) {
    e.preventDefault();
    this.setState({isUserMenuActive: this.state.isUserMenuActive ? false : true});
  }

  getGreeting() {
    let greeting = this.isEnglishMode() ? 'Hi' : 'Hola';
    return greeting + ' ' + this.getUserFirstName();
  }

  getUserFirstName() {
    return '';
    if(!this.state.currentUser){
      return '';
    }

    return this.state.currentUser.name;
  }

  isEnglishMode() {
    let pathname = window.location.pathname;
    // if we are on 'es' spanish mode
    if(pathname.split('/')[1] == 'es'){
      return false;
    }
    return true;
  }

  componentDidMount() {
    axios.get(`/wind/json/current-user`)
      .then(res => {
        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({
          isLoggedIn : res.data.uid == 0 ? false : true,
          currentUser : res.data
        });
      });
  }

  render(){
    if (this.state.isLoggedIn) {
      return (
        <>
          <div className={`user-block ml-3 dropdown ${this.state.isUserMenuActive && 'show'}`}>
            <a href="/user/1" className="nav-link d-flex align-items-center" data-toggle="dropdown" aria-expanded="false" onClick={this.handleUserMenuClick}>
              {this.getGreeting()},  &nbsp; <i className="fas fa-user-circle"> </i> &nbsp; <i className="fas fa-sort-down"> </i>
            </a>
            <div className={`dropdown-menu dropdown-menu-right ${this.state.isUserMenuActive && 'show'}`}
                 x-placement="bottom-end"
                 // style={{marginRight: spacing + 'em'}}
                 style={{position: 'absolute', transform: 'translate3d(0px, 36px, 0px', top: '0px', left: '0px', willChange: 'transform'}}>
              <a className="dropdown-item" href={`/user/${this.state.currentUser.uid}`}><span>My Account</span></a>
              <a className="dropdown-item" href="/user/logout"><span>Logout</span></a>
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
