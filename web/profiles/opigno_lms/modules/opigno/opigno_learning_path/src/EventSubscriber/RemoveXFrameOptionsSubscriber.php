<?php

namespace Drupal\opigno_learning_path\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

// remove  X-Frame-Options: SAMEORIGIN for angular dev
class RemoveXFrameOptionsSubscriber implements EventSubscriberInterface {

  public function RemoveXFrameOptions(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $response->headers->remove('X-Frame-Options');
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('RemoveXFrameOptions', -10);
    return $events;
  }
}
