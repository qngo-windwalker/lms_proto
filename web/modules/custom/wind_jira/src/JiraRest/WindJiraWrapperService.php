<?php

namespace Drupal\wind_jira\JiraRest;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\key\Entity\Key;
use biologis\JIRA_PHP_API\GuzzleCommunicationService;
use biologis\JIRA_PHP_API\IssueService;
use GuzzleHttp\Client;

/**
 * Class WindJiraWrapperService.
 *
 * @package Drupal\wind_jira\JiraRest
 */
class WindJiraWrapperService {

  /**
   * Issue API service.
   *
   * @var \biologis\JIRA_PHP_API\IssueService
   */
  protected $issueService;

  /**
   * Issue API service.
   *
   * @var \Drupal\wind_jira\JiraRest\Organization
   */
  protected $organizationService;

  protected $jiraCustomerService;

  protected $serviceDeskService;

  protected $config;
  protected $credents;

  /**
   * JiraRestWrapperService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory = null) {
    $config_factory = \Drupal::configFactory();
    // Current credentials and url coming from drupal config form.
    $config = $config_factory->get('jira_rest.settings');
    $password_key = Key::load($config->get('jira_rest.password'));

    $credents = [
      'username' => $config->get('jira_rest.username'),
      'password' => ($password_key) ? $password_key->getKeyValue() : '',
    ];

    $this->config = $config;
    $this->credents = $credents;
  }

  /**
   * Get the Issue service api.
   *
   * @return \Drupal\wind_jira\JiraRest\OrganizationService
   *   Issue Service API.
   */
  public function getOrganizationService(){
    $communicationService = new GuzzleCommunicationService($this->config->get('jira_rest.instanceurl') . '/rest/servicedeskapi/', $this->credents);
    $this->organizationService = new OrganizationService($communicationService);
    return $this->organizationService;
  }

  /**
   * Get the Issue service api.
   *
   * @return \Drupal\wind_jira\JiraRest\JiraCustomerService
   *   Issue Service API.
   */
  public function getCustomerService(){
    $communicationService = new GuzzleCommunicationService($this->config->get('jira_rest.instanceurl') . '/rest/servicedeskapi/', $this->credents);
    $this->jiraCustomerService = new JiraCustomerService($communicationService);
    return $this->jiraCustomerService ;
  }

  public function getServiceDeskService(){
    $communicationService = new GuzzleCommunicationService($this->config->get('jira_rest.instanceurl') . '/rest/servicedeskapi/', $this->credents);
    $this->serviceDeskService = new JiraServiceDeskService($communicationService);
    return $this->serviceDeskService;
  }

  /**
   * Get the Issue service api.
   *
   * @return \biologis\JIRA_PHP_API\IssueService
   *   Issue Service API.
   */
  public function getIssueService() {
    $communicationService = new GuzzleCommunicationService($this->config->get('jira_rest.instanceurl') . '/rest/api/2/', $this->credents);
    $this->issueService = new IssueService($communicationService);
    return $this->issueService;
  }

  /**
   * EXPERIMENTAL, will probably change, this later will be included as a function of \biologis\JIRA_PHP_API\Issue
   *
   * @param string $path
   * @param string $data
   * @return bool|mixed
   */
  public function attachFileToIssueByKey($file_path, $issuekey) {

    $path = 'issue/' . $issuekey  . '/attachments';

    $multipart =
      [
        [
          'name'     => 'file',
          'contents' => fopen($file_path, 'r'),
        ],
      ];

    $options = array(
      'multipart' => $multipart,
      'headers' => array(
        'X-Atlassian-Token' => 'nocheck',
      )
    );

    //current credentials and url coming from drupal config form
    $config = \Drupal::config('jira_rest.settings');

    $options += array(
      'auth' => array(
        $config->get('jira_rest.username'),
        $config->get('jira_rest.password')
      )
    );

    try {

      $guzzleHTTPClient = new Client([
        'base_uri' => $config->get('jira_rest.instanceurl') . '/rest/api/2/',
        'timeout'  => 10.0,
      ]);
      $response = $guzzleHTTPClient->request('POST', $path, $options);

      if ($response->getStatusCode() == 201 || $response->getStatusCode() == 200) {
        $response_content = json_decode($response->getBody()->getContents());
        return $response_content;
      }
      else {
        return false;
      }
    } catch (\Exception $e) {
      return false;
    }
  }
}
