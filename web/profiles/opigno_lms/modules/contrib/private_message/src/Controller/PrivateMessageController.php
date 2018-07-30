<?php

namespace Drupal\private_message\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\private_message\Entity\PrivateMessage;
use Drupal\private_message\Entity\PrivateMessageThread;
use Drupal\private_message\Service\PrivateMessageServiceInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PrivateMessageController extends ControllerBase implements PrivateMessageControllerInterface {

  /**
   * The current user
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The entity manager service
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The form builder interface
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The user data service
   *
   * @var \Drupal\user\UserDataInterface
   */
  protected $userData;

  /**
   * The private message service
   *
   * @var \Drupal\private_message\Service\PrivateMessageServiceInterface
   */
  protected $privateMessageService;

  /**
   * Constructs a PrivateMessageForm object
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The form builder service
   * @param \Drupal\user\UserDataInterface $userData
   *   The user data service
   * @param \Drupal\private_message\Service\PrivateMessageServiceInterface $privateMessageService
   *   The private message service
   */
  public function __construct(AccountProxyInterface $currentUser, EntityManagerInterface $entityManager, FormBuilderInterface $formBuilder, UserDataInterface $userData, PrivateMessageServiceInterface $privateMessageService) {
    $this->currentUser = $currentUser;
    $this->entityManager = $entityManager;
    $this->formBuilder = $formBuilder;
    $this->userData = $userData;
    $this->privateMessageService = $privateMessageService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user'),
      $container->get('entity.manager'),
      $container->get('form_builder'),
      $container->get('user.data'),
      $container->get('private_message.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function privateMessagePage() {
    $this->privateMessageService->updateLastCheckTime();

    $user = User::load($this->currentUser->id());

    $private_message_thread = $this->privateMessageService->getFirstThreadForUser($user);

    if ($private_message_thread) {
      $view_builder = $this->entityManager->getViewBuilder('private_message_thread');
      // No wrapper is provided, as the full view mode of the entity already provides
      // the #private-message-page wrapper
      $page = $view_builder->view($private_message_thread);
    }
    else {
      $page = [
        '#prefix' => '<div id="private-message-page">',
        '#suffix' => '</div>',
        'no_threads' => [
          '#prefix' => '<p>',
          '#suffix' => '</p>',
          '#markup' => $this->t('You do not have any messages'),
        ],
      ];
    }

    return $page;
  }

  /**
   * {@inheritdoc}
   */
  public function pmSettingsPage() {
    return [
      '#markup' => $this->t('Private Messages'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function pmThreadSettingsPage() {
    return [
      '#markup' => $this->t('Private Message Threads'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function configPage() {
    return [
      '#prefix' => '<div id="private_message_configuration_page">',
      '#suffix' => '</div>',
      'form' => $this->formBuilder->getForm('Drupal\private_message\Form\ConfigForm'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function adminUninstallPage() {
    return [
      '#prefix' => '<div id="private_message_admin_uninstall_page">',
      '#suffix' => '</div>',
      'form' => $this->formBuilder->getForm('Drupal\private_message\Form\AdminUninstallForm'),
    ];
  }
}
