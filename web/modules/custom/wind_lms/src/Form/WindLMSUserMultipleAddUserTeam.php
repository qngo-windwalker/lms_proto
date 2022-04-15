<?php

namespace Drupal\wind_lms\Form;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a confirmation form for cancelling multiple user accounts.
 *
 * @internal
 */
class WindLMSUserMultipleAddUserTeam extends ConfirmFormBase {

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
    return 'wind_lms_user_multiple_add_user_team_taxonomy';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Select which team(s) to add the selected user(s)');
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
    return $this->t('Add');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Retrieve the accounts to be canceled from the temp store.
    /* @var \Drupal\user\Entity\User[] $accounts */
    $accounts = $this->tempStoreFactory
      ->get('wind_lms_add_user_team_operations')
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


    // Todo: make table sortable: https://drupal.stackexchange.com/questions/259095/specify-default-sort-header-for-a-sortable-table-tablesort-in-d8
    $header = [
      'title' => $this->t('Team'),
    ];

    $form['option_table'] = array(
      '#type' => 'tableselect',
      '#header' => $header,
      '#options' => $this->getTableSelectOptions(),
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
    $this->tempStoreFactory->get('wind_lms_add_user_team_operations')->delete($current_user_id);

    if ($form_state->getValue('confirm')) {
      if ($form_state->getValue('submit')->render() == 'Filter') {

      } else {

        $checkedIds = $this->getCheckedRow($form_state->getValue('option_table'));
        $terms = Term::loadMultiple($checkedIds);
        $messenger = \Drupal::messenger();
        foreach ($form_state->getValue('accounts') as $uid => $value) {
          $user = User::load($uid);
          /** @var \Drupal\taxonomy\Entity\Term $term */
          foreach ($terms as $tid => $term) {
            // Get all of the Ids in an array format
            $currentValue = $user->get('field_team')->getValue();

            // Make sure we don't duplicate value b/c Drupal will allow it
            if(!in_array($tid, array_column($currentValue, 'target_id'))){
              $user->field_team[] = $tid;
              try {
                $user->save();
              } catch (EntityStorageException $e) {
                $messenger->addMessage(t('There was an error adding %user to %label.', [
                  '%user' => $user->getAccountName(),
                  '%label' => $term->label(),
                ]), $messenger::TYPE_ERROR);
              }
              $messenger->addMessage(t('Successful adding %user to %label.', [
                '%user' => $user->getAccountName(),
                '%label' => $term->label(),
              ]), $messenger::TYPE_STATUS);
            }
          }
        }
        $form_state->setRedirect('entity.user.collection');
      }
    }
  }

  private function getTableSelectOptions() {
    $vid = 'user_team';
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);

    if (!$terms) {
      return [];
    }

    $option = [];
    /** @var \Drupal\taxonomy\Entity\Term $term */
    foreach ($terms as $term) {
      $option[$term->tid] = [
        'title' => $term->name,
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
