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
import CurrentUserCourseTable from './currentUserCourseTable';
import DashboardAllUserProgressTable from './dashboardAllUserProgressTable';
import DashboardAllCoursesTable from './dashboardAllCoursesTable';
import SideModalContentUser from "./sideModalContentUser";
import SideModalContentCertUpload from "./sideModalContentCourseCertUpload";
import DashboardTeamTable from "./dashboardTeamTable";
import {Spinner} from "./GUI";

export default class  DashboardPanel extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isLoaded: false,
      currentUser : null,
      error: null,
    };
  }

  componentDidMount() {
    this.load(`/wl-json/currentuser`);
  }

  async load(url) {
    axios.get(url)
      .then(res => {
        // const posts = res.data.data.children.map(obj => obj.data);
        this.setState({
          isLoaded : true,
          currentUser : res.data
        });
      }).catch( (error) => {

        this.setState({
          isLoaded : true,
          error
        });
      }
    );
  }

  render() {
    const { error, isLoaded } = this.state;
    if (error) {
      return <></>;
    } else if (!isLoaded) {
      return (
        <Spinner text={'Loading...'} />
      );
    }

    let displayTeam = 'all';
    if(this.state.currentUser.roles.includes('manager')){
      displayTeam = this.state.currentUser.field_team;
    }

    return(
      <>
        {this.state.currentUser ? <CurrentUserCourseTable /> : <p>Loading...</p>}
        {this.doesCurrentUserHasManagerAccess() && <ManagerTables currentUser={this.state.currentUser} displayTeam={displayTeam} />}
        <Route path={["/user/:id", "/course/:nid/user/:uid/cert/upload"]} render={routeProps => { return (
          <Modala>
            {routeProps.match.path == '/user/:id' && <SideModalContentUser {...routeProps}/>}
            {routeProps.match.path == '/course/:nid/user/:uid/cert/upload' && <SideModalContentCertUpload currentUser={this.state.currentUser} {...routeProps}/>}
          </Modala>
        );}}/>
      </>
    );
  }

  doesCurrentUserHasManagerAccess() {
    if(!this.state.currentUser){
      return false;
    }

    if(this.state.currentUser.roles.includes('administrator')
      || this.state.currentUser.roles.includes('company_admin')
      || this.state.currentUser.roles.includes('manager')
    ) {
      return true
    }

    return false;
  }
}

function ManagerTables(props){
  const currentUser = props.currentUser;
  const displayTeam = props.displayTeam;
  const [isError, setIsError] = useState(null);
  const [isLoaded, setIsLoaded] = useState(false);
  const [items, setItems] = useState(null);

  const isEnglishMode = () => {
    let pathname = window.location.pathname;
    // if we are on 'es' spanish mode
    if(pathname.split('/')[1] == 'es'){
      return false;
    }
    return true;
  }

  useEffect(() => {
    let url = new URL(window.location.href);
    let testParam = url.searchParams.get('test') ? 'test=true' : '';
    let langParam = isEnglishMode() ? 'en' : 'es';

    const fetchData = async () => {
      try {
        const response = await fetch(`/wl-json/all-users-progress/?lang=${langParam}&${testParam}`);
        let result = await response.json();
        setItems(result);
        console.log(result);
      } catch (error) {
        console.log(error);
        setIsError(true);
      }
      setIsLoaded(true);
    }

    fetchData();
  }, []);

  return (
    <>
      {isError && <div className={`text-danger`}>Something went wrong ...</div>}
      {!isLoaded ?
        <Spinner text={'Loading...'} />
       : (
        <>
          <DashboardAllUserProgressTable currentUser={currentUser} displayTeam={displayTeam} data={items} />
          <DashboardTeamTable currentUser={currentUser} data={items} />
        </>
      )}
      <DashboardAllCoursesTable />
    </>
  )
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
    }, 350);
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
    }, 100);
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
            <button onClick={back} type="button" className="btn btn-outline-primary mx-auto" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  );
}


