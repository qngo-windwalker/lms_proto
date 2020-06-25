<?php

namespace Drupal\wind_tincan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;


class WindTincanAPIController extends ControllerBase{
  public function getContent() {
    return [
      'content' => [
        '#markup' => '<p>Are you sure this is the page you are looking for?</p>',
      ],
    ];
  }

}
