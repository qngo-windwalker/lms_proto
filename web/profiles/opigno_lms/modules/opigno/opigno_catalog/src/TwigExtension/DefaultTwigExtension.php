<?php

namespace Drupal\opigno_catalog\TwigExtension;

use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Render\Markup;

/**
 * Class DefaultTwigExtension.
 */
class DefaultTwigExtension extends \Twig_Extension
{

   /**
    * {@inheritdoc}
    */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'opigno_catalog_get_style',
                [$this, 'get_row_style']
            ),
            new \Twig_SimpleFunction(
                'opigno_catalog_is_member',
                [$this, 'is_member']
            ),
            new \Twig_SimpleFunction(
                'opigno_catalog_is_started',
                [$this, 'is_started']
            ),
            new \Twig_SimpleFunction(
                'opigno_catalog_get_default_image',
                [$this, 'get_default_image']
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opigno_catalog.twig.extension';
    }

    public function get_row_style()
    {
        $style = \Drupal::service('opigno_catalog.get_style')->getStyle();

        return ($style == 'line') ? 'style-line' : 'style-block' ;
    }

    public function is_member($group_id)
    {
        $group = \Drupal\group\Entity\Group::load($group_id);
        $account = \Drupal::currentUser();

        return (bool) $group->getMember($account);
    }

    public function is_started($group_id)
    {
        $group = \Drupal\group\Entity\Group::load($group_id);
        $account = \Drupal::currentUser();

        return (bool) opigno_learning_path_started($group, $account);
    }

    public function get_default_image($type)
    {
        $base_url = \Drupal::urlGenerator()->generateFromRoute('<front>', [], ['absolute' => TRUE]);
        $path = \Drupal::service('module_handler')->getModule('opigno_catalog')->getPath();
        switch ($type) {
          case 'course':
            $img = '<img src="' . $base_url . $path . '/img/img_course.png" alt="">';
            break;

          case 'module':
            $img = '<img src="' . $base_url . $path . '/img/img_module.png" alt="">';
            break;

          case 'learning_path':
            $img = '<img src="' . $base_url . $path . '/img/img_training.png" alt="">';
            break;

          default:
            $img = null;
            break;
        }

        return Markup::create($img);
    }
}
