<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class WindLMSCourseUserCertUploadController extends ControllerBase{

  /**
   * wind_lms.course.user.cert.upload:
   *  path: course/{node}/user/{user}/cert/upload
   *
   * @param \Drupal\node\NodeInterface $node Course node
   * @param \Drupal\user\UserInterface $user
   *
   * @return string[]
   */
  public function getContent(NodeInterface $node, UserInterface $user){
    // Get files information.
    if(\Drupal::request()->get('getAllFiles') == 'true') {
      return $this->getAllFiles($node, $user);
    }

    // Remove a file from certificate node field 'field_attachment'
    if(\Drupal::request()->get('remove-fid') && \Drupal::request()->get('cert-nid')) {
      return $this->removeFileFromCertificate(\Drupal::request()->get('cert-nid'), \Drupal::request()->get('remove-fid'));
    }

    // Upload file
    if (!\Drupal::request()->files->get('file')) {
      \Drupal::logger('wind_lms Certificate Upload')->warning('Unable to find file at the request level.', []);
      return new JsonResponse([
        'code' => 500,
        'error' => 1,
        'message' => 'Unable to find file.',
      ]);
    }

    $file = $_FILES['file'];

    // Checking File Size (Max Size - 5MB)
    if($file['size'] > 5242880){
      return new JsonResponse([
        'code' => 500,
        'error' => 1,
        'message' => 'File is too large.',
      ]);
    }

    // Todo: Add file extension validition
    $extensions = ['jpg jpeg gif png txt doc xls pdf ppt pptx'];
    $validators = ['file_validate_extensions' => $extensions];

    $destination = 'public://certificate_upload/';
    if (!\Drupal::service('file_system')->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY)) {
      \Drupal::logger('wind_lms Certificate Upload')->notice('The upload directory %directory for the file field %name could not be created or is not accessible. A newly uploaded file could not be saved in this directory as a consequence, and the upload was canceled.', ['%directory' => $destination, '%name' => $element['#field_name']]);
      return new JsonResponse([
        'code' => 500,
        'error' => 1,
        'message' => 'Unable to prepare directory for upload.',
      ]);
    }

    $file_temp = file_get_contents($file['tmp_name']);
    // Save the file in the Drupal system and set it to FILE_STATUS_PERMANENT
    $result = file_save_data($file_temp, $destination . $file['name'], FileSystemInterface::EXISTS_RENAME);
    $file_saved = \Drupal\file\Entity\File::load($result->id());

    if (!$result->id()) {
      \Drupal::logger('wind_lms Certificate Upload')->warning('Unable to save file data to the system', []);
      return new JsonResponse([
        'code' => 500,
        'error' => 1,
        'message' => 'Unable to find file.',
      ]);
    }
    $certNode = $this->createNewCertNode($result->id(), $node, $user->id());
    return new JsonResponse([
      'code' => 200,
      'success' => 1,
      'message' => 'Success',
      'nodeId' => $certNode->id(),
      'file' => array(
        'fid' => $file_saved->id(),
        'filename' => $file_saved->label(),
        'uri' => file_create_url($file_saved->getFileUri()),
        'filesize' => $file_saved->get('filesize')->getString(),
      )
    ]);
  }

  private function createNewCertNode($fid, NodeInterface $courseNode, $uid) {
    $node = Node::create(array(
      'title' => 'Certificate Upload -- Unverify',
      'body' => 'Node body content',
      'type' => 'certificate',
      'field_attachment' => ['target_id' => $fid, 'description' => 'Course certificate upload'],
      'field_learner' => ['target_id' => $uid],
      'field_activity' => ['target_id' => $courseNode->id()]
    ));
    $node->save();
    return $node;
  }

  private function getAllFiles(NodeInterface $node, UserInterface $user) {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'certificate');
    $query->condition('field_activity', $node->id());
    $query->condition('field_learner', $user->id());
    $result = $query->execute();
    $files = [];
    if($result){
      $certificate_nodes = \Drupal\node\Entity\Node::loadMultiple($result);
      foreach ($certificate_nodes as $cert_node) {
        /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $file */
        $field_attachment_ref_entities = $cert_node->get('field_attachment')->referencedEntities();
        if(empty($field_attachment_ref_entities)){
          continue;
        }
        /** @var \Drupal\file\Entity\File $field_attachment_ref_entities_file */
        $field_attachment_ref_entities_file = $field_attachment_ref_entities[0];
        $files[] = [
          'fid' => $field_attachment_ref_entities_file->id(),
          'filename' => $field_attachment_ref_entities_file->label(),
          'uri' => file_create_url($field_attachment_ref_entities_file->getFileUri()),
          'filesize' => $field_attachment_ref_entities_file->get('filesize')->getString(),
          'nid' => $cert_node->id(),
          'certificate_nid' => $cert_node->id()
        ];
      }
    }

    return new JsonResponse([
      'code' => 200,
      'success' => 1,
      'message' => 'Success',
      'files' => $files
    ]);
  }

  private function removeFileFromCertificate($nid, $fid) {
    $certificate_node = \Drupal\node\Entity\Node::load($nid);
    if(!$certificate_node){
      return new JsonResponse([
        'code' => 500,
        'error' => 1,
        'message' => 'Unable to find certificate node - Node Nid = .' . $nid,
      ]);
    }
    // Set and empty array to remove the file reference.
    $certificate_node->field_attachment->setValue(array());
    $saveResult = $certificate_node->save();
    if(!$saveResult){
      return new JsonResponse([
        'code' => 500,
        'error' => 1,
        'message' => 'Unable to unlink file from reference node - Node Nid = .' . $nid,
      ]);
    }

    $file = \Drupal\file\Entity\File::load($fid);
    if(!$file){
      return new JsonResponse([
        'code' => 500,
        'error' => 1,
        'message' => 'Unable to find file - fid = ' . $fid,
      ]);
    }

    // If there are no more remaining usages of this file, mark it as temporary,
    // which result in a delete through system_cron().
    $usage = \Drupal::service('file.usage')->listUsage($file);
    if (empty($usage)) {
      $file->setTemporary();
      $file->save();
    }

    // Once we've reached the end of the line, it's safe to say we didn't encounter any issue.
    return new JsonResponse([
      'code' => 200,
      'success' => 1,
      'message' => 'File removed successfully',
    ]);
  }

}
