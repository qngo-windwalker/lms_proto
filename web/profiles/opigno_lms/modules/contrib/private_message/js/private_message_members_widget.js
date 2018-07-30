/*global jQuery, Drupal, drupalSettings, window*/
/*jslint white:true, multivar, this, browser:true*/

(function($, Drupal, drupalSettings, window)
{
  "use strict";

  // Initialize script globals
  var initialized, usernameInput, container, inputWrapper, autocompleteUsernames, autocompleteResultsContainer, insertedUsernames, usernameInputTimeout, maxMembers;

  /**
   * Helper function to trigger ajax commands upon a successful
   * $.ajax request
   */
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

  /**
   * Usernames are entered into the default Drupal entity reference
   * widget which is hidden in the background. In order to enter
   * usernames, new fields must be added for each username.
   * This function enters the username into an empty field once
   * one is available. If none are available, it keeps calling itself
   * until one becomes avialable.
   *
   * @param int inputCount
   *   The number of inputs that existed when this function was
   *   first triggered.
   * @param string username
   *   The username to insert into an empty field when one
   *   becomes available
   */
  function insertUsernameOnComplete(inputCount, username) {
    // Get the possible inputs
    var inputs = inputWrapper.find("input[type='text']");
    // Check if more inputs exist than were originally there. If so
    // a new field exists
    if (inputs.length > inputCount) {
      // Check if the value of the last field is empty
      if (inputs.last().val() === "") {
        // Enter the username into the field
        inputs.last().val(username);
      }
      // The last field isn't empty, so the count is increased by one
      // and the check is re-initiated
      else {
        window.setTimeout(function() {
          insertUsernameOnComplete(inputCount + 1, username);
        }, 50);
      }
    }
    // The number of inputs has not changed, so another check
    // is re-initiated after a delay
    else {
      window.setTimeout(function() {
        insertUsernameOnComplete(inputCount, username);
      }, 50);
    }
  }

  /**
   * Add a given username to the members
   *
   * @param string username
   *   The username to add to the list of members
   * @param bool validateName
   *   Whether or not the name should be validated
   *   from the server
   */
  function addUserToMembers(username, validateName) {
    var found, trimmedVal;

    username = $.trim(username);
    // Only do something if the username isn't empty, and has
    // not already been inserted into the members list
    if(username.length && !insertedUsernames[username]) {
      // Store a reference to the username so that it cannot be
      // entered into the list again
      insertedUsernames[username] = true;

      // Insert a textual representation of the username for users to see
      // and click to remove if necessary
      Drupal.theme("usernameDisplayItem", username).insertBefore(usernameInput);
      Drupal.attachBehaviors(usernameInput.parent()[0]);

      // Attempt to insert the username into an empty field in the hidden entity
      // reference widget.
      inputWrapper.find("input[type='text']").each(function() {
        trimmedVal = $.trim($(this).val());
        if (trimmedVal === "") {
          $(this).val(username);
          found = true;
        }
        else if (trimmedVal === username) {
          found = true;
        }

        if (found) {
          return false;
        }
      });

      // The username was not able to be inserted, so the 'add element' link is clicked
      // and the username is set to be entered upon ajax complete
      if (!found) {
        insertUsernameOnComplete(inputWrapper.find("input[type='text']").length, username);
        $("#private-message-add-form .form-submit").mousedown();
      }

      // If the maximum number of users allowed has been reached, the input is hidden
      // so as to prevent more users from being entered
      if(maxMembers && Object.keys(insertedUsernames).length >= maxMembers) {
        usernameInput.parents("form:first").find(".field--name-message:first textarea:first").focus();
        usernameInput.detach();
      }

      // If the entered username needs to be validated, it happens here
      if (validateName) {
        $.ajax({
          url:drupalSettings.privateMessageMembersWidget.validateUsernameUrl,
          data:{username:username},
          success:function(data) {
            triggerCommands(data);
          }
        });
      }
    }
  }

  /**
   * If a username is not valid (doesn't exist, or is not allowed ot use the private
   * message system), the text display showing the username has a class applied
   * which can be used with CSS to give a visual representation of a bad username.
   *
   * @param string username
   *   The username to be marked as invalid
   */
  function markUsernameInvalid(username) {
    container.find(".private-message-member-display-item").each(function() {
      if($(this).attr("data-username") === username) {
        $(this).addClass("invalid-username");
      }
    });
  }

  /**
   * Removes the autocomplete popup from the DOM
   */
  function hideAutocompleteResults() {
    if (autocompleteResultsContainer) {
      Drupal.detachBehaviors(autocompleteResultsContainer[0]);
      autocompleteResultsContainer.remove();
      autocompleteResultsContainer = null;
    }
  }

  /**
   * Inserts the autocomplete results into the DOM
   *
   * @param string string
   *   The string for which autocomplete results are to be shown.
   */
  function showAutocompleteResults(string) {
    // Remove the autocomplete results if they happen to have been
    // left in the DOM previously
    hideAutocompleteResults();

    // Any results should be stored in autocompleteUsernames, so results are only
    // shown if any results were found for the searched string
    if (autocompleteUsernames && autocompleteUsernames[string] && autocompleteUsernames[string].length) {
      var position, list, i;

      // The position of the input in the DOM. The results are inserted into the DOM relative
      // to this input
      position = usernameInput.position();
      // Create the results container
      autocompleteResultsContainer =  $("<div/>", {id:"pm-members-autocomplete-results-wrapper"}).css({top:(position.top + usernameInput.outerHeight() + 2) + "px", left:position.left + "px", width:usernameInput.outerWidth() + "px"});

      // Create the list that will hold the results
      list = $("<ul/>", {class:"ui-front ui-menu ui-widget ui-widget-content"});

      // Create each of the list elements
      i = 1;
      $.each(autocompleteUsernames[string], function(uid) {
        var username = autocompleteUsernames[string][uid].username;
        if (!insertedUsernames[username]) {
          $("<li/>", {class:"ui-menu-item"}).append($("<a/>", {class:"pm-autocomplete-search-result", "data-username":username, tabindex:i}).text(username)).appendTo(list);
          i += 1;
        }
      });

      // Ensure that we actually have any results before appending the results to the DOM
      if (list.children("li:first").length) {
        if($.contains(document.documentElement, usernameInput[0])) {
          autocompleteResultsContainer.append(list).appendTo(container);
          Drupal.attachBehaviors(autocompleteResultsContainer[0]);
        }
      }
    }
  }

  /**
   * Watch the members field, inserting usernames as necessary
   */
  function membersFieldListener() {
    usernameInput.once("members-field-listener").each(function() {

      // Act on keydown
      $(this).keydown(function(e) {
        var keyCode;

        keyCode = e.keyCode || e.which;

        // Tab key
        // If a value has been entered, add it to the list of members
        // otherwise, allow the default tab action of moving focus
        // to the next field
        if (keyCode === 9) {
          if (usernameInput.val().length) {
            e.preventDefault();
            addUserToMembers(usernameInput.val(), true);
            usernameInput.val("");
            hideAutocompleteResults();
          }
        }
        // Down key pressed
        // Move the focus into the autocomplete results
        else if (keyCode === 40) {
          if (autocompleteResultsContainer) {
            e.preventDefault();
            autocompleteResultsContainer.find("a:first").focus();
          }
        }
        // Backspace key
        // If there is no value in the field, the last username entered
        // is deleted
        else if (keyCode === 8 && !$(this).val().length) {
          $(this).parent().children(".private-message-member-display-item:last").children(".pm-username-remove-link:last").click();
        }
      })
      // act on keyup
      .keyup(function(e) {
        var keyCode;

        keyCode = e.keyCode || e.which;

        // Alphabet key or backspace when there is a value
        // Show the search results
        if ((keyCode >= 65 && keyCode <= 90) || (keyCode === 8 && $(this).val().length)) {
          window.setTimeout(function() {
            var username = usernameInput.val();

            if (username.length) {
              if (!autocompleteUsernames[username]) {
                $.ajax({
                  url:drupalSettings.privateMessageMembersWidget.callbackPath,
                  data:{username:username},
                  success:function(data) {
                    triggerCommands(data);
                  }
                });
              }
              else {
                showAutocompleteResults(username);
              }
            }
            else {
              hideAutocompleteResults(username);
            }
          }, 0);
        }
      })
      // Act on blur, when the user leaves the field
      .blur(function() {
        // A short timeout is allowed before acting. This timeout is canceled
        // if the autocomplete results is focused upon, allowing the user
        // to use the down key to navigate from the text input into the
        // autocomplete results. If the autocomplete results is not
        // focused upon, then any entered text is added to the list of users
        usernameInputTimeout = window.setTimeout(function() {
          if(usernameInput.val().length) {
            addUserToMembers(usernameInput.val(), true);
            usernameInput.val("");
          }
          hideAutocompleteResults();
        }, 20);
      });
    });
  }

  /**
   * Click handler to add users to the list of members
   * from the autocomplete search results
   */
  function addUserToMembersClickHandler(e) {
    e.preventDefault();
    e.stopPropagation();

    addUserToMembers($(this).attr("data-username"));
    hideAutocompleteResults();
    usernameInput.val("").focus();
  }

  /**
   * Keydown handler to navigate through autocomplete
   * search results
   */
  function autompleteResultsKeydownNavigationHandler(e) {
    e.preventDefault();

    var keyCode;

    keyCode = e.keyCode || e.which;

    // Down key
    // Move to the next result if one exists
    if (keyCode === 40) {
      if ($(this).parent().next("li").length) {
        $(this).parent().next("li").children(":first").focus();
      }
      else {
        e.preventDefault();
      }
    }
    // Up key
    // Move to the previous result if one exists, and if not
    // then move back into the usernam input
    else if (keyCode === 38) {
      if ($(this).parent().prev("li").length) {
        $(this).parent().prev("li").children(":first").focus();
      }
      else {
        usernameInput.focus();
      }
    }
    // Tab key or Enter key
    // Add the selectedd username to the list of members
    else if (keyCode === 9 || keyCode === 13) {
      e.preventDefault();
      e.stopPropagation();

      addUserToMembers($(this).attr("data-username"));
      hideAutocompleteResults();
      usernameInput.val("").focus();
    }
  }

  /**
   * Focus handler to stop autocmplete search results
   * from being hidden when the username input field
   * is navigated away from
   */
  function autocompleteResultsFocusHandler() {
    window.clearTimeout(usernameInputTimeout);
  }

  /**
   * Listens to the autocomplete results, addding
   * names to the members lists and/or navigating
   * through the results
   */
  function autocompleteListener() {
    // Only do something if there is a container to work with
    if (autocompleteResultsContainer) {
      // The JS is only applied a single time, by using $.once()
      autocompleteResultsContainer.children("ul:first").children("li").children("a").once("pm-autocomplete-listener").each(function() {

        // If a search result is clicked, the username is added to the list of members
        $(this).click(addUserToMembersClickHandler)
        // When a result is focused upon, the timeout that was added on
        // blur to the username input field is canceled, so that the results
        // don't disappear
        .focus(autocompleteResultsFocusHandler)
        // Act upon keydown
        .keydown(autompleteResultsKeydownNavigationHandler);
      });
    }
  }

  function removeMember(element) {
   var username = element.parent().children(".pm-username:first").attr("data-pm-username");

    // Remove the user from the list of members
    delete insertedUsernames[username];

    // Remove the user from the default widget
    inputWrapper.find("input[type='text']").each(function() {
      if ($(this).val() === username) {
        $(this).val("");
      }
    });

    // Remove this element from the DOM altogether
    Drupal.detachBehaviors(element.parent()[0]);
    element.parent().remove();

    // If the username input has been hidden from the DOM due to the maximum
    // number of members being reached, it is inserted back into the DOM allowing
    // for more users to be added
    if (!$.contains(document.documentElement, usernameInput[0])) {
      usernameInput.appendTo(container);
    }

    usernameInput.focus(); 
  }

  /**
   * When a member has been added to the thread, a block is shown
   * with their username, and  cancel button. This sets the action
   * on the cancel button
   */
  function membersWatcher(context) {
    $(context).find(".private-message-member-display-item .pm-username-remove-link").once("private-message-members-watcher").each(function() {
      $(this).click(function() {
         removeMember($(this));
       });
    });
  }

  // Initialize the script, setting variables and creating elements
  function init() {
    // This should only happen a single time
    if (!initialized) {
      initialized = true;

      var label;

      // Store the username input into which usernames are entered
      usernameInput = $("<input/>", {type:"text", id:"thread-members-input", placeholder:drupalSettings.privateMessageMembersWidget.placeholder}).attr("autocomplete", "off").attr("size", drupalSettings.privateMessageMembersWidget.fieldSize);
      // Store the maximum number of members allowed to be added to the thread
      maxMembers = Number(drupalSettings.privateMessageMembersWidget.maxMembers);
      // The label for the field
      label = $("<label/>", {for:"thread-members-input"}).text("To:");

      // The wrapper for the default entity reference widget, that this widget overrides
      inputWrapper = $(".field--widget-private-message-thread-member-widget:first").hide();

      // Store the container to make for faster searches of the DOM in the script
      container = $("<div/>", {id:"thread-members-display-container"}).append(label).append(usernameInput).insertAfter(inputWrapper);

      // Store a container for any usernames that have been returned with ajax, so they can
      // be reused, rather than having to make repeated requests to the server
      autocompleteUsernames = {};

      // Store a reference to any names that have been entered into the users list
      // to prevent double entries, and to count when determining whether the maximum
      // number of users has been reached
      insertedUsernames = {};

      // When the page is refreshed, or an error is hit, the default widget will contain
      // usernames. We awnt to add these to the members list for a visual reference
      // to users
      inputWrapper.find("input[type='text']").each(function() {
        var trimmedVal = $.trim($(this).val());
        if (trimmedVal.length) {
          trimmedVal = trimmedVal.replace(/\s\(\d+\)$/, "");
          $(this).val(trimmedVal);
          addUserToMembers(trimmedVal, true);
        }
      });

      // Focus on the username input., allowing for users to be added.
      // This is good UX
      usernameInput.focus();
    }
  }

  Drupal.behaviors.privateMessageMembersWidget = {
    attach:function(context) {
      init();
      membersFieldListener();
      autocompleteListener();
      membersWatcher(context);

      // Ajax command callback, to show autocomplete results from the server
      Drupal.AjaxCommands.prototype.privateMessageMembersAutocompleteResponse = function(ajax, response) {
        // stifles jSlint warning.
        ajax = ajax;

        autocompleteUsernames[response.string] = response.userInfo;

        showAutocompleteResults(response.string);
      };

      // Ajax command callback indicating whether or not a username was validated on the server
      Drupal.AjaxCommands.prototype.privateMessageMemberUsernameValidated = function(ajax, response) {
        // stifles jSlint warning.
        ajax = ajax;

        if (!response.validUsername) {
          markUsernameInvalid(response.username);
        }
      };
	},
	detach:function(context) {
	  $(context).find(".private-message-member-display-item .pm-username-remove-link").unbind("click", removeMember);
	  $(context).find(".pm-autocomplete-search-result").unbind("click", addUserToMembersClickHandler).unbind("keydown", autompleteResultsKeydownNavigationHandler).unbind("focus", autocompleteResultsFocusHandler);
	}
  };

  // Theme function to create the visual representation for users showing that a user has been
  // added to the members list
  Drupal.theme.usernameDisplayItem = function (userName) {
    return $("<div />", {class:"private-message-member-display-item", "data-username":userName}).append($("<span/>", {class:"pm-username", "data-pm-username":userName}).text(userName)).append($("<span/>", {class:"pm-username-remove-link"}).text("X"));
  };

}(jQuery, Drupal, drupalSettings, window));
