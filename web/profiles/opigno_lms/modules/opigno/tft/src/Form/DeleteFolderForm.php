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
 * Delete a term form.
 */
class DeleteFolderForm extends FormBase {

  /**
   * Check if the term has no files or child terms.
   */
  protected function check_term_is_deletable($tid) {
    /** @var \Drupal\taxonomy\TermStorage $storage */
    $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
    $terms = $storage->loadTree('tft_tree', $tid, 1);

    if (!empty($terms)) {
      return FALSE;
    }

    $fids = \Drupal::entityQuery('media')
      ->condition('bundle', 'tft_file')
      ->condition('tft_folder.target_id', $tid)
      ->execute();

    if (!empty($fids)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tft_delete_term_form';
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

    if ($user->hasPermission(TFT_DELETE_TERMS)
      || $group && $group->hasPermission(TFT_DELETE_TERMS, $user)) {
      $name = $term->getName();

      // Check that this term has no child terms or files.
      if ($this->check_term_is_deletable($tid)) {
        $form['#title'] = t("Are you sure you want to delete the folder @term ?", [
          '@term' => $name,
        ]);

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
        $this->messenger()
          ->addMessage(t("<em>@name</em> contains files and/or child folders. Move or delete these before deleting this folder.", [
            '@name' => $name,
          ]), 'error');
        $destination = str_replace('%23', '#', $_GET['destination']);

        if ($destination) {
          return new RedirectResponse($destination);
        }
        else {
          return new RedirectResponse('/');
        }
      }
    }
    else {
      throw new AccessDeniedHttpException();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $term = Term::load($form_state->getValue('tid'));
    $term->delete();
  }

}
