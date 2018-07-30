<?php

namespace Drupal\opigno_learning_path\EventSubscriber;

use Drupal\opigno_learning_path\LearningPathContentTypesManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LearningPathEventSubscriber implements EventSubscriberInterface {

  private $content_types_manager;

  public function __construct(LearningPathContentTypesManager $content_types_manager)
  {
    $this->content_types_manager = $content_types_manager;
  }

  /**
   * Returns an array of event names this subscriber wants to listen to.
   *
   * The array keys are event names and the value can be:
   *
   *  * The method name to call (priority defaults to 0)
   *  * An array composed of the method name to call and the priority
   *  * An array of arrays composed of the method names to call and respective
   *    priorities, or 0 if unset
   *
   * For instance:
   *
   *  * array('eventName' => 'methodName')
   *  * array('eventName' => array('methodName', $priority))
   *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
   *
   * @return array The event names to listen to
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => 'onKernelRequest'
    ];
  }

  /**
   * Event called when a request is sent.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function onKernelRequest(GetResponseEvent $event) {
  }
}
