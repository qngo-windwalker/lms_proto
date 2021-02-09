<?php

namespace Drupal\wind_tincan\Controller;

use Drupal\wind_tincan\Entity\TincanState;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;


class WindTincanAPIActivitiesStateController extends ControllerBase{
  public function getContent() {
    return [
      'content' => [
        '#markup' => '<p>Are you sure this is the page you are looking for?</p>',
      ],
    ];
  }

  /**
   * Handler for GET /course/tcapi/activities/state
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function getActivitiesState(Request $request) {
    $queryAll = $request->query->all();
    $stateId = $request->get('stateId');
    $activityId = isset($queryAll['activityId']) ? $queryAll['activityId'] : FALSE;
    $agent_json = isset($queryAll['agent']) ? $queryAll['agent'] : FALSE;
    $registration = isset($queryAll['registration']) ? $queryAll['registration'] : FALSE;
    $since = isset($queryAll['since']) ? $queryAll['since'] : FALSE;
    $result = $this->tincanFindState($activityId, $agent_json, $registration, $stateId, $since);

    if ($result) {
      if (count($result) == 1) {
        $state_entity = TincanState::load(array_shift($result));
        // this will return the contents as binary data.....
        // need to add "JSON Procedure with Requirements" section handling
        // found here: https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#docapis
        $return = array();
        $return['binary'] = $state_entity->contents->value;
        return new Response($state_entity->contents->value, 200, $return);

      } else {
        // if its returning multiple states, then return array
        $stateIds = array();
        $state_entities = TincanState::loadMultiple(array_values($result));
        foreach($state_entities as $key => $state) {
          if ($state->contents->value) {
            $stateIds[] = $state->contents->value;
          }
        }
        // Todo Find out what to return
        return new Response('OK', 200, end($stateIds));
      }

    } else {
      // Throw 404 error
      // This applies to first time user taking the course - no record in the database.
      // Perfectly normal.
      throw new NotFoundHttpException('Not Found');
    }
  }

  public function putActivitiesState(Request $request) {
    $content = file_get_contents('php://input',TRUE);
    //    $jsonDecoded = Json::decode($content);
    $stateId = $request->get('stateId');
    return $this->tincanLSRActivitiesStateProcessor($content,$request->query->all(), $stateId);
  }

  private function tincanLSRActivitiesStateProcessor(string $content, array $queryAll, $stateId = FALSE) {
    $registration = isset($queryAll['registration']) ? $queryAll['registration'] : FALSE;
    // this works for the articulate package....should we also look in $content for non-CORS requests?
    $document = $content ? $content : FALSE;
    $agent_json =  isset($queryAll['agent']) ? $queryAll['agent'] : FALSE;
    $activityId =  isset($queryAll['activityId']) ? $queryAll['activityId'] : FALSE;
    $content_type = $_SERVER['CONTENT_TYPE'];
    // stateId, agent, and activityId are required for PUT
    if (!$stateId || !$agent_json || !$activityId) {
      throw new HttpException(412, 'Precondition Failed: missing required parameters ');
    }

    $eIDs = $this->tincanFindState($activityId, $agent_json, $registration, $stateId);
    if ($eIDs) {
      $state_entity = TincanState::load(end($eIDs));
      $state_entity->updated = strtotime("now");
      if ($content_type) {
        $state_entity->content_type = $content_type;
      }
      if($document) {
        $state_entity->contents = $document;
      }
    } else {

      $values = array();
      if ($activityId) {
        $values['activity_id'] = $activityId;
      }
      if ($registration) {
        $values['registration'] = $registration;
      }
      if ($stateId) {
        $values['state_id'] = $stateId;
      }
      if ($content_type) {
        $values['content_type'] = $content_type;
      }
      $values['stored_date'] = strtotime("now");
      $values['updated'] = $values['stored_date'];
      if ($document) $values['contents'] = $document;
      $state_entity = $this->tincanLSRActivitiesStateCreate($values);
      if ($agent_json) {
        $agent_id = $state_entity->findAgent($agent_json);
        if ($agent_id) {
          $state_entity->field_tincan_agent->target_id = $agent_id;
        } else {
          $agent_id = $state_entity->createAgent($agent_json);
          $state_entity->field_tincan_agent->target_id = $agent_id;
        }
      }
    }
    try {
      $state_entity->save();
    }
    catch (Exception $e) {
      return new Response('No Content', 204);
    }
    return new Response('No Content', 204);
  }

  /**
   * @param array $values
   *
   * @return TincanState
   */
  private function tincanLSRActivitiesStateCreate(array $values) {
    $state = TincanState::create($values);
    try {
      $state->save();
    } catch (\Exception $e) {
      throw new HttpException(500, 'Internal Server Error. Statement save fail: ' . $e->getMessage());
      //      services_error ('Internal Server Error', 500, 'Statement save fail: ' . $e->getMessage());
    }
    return $state;
  }

  /**
   * Helper function for finding Activity States
   *
   * @param string $activityId
   *   Activity ID
   * @param string $agent_json
   *   JSON Agent object
   * @param string $registration
   *   Registration UUID
   * @param string $stateID
   *  State ID
   * @param integer $since
   *   Timestamp
   *
   * @return integer or array
   *   Returns the entity id of the state if one is found
   *   Returns an array of entity ids if more than one is found
   *   Returns 0 is none are found
   */
  function tincanFindState($activityId, $agent_json, $registration, $stateId, $since = FALSE) {
    $query = \Drupal::entityQuery('tincan_state');
    if ($activityId) $query->condition('activity_id', $activityId);
    if ($registration) $query->condition('registration', $registration);
    if ($stateId) $query->condition('state_id', $stateId);

    if ($agent_json) {
      $agentArray = Json::decode($agent_json);
      $accountName = $agentArray['account']['name'];
      $agent_id = _wind_tincan_get_agent_id_by_tincan_agent_account_name($accountName);

      if($agent_id) {
        $query->condition('field_tincan_agent', $agent_id);
      }
    }
    // need to handle "since" for multiple document GET
    // https://github.com/adlnet/xAPI-Spec/blob/master/xAPI.md#stateapi
    // If "since" parameter is specified, this is limited to entries that have been stored or updated since the specified timestamp (exclusive)
    // Because the updated entity property is set at the same time as the stored property....its safe? to only query on the updated field
    if($since) {
      $query->condition('updated', $since, '>=');
    }
    $result = $query->execute();
    if ($result) {
      return $result;
    }
    return [];
  }
}
