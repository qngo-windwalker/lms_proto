import React, {Component} from 'react';
import axios from "axios";

export default class AllUserProgressTable extends Component{
  constructor(props) {
    super(props);
    this.state = { tableRow: [] };
    this.courseClickHandler = this.courseClickHandler.bind(this);
  };

  componentDidMount() {
    let url = new URL(window.location.href);
    let testParam = url.searchParams.get('test') ? 'test=true' : '';
    let langParam = this.isEnglishMode() ? 'en' : 'es';
    axios.get(`/wind-tincan-course/course-progress-datatable/?lang=${langParam}&${testParam}`)
      .then(res => {
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
    if(pathname.split('/')[1] == 'es'){
      return false;
    }
    return true;
  }

  render(){
    return (
      <>
        <h2>{this.isEnglishMode() ? 'Users Progress' : 'Progreso De Los Usuarios'}</h2>
        <table className="table responsive-enabled" data-striping="1">
          <thead>
          <tr>
            <th>{this.isEnglishMode() ? 'Username' : ''}</th>
            <th>{this.isEnglishMode() ? 'Email' : ''}</th>
            <th>{this.isEnglishMode() ? 'Status' : 'Estado'}</th>
            <th>{this.isEnglishMode() ? 'Enroll Date' : ''}</th>
            <th>{this.isEnglishMode() ? 'Course' : 'Progreso'}</th>
            <th>{this.isEnglishMode() ? 'Progress' : 'Progreso'}</th>
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
    console.log(dataObj);
    return(
      <tr key={key}>
        <td scope="row" className="text-left" dangerouslySetInnerHTML={{__html: dataObj.username}}></td>
        <td>{dataObj.mail}</td>
        <td>{dataObj.status}</td>
        <td>{dataObj.created}</td>
        <td>{dataObj.courseTitle}</td>
        <td dangerouslySetInnerHTML={{__html: dataObj.courseProgress}}></td>
      </tr>
    );
  }

  parseJson(data) {
    let collection = [];
    let dataObj = data.data;
    for(let i = 0; i < dataObj.length; i++){
      let Comp = this.rendertBodyRow(dataObj[i], i);
      // If user has not made selected an option to this item, then skip it.
      collection.push({Comp : Comp});
    }
    // const posts = res.data.data.children.map(obj => obj.data);
    this.setState({
      tableRow : collection
    });
  }
}
