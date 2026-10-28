<?php

namespace Drupal\commerce_cart_redirection\EventSubscriber;

use Drupal\commerce_cart\Event\CartEntityAddEvent;
use Drupal\commerce_cart\Event\CartEvents;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class CommerceCartRedirectionSubscriber implements EventSubscriberInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;


  /**
   * CartEventSubscriber constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      CartEvents::CART_ENTITY_ADD => 'tryRedirectToCheckout',
      KernelEvents::RESPONSE => ['checkRedirectIssued', -10],
    ];
    return $events;
  }

  /**
   * Conditionally skip cart and send user to checkout.
   *
   * If the added product meets criteria set in the config form then redirect.
   *
   * @param \Drupal\commerce_cart\Event\CartEntityAddEvent $event
   *   The add to cart event.
   */
  public function tryRedirectToCheckout(CartEntityAddEvent $event) {
    $redirect = FALSE;
    $purchased_entity = $event->getEntity();
    /** @var \Drupal\Core\Config\Config $config */
    $config = \Drupal::config('commerce_cart_redirection.settings');
    $active_bundles = $config->get('product_bundles');
    $negate = $config->get('negate_product_bundles');
    $purchased_bundle = $purchased_entity->bundle();

    if (isset($active_bundles[$purchased_bundle]) && $active_bundles[$purchased_bundle] !== 0) {
      if (!$negate) {
        $redirect = TRUE;
      }
    }
    else {
      if ($negate) {
        $redirect = TRUE;
      }
    }

    if ($redirect) {
      /*
	  $checkout_url = Url::fromRoute('commerce_checkout.form', [
        'commerce_order' => $event->getCart()->id(),
      ])->toString();
	  */
	  $checkout_url = "/cart";
	  
      $this->requestStack->getCurrentRequest()->attributes
        ->set('commerce_cart_redirection_url', $checkout_url);
    }
  }

  /**
   * Checks if a redirect url has been set.
   *
   * Redirects to the provided url if there is one.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The response event.
   */
  public function checkRedirectIssued(FilterResponseEvent $event) {
    $request = $event->getRequest();
    $redirect_url = $request->attributes->get('commerce_cart_redirection_url');
    if (isset($redirect_url)) {
      $event->setResponse(new RedirectResponse($redirect_url));
    }
  }

}