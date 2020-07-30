import React, {Component} from 'react';
import axios from 'axios';

export default class CurrentUser extends Component{
  constructor(props) {
    super(props);
    this.state = {
      name : ''
    };
  };

  componentDidMount() {
    axios.get(`/wl-json/currentuser`)
      .then(res => {
        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({ name : res.data.name});
      });
  }

  render(){
    if (this.props.isLogin) {
      let userName = '';
      return (
        <>
          {this.state.name}
        </>
      );
    }
    return null;
  }
}
