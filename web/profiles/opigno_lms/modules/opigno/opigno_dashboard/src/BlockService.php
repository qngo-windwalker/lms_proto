<?php

namespace Drupal\opigno_dashboard;

use Drupal\block\Entity\Block;

/**
 * Class BlockService.
 */
class BlockService implements BlockServiceInterface
{
    /**
     * Constructs a new BlockService object.
     */
    public function __construct()
    {
    }

    public function getAllBlocks()
    {
        $blockManager = \Drupal::service('plugin.manager.block');

        return $blockManager->getDefinitions();
    }

    public function getAvailableBlocks()
    {
        $blocks = $this->getAllBlocks();
        $availables = \Drupal::config('opigno_dashboard.settings')->get('blocks');

        foreach ($blocks as $key1 => &$block) {
            if (!isset($availables[$key1])
              || (isset($availables[$key1]) && !$availables[$key1]['available'])
            ) {
                unset($blocks[$key1]);
            } else {
                foreach ($block as $key2 => &$value) {
                    if (is_object($value)) {
                        $value = $value->render();
                    }
                }

                $blocks[$key1]['id'] = $key1;

                unset(
                  $blocks[$key1]['config_dependencies'],
                  $blocks[$key1]['class'],
                  $blocks[$key1]['provider'],
                  $blocks[$key1]['category'],
                  $blocks[$key1]['deriver'],
                  $blocks[$key1]['context']
                );
            }
        }

        return array_values($blocks);
    }

    public function getDashboardBlocksContents()
    {
        $manager = \Drupal::service('plugin.manager.block');

        $ids = [];
        foreach ($this->getAvailableBlocks() as $key => $block) {
            $ids[] = $block['id'];
        }

        $blocks = [];
        foreach ($ids as $id) {
            if ($block = \Drupal\block\Entity\Block::load($this->sanitizeId($id))) {
                $render = \Drupal::entityTypeManager()->getViewBuilder('block')->view($block);
                $blocks[$id] = \Drupal::service('renderer')->renderRoot($render);
            }
        }

        return $blocks;
    }

    public function createBlocksInstances()
    {
        $items = $this->getAvailableBlocks();
        $config = \Drupal::configFactory();

        foreach ($items as $item) {
            $id = $this->sanitizeId($item['id']);

            if (!\Drupal\block\Entity\Block::load($id)) {
                $settings = [
                    'plugin' => $item['id'],
                    'region' => 'content',
                    'id' => $id,
                    'theme' => $config->get('system.theme')->get('default'),
                    'label' => t('Dashboard: ') . $item['admin_label'],
                    'visibility' => [
                        'request_path' => [
                            'id' => 'request_path',
                            'pages' => '<front>',
                            'negate' => false,
                            'context_mapping' => []
                        ]
                    ],
                    'weight' => 0,
                ];

                $values = [];
                foreach (['region', 'id', 'theme', 'plugin', 'weight', 'visibility'] as $key) {
                    $values[$key] = $settings[$key];
                    // Remove extra values that do not belong in the settings array.
                    unset($settings[$key]);
                }
                foreach ($values['visibility'] as $id => $visibility) {
                    $values['visibility'][$id]['id'] = $id;
                }
                $values['settings'] = $settings;
                $block = Block::create($values);

                $block->save();
            }
        }
    }

    public function sanitizeId($id)
    {
        return 'dashboard_' . str_replace(':', '_', $id);
    }
}
