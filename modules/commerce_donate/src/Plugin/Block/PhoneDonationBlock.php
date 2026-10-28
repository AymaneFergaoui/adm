<?php

namespace Drupal\commerce_donate\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Phone to Donate block.
 *
 * @Block(
 *   id = "phone_donation",
 *   admin_label = @Translation("Phone donation"),
 *   category = @Translation("Commerce"),
 * )
 */
class PhoneDonationBlock extends BlockBase implements BlockPluginInterface {

  /**
   * Creates fields for Phone Donation block form.
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['intro'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Intro text'),
      '#description' => $this->t('Use this to introduce the donation telephone numbers.'),
      '#default_value' => isset($config['intro']) ? $config['intro'] : t('You can also donate over the phone by calling'),
    ];

    $form['main_number'] = [
      '#type' => 'details',
      '#title' => t('Main donation number'),
      '#open' => TRUE,
    ];

    $form['main_number']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number label'),
      '#description' => $this->t('Text to prefix the main donation number, for example: Free Call'),
      '#default_value' => isset($config['main_number_label']) ? $config['main_number_label'] : t('Free call'),
    ];

    $form['main_number']['number'] = [
      '#type' => 'number',
      '#title' => $this->t('Main Number'),
      '#description' => $this->t('Main telephone number.'),
      '#default_value' => isset($config['main_number']) ? $config['main_number'] : '',
    ];

    $form['intl_number'] = [
      '#type' => 'details',
      '#title' => t('International donation number'),
    ];

    $form['intl_number']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('International number label'),
      '#description' => $this->t('Prefix text for international number'),
      '#default_value' => isset($config['intl_number_label']) ? $config['intl_number_label'] : '',
    ];

    $form['intl_number']['number'] = [
      '#type' => 'number',
      '#title' => $this->t('International Number'),
      '#description' => $this->t('International telephone number with country prefix.'),
      '#default_value' => isset($config['intl_number']) ? $config['intl_number'] : '',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['intro'] = $values['intro'];
    $this->configuration['main_number'] = $values['main_number']['number'];
    $this->configuration['main_number_label'] = $values['main_number']['label'];
    $this->configuration['intl_number'] = $values['intl_number']['number'];
    $this->configuration['intl_number_label'] = $values['intl_number']['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $markup = '';

    if (!empty($config['intro'])) {
      $intro = $config['intro'];
      $markup .= '<p class="phone-donation__intro">' . $intro . '</p>';
    }

    if (!empty($config['main_number'])) {
      $main = $config['main_number'];
      $main_label = $config['main_number_label'];
      $markup .= $this->t('<p class="phone-donation__main">@main_label <a href="tel:@main" class="phone-donation__main-number phone-donation__number-link">@main</a></p>', [
        '@main_label' => $main_label,
        '@main' => $main,
      ]);
    }

    if (!empty($config['intl_number'])) {
      $international = $config['intl_number'];
      $international_label = $config['intl_number_label'];
      $markup .= $this->t('<p class="phone-donation__international">@international_label <a href="tel:@international" class="phone-donation__international-number phone-donation__number-link">@international</a></p>', [
        '@international_label' => $international_label,
        '@international' => $international,
      ]);
    }

    return [
      '#markup' => $markup,
    ];
  }

}
