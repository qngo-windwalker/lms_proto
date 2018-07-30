(function ($) {
  $(document).ready(function () {
    if (H5P && H5P.externalDispatcher) {
      H5P.externalDispatcher.on('xAPI', function (event) {
        const $back_btn = $('#opigno-answer-opigno-h5p-form #edit-back');
        // Check that 'back' button is initially disabled.
        const is_back_btn_disabled = $back_btn.prop('disabled');
        if (is_back_btn_disabled) {
          // Enable 'back' button.
          $back_btn.prop('disabled', false);
        }

        const $next_btn = $('#opigno-answer-opigno-h5p-form #edit-submit');
        const statement = event.data.statement;
        if (statement.verb
            && statement.verb.id === 'http://adlnet.gov/expapi/verbs/attempted'
            && statement.context
            && statement.context.contextActivities.category.length) {
          const category = statement.context.contextActivities.category[0].id;
          if (category.indexOf('H5P.CoursePresentation') !== -1) {
            // Disable the 'back' button if user is not on the first page
            // and use it to pilot H5P navigation.
            $back_btn.click(function (event) {
              const $h5p_iframe = $('.h5p-iframe');
              const $iframe = $($h5p_iframe.get(0).contentWindow.document);
              const $current_page = $('.h5p-footer-slide-count-current', $iframe);
              const current_page = parseInt($current_page.text());
              if (current_page !== 1) {
                // Simulate click on the H5P back button.
                const $h5p_back_btn = $('.h5p-footer-previous-slide', $iframe);
                $h5p_back_btn.click();

                // Prevent default click action.
                event.preventDefault();
                return false;
              }
            });

            // Disable the 'next' button if user is not on the last page
            // and use it to pilot H5P navigation.
            $next_btn.click(function (event) {
              const $h5p_iframe = $('.h5p-iframe');
              const $iframe = $($h5p_iframe.get(0).contentWindow.document);
              const $current_page = $('.h5p-footer-slide-count-current', $iframe);
              const current_page = parseInt($current_page.text());
              const $total_pages = $('.h5p-footer-slide-count-max', $iframe);
              const total_pages = parseInt($total_pages.text());
              if (current_page !== total_pages) {
                // Simulate click on the H5P next button.
                const $h5p_next_btn = $('.h5p-footer-next-slide', $iframe);
                $h5p_next_btn.click();

                // Prevent default click action.
                event.preventDefault();
                return false;
              }
            });
          }
          else if (category.indexOf('H5P.QuestionSet') !== -1) {
            // Disable the 'back' button if user is not on the first page
            // and use it to pilot H5P navigation.
            $back_btn.click(function (event) {
              const $h5p_iframe = $('.h5p-iframe');
              const $iframe = $($h5p_iframe.get(0).contentWindow.document);
              const $results_page = $('.questionset-results', $iframe);
              const is_on_results_page = $results_page.length > 0
                  && $results_page.css('display') !== 'none';
              if (is_on_results_page) {
                // Simulate click on the retry button.
                $('.qs-retrybutton', $iframe).click();

                // Prevent default click action.
                event.preventDefault();
                return false;
              }

              const $first_progress_dot = $('.progress-dot', $iframe).first();
              const is_on_first_page = $first_progress_dot.hasClass('current');
              if (!is_on_first_page) {
                // Back button is an anchor link so use native DOM click.
                const $h5p_back_btn = $('.h5p-question-prev', $iframe);
                if ($h5p_back_btn.length) {
                  $h5p_back_btn.get(0).click();
                }

                // Prevent default click action.
                event.preventDefault();
                return false;
              }
            });

            // Disable the 'next' button if user is not on the result page
            // and use it to pilot H5P navigation.
            $next_btn.click(function (event) {
              const $h5p_iframe = $('.h5p-iframe');
              const $iframe = $($h5p_iframe.get(0).contentWindow.document);
              const $results_page = $('.questionset-results', $iframe);
              const is_on_results_page = $results_page.length > 0
                  && $results_page.css('display') !== 'none';
              if (!is_on_results_page) {
                // Next button is an anchor link so use native DOM click.
                const $h5p_next_btn = $('.h5p-question-next', $iframe);
                if ($h5p_next_btn.length) {
                  $h5p_next_btn.get(0).click();
                }

                // Prevent default click action.
                event.preventDefault();
                return false;
              }
            });
          }
        }

        $back_btn.click(function (event) {
          if (is_back_btn_disabled) {
            // If 'back' button was initially disabled.
            event.preventDefault();
            return false;
          }
        });

        var score = event.getScore();
        var maxScore = event.getMaxScore();

        if (score === undefined || score === null) {
          var contentId = event.getVerifiedStatementValue([
            'object',
            'definition',
            'extensions',
            'http://h5p.org/x-api/h5p-local-content-id'
          ]);

          for (var i = 0; i < H5P.instances.length; i++) {
            if (H5P.instances[i].contentId === contentId) {
              if (typeof H5P.instances[i].getScore === 'function') {
                score = H5P.instances[i].getScore();
                maxScore = H5P.instances[i].getMaxScore();
              }
              break;
            }
          }
        }

        if (score !== undefined && score !== null) {
          var key = maxScore > 0 ? score / maxScore : 0;
          key = (key + 32.17) * 1.234;
          $('#activity-h5p-result').val(key);
        }

        // Store correct answer patterns.
        var object = statement.object;

        if (object) {
          $('#activity-h5p-correct-response').val(object.definition.correctResponsesPattern);
        }

        // Store user answer.
        var result = statement.result;

        if (result) {
          $('#activity-h5p-response').val(result.response);
        }
      });
    }
  });
})(jQuery);
