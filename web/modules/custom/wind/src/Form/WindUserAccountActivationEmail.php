<?php

namespace Drupal\wind\Form;

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

/**
 * Provides a confirmation form for cancelling multiple user accounts.
 *
 * @internal
 */
class WindUserAccountActivationEmail extends ConfirmFormBase {

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
   * Constructs a new WindUserAccountActivationEmail.
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
    return 'wind_user_account_activation_email';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Send the Account Activation email to the selected user(s)');
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
    return $this->t('Send');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Retrieve the accounts to be canceled from the temp store.
    /* @var \Drupal\user\Entity\User[] $accounts */
    $accounts = $this->tempStoreFactory
      ->get('wind_email_account_activation_operations_send')
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

    $form['operation'] = ['#type' => 'hidden', '#value' => 'send'];

    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_user_id = $this->currentUser()->id();

    // Clear out the accounts from the temp store.
    $this->tempStoreFactory->get('wind_email_account_activation_operations_send')->delete($current_user_id);

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
        $users = User::loadMultiple($form_state->getValue('accounts'));
        foreach ($users as $user) {
          $result = _user_mail_notify('status_activated', $user);
          if ($result) {
            \Drupal::messenger()->addMessage("An Account Activation notification email has been send to {$user->getEmail()}.");
          }
        }

        $form_state->setRedirect('entity.user.collection');
      }
    }
  }

}
