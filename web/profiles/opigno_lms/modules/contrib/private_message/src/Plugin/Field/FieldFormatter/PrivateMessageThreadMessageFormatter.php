<?php

namespace Drupal\private_message\Plugin\Field\FieldFormatter;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @FieldFormatter(
 *   id = "private_message_thread_message_formatter",
 *   label = @Translation("Private Message Messages"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class PrivateMessageThreadMessageFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The entity manager service
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

   /**
    * The current user
    *
    * @var \Drupal\Core\Session\AccountProxyInterface
    */
   protected $currentUser;

  /**
   * The CSRF token generator
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfTokenGenerator;

  /**
   * Construct a PrivateMessageThreadFormatter object
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service
   * @param |Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user
   * @param \Drupal\Core\Access\CsrfTokenGenerator $csrfTokenGenerator
   *   The CSRF token generator
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityManagerInterface $entityManager, AccountProxyInterface $currentUser, CsrfTokenGenerator $csrfTokenGenerator) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->entityManager = $entityManager;
    $this->currentUser = $currentUser;
    $this->csrfTokenGenerator = $csrfTokenGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity.manager'),
      $container->get('current_user'),
      $container->get('csrf_token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return ($field_definition->getFieldStorageDefinition()->getTargetEntityTypeId() == 'private_message_thread' && $field_definition->getFieldStorageDefinition()->getSetting('target_type') == 'private_message');
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'message_count' => 5,
      'ajax_previous_load_count' => 5,
      'message_order' => 'asc',
      'ajax_refresh_rate' => 20,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $settings = $this->getSettings();

    $summary[] = $this->t('Number of threads to show on load: @count', ['@count' => $settings['message_count']]);
    $summary[] = $this->t('Number of threads to show when clicking load previous: @count', ['@count' => $settings['ajax_previous_load_count']]);
    $summary[] = $this->t('Order of messages: @order', ['@order' => $this->translateKey('order', $settings['message_order'])]);
    $summary[] = $this->t('Ajax refresh rate: @count seconds', ['@count' => $settings['ajax_refresh_rate']]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['message_count'] = [
      '#title' => $this->t('Message Count'),
      '#type' => 'number',
      '#default_value' => $this->getSetting('message_count'),
      '#description' => $this->t('The number of messages to display on load'),
    ];

    $element['ajax_previous_load_count'] = [
      '#title' => $this->t('Load Previous Ajax Count'),
      '#type' => 'number',
      '#default_value' => $this->getSetting('ajax_previous_load_count'),
      '#description' => $this->t('The number of previous messages to load using ajax when clicking the load previous link'),
    ];

    $element['ajax_refresh_rate'] = [
      '#title' => $this->t('Ajax refresh rate'),
      '#type' => 'number',
      '#min' => 5,
      '#default_value' => $this->getSetting('ajax_refresh_rate'),
      '#description' => $this->t('The number of seconds between checks for new messages. Note that a lower number will cause more requests, use more bandwidth, and cause more strain on the server.'),
    ];

    $element['message_order'] = [
      '#type' => 'radios',
      '#title' => $this->t('Message direction'),
      '#options' => [
        'asc' => $this->translateKey('order', 'asc'),
        'desc' => $this->translateKey('order', 'desc'),
      ],
      '#description' => $this->t('Whether to show messages first to last, or last to first'),
      '#default_value' => $this->getSetting('message_order'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    $private_message_thread = $items->getEntity();

    $element = [
      '#prefix' => '<div class="private-message-thread-messages">',
      '#suffix' => '</div>',
    ];

    $view_builder = $this->entityManager->getViewBuilder('private_message');

    $user = User::load($this->currentUser->id());
    $messages = $private_message_thread->filterUserDeletedMessages($user);
    $messages = array_slice($messages, -1 * $this->getSetting('message_count'));

    foreach ($messages as $index => $message) {
      $element[$message->id()] = $view_builder->view($message, 'full');
    }

    if ($this->getSetting('message_order') == 'desc') {
      $element = array_reverse($element);
    }

    $new_url = Url::fromRoute('private_message.ajax_callback', ['op' => 'get_new_messages']);
    $token = $this->csrfTokenGenerator->get($new_url->getInternalPath());
    $new_url->setOptions(['absolute' => TRUE, 'query' => ['token' => $token]]);

    $prev_url = Url::fromRoute('private_message.ajax_callback', ['op' => 'get_old_messages']);
    $token = $this->csrfTokenGenerator->get($prev_url->getInternalPath());
    $prev_url->setOptions(['absolute' => TRUE, 'query' => ['token' => $token]]);

    $load_url = Url::fromRoute('private_message.ajax_callback', ['op' => 'load_thread']);
    $load_token = $this->csrfTokenGenerator->get($load_url->getInternalPath());
    $load_url->setOptions(['absolute' => TRUE, 'query' => ['token' => $load_token]]);

    $element['#attached']['drupalSettings']['privateMessageThread'] = [
      'newMessageCheckUrl' => $new_url->toString(),
      'previousMessageCheckUrl' => $prev_url->toString(),
      'messageOrder' => $this->getSetting('message_order'),
      'refreshRate' => $this->getSetting('ajax_refresh_rate') * 1000,
      'loadThreadUrl' => $load_url->toString(),
    ];

    $element['#attached']['library'][] = 'private_message/private_message_thread';

    return $element;
  }

  private function translateKey($type, $value) {
    if ($type == 'order') {
      $keys = [
        'asc' => $this->t('Ascending'),
        'desc' => $this->t('Descending'),
      ];

      return isset($keys[$value]) ? $keys[$value] : $value;
    }

    return $value;
  }
}
