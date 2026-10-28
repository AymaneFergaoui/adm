<?php

namespace Drupal\commerce_donate;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\OrderProcessorInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;

use Drupal\commerce_currency_resolver\CommerceCurrencyResolversRefreshTrait;
use Drupal\commerce_currency_resolver\CurrentCurrency;

/**
 * Adjust Currency of Donation when Order Changes currency.
 */
class DonateOrderProcessor implements OrderProcessorInterface {

  use CommerceCurrencyResolversRefreshTrait;

  /**
   * The order storage.
   *
   * @var \Drupal\commerce_order\OrderStorage
   */
  protected $orderStorage;

  /**
   * Current currency.
   *
   * @var \Drupal\commerce_currency_resolver\CurrentCurrencyInterface
   */
  protected $currentCurrency;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CurrentCurrency $currency, AccountInterface $account, RouteMatchInterface $route_match) {
    $this->orderStorage = $entity_type_manager->getStorage('commerce_order');
    $this->routeMatch = $route_match;
    $this->account = $account;
    $this->currentCurrency = $currency;
  }

  /**
   * {@inheritdoc}
   */
  public function process(OrderInterface $order) {

    // Try to find an existing order item.
    foreach ($order->getItems() as $order_item) {
      if ($order_item->bundle() == 'donation') {
        $donation_order_item = $order_item;
        break;
      }
    }
    if (isset($donation_order_item)) {
      // Get order total.
      $total = $order->getTotalPrice();
      // Get main currency.
      $resolved_currency = $this->currentCurrency->getCurrency();

      // This is used only to ensure that order have resolved currency.
      // In combination with check on order load we can ensure that currency is
      // same accross entire order.
      // This solution provides avoiding constant recalculation
      // on order load event on currency switch (if we don't explicitly set
      // currency for total price when we switch currency.
      // @see \Drupal\commerce_currency_resolver\EventSubscriber\OrderCurrencyRefresh
      if ($total->getCurrencyCode() !== $resolved_currency && $this->shouldCurrencyRefresh($order)) {
        $amount = $donation_order_item->getUnitPrice()->getNumber();
        $donation_order_item->unit_price = ['number' => $amount, 'currency_code' => $resolved_currency];
        $currency_formatter = \Drupal::service('commerce_price.currency_formatter');
        $amount_label = $currency_formatter->format($amount, $resolved_currency);
        $donation_order_item->title = t('@amount donation', ['@amount' => $amount_label]);
        $donation_order_item->save();

        // Save order.
        $order->save();
      }


    }
  }

}

