<?php

namespace Drupal\wind_help\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\Core\Access\AccessResult;
use Drupal\jira_rest\JiraRestWrapperService;
use Drupal\wind_jira\JiraRest\WindJiraWrapperService;

class WindHelpAtlassianRequestForm extends FormBase {
  /**
   * @var \Drupal\group\Entity\Group $group
   */
  private $group;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_help_atlassian_request';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
//    $this->group = $group;
    $destination = \Drupal::request()->query->get('destination');
    $uri = $destination ? 'internal:' . $destination : 'internal:/';
    $form['#tree'] = TRUE;
    $form['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => array(
        'Technical Support',
        'Licensing and Billing',
        'Suggestion',
      )
    ];
    $form['subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#required' => true,
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#required' => true,
    ];
    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromUri($uri),
      '#attributes' => array(
        'class' => ['btn', 'btn-secondary', 'mr-1']
      )
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit Ticket',
      '#attributes' => array(
        'class' => ['btn', 'btn-primary']
      )
    ];
    return $form;
  }

  /**
   * @see \Drupal\image\Controller\QuickEditImageController->upload() to see how to implement Ajax.
   *
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $currentUser = \Drupal::currentUser();
    $org = wind_help_get_user_group_organization($currentUser);
    $configFactory = \Drupal::configFactory();
    $jiraRestWrapperService = new WindJiraWrapperService($configFactory);
    /** @var \Drupal\wind_jira\JiraRest\JiraServiceDeskRequest; $request */
    $request = $jiraRestWrapperService->getServiceDeskService()->create('request');
    $request->addGenericJiraObject('serviceDeskId');
    // serviceDeskId is the project id. Note it is NOT project key such as "ESD"
    $request->setserviceDeskId('1');
    $request->addGenericJiraObject('requestTypeId');
    // 1 = Technical support; 5 = Email Request
    switch ($form_state->getValue('category')) {
      case 'Technical Support':
          $request->setrequestTypeId('1');
        break;
      case 'Licensing and Billing':
        $request->setrequestTypeId('2');
        break;
      case 'Suggestion':
        $request->setrequestTypeId('8');
        break;
      default:
        // 4 = Other questions
        $request->setrequestTypeId('4');
        break;
    }
    $request->addGenericJiraObject('requestFieldValues');
    $request->requestFieldValues->addGenericJiraObject('summary');
    $request->requestFieldValues->setsummary($form_state->getValue('subject'));
    $request->requestFieldValues->addGenericJiraObject('description');
    $request->requestFieldValues->setdescription(utf8_encode($form_state->getValue('description')));
    if ($org) {
      // customfield_10113 = Clearinghouse Organization Id
      $request->requestFieldValues->addGenericJiraObject('customfield_10113');
      $request->requestFieldValues->setcustomfield_10113($org->id());

      $schemaAndHost = \Drupal::request()->getSchemeAndHttpHost();
      // customfield_10114 = Clearinghouse Organization Link
      $request->requestFieldValues->addGenericJiraObject('customfield_10114');
      $request->requestFieldValues->setcustomfield_10114($schemaAndHost . '/org/' . $org->id());

      if ($org->hasField('field_service_desk_org_id')) {
        $serviceDeskId = (int) $org->get('field_service_desk_org_id')->getString();
        // customfield_10002 = Organization
        $request->requestFieldValues->addGenericJiraObject('customfield_10002');
        $request->requestFieldValues->setcustomfield_10002([$serviceDeskId]);
      }
    }
    $result = $request->save();

    $messenger = \Drupal::messenger();
    if ($result) {
      $messenger->addMessage(t('Your ticket has been submitted.', []), $messenger::TYPE_STATUS);
    } else {
      $messenger->addMessage(t('There was an error. Please try again.', []), $messenger::TYPE_ERROR);
    }

    $destination = \Drupal::request()->query->get('destination');
    if ($destination) {
      $form_state->setRedirectUrl(Url::fromUserInput($destination));
    } else {
      if ($result) {
        $form_state->setRedirectUrl(Url::fromUserInput('/org/' . $org->id() . '/support-ticket'));
      } else {
        // If fail, send user back to the form.
        $form_state->setRedirectUrl(Url::fromUserInput('/help'));
      }
    }
  }

  public function access(Group $group) {
    $currentUser = \Drupal::currentUser();
    if (wind_does_user_has_sudo($currentUser)){
      return AccessResult::allowed();
    }

    $membership = $group->getMember($currentUser);
    if(!$membership){
      return AccessResult::forbidden();
    }

    // Check if the current user has the right role or permissions.
    $roles = wind_lms_get_user_group_roles($currentUser, $group);
    foreach ($roles as $role) {
      if ($role->label() == 'Admin') {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }

  public function getTitle(Group $group) {
    return $this->t('Add Licenses for %label', [
      '%label' => $group->label(),
    ]);
  }

}
