<?php

namespace Drupal\q_devel\Controller;

//use Drupal\block\Entity\Block;
use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Enity\Node;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedLink;
//
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\group\Entity\Group;
use Drupal\wind_jira\JiraRest\WindJiraWrapperService;
use Drupal\jira_rest\JiraRestWrapperService;

class QDevelJiraController{

  public function getContent(){

//    $org = $jiraRestWrapperService->getOrganizationService()->create('New Org from Clearinghouse');
//    $org->setName('test');
//    $result = $org->save();
    return array(
      '#type' => 'markup',
      '#markup' => 'bbtesting'
    );
  }

  private function createJiraIssue(){
    // This code sample uses the 'Unirest' library:
// http://unirest.io/php.html
    $headers = array(
      'Accept' => 'application/json',
      'Content-Type' => 'application/json',
      'Authorization' => array('Basic cXVhbi5uZ29Ad2luZHdhbGtlci5jb206V1lGWko2aGozQzJIVFJIbmo1c1lERTVC'),
      'Host' => array('windwalker.atlassian.net')
    );

    $bodyData = array(
      "fields" => array(
        "summary" => "something's wrong",
        "issuetype" => array(
          "id" => "10000"
        ),
      )
    );
//    $body = Unirest\Request\Body::Json($bodyData);
//    $response = Unirest\Request::post(
//      'https://windwalker.atlassian.net/rest/api/2/issue',
//      $headers,
//      $body
//    );

    var_dump($response);

  }


}
