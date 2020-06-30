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

export default class App extends Component{
  constructor(props) {
    super(props);
    this.state = { isLogin: false };
    this.onUpdateUser = this.onUpdateUser.bind(this);
  };

  componentDidMount() {
    axios.get(`/user/login_status?_format=json`)
      .then(res => {
        console.log(res.data);

        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({ isLogin : res.data == 1 ? true : false });
      });
  }

  onUpdateUser(event){
    // This method defined in mapActionsToProps object
    this.props.onUpdateUser('User 3');
  }

  render(){
    if (this.state.isLogin) {
      return (
        <>
          <a href="/user/logout">Logout</a>
        </>
      );
    }
    return (
      <>
        <a href="/user">Login</a>
      </>
    );
  }
}
