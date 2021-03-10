import { createBrowserHistory } from 'history';

const history = createBrowserHistory();
export default history;

/**
 * @see https://codesandbox.io/s/owQ8Wrk3?file=/index.js:1150-1761
 * @see https://stackoverflow.com/a/44163399
 *
 * Usages:

 import React from 'react';
 import { render } from 'react-dom';
 import history from "./history.js";

 import { Router, Route } from "react-router-dom";

 const styles = {
  fontFamily: 'sans-serif',
  textAlign: 'center',
};

 const Test = () => <div>Welcome to /test</div>;

 const App = () => (
 <div style={styles}>
 <Router history={history}>
 <Route path="/test" component={Test} />
 </Router>
 <button onClick={() => history.push('/test')}>goto /test</button>
 <button onClick={() => history.push('/')}>goto /</button>
 </div>
 );

 render(<App />, document.getElementById('root'));
 */
