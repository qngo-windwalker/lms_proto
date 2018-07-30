<?php

namespace Drupal\private_message\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay ;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\private_message\Ajax\PrivateMessageInboxTriggerUpdateCommand;
use Drupal\private_message\Ajax\PrivateMessageLoadNewMessagesCommand;
use Drupal\private_message\Entity\PrivateMessageThread;
use Drupal\private_message\Entity\PrivateMessageThreadInterface;
use Drupal\private_message\Service\PrivateMessageServiceInterface;
use Drupal\user\entity\User;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PrivateMessageForm extends ContentEntityForm {

  /**
   * A unique instance identifier for the form
   *
   * @var int
   */
  protected $formId;

  /**
   * The current user
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity type manager service
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The typed data manager service
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * The user data service
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * The private message configuration
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The private message service
   *
   * @var \Drupal\private_message\Service\PrivateMessageServiceInterface
   */
  protected $privateMessageService;

  /**
   * Constructs a PrivateMessageForm object
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager service
   * @param \Drupal\user\UserDataInterface $userData
   *   The user data service
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory service
   * @param \Drupal\private_message\Service\PrivateMessageServiceInterface $privateMessageService
   *   The private message service
   */
  public function __construct(EntityManagerInterface $entityManager, AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager, TypedDataManagerInterface $typedDataManager, UserDataInterface $userData, ConfigFactoryInterface $configFactory, PrivateMessageServiceInterface $privateMessageService) {
    parent::__construct($entityManager);

    $this->currentUser = $currentUser;
    $this->entityTypeManager = $entityTypeManager;
    $this->typedDataManager = $typedDataManager;
    $this->userData = $userData;
    $this->config = $configFactory->get('private_message.settings');
    $this->privateMessageService = $privateMessageService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('typed_data_manager'),
      $container->get('user.data'),
      $container->get('config.factory'),
      $container->get('private_message.service')
    );
  }

  public function setFormId($id) {
    $this->formId = Html::escape($id);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    $form_id = parent::getFormId();

    if ($this->formId) {
      $form_id .= '-' . $this->formId;
    }

    return $form_id;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, PrivateMessageThreadInterface $private_message_thread = NULL) {
    $form = parent::buildForm($form, $form_state);

    if ($private_message_thread) {
      $form_state->set('thread_members', $private_message_thread->getMembers());
      $form['actions']['submit']['#ajax'] = [
        'callback' => '::ajaxCallback',
      ];

      // Only to do these when using #ajax
      $form['#attached']['library'][] = 'private_message/message_form';
      $form['message']['widget'][0]['#attributes']['autofocus'] = 'autofocus';
	}
	else {
      // Create a dummy private message thread form so as to retrieve
      // the members element from it
      $private_message_thread = PrivateMessageThread::create();
      $form_copy = $form;
      $form_state_copy = clone($form_state);
      $form_display = EntityFormDisplay::collectRenderDisplay($private_message_thread, 'default');
      $form_display->buildForm($private_message_thread, $form_copy, $form_state_copy);
      $form['members'] = $form_copy['members'];

      $form['#validate'][] = '::validateMembers';
	}

    if($this->config->get('hide_form_filter_tips')) {
      $form['#after_build'][] = '::afterBuild';
    }

    return $form;
  }

  /**
   * Validates that submitted members have permission to use the Private message system.
   * This validation is not added automatically, as the members field is not part of the
   * PrivateMessage entity, but rather something that has been shoehorned in from
   * the PrivateMessageThread entity, to make for a better user experience, by creating
   * a thread and a message in a single form.
   *
   * @see \Drupal\private_message\Entity\PrivateMessageThead::baseFieldDefinitions
   */
  public function validateMembers(array &$form, FormStateInterface $form_state) {
    // The members form element was loaded from the PrivateMessageThread entity type.
    // As it is not a part of the PrivateMessage entity, for which this form is built, the
    // constraints that are a part of the field on the PrivateMessageThread are not applied.
    // As such, the constraints need to be checked manually.

    // First, get the PrivateMessageThread entity type
    $entity_type = $this->entityTypeManager->getDefinition('private_message_thread');
    // Next, load the field definitions as defined on the entity type
    $field_definitions = PrivateMessageThread::baseFieldDefinitions($entity_type);

    // Get the member's field, as this is the field to be validated
    $members_field = $field_definitions['members'];

    // Retrieve any members submitted on the form
    $members = [];
    foreach ($form_state->getValue('members') as $info) {
      if (is_array($info) && is_numeric($info['target_id'])) {
        $user = User::load($info['target_id']);
        if ($user) {
          $members[] = $user;
        }
      }
    }

    // Get a typed data element that can be used for validation.
    $typed_data = $this->typedDataManager->create($members_field, $members);

    // Validate the submitted members
    $violations = $typed_data->validate();

    // Check to see if any contraint violations were found
    if ($violations->count() > 0) {
      // Output any errors for found constraint violations
      foreach ($violations as $violation) {
        $form_state->setError($form['members'], $violation->getMessage());
      }
    }
  }

  /**
   * Ajax callback for the PrivateMessageForm
   */
  public function ajaxCallback(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $form['message']['widget'][0]['value']['#value'] = '';
    $response->addCommand(new ReplaceCommand(NULL, $form));
    $response->addCommand(new PrivateMessageLoadNewMessagesCommand());
    $response->addCommand(new PrivateMessageInboxTriggerUpdateCommand());

    return $response;
  }

  /**
   * After build callback for the Private Message Form
   */
  public function afterBuild(array $form, FormStateInterface $form_state) {
    $form['message']['widget'][0]['format']['#access'] = FALSE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    $members = $form_state->get('thread_members');
    if (!$members) {
      // Generate an array containing the members of the thread
      $current_user = User::load($this->currentUser->id());
      $members = [$current_user];
      foreach ($form_state->getValue('members') as $info) {
        $user = User::load($info['target_id']);
        if ($user) {
          $members[] = $user;
        }
      }
    }
    // Get a private message thread containing the given users.
    $private_message_thread = $this->privateMessageService->getThreadForMembers($members);

    // Add the new message to the thread and save.
    $private_message_thread->addMessage($this->entity)->save();

    $mailManager = \Drupal::service('plugin.manager.mail');
    $params = [
      'private_message' => $this->getEntity(),
      'private_message_thread' => $private_message_thread,
    ];

    foreach ($members as $member) {
      if($member->id() != $this->currentUser->id()) {
        $params['member'] = $member;
        $send = $this->userData->get('private_message', $member->id(), 'email_notification');
        $send = is_numeric($send) ? (bool) $send : ($this->config('enable_email_notifications') && $this->config('send_by_default'));
        if ($send) {
          $mailManager->mail('private_message', 'message_notification', $member->getEmail(), $member->getPreferredLangcode(), $params);
        }
      }
    }

    // Send the user to the private message page. As this thread is the newest,
    // it wll be at the top of the list
    $form_state->setRedirect('entity.private_message_thread.canonical', ['private_message_thread' => $private_message_thread->id()]);

    return $status;
  }
}