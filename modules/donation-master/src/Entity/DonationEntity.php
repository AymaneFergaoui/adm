<?php

namespace Drupal\donation\Entity;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\ContentEntityBase;
// Use Drupal\Core\Entity\EntityPublishedTrait;.
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Donation entity.
 *
 * @ingroup donation
 *
 * @ContentEntityType(
 *   id = "donation",
 *   label = @Translation("Donation"),
 *   label_collection = @Translation("Donations"),
 *   label_singular = @Translation("Donation"),
 *   label_plural = @Translation("Donations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count donation",
 *     plural = "@count donations",
 *   ),
 *   bundle_label = @Translation("Donation type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\donation\ListBuilder\DonationEntityListBuilder",
 *     "views_data" = "Drupal\donation\Entity\DonationEntityViewsData",
 *     "translation" = "Drupal\donation\Translation\DonationEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\donation\Form\DonationEntityForm",
 *       "add" = "Drupal\donation\Form\DonationEntityForm",
 *       "edit" = "Drupal\donation\Form\DonationEntityForm",
 *       "delete" = "Drupal\donation\Form\DonationEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\donation\Routing\DonationEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\donation\Access\DonationEntityAccessControlHandler",
 *   },
 *   base_table = "donation",
 *   data_table = "donation_field_data",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "administer donation entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/donations/donation/{donation}",
 *     "add-page" = "/admin/donations/donation/add",
 *     "add-form" = "/admin/donations/donation/add/{donation_type}",
 *     "edit-form" = "/admin/donations/donation/{donation}/edit",
 *     "delete-form" = "/admin/donations/donation/{donation}/delete",
 *     "collection" = "/admin/donations",
 *   },
 *   bundle_entity_type = "donation_type",
 *   field_ui_base_route = "entity.donation_type.edit_form"
 * )
 */
class DonationEntity extends ContentEntityBase implements DonationEntityInterface {

  use EntityChangedTrait;
  // Use EntityPublishedTrait;.
  // /**
  //  * {@inheritdoc}
  //  */
  // public function getName() {
  //   return $this->get('name')->value;
  // }
  // /**
  //  * {@inheritdoc}
  //  */
  // public function setName($name) {
  //   $this->set('name', $name);
  //   return $this;
  // }.

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    // $fields += static::publishedBaseFieldDefinitions($entity_type);
    // $fields['name'] = BaseFieldDefinition::create('string')
    //   ->setLabel(t('Name'))
    //   ->setDescription(t('The name of the Donation entity.'))
    //   ->setSettings([
    //     'max_length' => 50,
    //     'text_processing' => 0,
    //   ])
    //   ->setDefaultValue('')
    //   ->setDisplayOptions('view', [
    //     'label' => 'above',
    //     'type' => 'string',
    //     'weight' => -4,
    //   ])
    //   ->setDisplayOptions('form', [
    //     'type' => 'string_textfield',
    //     'weight' => -4,
    //   ])
    //   ->setDisplayConfigurable('form', TRUE)
    //   ->setDisplayConfigurable('view', TRUE)
    //   ->setRequired(TRUE);
    $fields['mode'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Donation Mode'))
      ->setDescription(t('The Donation mode either offline/online.'))
      ->setSettings([
        'allowed_values' => [
          'online' => 'Online',
          'offline' => 'Offline',
        ],
        'text_processing' => 0,
      ])
      ->setDefaultValue('online')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['amount_pledged'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Amount Pledged'))
      ->setDescription(t('The Donation amount that has been pledged by donor.'))
      ->setSettings([
        'precision' => 5,
        'scale' => 2,
      ])
      // ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -4,
      ])
      // ->setDisplayOptions('form', [
      //   'type' => 'options_select',
      //   'weight' => -4,
      // ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['amount'] = BaseFieldDefinition::create('decimal')
      ->setLabel(t('Amount'))
      ->setDescription(t('The Donation amount donated by donor.'))
      ->setSettings([
        'precision' => 15,
        'scale' => 2,
      ])
      // ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['payment_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Payment Type'))
      ->setDescription(t('The payment of donation is oneline or recurring.'))
      ->setSettings([
        'allowed_values' => [
          'onetime' => 'Onetime',
          'recurring' => 'Recurring',
        ],
        'text_processing' => 0,
      ])
      ->setDefaultValue('onetime')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['start_and_end_date'] = BaseFieldDefinition::create('daterange')
      ->setLabel(t('Donation Start and End Date'))
      ->setDescription(t('The Donation start and end date.'))
      ->setDefaultValue(DrupalDateTime::createFromTimestamp(time()))
      ->setSettings([
        'datetime_type' => 'date',
        // 'date source' => 'value',
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'daterange_default',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['covers_transaction_fee'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('User covers the transaction fee'))
      ->setDefaultValue(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => TRUE,
        ],
        'weight' => 16,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['user'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User account'))
      ->setDescription(t('The user account this donation is connected to'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'views')
      ->setSetting('handler_settings', [
        'target_bundles' => ['user' => 'user'],
        'view' => [
          'view_name' => 'donation_user',
          'display_name' => 'entity_reference_1',
          // 'arguments' => ['type' => 'user'],
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
