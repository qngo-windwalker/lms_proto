
'use strict';
import React, {useEffect, useState} from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link, useHistory, useParams
} from "react-router-dom";
import axios from 'axios';
import { createPortal } from "react-dom";
import {Progress} from 'reactstrap';
import { ToastContainer, toast } from 'react-toastify';
// import 'react-toastify/dist/ReactToastify.css';
import _ from 'lodash';

/**
 * Required file_upload.css
 */
export default class FileUpload extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      selectedFile: null,
      loadedProgress:0,
      uploadedFile : false,
      uploadedFiles : []
    };
    this.onChangeHandler = this.onChangeHandler.bind(this);
    this.onClickHandler = this.onClickHandler.bind(this);
    this.onFileRemove = this.onFileRemove.bind(this);
  }

  componentDidMount() {
    this.load(`${this.props.postURL}?getAllFiles=true`);
  }

  async load(url) {
    axios.get(url)
      .then(res => {
        this.setState({
          uploadedFile : true,
          uploadedFiles : res.data.files
        });
      });
  }

  checkMimeType(event){
    //getting file object
    let files = event.target.files
    //define message container
    let err = []
    // list allow mime type
    const types = ['image/png', 'image/jpeg', 'image/gif']
    // loop access array
    for(var x = 0; x<files.length; x++) {
      // compare file type find doesn't matach
      if (types.every(type => files[x].type !== type)) {
        // create error message and assign to container
        err[x] = files[x].type+' is not a supported format\n';
      }
    };
    for(var z = 0; z<err.length; z++) {// if message not same old that mean has error
      // discard selected file
      toast.error(err[z])
      event.target.value = null
    }
    return true;
  }

  maxSelectFile(event){
    let files = event.target.files
    if (files.length > 3) {
      const msg = 'Only 3 images can be uploaded at a time'
      event.target.value = null
      toast.warn(msg)
      return false;
    }
    return true;
  }

  checkFileSize(event) {
    let files = event.target.files
    let size = 2000000
    let err = [];
    for (var x = 0; x < files.length; x++) {
      if (files[x].size > size) {
        err[x] = files[x].type + 'is too large, please pick a smaller file\n';
      }
    }
  }
  onChangeHandler(event){
    this.setState({
      selectedFile: event.target.files[0],
      loadedProgress: 0,
    });
  }

  onClickHandler(e){
    if (!this.state.selectedFile) {
      toast.warn('Please click Choose File to to upload.')
      return;
    }
    const data = new FormData();
    data.append('file', this.state.selectedFile)
    axios.post(this.props.postURL, data, {

      onUploadProgress: ProgressEvent => {
        this.setState({
          loadedProgress: (ProgressEvent.loaded / ProgressEvent.total*100),
        })
      }
      // receive two parameter endpoint url ,form data
    }).then(res => { // then print response status
      // If server doesn't crash
      if(res.statusText == 'OK'){
        console.log(res.data);
        if(res.data.hasOwnProperty('error') ){
          // res.data.hasOwnProperty('message') && toast.error(res.data.message);
        }

        if(res.data.hasOwnProperty('success') ){
          // res.data.hasOwnProperty('message') && toast.success(res.data.message);
          this.setState({
            uploadedFile: true,
            uploadedFiles: [res.data.file],
            loadedProgress:0,
            selectedFile: null,
          });
        }
      }
    })
  }

  onFileRemove(e) {
    // e.props is available b/c we added as a property of the event
    let fileListItemProps = e.props;
    axios.get(`${this.props.postURL}?remove-fid=${fileListItemProps.file.fid}&cert-nid=${fileListItemProps.file.certificate_nid}`)
      .then(res => {
        if(res.statusText == 'OK'){
          if(res.data.hasOwnProperty('success') ){
            // res.data.hasOwnProperty('message') && toast.success(res.data.message);
            let uploadedFiles = this.state.uploadedFiles;
            _.remove(uploadedFiles, function(currentObject) {
              return currentObject.fid == fileListItemProps.file.fid;
            });

            this.setState({
              uploadedFiles: uploadedFiles,
            });
          }
        }
      });
  }

  render() {
    console.log(this.state.uploadedFiles);
    if(this.state.uploadedFiles.length){
      return (
        <div>
          <h4>File uploaded</h4>
            {this.state.uploadedFiles.map((obj, index) => {
              return (
                <FileListItem file={obj} key={index} onFileRemove={this.onFileRemove} />
              );
            })}
        </div>
      );
    }

    return (
      <form>
        <div className="form-group files">
          <label htmlFor="file">Drag & Drop or <span className={`filepond--label-action`}>Browse</span></label>
          <input type="file" className="form-control form-control-file" id="file-upload" name="file" onChange={this.onChangeHandler}/>
        </div>

        <div className="form-group">
          <ToastContainer/>
          <Progress max="100" color="success" value={this.state.loadedProgress}>{Math.round(this.state.loadedProgress, 2)}%</Progress>
        </div>

        <div className="form-group text-align-center">
          <button type="button" className={`btn mx-auto ${this.state.selectedFile ? 'btn-primary' : 'btn-outline-secondary disabled'}`} onClick={this.onClickHandler}><i className="fas fa-file-upload mr-1"></i> Upload</button>
        </div>
      </form>
    );
  }
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

  let formatBytes = (bytes, decimals = 2)  => {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  }

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
      <li className="list-group-item d-flex justify-content-between align-items-center">
        Verified Status
        <span className="badge badge-warning">No</span>
      </li>
    </ul>
  );
}
