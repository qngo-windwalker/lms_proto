<?php

/**
 * Note: Use $e->getResponse()->getBody()->getContents() to view un-truncated erro message
 * in GuzzleCommunicationService.php
 */

namespace Drupal\wind_jira\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\group\Entity\Group;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;
use Drupal\jira_rest\JiraRestWrapperService;
use Drupal\wind_jira\JiraRest\WindJiraWrapperService;

class WindJiraDevController extends ControllerBase{
  public function getContent() {
    return [
      '#markup' => 'testing',
    ];
  }

  public function createIssue() {
    $configFactory = \Drupal::configFactory();
    $jiraRestWrapperService = new JiraRestWrapperService($configFactory);
//    $issue = $jiraRestWrapperService->getIssueService()->load('CLH-25');

    /** @var \biologis\JIRA_PHP_API\Issue $issue */
    $issue = $jiraRestWrapperService->getIssueService()->create();
    //mandatory fields to set
    $issue->fields->project->setKey('CLEAR'); //or you can use the project id with: $issue->fields->project->setId($jiraProjectId);
    $issue->fields->issuetype->setId('10001');	// Issue type : Task
    $issue->fields->setSummary(utf8_encode('New account has been created'));
    // Used \\\\ because \n will be escaped when json_encode gets executed.
    $issue->fields->setDescription(utf8_encode('New account check list:\\\\* Assign account manager\\\\* Check system'));
    $issue->fields->addGenericJiraObject('priority');
    $issue->fields->priority->setId('1'); // Priority Highest
    $issue->fields->addGenericJiraObject('assignee');
    $issue->fields->assignee->setId('5c8bf89d28ed16193ca65e93'); // Priority Highest
    $issue->addComment('this is a comment added by xxx',true);
//    $issue->fields->addGenericJiraObject('customfield_10011');
//    $issue->fields->customfield_10011->setValue('true');
//    $issue->fields->addGenericJiraObject('ignoreEpics');
    //create the parent issue
    $result = $issue->save();
  }

  public function createCustomer() {
    $configFactory = \Drupal::configFactory();
    $jiraRestWrapperService = new WindJiraWrapperService($configFactory);
    $customer = $jiraRestWrapperService->getCustomerService()->create();
    $customer->setdisplayName('Quan Windwalker');
    $customer->setemail('quan.windwalker@gmail.com');
    $result = $customer->save();
  }

  public function createOrganization($name) {
    $configFactory = \Drupal::configFactory();
    $jiraRestWrapperService = new WindJiraWrapperService($configFactory);
    $org = $jiraRestWrapperService->getOrganizationService()->create($name);
    $result = $org->save();
  }

  public function addOrganization(){
    $configFactory = \Drupal::configFactory();
    $jiraRestWrapperService = new WindJiraWrapperService($configFactory);
    $org = $jiraRestWrapperService->getOrganizationService()->addToProject('8', 'ESD');
  }

  public function addCustomerToOrganization() {
    $configFactory = \Drupal::configFactory();
    $jiraRestWrapperService = new WindJiraWrapperService($configFactory);
    $org = $jiraRestWrapperService->getOrganizationService()->addUser('qm:d03d4120-37a7-4616-a041-12723bd363fd:bebf4139-7336-42c5-b5d7-9a1aca661e09', '3');
  }

  public function createRequest() {
    $configFactory = \Drupal::configFactory();
    $jiraRestWrapperService = new WindJiraWrapperService($configFactory);
    $request = $jiraRestWrapperService->getServiceDeskService()->create('request');
//    $request->addGenericJiraObject('requestParticipants');
//    $request->addrequestParticipants(['qm:d03d4120-37a7-4616-a041-12723bd363fd:bebf4139-7336-42c5-b5d7-9a1aca661e09']);
    $request->addGenericJiraObject('serviceDeskId');
    // serviceDeskId is the project id. Note it is NOT project key such as "ESD"
    $request->setserviceDeskId('1');
    $request->addGenericJiraObject('requestTypeId');
    // 1 = Technical support; 5 = Email Request
    $request->setrequestTypeId('1');
    $request->addGenericJiraObject('requestFieldValues');
    $request->requestFieldValues->addGenericJiraObject('summary');
    $request->requestFieldValues->setsummary('July 11 Request JSD help via REST 6');
    $request->requestFieldValues->addGenericJiraObject('description');
    $request->requestFieldValues->setdescription('I need a new *mouse* for my Mac');
    // customfield_10002 = Organization
    $request->requestFieldValues->addGenericJiraObject('customfield_10002');
    $request->requestFieldValues->setcustomfield_10002([3]);
    // customfield_10113 = Clearinghouse Organization Id
    $request->requestFieldValues->addGenericJiraObject('customfield_10113');
    $request->requestFieldValues->setcustomfield_10113('22');
    // customfield_10114 = Clearinghouse Organization Link
    $request->requestFieldValues->addGenericJiraObject('customfield_10114');
    $request->requestFieldValues->setcustomfield_10114('http://cn.clearinghousenavigator.com');
    $result = $request->save();
  }
}
