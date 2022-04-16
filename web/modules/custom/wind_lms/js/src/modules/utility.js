export default {

  /**
   * @see https://stackoverflow.com/a/847196
   * @param unix_timestamp
   * @returns {}
   */
  unixTimestampToTime(unix_timestamp){
    let a = new Date(unix_timestamp * 1000);
    let months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    let year = a.getFullYear();
    let month = months[a.getMonth()];
    let date = a.getDate();
    let hour = a.getHours();
    let min = a.getMinutes();
    let sec = a.getSeconds();
    let time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
    let ddmmyy = date + ' ' + month + ' ' + year ;
    return {date : ddmmyy, time:  hour + ':' + min + ':' + sec };
  },

  coursePopup(href) {
    let day = new Date();
    let id = day.getTime();
    let screenHeight = screen.height >= 768 ? 985 : screen.height;
    let params = ['toolbar=no', 'scrollbars=no', 'location=no', 'statusbar=no', 'menubar=no', 'directories=no', 'titlebar=no', 'toolbar=no', 'resizable=1', 'height=' + screenHeight, 'width=1254'
      //            'fullscreen=yes' // only works in IE, but here for completeness
    ].join(',');

    let popupWin = window.open(href, "window" + id, params);
    // Reload the page page when user closes the course so the parent page can show the latest course progress.
    let timer = setInterval(function() {
      if(popupWin.closed) {
        clearInterval(timer);
        location.reload();
      }
    }, 700);
  },

  docCookie : {
    setCookie : function(name,value,days) {
      console.log('delete cookie');
      var expires = "";
      if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
      }
      document.cookie = name + "=" + (value || "")  + expires + "; path=/";
    },

    getCookie : function(name) {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
      }
      return null;
    }
  },

  isEnglishMode() {
    let pathname = window.location.pathname;
    // if we are on 'es' spanish mode
    if(pathname.split('/')[1] == 'es'){
      return false;
    }
    return true;
  },

  /**
   *  Get all users that has tid value in user.field_team.
   *  Note: This includes users with status of ACTIVE or status of BLOCKED.
   * @param tid
   * @param users
   * @returns {*[]}
   */
  getAllUsersInTeam(tid, users) {
    let collection = [];
    for (const userData of users){
      const userField_team = userData.user.field_team;
      if (!userField_team.length) {
        continue;
      }

      for (const team of userField_team) {
        if (team.tid == tid) {
          collection.push(userData);
        }
      }
    }
    return collection;
  },

  getAllActiveUsersInTeam(tid, users) {
    let teamUsers = this.getAllUsersInTeam(tid, users);

    let collection = [];
    for (const userData of teamUsers){
      if (userData.user.status == '1') {
        collection.push(userData);
      }
    }
    return collection;
  },

  getUserOverallCourseProgress(courses) {
    let completed = 0;
    if (courses.length) {
      for (const course of courses){
        // Check by completion
        if (course.isCompleted == true) {
          completed++;
          continue;
        }

        // Check by certificate upload.
        if (course.certificateNode && course.certificateNode.field_completion_verified == '1') {
          completed++;
        }
      }
    }

    return {
      totalCompleted: completed,
      totalNumberCourse: courses.length,
      // To avoid NaN b/c of stupid Javascript zero divided by zero equal NaN : 0 / 0 equal NaN
      completePercentage : courses.length ? (completed / courses.length) : 0
    }
  }
}
