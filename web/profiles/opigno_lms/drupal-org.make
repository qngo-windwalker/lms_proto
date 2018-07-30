api = 2
core = 8.x

; Opigno modules =============================================

projects[opigno_calendar][type] = module
projects[opigno_calendar][subdir] = "opigno"
projects[opigno_calendar][version] = 1.0-beta1

projects[opigno_calendar_event][type] = module
projects[opigno_calendar_event][subdir] = "opigno"
projects[opigno_calendar_event][version] = 1.0-beta1

projects[opigno_catalog][type] = module
projects[opigno_catalog][subdir] = "opigno"
projects[opigno_catalog][version]= 1.0-beta1

projects[opigno_certificate][type] = module
projects[opigno_certificate][subdir] = "opigno"
projects[opigno_certificate][version] = 1.0-beta1

projects[opigno_class][type] = module
projects[opigno_class][subdir] = "opigno"
projects[opigno_class][version] = 1.0-beta1

projects[opigno_course][type] = module
projects[opigno_course][subdir] = "opigno"
projects[opigno_course][version] = 1.0-beta1

projects[opigno_dashboard][type] = module
projects[opigno_dashboard][subdir] = "opigno"
projects[opigno_dashboard][version] = 1.0-beta1

projects[opigno_group_manager][type] = module
projects[opigno_group_manager][subdir] = "opigno"
projects[opigno_group_manager][version] = 1.0-beta1

projects[opigno_learning_path][type] = module
projects[opigno_learning_path][subdir] = "opigno"
projects[opigno_learning_path][version] = 1.0-beta1

projects[opigno_messaging][type] = module
projects[opigno_messaging][subdir] = "opigno"
projects[opigno_messaging][version] = 1.0-beta1

projects[opigno_module][type] = module
projects[opigno_module][subdir] = "opigno"
projects[opigno_module][version] = 1.0-beta2

projects[opigno_notification][type] = module
projects[opigno_notification][subdir] = "opigno"
projects[opigno_notification][version] = 1.0-beta1

projects[opigno_scorm][type] = module
projects[opigno_scorm][subdir] = "opigno"
projects[opigno_scorm][version] = 1.0-beta1

projects[tft][type] = module
projects[tft][subdir] = "opigno"
projects[tft][version] = 1.0-beta1

; Contrib modules ============================================

projects[better_exposed_filters][type] = module
projects[better_exposed_filters][version] = 3.0-alpha3
projects[better_exposed_filters][subdir] = contrib

projects[config_rewrite][type] = module
projects[config_rewrite][version] = 1.1
projects[config_rewrite][subdir] = contrib

projects[entity][type] = module
projects[entity][version] = 1.0-beta1
projects[entity][subdir] = contrib

projects[field_group][type] = module
projects[field_group][version] = 1.0
projects[field_group][subdir] = contrib

projects[h5p][type] = module
projects[h5p][version] = 1.0-rc6
projects[h5p][subdir] = contrib

projects[multiselect][type] = module
projects[multiselect][version] = 1.0
projects[multiselect][subdir] = contrib

projects[private_message][type] = module
projects[private_message][version] = 1.0-beta18
projects[private_message][subdir] = contrib

projects[token][type] = module
projects[token][version] = 1.1
projects[token][subdir] = contrib

projects[token_filter][type] = module
projects[token_filter][version] = 1.0-beta1
projects[token_filter][subdir] = contrib

projects[view_mode_selector][type] = module
projects[view_mode_selector][version] = 1.x-dev
projects[view_mode_selector][subdir] = contrib
projects[view_mode_selector][download][type] = git
projects[view_mode_selector][download][branch] = 8.x-1.x
projects[view_mode_selector][download][url] = "https://git.drupal.org/project/view_mode_selector.git"
projects[view_mode_selector][download][revision] = 11c9ad2148d5b9c3bd552883da54b401736d0b31

projects[views_role_based_global_text][type] = module
projects[views_role_based_global_text][version] = 1.x-dev
projects[views_role_based_global_text][subdir] = contrib
projects[views_role_based_global_text][download][type] = git
projects[views_role_based_global_text][download][branch] = 8.x-1.x
projects[views_role_based_global_text][download][url] = "https://git.drupal.org/project/views_role_based_global_text.git"
projects[views_role_based_global_text][download][revision] = 550f8e076fa3c6458a5fe5640f765cf669fd4e0d
projects[views_role_based_global_text][patch][2969716] = "https://www.drupal.org/files/issues/2018-05-04/2969716-Undefined-index-default.patch"

projects[views_templates][type] = module
projects[views_templates][version] = 1.0-alpha1
projects[views_templates][subdir] = contrib

projects[calendar][type] = module
projects[calendar][version] = 1.x-dev
projects[calendar][subdir] = contrib
projects[calendar][download][type] = git
projects[calendar][download][branch] = 8.x-1.x
projects[calendar][download][url] = "https://git.drupal.org/project/calendar.git"
projects[calendar][download][revision] = 73e2979f3ed951b1fb3ad942e2d89d673aa52e1d

projects[calendar][patch][2756445] = "https://www.drupal.org/files/issues/2756445-month-names-not-showing-10.patch"
projects[calendar][patch][2699477] = "https://www.drupal.org/files/issues/2018-04-06/calendar-date_range-2699477-71.patch"
projects[calendar][patch][2604546] = "https://www.drupal.org/files/issues/2018-05-09/2604546-33.patch"
projects[calendar][patch][2630234] = "https://www.drupal.org/files/issues/fix_menu_tabs_in-2630234-15.patch"
projects[calendar][patch][2867991] = "https://www.drupal.org/files/issues/tab_navigation_not_working_after_using_pager-2867991-5.patch"
projects[calendar][patch][2901594] = "https://www.drupal.org/files/issues/week-week-day-display-issues-2901594-15.patch"
projects[calendar][patch][2955351] = "https://www.drupal.org/files/issues/2018-04-23/2955351-5-base-filter.patch"

projects[entity_print][type] = module
projects[entity_print][version] = 2.0
projects[entity_print][subdir] = contrib
projects[entity_print][patch][2969184] = "https://www.drupal.org/files/issues/2018-05-03/entity_print-dompdf-2969184.patch"

libraries[dompdf][download][type] = git
libraries[dompdf][download][url] = https://github.com/dompdf/dompdf.git
libraries[dompdf][download][download][branch] = master
libraries[dompdf][download][download][download][revision] = 2e643732cdb61fb09f867a15f2ae4e224503d1b0
libraries[dompdf][destination] = libraries

projects[group][type] = module
projects[group][version] = 1.0-rc2
projects[group][subdir] = contrib
projects[group][patch][2736233] = "https://www.drupal.org/files/issues/2018-04-20/2736233-156.patch"
projects[group][patch][2973005] = "https://www.drupal.org/files/issues/2018-05-15/group-2973005-2.patch"

projects[popup_field_group][type] = module
projects[popup_field_group][version] = 1.2
projects[popup_field_group][subdir] = contrib
projects[popup_field_group][patch][2965624] = "https://www.drupal.org/files/issues/2018-04-24/popup_field_group-form_submit-2965624-2.patch"
projects[popup_field_group][patch][2971021] = "https://www.drupal.org/files/issues/2018-05-07/popup_field_group-link_query-2971021-2.patch"

; Themes ========================================
projects[platon][type] = theme
projects[platon][version] = 1.0-beta2
