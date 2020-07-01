import React, {Component} from 'react';
import axios from 'axios';

export default class HeaderTopLeft extends Component{
  constructor(props) {
    super(props);
  };

  render(){
    let authenticatedOutput;
    if (this.props.data.isLogin) {
      authenticatedOutput = <><a className="link link-dashboard" href="/dashboard" className="nav-link">Dashboard</a></>;
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
