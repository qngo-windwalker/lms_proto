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

export default class LanguageSwitcher extends Component{
  constructor(props) {
    super(props);
    this.state = { isLogin: false };
    this.esHref = window.location.origin + '/es/node/1'
  };

  componentDidMount() {
    axios.get(`/wind/json/language`)
      .then(res => {
        console.log(res.data);
      });
  }

  render(){
    return (
      <>
        <a href={this.esHref} className="nav-link language-toggle" lang="es" data-pt-name="hd_espanol">Español</a>
      </>
    );
  }
}
