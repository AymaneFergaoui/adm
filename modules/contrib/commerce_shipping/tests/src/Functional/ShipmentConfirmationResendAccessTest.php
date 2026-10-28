<?php

namespace Drupal\Tests\commerce_shipping\Functional;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\Core\Url;
use Drupal\Tests\commerce_order\Functional\OrderBrowserTestBase;

/**
 * Tests access to the shipment confirmation resend form.
 *
 * @group commerce_shipping
 */
class ShipmentConfirmationResendAccessTest extends OrderBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_shipping',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $order_type = OrderType::load('default');
    $order_type->setThirdPartySetting('commerce_shipping', 'shipment_type', 'default');
    $order_type->save();

    $order_type = OrderType::create([
      'id' => 'other',
      'label' => 'Other',
      'workflow' => 'order_default',
    ]);
    $order_type->setThirdPartySetting('commerce_shipping', 'shipment_type', 'default');
    $order_type->save();
  }

  /**
   * Tests that the form requires access to the shipment's own order.
   */
  public function testResendAccess(): void {
    [$order, $shipment] = $this->createOrderWithShipment('default');
    [$other_order, $other_shipment] = $this->createOrderWithShipment('other');

    $account = $this->drupalCreateUser([
      'view default commerce_order',
      'access commerce administration pages',
    ]);
    $this->drupalLogin($account);

    $this->drupalGet($this->getResendUrl($order, $shipment));
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet($other_order->toUrl());
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet($this->getResendUrl($other_order, $other_shipment));
    $this->assertSession()->statusCodeEquals(403);
    // The shipment of an order the user can't view, under an order the user
    // can view.
    $this->drupalGet($this->getResendUrl($order, $other_shipment));
    $this->assertSession()->statusCodeEquals(403);
    $this->assertSession()->pageTextNotContains($other_order->getEmail());
  }

  /**
   * Creates a completed order with a shipment.
   *
   * @param string $order_type_id
   *   The order type ID.
   *
   * @return array
   *   The order and the shipment.
   */
  protected function createOrderWithShipment(string $order_type_id): array {
    $customer = $this->drupalCreateUser();
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->createEntity('commerce_order', [
      'type' => $order_type_id,
      'mail' => $customer->getEmail(),
      'uid' => $customer->id(),
      'store_id' => $this->store,
      'state' => 'completed',
    ]);
    $order->setOrderNumber($order->id())->save();
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = $this->createEntity('commerce_shipment', [
      'type' => 'default',
      'title' => 'Shipment ' . $order_type_id,
      'order_id' => $order->id(),
      'amount' => new Price('10', 'USD'),
    ]);
    return [$order, $shipment];
  }

  /**
   * Gets the resend form URL for the given order and shipment.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   * @param \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment
   *   The shipment.
   *
   * @return \Drupal\Core\Url
   *   The URL.
   */
  protected function getResendUrl(OrderInterface $order, ShipmentInterface $shipment): Url {
    return Url::fromRoute('entity.commerce_shipment.resend_confirmation_form', [
      'commerce_order' => $order->id(),
      'commerce_shipment' => $shipment->id(),
    ]);
  }

}
