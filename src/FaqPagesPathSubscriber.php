<?php

/**
 * @file
 * Contains Drupal\faq_pages\FaqPagesPathSubscriber.
 */

namespace Drupal\faq_pages;

use Drupal\Core\EventSubscriber\PathListenerBase;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Path subscriber for faq_pages.
 */
class FaqPagesPathSubscriber extends PathListenerBase implements EventSubscriberInterface {

  /**
   * Resolve the system path based on some arbitrary rules.
   *
   * @param Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestPathResolve(GetResponseEvent $event) {
    $request = $event->getRequest();
    $path = $this->extractPath($request);

    $routes = NULL;
    if ($cache = \Drupal::cache()->get('faqpages:routes')) {
      $routes = $cache->data;
    }
    else {
      $query = db_select('faq_pages', 'fp');
      $query->fields('fp', array('sid', 'url'));
      $routes = $query->execute()->fetchAllAssoc('url');
      \Drupal::cache()->set('faqpages:routes', $routes, REQUEST_TIME + 60); 
    }
    
    if (array_key_exists($path, $routes)) {
      $path = 'faq/' . $routes[$path]->sid;
      $this->setPath($request, $path);
    }
    
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('onKernelRequestPathResolve', 100);
    return $events;
  }

}
