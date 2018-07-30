/* eslint-disable */

/**
 * @file
 * Drupal Platon object.
 */

/**
 * All Drupal Platon JavaScript APIs are contained in this namespace.
 *
 * @namespace
 */
(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.platon = {
    settings: drupalSettings.platon || {}
  };

  Drupal.platon.settings = {
    sliderSpeedAutoplaySpeed: 5000,
    speed: 1000,
    autoplay: true,
    fade: true,
    arrows: false,
    responsive: true,
    pauseOnHover: false
  };

  Drupal.platon.getViewport = function () {
    return {
      width: Math.max(document.documentElement.clientWidth, window.innerWidth || 0),
      height: Math.max(document.documentElement.clientHeight, window.innerHeight || 0)
    };
  };

  Drupal.platon.frontpageSlider = function () {

    if ($('body.anonymous-slider').length) {
      $('div.anonymous-slider, div.anonymous-slider .slider-item').width(Drupal.platon.getViewport().width);

      $('div.anonymous-slider').slick({
        fade: Drupal.platon.settings.fade,
        autoplay: Drupal.platon.settings.autoplay,
        autoplaySpeed: Drupal.platon.settings.sliderSpeedAutoplaySpeed,
        speed: Drupal.platon.settings.speed,
        arrows: Drupal.platon.settings.arrows,
        responsive: Drupal.platon.settings.responsive,
        pauseOnHover: Drupal.platon.settings.pauseOnHover
      });
    }

    $(window).resize(function () {
      $('div.anonymous-slider, div.anonymous-slider .slider-item').width(Drupal.platon.getViewport().width);
    });
  };

  Drupal.platon.anonymousUserForms = function (context) {
    if (!$('body.anonymous-slider').length) {
      return;
    }

    $('#user-sidebar a', context).once().click(function (e) {
      var href = $(this).attr('href');
      if (
        (href === '/user/login' || href === '/user/password' || href === '/user/register') &&
        $('.form-wrapper[data-target="' + href + '"]').length
      ) {
        e.preventDefault();
        $('.form-wrapper[data-target]').hide();
        $('.form-wrapper[data-target="' + href + '"]').show();
      }
    });
  };

  Drupal.platon.searchBar = function (context) {
    $('.search-trigger a', context).once().click(function (e) {
      e.preventDefault();

      if ($(this).hasClass('open')) {
        $(this).removeClass('open');
        $('#search-form').hide();
      }
      else {
        $(this).addClass('open');
        $('#search-form').show();
        $('#search-form form input[type="search"]').focus();
      }
    });
  };

  Drupal.platon.trainingCatalog = function (context) {
    $('body.page-catalog .views-exposed-form fieldset#edit-sort-by--wrapper legend', context).once().click(function () {
      if ($(this).hasClass('active')) {
        $(this).removeClass('active');
      } else {
        $(this).addClass('active');
      }
    });
  };

  Drupal.platon.privateMessageRecipients = function (context) {
    var pmc = $('.private-message-recipients');

    pmc.each(function(){
      var lh = parseInt($(this).css('line-height'), 10);
      $(this).css('max-height', lh);

      if ($(this).find('.content').height() > lh) {
        $(this).css('max-height', lh).addClass('short').append('<a href="#" class="expander">...</a>');
        $(this).find('.expander').on('click', function() {
          $(this).closest('.private-message-recipients').css('max-height', 'none').removeClass('short');
          $(this).hide();
          return false;
        });
      }
    });
  };

  Drupal.platon.stepsVisibility = function (context) {
    if (!$('div#block-lp-steps-block').length) {
      return;
    }

    var defaultMainClass = $('#content').attr('class');
    $('#sidebar-first', context).hide();
    $('#content', context).addClass('col-lg-12');

    // Add trigger
    $('#main div#edit-actions', context).prepend('<a href="#" id="lp-steps-trigger" class="btn btn-link mr-auto">show</a>');

    // Handle trigger clicks
    $('a#lp-steps-trigger', context).once().click(function(e) {
      e.preventDefault();

      if ($('a#lp-steps-trigger', context).hasClass('open')) {
        $('a#lp-steps-trigger', context).removeClass('open');
        $('#sidebar-first', context).hide();
        $('#content', context).addClass('col-lg-12');
      } else {
        $('a#lp-steps-trigger', context).addClass('open');
        $('#sidebar-first', context).show();
        $('#content', context).attr('class', defaultMainClass);
      }
    });
  };

  Drupal.platon.formatTFTOperations = function (context) {
    var $td = $('div#documents-library table tbody tr > td:last-child', context);

    $td.each(function() {
      if ($(this).hasClass('js-formatted')) {
        return;
      }

      $(this).addClass('js-formatted');

      var html = '<div class="btn-group operations">';
      html += '<button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown"></button>';
      html += '<div class="dropdown-menu dropdown-menu-right">';
      $(this).find('a').each(function() {
        $(this).removeClass('ops-link');
        html += $(this)[0].outerHTML;
      });
      html += '</div>';
      html += '</div>';

      $(this).html(html);
    });
  };

  Drupal.platon.mobileMenu = function (context) {
    var $toggler = $('button.navbar-toggler', context);
    var $nav = $('div#menu-wrapper', context);

    $toggler.once().click(function() {
      if ($toggler.hasClass('open')) {
        $toggler.removeClass('open');
        $nav.removeClass('show');
      } else {
        $toggler.addClass('open');
        $nav.addClass('show');
      }
    });
  };

  Drupal.platon.fileWidget = function(context) {
    $('.opigno-file-widget-wrapper', context).each(function () {
      if ($(this).find('input[type="hidden"] + span.file').length) {
        $(this).addClass('not-empty');
      }
      else {
        $(this).removeClass('not-empty');
      }
    });
  };

  Drupal.behaviors.platon = {
    attach: function (context, settings) {
      Drupal.platon.frontpageSlider();
      Drupal.platon.anonymousUserForms(context);
      Drupal.platon.searchBar(context);
      Drupal.platon.trainingCatalog(context);
      Drupal.platon.privateMessageRecipients(context);
      Drupal.platon.stepsVisibility(context);
      Drupal.platon.mobileMenu(context);
      Drupal.platon.fileWidget(context);

      $('a[href="#documents-library"]', context).once().click(function() {
        Drupal.platon.formatTFTOperations(context);
      });

      $(document).ajaxSuccess(function() {
        Drupal.platon.formatTFTOperations(context);
      });
    }
  };
})(window.jQuery, window.Drupal, window.drupalSettings);
