<?php

namespace Drupal\wind_lms\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;
use Drupal\group\Entity\Group;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a 'WindLMSCourseUserCountBlock' block.
 *
 * @Block(
 *  id = "wind_lmd_course_user_count_block",
 *  admin_label = @Translation("Single Course User Count Block"),
 * )
 */
class WindLMSCourseUserCountBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $markup = '';
    if (isset($this->configuration['gid'])) {
      $group = Group::load($this->configuration['gid']);
      $markup .= $this->renderLearningPath($group);
    }

    if (isset($this->configuration['groups'])) {
      /** @var Group[] $groups */
      $groups = $this->configuration['groups'];
      foreach ($groups as $gid => $group){
        $markup .= $this->renderLearningPath($group);
      }
    }

    if (empty($markup)) {
      $markup = '<h3 class="card-title">' . t('No Course Subscription') . '</h3>';
    }

    $build = wind_gen_block_card_template('Course');
    $build['card_body']['card_text_container']['#markup'] = $markup;

    return $build;
  }

  private function renderLearningPath(Group $group) {
    $currentUser = \Drupal::currentUser();
    $orgGroup = ch_nav_get_user_group_organization($currentUser);
    if ($orgGroup) {
      $totalLicenses = ch_nav_get_total_license_num_by_group($orgGroup);
      $memberData = ch_nav_parse_learning_path_group_vs_org_group($group, $orgGroup);
      $activeUserCount = count($memberData['activeUsers']);
      $learningGid = $group->id();
      $orgGid = $orgGroup->id();
    } else {
      $activeUserCount = '0';
      $totalLicenses = '0';
      $learningGid = 0;
      $orgGid = 0;
    }

    $url = Url::fromUserInput(
      "/course/{$learningGid}/org/{$orgGid}#users",
      array('attributes' => array('class' => ''))
    );
    $title = Link::fromTextAndUrl($group->label(), $url)->toString();

    $markup = '<div class="course-item mb-3">';
    $markup .= '<div class="row">';
    $markup .= '<div class="col-md-3">';
    $markup .= '  <div class="img-placeholder"></div>';
    $markup .= '</div>';
    $markup .= '<div class="col-md-9">';
    $markup .= '<h3 class="card-title">' . $title . '</h3>';
    $markup .= '  <p><strong>' . $activeUserCount . ' / '. $totalLicenses .' </strong> licenses used</p>';
    $markup .= $this->getEnrollUserLink($learningGid, $orgGid);
    $markup .= '</div>';
    $markup .= '</div>';
    $markup .= '</div>';
    return $markup;
  }

  /**
   * @param $learningGid
   * @param $orgGid
   * @return \Drupal\Core\GeneratedLink
   */
  private function getEnrollUserLink($learningGid, $orgGid) {
    $assignUserUrl = Url::fromUserInput(
      "/course/{$learningGid}/org/{$orgGid}#users",
      array(
        'attributes' => array('class' => 'card-link'),
      )
    );
    $anchorContent = '<i class="fas fa-plus-circle"></i> Enroll User';
    $renderedAnchorContent = render($anchorContent);
    return Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $assignUserUrl)->toString();
  }
}
