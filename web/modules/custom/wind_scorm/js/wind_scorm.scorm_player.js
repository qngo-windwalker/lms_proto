/**
 * @file
 * JS UI logic for SCORM player.
 *
 * @see js/lib/player.js
 * @see js/lib/api.js
 */

;(function($, Drupal, window, undefined) {

  console.log('wind scorm_player loaded');
  Drupal.behaviors.windScormPlayer = {

    attach: function(context, settings) {
      // Initiate the API.
      if (settings.scormVersion === '1.2') {
        var scormAPIobject = window.API;
      } else {
        var scormAPIobject = window.API_1484_11;
      }

      // Get all SCORM players in our context.
      var $players = $('.scorm-ui-player', context);

      // If any players were found...
      if ($players.length) {
        // Register each player.
        // NOTE: SCORM only allows on SCORM package on the page at any given time.
        // Skip after the first one.
        var first = true;
        $players.each(function() {
          if (!first) {
            return false;
          }

          var element = this,
              $element = $(element),
              alertDataStored = false;

          var eventName = 'commit';
          if (settings.scormVersion === '1.2') {
            eventName = 'commit12';
          }
          // Listen on commit event, and send the data to the server.
          scormAPIobject.bind(eventName, function(value, data, scoId) {
            console.log('commit', data.cmi);
            console.log(value, data);
            $.ajax({
              url: '/scorm-course/scorm/' + $element.data('scorm-id') + '/' + scoId + '/commit',
              data: { data: JSON.stringify(data) },
              async:   false,
              dataType: 'json',
              type: 'post',
              success: function(json) {
                if (alertDataStored) {
                  alert(Drupal.t('We successfully stored your results. You can now proceed further.'));
                }
              }
            });
          });

          // Listen to the unload event. Some users click "Next" or go to a different page, expecting
          // their dashboard page to refresh with updated data.
          $(window).bind('beforeunload', function() {
            window.opener.location.reload(false);
          });

          first = false;
        });
      }
    }
  };

})(jQuery, Drupal, window);
