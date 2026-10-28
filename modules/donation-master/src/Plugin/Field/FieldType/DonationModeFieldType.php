<?php

namespace Drupal\donation\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'donation_mode_field_type' field type.
 *
 * @FieldType(
 *   id = "donation_mode_field_type",
 *   label = @Translation("Donation Mode"),
 *   description = @Translation("Provides a field type for donation mode."),
 *   default_widget = "donation_mode_widget_type",
 *   default_formatter = "donation_mode_formatter_type"
 * )
 */
class DonationModeFieldType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'mode' => [
        'max_length' => 5,
        'is_ascii' => FALSE,
        'case_sensitive' => FALSE,
      ],
      'mode_type' => [
        'max_length' => 3,
        'is_ascii' => FALSE,
        'case_sensitive' => FALSE,
      ],
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Prevent early t() calls by using the TranslatableMarkup.
    $properties = [];
    $properties['mode'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Donation Mode'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);
    $properties['mode_type'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Donation Mode Type'))
      ->setSetting('case_sensitive', $field_definition->getSetting('case_sensitive'))
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $storageSettings = $field_definition->getSettings();
    $schema = [
      'columns' => [
        'mode' => [
          'type' => $storageSettings['mode']['is_ascii'] === TRUE ? 'varchar_ascii' : 'varchar',
          'length' => (int) $storageSettings['mode']['max_length'],
          'binary' => $storageSettings['mode']['case_sensitive'],
        ],
        'mode_type' => [
          'type' => $storageSettings['mode_type']['is_ascii'] === TRUE ? 'varchar_ascii' : 'varchar',
          'length' => (int) $storageSettings['mode_type']['max_length'],
          'binary' => $storageSettings['mode_type']['case_sensitive'],
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    if ($max_length = $this->getSetting('max_length')) {
      $constraint_manager = \Drupal::typedDataManager()->getValidationConstraintManager();
      $constraints[] = $constraint_manager->create('ComplexData', [
        'mode' => [
          'Length' => [
            'max' => $max_length,
            'maxMessage' => t('%name: may not be longer than @max characters.', [
              '%name' => $this->getFieldDefinition()->getLabel(),
              '@max' => $max_length,
            ]),
          ],
        ],
      ]);
      $constraints[] = $constraint_manager->create('ComplexData', [
        'mode_type' => [
          'Length' => [
            'max' => $max_length,
            'maxMessage' => t('%name: may not be longer than @max characters.', [
              '%name' => $this->getFieldDefinition()->getLabel(),
              '@max' => $max_length,
            ]),
          ],
        ],
      ]);
    }

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $values['value'] = $random->word(mt_rand(1, $field_definition->getSetting('max_length')));
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('mode')->getValue();
    return $value === NULL || $value === '';
  }

}
