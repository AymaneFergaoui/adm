<?php

namespace Drupal\donation\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'donation_mode_widget_type' widget.
 *
 * @FieldWidget(
 *   id = "donation_mode_widget_type",
 *   module = "donation",
 *   label = @Translation("Donation Mode Widget"),
 *   field_types = {
 *     "donation_mode_field_type"
 *   }
 * )
 */
class DonationModeWidgetType extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'mode' => 'online',
      'mode_type' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $donationModeField = $items[$delta];

    $element['mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('Donation Mode'),
      '#options' => [
        'online' => $this->t('Online'),
        'offline' => $this->t('Offline'),
      ],
      '#default_value' => !$donationModeField->isEmpty() ? $donationModeField->mode : $this->getSetting('mode'),
      '#required' => FALSE,
    ];

    $element['mode_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Donation Mode Type'),
      '#options' => [
        'cash' => $this->t('Cash'),
        'cheque' => $this->t('Cheque'),
      ],
      '#default_value' => !$donationModeField->isEmpty() ? $donationModeField->mode_type : $this->getSetting('mode_type'),
      "#empty_option" => $this->t('- Select -'),
      '#required' => FALSE,
      '#description' => $this->t('Choose the donation mode type like cash, cheque etc.'),
      '#states' => [
        'visible' => [
          ":input[name=\"{$items->getName()}[$delta][mode]\"]" => ['value' => 'offline'],
        ],
      ],
    ];

    return $element;
  }

}
