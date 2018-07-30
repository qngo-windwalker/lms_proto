<?php

namespace Drupal\tft\Form;

use \Drupal\Core\Form\FormBase;
use \Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Url;
use \Drupal\group\Entity\Group;
use \Drupal\taxonomy\Entity\Term;
use \Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Edit a term form.
 */
class EditFolderForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tft_edit_term_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tid = NULL) {
    if (!_tft_term_access($tid)) {
      $this->messenger()->addMessage(t("You do not have access to this folder. You cannot modify or delete it."), 'error');
      $destination = str_replace('%23', '#', $_GET['destination']);

      if ($destination) {
        return new RedirectResponse($destination);
      }
      else {
        return new RedirectResponse('/');
      }
    }

    $term = Term::load($tid);

    if (!$term) {
      $this->messenger()->addMessage(t("An error occurred. The '@tid' folder could not be found. Please contact the site administrator.", [
        '@tid' => $tid,
      ]), 'error');
      $destination = str_replace('%23', '#', $_GET['destination']);

      if ($destination) {
        return new RedirectResponse($destination);
      }
      else {
        return new RedirectResponse('/');
      }
    }

    $user = \Drupal::currentUser();
    $gid = _tft_get_group_gid($tid);
    $group = Group::load($gid);

    if ($user->hasPermission(TFT_ADD_TERMS)
      || $group && $group->hasPermission(TFT_ADD_TERMS, $user)) {
      $name = $term->getName();
      $form['name'] = [
        '#type' => 'textfield',
        '#title' => t("Name"),
        '#required' => TRUE,
        '#default_value' => $name,
        '#weight' => -10,
      ];

      $form['tid'] = [
        '#type' => 'hidden',
        '#value' => $tid,
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => t("Save"),
      ];

      $cancel_uri = str_replace('%23', '#', $_GET['destination']);
      $form['actions']['cancel'] = [
        '#type' => 'link',
        '#title' => t("cancel"),
        '#url' => Url::fromUri('internal:' . $cancel_uri),
      ];

      return $form;
    }
    else {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check for forbidden characters.
    if (strpos($form_state->getValue('name'), ',') !== FALSE
      || strpos($form_state->getValue('name'), '+') !== FALSE) {
      $form_state->setErrorByName('name', t("The following characters are not allowed: ',' (comma) and +"));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Update the term name.
    $term = Term::load($form_state->getValue('tid'));
    $term->setName($form_state->getValue('name'));
    $term->save();

    $this->messenger()->addMessage(t("The folder '@name' was updated.", [
      '@name' => $form_state->getValue('name'),
    ]));
  }

}
