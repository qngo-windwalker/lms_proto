<?php

namespace Drupal\wind_tincan\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Access\AccessResult;
use Drupal\Component\Serialization\Json;
use Drupal\wind_tincan\Controller\WindTincanAdminTincanController;
use Drupal\wind_tincan\Entity\TincanStatement;

class AgentSearchForm extends FormBase{
  private $group;
  private $user;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_tincan_agent_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Group $group = NULL, User $user = NULL) {
    $this->group = $group;
    $this->user = $user;

    $form['file_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('File Fid'),
    ];

    $form['agent_text'] = [
      '#type' => 'textfield',
      '#size' => '380',
      '#maxlength' => 255,
      '#title' => $this->t('Agent:'),
      '#description' => 'Example: {"objectType":"Agent","account":{"name":"esouth@aurora.tech|esouth@aurora.tech","homePage":"http://lms-proto.lndo.site:8080/user/104"},"name":"esouth@aurora.tech"}',
      '#ajax' => [
        'callback' => '::myAjaxCallback', // don't forget :: when calling a class method.
        //'callback' => [$this, 'myAjaxCallback'], //alternative notation
        'disable-refocus' => FALSE, // Or TRUE to prevent re-focusing on the triggering element.
        'event' => 'keyup',
        'wrapper' => 'edit-output', // This element is updated with this AJAX callback.
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Verifying entry...'),
        ],
      ]
    ];

    // Create a textbox that will be updated
    // when the user selects an item from the select box above.
    $form['output'] = [
      '#type' => 'markup',
      '#prefix' => '<div id="edit-output">',
      '#suffix' => '</div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Enroll User',
      '#attributes' => array(
        'class' => ['btn', 'btn-primary']
      )
    ];
    return $form;
  }

  // Get the value from example select field and fill
  // the textbox with the selected text.
  public function myAjaxCallback(array &$form, FormStateInterface $form_state) {
    $markup = 'Enter Agent Json';

    if ($textValue = $form_state->getValue('agent_text')) {
      $json_array = Json::decode($textValue);
      $tincanStatement = TincanStatement::create();
      $result = $tincanStatement->findAgent($json_array);
      if (!$result) {
        return ['#markup' => "<div id='edit-output'>Unable to locate agent.</div>"];
      }

      $file_id = $form_state->getValue('file_id');
      if (!$file_id) {
        return ['#markup' => "<div id='edit-output'>Required File fid</div>"];
      }
      $tincan = _wind_lms_tincan_load_by_fid($file_id);
      if (!$tincan) {
        return ['#markup' => "<div id='edit-output'>Unable to locate tincan folder by fid: {$file_id}</div>"];
      }

      return $this->getStepTwo($result, $tincan);

    }


    // Don't forget to wrap your markup in a div with the #edit-output id
    // or the callback won't be able to find this target when it's called
    // more than once.
    $output = "<div id='edit-output'>$markup</div>";

    // Return the HTML markup we built above in a render array.
    return ['#markup' => $output];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  // Currently not working.
  private function getStepTwo($agentID, $tincan) {
    $controller = new WindTincanAdminTincanController;
    $renderArray = $controller->getUserTincanStateDatatable($agentID, $tincan->activity_id);
    $renderArray['#prefix'] = "<div id='edit-output'>";
    $renderArray['#suffix'] = "</div>";
    return $renderArray;
  }

}
