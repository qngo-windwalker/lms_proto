<?php

namespace Drupal\opigno_course\Form;

use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
//use Drupal\opigno_learning_path\Database\LearningPathContentsDatabase;
//use Drupal\opigno_learning_path\LearningPathToolsManager;
use Symfony\Component\Routing\Route;

class CourseContentForm extends FormBase {

  /**
   * !!!!!!!!!!!!!!!!!!
   *
   * THIS WHOLE FILE WILL BE REPLACED BY A LEARNING PATH MANAGER LOOKALIKE TOOL.
   * // TODO: Create the course content manager using the learning path manager app.
   *
   * !!!!!!!!!!!
   */

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'opigno_course_content_add';
  }

  /**
   * Form constructor.
   *
   * @param array                                $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Get all the tools
//    $tools_manager = new LearningPathToolsManager();
//    $tools = $tools_manager->getTools();
//    $tools_options = [];
//    foreach($tools as $tool) {
//      $tools_options[ $tool->getToolId() ] = $tool->getName();
//    }
//
//    // Get the content of this course
//    $db = new LearningPathContentsDatabase(\Drupal::getContainer()->get('database'));
//    $contents_db = $db->getContentByLPID($this->getRequestGroup()->id());
//    $course_content_rows = [];
//    foreach($contents_db as $content_db) {
//      $route_params = [
//        'learning_path_id' => $this->getRequestGroup()->id(), // TODO: Verify this...
//        'entity_type' => $content_db->getEntityType(),
//        'entity_bundle' => $content_db->getEntityBundle(),
//        'entity_id' => $content_db->getEntityId()
//      ];
//
//      $course_content_rows[] = [
//        $content_db->getEntityTitle(),
//        $tools_options[$content_db->getEntityToolId()],
//        Link::createFromRoute('Remove from course', 'opigno_learning_path.content.remove', $route_params, ['query' => ['callback' => $this->getRequest()->getPathInfo()]])->toString()
//      ];
//    }
//
//    // Get the content that is not part of this course
//    $not_contents_db = $db->getContentNotAssocToLPID($this->getRequestGroup()->id());
//    $not_course_content_rows = [];
//    foreach($not_contents_db as $not_content_db) {
//      $route_params = [
//        'learning_path_id' => $this->getRequestGroup()->id(),
//        'entity_type' => $not_content_db->getEntityType(),
//        'entity_bundle' => $not_content_db->getEntityBundle(),
//        'entity_id' => $not_content_db->getEntityId()
//      ];
//
//      $not_course_content_rows[] = [
//        $not_content_db->getEntityTitle(),
//        $tools_options[$not_content_db->getEntityToolId()],
//        Link::createFromRoute('Add to course', 'opigno_learning_path.content.add', $route_params, ['query' => ['callback' => $this->getRequest()->getPathInfo()]])->toString()
//      ];
//    }
//
//    $form['add_content_fieldset'] = [
//      '#type' => 'fieldset',
//      '#title' => $this->t('Add a new content'),
//      '#attributes' => [
//        'class' => ['container-inline']
//      ]
//    ];
//
//    $form['add_content_fieldset']['select_content'] = [
//      '#type' => 'select',
//      '#options' => $tools_options
//    ];
//
//    // Button "Add content".
//    $form['add_content_fieldset']['add_content'] = [
//      '#type' => 'submit',
//      '#value' => '+ '. $this->t('Add'),
//      '#attributes' => [
//        'class' => ['button', 'button-action', 'button--primary', 'button--small']
//      ]
//    ];
//
//    // List this course content.
//    $form['list_course_content_fieldset'] = [
//      '#type' => 'fieldset',
//      '#title' => $this->t('This course content')
//    ];
//
//    // List all the existing course contents in a table.
//    $form['list_course_content_fieldset']['content_list'] = [
//      '#type' => 'table',
//      '#headers' => ['Title', 'Type', 'Actions'],
//      '#rows' => $course_content_rows
//    ];
//
//    $form['list_existing_content_fieldset'] = [
//      '#type' => 'fieldset',
//      '#title' => $this->t('Add existing content')
//    ];
//
//    // List all the existing contents in a table.
//    $form['list_existing_content_fieldset']['content_list'] = [
//      '#type' => 'table',
//      '#headers' => ['Title', 'Type', 'Actions'],
//      '#rows' => $not_course_content_rows
//    ];


    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
//    $tool_id = $form_state->getValue('select_content');
//
//    $tools_manager = new LearningPathToolsManager();
//    $tool = $tools_manager->getToolById($tool_id);
//    $url = $tool->getNewContentUrl();
//
//    $url->setOption('query', ['destination' => $this->getRequest()->getRequestUri()]);
//
//    $form_state->setRedirectUrl($url);
  }

  /**
   * Check the access to this form.
   */
  public function access(Group $group) {
    if ($group->getGroupType()->id() == 'opigno_course') {
      return AccessResult::allowed();
    }
    return AccessResult::neutral();
  }
}
