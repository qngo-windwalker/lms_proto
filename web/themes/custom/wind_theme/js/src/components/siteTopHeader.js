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

  render(){
    return (
      <>
        <div id="header-top-left" className="col-md-8">
          <HeaderTopLeft data={this.state}/>
        </div>

        <div id="header-top-right" className="col-md-4">
          <UserMenu />
        </div>
      </>
    );
  }
}
