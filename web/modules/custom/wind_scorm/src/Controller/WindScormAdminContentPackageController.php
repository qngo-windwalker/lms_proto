<?php

namespace Drupal\wind_scorm\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;

class WindScormAdminContentPackageController extends ControllerBase{

  public function getContent() {
    \Drupal::configFactory()->getEditable('system.performance')->set('js.preprocess', 0);

    $table = $this->getDataTableRenderable('scorm-package-tbl', "/wind-scorm/course-progress-datatable/");
    $markup = '';
    $markup .= render($table);
    return [
      '#markup' => $markup,
    ];
  }

  private function getDataTableRenderable($tableElemntId, $datatableURL) {
    $header = [
      array('data' => 'Title', 'class' => 'header-title'),
      array('data' => 'ID', 'class' => 'header-id'),
      array('data' => 'FID', 'class' => 'header-fid'),
      array('data' => 'Course', 'class' => 'header-course'),
      array('data' => 'Extracted Dir', 'class' => 'header-dir'),
      array('data' => 'Namifest ID', 'class' => 'header-manifest'),
      array('data' => 'Meta Data', 'class' => 'header-meta-data'),
    ];

    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $this->getTableRows(),
      '#empty' => t('There are no data.'),
      '#attributes' => array(
        'id' => $tableElemntId,
        'class' => array('table' ,'table-wind-theme-strip')
      ),
      '#attached' => array(
        'library' => array(
//          'wind_tincan/admin_user_course_progresses'
        ),
        'drupalSettings' => array(
          'ch_nav' => array(
            'datatableURL' => $datatableURL,
            'datatableElementId' => '#' . $tableElemntId
          )
        )
      )
    ];
  }

  private function getTableRows() {
    $result = \Drupal::database()->select('opigno_scorm_packages', 'o')
      ->fields('o', [])
      ->execute()
      ->fetchAll();

    if (!$result) {
      return [];
    }

    $rows = array();
    /** @var \Drupal\opigno_scorm\OpignoScorm $scorm_service */
    $scorm_service = \Drupal::service('opigno_scorm.scorm');
    /** @var \Drupal\opigno_scorm\OpignoScormPlayer $scorm_player */
    $scorm_player = \Drupal::service('opigno_scorm.scorm_player');
    foreach ($result as $obj) {
      $element = $scorm_player->toRendarableArray($obj);
      $title = $element['#start_sco']->title;
      $courseNode = $this->getCourseByFileId($obj->id);

      $rows[$obj->id] = array(
        $this->getLink($obj->id, $title),
        $this->getLink($obj->id, $obj->id),
        $obj->fid,
        $courseNode ? $this->getCourseLink($courseNode) : '',
        $obj->extracted_dir,
        // This info is not important. Ex: public://opigno_scorm_extracted/scorm_27/imsmanifest.xml.
//        $obj->manifest_file,
        $obj->manifest_id,
        $obj->metadata,
      );
    }
    return $rows;
  }

  private function getLink($id, $label){
    $url = Url::fromRoute(
      'wind_scorm.scorm_package.id',
      array('id' => $id),
      array(
//        'query' => ['destination' => $destination->toString()],
        'attributes' => array('class' => 'card-link text-danger')
      )
    );
    $linkContent = '<i class="fas fa-pen"></i> ' . $label;
    $renderedAnchorContent = render($linkContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

  private function getCourseByFileId($id) {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'course');
    $query->condition('field_package_file', $id, 'IN');
    $result = $query->execute();

    // If no result, return empty array.
    if (empty($result)) {
      return false;
    }
    return \Drupal\node\Entity\Node::load(array_shift($result));
  }

  private function getCourseLink(\Drupal\Core\Entity\EntityBase $courseNode) {
    $linkContent = "<span> {$courseNode->label()}</span>";
    $renderedAnchorContent = render($linkContent);
    $url = Url::fromUserInput(
      '/node/' . $courseNode->id(),
      [
        'attributes' => [
        ]
      ]
    );
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

}
