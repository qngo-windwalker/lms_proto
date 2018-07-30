/*global jQuery, Drupal, drupalSettings, window*/
/*jslint white:true, multivar, this, browser:true*/

(function ($, Drupal, drupalSettings, window) {

  "use strict";

  var initialized, container, updateInterval, loadingPrev, loadingNew;

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

  function updateInbox() {
    if(!loadingNew) {
      loadingNew = true;

      var ids = {};
      container.find(".private-message-thread-inbox").each(function() {
        ids[$(this).attr("data-thread-id")] = $(this).attr("data-last-update");
      });

      $.ajax({
        url:drupalSettings.privateMessageInboxBlock.loadNewUrl,
        method:"POST",
        data:{ids:ids},
        success:function(data) {
          loadingNew = false;
          triggerCommands(data);
          window.setTimeout(updateInbox, updateInterval);
        }
      });
    }
  }

  function reorderInbox(threadIds, newThreads) {
    var map = {};

    container.children(".private-message-thread-inbox").each(function() { 
      var element = $(this);
      map[element.attr("data-thread-id")] = element;
    });

    $.each(threadIds, function(index) {
      var threadId = threadIds[index];

      if (newThreads[threadId]) {
        if (map[threadId]) {
          map[threadId].remove();
        }

        $("<div/>").html(newThreads[threadId]).contents().appendTo(container);
      }
      else if (map[threadId]) {
        container.append(map[threadId]);
      }
    });

    Drupal.attachBehaviors(container[0]);
  }

  function insertPreviousThreads(threads) {
    var contents = $("<div/>").html(threads).contents();

    contents.css("display", "none").appendTo(container).slideDown(300);
    Drupal.attachBehaviors(contents[0]);
  }

  function setActiveThread(threadId) {
    container.find(".active-thread:first").removeClass("active-thread");
    container.find(".private-message-thread[data-thread-id='" + threadId + "']:first").removeClass("unread-thread").addClass("active-thread");
  }

  function loadOldThreadWatcherHandler(e) {
    e.preventDefault();

    if (!loadingPrev) {
      loadingPrev = true;

      var oldestTimestamp;
       container.find(".private-message-thread").each(function() {
        if (!oldestTimestamp || Number($(this).attr("data-last-update")) < oldestTimestamp) {
          oldestTimestamp = Number($(this).attr("data-last-update"));
        }
      });

      $.ajax({
        url:drupalSettings.privateMessageInboxBlock.loadPrevUrl,
        data:{timestamp:oldestTimestamp, count:drupalSettings.privateMessageInboxBlock.threadCount},
        success:function(data) {
          loadingPrev = false;
          triggerCommands(data);
        }
      });
    }
  }

  function loadOlderThreadWatcher(context) {
    $(context).find("#load-previous-threads-button").once("load-loder-threads-watcher").each(function() {
      $(this).click(loadOldThreadWatcherHandler);
    });
  }

  var inboxThreadLinkListenerHandler = function(e) {
    e.preventDefault();

    Drupal.PrivateMessages.loadThread($(this).attr("data-thread-id"));
  };

  function inboxThreadLinkListener(context) {
    $(context).find(".private-message-inbox-thread-link").once("inbox-thread-link-listener").each(function() {
      $(this).click(inboxThreadLinkListenerHandler);
    });
  }

  function init() {
    if (!initialized) {
      initialized = true;
      container = $(".block-private-message-inbox-block .content:first");
      $("<div/>", {id:"load-previous-threads-button-wrapper"}).append($("<a/>", {href:"#", id:"load-previous-threads-button"}).text(Drupal.t("Load Previous"))).insertAfter(container);
      updateInterval = drupalSettings.privateMessageInboxBlock.ajaxRefreshRate * 1000;
      if(updateInterval) {
        window.setTimeout(updateInbox, updateInterval);
      }
    }
  }

  Drupal.behaviors.privateMessageInboxBlock = {
    attach:function(context) {
      init();
      loadOlderThreadWatcher(context);
      inboxThreadLinkListener(context);

      Drupal.AjaxCommands.prototype.insertInboxOldPrivateMessageThreads = function(ajax, response) {
        // stifles jSlint warning.
        ajax = ajax;

        if (!response.threads) {
          $("#load-previous-threads-button").parent().slideUp(300, function() {
            $(this).remove();
          });
        }
        else {
          insertPreviousThreads(response.threads);
        }
      };

      Drupal.AjaxCommands.prototype.privateMessageInboxUpdate = function(ajax, response) {
        // stifles jSlint warning.
        ajax = ajax;

        reorderInbox(response.threadIds, response.newThreads);
      };

      Drupal.AjaxCommands.prototype.privateMessageTriggerInboxUpdate = function() {
        updateInbox();
      };

      Drupal.PrivateMessages.setActiveThread = function(id) {
        setActiveThread(id);
      };
    },
    detatch:function(context) {
      $(context).find("#load-previous-threads-button").unbind("click", loadOldThreadWatcherHandler);
      $(context).find(".private-message-inbox-thread-link").unbind("click", inboxThreadLinkListenerHandler);
    }
  };

}(jQuery, Drupal, drupalSettings, window));
