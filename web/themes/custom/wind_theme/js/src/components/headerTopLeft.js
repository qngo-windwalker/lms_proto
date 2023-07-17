import React, {Component} from 'react';
import axios from 'axios';

export default class HeaderTopLeft extends Component{
  constructor(props) {
    super(props);
    this.state = {
      siteLogoImgSrc: '',
      currentUser : null
    };
  };

  async componentDidMount() {
    axios.get(`/wind/json/site-info`)
      .then(res => {
        // Get the custom user uploaded logo if logoUseDefault is false.
        let logoPath = res.data.site.logoUseDefault ? '/themes/custom/wind_theme/img/windwalker_logo.jpg' : res.data.site.logoPath;
        this.setState({
          currentUser : res.data.currentUser,
          siteLogoImgSrc : logoPath
        });
      });
  }

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
          <img src={this.state.siteLogoImgSrc} alt="Company Logo"/>
        </a>
        {/*<nav id="site-header-left-nav" className="my-2 my-md-0 mr-md-3">*/}
        {/*  {authenticatedOutput}*/}
        {/*</nav>*/}
      </>
    );
  }

}
