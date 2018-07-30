<?php

namespace Drupal\opigno_catalog\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase
{
    public function setStyle($style)
    {
        $tempstore = \Drupal::service('user.private_tempstore')->get('opigno_catalog');
        $tempstore->set('style', $style);

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
