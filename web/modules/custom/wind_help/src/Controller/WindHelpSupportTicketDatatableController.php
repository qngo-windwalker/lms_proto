<?php

namespace Drupal\wind_help\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Render\Markup;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\wind_jira\JiraRest\WindJiraWrapperService;

class WindHelpSupportTicketDatatableController extends ControllerBase{
  public function getContent() {
    $collection = [];
    $currentUser = \Drupal::currentUser();
    $org = wind_help_get_user_group_organization($currentUser);
    if (!$org) {
      return new JsonResponse(['data' => []]);
    }

    $serviceDeskId = null;
    if ($org->hasField('field_service_desk_org_id')) {
      $serviceDeskId = $org->get('field_service_desk_org_id')->getString();
    }
    $configFactory = \Drupal::configFactory();
    $jiraRestWrapperService = new WindJiraWrapperService($configFactory);
//    $query_parameters= http_build_query(array_filter( ['ORGANIZATION' => array(3)]));
//    $response = $jiraRestWrapperService->getServiceDeskService()->get('request/?' . $query_parameters);
    $response = $jiraRestWrapperService->getServiceDeskService()->get('request/');
    if ($response) {
      foreach ($response->values as $std) {
        $jiraOrgId = null;
        $summary = '';
        $description = '';
        foreach ($std->requestFieldValues as $requestFieldValue){
          // Clearinghouse Organization Id
          if ($requestFieldValue->fieldId == 'customfield_10113') {
            $jiraOrgId =  $requestFieldValue->value;
          }
          if ($requestFieldValue->fieldId == 'summary') {
            $summary =  $requestFieldValue->value;
          }
          if ($requestFieldValue->fieldId == 'description') {
            $description =  $requestFieldValue->value;
          }
        }
        // Filter out to only applicable Jira Service Desk organization Id.
        // Todo: Optimize using REST API query
        if($jiraOrgId != $serviceDeskId){
          continue;
        }
        $collection[] = [
          'issueId' => $std->issueId,
          'summary' => $summary,
          'createdDate' => $std->createdDate->friendly,
          'description' => $description,
          'customfield_10113' => $jiraOrgId,
          'jiraOrgId' => $jiraOrgId,
          'operations' => '',
        ];
      }
    }
    return new JsonResponse(['data' => $collection]);
  }

  private function formatTime($timestamp) {
    if ($timestamp) {
      return date('m-d-Y', $timestamp);
    } else {
      // If the $timestamp is 0
      return 'Never';
    }
  }

  private function getUserDisplayData(User $user) {
    $uid = $user->id();
    return [
      'status' => $user->isActive() ? 'Active' : 'Inactive',
      'first_name' => $user->get('field_first_name')->getString(),
      'last_name' => $user->get('field_last_name')->getString(),
      'email' => $user->getEmail(),
      'last_login' => $this->formatTime($user->getLastLoginTime()),
      'last_accessed' => $this->formatTime($user->getLastAccessedTime()),
      'rowUid' => 'uid-' . $uid,
      'uid' => $uid,
    ];
  }

  private function currentUserHasOrgPermission($user, $group_organization, $string) {
    // Note! Current user can be outside of current group_organization such as admin.
    if(wind_does_user_has_sudo($user)){
      return true;
    };
    $roles = wind_lms_get_user_group_roles($user, $group_organization);
    if (isset($roles['organization-admin'])) {
      return true;
    }

    return false;
  }

  private function getInviteeOperation($invitee, $group) {
    if(!$this->currentUserHasOrgPermission(\Drupal::currentUser(), $group, 'edit user')){
      return 'N/A';
    };
    $resendInviteURL = Url::fromUserInput(
      "/org/{$group->id()}/invite/{$invitee->id()}/resend",
      array(
        'query' => ['destination' => "/org/{$group->id()}"],
        'attributes' => array('class' => '')
      )
    );
    $withdrawInviteURL = Url::fromUserInput(
      "/org/{$group->id()}/invite/{$invitee->id()}/withdraw",
      array(
        'query' => ['destination' => "/org/{$group->id()}"],
        'attributes' => array('class' => 'card-link text-danger')
      )
    );
    $linkContent = '<i class="fas fa-pen"></i> Resend';
    $renderedAnchorContent = render($linkContent);
    $removeLinkContent = '<i class="fas fa-minus-circle"></i> Withdraw';
    $output = Link::fromTextAndUrl(Markup::create($renderedAnchorContent), $resendInviteURL)->toString();
    $output .= '<br />' . Link::fromTextAndUrl(Markup::create(render($removeLinkContent)), $withdrawInviteURL)->toString();
    return $output;
  }
}
