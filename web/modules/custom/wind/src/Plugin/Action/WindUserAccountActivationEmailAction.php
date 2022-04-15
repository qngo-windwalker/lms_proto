<?php

namespace Drupal\wind\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * To create a new View bulk action:
 *   Go to: admin/config/development/configuration/single/import
 *   Select Action in Configuration type dropdown
 *   Add (Example can be found at system.action.enroll_course_action.yml) :
langcode: en
status: true
dependencies:
  module:
    - user
id: wind_send_user_account_activation_action
label: 'Send the Account Activation email to the selected user(s)'
type: user
plugin: wind_send_user_account_activation_action
configuration: {  }

 *    Troubleshhoot: If this action does NOT appear in the list, check the following:
 *      Check Views: People (User) @ /admin/structure/views/view/user_admin_people > Field > User: Bulk update (Bulk update)
 *
 *    example: Drupal\user\Plugin\Action\CancelUser
 *    more info: https://www.drupal.org/node/2892204
 */


/**
 * Send the Account Activation email to the selected user(s)
 *
 * @Action(
 *   id = "wind_send_user_account_activation_action",
 *   label = @Translation("Send the Account Activation email to the selected user(s)"),
 *   type = "user",
 *   confirm_form_route_name = "wind.user.multiple_user_account_activation_email",
 * )
 */
class WindUserAccountActivationEmailAction extends ActionBase implements ContainerFactoryPluginInterface  {

  /**
   * The tempstore factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a CancelUser object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store_factory, AccountInterface $current_user) {
    $this->currentUser = $current_user;
    $this->tempStoreFactory = $temp_store_factory;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tempstore.private'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $entities) {
    // @see WindLMSUserMultipleEnroll::buildForm
    $this->tempStoreFactory->get('wind_email_account_activation_operations_send')->set($this->currentUser->id(), $entities);
    // Process the entity is being handled by WindLMSUserMultipleEnroll.php
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    $this->executeMultiple([$object]);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\user\UserInterface $object */
    $access = $object->status->access('edit', $account, TRUE)
      ->andIf($object->access('update', $account, TRUE));

    return $return_as_object ? $access : $access->isAllowed();
  }

}
