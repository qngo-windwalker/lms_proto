<?php

namespace Drupal\wind_lms\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * Note: `schema = "wind"` is defined at wind_graphql/graphql/wind.graphqls
 * Do not change PHP Annoations below
 *
 * @SchemaExtension(
 *   id = "windlms_extension",
 *   name = "Windlms extension",
 *   description = "A simple extension that adds node related fields.",
 *   schema = "wind"
 * )
 */
class WindLMSSchemaExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    $this->addQueryFields($registry, $builder);
    $this->addPageFields($registry, $builder);
//    $this->addCoursePackageFields($registry, $builder);
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addPageFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Course', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Course', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent()),
        $builder->produce('uppercase')
          ->map('string', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('Course', 'field_package_file', $builder->compose(
        $builder->produce('entity_reference')->map('entity', $builder->fromParent())->map('field', $builder->fromValue('field_package_file'))
    ));

    // @see https://drupal-graphql.gitbook.io/graphql/v/8.x-4.x/queries/references
    $registry->addFieldResolver('Course', 'packages',
      $builder->produce('entity_reference')
        ->map('entity', $builder->fromParent())
        ->map('field', $builder->fromValue('field_package_file'))
    );

    $registry->addFieldResolver('CoursePackage', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('CoursePackage', 'name',
      $builder->produce('entity_label')
        ->map('entity', $builder->fromParent())
    );

  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addQueryFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'course',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['course']))
        ->map('id', $builder->fromArgument('id'))
    );
  }

  /**
   * @param \Drupal\graphql\GraphQL\ResolverRegistryInterface $registry
   * @param \Drupal\graphql\GraphQL\ResolverBuilder $builder
   */
  protected function addCoursePackageFields(ResolverRegistryInterface $registry, ResolverBuilder $builder) {
    $registry->addFieldResolver('Query', 'course',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['course']))
        ->map('id', $builder->fromArgument('id'))
    );
  }
}
