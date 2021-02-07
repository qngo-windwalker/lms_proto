<?php

namespace Drupal\wind_graphql\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\wind_graphql\Wrappers\QueryConnection;

/**
 * Add "Wind schema" to the Schema list of option at admin/config/graphql/servers/manage/graphql_server
 * @see Drupal\graphql_examples\Plugin\GraphQL\Schema\ExampleSchema
 * Do not delete codes starting '@Schema' -- PHP Annotations.
 *
 * @Schema(
 *   id = "wind",
 *   name = "Wind schema"
 * )
 */
class WindSchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry();

    $this->addQueryFields($registry, $builder);
    $this->addArticleFields($registry, $builder);

    $this->addCurrentUserQueryFields($registry, $builder);

    $this->addNode($registry, $builder);
    $this->addFile($registry, $builder);

    // Re-usable connection type fields.
    $this->addConnectionFields('ArticleConnection', $registry, $builder);
    $this->addConnectionFields('NodeConnection', $registry, $builder);
    $this->addConnectionFields('FileConnection', $registry, $builder);

    return $registry;
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addArticleFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Article', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Article', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent()),
        $builder->produce('uppercase')
          ->map('string', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('Article', 'author',
      $builder->compose(
        $builder->produce('entity_owner')
          ->map('entity', $builder->fromParent()),
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent())
      )
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addQueryFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'article',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['article']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'articles',
      $builder->produce('query_articles')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );
  }

  /**
   * @param string $type
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addConnectionFields($type, ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver($type, 'total',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->total();
      })
    );

    $registry->addFieldResolver($type, 'items',
      $builder->callback(function (QueryConnection $connection) {
        return $connection->items();
      })
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  private function addNode(ResolverRegistry $registry, ResolverBuilder $builder) {
    $this->addNodeQueryFields($registry, $builder);
    $this->addNodeFields($registry, $builder);
  }

  private function addNodeQueryFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'node',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
//        ->map('bundles', $builder->fromValue(['entity']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'nodes',
      $builder->produce('query_nodes')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addNodeFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Node', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Node', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent()),
        $builder->produce('uppercase')
          ->map('string', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('Node', 'author',
      $builder->compose(
        $builder->produce('entity_owner')
          ->map('entity', $builder->fromParent()),
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent())
      )
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addCurrentUserQueryFields(ResolverRegistry $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'currentUser', $builder->compose(
      $builder->produce('current_user'),
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('user'))
        ->map('id', $builder->fromParent())
    ));
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistry $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addFile(ResolverRegistry $registry, ResolverBuilder $builder) {
    // Query ---------------------------
    $registry->addFieldResolver('Query', 'file',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('file'))
        //        ->map('bundles', $builder->fromValue(['entity']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'files',
      $builder->produce('query_files')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );

    // Field ---------------------------
    $registry->addFieldResolver('File', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('File', 'name',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );
  }

}
