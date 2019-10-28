<?php

namespace Drupal\wind_jira\JiraRest;

use biologis\JIRA_PHP_API\AService;
use biologis\JIRA_PHP_API\GenericJiraObject;
use biologis\JIRA_PHP_API\Search;

/**
 * Class JiraServiceDeskService
 * @package Drupal\wind_jira\JiraRest
 */
class JiraServiceDeskService extends AService {
  /**
   * Creates a new JIRA issue search.
   * @return \biologis\JIRA_PHP_API\Search
   */
  public function createSearch() {
    $search = new Search($this);
    return $search;
  }

  public function get($path, $parameters = array()) {
    $response = $this->getCommunicationService()->get($path, $parameters);
    if ($response) {
      return $response;
    } else {
      return null;
    }
  }

  /**
   * @param $key
   * @param $data
   * @return GenericJiraObject|bool|mixed|null
   */
  public function updateStatus($key, $data) {
    $response = $this->getCommunicationService()->post('request/' . $key . '/transition', $data, 204);
//    $data = (object) [
//      'update' => [
//        'comment' => [
//          'add' => [
//            'body' => 'comment added when resolving issue'
//          ]
//        ]
//      ],
//      'transition' => [
//        'id' => '5'
//      ]
//    ];
//    $response = $this->getCommunicationService()->post('rest/api/2/issue/' . $key . '/transitions?expand=transitions.fields', $data, 204);
    if ($response === false) {
      return false;
    }

    // When status is set to 'Cancel' the reponse returns null which is a false positive.
    if ($response === null) {
      return null;
    }

    $response = GenericJiraObject::transformStdClassToGenericJiraObject($response);
    if (!empty($response->id) && !empty($response->body)) {
      return $response;
    } else {
      // this exception only occurs if JIRA does not provide data of the created issue
      // if save() is executed again, it would create another issue with the same data instead of updating the current one
//            throw new \RuntimeException('The request was created but this object could not be linked to it.');
      return null;
    }
    return false;
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
      return new JiraServiceDesk($this, $response, TRUE);
    } else {
      return null;
    }
  }

  public function createComment($key, $data) {
    $response = $this->getCommunicationService()->post('request/' . $key . '/comment', $data, 201);
    if ($response == false) {
      return false;
    }

    $response = GenericJiraObject::transformStdClassToGenericJiraObject($response);
    if (!empty($response->id) && !empty($response->body)) {
      return $response;
    } else {
      // this exception only occurs if JIRA does not provide data of the created issue
      // if save() is executed again, it would create another issue with the same data instead of updating the current one
//            throw new \RuntimeException('The request was created but this object could not be linked to it.');
      return null;
    }
  }


  /**
   * Creates a new JIRA issue.
   * @return \Drupal\wind_jira\JiraRest\JiraServiceDesk;
   */
  public function create($type) {
    switch ($type) {
      case 'request':
        $request = new JiraServiceDeskRequest($this);
        return $request;
        break;

      case 'customer':
        $customer = new JiraCustomer($this);
        return $customer;
        break;
    }
  }
}
