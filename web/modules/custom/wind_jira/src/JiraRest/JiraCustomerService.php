<?php

namespace Drupal\wind_jira\JiraRest;

use biologis\JIRA_PHP_API\AService;
use biologis\JIRA_PHP_API\GenericJiraObject;
use biologis\JIRA_PHP_API\Search;

/**
 * Class JiraCustomerService
 * @package Drupal\wind_jira\JiraRest
 */
class JiraCustomerService extends AService {
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
   * @return \Drupal\wind_jira\JiraRest\JiraCustomer or null if it does not exist.
   */
  public function load($key) {
    $parameters = array(
      'fields' => '',
      'expand' => '',
    );

    $response = $this->getCommunicationService()->get('customer/' . $key, $parameters);
    if ($response) {
      $response = GenericJiraObject::transformStdClassToGenericJiraObject($response);

      return new JiraCustomer($this, $response, TRUE);
    }
    else {
      return null;
    }
  }


  /**
   * Creates a new JIRA issue.
   * @return \Drupal\wind_jira\JiraRest\JiraCustomer;
   */
  public function create() {
    $customer = new JiraCustomer($this);
    return $customer;
  }
}
