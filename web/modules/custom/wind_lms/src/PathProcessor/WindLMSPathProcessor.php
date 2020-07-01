<?php

namespace Drupal\wind_lms\PathProcessor;

use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Symfony\Component\HttpFoundation\Request;

class WindLMSPathProcessor implements InboundPathProcessorInterface {

  /**
   * Makes URL /dashboard/a/b/c/... points to /dashboard controller
   * @see https://drupal.stackexchange.com/questions/225116/routing-match-everything
   * @param string $path
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return string
   */
  public function processInbound($path, Request $request) {
    if (strpos($path, '/dashboard/') === 0) {
      return "/dashboard";
    }
    return $path;
  }
}
