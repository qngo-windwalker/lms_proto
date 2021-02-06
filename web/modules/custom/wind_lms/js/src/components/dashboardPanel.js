'use strict';

import React, { useEffect, useState  } from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  useLocation, useHistory, useParams,
  HashRouter,
  Link
} from "react-router-dom";
import axios from 'axios';
import UserCourseTable from './userCourseTable';
import AllUserProgressTable from './allUserProgressTable';
import DashboardAllCoursesTable from './dashboardAllCoursesTable';
import ModalContent from "./modalContent";

export default class DashboardPanel extends React.Component {
  constructor(props) {
    super(props);
    this.state = { currentUser : null };
  }

  componentDidMount() {
    axios.get(`/wind/json/current-user`)
      .then(res => {
        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({
          currentUser : res.data
        });
      });
  }

  render() {
    return(
      <>
        <Link to="/user/1">User</Link>
        <Link to="/user/5">User</Link>
        <UserCourseTable />
        {this.getAllUsersProgressTable()}
        {this.getDashboardAllCoursesTable()}
        <Route path="/user/:id"
          render={routeProps => {
            return (
              <Modala>
                <ModalContent {...routeProps}/>
              </Modala>
            );
          }}
        />
      </>
    );
  }

  getAllUsersProgressTable() {
    if(!this.state.currentUser){
      return null;
    }

    if(this.state.currentUser.roles.includes('administrator') || this.state.currentUser.roles.includes('manager')) {
      return <AllUserProgressTable />;
    }

    return null;
  }

  getDashboardAllCoursesTable() {
    if(!this.state.currentUser){
      return null;
    }

    if(this.state.currentUser.roles.includes('administrator') || this.state.currentUser.roles.includes('manager')) {
      return <DashboardAllCoursesTable />;
    }

    return null;
  }
}

function Modala(props) {
  // Define variable and it's setFunction
  const [className, setClassName] = useState('');
  let history = useHistory();
  let { id } = useParams();

  let back = e => {
    e.stopPropagation();
    // Remove the class name so the CSS transition to play
    setClassName('');
    setTimeout(() => {
      // After CSS transition finished, call history to hide this hook.
      history.goBack();
    }, 200);
  };

  /**
   * Note: we have passed empty array [] as a second argument
   * to the useEffect hook so that it only runs when a App functional component
   * is initially rendered into the dom,
   * it is similar like componentDidMount in class components.
   * @see https://reactgo.com/settimeout-in-react-hooks/
   */
  useEffect(() => {
    const timer  = setTimeout(() => {
      // Assign className variable to 'show'. Which will add animation to modal.
      setClassName('show');
    }, 350);
    // returning a function inside useEffect hook is like using a componentWillUnmount()
    // lifecycle method inside class-based react components.
    return () => clearTimeout(timer);
  },[]);

  return (
      <div className={`modal fade side-modal ${className} `} onClick={back} aria-modal="true">
        <div className="modal-dialog" onClick={ e => { e.stopPropagation()}} >
          <div className="modal-content pt-3">
            {props.children}
            <div className="modal-footer text-align-center">
              <button onClick={back} type="button" className="btn btn-secondary mx-auto" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
  );
}


