<?php

namespace Drupal\wind_lms\Controller;

use Drupal\block\Entity\Block;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Enity\Node;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedLink;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;

class WindLMSDevPageController{

  public function content(){

//    $this->testPHPMailer();
//
//    $this->dbQuery(6, 4);
    /** @var  $grp_membership_service \Drupal\group\GroupMembershipLoader */
//    $user_account = user_load(6);
//    $grps = $grp_membership_service->loadByUser($user_account);
//    foreach ($grps as $grp) {
//      $group = $grp->getGroup();
//      $uid = $user_account->id();
//      wind_set_activity_completed($uid, $group->id());
////      $this->getUserProgress($uid, $group->id());
////      $progress = opigno_learning_path_progress($group->id(), $uid);
////      $this->getAllUserScorms($uid);
////      dsm($progress);
//    }
//
    // Check if there's any group
    $group_ids = $this->loadGroupMultiple();
    if (empty($group_ids)) {
      $group = $this->createGroup('Clearinghouse Learning Path');
      $module = $this->createModule('Clearinghouse Module');

      // Create the added item as an LP content.
      $new_content = OpignoGroupManagedContent::createWithValues(
        $group->id(),
        'ContentTypeModule',
        $module->id(),
        0,
        1
      );
      $new_content->save();

      $group->addContent($module, 'opigno_module_group');

      $path = drupal_get_path('module', 'ch_nav') . '/assets/employee-health-scorm2004_v4.zip';
      $file = $this->setFileRecord($path);

      $activity = $this->createScormActivity('Clearinghouse Activity', $file->id());

      /** @var \Drupal\opigno_module\Controller\OpignoModuleController $opigno_module_controller */
      $opigno_module_controller = \Drupal::service('opigno_module.opigno_module');
      $save_acitivities = $opigno_module_controller->activitiesToModule([$activity], $module);

      if (!$save_acitivities) {
        // Todo: Create error watchdog.
      }
    }

    $group = \Drupal\group\Entity\Group::load(2);

    $module = \Drupal::entityTypeManager()->getStorage('opigno_module')->load(2);

    $path = drupal_get_path('module', 'ch_nav') . '/assets/employee-health-scorm2004_v4.zip';
    $file = $this->setFileRecord($path);

    $activity = $this->createScormActivity('Clearinghouse Activity', $file->id());

    /** @var \Drupal\opigno_module\Controller\OpignoModuleController $opigno_module_controller */
    $opigno_module_controller = \Drupal::service('opigno_module.opigno_module');
    $save_acitivities = $opigno_module_controller->activitiesToModule([$activity], $module);
    dsm($save_acitivities);

//    foreach ($group_ids as $id) {
//      $group = \Drupal\group\Entity\Group::load($id);
//
////      if($group->label() == 'Employee Health Learning Path'){
//      if($group->label() == 'Redsox Learning Path'){
////        $this->addModuleToGroup($group);
//      }
//    }

    return array(
      '#type' => 'markup',
      '#markup' => 'aatesting'
    );
  }

  public function testPHPMailer() {
    $mail = new PHPMailer(TRUE);
    try {
      $mail->SMTPDebug = 2;
      $mail->isSMTP();
      $mail->Host = 'smtp1.example.com;smtp2.example.com';
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;

      $mail->setFrom('quan.ngo@windwalker.com', 'Mailer');
      $mail->addAddress('quan.windwalker@gmail.com', 'Quan Windwalker');
      $mail->addReplyTo('quan.ngo@windwalker.com', 'Quan Ngo');

      // Content
      $mail->isHTML(TRUE);
      $mail->Subject = 'Here is the subject';
      $mail->Body = 'This is the HTML message body <b> in bold!</b>';
      $mail->send();

      echo 'Message has been sent';
    } catch (Exception $exception) {
      echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
  }

  /**
   * @param $group
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @see \Drupal\opigno_learning_path\Controller\LearningPathManagerController::addItem
   */
  private function addModuleToGroup($group) {
    $entity = OpignoModule::create(array(
      'name' => 'Fan Screening Course',
      'status' => true,
    ));
    $entity->save();

    // Create the added item as an LP content.
    $new_content = OpignoGroupManagedContent::createWithValues(
      $group->id(),
      'ContentTypeModule',
      $entity->id()
    );
    $new_content->save();

    //        $new_link = OpignoGroupManagedLink::createWithValues(
    //          $group->id(),
    //          $parentCid,
    //          $new_content->id()
    //        );
    //        $new_link->save();

    $added_entity = \Drupal::entityTypeManager()
      ->getStorage('opigno_module')
      ->load($entity->id());
    $group->addContent($added_entity, 'opigno_module_group');

    //
    //        /* @var $connection \Drupal\Core\Database\Connection */
    //        $connection = \Drupal::service('database');
    //        $insert_query = $connection->insert('opigno_module_result_options')
    //          ->fields([
    //            'module_id',
    //            'module_vid',
    //            'option_name',
    //            'option_summary',
    //            'option_summary_format',
    //            'option_start',
    //            'option_end',
    //          ]);
    //
    //        $insert_query->execute();

    //        $related = $this->getRelatedGroupContent($id);

  }

  public function loadGroupMultiple() {
    $query = \Drupal::entityQuery('group');
    $result = $query->execute();

    if ($result) {
      return $result;
    } else {
      return array();
    }
  }

  public function dbQuery($uid, $scorm_id) {
    $data = NULL;
    $result = db_select('opigno_scorm_scorm_cmi_data', 'o')
      ->fields('o', array('value', 'serialized'))
      ->condition('o.uid', $uid)
      ->condition('o.scorm_id', $scorm_id)
//      ->condition('o.cmi_key', $cmi_key)
      ->execute()
      ->fetchObject();

    if (isset($result->value)) {
      $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
    }

    return $data;
  }

  public function entityQuery() {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
//    $query->condition('parent_content_id', $this->id());
//    $query->condition('required_score', $user_score, '<=');
//    $query->sort('required_score', 'DESC');
//    $query->range(0, 1);
    $result = $query->execute();

    // If no result, return FALSE.
    if (empty($result)) {
      return FALSE;
    }
  }


  public function getUserProgress($uid, $group_id) {
//    $activities = opigno_learning_path_get_activities($group_id, $uid);
    $this->wind_learning_path_get_steps($group_id, $uid);
//    opigno_learning_path_get_steps($group_id, $uid);
  }

  public function wind_learning_path_get_steps($group_id, $uid) {
    if (!isset($steps)) {
      $user = User::load($uid);
      /** @var \Drupal\opigno_group_manager\OpignoGroupContentTypesManager $content_type_manager */
      $content_type_manager = \Drupal::service('opigno_group_manager.content_types.manager');

      $steps = [];
      /** @var \Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent $first_content */
      $step = OpignoGroupManagedContent::getFirstStep($group_id);

      while ($step) {
        $cid = $step->id();
        $id = $step->getEntityId();
        $type_id = $step->getGroupContentTypeId();
        $type = $content_type_manager->createInstance($type_id);
        /** @var \Drupal\opigno_group_manager\OpignoGroupContent $content */
        $content = $type->getContent($id);

        $name = $content->getTitle();
        $typology = str_replace('ContentType', '', $type_id);

        // Get score required to pass step in percents.
        $required_score = (int) $step->getSuccessScoreMin();

        if ($type_id === 'ContentTypeModule') {
          // Get best score.
          $best_score = opigno_learning_path_get_module_best_score($id, $uid);

          /** @var \Drupal\opigno_module\Entity\OpignoModule $module */
          $module = OpignoModule::load($id);
          $attempts = $module->getModuleAttempts(User::load($uid));

          // Get activities.
          $activities = $module->getModuleActivities();
          /** @var \Drupal\opigno_module\Entity\OpignoActivity[] $activities */
          $activities = array_map(function ($activity) {
            return OpignoActivity::load($activity->id);
          }, $activities);

          $scorm_controller = \Drupal::service('opigno_scorm.scorm');
          foreach ($activities as $activity) {
            $f = $activity->getFields();
            $e = $activity->get('opigno_scorm_package');
            foreach ($e->referencedEntities() as $file) {
              $s = $scorm_controller->scormLoadByFileEntity($file);

              $data = NULL;
              $result = db_select('opigno_scorm_scorm_cmi_data', 'o')
                ->fields('o', array('value', 'serialized'))
                ->condition('o.uid', $uid)
                //              ->condition('o.scorm_id', $s->id)
                ->condition('o.cmi_key', 'cmi.completion_status')
                ->execute()
                ->fetchObject();

              if (isset($result->value)) {
                $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
              }

              return $data;
              //            opigno_scorm_cmi_get($uid, $s->id);
              //          dsm($s);
            }

          }

          // Get activities count.
          $activities_count = count($activities);

          // Filter finished attempts.
          $attempts = array_filter($attempts, function ($attempt) use (
            $required_score,
            $module,
            $user,
            $activities
          ) {
            /** @var \Drupal\opigno_module\Entity\UserModuleStatus $attempt */
            return (int) $attempt->get('finished')->getValue()[0]['value'] > 0;
          });
          $attempts_count = count($attempts);

          // Get time spent.
          $time_spent = array_sum(array_map(function ($attempt) {
            /** @var \Drupal\opigno_module\Entity\UserModuleStatus $attempt */
            $started = (int) $attempt->get('started')->getValue()[0]['value'];
            $finished = (int) $attempt->get('finished')->getValue()[0]['value'];

            return $finished > $started ? $finished - $started : 0;
          }, $attempts));

          // Get finish date of the first attempt that has passed.
          $passed_attempts = array_filter($attempts, function ($attempt) use (
            $required_score,
            $module,
            $user,
            $activities
          ) {
            /** @var \Drupal\opigno_module\Entity\UserModuleStatus $attempt */
            // Check that all actual module activities is evaluated.
            $evaluated = TRUE;
            foreach ($activities as $activity) {
              $answer = $activity->getUserAnswer($module, $attempt, $user);

              if ($answer === NULL) {
                $evaluated = FALSE;
              }
            }

            $score = opigno_learning_path_get_attempt_score($attempt);
            return $evaluated && $score >= $required_score;
          });

          $completed_on = !empty($passed_attempts) ? min(array_map(function ($attempt) {
            /** @var \Drupal\opigno_module\Entity\UserModuleStatus $attempt */
            return (int) $attempt->get('finished')->getValue()[0]['value'];
          }, $passed_attempts)) : 0;
        }
        elseif ($type_id === 'ContentTypeCourse') {
          $course_steps = opigno_learning_path_get_steps($id, $uid);

          // Get best score as an average modules best score.
          if (!empty($course_steps)) {
            $step_count = count($course_steps);
            $best_score = round(array_sum(array_map(function ($step) {
                return $step['best score'];
              }, $course_steps)) / $step_count);
          }
          else {
            $best_score = 0;
          }

          $attempts_count = 0;
          $activities_count = 0;

          // Sum of steps time spent.
          $time_spent = array_sum(array_map(function ($step) {
            return (int) $step['time spent'];
          }, $course_steps));

          // Get completed steps.
          $completed_steps = array_filter($course_steps, function ($step) {
            return $step['completed on'] > 0;
          });

          $completed_on = 0;

          // If all steps completed.
          if ($course_steps && count($course_steps) === count($completed_steps)) {
            // Get the last completion time.
            $completed_on = max(array_map(function ($step) {
              return $step['completed on'];
            }, $course_steps));
          }
        }
        else {
          $attempts_count = 0;
          $activities_count = 0;
          $best_score = 0;
          $time_spent = 0;
          $completed_on = 0;
        }

        $step_info = [
          // OpignoGroupManagedContent id.
          'cid' => $cid,
          // Group/Module entity id.
          'id' => $id,
          'name' => $name,
          'typology' => $typology,
          'time spent' => $time_spent,
          'completed on' => $completed_on,
          'best score' => $best_score,
          'required score' => $required_score,
          'attempts' => $attempts_count,
          'activities' => $activities_count,
          'mandatory' => $step->isMandatory(),
        ];
        $steps[] = $step_info;

        // If user is not attempted step,
        // assume user has got 100% score.
        if (!opigno_learning_path_is_attempted($step_info, $uid)) {
          $best_score = 100;
        }

        $step = $step->getNextStep($best_score);
      }
    }

    return $steps;
  }
  public function getAllUserScorms($uid){
    $data = NULL;
    $result = db_select('opigno_scorm_scorm_cmi_data', 'o')
      ->fields('o', array('value', 'serialized'))
      ->condition('o.uid', $uid)
//      ->condition('o.scorm_id', $scorm_id)
//      ->condition('o.cmi_key', $cmi_key)
      ->execute()
      ->fetchObject();

    if (isset($result->value)) {
      $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
    }

    return $data;
  }

  public function databaseQuery(){
    $result = db_select('opigno_scorm_packages', 'o')
      ->fields('o', array('value', 'serialized'))
      //      ->condition('o.scorm_id', $scorm_id)
      //      ->condition('o.cmi_key', $cmi_key)
      ->execute()
      ->fetchObject();

    if (isset($result->value)) {
      $data = !empty($result->serialized) ? unserialize($result->value) : $result->value;
    }

    return $data;
  }

  /**
   * Callback handler for wind-dev/user/{user}.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   */
  public function userContent(AccountInterface $user) {
    // Do something with $user.

  }

  public function createGroup($title) {
    $group = \Drupal\group\Entity\Group::create([
        'type' => 'learning_path',
        'label' => $title,
      ]
    );
    $group->enforceIsNew();
    $group->save();

    return $group;
  }

  public function createModule($name) {
    $entity = OpignoModule::create(array(
      'name' => $name,
      'status' => true,
    ));
    $entity->save();

    return $entity;
  }

  public function createScormActivity($name, $fid){
    // Create activity.
    $activity = \Drupal\opigno_module\Entity\OpignoActivity::create([
      'type' => 'opigno_scorm',
      'name' => $name,
      'opigno_scorm_package' => [
        'target_id' => $fid,
      ],
    ]);

    $activity->save();
    return $activity;
  }

  public function setFileRecord($filepath){
    $parsed_url = UrlHelper::parse($filepath);
    $filepath = $parsed_url['path'];
    $contents = file_get_contents($filepath);
    $file_name = drupal_basename($filepath);
    // Prepare folder.
    $temporary_file_path = 'public://external_packages/' . $file_name;
    /** @var \Drupal\file\FileInterface|false $file */
    $result = file_save_data($contents, $temporary_file_path);
    $file = \Drupal\file\Entity\File::load($result->id());
    return $file;
  }

  public function createUser() {
    $user = User::create([
      'name' => 'manager3',
      'mail' => 'quan.ngo@windwalker.com',
      'status' => 1,
      'roles' => array('user_manager'),
    ]);
    $user->save();
    return $user;
  }

  public function blockDev() {
//   Block::load('wind_theme_dashboard_views_block_who_s_online-who_s_online_block')->disable()->save();
//   Block::load('wind_theme_dashboard_views_block_opigno_notifications-block_unread_dashboard')->disable()->save();
//   Block::load('wind_theme_dashboard_views_block_private_message-block_dashboard')->disable()->save();
//   Block::load('wind_theme_breadcrumbs')->disable()->save();
    $block = Block::load('seven_login');
    //    $render = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
    //    $user_login_block = \Drupal::entityTypeManager()->getStrorage('block_content')->load('seven_login');
    //    dsm($user_login_block);
    //    $block = \Drupal\block_content\Entity\BlockContent::load('seven_login');
    //    $render = \Drupal::entityTypeManager()->getViewBuilder('block_content')->view($block);

  }

  public function getAnswers() {
    $answer_storage = static::entityTypeManager()->getStorage('opigno_answer');
    $query = $answer_storage->getQuery();
    $aids = $query->condition('user_id', $this->getOwnerId())
      ->condition('user_module_status', $this->id())
      ->execute();
    $answers = $answer_storage->loadMultiple($aids);
    return $answers;
  }

  public function createNode() {
    $node = Node::create(array(
      'title' => 'New Node',
      'body' => 'Node body content',
      'type' => 'article',
      'field_image' => ['target_id' => $file->id(), 'title' => 'This is a file title']
    ));
    $node->save();
  }

  private function getRelatedGroupContent($gid) {
    $result = \Drupal::entityQuery('group_content')
      ->condition('gid', $gid)
      ->execute();

    if ($result) {
      $relations = \Drupal\group\Entity\GroupContent::loadMultiple($result);
      foreach ($relations as $relation) {
        $entity = $relation->getEntity();
        $group = $relation->getGroup();
        $a = $entity;
        if ($entity->getEntityTypeId() == 'opigno_module') {

        }
      }
    }

  }

  private function databaseInsert() {
    /* @var $connection \Drupal\Core\Database\Connection */
    $connection = \Drupal::service('database');
    $insert_query = $connection->insert('opigno_module_result_options')
      ->fields([
        'module_id',
        'module_vid',
        'option_name',
        'option_summary',
        'option_summary_format',
        'option_start',
        'option_end',
      ]);

    $insert_query->values(array(
      'module_id' => $this->id(),
      'module_vid' => $this->getRevisionId(),
      'option_name' => $option['option_name'],
      'option_summary' => $option['option_summary'],
      'option_summary_format' => $option['option_summary_format'],
      'option_start' => $option['option_start'],
      'option_end' => $option['option_end'],
    ));

    $insert_query->execute();
  }


  /**
   * @param $title
   * @param $link
   * @return mixeduse
   * Drupal\Core\Link;
   * Drupal\Core\Url;
   */
  private function _l($title, $link) {
    $l = Link::fromTextAndUrl(
      $title,
      Url::fromUri("internal:/{$link}")
    );
    return $l->toString();
  }


  private function _l_fromRoute($title, $link) {
    $l = Link::fromTextAndUrl(
      $title,
      Url::fromRoute(
        'opigno_module.opigno_activities_browser',
        array('opigno_module' => $moduleId, 'opigno_activity' => $activity->id()),
        array('attributes' => array('target' => '_blank', 'class' => 'wind-scorm-popup-link'))
      )
    );
    return $l->toString();
  }

}
