<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\EntityInterface;

class WindLMSImportController extends ControllerBase {

  public function getContent(Request $request){
    $content = $request->getContent();
    if (empty($content)) {
      return new JsonResponse($this->getError('Body can not be empty. Must contain JSON data'));
    }
    switch (\Drupal::request()->get('data')){
      case 'user' :
        return new JsonResponse($this->importUsers($content));
        break;
    }
    return new JsonResponse($this->getError('Query does not match'));
  }

  public function addCourse(array $courseData, string $topicTitle) {
    $courseNid = $this->addNode($courseData, 'course');

    if (!is_numeric($courseNid) || $courseNid == 0 ) {
      return;
    }

    $topicNid = $this->loadNodeByTitle($topicTitle, 'curriculum');
    if ($topicNid) {
      // Connect Topic to our newly created course.
      $this->linkReferenceEnity(Node::load($topicNid), 'field_activity', Node::load($courseNid));
    }
  }

  public function addNode(array $data, string $node_type) {
    $nid = $this->loadNodeByTitle($data['title'], $node_type);
    // Update the node if it's already in the database.
    if ($nid) {
      $node = Node::load($nid);
    } else {
      $node = Node::create([
        'type' => $node_type,
        //        'uid' => 1,
        //        'revision' => 0,
        'status' => TRUE,
        //        'promote' => 0,
        //        'created' => time(),
        //        'langcode' => 'en',
        'title' => $data['title'],
      ]);
    }

    $node->set('body', [
      'value' => $data['body'],
      'format' => 'basic_html'
    ]);

    try {
      $result = $node->save();
      return $result ? $node->id() : $result;
    } catch (EntityStorageException $e) {
      return $e;
    }
  }

  public function loadNodeByTitle(string $title, string $type) {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('title', $title);
    $query->condition('type', $type);
    $result = $query->execute();
    if($result){
      return array_shift($result);
    }
    return false;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $parentNode
   * @param string $field
   * @param \Drupal\Core\Entity\EntityInterface $childNode
   *
   * @return \Drupal\Core\Entity\EntityStorageException|\Exception|int|void
   */
  public function linkReferenceEnity(EntityInterface $parentNode, string $field, EntityInterface $childNode) {
    $parenFieldValue = $parentNode->get($field)->getValue();
    $target_ids = array_column($parenFieldValue, 'target_id');
    // Don't mess with it if it's already linked.
    if( in_array($childNode->id(), $target_ids) ){
      return;
    }
    $parenFieldValue[] = array('target_id' => $childNode->id());
    $parentNode->$field->setValue($parenFieldValue);
    try {
      return $parentNode->save();
    } catch (EntityStorageException $e) {
      return $e;
    }
  }

  private function importUsers($content){
    $successCollection = array();
    $errorCollection = array();
    $params = json_decode($content, TRUE);
    // Validate
    if($params[0][0] == 'First Name' && $params[0][1] == 'Last Name'){
      foreach ($params as $index => $param) {
        // We do NOT want the label row.
        if ($index == 0) {
          continue;
        }

        $firstName = trim($param[0]);
        $lastName = trim($param[1]);
        $email = trim($param[2]);
        $role = strtolower(trim($param[3]));
        $role = str_replace(' ', '_', $role);
        $teamTid = $this->getUserTeamTid(trim($param[4]));

        $result = $this->addUser($firstName, $lastName, $email, $role, $teamTid);
        if ($result) {
          $successCollection[] = $param;
        } else {
          $errorCollection[] = $param;
        }
      }
    }

    return [
      'success' => 1,
      'message' => 'Success',
      'imported' => $successCollection,
      'issues' => $errorCollection
    ];
  }

  private function importCourses($content){
    // Todo: Add some importing magic!
  }

  private function getError($msg){
    return array(
      'code' => 500,
      'error' => 1,
      'message' => $msg,
    );
  }

  private function addUser($firstName, $lastName, $email, $role, $teamTid){
    $uid = $this->loadUserByEmail($email);
    $userIsNew = null;
    if ($uid) {
      // If user already exist in the system
      $user = User::load($uid);
    } else {
      $password = substr(strtoupper($firstName), 0, 1). ucfirst($lastName);

      $user = User::create();
      // Mandatory settings
      $user->setPassword($password);
      $user->enforceIsNew();
      $user->setEmail($email);
      $user->setUsername($email);
      // Optional settings
      $user->set("init", 'email');
      $user->addRole($role);
      $user->set('field_first_name', $firstName);
      $user->set('field_last_name', $lastName);
      $user->activate();

      $userIsNew = TRUE;
    }


    // This will replace any existing value
//    if ($teamTid) {
//      $user->set('field_team', $teamTid);
//    }

    // If we re-import the same user but with different Team,
    // add Team Tid if it doesn't exit instead of replacing it
    $user = $this->appendUserFieldTeam($user, $teamTid);

    try {
      $user->save();
      // Note: $user->isNew() won't work after user has been saved ( $user->save() )to the database.
      // And calling _user_mail_notify() before $user->save() won't work because the account won't have an Uid associated with it.
      if ($userIsNew) {
        $result = _user_mail_notify('status_activated', $user);
        if ($result) {
          // Code commetted out b/c ReactJS Frontend will have it own success message. Keeping codes for reference.
          /** @var \Drupal\Core\Messenger\MessengerInterface $message */
//          $message = \Drupal::messenger()->addMessage("An Account Activation notification email has been send to {$user->getEmail()}.");
//          $statusMessages = $message->messagesByType(MessengerInterface::TYPE_STATUS);
        }
      }

    } catch (EntityStorageException $e) {
      return FALSE;
    }
    return $user->id();
  }

  private function loadUserByNames(string $firstName, string $lastName) {
    $query = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
    $query->condition('field_first_name', $firstName);
    $query->condition('field_first_name', $lastName);
    $result = $query->execute();
    if($result){
      return array_shift($result);
    }
    return false;
  }

  function appendUserFieldTeam(EntityInterface &$userEntity, $teamTid){
    if (!$teamTid) {
      return $userEntity;
    }

    $currentValue = $userEntity->get('field_team')->getValue();

    // Only add if user does NOT have it.
    if(!in_array($teamTid, array_column($currentValue, 'target_id'))){
      // @credit https://drupal.stackexchange.com/a/197628
      $userEntity->field_team[] = $teamTid;
    }

    return $userEntity;
  }

  private function loadUserByEmail(string $email) {
    $query = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
    $query->condition('mail', $email);
    $result = $query->execute();
    if($result){
      return array_shift($result);
    }
    return false;
  }

  private function getUserTeamTid(string $title) {
    $vocab = Vocabulary::load('user_team');
    if (!$vocab) {
      return;
    }

    $term = wind_get_tid_by_name($title, $vocab->id());
    // If term already exist, do an update.
    if ($term) {
      // Todo: add update to term.
    } else {
      $term = Term::create(array(
        // htmlspecialchars — Convert special characters to HTML entities
        'name' => $title,
        'vid' => $vocab->id(),
//        'description' => htmlspecialchars($body),
//        'weight' => $weight,
      ));
    }

    try {
      $term->save();
    } catch (EntityStorageException $e) {
      return FALSE;
    }
    return $term->id();
  }

}
