<?php

namespace Drupal\wind_lms;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Access\AccessResult;
use TCPDF;

class CertificatePDF extends TCPDF {
  //Page header
  public function Header() {
    // get the current page break margin
    $bMargin = $this->getBreakMargin();
    // get current auto-page-break mode
    $auto_page_break = $this->AutoPageBreak;
    // disable auto-page-break
    $this->SetAutoPageBreak(false, 0);
    // set bacground image
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('wind_lms')->getPath();
    $certificatePath = $module_path . '/img/certificate.jpg';
    $fitonpage = true;
    $fitbox = true;
    $align = 'LTR';
    $palign = 'C';
    $this->Image($certificatePath, 0, 6, 280, 0, '', '', $align, false, 300, $palign, false, false, 0, $fitbox, false);
    // restore auto-page-break status
    $this->SetAutoPageBreak($auto_page_break, $bMargin);
    // set the starting point for the page content
    $this->setPageMark();
  }
}
