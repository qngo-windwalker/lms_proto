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
 * Add a term form.
 */
class AddFolderForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tft_add_term_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => t("Name"),
      '#required' => TRUE,
      '#weight' => -10,
    ];

    $parent = !empty($_GET['parent']) ? (int) $_GET['parent'] : 0;

    if (!_tft_term_access($parent)) {
      $this->messenger()->addMessage(t("You do not have access to this folder. You cannot modify or delete it."), 'error');
      $destination = str_replace('%23', '#', $_GET['destination']);

      if ($destination) {
        return new RedirectResponse($destination);
      }
      else {
        return new RedirectResponse('/');
      }
    }

    $user = \Drupal::currentUser();
    $gid = _tft_get_group_gid($parent);
    $group = Group::load($gid);

    if ($user->hasPermission(TFT_ADD_TERMS)
      || $group && $group->hasPermission(TFT_ADD_TERMS, $user)) {
      $form['parent'] = [
        '#type' => 'hidden',
        '#value' => $parent,
      ];

      $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => t("Add"),
        '#button_type' => 'primary',
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
    // If the user can only add terms to an Group.
    if (!\Drupal::currentUser()->hasPermission(TFT_ADD_TERMS)) {
      if (!_tft_term_access($form_state->getValue('parent'))) {
        $form_state->setErrorByName('name');
        $this->messenger()->addMessage(t("You must select a parent folder that is part of a group you're a member of."), 'error');
        $destination = $_GET['destination'];

        if ($destination) {
          return new RedirectResponse($destination);
        }
        else {
          return new RedirectResponse('/');
        }
      }
    }

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
    $term = Term::create([
      'vid' => 'tft_tree',
      'name' => $form_state->getValue('name'),
      'parent' => $form_state->getValue('parent'),
    ]);
    $term->save();
    return $term->id();
  }

}
