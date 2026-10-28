<?php

namespace Drupal\commerce_donate\Form;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_store\CurrentStoreInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the donation form.
 */
class DonationForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The current store.
   *
   * @var \Drupal\commerce_store\CurrentStoreInterface
   */
  protected $currentStore;

  /**
   * Constructs a new DonationForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_store\CurrentStoreInterface $current_store
   *   The current store.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, CurrentStoreInterface $current_store) {
    $this->entityTypeManager = $entity_type_manager;
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
    $this->currentStore = $current_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_store.current_store')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donation_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
    $selected_amount = reset($predefined_amount_keys);

    $form['frequency'] = [
      '#type' => 'radios',
      '#options' => [
        'onetime' => t('Single Donation'),
        'monthly' => t('Monthly Donation'),
      ],
      '#default_value' => 'onetime',
      '#required' => TRUE,
    ];
    $form['amount_onetime'] = [
      '#type' => 'fieldgroup',
      '#states' => [
        'visible' => [
          ':input[name="frequency"]' => ['value' => 'onetime'],
        ],
      ],
      '#attributes' => [
        'class' => [
          'form-item-amount-other',
          'js-form-item-amount-other',
          'fieldgroup',
        ],
      ],
    ];
    $form['amount_onetime']['amount_onetime'] = [
      '#type' => 'select_or_other_buttons',
      '#title' => t('I would like to Donate'),
      '#options' => $predefined_amounts,
      '#default_value' => $selected_amount,
      '#required' => TRUE,
      '#description' => t('If you donate €250 per year or more, we can claim back tax from the Revenue Commissioners and help even more people with your donation.'),
    ];
    $predefined_amounts = [
      '10' => $currency_symbol . '10',
      '21' => $currency_symbol . '21',
      '50' => $currency_symbol . '50',
    ];
    $predefined_amount_keys = array_keys($predefined_amounts);
    $form['amount_monthly'] = [
      '#type' => 'fieldgroup',
      '#states' => [
        'visible' => [
          ':input[name="frequency"]' => ['value' => 'monthly'],
        ],
      ],
      '#attributes' => [
        'class' => [
          'form-item-amount-other',
          'js-form-item-amount-other',
          'fieldgroup',
        ],
      ],
    ];
    $form['amount_monthly']['amount_monthly'] = [
      '#type' => 'select_or_other_buttons',
      '#title' => t('I would like to Donate'),
      '#options' => $predefined_amounts,
      '#default_value' => $selected_amount,
      '#required' => TRUE,
      '#description' => t('If you donate €250 per year or more, we can claim back tax from the Revenue Commissioners and help even more people with your donation.'),
      '#states' => [
        'visible' => [
          ':input[name="frequency"]' => ['value' => 'monthly'],
        ],
      ],
    ];

    $form['in_memory'] = [
      '#type' => 'checkbox',
      '#title' => t('I wish to make this donation in memory of someone'),
      '#default_value' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="frequency"]' => ['value' => 'onetime'],
        ],
      ],
    ];
    $form['in_memory_name'] = [
      '#type' => 'textfield',
      '#title' => t('Donate in memory of'),
      '#placeholder' => t("Enter person's name"),
      '#states' => [
        'visible' => [
          ':input[name="in_memory"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['in_memory_memorial'] = [
      '#type' => 'checkbox',
      '#title' => t('Receive an In Memory Card.'),
      '#default_value' => FALSE,
      '#states' => [
        'visible' => [
          ':input[name="in_memory"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $amount = $form_state->getValue('amount_onetime')[0];
    if (!is_numeric($amount)) {
      $form_state->setError($form['amount_onetime']['amount_onetime'], t('The amount must be a valid number.'));
    }
    $amount = $form_state->getValue('amount_monthly')[0];
    if (!is_numeric($amount)) {
      $form_state->setError($form['amount_monthly']['amount_monthly'], t('The amount must be a valid number.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_currency = \Drupal::service('commerce_currency_resolver.current_currency');
    $selected_currency = $current_currency->getCurrency();
    $entity_type_manager = \Drupal::service('entity_type.manager');
    $currency = $entity_type_manager->getStorage('commerce_currency')->load($selected_currency);
    $currency_symbol = $currency->getSymbol();
    $donation_freq = $form_state->getValue('frequency');
    if ($donation_freq == 'onetime') {
      $amount = $form_state->getValue('amount_onetime')[0];
    }
    if ($donation_freq == 'monthly') {
      $amount = $form_state->getValue('amount_monthly')[0];
    }
    $currency_formatter = \Drupal::service('commerce_price.currency_formatter');
    $amount_label = $currency_formatter->format($amount, $selected_currency);
    // Single donation - add to cart and checkout.
    if ($donation_freq == 'onetime') {
      $donation_order_item = NULL;
      $store = $this->currentStore->getStore();
      $cart = $this->cartProvider->getCart('default', $store);
      if (!$cart) {
        $cart = $this->cartProvider->createCart('default', $store);
      }
      // Try to find an existing order item.
      foreach ($cart->getItems() as $order_item) {
        if ($order_item->bundle() == 'donation') {
          $cart->removeItem($order_item);
          break;
        }
      }
      $order_item = $this->entityTypeManager->getStorage('commerce_order_item')->create([
        'type' => 'donation',
        'title' => t('@amount donation', ['@amount' => $amount_label]),
        'unit_price' => ['number' => $amount, 'currency_code' => $selected_currency],
        'field_in_memory' => $form_state->getValue('in_memory'),
        'field_in_memory_name' => $form_state->getValue('in_memory_name'),
        'field_in_memory_memorial' => $form_state->getValue('in_memory_memorial'),
      ]);
      $store = $this->currentStore->getStore();
      // Always use the 'default' order type.
      $cart = $this->cartProvider->getCart('default', $store);
      if (!$cart) {
        $cart = $this->cartProvider->createCart('default', $store);
      }
      $this->cartManager->addOrderItem($cart, $order_item, FALSE);

      // Go to checkout.
      $form_state->setRedirect('commerce_checkout.form', ['commerce_order' => $cart->id()]);
    }
    // Else redirect to Monthly Donation form.
    else {
      $options = [
        'webform' => 'monthly_donation',
        'donate_amount' => $amount,
        'in_memory' => $form_state->getValue('in_memory'),
        'in_memory_name' => $form_state->getValue('in_memory_name'),
        'in_memory_memorial' => $form_state->getValue('in_memory_memorial'),
      ];
      $form_state->setRedirect('entity.webform.canonical', $options);
    }

  }

}
