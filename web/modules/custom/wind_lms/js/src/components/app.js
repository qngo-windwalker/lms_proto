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

class App extends Component{
	constructor(props) {
    	super(props);
		this.onUpdateUser = this.onUpdateUser.bind(this);
    };

    onUpdateUser(event){
    	// This method defined in mapActionsToProps object
    	this.props.onUpdateUser('User 3');
  	}

	render(){
		console.log(this.props);
		return (
			<>
        <div className="learning-path-steps-summary"><span className="learning-path-steps-summary-state-pending"></span>
          <h3 className="learning-path-steps-summary-title">In progress</h3>
          <p className="learning-path-steps-summary-score">Average score : 0%</p>
          <p className="learning-path-steps-summary-progress">Progress : 0%</p>
        </div>
        <h3 className="learning-path-steps-title">Course 1</h3>
        <table className="table responsive-enabled" data-striping="1">
          <thead>
          <tr>
            <th>Name</th>
            <th>State</th>
          </tr>
          </thead>
        </table>
			</>
		);
	}
}

// The parentheses is to automatically return this object
const mapStateToProps = state => ({
	products: state.products,
	user: state.user
});

// const mapStateToProps = state => {
// 	return state;
// };

// To allow automatically dispatch Redux Action to the Redux Store
const mapActionsToProps = {
	onUpdateProduct : updateProduct,
	onUpdateUser : updateUser

};

// Connect Redux to App
// Which allows <App /> to have 'state' this.props
export default connect(mapStateToProps, mapActionsToProps)(App);
