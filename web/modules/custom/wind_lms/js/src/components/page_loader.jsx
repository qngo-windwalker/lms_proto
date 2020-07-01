import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  withRouter,
  Link
} from "react-router-dom";
import { connect } from 'react-redux'

const mapStateToProps = (state) => {
    return {
        somethingFromStore: state
    }
}

class PageLoader extends React.Component {
  constructor(props) {
    super(props);
     this.state = {
      components: [],
      component: null,
      page : undefined,
      locationHash : ''
    };
  }

async componentDidMount() {
    const { events } = this.props;
    // events.map(async type => await this.addComponent(type));
    this.getComponent();
  }

  componentDidUpdate(prevProps) {
    const locationChanged = this.props.location !== prevProps.location;
    if (locationChanged){
      this.getComponent();
    }
  }

  getComponent(){
    let locationHash = this.props.location.hash;
    let pageComponentName = locationHash == '#/' ? 'home' : locationHash.replace('#/', '');
    this.addComponent(pageComponentName);
  }


  /**
   * @see https://sung.codes/blog/2018/10/28/loading-react-components-dynamically-on-demand-using-react-lazy/
   */
   addComponent(type) {

    console.log(`Loading %c ${type} page...`, 'color: green;');

  {/*
    Dynamic loading moules.
    Webpack will output the file to js/dist/.. as configured in 
      webpack.config.js 
        @ output {
          chunkFilename: '[name].bundle.js',
          publicPath: 'js/dist/',
        },
      }
  */}
    // import('../pages/' + type + '.js')
    import(`../pages/${type}.js`)
    .then(module  =>
      this.setState({
        components: this.state.components.concat(module.default),
        component: module.default,
        page : type,
        locationHash: this.props.location.hash
      })
    ).catch(error => {
      console.error(`"${type}" not yet supported`);
      console.error(error);
    });;
  };

  render() {
    if (this.state.component === null) return <div>Loading...</div>;

    const componentsElement = <this.state.component />
    return <>{componentsElement}</>;
  }
}


/**
 * Use 'withRouter' to get access to 'history' object's properties.
 * https://github.com/ReactTraining/react-router/blob/master/packages/react-router/docs/api/withRouter.md
 */
export default withRouter(PageLoader)