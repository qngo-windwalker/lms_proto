<?php

namespace Drupal\opigno_certificate\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface {

  public function requireDompdf(GetResponseEvent $event) {
    $dompdf_autoloaders = [
      'libraries/dompdf/src/Autoloader.php',
      'profiles/opigno_lms/libraries/dompdf/src/Autoloader.php',
    ];

    foreach ($dompdf_autoloaders as $dompdf_autoloader) {
      if (file_exists($dompdf_autoloader)) {
        // Load dompdf for the entity_print.
        require_once $dompdf_autoloader;
        \Dompdf\Autoloader::register();
        break;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['requireDompdf'];
    return $events;
  }

}
