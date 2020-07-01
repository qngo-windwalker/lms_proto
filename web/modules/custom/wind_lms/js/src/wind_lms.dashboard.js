
'use strict';

import React, {Component} from 'react';
import ReactDOM from "react-dom";
import {
  BrowserRouter,
  Switch,
  Route,
  HashRouter,
  withRouter,
  Link
} from "react-router-dom";

import { Provider, connect } from 'react-redux'
import { combineReducers, createStore } from 'redux';
// import rootReducer from './reducers'

import productsReducer from './reducers/products-reducer';
import userReducer from './reducers/usersReducer';
import { updateProduct } from  './actions/productsActions';
import App from './components/app';

// function userReducer(state = '', action){
// 	switch(action.type){
// 		case 'updateUser':
// 			return action.payload;
// 			break;
// 	}
// 	return state;
// }

const allReducers = combineReducers({
	products: productsReducer,
	user: userReducer

})
const store = createStore(
	allReducers,
	{
		products: [
			{
				name: '',
				desc: '',
			}
		],
		user: 'User 1'
	},
	window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__()
);

// console.log(store.getState());

// const appGlobalVars = new GlobalVars;

  //   	console.log(GlobalVars.getPathnameFromRoot());
		// ReactDOM.render(<YourCart />, document.getElementById('your-cart-container'));
		//  // The <Router> wrap is b/c <SearchBar />  export default withRouter()
		// ReactDOM.render(<BrowserRouter> <PageLoader appGlobalVars={appGlobalVars} /> </BrowserRouter> , document.getElementById('page-container'));

ReactDOM.render(<Provider store={store}><App /></Provider>, document.getElementById('react-dashboard-container'));

const updateUserAction = {
	type: 'updateUser',
	payload: {
		user: 'User 2'
	}
}


// const updateProductAction = {
// 	type: 'updateProduct',
// 	payload: {
// 		products: [ { name : 'Product 2', value: 3}]
// 	}
// }


// store.dispatch(updateUserAction);
// store.dispatch(updateProductAction);
// console.log(store.getState());
