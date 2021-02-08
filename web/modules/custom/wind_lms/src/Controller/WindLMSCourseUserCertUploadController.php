<?php

namespace Drupal\wind_lms\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
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
        'uri' => $file_saved->get('uri')->getString(),
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

}
