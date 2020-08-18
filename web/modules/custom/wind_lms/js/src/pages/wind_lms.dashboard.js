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
import productsReducer from '../reducers/products-reducer';
import userReducer from '../reducers/usersReducer';
import { updateProduct } from  '../actions/productsActions';
import UserCourseTable from '../components/userCourseTable';

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

ReactDOM.render(<Provider store={store}><UserCourseTable /></Provider>, document.getElementById('react-container'));
