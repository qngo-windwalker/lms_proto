<?php

namespace Drupal\wind_tincan\Plugin\Block;

use Drupal\wind_tincan\Entity\TincanStatement;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\Group;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Serialization\Json;

/**
 * Provides a 'My Training' block.
 *
 * @Block(
 *  id = "wind_tincan",
 *  admin_label = @Translation("My Training Block"),
 * )
 */
class WindTincanMyTrainingBlock extends Blockbase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $title = isset($this->configuration['title']) ? $this->configuration['title'] : 'My Training';
    $build = wind_gen_block_card_template($title);
    $user = \Drupal::currentUser();
    $steps = $this->getAllUserCourses($user);
    $emptyClass = empty($steps) ? 'empty-table' : '';
    $trainingBuild = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['lp_steps_block'],
      ],
      [
        '#type' => 'table',
        '#header' => [
          t('Name'),
          t('Status'),
          t('Certificate'),
        ],
        '#rows' => $steps,
        '#attributes' => [
          'class' => ['lp_steps_block_table', 'table', $emptyClass, 'block-card-table', 'mb-0'],
        ],
        '#empty' => t('There are no courses available.')
      ],
      '#attached' => [
        'library' => [
          'wind_tincan/my_training_block',
        ],
      ],
    ];
    $build['card_body']['card_text_container']['#markup'] = render($trainingBuild);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $formState) {
    $config = $this->getConfiguration();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $formState) {
    $this->configuration['wind_tincan'] = $formState->getValues('wind_tincan');
    return $form;
  }

  protected function buildCourseLink($title, $course_folder) {
    $linkContent = '<i class="fas fa-external-link-alt align-self-center pr-1"></i> ' . "<span> {$title}</span>";
    $renderedAnchorContent = render($linkContent);
    $url = Url::fromUserInput(
      '/course/' . $course_folder,
      [
        'attributes' => [
          'data-coure-href' => _ch_nav_gen_course_link($course_folder),
          'class' => array('wind-scorm-popup-link', 'd-flex')
        ]
      ]
    );
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

  private function getAllUserCourses(\Drupal\Core\Session\AccountProxyInterface $user) {
    $rows = array();
    $coursesData = _wind_tincan($user);
    foreach ($coursesData as $courseData) {
      $rows[] = $this->buildCourseRow($courseData);
    }
    return $rows;
  }

  protected function buildCourseRow($courseData) {
    $title = $courseData['title'];
    $course_folder = $courseData['folder'];
    $TC_COURSE_ID = $courseData['tincan_course_id'];
    $progress = $courseData['progress'];
    $certificateLink = $progress != 'Completed' ? 'N/A' : $this->getCourseCertificate($courseData);
    return [
      'data' => array(
        $this->buildCourseLink($title, $course_folder),
        $progress,
        $certificateLink,
      ),
      'class' => array('course-row'),
      'data-tincan-id' =>$TC_COURSE_ID
    ];
  }

  private function getCourseCertificate($courseData) {
    if(!isset($courseData['statement'])){
      return '';
    }
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('wind_tincan')->getPath();
    $linkContent = '<img width="26" src="' . $module_path . '/img/certificate_icon.png">';
    $renderedAnchorContent = render($linkContent);
    $url = Url::fromUserInput('/certificate/' . $courseData['statement']->get('statement_id')->value, ['attributes' => ['target' => '_blank']]);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $url)->toString();
  }

}
