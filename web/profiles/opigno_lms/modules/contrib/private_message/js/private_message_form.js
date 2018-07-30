/*global jQuery, Drupal, drupalSettings, window*/
/*jslint white:true, multivar, this, browser:true*/

(function ($, Drupal) {

  "use strict";

  function submitKeyPress(e) {
    var keyCode;

    keyCode = e.keyCode || e.which;
    if(keyCode === 13) {
      $(this).mousedown();
    }
  }

  function submitButtonListener(context) {
    $(context).find(".private-message-add-form .form-actions .form-submit").once("private-message-form-submit-button-listener").each(function() {
	  $(this).keydown(submitKeyPress);
    });
  }

  Drupal.behaviors.privateMessageForm = {
    attach:function(context) {
      submitButtonListener(context);
    },
    detach:function(context) {
      $(context).find(".private-message-add-form .form-actions .form-submit").unbind("keydown", submitKeyPress);
    }
  };

}(jQuery, Drupal));
