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
    let pathname = window.location.pathname;
    let split = pathname.split('/');
    if(split[1] == 'es'){
      this.setState({
        languageLabel : 'English',
        href : pathname.replace('/es/', '/')
      });
    } else {
      this.setState({
        languageLabel : 'Español',
        href : '/es' + pathname
      });
    }
  }

  render(){
    return (
      <>
        <a href={this.state.href} className="nav-link language-toggle" lang="es" data-pt-name="hd_espanol">{this.state.languageLabel}</a>
      </>
    );
  }
}
