<?php

namespace Drupal\opigno_group_manager\EventSubscriber;

use Drupal\opigno_group_manager\Entity\OpignoGroupManagedContent;
use Drupal\opigno_group_manager\OpignoGroupContentTypesManager;
use Drupal\opigno_group_manager\OpignoGroupContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class OpignoGroupEventSubscriber implements EventSubscriberInterface {

  private $content_types_manager;

  public function __construct(OpignoGroupContentTypesManager $content_types_manager)
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
   * Store the current learning path context if there is any. Else, remove the context from the session vars.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function onKernelRequest(GetResponseEvent $event) {
    // Do not consider the ajax requests
    $request = $event->getRequest();
    if ($request->isXmlHttpRequest() === TRUE) {
      return;
    }

    // The learning path ID is added in context in the method LearningPathStepsController::start.
    // So, check if there is a context before checking if the viewing content is a Learning Path Content.
    if (empty(OpignoGroupContext::getCurrentGroupContentId())) {
      return;
    }

    $route = \Drupal::routeMatch()->getRouteName();
    if ($route === 'opigno_learning_path.steps.next') {
      return;
    }

    // If there is a Learning Path ID, check if the current content is a Learning Path Content.
    // If the object is a learning path content, clear menu cache in order to show the
    //   "next" or "finish" button if necessary.
    $types_definitions = $this->content_types_manager->getDefinitions();
    foreach($types_definitions as $plugin_id => $type_definition) {
      $content_type = $this->content_types_manager->createInstance($plugin_id);

      // Get the content from the request. If no content found, try with the next content type.
      $content = $content_type->getContentFromRequest($request);
      if ($content === FALSE) {
        continue;
      }

      // If a content is found, check that this content is still the current one in context.
      // If it's still the good content, rebuild the menu and leave the method. If not, erase the context.
      $context_content = OpignoGroupManagedContent::load(OpignoGroupContext::getCurrentGroupContentId());
      if (
        $content->getGroupContentTypeId() == $context_content->getGroupContentTypeId()
        && $content->getEntityId() == $context_content->getEntityId()
      ) {
        OpignoGroupContext::rebuildActions();
        return;
      }
    }

    // If there is a context but the user is not in a Learning Path Content anymore, remove the context.
    // OpignoGroupContext::removeContext();
  }
}
