/**
 * Usage: Add <script src="/modules/custom/wind_tincan/js/wind_tincan.customXAPI.js"></script> to index_lms.html in your Tincan zip file.
 * This file is to provide functions to be call in Storyline.
 * Example for creating a button in Storyline and call the JS function: https://www.youtube.com/watch?v=1IGHdExIGis&ab_channel=TLWorkspace
 * This file needs to be before user.js in index_lms.html
 * Tip: run on the console: TinCan.enableDebug();
 * @requires scormdriver.js
 *  TinCan class is in scormdriver.js
 */
let CustomXAPI = {};

CustomXAPI.getQueryVariable = function(variable) {
  var query = window.location.search.substring(1);
  var vars = query.split('&');
  for (var i=0;i<vars.length;i++) {
    var pair = vars[i].split('=');
    if (pair[0] == variable) {
      return pair[1];
    }
  }
  return false;
}

CustomXAPI.parseURL = function(t) {
  var e, n, i, r, o = /\+/g, s = /([^&=]+)=?([^&]*)/g, a = function(t) {
    return decodeURIComponent(t.replace(o, " "))
  };
  if (e = new RegExp(["^(https?:)//", "(([^:/?#]*)(?::([0-9]+))?)", "(/[^?#]*)", "(\\?[^#]*|)", "(#.*|)$"].join("")),
    (i = {
      protocol: (n = t.match(e))[1],
      host: n[2],
      hostname: n[3],
      port: n[4],
      pathname: n[5],
      search: n[6],
      hash: n[7],
      params: {}
    }).path = i.protocol + "//" + i.host + i.pathname,
  "" !== i.search)
    for (; r = s.exec(i.search.substring(1)); )
      i.params[a(r[1])] = a(r[2]);
  return i
}
/**
 * @source lib/scrips/slide.min.js
 * @returns {*}
 */
CustomXAPI.parseParams = function() {
  if (null != window.globals.parsedParams)
    return window.globals.parsedParams;
  for (var t, e = window.location.search.substr(1).split("+").join(" "), i = {}, n = /[?&]?([^=]+)=([^&]*)/g; t = n.exec(e); )
    i[decodeURIComponent(t[1]).trim()] = decodeURIComponent(t[2]).trim();
  return window.globals.parsedParams = i,
    i
}

/**
 * @source https://www.dropbox.com/s/xb0t0uso3ejhf37/xAPI.js?dl=0&utm_campaign=elearningindustry.com&utm_source=%2Fcustom-xapi-statements-for-storyline-3-short-videos&utm_medium=link
 * @returns {string}
 */
CustomXAPI.generateUUID = function() {
  var d = new Date().getTime();
  if (window.performance && typeof window.performance.now === 'function') {
    d += performance.now(); // Use high-precision timer if available
  }
  var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    var r = (d + Math.random()*16)%16 | 0;
    d = Math.floor(d / 16);
    return (c=='x' ? r : (r&0x3|0x8)).toString(16);
  });
  return uuid;
}

/**
 * Ensure the statement structure like this:
 *  {
     id : "http://adlnet.gov/expapi/verbs/commented",
     display : {
       "en-US": "commented"
     }
  }
 * @param string t
 * @returns {*}
 */
CustomXAPI.getVerb = function(t){
  let verb = new TinCan.Verb(t);
  verb.display["en-US"] = verb.display.und;
  delete verb.display.und;
  if (verb.id == 'commented') {
    verb.id = 'http://adlnet.gov/expapi/verbs/' + t;
  }
  return verb;
}
CustomXAPI.getContext = function(activityId){
  return {
    "contextActivities": {
      "parent": [
        {
          "id": activityId,
          "objectType": "Activity"
        }
      ],
      "grouping": [
        {
          "id": activityId,
          "objectType": "Activity"
        }
      ]
    }
  }
}

CustomXAPI.constructStatement = function(statement){
  var actor = decodeURI(CustomXAPI.getQueryVariable("actor"));
  let parsedParams = CustomXAPI.parseParams();
  let activityId = decodeURIComponent(CustomXAPI.getQueryVariable("activity_id"));

  statement.id = TinCan.Utils.getUUID();
  statement.timestamp = TinCan.Utils.getISODateString(new Date);
  statement.verb = this.getVerb('commented');
  statement.context = this.getContext(activityId);
  if(statement.hasOwnProperty('object')){
    if (!statement.object.hasOwnProperty('id')) {
      statement.object.id = activityId;
    }
  }
  return statement;
}

CustomXAPI.sendStatementCallback = function(t, e){
  console.log(t, e);
}

/**
 * Public function
 * Example:
 CustomXAPI.sendStatement({
      'verb' : 'commented',
      'result' : {
        'response' : GetPlayer().GetVar('ReflectResponse_01'),
      },
      'object' : {
          "definition": {
            "type": "http://adlnet.gov/expapi/activities/course",
            "name": {
              "en-US": "Reflection_question_01"
            },
            "description": {
              "en-US": "Think about three other examples of barriers to access or opportunity a person or employee might experience. Then, write several sentences about how you or your company could help them overcome those barriers. Try to come up with ones that are realistic or that you have witnessed/experienced. "
            }
          }
        }
    });

 * @param statement
 */
CustomXAPI.sendStatement = function(statement){

  let newStatement = CustomXAPI.constructStatement(statement);
  var randomString = CustomXAPI.generateUUID();
  let parsedURL = TinCan.Utils.parseURL(window.location.href);
  let endpoint = this.parseParams().endpoint;

  let tincan = new TinCan({
    url: window.location.toString().replace("&tincan=true", "")
  });
  let result = tincan.sendStatement(newStatement, this.sendStatementCallback.bind(this));
  console.log(result);
};
