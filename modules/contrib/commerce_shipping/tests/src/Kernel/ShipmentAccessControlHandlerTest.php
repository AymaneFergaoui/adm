<?php

namespace Drupal\Tests\commerce_shipping\Kernel;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_shipping\Entity\Shipment;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the shipment access control.
 *
 * @coversDefaultClass \Drupal\commerce_shipping\ShipmentAccessControlHandler
 * @group commerce
 */
class ShipmentAccessControlHandlerTest extends ShippingKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'commerce_cart',
    'commerce_shipping_field_access_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['commerce_cart']);

    // Create uid: 1 here so that it's skipped in test cases.
    $admin_user = $this->createUser();
  }

  /**
   * @covers ::checkAccess
   */
  public function testAccess() {
    $order = Order::create([
      'type' => 'default',
      'state' => 'canceled',
    ]);
    $order->save();
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = Shipment::create([
      'type' => 'default',
      'title' => 'Shipment',
      'order_id' => $order->id(),
    ]);
    $shipment->save();
    $shipment = $this->reloadEntity($shipment);

    $account = $this->createUser(['access administration pages']);
    $this->assertFalse($shipment->access('view', $account));
    $this->assertFalse($shipment->access('update', $account));
    $this->assertFalse($shipment->access('delete', $account));

    $account = $this->createUser(['view commerce_order']);
    $this->assertTrue($shipment->access('view', $account));
    $this->assertFalse($shipment->access('update', $account));
    $this->assertFalse($shipment->access('delete', $account));

    $account = $this->createUser(['update default commerce_order']);
    $this->assertFalse($shipment->access('view', $account));
    $this->assertTrue($shipment->access('update', $account));
    $this->assertFalse($shipment->access('delete', $account));

    $account = $this->createUser(['view commerce_order', 'update default commerce_order']);
    $this->assertTrue($shipment->access('view', $account));
    $this->assertTrue($shipment->access('update', $account));
    $this->assertFalse($shipment->access('delete', $account));

    $account = $this->createUser([
      'manage default commerce_shipment',
    ]);
    $this->assertTrue($shipment->access('view', $account));
    $this->assertTrue($shipment->access('update', $account));
    $this->assertTrue($shipment->access('delete', $account));

    $account = $this->createUser(['administer commerce_shipment']);
    $this->assertTrue($shipment->access('view', $account));
    $this->assertTrue($shipment->access('update', $account));
    $this->assertTrue($shipment->access('delete', $account));

    // Broken order reference.
    $shipment->set('order_id', '999');
    $account = $this->createUser(['manage default commerce_order_item']);
    $this->assertFalse($shipment->access('view', $account));
    $this->assertFalse($shipment->access('update', $account));
    $this->assertFalse($shipment->access('delete', $account));
  }

  /**
   * Tests that resending the confirmation follows the shipment's order.
   *
   * @covers ::checkAccess
   */
  public function testResendConfirmationAccess() {
    $order = Order::create([
      'type' => 'default',
      'state' => 'completed',
    ]);
    $order->save();
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = Shipment::create([
      'type' => 'default',
      'title' => 'Shipment',
      'order_id' => $order->id(),
    ]);
    $shipment->save();
    $shipment = $this->reloadEntity($shipment);

    $account = $this->createUser(['access administration pages']);
    $this->assertFalse($shipment->access('resend_confirmation', $account));

    $account = $this->createUser(['view default commerce_order']);
    $this->assertTrue($shipment->access('resend_confirmation', $account));

    $account = $this->createUser(['manage default commerce_shipment']);
    $this->assertTrue($shipment->access('resend_confirmation', $account));

    // Confirmations can't be resent for draft orders.
    $order->set('state', 'draft');
    $order->save();
    $shipment = $this->reloadEntity($shipment);
    $account = $this->createUser(['view default commerce_order']);
    $this->assertFalse($shipment->access('resend_confirmation', $account));
  }

  /**
   * Tests that customers can't edit restricted fields of their cart.
   *
   * @covers ::checkFieldAccess
   */
  public function testFieldAccess() {
    $customer = $this->createUser();
    $order = $this->container->get('commerce_cart.cart_provider')->createCart('default', $this->store, $customer);
    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    $shipment = Shipment::create([
      'type' => 'default',
      'title' => 'Shipment',
      'order_id' => $order->id(),
    ]);
    $shipment->save();
    $shipment = $this->reloadEntity($shipment);
    $order->set('shipments', [$shipment]);
    $order->save();
    $order = $this->reloadEntity($order);

    $restricted_fields = $this->entityTypeManager->getDefinition('commerce_shipment')->get('restricted_fields');
    $field_names = [
      'order_id',
      'package_type',
      'shipping_service',
      'title',
      'items',
      'weight',
      'original_amount',
      'amount',
      'adjustments',
      'tracking_code',
      'state',
      'data',
      'shipped',
      'test_restricted_field',
    ];
    foreach ($field_names as $field_name) {
      $this->assertContains($field_name, $restricted_fields);
    }

    // Customers can update the shipment of their cart (through the order),
    // but not its restricted fields, or the shipments of the order.
    $this->assertTrue($shipment->access('update', $customer));
    foreach ($field_names as $field_name) {
      $this->assertFalse($shipment->get($field_name)->access('edit', $customer), "The customer cannot edit $field_name.");
      $this->assertTrue($shipment->get($field_name)->access('view', $customer), "The customer can view $field_name.");
    }
    $this->assertTrue($shipment->get('shipping_method')->access('edit', $customer));
    $this->assertTrue($shipment->get('shipping_profile')->access('edit', $customer));
    $this->assertFalse($order->get('shipments')->access('edit', $customer));

    // Shipment restricted fields require the shipment permissions, like
    // order item fields require the order item permissions.
    foreach (['manage default commerce_shipment', 'administer commerce_shipment'] as $permission) {
      $account = $this->createUser([$permission]);
      foreach ($field_names as $field_name) {
        $this->assertTrue($shipment->get($field_name)->access('edit', $account), "$permission: $field_name");
      }
      $this->assertFalse($order->get('shipments')->access('edit', $account), $permission);
    }
    // The shipments of the order, and the restricted fields of its shipments
    // (e.g. the tracking code, for order managers), also allow the order
    // permissions.
    foreach (['update default commerce_order', 'administer commerce_order'] as $permission) {
      $account = $this->createUser([$permission]);
      $this->assertTrue($order->get('shipments')->access('edit', $account), $permission);
      foreach ($field_names as $field_name) {
        $this->assertTrue($shipment->get($field_name)->access('edit', $account), "$permission: $field_name");
      }
    }

    $account = $this->createUser(['view default commerce_order']);
    $this->assertFalse($shipment->get('state')->access('edit', $account));
    $this->assertFalse($order->get('shipments')->access('edit', $account));

    // The fields are also restricted when checked without an entity, e.g.
    // by Views field handlers. The bundle isn't known for base fields then,
    // so only the admin permissions grant access.
    $access_control_handler = $this->entityTypeManager->getAccessControlHandler('commerce_shipment');
    $admin = $this->createUser(['administer commerce_shipment']);
    $order_admin = $this->createUser(['administer commerce_order']);
    $manager = $this->createUser(['manage default commerce_shipment']);
    $order_manager = $this->createUser(['update default commerce_order']);
    foreach (['state', 'items', 'amount'] as $field_name) {
      $field_definition = $shipment->getFieldDefinition($field_name);
      $this->assertFalse($access_control_handler->fieldAccess('edit', $field_definition, $customer), $field_name);
      $this->assertFalse($access_control_handler->fieldAccess('edit', $field_definition, $manager), $field_name);
      $this->assertFalse($access_control_handler->fieldAccess('edit', $field_definition, $order_manager), $field_name);
      $this->assertTrue($access_control_handler->fieldAccess('edit', $field_definition, $admin), $field_name);
      $this->assertTrue($access_control_handler->fieldAccess('edit', $field_definition, $order_admin), $field_name);
    }

    // Other modules can still grant access to a restricted field.
    $editor = $this->createUser(['edit test restricted field']);
    $this->assertTrue($shipment->get('test_restricted_field')->access('edit', $editor));
    $this->assertFalse($shipment->get('state')->access('edit', $editor));
  }

  /**
   * Tests that configurable fields aren't restricted.
   *
   * @covers ::checkFieldAccess
   */
  public function testConfigurableFieldAccess() {
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

    $customer = $this->createUser();
    $order = $this->container->get('commerce_cart.cart_provider')->createCart('default', $this->store, $customer);
    $shipment = Shipment::create([
      'type' => 'default',
      'title' => 'Shipment',
      'order_id' => $order->id(),
    ]);
    $shipment->save();
    $shipment = $this->reloadEntity($shipment);

    $this->assertTrue($shipment->access('update', $customer));
    $this->assertTrue($shipment->get('field_notes')->access('edit', $customer));
    $this->assertFalse($shipment->get('state')->access('edit', $customer));
  }

  /**
   * @covers ::checkCreateAccess
   */
  public function testCreateAccess() {
    $access_control_handler = \Drupal::entityTypeManager()->getAccessControlHandler('commerce_shipment');

    $account = $this->createUser(['access content']);
    $this->assertFalse($access_control_handler->createAccess('default', $account));

    $account = $this->createUser(['administer commerce_shipment']);
    $this->assertTrue($access_control_handler->createAccess('default', $account));

    $account = $this->createUser(['manage default commerce_shipment']);
    $this->assertTrue($access_control_handler->createAccess('default', $account));
  }

}
