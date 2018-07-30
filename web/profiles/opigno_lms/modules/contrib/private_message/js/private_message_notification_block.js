/*global jQuery, Drupal, drupalSettings, window*/
/*jslint white:true, multivar, this, browser:true*/

(function($, Drupal, drupalSettings, window)
{
  "use strict";

  var initialized, notificationWrapper, refreshRate, checkingCount;

  function triggerCommands(data) {
    var ajaxObject = Drupal.ajax({
      url: "",
      base: false,
      element: false,
      progress: false
    });

    // Trigger any any ajax commands in the response
    ajaxObject.success(data, "success");
  }

  function updateCount(unreadThreadCount) {
    if(unreadThreadCount) {
      notificationWrapper.addClass("unread-threads");
    }
    else {
      notificationWrapper.removeClass("unread-threads");
    }

    notificationWrapper.find(".private-message-page-link").text(unreadThreadCount);
  }

  function checkCount() {
    if(!checkingCount) {
      checkingCount = true;

      $.ajax({
        url:drupalSettings.privateMessageNotificationBlock.newMessageCountCallback,
        success:function(data) {
          triggerCommands(data);

          checkingCount = false;
          window.setTimeout(checkCount, refreshRate);
        }
      });
    }
  }
 
  function init() {
    if(!initialized) {
      initialized = true;

      if(drupalSettings.privateMessageNotificationBlock.ajaxRefreshRate) {
        notificationWrapper = $(".private-message-notification-wrapper");
        refreshRate = drupalSettings.privateMessageNotificationBlock.ajaxRefreshRate * 1000;
        window.setTimeout(checkCount, refreshRate);
      }
    }
  }
 
  Drupal.behaviors.privateMessageNotificationBlock = {
    attach:function() {

      init();

      Drupal.AjaxCommands.prototype.privateMessageUpdateUnreadThreadCount = function(ajax, response) {
        // stifles jSlint warning.
        ajax = ajax;

        updateCount(response.unreadThreadCount);
      };
    }
  };
}(jQuery, Drupal, drupalSettings, window));
