import React, {Component} from 'react';
import axios from 'axios';

export default class HeaderTopLeft extends Component{
  constructor(props) {
    super(props);
  };

  getDashboardLink() {
    let pathname = window.location.pathname;
    // if we are on 'es' spanish mode
    if(pathname.split('/')[1] == 'es'){
      return {
        href : '/es/dashboard',
        label : 'Tablero'
      }
    }
    return {
      href: '/dashboard',
      label: 'Dashboard'
    };
  }

  render(){
    let authenticatedOutput;
    let dashboardLink = this.getDashboardLink();
    if (this.props.data.isLogin) {
      authenticatedOutput = <><a className="link link-dashboard" href={dashboardLink.href} className="nav-link">{dashboardLink.label}</a></>;
    }
    return (
      <>
        <a id="site-logo"  className="my-0 mr-md-auto font-weight-normal" href="/" rel="Home" title="Home">
          <img src="/themes/custom/wind_theme/img/windwalker_logo.jpg" alt="Windwalker Group logo"/>
        </a>
        <nav id="site-header-left-nav" className="my-2 my-md-0 mr-md-3">
          {authenticatedOutput}
        </nav>
      </>
    );
  }

}
