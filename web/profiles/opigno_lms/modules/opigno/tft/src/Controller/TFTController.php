<?php

namespace Drupal\tft\Controller;

use \Drupal\Component\Utility\UrlHelper;
use \Drupal\Core\Controller\ControllerBase;
use \Drupal\Core\Url;
use \Drupal\file\Entity\File;
use \Drupal\group\Entity\Group;
use \Drupal\media\Entity\Media;
use \Drupal\taxonomy\Entity\Term;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;
use \Symfony\Component\HttpFoundation\JsonResponse;
use \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TFTController extends ControllerBase {

  /**
   * Format a folder or file link.
   *
   * @param string $title
   *        The link title
   * @param int $id
   *        Either the taxonomy term tid or the media id
   * @param string $mime
   *        The mime type of the file (for a folder, use 'folder')
   *
   * @return array
   *        Render array with the formatted link
   */
  protected function link($title, $id, $mime) {
    if ($mime == 'folder') {
      return [
        'data' => [
          '#type' => 'link',
          '#title' => $title,
          '#url' => Url::fromUri("internal:#term/$id"),
          '#attributes' => [
            'class' => 'folder-folder-link',
            'id' => "tid-$id",
          ],
        ],
      ];
    }
    else {
      // Get the filefield icon.
      $icon_class = file_icon_class($mime);

      return [
        'data' => [
          '#type' => 'link',
          '#title' => $title,
          '#url' => Url::fromUri("internal:/tft/download/file/$id"),
          '#attributes' => [
            'class' => "file $icon_class",
            'target' => '_blank',
          ],
        ],
      ];
    }
  }

  /**
   * Return an <ul> with links for the current folder.
   * Links include:
   *  - "go to parent"
   *  - "edit permissions"
   *  - "reorder folders"
   *
   * @param int $tid
   *        The term tid
   *
   * @return array
   *        The render array
   */
  protected function operation_links($type, $id, $media = NULL, $gid = NULL) {
    $links = [];
    $tempstore = \Drupal::service('user.private_tempstore')->get('tft');
    $query = 'destination=' . $tempstore->get('q');

    switch ($type) {
      case 'folder':
        $group = Group::load($gid);
        $edit = FALSE;

        // Hide edit link if the user has no access.
        if (\Drupal::currentUser()->hasPermission(TFT_ADD_TERMS)
          || ($group && $group->hasPermission(TFT_ADD_TERMS, \Drupal::currentUser()))) {
          $edit = TRUE;
          $links[] = [
            '#type' => 'link',
            '#title' => t("edit"),
            '#url' => Url::fromUri("internal:/tft/term/edit/$id?" . $query),
            '#attributes' => [
              'class' => 'ops-link term-edit-link',
            ],
          ];
        }

        if (\Drupal::currentUser()->hasPermission(TFT_DELETE_TERMS)
          || ($group && $group->hasPermission(TFT_DELETE_TERMS, \Drupal::currentUser()))) {
          if ($edit) {
            $links[] = [
              '#markup' => ' | ',
            ];
          }

          $links[] = [
            '#type' => 'link',
            '#title' => t("delete"),
            '#url' => Url::fromUri("internal:/tft/term/delete/$id?" . $query),
            '#attributes' => [
              'class' => 'ops-link term-edit-link',
            ],
          ];
        }
        break;

      case 'file':
        /** @var \Drupal\media\Entity\Media $media */
        if ($media->access('update')) {
          $links[] = [
            '#type' => 'link',
            '#title' => t("edit"),
            '#url' => Url::fromUri("internal:/media/$id/edit?" . $query),
            '#attributes' => [
              'class' => 'ops-link node-edit-link',
            ],
          ];

          $links[] = [
            '#markup' => ' | ',
          ];
        }

        $links[] = [
          '#type' => 'link',
          '#title' => t("more info"),
          '#url' => Url::fromUri("internal:/media/$id"),
          '#attributes' => [
            'class' => 'ops-link',
          ],
        ];
        break;
    }

    return [
      'data' => $links,
    ];
  }

  /**
   * Get the folder content and return it in an array form for the theme_table call
   *
   * @param int $tid
   *        The taxonomy term tid
   *
   * @return array
   *        The folder content
   */
  protected function get_content($tid, $gid = NULL) {
    $content = [];

    $elements = _tft_folder_content($tid, FALSE, $gid);

    foreach ($elements as $element) {
      if ($element['type'] == 'term') {
        $content[] = [
          $this->link($element['name'], $element['id'], 'folder'),
          '',
          '',
          t("Folder"),
          $this->operation_links('folder', $element['id'], NULL, $gid),
        ];
      }
      else {
        /** @var \Drupal\media\Entity\Media $media */
        $media = Media::load($element['id']);
        $fids = $media->get('tft_file')->getValue();
        $fid = reset($fids)['target_id'];
        $file = File::load($fid);
        $user = $media->getOwner();

        $file_name = $file->getFilename();
        $file_name_parts = explode('.', $file_name);
        $file_extension = end($file_name_parts);

        $content[] = [
          $this->link($element['name'], $element['id'], $file->getMimeType()),
          $user->getDisplayName(),
          date('d/m/Y H:i', $media->getChangedTime()),
          t('@type file', [
            '@type' => strtoupper($file_extension),
          ]),
          $this->operation_links('file', $element['id'], $media, $gid),
        ];
      }
    }

    // Fix error in jquery.tablesorter if table is empty.
    if (empty($elements)) {
      $content[] = [
        '',
        '',
        '',
        '',
        '',
      ];
    }

    return $content;
  }

  /**
   * Render the add file and add folder links.
   *
   * @param int $tid = 0
   *        The term tid of the current folder, or 0 for root
   */
  protected function add_content_links($tid = 0, $gid = NULL) {
    $items = [];

    $tempstore = \Drupal::service('user.private_tempstore')->get('tft');
    $add_file_query = ['destination' => $tempstore->get('q')];
    $add_term_query = ['destination' => $tempstore->get('q')];

    // Do we have a tid ?
    if ($tid) {
      $add_file_query['tid'] = $tid;
      $add_term_query['parent'] = $tid;

      if (!$gid) {
        $gid = _tft_get_group_gid($tid);
      }
    }

    $group = Group::load($gid);

    // Can the user create files ?
    if (\Drupal::currentUser()->hasPermission('create media')) {
      // Can they add files in this context ?
      if (\Drupal::currentUser()->hasPermission(TFT_ADD_FILE)
        || ($group && $group->hasPermission(TFT_ADD_FILE, \Drupal::currentUser()))) {
        $query = UrlHelper::buildQuery(array_reverse($add_file_query));
        $items[] = [
          '#wrapper_attributes' => [
            'class' => 'folder-add-content-link',
          ],
          '#type' => 'link',
          '#title' => t("Add a file"),
          '#url' => Url::fromUri("internal:/media/add/tft_file?$query"),
          '#attributes' => [
            'id' => 'add-child-file',
          ],
        ];
      }
    }

    // Can the user add terms anywhere, only under Group or never ?
    if (\Drupal::currentUser()->hasPermission(TFT_ADD_TERMS)
      || ($group && $group->hasPermission(TFT_ADD_TERMS, \Drupal::currentUser()))) {
      $query = UrlHelper::buildQuery(array_reverse($add_term_query));
      $items[] = [
        '#wrapper_attributes' => [
          'class' => 'folder-add-content-link',
        ],
        '#type' => 'link',
        '#title' => t("Add a folder"),
        '#url' => Url::fromUri("internal:/tft/term/add?$query"),
        '#attributes' => [
          'id' => 'add-child-folder',
        ],
      ];
    }

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#attributes' => [
        'id' => 'folder-add-content-links',
      ],
      '#items' => $items,
    ];
  }

  /**
   * Get the folder content in HTML table form
   *
   * @param int $tid
   *        The folder taxonomy term tid
   *
   * @return array
   *        The render array
   */
  protected function content_table($tid, $gid = NULL) {
    $headers = [
      [
        'id' => 'table-th-name',
        'data' => t('Name'),
      ],
      [
        'id' => 'table-th-loaded-by',
        'data' => t('Loaded by'),
      ],
      [
        'id' => 'table-th-date',
        'data' => t('Last modified'),
      ],
      [
        'id' => 'table-th-type',
        'data' => t('Type'),
      ],
      [
        'id' => 'table-th-ops',
        'data' => t('Operations'),
      ],
    ];

    return [
      [
        '#type' => 'table',
        '#header' => $headers,
        '#rows' => $this->get_content($tid, $gid),
      ],
      $this->add_content_links($tid, $gid),
    ];
  }

  /**
   * Return an <ul> with links for the current folder.
   *
   * Links include:
   *  - "go to parent"
   *  - "edit permissions"
   *  - "reorder folders"
   *
   * @param int $tid
   *        The term tid.
   *
   * @return array
   *        The render array
   */
  protected function get_folder_operation_links($tid, $gid = NULL) {
    $items = [];

    // First link: got to parent.
    $parent_tid = _tft_get_parent_tid($tid);

    $tempstore = \Drupal::service('user.private_tempstore')->get('tft');
    $root_tid = $tempstore->get('root_tid');
    $query = 'destination=' . $tempstore->get('q');

    $disabled = FALSE;

    if ($parent_tid > -1 && $tid != $root_tid) {
      if (!_tft_term_access($parent_tid)) {
        $disabled = TRUE;
      }
    }
    else {
      $disabled = TRUE;
    }

    $class = $disabled ? 'disabled' : '';
    $fragment = $disabled ? '#' : "#term/$parent_tid";

    $items[] = [
      '#wrapper_attributes' => [
        'id' => 'tft-back',
        'class' => 'folder-menu-ops-link first',
      ],
      '#type' => 'link',
      '#title' => t("parent folder"),
      '#url' => Url::fromUri("internal:$fragment"),
      '#attributes' => [
        'class' => $class,
        'id' => 'tft-back-link',
      ],
    ];

    // Third link: reorder child terms.
    $uri = "/tft/terms/reorder/$tid?$query";
    $group = Group::load($gid);

    if (\Drupal::currentUser()->hasPermission(TFT_REORDER_TERMS)
      || ($group && $group->hasPermission(TFT_REORDER_TERMS, \Drupal::currentUser()))) {
      $items[] = [
        '#wrapper_attributes' => [
          'id' => 'manage-folders',
          'class' => 'folder-menu-ops-link',
        ],
        '#type' => 'link',
        '#title' => t("reorder elements"),
        '#url' => Url::fromUri('internal:' . $uri),
      ];
    }

    return [
      '#theme' => 'item_list',
      '#list_type' => 'ul',
      '#attributes' => [
        'class' => 'tabs primary',
        'id' => 'folder-menu-ops-links',
      ],
      '#items' => $items,
    ];
  }

  /**
   * File explorer.
   */
  protected function tft($tid = 'all', $gid = NULL) {
    if ($tid == 'all' || !(int) $tid) {
      if (\Drupal::currentUser()->hasPermission(TFT_ACCESS_FULL_TREE)) {
        $tid = 0;
      }
      else {
        throw new AccessDeniedHttpException();
      }
    }

    // Check if the user has access to this tree.
    if (!_tft_term_access($tid)) {
      throw new AccessDeniedHttpException();
    }

    if ($tid) {
      $term = Term::load($tid);
      $name = $term->getName();
    }
    else {
      $name = t("Root");
    }

    $tempstore = \Drupal::service('user.private_tempstore')->get('tft');

    // Store the URL query. Need the current path for some AJAX callbacks.
    $tempstore->set('q', \Drupal::service('path.current')->getPath());

    // Store the current term tid.
    $tempstore->set('root_tid', $tid);

    $path = drupal_get_path('module', 'tft');

    return [
      // Get the themed title bar.
      [
        '#theme' => 'tft_folder_menu',
        '#name' => $name,
        '#path' => $path,
        '#ops_links' => $this->get_folder_operation_links($tid, $gid),
      ],
      // Prepare the folder content area.
      [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'folder-content-container',
        ],
        'content' => $this::content_table($tid, $gid),
      ],
      // Add CSS and Javascript files.
      '#attached' => [
        'library' => [
          'tft/tft',
        ],
        'drupalSettings' => [
          'tftDirectory' => $path,
        ],
      ],
    ];
  }

  public function downloadFile($id) {
    /** @var \Drupal\media\Entity\Media $media */
    $media = Media::load($id);

    if (!$media) {
      throw new NotFoundHttpException();
    }

    if (!$media->access('view')) {
      throw new AccessDeniedHttpException();
    }

    $fids = $media->get('tft_file')->getValue();
    $fid = reset($fids)['target_id'];
    $file = File::load($fid);

    if (!$file) {
      throw new NotFoundHttpException();
    }

    if (!$file->access('view')) {
      throw new AccessDeniedHttpException();
    }

    $file_name = $file->getFilename();
    $headers = [
      'Content-Type' => $file->getMimeType(),
      'Content-Disposition' => 'attachment; filename="' . $file_name . '"',
    ];

    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
      $headers['Cache-Control'] = 'must-revalidate, post-check=0, pre-check=0';
      $headers['Pragma'] = 'public';
    }
    else {
      $headers['Pragma'] = 'no-cache';
    }

    return new BinaryFileResponse($file->getFileUri(), 200, $headers);
  }

  public function listDirectory($tid) {
    return $this->tft($tid);
  }

  public function listGroup($group) {
    $group = Group::load($group);

    if (!$group) {
      throw new NotFoundHttpException();
    }

    if (!$group->access('view')) {
      throw new AccessDeniedHttpException();
    }

    $tid = _tft_get_group_tid($group->id());

    if (!$tid) {
      return [
        '#markup' => t("No term was found for this group ! Please contact your system administrator.")
      ];
    }

    return $this->tft($tid, $group->id());
  }

  public function ajaxGetFolder() {
    $tid = $_GET['tid'];

    if (!$tid && $tid != 0) {
      return new JsonResponse([
        'error' => 1,
        'data' => t("No valid identifier received"),
      ]);
    }
    elseif (!_tft_term_access($tid)) {
      return new JsonResponse([
        'error' => 1,
        'data' => t("You do not have access to this folder."),
      ]);
    }

    $gid = _tft_get_group_gid($tid);
    $renderer = \Drupal::service('renderer');

    $data = $this->content_table($tid, $gid);
    $ops_links = $this->get_folder_operation_links($tid, $gid);

    return new JsonResponse([
      'data' => $renderer->renderRoot($data),
      'parent' => _tft_get_parent_tid($tid, $gid),
      'ops_links' => $renderer->renderRoot($ops_links),
    ]);
  }

}
