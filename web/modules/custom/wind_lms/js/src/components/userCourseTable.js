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
import axios from "axios";

class UserCourseTable extends Component{
	constructor(props) {
    super(props);
    this.state = { tableRow: [] };
    this.courseClickHandler = this.courseClickHandler.bind(this);
  };

  componentDidMount() {
    axios.get(`/wind-lms/json/dashboard`)
      .then(res => {
        console.log(res.data);
        this.parseJson(res.data);
        this.addPopupClickEvent();
      });
  }

  componentDidUpdate(){
    this.addPopupClickEvent();
  }

  addPopupClickEvent(){
    const buttons = document.querySelectorAll('a.wind-scorm-popup-link');
    for (const button of buttons) {
      // Javascript doesn't have hasEventListener function.
      // Add and attribute to make sure we only attach click handler 1 time.
      if(!button.hasAttribute('data-listener-attached')){
        // Add click event handler to modules from search result.
        button.addEventListener('click', (e) => this.courseClickHandler(e));
        button.setAttribute('data-listener-attached', 'click');
      }
    }
  }

  courseClickHandler(e){
    let elem = e.currentTarget;
    e.preventDefault();
    if(elem.hasAttribute('data-coure-href')){
      let href = elem.getAttribute('data-coure-href');
      this.popup(href);
    }
  }

  popup(href){
    var day = new Date();
    var id = day.getTime();
    var screenHeight = screen.height >= 768 ? 700 : screen.height;
    var params = ['toolbar=no', 'scrollbars=no', 'location=no', 'statusbar=no', 'menubar=no', 'directories=no', 'titlebar=no', 'toolbar=no', 'resizable=1', 'height=' + screenHeight, 'width=1024'
      //            'fullscreen=yes' // only works in IE, but here for completeness
    ].join(',');
    window.open(href, "window" + id, params);
  }

  isEnglishMode() {
    let pathname = window.location.pathname;
    // if we are on 'es' spanish mode
    console.log(pathname.split('/'));
    if(pathname.split('/')[1] == 'es'){
      return false;
    }
    return true;
  }

	render(){
		return (
			<>
        <h2>{this.isEnglishMode() ? 'My Training' : 'Mi Entrenamiento'}</h2>
        <table className="table responsive-enabled" data-striping="1">
          <thead>
          <tr>
            <th>Name</th>
            <th>Status</th>
            <th>Certificate</th>
          </tr>
          </thead>
          <tbody>
          {this.state.tableRow.map((obj, index) => {
            return obj.Comp;
            // return this.rendertBodyRow(obj.name, obj.rateFormatted, obj.hours, obj.priceCalculatedFormatted, index);
          })}
          </tbody>
        </table>
			</>
		);
	}

  rendertBodyRow(dataObj, key){
    return(
      <tr key={key}>
        <td scope="row" className="text-left" dangerouslySetInnerHTML={{__html: dataObj.data[0]}}></td>
        <td>{dataObj.data[1]}</td>
        <td dangerouslySetInnerHTML={{__html: dataObj.data[2]}}></td>
      </tr>
    );
  }

  parseJson(data) {
    let collection = [];
    for(var i = 0; i < data.tableRow.length; i++){
      let Comp = this.rendertBodyRow(data.tableRow[i], i);
      // If user has not made selected an option to this item, then skip it.
      collection.push({Comp : Comp});
    }
    // const posts = res.data.data.children.map(obj => obj.data);
    this.setState({
      tableRow : collection
    });
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
export default connect(mapStateToProps, mapActionsToProps)(UserCourseTable);
