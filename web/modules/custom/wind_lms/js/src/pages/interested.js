
'use strict';
import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";

import { connect } from 'react-redux'
import { updateProduct } from  '../actions/productsActions';
import { updateUser } from  '../actions/userActions';

const mapStateToProps = state => ({
  products: state.products,
  user: state.user
});

// To allow automatically dispatch Redux Action to the Redux Store
const mapActionsToProps = {
  onUpdateProduct : updateProduct,
  onUpdateUser : updateUser

};

class InterestedPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = { liked: false };


    this.onUpdateProduct = this.onUpdateProduct.bind(this);
  }

  onUpdateProduct(event){
    // This method defined in mapActionsToProps object
    this.props.onUpdateProduct({name: event.currentTarget.value});

  }


  render() {
    console.log(this.props);
    return(
      <div className="mb-3">
          <h6>I am interested in: </h6>
          <ul>
            <li><button onClick={this.onUpdateProduct} value="E-Learning">E-Learning </button></li>
            <li><button onClick={this.onUpdateProduct} value="Classroom training">Classroom training </button></li>
            <li><button onClick={this.onUpdateProduct} value="Virtual Instructor Led Training">Virtual Instructor Led Training </button></li>
            <li><button onClick={this.onUpdateProduct} value="Other">Other (example podcast, infographics, posters) </button></li>
          </ul>
          <div>Selected: {this.props.products.map((product, index) => (
            <div key={index}>{product.name}</div>
            ))}</div>
      </div>
    );
  }
}


// wrap InterestedPage in connect and pass in mapStateToProps
// export default connect(mapStateToProps)(InterestedPage)
export default connect(mapStateToProps, mapActionsToProps)(InterestedPage);
