<?php

namespace Drupal\private_message\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\private_message\Ajax\PrivateMessageInboxUpdateCommand;
use Drupal\private_message\Ajax\PrivateMessageInsertNewMessagesCommand;
use Drupal\private_message\Ajax\PrivateMessageInboxInsertThreadsCommand;
use Drupal\private_message\Ajax\PrivateMessageInsertPreviousMessagesCommand;
use Drupal\private_message\Ajax\PrivateMessageInsertThreadCommand;
use Drupal\private_message\Ajax\PrivateMessageMembersAutocompleteResponseCommand;
use Drupal\private_message\Ajax\PrivateMessageMemberUsernameValidatedCommand;
use Drupal\private_message\Ajax\PrivateMessageUpdateUnreadThreadCountCommand;
use Drupal\private_message\Entity\PrivateMessageThread;
use Drupal\private_message\Service\PrivateMessageServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AjaxController extends ControllerBase implements AjaxControllerInterface  {

  const AUTOCOMPLETE_COUNT = 10;

  /**
   * The renderer service
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The request stack
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The entity manager service
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The configuration factory
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The private message service
   *
   * @var \Drupal\private_message\Service\PrivateMessageServiceInterface
   */
  protected $privateMessageService;

  /**
   * Constructs n AjaxController object
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration factory
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user
   * @param \Drupal\private_message\Service\PrivateMessageServiceInterface $privateMessageService
   *   The private message service
   */
  public function __construct(RendererInterface $renderer, RequestStack $requestStack, EntityManagerInterface $entityManager, ConfigFactoryInterface $configFactory, AccountProxyInterface $currentUser, PrivateMessageServiceInterface $privateMessageService) {
    $this->renderer = $renderer;
    $this->requestStack = $requestStack;
    $this->entityManager = $entityManager;
    $this->configFactory = $configFactory;
    $this->currentUser = $currentUser;
    $this->privateMessageService = $privateMessageService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer'),
      $container->get('request_stack'),
      $container->get('entity.manager'),
      $container->get('config.factory'),
      $container->get('current_user'),
      $container->get('private_message.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxCallback($op) {
    $response = new AjaxResponse();

    if ($this->currentUser->hasPermission('use private messaging system')) {
      switch($op) {

        case 'get_new_messages': {
          $command = $this->getNewPrivateMessages($response);
          break;
        }

        case 'get_old_messages': {
          $command = $this->getOldPrivateMessages($response);
          break;
        }

        case 'get_old_inbox_threads': {
          $command = $this->getOldInboxThreads($response);
          break;
        }

        case 'get_new_inbox_threads': {
          $command = $this->getNewInboxThreads($response);
          break;
        }

        case 'validate_private_message_member_username': {
          $this->validatePrivateMessageMemberUsername($response);
          break;
        }

        case 'get_new_unread_thread_count': {
          $command = $this->getNewUnreadThreadCount($response);
          break;
        }

        case 'load_thread': {
          $this->loadThread($response);
          break;
        }
      }
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function privateMessageMembersAutocomplete() {
    $response = new AjaxResponse();

    $username = $this->requestStack->getCurrentRequest()->get('username');
    $accounts = $this->privateMessageService->getUsersFromString($username, self::AUTOCOMPLETE_COUNT);
    $user_info = [];
    foreach ($accounts as $account) {
      if ($account->access('view', $this->currentUser)) {
        $user_info[] = [
          'uid' => $account->id(),
          'username' => $account->getDisplayName(),
        ];
      }
    }

    $response->addCommand(new PrivateMessageMembersAutocompleteResponseCommand($username, $user_info));

    return $response;
  }

  /**
   * Create Ajax Command containing new private messages
   *
   * @param Drupal\Core\Ajax\AjaxResponse $response
   *   The response to which any commands should be attached
   */
  protected function getNewPrivateMessages($response) {
    $thread_id = $this->requestStack->getCurrentRequest()->get('threadid');
    $message_id = $this->requestStack->getCurrentRequest()->get('messageid');
    if (is_numeric($thread_id) && is_numeric($message_id)) {
      $new_messages = $this->privateMessageService->getNewMessages($thread_id, $message_id);
      if (count($new_messages)) {
        $messages = [];
        $view_builder = $this->entityManager->getViewBuilder('private_message');
        foreach ($new_messages as $message) {
          if ($message->access('view', $this->currentUser)) {
            $messages[] = $view_builder->view($message);
          }
        }

        $response->addCommand(new PrivateMessageInsertNewMessagesCommand($this->renderer->renderRoot($messages)));
      }
    }
  }

  /**
   * Create Ajax Command containing old private messages
   *
   * @param Drupal\Core\Ajax\AjaxResponse $response
   *   The response to which any commands should be attached
   */
  protected function getOldPrivateMessages($response) {
    $current_request = $this->requestStack->getCurrentRequest();
    $thread_id = $current_request->get('threadid');
    $message_id = $current_request->get('messageid');
    $message_count = $current_request->get('count');
    if (is_numeric($thread_id) && is_numeric($message_id)) {
      $message_info = $this->privateMessageService->getPreviousMessages($thread_id, $message_id);

      if (count($message_info)) {
        $messages = [];
        $view_builder = $this->entityManager->getViewBuilder('private_message');
        foreach ($message_info as $message) {
          if ($message->access('view', $this->currentUser)) {
            $messages[] = $view_builder->view($message);
          }
        }

        $response->addCommand(new PrivateMessageInsertPreviousMessagesCommand($this->renderer->renderRoot($messages)));
      }
      else {
        $response->addCommand(new PrivateMessageInsertPreviousMessagesCommand(''));
      }
    }
  }

  /**
   * Create Ajax Command containing old threads for the private message inbox
   *
   * @param Drupal\Core\Ajax\AjaxResponse $response
   *   The response to which any commands should be attached
   */
  protected function getOldInboxThreads($response) {
    $timestamp = $this->requestStack->getCurrentRequest()->get('timestamp');
    $thread_count = $this->requestStack->getCurrentRequest()->get('count');
    if (is_numeric($timestamp)) {
      $thread_info = $this->privateMessageService->getThreadsForUser($thread_count, $timestamp);
      if (count($thread_info['threads'])) {
        $oldest_timestamp = FALSE;
        $view_builder = $this->entityManager->getViewBuilder('private_message_thread');
        $threads = [];
        foreach ($thread_info['threads'] as $thread) {
          if ($thread->access('view', $this->currentUser)) {
            if ($thread_info['next_exists']) {
              $oldest_timestamp = $thread->get('updated')->value;
            }
            $threads[] = $view_builder->view($thread, 'inbox');
          }
        }

        $response->addCommand(new PrivateMessageInboxInsertThreadsCommand($this->renderer->renderRoot($threads), $oldest_timestamp));
      }
        else {
        $response->addCommand(new PrivateMessageInboxInsertThreadsCommand('', FALSE));
      }
    }
  }

  /**
   * Create Ajax Command containing new threads for the private message inbox
   *
   * @param Drupal\Core\Ajax\AjaxResponse $response
   *   The response to which any commands should be attached
   */
  protected function getNewInboxThreads($response) {
    $info = $this->requestStack->getCurrentRequest()->get('ids');

    // Check to see if any thread IDs were POSTed
    if (count($info)) {
      // Get new inbox information based on the posted IDs
      $inbox_threads = $this->privateMessageService->getUpdatedInboxThreads($info);
    }
    else {
      // No IDs were posted, so the maximum possible number of threads to be returned is
      // retrieved from the block settings.
      $thread_count = $this->configFactory->get('block.block.privatemessageinbox')->get('settings.thread_count');
      $inbox_threads = $this->privateMessageService->getUpdatedInboxThreads([], $thread_count);
    }

    // Only need to do something if any thread IDS were found
    if (count($inbox_threads['thread_ids'])) {
      $view_builder = $this->entityManager->getViewBuilder('private_message_thread');

      // Render any new threads as HTML to be sent to the browser.
      $rendered_threads = [];
      foreach (array_keys($inbox_threads['new_threads']) as $thread_id) {
        if ($inbox_threads['new_threads'][$thread_id]->access('view', $this->currentUser)) {
          $renderable = $view_builder->view($inbox_threads['new_threads'][$thread_id], 'inbox');
          $rendered_threads[$thread_id] = $this->renderer->renderRoot($renderable);
        }
      }

      // Add the command that will tell the inbox which thread items to update
      $response->addCommand(new PrivateMessageInboxUpdateCommand($inbox_threads['thread_ids'], $rendered_threads));
    }
  }

  /**
   * Create Ajax Command determining whether a given username is valid to be used
   * as a member for a private message
   *
   * @param Drupal\Core\Ajax\AjaxResponse $response
   *   The response to which any commands should be attached
   */
  protected function validatePrivateMessageMemberUsername($response) {
    $username = $this->requestStack->getCurrentRequest()->get('username');
    $valid = $this->privateMessageService->validatePrivateMessageMemberUsername($username);

    $response->addCommand(new PrivateMessageMemberUsernameValidatedCommand($username, $valid));
  }

  /**
   * Create Ajax Command returning the number of unread private message threads
   * since the current user last visited the private message page.
   *
   * @param Drupal\Core\Ajax\AjaxResponse $response
   *   The response to which any commands should be attached
   */
  protected function getNewUnreadThreadCount($response) {
    $unread_thread_count = $this->privateMessageService->getUnreadThreadCount();

    $response->addCommand(new PrivateMessageUpdateUnreadThreadCountCommand($unread_thread_count));
  }

  /**
   * Load a private message thread to be dynamically inserted into the page.
   *
   * @param Drupal\Core\Ajax\AjaxResponse $response
   *   The response to which any commands should be attached
   */
  protected function loadThread($response) {
    $thread_id = $this->requestStack->getCurrentRequest()->get('id');
    if($thread_id) {
      $thread = PrivateMessageThread::load($thread_id);

      if ($thread && $thread->access('view', $this->currentUser)) {
        $this->privateMessageService->updateLastCheckTime();

        $view_builder = $this->entityManager->getViewBuilder('private_message_thread');
        $renderable = $view_builder->view($thread);
        $rendered_thread = $this->renderer->renderRoot($renderable);
        $index = array_search('private_message/private_message_thread', $renderable['#attached']['library']);
        unset($renderable['#attached']['library'][$index]);
        $response->setAttachments($renderable['#attached']);

        $response->addCommand(new PrivateMessageInsertThreadCommand($rendered_thread));
        $unread_thread_count = $this->privateMessageService->getUnreadThreadCount();
        $response->addCommand(new PrivateMessageUpdateUnreadThreadCountCommand($unread_thread_count));
      }
    }
  }
}
