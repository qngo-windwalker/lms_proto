;(function(root) {
  var tincan;
  var statementQueue = [];

  var state = {
    completed: false,
    prevDuration: null,
    score: null,
    startTime: null
  }

  var BOOKMARK = 'bookmark';
  var SUSPEND_DATA = 'suspend_data';
  var TOTAL_TIME = 'cumulative_time';

  var getUUID = root.TinCan.Utils.getUUID;
  var formatTime = root.TinCan.Utils.convertMillisecondsToISO8601Duration;
  var noop = Function.prototype;

  function isFile() {
    return /file/.test(root.location.protocol)
  }

  function assign(target) {
    if (target === undefined || target === null) {
      throw new TypeError('assign: Cannot convert undefined or null to object');
    }

    var output = Object(target);

    var source;
    var nextKey;

    for (var index = 1; index < arguments.length; index++) {
      source = arguments[index];

      if (source !== undefined && source !== null) {
        for (nextKey in source) {
          if (source.hasOwnProperty(nextKey)) {
            output[nextKey] = source[nextKey];
          }
        }
      }
    }

    return output;
  }

  function debounce(fn, delay) {
    var timer = null;

    return function () {
      var context = this, args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () {
        fn.apply(context, args);
      }, delay);
    };
  }

  function closeContent() {
    if(root.top === root) {
      root.close();
    }
    else {
      root.location.pathname =
        root.location.pathname
          .split('/')
          .slice(0, -1)
          .join('/')
          .concat('/goodbye.html');
    }
  }

  function queueStatement(stmt) {
    if(statementQueue.length) {
      statementQueue.unshift(stmt);
    } else {
      sendStatement(stmt);
    }
  }

  function popStatement(stmt) {
    if(statementQueue.length) {
      sendStatement(statementQueue.pop());
    }
  }

  function sendStatement(stmt) {
    tincan.sendStatement(wrapStatement(stmt), popStatement);
  }

  function wrapStatement(stmt) {
    return assign({}, {
      id: getUUID(),
      type: 'http://adlnet.gov/expapi/activities/course',
      object: { id: tincan.activity.id }
    }, stmt)
  }

  function loadBookmark() {
    var hash = GetBookmark();

    if(!hash) {
      return;
    }

    if(root.history.pushState) {
      root.history.pushState(null, null, hash);
    } else {
      root.location.hash = hash;
    }
  }

  function sendAttempted() {
    commitData({
      verb: 'attempted'
    });
  }

  function sendExperienced(completion) {
    commitData({
      verb: 'experienced',
      result: { completion: completion }
    });
  }

  function addFinishData(duration, stmt) {
    const result = assign({}, stmt.result);

    result.duration = formatTime(duration);

    if(state.score !== null) {
      result.score = state.score;
    }

    return assign({}, stmt, { result: result });
  }

  function getDuration() {
    if(state.prevDuration === null) {
      state.prevDuration = Number(getState(TOTAL_TIME)) || 0
    }

    state.prevDuration = state.prevDuration + ((new Date()).getTime() - state.startTime)

    state.startTime = (new Date()).getTime();

    return state.prevDuration;
  }

  function commitData(stmt) {
    var duration = getDuration();

    queueStatement(addFinishData(duration, stmt));

    // run last as we need to have a sync call for beforeunload
    // called with no cfg.callback
    setState(TOTAL_TIME, duration);
  }

  function getState(key, cfg) {
    try {
      var state =
        tincan.getState(key, cfg || {}).state;

      return state && state.contents
        ? state.contents
        : '';
    }
    catch(ex) {
      return '';
    }
  }

  function setState(key, data, cfg) {
    try {
      tincan.setState(key, data, cfg || {});
    }
    catch(ex) {
      return;
    }
  }

  function SetBookmark(data) {
    setState(BOOKMARK, data, { callback: noop });
  }

  function GetBookmark() {
    return getState(BOOKMARK);
  }

  function GetTime() {
    return getState(TOTAL_TIME);
  }

  function SetTime() {
    return setState(TOTAL_TIME);
  }

  function SetDataChunk(data) {
    setState(SUSPEND_DATA, data, { callback: noop });
  }

  function GetDataChunk() {
    return getState(SUSPEND_DATA);
  }

  function SetScore(newScore, max, min) {
    state.score = {
      raw: newScore,
      max: max,
      min: min
    };
  }

  function InitCompletion(isComplete) {
    state.completed = isComplete;
  }

  function SetFailed() {
    state.completed = true;

    commitData({
      verb: 'failed',
      result: {
        completion: state.completed,
        success: false
      }
    });
  }

  function SetPassed() {
    state.completed = true;

    commitData({
      verb: 'passed',
      result: {
        completion: state.completed,
        success: true
      }
    });
  }

  // Record Answer Data
  function buildDescriptionObj(id, title) {
    return {
      id: id,
      description: {
        und: title
      }
    }
  }

  function prop(property) {
    return function (obj) {
      return obj[property];
    }
  }

  function mapMatchingArray(match) {
    return match.source.id + '[.]' + match.target.id;
  }

  function joinArray(a) {
    return a.join('[,]');
  }

  function getSources(answers) {
    var mapAnswers = function(answer) {
      return buildDescriptionObj(answer.source.id, answer.source.title);
    };

    return answers.map(mapAnswers);
  }

  function getTargets(answers) {
    var mapAnswers = function(answer) {
      return buildDescriptionObj(answer.target.id, answer.target.title);
    };

    return answers.map(mapAnswers);
  }

  function getChoices(answers) {
    var mapAnswers = function(answer) {
      return buildDescriptionObj(answer.id, answer.title);
    };

    return answers.map(mapAnswers);
  }

  function getCorrectResponsesPattern(data) {
    var correctResponse = data.correctResponse;

    switch (data.type) {
      case 'FILL_IN_THE_BLANK':
        return correctResponse;
      case 'MATCHING':
        return [ joinArray(correctResponse.map(mapMatchingArray)) ];
      case 'MULTIPLE_CHOICE':
      case 'MULTIPLE_RESPONSE':
      default:
        return [ joinArray(correctResponse.map(prop('id'))) ];
    }
  }

  function buildDefinition(data) {
    var answers = data.answers;
    var correctResponse = data.correctResponse;
    var title = { und: data.questionTitle };
    var type = data.type;

    var interactionTypes = {
      FILL_IN_THE_BLANK: 'fill-in',
      MATCHING: 'matching',
      MULTIPLE_CHOICE: 'choice',
      MULTIPLE_RESPONSE: 'choice'
    };

    var definition = {
      name: title,
      description: title,
      type: 'http://adlnet.gov/expapi/activities/cmi.interaction',
      interactionType: interactionTypes[type || 'MULTIPLE_CHOICE'],
      correctResponsesPattern: getCorrectResponsesPattern(data)
    };

    if (type === 'MULTIPLE_CHOICE' || type === 'MULTIPLE_RESPONSE') {
      definition.choices = getChoices(answers);
    }

    if (type === 'MATCHING') {
      definition.source = getSources(answers);
      definition.target = getTargets(answers);
    }

    return definition;
  }

  function buildResponse(data) {
    var response = data.response;

    switch (data.type) {
      case 'FILL_IN_THE_BLANK':
        return response;
      case 'MATCHING':
        return joinArray(response.map(mapMatchingArray));
      case 'MULTIPLE_CHOICE':
      case 'MULTIPLE_RESPONSE':
      default:
        return joinArray(response.map(prop('id')));
    }
  }

  function ReportAnswer(data) {
    var definition = buildDefinition(data);
    var response = buildResponse(data);
    var title = data.questionTitle;

    commitData({
      type: 'http://adlnet.gov/expapi/activities/cmi.interaction',
      verb: 'answered',
      description: title,
      name: title,
      object: {
        id: tincan.activity.id + '/' + data.itemId + '_' + Date.now(),
        definition: definition
      },
      result: {
        success: data.isCorrect,
        response: response
      }
    });
  }

  function ConcedeControl() {
    sendExperienced(state.completed);
    root.removeEventListener('beforeunload', ConcedeControl);
    closeContent();
  }

  function lms() {
    root.removeEventListener('beforeunload', ConcedeControl);
    root.addEventListener('beforeunload', ConcedeControl);

    var url =
      root.location.href.replace(root.location.hash, '');

    var config = {
      activity: {
        id: TC_COURSE_ID,
        definition: {
          name: TC_COURSE_NAME,
          description: TC_COURSE_DESC
        }
      }
    }

    tincan = new TinCan(isFile()
      ? config
      : assign({}, config, { url: url })
    );

    state.startTime = (new Date()).getTime();

    loadBookmark();
    sendAttempted();

    return {
      SetBookmark: SetBookmark,
      SetDataChunk: SetDataChunk,
      GetDataChunk: GetDataChunk,
      InitCompletion: InitCompletion,
      ReportAnswer: ReportAnswer,
      SetFailed: SetFailed,
      SetPassed: SetPassed,
      SetScore: SetScore,
      ConcedeControl: ConcedeControl,
      utils: {
        assign: assign,
        debounce: debounce
      }
    };
  }

  root.lms = lms;

}(window));
