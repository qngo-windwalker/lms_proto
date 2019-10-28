<?php

namespace Drupal\wind_jira\JiraRest;

use biologis\JIRA_PHP_API\GenericJiraObject;
use biologis\JIRA_PHP_API\IGenericJiraObjectRoot;
use Drupal\wind_jira\JiraRest\JiraServiceDeskService;

/**
 * Class JiraServiceDesk
 * @package Drupal\wind_jira\JiraRest
 */
class JiraServiceDeskRequest extends JiraServiceDesk {

  /**
   * Either updates or creates this issue in JIRA.
   *
   * @return bool
   */
  public function save() {
    // if this is a sub task, assure that parent is persistent and that parent information exists
    if (!empty($this->parent)) {
      if (!$this->parent->isPersistent()) {
        return false;
      }

      if (!$this->persistent && empty($this->fields->parent->getKey()) && empty($this->fields->parent->getId())) {
        return false;
      }
    }

    $this->createDiffObject();

    // update if this issue is already persistent in jira
    if ($this->persistent) {
      // if nothing changed, fake storage
      if (!empty((array) $this->getDiffObject())) {
        $issue_identifier = '';

        // prefer key over id
        if (!empty($this->key)) {
          $issue_identifier = $this->key;
        } elseif (!empty($this->id)) {
          $issue_identifier = $this->id;
        } else {
          return false;
        }

        $path = 'request/' . $issue_identifier;
        $response = $this->serviceDeskService->getCommunicationService()->put($path, $this->getDiffObject());
        if ($response !== false) {
          $this->resetPropertyChangelist();
        } else {
          return false;
        }
      }
    }
    // create if this issue was not yet persistent in jira
    else {
      if ($this->hasRequiredCreateProperties()) {
        $response = $this->serviceDeskService->getCommunicationService()->post('request', $this->getDiffObject(), 201);

        if ($response !== false) {
          $response = GenericJiraObject::transformStdClassToGenericJiraObject($response);

          if (!empty($response->issueId) && !empty($response->issueKey)) {
            $this->merge($response);
            $this->resetPropertyChangelist();
            $this->persistent = true;
          } else {
            // this exception only occurs if JIRA does not provide data of the created issue
            // if save() is executed again, it would create another issue with the same data instead of updating the current one
//            throw new \RuntimeException('The request was created but this object could not be linked to it.');
            return null;
          }
        }
        else {
          return false;
        }
      }
      else {
        return false;
      }
    }

    return true;
  }

  public function createComment($key, $data) {
    $response = $this->serviceDeskService->getCommunicationService()->post('request/' . $key . '/comment', $data, 201);

    if ($response !== false) {
      $response = GenericJiraObject::transformStdClassToGenericJiraObject($response);

      if (!empty($response->issueId) && !empty($response->issueKey)) {
        $this->merge($response);
        $this->resetPropertyChangelist();
        $this->persistent = true;
      } else {
        // this exception only occurs if JIRA does not provide data of the created issue
        // if save() is executed again, it would create another issue with the same data instead of updating the current one
//            throw new \RuntimeException('The request was created but this object could not be linked to it.');
        return null;
      }
    }
    else {
      return false;
    }

  }

  /**
   * Adds all required properties to create a new issue.
   */
  protected function initializeIssueStub() {
    $fields = $this->addGenericJiraObject('fields');
    $project = $fields->addGenericJiraObject('project');
    $project->key = '';
    $project->id = '';
    $fields->summary = '';
    $fields->descripton = '';
    $issuetype = $fields->addGenericJiraObject('issuetype');
    $issuetype->name = '';
    $issuetype->id = '';
  }

  protected function hasRequiredCreateProperties() {
    $diffObject = $this->getDiffObject();
    $serviceDeskIdExists = !empty($diffObject->serviceDeskId);
    $requestTypeIdExists = !empty($diffObject->requestTypeId);

    return $serviceDeskIdExists && $requestTypeIdExists;
  }

}
