/**
 * @file
 * JS UI logic for SCORM player.
 *
 * @see js/lib/player.js
 * @see js/lib/api.js
 */

;(function($, Drupal, window, undefined) {

  Drupal.behaviors.opignoScormIOS13 = {
    attach: function(context, settings) {
      function iOSversion() {
        if (/iP(hone|od|ad)/.test(navigator.platform)) {
          // supports iOS 2.0 and later: <http://bit.ly/TJjs1V>
          var v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
          return [parseInt(v[1], 10), parseInt(v[2], 10), parseInt(v[3] || 0, 10)];
        }
      }

      var iOSversion = iOSversion();
      if (typeof iOSversion !== 'undefined' &&
        iOSversion[0] == 13
      ) {
        var $iframe = $('.scorm-ui-player-iframe-wrapper > iframe');
        var defaultIframeHeight = $iframe.height();
        var loop = 0;

        $('.scorm-ui-player-iframe-wrapper')
          .css('height', defaultIframeHeight)
          .css('overflow', 'auto');

        var checkIframe = setInterval(function() {

          var $pageWrap = $iframe
            .contents()
            .find('iframe[name="scormdriver_content"]')
            .contents()
            .find('.page-wrap');

          var $pageOverview = $iframe
            .contents()
            .find('iframe[name="scormdriver_content"]')
            .contents()
            .find('.overview');

          if ($pageWrap.length) {
            loop++;

            var height = ($pageWrap.children('main').length) ? $pageWrap.children('main').outerHeight() : null;

            // Remove min-height: 100vh
            $pageWrap
              .find('.page__wrapper, .quiz__item, .quiz__wrap, .quiz__item--active, .quiz__item-wrap, .quiz-card, .page')
              .css('min-height', 0);

            // Scroll element to top
            if (loop == 1) {
              setTimeout(function() {
                var scrollTop = $pageWrap.scrollTop(0);
                console.log('scroll to top');
              }, 100);
            }

            // Set Iframe height to remove scrollbar
            $iframe
              .height(height)
              .attr('scrolling', 'no');

            // console.log('set iframe height to ' + height + 'px');

            // And remove scrollbar inside scorm
            $pageWrap.css('overflow', 'hidden');
            $pageWrap.find('.quiz-card__container').css('min-height', 0);
          }
          // If quiz home
          else if ($pageOverview.length) {
            $pageOverview
              .css('height', 'auto')
              .css('overflow', 'auto');

            var height = $pageOverview.outerHeight();

            // Set Iframe height to remove scrollbar
            $iframe
              .height(height)
              .attr('scrolling', 'no');

            // console.log('set iframe height to ' + height + 'px');
          }
        }, 500);
      }
    }
  };

})(jQuery, Drupal, window);
