<?php

namespace Drupal\commerce_donate\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\commerce_order\Entity\OrderItem;
use Drupal\commerce_price\Calculator;
use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_currency_resolver\CurrencyHelper;
use Drupal\commerce_currency_resolver\CurrentCurrency;

/**
 * Provides the donation pane.
 *
 * @CommerceCheckoutPane(
 *   id = "donation",
 *   label = @Translation("Donation"),
 *   default_step = "order_information",
 *   wrapper_element = "fieldset",
 * )
 */
class Donation extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    // Hide the pane if there's already a donation order item?
    $order_item = $this->getOrderItem();
    if ($order_item) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {
    $summary = [];
    if ($this->isVisible()) {
      $order_item = $this->getOrderItem();
      // Expand this to provide the appropriate output at checkout review.
      $summary = [
        '#plain_text' => $order_item->label(),
      ];
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $current_currency = \Drupal::service('commerce_currency_resolver.current_currency');
    $selected_currency = $current_currency->getCurrency();
    $entity_type_manager = \Drupal::service('entity_type.manager');
    $currency = $entity_type_manager->getStorage('commerce_currency')->load($selected_currency);
    $currency_symbol = $currency->getSymbol();
    $predefined_amounts = [
      '50' => $currency_symbol . '50',
      '100' => $currency_symbol . '100',
      '250' => $currency_symbol . '250',
    ];
    $predefined_amount_keys = array_keys($predefined_amounts);
    $order_item = $this->getOrderItem();
    $unit_price = $order_item->getUnitPrice();
    $amount = $unit_price ? Calculator::trim($unit_price->getNumber()) : reset($predefined_amount_keys);

    $pane_form['donation'] = [
      '#type' => 'checkbox',
      '#title' => t('I would like to make a donation'),
      '#default_value' => ($unit_price ? '1' : '0')
    ];
    $pane_form['details'] = [
      '#type' => 'fieldset',
      '#states' => [
        'visible' => [
          ':input[name="donation[donation]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $pane_form['details']['amount'] = [
      '#type' => 'select_or_other_buttons',
      '#title' => t('I would like to Donate'),
      '#options' => $predefined_amounts,
      '#default_value' => $amount,
      '#required' => TRUE,
    ];

    $pane_form['details']['in_memory'] = [
      '#type' => 'checkbox',
      '#title' => t('I wish to make this donation in memory of someone'),
      '#default_value' => $order_item->field_in_memory->value,
    ];
    $pane_form['details']['in_memory_name'] = [
      '#type' => 'textfield',
      '#title' => t('Donate in memory of'),
      '#placeholder' => t("Enter person's name"),
      '#default_value' => $order_item->field_in_memory_name->value,
      '#states' => [
        'visible' => [
          ':input[name="donation[details][in_memory]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $pane_form['details']['in_memory_memorial'] = [
      '#type' => 'checkbox',
      '#title' => t('Receive an In Memory Card.'),
      '#default_value' => $order_item->field_in_memory_memorial->value,
      '#states' => [
        'visible' => [
          ':input[name="donation[details][in_memory]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $amount = $values['details']['amount'][0];
    if (!is_numeric($amount)) {
      $form_state->setError($pane_form['details']['amount'], t('The amount must be a valid number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $order_item = $this->getOrderItem();
    $values = $form_state->getValue($pane_form['#parents']);
    $amount = $values['details']['amount'][0];
    $make_donation = $values['donation'];

    $current_currency = \Drupal::service('commerce_currency_resolver.current_currency');
    $selected_currency = $current_currency->getCurrency();
    $entity_type_manager = \Drupal::service('entity_type.manager');
    $currency = $entity_type_manager->getStorage('commerce_currency')->load($selected_currency);
    $currency_symbol = $currency->getSymbol();
    $currency_formatter = \Drupal::service('commerce_price.currency_formatter');
    $amount_label = $currency_formatter->format($amount, $selected_currency);
    $order_item->title = t('@amount donation', ['@amount' => $amount_label]);

    $order_item->unit_price = ['number' => $amount, 'currency_code' => $selected_currency];
    $order_item->field_in_memory = $values['details']['in_memory'];
    $order_item->field_in_memory_name = $values['details']['in_memory_name'];
    $order_item->field_in_memory_memorial = $values['details']['in_memory_memorial'];
    $order_item->save();
    // Add or update Donation Line item.
    if (!$this->order->hasItem($order_item) && $make_donation) {
      $this->order->addItem($order_item);
    }
    // Remove Donation if required.
    if (!$make_donation && $this->order->hasItem($order_item)) {
      $this->order->removeItem($order_item);
    }
  }

  /**
   * Gets the donation order item.
   *
   * If one isn't found, it will be created.
   *
   * @return \Drupal\commerce_order\Entity\OrderItemInterface
   *   The donation order item.
   */
  protected function getOrderItem() {
    $donation_order_item = NULL;
    // Try to find an existing order item.
    foreach ($this->order->getItems() as $order_item) {
      if ($order_item->bundle() == 'donation') {
        $donation_order_item = $order_item;
        break;
      }
    }

    return $donation_order_item;
  }

}
