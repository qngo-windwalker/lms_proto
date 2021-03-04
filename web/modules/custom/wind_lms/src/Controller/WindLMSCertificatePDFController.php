<?php

namespace Drupal\wind_lms\Controller;

use Drupal\wind_lms\CertificatePDF;
use Drupal\wind_tincan\Entity\TincanState;
use Drupal\wind_tincan\Entity\TincanStatement;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use TCPDF;

class WindLMSCertificatePDFController extends ControllerBase{

  public function getContent($certificateId,  \Drupal\user\Entity\User $user) {
    $fullName = $this->getUserFullName($user);
    $site_name = \Drupal::config('system.site')->get('name');
    $idDecoded = _wind_lms_decode_certificate_id($certificateId);
    $courseNode = \Drupal\node\Entity\Node::load($idDecoded['node_nid']);

    // Include the main TCPDF library (search for installation path).
    // create new PDF document
    // L = landscape
    $pdf = new CertificatePDF('L', PDF_UNIT, 'LETTER', true, 'UTF-8', false);
    // set document information
    $pdf->SetCreator($site_name);
    $pdf->SetAuthor('Windwalkerlearning');
    $pdf->SetTitle('Certificate of Completion');
    $pdf->SetSubject('Course Name');
    $pdf->SetKeywords("{$site_name}, Certificate, Course, PDF");

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
    $pdf->writeHTMLCell(0, 0, 0, '100', $fullNameMarkup, 0, 1, 0, true, '', true);

    $courseTitle = '<span style="font-size:28pt;">' . $courseNode->label() . '</span>';
//    $courseTitle = '<span style="font-size:28pt;">Very very merry berry cherry carry hairry long title 20120</span>';
    $pdf->writeHTMLCell(0, 0, 0, '145', $courseTitle, 0, 1, 0, true, 'C', true);

    //Todo Add real completion date.
//    $completionData = '<span style="font-size:12pt;">'. date('m/d/Y', strtotime($json_array['timestamp'] )) . '</span>';
    $completionData = '<span style="font-size:12pt;">'. date('m/d/Y') . '</span>';
    $pdf->writeHTMLCell(0, 0, '105', '185', $completionData, 0, 1, 0, true, '', true);

    $statementIdMarkup = '<span style="font-size:12pt;">' . $certificateId . '</span>';
    $pdf->writeHTMLCell(0, 0, '158', '185', $statementIdMarkup, 0, 1, 0, true, '', true);
//    $pdf->writeHTML($html, true, false, true, false, '');
    // Close and output PDF document
    // This method has several options, check the source code documentation for more information.
    $username = $user->label();
    $pdf->Output($username . '_certificate_001.pdf', 'I');
  }

  private function getUserFullName($user) {
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
