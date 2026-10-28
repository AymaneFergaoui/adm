<?php

namespace Drupal\unpublished_redirect\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\node\NodeInterface;

/**
 * Redirects unpublished node requests to the homepage.
 */
class UnpublishedRedirectSubscriber implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a new UnpublishedRedirectSubscriber object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::EXCEPTION][] = ['onException', 100];
    return $events;
  }

  /**
   * Redirects access denied or not found node pages to the homepage.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
   *   The exception event.
   */
  public function onException($event) {
    $exception = method_exists($event, 'getThrowable') ? $event->getThrowable() : $event->getException();

    // We only care about Access Denied or Not Found exceptions.
    if (!$exception instanceof AccessDeniedHttpException && !$exception instanceof NotFoundHttpException) {
      return;
    }

    // Get the node from the route.
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof NodeInterface) {
      // If the node is unpublished, redirect to the homepage.
      if (!$node->isPublished()) {
        $response = new RedirectResponse('/', 302);
        $event->setResponse($response);
      }
    }
  }

}
