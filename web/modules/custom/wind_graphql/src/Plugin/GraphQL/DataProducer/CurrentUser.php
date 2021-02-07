<?php

namespace Drupal\wind_graphql\Plugin\GraphQL\DataProducer;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\wind_graphql\Wrappers\QueryConnection;
use GraphQL\Error\UserError;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Gets the ID of current user.
 * @see https://drupal-graphql.gitbook.io/graphql/v/8.x-4.x/data-producers/custom
 *
 * @DataProducer(
 *   id = "current_user",
 *   name = @Translation("Current user"),
 *   description = @Translation("Current logged in user."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Current user")
 *   )
 * )
 */
class CurrentUser extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user')
    );
  }

  /**
   * UserRegister constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currentUser = $current_user;
  }

  /**
   * Returns current user id.
   *
   * @return int
   *   The current user id.
   */
  public function resolve() {
    return $this->currentUser->id();
  }
}
