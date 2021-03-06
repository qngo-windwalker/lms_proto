<?php

namespace Drupal\wind_jira\JiraRest;

use biologis\JIRA_PHP_API\GenericJiraObject;
use biologis\JIRA_PHP_API\IGenericJiraObjectRoot;
use Drupal\wind_jira\JiraRest\OrganizationService;

/**
 * Class JiraOrganizationOrganization
 * @package Drupal\wind_jira\JiraRest
 */
class JiraOrganization extends GenericJiraObject implements IGenericJiraObjectRoot {
  /**
   * Reference to the OrganizationService that generated this organization.
   * @var \Drupal\wind_jira\JiraRest\OrganizationService
   */
  private $organizationService;

  /**
   * Whether this issue is fully loaded or not.
   * @var bool
   */
  private $loaded;

  /**
   * Whether this issue is stored in JIRA or not.
   * @var bool
   */
  private $persistent;

  /**
   * ID of this issue.
   * @var int
   */
  public $id;

  /**
   * Key of this issue.
   * @var string
   */
  public $key;

  /**
   * An array of \biologis\JIRA_PHP_API\Issue objects (sub tasks)
   * @var array
   */
  private $subIssues;

  /**
   * If this is a sub task, it will have a parent issue associated.
   * If not, this value will be null.
   * @var \biologis\JIRA_PHP_API\Issue
   */
  private $parent;

  /**
   * Currently active transition object (if exists).
   *
   * @var \biologis\JIRA_PHP_API\Transition
   */
  private $activeTransition;

  /**
   * Issue constructor.
   * @param \Drupal\wind_jira\JiraRest\OrganizationService
   * @param \biologis\JIRA_PHP_API\GenericJiraObject|NULL $initObject this object will be merged into the issue
   * @param bool $isLoaded false if initObject might not contain all data of the issue
   */
  public function __construct(OrganizationService $organizationService, GenericJiraObject $initObject = null, $isLoaded = false, $parent = null) {
    parent::__construct();

    $this->loaded = false;
    $this->organizationService = $organizationService;
    $this->value = array();
    $this->parent = $parent;
    $this->activeTransition = null;

    if ($initObject == null) {
      $this->persistent = false;
      $this->initializeIssueStub();
    }
    else {
      $this->persistent = true;
      $this->initialize($initObject);
    }

    $this->loaded = $isLoaded;
    $this->subIssues = array();
  }

  /**
   * @return boolean
   */
  public function isLoaded() {
    return $this->loaded;
  }


  /**
   * @return boolean
   */
  public function isPersistent() {
    return $this->persistent;
  }

  /**
   * @return int
   */
  public function getId() {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getKey() {
    return $this->key;
  }

  /**
   * @return Issue
   */
  public function getParent() {
    return $this->parent;
  }

  /**
   * Creates a diff object and returns if there are some changes to save or not.
   *
   * @return bool
   */
  public function hasChanges() {
    return empty((array)$this->createDiffObject());
  }


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

        $path = 'organization/' . $issue_identifier;
        $response = $this->organizationService->getCommunicationService()->put($path, $this->getDiffObject());
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
        $response = $this->organizationService->getCommunicationService()->post('organization', $this->getDiffObject());

        if ($response !== false) {
          $response = GenericJiraObject::transformStdClassToGenericJiraObject($response);

          if (!empty($response->id) && !empty($response->name)) {
            $this->merge($response);
            $this->resetPropertyChangelist();
            $this->persistent = true;
          } else {
            // this exception only occurs if JIRA does not provide data of the created issue
            // if save() is executed again, it would create another issue with the same data instead of updating the current one
            throw new \RuntimeException('The issue was created but this object could not be linked to it.');
          }
        } else {
          return false;
        }
      } else {
        return false;
      }
    }

    return true;
  }

  public function addToProject($organizationId, $projectId){
    $std =  new \stdClass();
    $std->organizationId = $organizationId;

    $response = $this->organizationService->getCommunicationService()->post('servicedesk/' . $projectId . '/organization', $std, 204);

    if ($response !== false) {
      // null is false positive for this case.
      if ($response === null) {
        return true;
      } else {
        // this exception only occurs if JIRA does not provide data of the created issue
        // if save() is executed again, it would create another issue with the same data instead of updating the current one
        throw new \RuntimeException('There was an issue adding to a project.');
      }
    } else {
      return false;
    }
  }


  /**
   * Returns all sub tasks of this issue as an array of Issue objects.
   * If no sub tasks exist, false is returned.
   * If this is a sub tasks, false is returned.
   *
   * @return array|bool
   */
  public function getSubIssues() {
    if (empty($this->parent)) {  // only issues without parents can have sub tasks
      $sub_issue_array = $this->fields->subtasks;

      // FIXME  sub tasks will never loaded if one was created before
      if (!empty($sub_issue_array) && empty($this->subIssues)) {
        // transform GenericJiraObjects to Issues
        foreach ($sub_issue_array as $sub_issue) {
          $this->subIssues[$sub_issue->key] = new Issue($this->organizationService, $sub_issue, false, $this);
        }

        $this->fields->subtasks = array();
        return $this->subIssues;
      }
      elseif (!empty($this->subIssues)) {
        return $this->subIssues;
      }
    }
    return false;
  }

  /**
   * Creates a sub task and returns it as \biologis\JIRA_PHP_API\Issue object.
   * The issue is also stored in this Issues sub issue list.
   * If this is a sub issue (has parent), do not create an issue and return false.
   *
   * @return \biologis\JIRA_PHP_API\Issue|bool
   */
  public function createSubIssue() {
    $sub_issue = false;

    if (empty($this->parent)) { // only create sub task if this issue has no parent
      $sub_issue = new Issue($this->organizationService, null, false, $this);
      $this->subIssues[] = $sub_issue;

      $parent = $sub_issue->fields->addGenericJiraObject('parent');

      if ($this->isPersistent()) {
        if (!empty($this->key)) {
          $parent->setKey($this->key);
        }
        elseif (!empty($this->id)) {
          $parent->setId($this->id);
        }

        if (!empty($this->fields->project->key)) {
          $sub_issue->fields->project->setKey($this->fields->project->key);
        }
        elseif (!empty($this->fields->project->id)) {
          $sub_issue->fields->project->setId($this->fields->project->id);
        }
      }
    }

    return $sub_issue;
  }


  /**
   * @param \biologis\JIRA_PHP_API\GenericJiraObject $object
   */
  private function initialize(GenericJiraObject $object) {
    if (!$this->loaded && $this->persistent) {
      $this->merge($object);

      // a persistent issue requires at least a key or id
      $key_exists = property_exists($this, 'key') && !empty($this->key);
      $id_exists = property_exists($this, 'id') && !empty($this->id);

//      if (!$key_exists || !$id_exists) {
      if (!$id_exists) {
        throw new \UnexpectedValueException('Loaded issue does not provide any key or id property.');
      }

      // identify if this is a sub task, if yes, add parent object
      if (!empty($this->fields->parent)) {
        $this->parent = new Issue($this->organizationService, $this->fields->parent);
      }
    }
  }

  /**
   * @param \biologis\JIRA_PHP_API\GenericJiraObject $object
   */
  protected function merge(GenericJiraObject $object) {
    parent::merge($object);

    $this->setGenericJiraObjectRootRecursive($this);
  }

  /**
   * Adds all required properties to create a new issue.
   */
  private function initializeIssueStub() {
    $fields = $this->addGenericJiraObject('fields');
    $project = $fields->addGenericJiraObject('project');
//    $this->addDiffableObject('name', 'tseting');
  }

  /**
   * Checks if all properties required to create a new issue are set.
   *
   * @return bool
   */
  private function hasRequiredCreateProperties() {
    $diffObject = $this->getDiffObject();
    $name_exists = !empty($diffObject->name);
    return $name_exists;
  }

  /**
   * Loads additional data from JIRA if this issue has not been fully loaded (e.g. sub task of loaded issue)
   */
  public function loadData() {
    if ($this->persistent && !$this->loaded) {
      if (!empty($this->key) || !empty($this->id)) {
        $key = $this->key;

        if (empty($key)) {
          $key = $this->id;
        }

        $response = $this->organizationService->getCommunicationService()->get('issue/' . $key);

        if ($response) {
          $response = GenericJiraObject::transformStdClassToGenericJiraObject($response);

          $this->merge($response);
          $this->loaded = true;
        }
      }
      else {
        // misconfigured object that is persistent, but does not have an id or key
        $this->persistent = false;
      }
    }
  }


  /**
   * Return communication service.
   * @return \biologis\JIRA_PHP_API\ICommunicationService
   */
  public function getCommunicationService() {
    return $this->organizationService->getCommunicationService();
  }


  /**
   * Adds and saves a comment.
   * @param $comment
   * @param bool $forceSave
   * @return bool
   */
  public function addComment($comment, $forceSave = false) {
    if ($forceSave) {
      $this->save();
    }

    if (empty($this->getId())) {
      return false;
    }

    $commentService = CommentService::getCommentService($this->getCommunicationService());

    $commentObject = $commentService->create($this->getId());
    $commentObject->setComment($comment);
    return $commentObject->save();
  }


  /**
   * Creates a new transition and sets it as the issue's active one.
   *
   * @param bool $transitionId
   * @return \biologis\JIRA_PHP_API\Transition|null
   */
  public function newTransition($transitionId = false) {
    $this->activeTransition = new Transition($this, $transitionId);
    return $this->activeTransition;
  }


  /**
   * @return \biologis\JIRA_PHP_API\Transition
   */
  public function getActiveTransition() {
    return $this->activeTransition;
  }

  /**
   * Sets the currently active transition to null if a reference to the current instance is given.
   * @param $transition \biologis\JIRA_PHP_API\Transition
   */
  public function deleteActiveTransition($transition) {
    if ($transition === $this->activeTransition) {
      $this->activeTransition = null;
    }
  }
}
