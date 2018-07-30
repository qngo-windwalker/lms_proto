<?php

/**
 * @file
 * Lists available colors and color schemes for the Bartik theme.
 */

$info = [
  // Available colors and color labels used in theme.
  'fields' => [
    'white' => t('White'),
    'very_light_gray' => t('Very light gray'),
    'light_gray' => t('Light gray'),
    'medium_gray' => t('Medium gray'),
    'dark_gray' => t('Dark gray'),
    'light_blue' => t('Light blue'),
    'dark_blue' => t('Dark blue'),
    'deep_blue' => t('Deep blue'),
    'leaf_green' => t('Leaf green'),
    'blood_red' => t('Blood red'),
  ],

  // Pre-defined color schemes.
  'schemes' => [
    'default' => [
      'title' => t('Platon (default)'),
      'colors' => [
        'white' => '#fcfcfc',
        'very_light_gray' => '#f2f2f0',
        'light_gray' => '#f8f7f5',
        'medium_gray' => '#ececeb',
        'dark_gray' => '#58585a',
        'light_blue' => '#88c1e6',
        'dark_blue' => '#009ee0',
        'deep_blue' => '#0a71b4',
        'leaf_green' => '#5aa449',
        'blood_red' => '#ea645b',
        'base' => '#ffffff',
        'link' => '#009ee0',
      ],
    ],
    'aristoteles' => [
      'title' => t('Aristoteles'),
      'colors' => [
        'white' => '#fcfcfc',
        'very_light_gray' => '#f2f2f0',
        'light_gray' => '#ffffff',
        'medium_gray' => '#e8e8e8',
        'dark_gray' => '#58585a',
        'light_blue' => '#3e3e3e',
        'dark_blue' => '#000000',
        'deep_blue' => '#040404',
        'leaf_green' => '#0f1a2c',
        'blood_red' => '#e2a238',
        'base' => '#ffffff',
        'link' => '#18681d',
      ],
    ],
  ],

  // CSS files (excluding @import) to rewrite with new color scheme.
  'css' => [
    '../dist/styles/main.css',
  ],

  // Files to copy.
  'copy' => [],

  // Gradient definitions.
  'gradients' => [],

  // Color areas to fill (x, y, width, height).
  'fill' => [
    'dark_blue' => [0, 0, 500, 318],
    'deep_blue' => [0, 318, 3000, 134],
  ],

  // Coordinates of all the theme slices (x, y, width, height)
  // with their filename as used in the stylesheet.
  'slices' => [
    'img/all.png'                                            => [0, 0, 500, 500],
    'img/main-navigation-training-catalogue-icon-active.png' => [120, 0, 40, 40],
    'img/main-navigation-my-courses-icon-active.png'         => [40, 40, 40, 40],
    'img/main-navigation-my-achievements-icon-active.png'    => [80, 40, 40, 40],
    'img/main-navigation-messages-icon-active.png'           => [40, 0, 40, 40],
    'img/main-navigation-home-icon-active.png'               => [40, 80, 40, 40],
    'img/main-navigation-forum-icon-active.png'              => [0, 40, 40, 40],
    'img/main-navigation-calendar-icon-active.png'           => [0, 80, 40, 40],
    'img/main-navigation-administration-icon-active.png'     => [0, 0, 40, 40],
    'img/main-navigation-statistics-icon-active.png'         => [120, 40, 40, 40],
    'img/main-navigation-collaborative-workspaces-icon-active.png' => [80, 0, 40, 40],
    'img/collapse-menu-icon.png'                             => [160, 0, 13, 50],
    'img/back-icon.png'                                      => [160, 58, 6, 8],
    'img/header-background.jpg'                              => [0, 318, 3000, 134],
  ],

  // Reference color used for blending.
  'blend_target' => '#ffffff',

  // Preview files.
  'preview_library' => 'platon/color.preview',
  'preview_html' => 'color/preview.html',

  // Base file for image generation.
  'base_image' => 'color/base2.png',
];
