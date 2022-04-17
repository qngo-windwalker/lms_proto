import React, {useEffect, useState} from "react";
import {useHistory, useParams} from "react-router-dom";
import axios from "axios";

/**
 * @see https://reactjs.org/docs/forwarding-refs.html
 * @type {React.ForwardRefExoticComponent<React.PropsWithoutRef<{}> &
 *   React.RefAttributes<unknown>>}
 */
export const ButtonGem = React.forwardRef((props, ref) => {
  let onClickHandler = e => {
    if(props.hasOwnProperty('onClick')){
      props.onClick(e);
    }
  };

  let isDisabled = () => {
    if (props.hasOwnProperty('disabled') && props.disabled) {
      return true;
    }
    return false;
  }

  const Element = {
    type : props.href ? 'a' : 'button',
    href : props.href ? {href: props.href} : null,
  }

  return (
    <div className={`shape-gem ${props.className == undefined ? '' : props.className}`}>
      <Element.type className={`btn ${isDisabled() ? 'btn-secondary disabled' : 'btn-success'}`} ref={ref} {...Element.href} onClick={(e) => onClickHandler(e)} disabled={isDisabled()} >
        {props.children}
      </Element.type>
    </div>
  );
});

export function Rhombus(props) {
  let getProgressBackgroundPositionTop = () =>{
    if(!props.completePercentage){
      return 100;
    }

    // 77 starting point
    // goes backward to 0 which is 100%.

    // Covert percetage to decimal form.
    let decimal = props.completePercentage / 100;
    // the percentage of 77
    let diff = decimal * 77;
    return 77 - diff;
  }

  let typeClass;
  let progressContent;
  switch (props.type) {
    case 'unlocked' :
      typeClass = 'rhombus-unlock'
      break;
    case 'locked' :
      typeClass = 'rhombus-lock'
      break;
    default :
      let percentageTxt = props.completePercentage ? Math.floor(props.completePercentage) + '%' : '0%';
      typeClass = 'rhombus-progress';
      progressContent = (
        <div className="rhombus-progress-bar" style={{backgroundPosition: '0 ' + getProgressBackgroundPositionTop() + 'px'}}>
          <p>{percentageTxt}</p>
        </div>
      );
      break;
  }
  return(
    <div className={`rhombus rhombus-small ${typeClass}`}>
      <div className="rhombus-mask">
        {progressContent}
      </div>
    </div>
  );
}

/**
 * Example: <Spinner text="Loading..." />
 * @param props
 * @returns {JSX.Element}
 * @constructor
 */
export function Spinner(props) {
  return (
    <div className={`spinner-container text-center ${props.containerClass}`}>
      <div className="spinner-border text-primary" role="status">
        <span className="sr-only">{props.text}</span>
      </div>
    </div>
  );
}

export function Modala(props) {
  // Define variable and it's setFunction
  const [className, setClassName] = useState('');
  const [title, setTitle] = useState(props.title);
  let history = useHistory();
  // let { id } = useParams();

  let back = e => {
    e.stopPropagation();
    // Remove the class name so the CSS transition to play
    setClassName('');
    setTimeout(() => {
      if(props.hasOwnProperty('overrideCloseButton') && props.overrideCloseButton){
        // This apply to the sideModalUserAccount.js
      } else {
        // After CSS transition finished, call history to hide this hook.
        history.goBack();
      }

      if(props.hasOwnProperty('onSlideOutComplete')){
        props.onSlideOutComplete();
      }
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

    // In case parent update title attribute.  Used in <SideModalUserReward />
    if (title != props.title) {
      setTitle(props.title);
    }

    // returning a function inside useEffect hook is like using a componentWillUnmount()
    // lifecycle method inside class-based react components.
    return () => clearTimeout(timer);
  },[props.title]); // Check if there's update. Used in <SideModalUserReward />

  return (
    <div className={`modal fade side-modal ${className} `} onClick={back} aria-modal="true">
      <div className="modal-dialog" onClick={ e => { e.stopPropagation()}} >
        <div className="modal-content">
          <div className="modal-header mb-3">
            <h3 className="modal-title">{title}</h3>
            <button type="button" className="close" onClick={back}  data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div className="modal-body">
            {props.children}
          </div>

          <div className="modal-footer text-align-center">
            <button onClick={back} type="button" className="btn btn-outline-primary mx-auto" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  );
}

export function SideModalLayout(props) {
  let back = e => {
    e.stopPropagation();
  };

  let title = props.hasOwnProperty('title') ? props.title : '';

  return (
    <Modala>
      <div className="modal-header">
        <h3 className="modal-title">{title}</h3>
        <button type="button" className="close" onClick={back}  data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div className="modal-body">
        {props.children}
      </div>
    </Modala>
  )
}

export function AlertMsg(props) {
  return (
    <div className={`alert ${props.className}`} role="alert">{props.children}</div>
  );
}

export function UserAvatarImg(props) {
  const [isLoaded, setIsLoaded] = useState(false);
  const [item, setItem] = useState(null);

  let onRewardItemClick = (e) => {
    setIsLoaded(false);
    loadAvatar();
  }

  let loadAvatar = () => {
    fetch("/skywind/getjson?data=get-avatar")
      .then(res => res.json())
      .then(
        (result) => {
          setTimeout(() => {
            if (result.hasOwnProperty('success')) {
              setItem(result.field_avatar);
            }
            setIsLoaded(true);
          }, 500);
        },
        // Note: it's important to handle errors here
        // instead of a catch() block so that we don't swallow
        // exceptions from actual bugs in components.
        (error) => {
          console.log('%cError loading user avartar.', 'color: orange', error);
        }
      )
  }

  // Note: the empty deps array [] means
  // this useEffect will run once
  // similar to componentDidMount()
  useEffect(() => {
    window.addEventListener("rewardItemClick", onRewardItemClick);
    loadAvatar();
  }, []);

  if (!isLoaded) {
    return <div className='src-only d-none'>Unable to load avartar.</div>;
  } else {
    return (
      <>
        <img className={`user-avatar`} src={`${props.dir}/${item == '' ? 'sword.png' : item}`} />
      </>
    );
  }
}

export function ImgLoader(props) {
  const [isLoaded, setIsLoaded] = useState(false);
  const [item, setItem] = useState(null);

  let loadAvatar = (url) => {
    fetch(url)
      .then(res => res.json())
      .then(
        (result) => {
          setIsLoaded(true);
          setItem(result);
        },
        // Note: it's important to handle errors here
        // instead of a catch() block so that we don't swallow
        // exceptions from actual bugs in components.
        (error) => {
          console.log('%cError loading image.', 'color: orange', error);
        }
      )
  }

  // Note: the empty deps array [] means
  // this useEffect will run once
  // similar to componentDidMount()
  useEffect(() => {
    loadAvatar(props.getFilenameAPI);
  }, []);

  if (!isLoaded) {
    return '';
  } else {
    return (
      <>
        <img className={`user-avatar`} src={`${props.dir}/${item == '' ? 'sword.png' : item}`} />
      </>
    );
  }
}

export function ProgressBar(props){
  let percentage = Math.floor((props.numerator / props.total) * 100);
  return (
    <div className="progress mt-2">
      <div className="progress-bar bg-success" role="progressbar" style={{width: percentage + '%'}} aria-valuenow={percentage} aria-valuemin="0" aria-valuemax="100">
        <span className="d-none" aria-hidden="true">{percentage}%</span>
      </div>
    </div>
  );
}

export function RewardItem(props) {
  const [error, setError] = useState(null);
  const [isLoaded, setIsLoaded] = useState(true);
  let imgURL = '/modules/custom/skywind/img/reward_icons/';
  // let imgSrc = props.isLocked ? imgURL + 'reward_locked_icon.png' : imgURL +  props.imgFile;
  let imgSrc = imgURL +  props.imgFile;
  let button;
  let requireMsg;

  // @see userMenu.js > UserAvatar
  let dispatchWindowEvent = () => {
    const event = new CustomEvent('rewardItemClick', { detail: 'testing' });
    // Dispatch the event.
    window.dispatchEvent(event);
  }

  let applyAvatar = () => {
    setIsLoaded(false);

    axios.get(`/skywind/getjson?data=apply-avatar&id=${props.imgFile}`)
      .then(res => {
        setTimeout(() => {
          if (res.data.hasOwnProperty('success')) {
            dispatchWindowEvent();
          }
          setIsLoaded(true);
        }, 500);

      })
      .catch(error => {
        setError(error);
        setIsLoaded(true);
        console.log(error);
      });
  }

  if (!props.isLocked) {
    button = <ButtonGem disabled={!isLoaded} onClick={() => applyAvatar()}>{isLoaded ? 'Apply Icon' : <Spinner text="Loading..." />} </ButtonGem>;
  } else {
    requireMsg = <p><img className={`locked-icon`} src={`${imgURL}rewards_locked_icon_xs.png`} /> Unlock at {props.requiredSP} SP</p>
  }

  return (
    <div className="media mb-3 reward-item">
      <img src={imgSrc} className={`mr-3 ${props.isLocked ? 'locked' : ''}`} alt={props.imgAlt} />
      <div className="media-body">
        <h5 className="mt-0">{props.title}</h5>
        {button}
        {requireMsg}
      </div>
    </div>
  );
}

export function StatusCircle(props){
  const colorClass = props.label == 'Active' || props.label == 'Published' ? 'text-success' : 'text-danger';
  return (
    <>
      <i style={{fontSize : '0.5rem'}} className={`fas fa-circle fa-xs ${colorClass}`}></i>
      <span className={`ml-2`}>{props.label}</span>
    </>
  );
}

export function ExportCSV(props) {
  let downloadCSV = () => {
    console.log(this)
    const rows = [
      ["name1", "city1", "some other info"],
      ["name2", "city2", "more info"]
    ];

    let csvContent = "data:text/csv;charset=utf-8,"
      + props.data.map(e => e.join(",")).join("\n");

    var encodedUri = encodeURI(csvContent);
    var link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", props.fileName);
    link.style.visibility = 'hidden';
    document.body.appendChild(link); // Required for FF
    link.click();
  }

  return (
    <button className="btn btn-sm btn-outline-secondary" onClick={downloadCSV}>
      Export CSV
    </button>
  )
}
