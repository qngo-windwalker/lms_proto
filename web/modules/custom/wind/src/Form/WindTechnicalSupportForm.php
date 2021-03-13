<?php

namespace Drupal\wind\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;

class WindTechnicalSupportForm extends FormBase{

  private $user;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wind_technical_support_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $destination = \Drupal::request()->query->get('destination');
    $uri = $destination ? 'internal:' . $destination : 'internal:/';
    $form['#tree'] = TRUE;
    $form['subject'] = [
      '#type' => 'select',
      '#title' => $this->t('Subject'),
      '#options' => array(
        'Unable to Upload Certificate' => 'Unable to Upload Certificate',
        'Other' => 'Other',
      ),
      '#attributes' => array(
        'class' => ['d-block'] // Bootstrap display block class
      )
    ];
    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#required' => true,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Submit Ticket',
      '#attributes' => array(
        'class' => ['btn', 'btn-primary', 'mr-3', 'mt-3']
      )
    ];

    $form['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromUri($uri),
      '#attributes' => array(
        'class' => ['btn', 'btn-secondary', 'mt-3']
      )
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $messenger = \Drupal::messenger();
    $currentUser = \Drupal::currentUser();

    $result = false;
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
        $form_state->setRedirectUrl(Url::fromUserInput('/dashboard'));
      } else {
        // If fail, send user back to the form.
        $form_state->setRedirectUrl(Url::fromUserInput('/technical-support'));
      }
    }
  }
}
