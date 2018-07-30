<?php

namespace Drupal\opigno_learning_path;

use Drupal\Core\Url;
use Drupal\group\Entity\Group;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_learning_path\Entity\LPManagedContent;
use Drupal\opigno_module\Entity\OpignoModule;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LearningPathValidator {

  /**
   * Check if the user has successfully passed all the conditions of a learning path.
   */
  public static function userHasPassed($uid, Group $learning_path) {
    // Check if all the mandatory contents are okay and if all the minimum score of the mandatories are good.
    $contents = LPManagedContent::loadByLearningPathId($learning_path->id());
    foreach($contents as $content) {

      // If the content is not mandatory, go to next iteration.
//      if ($content->isMandatory() == FALSE) {
//        continue;
//      }

      // Get the minimum score required.
      $min_score = $content->getSuccessScoreMin() / 100;

      // Compare the user score with the minimum score required.
      $content_type = $content->getLearningPathContentType();
      $user_score = $content_type->getUserScore($uid, $content->getEntityId());

      // If the minimum score is no good, return FALSE.
      if ($user_score < $min_score) {
        return FALSE;
      }

    }

    // If all the scores are okay, return TRUE.
    return TRUE;
  }

  /**
   *  Redirect user if one of learning path steps aren't completed.
   *
   * @param \Drupal\group\Entity\Group $group
   *
   * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public static function stepsValidate(Group $group)  {
    $group_type = opigno_learning_path_get_group_type();
    $current_step = opigno_learning_path_get_current_step();
    $current_route = \Drupal::routeMatch()->getRouteName();
    $messenger = \Drupal::messenger();
    $url_param = \Drupal::service('current_route_match');
    $current = $url_param->getParameter('current');
    // Step 1 doesn't need validation because it has form validation.
    if ($current_step == 1) {
      return;
    };
    // Validate group type "learning_path".
    if ($group_type == 'learning_path') {
      // Route with invalid step.
      $route = '';
      // Get all training content.
      $contents = OpignoGroupManagedContent::loadByGroupId($group->id());
      // Learning path is empty
      if (empty($contents)) {
        $step = 2;
        $route = array_search($step, opigno_learning_path_get_routes_steps());
        // Show message only if user click on "next" button from current route.
        if ($current == strval($step)) {
            $messenger->addError("Please, add some course or module!");
        }
      }
      // Learning path is created and not empty
      else {
        // Skip 4 step if learning path hasn't any courses.
        $group_courses = $group->getContent('subgroup:opigno_course');
        if (empty($group_courses) && $current_route == 'opigno_learning_path.learning_path_courses') {
          $step = 4;
          $route = array_search($step, opigno_learning_path_get_routes_steps());
          // $messenger->addError("You can't visit this page because you didn't add any course with modules!");
        }

        // Check if training has at least one mandatory entity.
        $has_mandatory = self::hasMandatoryItem($contents);
        if (!$has_mandatory) {
          $step = 2;
          $route = array_search($step, opigno_learning_path_get_routes_steps());
          // Show message only if user click on "next" button from current route.
          if ($current == strval($step)) {
          $messenger->addError("At least one entity must be mandatory!");
          }
        }
        else {
          foreach ($contents as $cid => $content) {
            $type_id = $content->getGroupContentTypeId();
            if ($type_id === 'ContentTypeModule') {
              // Check if all modules has at least one activity.
              $module_id = $content->getEntityId();
              $module = OpignoModule::load($module_id);
              $activities = $module->getModuleActivities();
              // If at least one module hasn't activity.
              if (empty($activities)) {
                $step = 4;
                $route = array_search($step, opigno_learning_path_get_routes_steps());
                // Show message only if user click on "next" button from current route.
                if ($current == strval($step)) {
                  $messenger->addError("Please, add at least one activity to {$module->label()} module!");
                }
              }
            }
            else {
              if ($type_id === 'ContentTypeCourse') {
                $contents = OpignoGroupManagedContent::loadByGroupId($content->getEntityId());
                // Check if each course has at least one module.
                if (empty($contents)) {
                  $step = 3;
                  $route = array_search($step, opigno_learning_path_get_routes_steps());
                  // Show message only if user click on "next" button from current route.
                  if ($current == strval($step)) {
                    $messenger->addError("Please, add to course at least one module!");
                  }
                }
                else {
                  foreach ($contents as $cid => $content) {
                    // Check if all modules in course has at least one activity.
                    $module_id = $content->getEntityId();
                    $module = OpignoModule::load($module_id);
                    $activities = $module->getModuleActivities();
                    // If at least one module hasn't activity.
                    if (empty($activities)) {
                      $step = 4;
                      $route = array_search($step, opigno_learning_path_get_routes_steps());
                      // Show message only if user click on "next" button from current route.
                      if ($current == strval($step)) {
                        $messenger->addError("Please, add at least one activity to {$module->label()} module!");
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
      if (!empty($route)) {
        if ($route == $current_route) {
          // Prevent redirect from current route
          return;
        };
        // Redirect to incompleted step.
        $response = new RedirectResponse(Url::fromRoute($route, ['group' => $group->id()])->toString());
        return $response->send();
      };
    }
    // If validation is passed successful.
    return;
  }

  /**
   *  Check if training has at least one mandatory content.
   *
   * @param array
   *
   * @return bool
   */
  protected static function hasMandatoryItem($contents) {
    foreach ($contents as $cid => $content) {
      if ($content->isMandatory()) {
        return TRUE;
      };
    }
    // If training hasn't mandatory entity return FALSE.
    return FALSE;
  }

}
