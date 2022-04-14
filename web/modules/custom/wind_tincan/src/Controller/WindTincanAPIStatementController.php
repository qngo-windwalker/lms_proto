<?php

namespace Drupal\wind_tincan\Controller;

use Drupal\wind_tincan\Entity\TincanState;
use Drupal\wind_tincan\Entity\TincanStatement;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;


class WindTincanAPIStatementController extends ControllerBase{

  /**
   * Handler for GET /course/tcapi/statement
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function getStatements(Request $request) {
    $query = \Drupal::entityQuery('tincan_statement');

    $statementId = $request->get('statementId');
    if ($statementId) {
      $query->condition('statement_id', $statementId);
    }

    $result = $query->execute();
    $entities = TincanStatement::loadMultiple(array_values($result));
    if (count($entities) == 1) {
      $entity = end($entities);
      $json = Json::decode($entity->json->value);
      return new Response($entity->json->value, 200, $json);
    } else {
      $statements_entities = array();
      foreach ($entities as $entity) {
        $statements_entities[] = Json::decode($entity->json->value);
      }
      $return = new stdClass();
      $return->statements = $statements_entities;
      return new Response($return, 200);
    }
  }

  /**
   * Callback for "statements" resource, update (PUT) method.
   *
   * @return array
   */
  public function putStatements(Request $request) {
    $content = file_get_contents('php://input',TRUE);
    $jsonDecoded = Json::decode($content);
    $statementId = $request->get('statementId');
    return $this->tincanLSRStatementsProcess($content, $request->query->all(), $statementId);
  }

  private function tincanLSRStatementsProcess($content, $params = array(), $statementId = FALSE){
    $statement_json = $content;
    $tincan_statement = array();
    $tincan_statement['json'] = $content;

    // look for a url query parameter statementId  if it it exists, set the to be created entity statement_id
    if (isset($params['statementId'])) {
      $tincan_statement['statement_id'] = $params['statementId'];
    }
    // look for a url path component statementId if it it exists, set the to be created entity statement_id
    if ($statementId) {
      $tincan_statement['statement_id'] = $statementId;
    }

    $lookup_id = FALSE;
    // determine statement id to lookup for existing statement
    if (!isset($tincan_statement['statement_id'])) {
      $content_array = Json::decode($content);
      if (isset($content_array['id'])) $lookup_id = $content_array['id'];
    } else {
      $lookup_id = $tincan_statement['statement_id'];
    }
    // should check here if a statement with that statement id exists already.
    if ($lookup_id) {
      // do lookup
      $query = \Drupal::entityQuery('tincan_statement');
      $query->condition('statement_id', $lookup_id);
      $statements = $query->execute();
      if ($statements) {
        $sid = end($statements);
        $existing_statement = \Drupal::entityManager()->getStorage('tincan_statement')->load($sid);
        // then a match to the statement id was found and now we check to see if the statements match
        if ($statement_json == $existing_statement->json->value) {
          // if so then return 204 No Content and do nothing
          return new Response('No Content', 204);
        } else {
          // if they don't match return 409 Conflict and do nothing
        throw new ConflictHttpException('Conflict. Received statement does not match existing statement');
        }
      } // end if existing statements with id found

    } // end if statement id
    else {
      //if no statement id to lookup, then return 400 Bad Request because it is required for PUT
      throw new BadRequestHttpException('Bad Request. No statementId parameter in request');
    }

    return $this->TincanLSRStatementCreate($tincan_statement);
  }

  private function TincanLSRStatementCreate(array $tincan_statement) {
    $statement = TincanStatement::create($tincan_statement);
    try {
      $statement->save();

      $jsonDecoded = Json::decode($tincan_statement['json']);
      if (isset($jsonDecoded['result']) && isset($jsonDecoded['result']['completion']) && $jsonDecoded['result']['completion'] == true) {
        $this->invokeCourseCompleteHook($statement, $jsonDecoded);
      }
    } catch (\Exception $e) {
      throw new HttpException(500, 'Internal Server Error. Statement save fail: ' . $e->getMessage());
//      services_error ('Internal Server Error', 500, 'Statement save fail: ' . $e->getMessage());
    }

    return new Response('Success', 204);
  }

  private function invokeCourseCompleteHook(TincanStatement $tincanStatment, $tincanStatementJson) {
    $agentId = $tincanStatment->findAgent($tincanStatementJson['actor']);
    $user = _wind_tincan_load_user_id_by_agent_id($agentId);
    $tincanObjectValue =  $tincanStatment->get('field_tincan_object')->getValue();
    $tincanObjectActivityId = $tincanObjectValue[0]['id'];
    $tincanTypeObjCollection = _wind_tincan_file_id_by_activity_id($tincanObjectActivityId);
    if (empty($tincanTypeObjCollection)) {
      \Drupal::moduleHandler()->invokeAll('tincan_course_complete', [null, $user, ['agentId' => $agentId, 'tincanStatment' => $tincanStatment, 'tincanStatmentJsonDecoded' => $tincanStatementJson]]);
      return;
    }

    foreach ($tincanTypeObjCollection as $tincanTypeObj) {
      // Get the course node that the package uploaded to.
      $course_nids = _wind_tincan_get_course_nids_by_tincan_fid($tincanTypeObj->fid);
      if (empty($course_nids)) {
        continue;
      }

      foreach ($course_nids as $course_nid) {
        \Drupal::moduleHandler()->invokeAll('tincan_course_complete', [$course_nid, $user, ['agentId' => $agentId, 'tincanStatment' => $tincanStatment, 'tincanStatmentJsonDecoded' => $tincanStatementJson]]);
      }
    }
  }

}
