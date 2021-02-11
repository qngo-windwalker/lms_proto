
'use strict';
import React from "react";
import {
  BrowserRouter as Router,
  Switch,
  Route,
  HashRouter,
  Link
} from "react-router-dom";
import axios from 'axios';
import { createPortal } from "react-dom";
import {Progress} from 'reactstrap';
import { ToastContainer, toast } from 'react-toastify';
// import 'react-toastify/dist/ReactToastify.css';

/**
 * Required file_upload.css
 */
export default class FileUpload extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      selectedFile: null,
      loadedProgress:0,
      uploadedFile : false
    };
    this.onChangeHandler = this.onChangeHandler.bind(this);
    this.onClickHandler = this.onClickHandler.bind(this);
    this.onFileRemoveClickHandler = this.onFileRemoveClickHandler.bind(this);
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
    console.log(event.target.files[0])
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
        // console.log(res.data.message);
        if(res.data.hasOwnProperty('error') ){
          res.data.hasOwnProperty('message') && toast.error(res.data.message);
        }

        if(res.data.hasOwnProperty('success') ){
          res.data.hasOwnProperty('message') && toast.success(res.data.message);
          this.setState({uploadedFile: true});
        }
      }
    })
  }

  onFileRemoveClickHandler(e) {
    this.setState({
      uploadedFile: false,
      selectedFile : null,
      loadedProgress: 0
    });
  }

  render() {
    if(this.state.uploadedFile){
      return (
        <div>
          <h4>File uploaded</h4>
          <ul className="list-group">
            <li className="list-group-item d-flex justify-content-between align-items-center">
              <a href={'#'}><i className="fas fa-file mr-1"></i>  Very long long file Name.ext</a> <span className="file-size text-secondary text-monospace">23. MB </span>
              <button className="btn btn-outline-secondary" onClick={this.onFileRemoveClickHandler}>
                <i className="fas fa-minus-circle rm-1"></i> Remove
              </button>
            </li>
            <li className="list-group-item d-flex justify-content-between align-items-center">
              Verified Status
              <span className="badge badge-warning">No</span>
            </li>
          </ul>

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
