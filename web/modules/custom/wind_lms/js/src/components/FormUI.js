
'use strict';

import React, {useEffect, useState} from "react";
import axios from 'axios';
import { ToastContainer, toast } from 'react-toastify';
import _ from 'lodash';

const MAX_FILE_SIZE = 10485760;
/**
 * Required file_upload.css
 */
export class SimpleFileInput extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      selectedFile: null,
      loadedProgress:0,
      uploadedFile : false,
      uploadedFiles : []
    };
    this.onChangeHandler = this.onChangeHandler.bind(this);
  }

  checkMimeType(event){
    //getting file object
    let files = event.target.files
    //define message container
    let err = []
    // list allow mime type
    const types = ['image/png', 'image/jpeg', 'image/gif', 'application/pdf']
    // loop access array
    for(let i = 0; i < files.length; i++) {
      // compare file type find doesn't matach
      if (types.every(type => files[i].type !== type)) {
        // create error message and assign to container
        err[i] = files[i].type+' is not a supported format\n';
      }
    };

    for(let z = 0; z < err.length; z++) {// if message not same old that mean has error
      // discard selected file
      toast.error(err[z])
      event.target.value = null
    }
    return true;
  }

  maxSelectFile(event){
    let files = event.target.files
    if (files.length > 1) {
      event.target.value = null
      toast.warn('Only 1 file can be uploaded at a time')
      return false;
    }
    return true;
  }

  checkFileSize(event) {
    let files = event.target.files
    let size = MAX_FILE_SIZE
    let err = [];
    for (let i = 0; i < files.length; i++) {
      if (files[i].size > size) {
        err[i] = files[i].type + 'is too large, please pick a smaller file\n';
      }
    }

    if (err.length) {
      toast.warn(`The file size exceeded the maximum allowed of ${formatBytes(MAX_FILE_SIZE)}. Please pick a smaller file.`)
      return false;
    } else {
      return true;
    }
  }

  onChangeHandler(event){
    // if(this.maxSelectFile(event) && this.checkMimeType(event) && this.checkFileSize(event)){
    if(this.maxSelectFile(event) && this.checkFileSize(event)){
      this.setState({
        selectedFile: event.target.files[0],
        loadedProgress: 0,
      });

      if(this.props.hasOwnProperty('onChange')){
        this.props.onChange(event);
      }
    }
  }


  render() {
    return (
      <>
        <input className="form-control-file" onChange={this.onChangeHandler} type="file" />
      </>
    );
  }
}

/**
 * This function is outside of any class so both FileUpload class and
 * FileListItem hook can utilize it.
 * @param bytes
 * @param decimals
 * @returns {string}
 */
function formatBytes(bytes, decimals = 2){
  if (bytes === 0) return '0 Bytes';

  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

  const i = Math.floor(Math.log(bytes) / Math.log(k));

  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function FileListItem(props) {
  // Define variable and it's setFunction
  const [className, setClassName] = useState('');

  let onFileRemoveClickHandler = e => {
    e.stopPropagation();

    if(props.hasOwnProperty('onFileRemove')){
      e.props = props;
      props.onFileRemove(e);
    }
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
      // Delayed set class to use transition
      setClassName('show');
    }, 100);
    // returning a function inside useEffect hook is like using a componentWillUnmount()
    // lifecycle method inside class-based react components.
    return () => clearTimeout(timer);
  },[]);

  return (
    <ul className={`list-group mb-4 ${className}`}>
      <li className="list-group-item d-flex justify-content-between align-items-center">
        <a href={props.file.uri} download><i className="fas fa-file mr-1"></i> {props.file.filename}</a>
        <button className="btn btn-outline-secondary" onClick={onFileRemoveClickHandler}>
          <i className="fas fa-minus-circle rm-1"></i> Remove
        </button>
      </li>
      <li className="list-group-item d-flex justify-content-between align-items-center">
        File Size
        <span className="file-size text-secondary text-monospace">{formatBytes(props.file.filesize)}</span>
      </li>
    </ul>
  );
}
