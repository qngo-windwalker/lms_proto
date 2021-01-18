<?php

namespace Drupal\wind_lms\Controller;

use Drupal\wind_tincan\CertificatePDF;
use Drupal\wind_tincan\Entity\TincanState;
use Drupal\wind_tincan\Entity\TincanStatement;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use TCPDF;

class WindLMSCertUserController extends ControllerBase{

  public function getContent( \Drupal\node\Entity\Node $node,  \Drupal\user\Entity\User $user) {
    $title = $node->label() . 'Certificate of Completion';
    $subject = $node->label();
    $fullName = _wind_lms_get_user_full_name($user);
    $id = $node->id();
    // Todo: add completion date to certificate.
    $completionDate = date('m/d/Y', strtotime('' ));

    // Include the main TCPDF library (search for installation path).
    // create new PDF document
    // L = landscape
    $pdf = new CertificatePDF('L', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
    // set document information
    $pdf->SetCreator($subject);
    $pdf->SetAuthor($subject);
    $pdf->SetTitle($title);
    $pdf->SetSubject($subject);
    $pdf->SetKeywords('Certificate, Course, PDF');

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);

    // remove default footer
    $pdf->setPrintFooter(false);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $l = Array();

    // PAGE META DESCRIPTORS --------------------------------------

    $l['a_meta_charset'] = 'UTF-8';
    $l['a_meta_dir'] = 'ltr';
    $l['a_meta_language'] = 'en';

    // TRANSLATIONS --------------------------------------
    $l['w_page'] = 'page';

    // set some language-dependent strings (optional)
    $pdf->setLanguageArray($l);

    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage();
    // set JPEG quality
    $pdf->setJPEGQuality(100);

    // remove default header
    $pdf->setPrintHeader(false);

    // get the current page break margin
    $bMargin = $pdf->getBreakMargin();
    // get current auto-page-break mode
    $auto_page_break = $pdf->getAutoPageBreak();
    // disable auto-page-break
    $pdf->SetAutoPageBreak(false, 0);
    // restore auto-page-break status
    $pdf->SetAutoPageBreak($auto_page_break, $bMargin);
    // set the starting point for the page content
    $pdf->setPageMark();

    // Print text
    $fullNameMarkup = '<span style="text-align:center;font-size:24pt;">'. $fullName . '</span>';
    $pdf->writeHTMLCell(0, 0, 0, '108', $fullNameMarkup, 0, 1, 0, true, '', true);

    //    $completionData = '<span style="font-size:12pt;">'. date('m/d/Y', $json_array['timestamp'] . '</span>';
    $completionData = '<span style="font-size:12pt;">'. $completionDate . '</span>';
    $pdf->writeHTMLCell(0, 0, '45', '185', $completionData, 0, 1, 0, true, '', true);

    $statementIdMarkup = '<span style="font-size:12pt;">' . $id . '</span>';
    $pdf->writeHTMLCell(0, 0, '98', '185', $statementIdMarkup, 0, 1, 0, true, '', true);
    //    $pdf->writeHTML($html, true, false, true, false, '');
    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $pdf->Output('certificate_001.pdf', 'I');
  }

  private function getUserFullName($json_array_account) {
    $accountName = explode('|', $json_array_account['name']);
    $result = \Drupal::entityQuery('user')
      ->condition('mail', $accountName[1])
      ->execute();
    if(empty($result)){
      return $accountName[0];
    }

    $user = User::load($this->array_first_child_value($result));
    $firstName = $user->hasField('field_first_name') ? $user->get('field_first_name')->value : '';
    $lastName = $user->hasField('field_last_name') ? $user->get('field_last_name')->value : '';

    if (empty($firstName) && empty($lastName)) {
      return $user->get('name')->value;
    }

    return $firstName . ' ' . $lastName;
  }

  /**
   * Get first child value of an array.
   *  Have to write it out to avoid: Notice: Only variables should be passed by reference
   * @param $arr
   *
   * @return mixed
   */
  private function array_first_child_value($arr) {
    $reverse = array_reverse($arr);
    return array_pop($reverse);
  }
}
