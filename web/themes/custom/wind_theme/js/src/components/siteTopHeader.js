import React, {Component} from 'react';
import axios from 'axios';
import UserMenu from './userMenu';
import HeaderTopLeft from './headerTopLeft';

export default class SiteTopHeader extends Component{
  constructor(props) {
    super(props);
    this.state = { isLogin: false };
  };

  componentDidMount() {
    axios.get(`/user/login_status?_format=json`)
      .then(res => {
        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({ isLogin : res.data == 1 ? true : false });
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
    // Todo: (Low priority) See https://getbootstrap.com/docs/4.5/components/navbar/ for a more clearner markup structure.

    let authenticatedOutput;
    let dashboardLink = this.getDashboardLink();
    if (this.state.isLogin) {
      authenticatedOutput = <><a className="link link-dashboard" href={dashboardLink.href} className="nav-link">{dashboardLink.label}</a></>;
    }

    return (
      <>
        <div id="header-top-left" className="my-0 mr-md-auto ">
          <HeaderTopLeft data={this.state}/>
        </div>

        <div id="header-top-right" className="">
          <nav className="navbar navbar-expand-lg">
            <ul className="navbar-nav mr-auto">
              {/*<li className="nav-item"><LanguageSwitcher /></li>*/}
              <li className="nav-item">{authenticatedOutput}</li>
              <li className="nav-item"><UserMenu /></li>
            </ul>
          </nav>
        </div>
      </>
    );
  }
}
