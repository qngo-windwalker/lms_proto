<?php

namespace Drupal\wind_jira\JiraRest;

use biologis\JIRA_PHP_API\AService;
use biologis\JIRA_PHP_API\GenericJiraObject;
use biologis\JIRA_PHP_API\Search;

/**
 * Class OrganizationService
 * @package Drupal\wind_jira\JiraRest
 */
class OrganizationService extends AService {
  /**
   * Creates a new JIRA issue search.
   * @return \biologis\JIRA_PHP_API\Search
   */
  public function createSearch() {
    $search = new Search($this);
    return $search;
  }

  /**
   * Loads and returns a jira issue.
   *
   * @param string|int $key issue key or id to load
   * @return \Drupal\wind_jira\JiraRest\JiraOrganization or null if it does not exist.
   */
  public function load($key) {
    $parameters = array(
      'fields' => '',
      'expand' => '',
    );

    $response = $this->getCommunicationService()->get('organization/' . $key, $parameters);
    if ($response) {
      $response = GenericJiraObject::transformStdClassToGenericJiraObject($response);
      return new JiraOrganization($this, $response, TRUE);
    } else {
      return null;
    }
  }

  /**
   * Creates a new JIRA issue.
   * @return \Drupal\wind_jira\JiraRest\JiraOrganization;
   */
  public function create($name = '') {
    $org = new JiraOrganization($this);
    $org->setname($name);
    return $org;
  }

  public function addToProject($organizationId, $projectId) {
    $org = new JiraOrganization($this);
    return $org->addToProject($organizationId, $projectId);
  }

  public function addUser($id, $orgId) {
    $std =  new \stdClass();
    $std->accountIds = [$id];
    $response = $this->getCommunicationService()->post('organization/' . $orgId . '/user', $std, 204);
    if ($response) {
      $response = GenericJiraObject::transformStdClassToGenericJiraObject($response);
      return new JiraOrganization($this, $response, TRUE);
    } else {
      return null;
    }
  }

}
