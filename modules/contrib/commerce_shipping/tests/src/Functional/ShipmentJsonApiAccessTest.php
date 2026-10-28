<?php

namespace Drupal\Tests\commerce_shipping\Functional;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Entity\OrderType;
use Drupal\commerce_price\Price;
use Drupal\commerce_shipping\Entity\ShipmentInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\commerce_order\Functional\OrderBrowserTestBase;
use Drupal\user\UserInterface;
use GuzzleHttp\RequestOptions;

/**
 * Tests that customers can't change shipments over JSON:API.
 *
 * @group commerce_shipping
 */
class ShipmentJsonApiAccessTest extends OrderBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'basic_auth',
    'commerce_cart',
    'commerce_shipping',
    'jsonapi',
    'serialization',
  ];

  /**
   * The customer.
   */
  protected UserInterface $customer;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('jsonapi.settings')->set('read_only', FALSE)->save(TRUE);
    $order_type = OrderType::load('default');
    $order_type->setThirdPartySetting('commerce_shipping', 'shipment_type', 'default');
    $order_type->save();
    // Create the order field.
    $field_definition = commerce_shipping_build_shipment_field_definition($order_type->id());
    \Drupal::service('commerce.configurable_field_manager')->createField($field_definition);

    FieldStorageConfig::create([
      'field_name' => 'field_notes',
      'entity_type' => 'commerce_shipment',
      'type' => 'string',
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_notes',
      'entity_type' => 'commerce_shipment',
      'bundle' => 'default',
    ])->save();
    $this->container->get('router.builder')->rebuild();

    $this->customer = $this->drupalCreateUser();
  }

  /**
   * Tests that customers can't change their cart's shipments.
   */
  public function testCustomerAccess(): void {
    $cart = $this->createOrder($this->customer, 'draft', TRUE);
    $shipment = $this->createShipment($cart);
    $other_order = $this->createOrder($this->drupalCreateUser(), 'completed', FALSE);
    $other_shipment = $this->createShipment($other_order);

    $response = $this->patch('commerce_shipment', $shipment, [
      'attributes' => ['state' => 'ready'],
    ]);
    $this->assertEquals(403, $response->getStatusCode());
    $response = $this->patch('commerce_shipment', $shipment, [
      'attributes' => ['tracking_code' => 'FAKE-1234'],
    ]);
    $this->assertEquals(403, $response->getStatusCode());
    $shipment = $this->reloadEntity($shipment);
    $this->assertEquals('draft', $shipment->getState()->getId());
    $this->assertEmpty($shipment->getTrackingCode());

    // Fields that aren't restricted can still be changed.
    $response = $this->patch('commerce_shipment', $shipment, [
      'attributes' => ['field_notes' => 'Leave at the door'],
    ]);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Leave at the door', $this->reloadEntity($shipment)->get('field_notes')->value);

    $response = $this->patch('commerce_order', $cart, [
      'relationships' => [
        'shipments' => [
          'data' => [
            ['type' => 'commerce_shipment--default', 'id' => $shipment->uuid()],
            ['type' => 'commerce_shipment--default', 'id' => $other_shipment->uuid()],
          ],
        ],
      ],
    ]);
    $this->assertEquals(403, $response->getStatusCode());
    $cart = $this->reloadEntity($cart);
    $this->assertEquals([$shipment->id()], array_column($cart->get('shipments')->getValue(), 'target_id'));
  }

  /**
   * Sends a JSON:API PATCH request as the customer.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param array $data
   *   The attributes and relationships to send.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   */
  protected function patch(string $entity_type_id, ContentEntityInterface $entity, array $data) {
    $url = Url::fromUri("base:/jsonapi/$entity_type_id/{$entity->bundle()}/{$entity->uuid()}");
    return $this->getHttpClient()->request('PATCH', $url->setAbsolute()->toString(), [
      RequestOptions::HTTP_ERRORS => FALSE,
      RequestOptions::AUTH => [$this->customer->getAccountName(), $this->customer->passRaw],
      RequestOptions::HEADERS => [
        'Accept' => 'application/vnd.api+json',
        'Content-Type' => 'application/vnd.api+json',
      ],
      RequestOptions::BODY => json_encode([
        'data' => [
          'type' => "$entity_type_id--{$entity->bundle()}",
          'id' => $entity->uuid(),
        ] + $data,
      ]),
    ]);
  }

  /**
   * Creates an order.
   *
   * @param \Drupal\user\UserInterface $customer
   *   The customer.
   * @param string $state
   *   The order state.
   * @param bool $cart
   *   Whether the order is a cart.
   *
   * @return \Drupal\commerce_order\Entity\OrderInterface
   *   The order.
   */
  protected function createOrder(UserInterface $customer, string $state, bool $cart): OrderInterface {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->createEntity('commerce_order', [
      'type' => 'default',
      'mail' => $customer->getEmail(),
      'uid' => $customer->id(),
      'store_id' => $this->store,
      'state' => $state,
      'cart' => $cart,
    ]);
    return $order;
  }

  /**
   * Creates a shipment for the given order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order.
   *
   * @return \Drupal\commerce_shipping\Entity\ShipmentInterface
   *   The shipment.
   */
  protected function createShipment(OrderInterface $order): ShipmentInterface {
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = $this->createEntity('commerce_shipment', [
      'type' => 'default',
      'title' => 'Shipment',
      'order_id' => $order->id(),
      'amount' => new Price('10', 'USD'),
    ]);
    $order->set('shipments', [$shipment]);
    $order->save();
    return $shipment;
  }

}
