<?php

namespace Drupal\wind_lms\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\node\Entity\Node;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
/**
 * Provides a confirmation form for cancelling multiple user accounts.
 *
 * @internal
 */
class WindLMSUserMultipleEnroll extends ConfirmFormBase {

  /**
   * The temp store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new WindLMSUserMultipleEnroll.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The temp store factory.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(PrivateTempStoreFactory $temp_store_factory, UserStorageInterface $user_storage, EntityTypeManagerInterface $entity_type_manager) {
    $this->tempStoreFactory = $temp_store_factory;
    $this->userStorage = $user_storage;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_lms_user_multiple_enroll';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Select which course(s) to enroll the selected user(s)');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.user.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Large volume may have performance impact. This action can be time consuming to undo for.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Enroll');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Retrieve the accounts to be canceled from the temp store.
    /* @var \Drupal\user\Entity\User[] $accounts */
    $accounts = $this->tempStoreFactory
      ->get('wind_lms_user_operations_enroll')
      ->get($this->currentUser()->id());
    if (!$accounts) {
      return $this->redirect('entity.user.collection');
    }

    $root = NULL;
    $names = [];
    $form['accounts'] = ['#tree' => TRUE];
    foreach ($accounts as $account) {
      $uid = $account->id();
      $names[$uid] = $account->label();
      $form['accounts'][$uid] = [
        '#type' => 'hidden',
        '#value' => $uid,
      ];
    }

    $form['account']['names'] = [
      '#theme' => 'item_list',
      '#items' => $names,
    ];

    $form['operation'] = ['#type' => 'hidden', '#value' => 'enroll'];

    // Because of the destination in the URL query, it's not possible to have form_state rebuild
    // Commetted out for now. might come back later.
//    $form['filters'] = [
//      '#type'  => 'fieldset',
//      '#title' => $this->t('Filter'),
//      '#open'  => true,
//    ];
//
//    $form['filters']['search_title'] = [
//      '#title'         => 'Title',
//      '#type'          => 'search'
//    ];
//
//    $form['filters']['field_category'] = [
//      '#title'         => 'Category',
//      '#type'          => 'select',
//      '#empty_value'   => 'none',
//      '#empty_option'  => '- None -',
//      '#size'          => 0,
//      '#options'       => ['value1' => 'label1', 'value2' => 'label2'],
//      '#default_value' => 'none'
//    ];
//
//    $form['filters']['actions'] = [
//      '#type'       => 'actions'
//    ];
//
//    $form['filters']['actions']['submit'] = [
//      '#type'  => 'submit',
//      '#value' => $this->t('Filter')
//    ];

    // Todo: make table sortable: https://drupal.stackexchange.com/questions/259095/specify-default-sort-header-for-a-sortable-table-tablesort-in-d8
    $header = [
      'title' => $this->t('Course Name'),
      'field_category' => $this->t('Category'),
      'field_learner_access' => $this->t('Accessible To All Leaners'),
    ];

    $form['course_table'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $this->getCourseNodesOptions(),
      '#empty' => $this->t('No records found'),
    );

    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_user_id = $this->currentUser()->id();

    // Clear out the accounts from the temp store.
    $this->tempStoreFactory->get('wind_lms_user_operations_enroll')->delete($current_user_id);


    if ($form_state->getValue('confirm')) {
      if ($form_state->getValue('submit')->render() == 'Filter') {
        $form_state->setStorage([
          'filter' => array(
            'text' =>  $form_state->getValue('search_title'),
            'field_category' =>  $form_state->getValue('field_category')
          )
        ]);
        // This probably available in Drupal 9.2 @see https://www.drupal.org/project/drupal/issues/2950883
//        $form_state->ignoreDestination();
//        $form_state->getRedirectDestination()->set(FALSE);

//        unset($_GET['destination']);
        $form_state->setRebuild();
      } else {

        $checkedCourseIds = $this->getCheckedRow($form_state->getValue('course_table'));
        $courseNodes = \Drupal\node\Entity\Node::loadMultiple($checkedCourseIds);
        foreach ($form_state->getValue('accounts') as $uid => $value) {
          foreach ($courseNodes as $courseNode) {
            // Get all of the Ids in an array format
            $field_learner = explode(',', $courseNode->field_learner->getString());

            // Make sure we don't duplicate value b/c Drupal will allow it
            if (!in_array($uid, $field_learner)) {
              $courseNode->field_learner[] = $uid;
              $courseNode->save();
            }
          }
        }
        $form_state->setRedirect('entity.user.collection');
      }
    }
  }

  private function getCourseNodesOptions() {
    $result = \Drupal::entityQuery('node')
      ->condition('type', 'course')
      ->execute();

    if (!$result) {
      return [];
    }

    $option = [];
    $nodes = Node::loadMultiple($result);
    foreach ($nodes as $nid => $node) {
      $field_category = $node->field_category->referencedEntities();
      /** @var \Drupal\taxonomy\Entity\Term $term */
      $tagEntities = array_map (function($term){
        return $term->label();
      }, $field_category);

      $option[$nid] = [
        'title' => $node->label(),
        'field_category' => implode(', ', $tagEntities),
        'field_learner_access' => $node->field_learner_access->getString() == '1' ? 'Yes' : 'No',
      ];
    }
    return $option;
  }

  private function getCheckedRow($row) {
    $checked = [];
    foreach ($row as $id => $value) {
      if ($value) {
        $checked[$id] = $value;
      }
    }

    return $checked;
  }
}
