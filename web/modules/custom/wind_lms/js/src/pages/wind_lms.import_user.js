'use strict';

import React, {useState, Component} from 'react';
import ReactDOM from "react-dom";
import {
  BrowserRouter,
  Switch,
  Route,
  HashRouter,
  withRouter,
  useHistory,
  useLocation,
  Link
} from "react-router-dom";
import axios from "axios";
import {ButtonGem, Spinner} from "../components/GUI";
import {SimpleFileInput} from "../components/FormUI";
import readXlsxFile from "read-excel-file";

export default class ImportUser extends React.Component {

  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return (
      <>
        <HashRouter>
          <Switch>
            <HashRouter path="/">
              <ImportForm/>
            </HashRouter>
          </Switch>
          <LocationConsole />
        </HashRouter>
      </>
    );
  }
}

function ImportForm(props) {
  let item = {
    label: 'User Import',
    value : 'user',
    templateFileName : 'user_template.xlsx',
    api : 'wind-lms/api/import/json?data=user'
  };

  const [processedExcelData, setProcessedExcelData] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  const [success, setSuccess] = useState(null);
  const [error, setError] = useState(null);

  let processExcel = (row) => {
    // To remove the extraneous description.
    row[0][3] = 'Role';
    // Add Username column
    row[0].push('Username');
    for (let i = 0; i < row.length; i++) {
      if(i == 0){ continue;}
      let firstName = row[i][0];
      let lastName = row[i][1];
      let username = firstName.charAt(0) + lastName;
      row[i].push(username);
    }
  }

  let onChangeHandler = (event) => {
    readXlsxFile(event.target.files[0]).then((rows) => {
      // `rows` is an array of rows
      // each row being an array of cells.
      let processedData = processExcel(rows);
      setProcessedExcelData(rows);
    });
  }

  let sendToServer = async () => {
    axios.post(`wind-lms/api/import/json?data=user`, processedExcelData, {
      // Overwrite Axios's automatically set Content-Type
      'Content-Type': 'application/json',
      onUploadProgress: ProgressEvent => {
        // this.setState({
        //   loadedProgress: (ProgressEvent.loaded / ProgressEvent.total*100),
        // })
      }
      // receive two parameter endpoint url ,form data
    }).then(res => { // then print response status
      // If server doesn't crash
      if(res.statusText == 'OK'){
        if(res.data.hasOwnProperty('error') ){
          setError(res.data);
        }

        if(res.data.hasOwnProperty('success') ){
          setSuccess(res.data);
        }

        setTimeout(() => {
          setIsLoading(false);
        }, 500);
      }
    });
  }

  let handleSubmit = (event) => {
    event.preventDefault();
    setIsLoading(true);
    sendToServer();
  }

  let getMessage = () => {
    if(success){
      return(
        <div className="alert alert-success" role="alert">Content successfully imported.</div>
      );
    }

    if (error) {
      return(
        <div className="alert alert-warning" role="alert">There was an error while importing.</div>
      );
    }
  }

  return (
    <form onSubmit={handleSubmit}>
      {getMessage()}
      <div>
        <h4>1. Download the excel template file and add the information.</h4>
        <p>Excel template: <a href={`/modules/custom/wind_lms/file/import_template/${item.templateFileName}`} download> <i className="far fa-file-excel"></i> {item.templateFileName}</a> </p>
      </div>
      <h4>2. Upload the completed excel file.</h4>
      <div className="form-group border border-info rounded-sm p-3">
        <label className="w-100"><strong>File Upload</strong>
          <SimpleFileInput onChange={onChangeHandler}  />
        </label>
      </div>

      <h4>3. Click Import to finish the import.</h4>
      <div className="form-group p-3">
        <ButtonGem disabled={isLoading} >{isLoading ? <Spinner text="Loading..." /> : 'Import'}</ButtonGem>
        <p className="">Warning! This action cannot be undone!</p>
      </div>
      <PreviewTable data={processedExcelData} />
    </form>
  );
}

function PreviewTable(props){
  if (!props.data.length) {
    return <div></div>;
  }

  let labelRow = props.data[0];
  // Copy the array and remove the first row.
  let dataRows = props.data.slice(1);
  return (
    <div className="mt-4">
      <h5>Preview</h5>
      <table className="table table-hover bkgd-white">
        <thead className="thead-dark">
        <tr>
          <th scope="col">#</th>
          {labelRow.map( (item, index) => (
            <th key={index} scope="col">
              {item}
            </th>
          ))}
        </tr>
        </thead>
        <tbody>
        {dataRows.map( (cols, index) => (
          <tr key={index}>
            <th scope="row">{index + 1}</th>
            {cols.map( (col, index2) => (
              <td key={index2}>{col}</td>
            ))}
          </tr>
        ))}
        </tbody>
      </table>
    </div>
  );
}

function LocationConsole() {
  const location = useLocation();
  console.log( '%clocation.pathname : ' + location.pathname, 'color: green');
  return (<> </>);
}

ReactDOM.render(<ImportUser/>, document.getElementById('react-container'));
