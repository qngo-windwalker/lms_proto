<?php

namespace Drupal\q_devel\Controller;

//use Drupal\block\Entity\Block;
use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Session\AccountInterface;
use Drupal\user\Entity\User;
use Drupal\node\Enity\Node;
use Drupal\opigno_module\Entity\OpignoModule;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_group_manager\Entity\OpignoGroupManagedLink;
//
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Component\Utility\UrlHelper;
use Drupal\group\Entity\Group;
use Drupal\wind_jira\JiraRest\WindJiraWrapperService;
use Drupal\jira_rest\JiraRestWrapperService;

class QDevelMailController{

  public function getContent(){
    return array(
      '#type' => 'markup',
      '#markup' => 'bbtesting'
    );
  }

  public function testPHPMailer() {
    $mail = new PHPMailer(TRUE);
    try {
      $mail->SMTPDebug = 2;
      $mail->isSMTP();
      $mail->Host = 'smtp1.example.com;smtp2.example.com';
      $mail->SMTPSecure = 'tls';
      $mail->Port = 587;

      $mail->setFrom('quan.ngo@windwalker.com', 'Mailer');
      $mail->addAddress('quan.windwalker@gmail.com', 'Quan Windwalker');
      $mail->addReplyTo('quan.ngo@windwalker.com', 'Quan Ngo');

      // Content
      $mail->isHTML(TRUE);
      $mail->Subject = 'Here is the subject';
      $mail->Body = 'This is the HTML message body <b> in bold!</b>';
      $mail->send();

      echo 'Message has been sent';
    } catch (Exception $exception) {
      echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
  }

}
