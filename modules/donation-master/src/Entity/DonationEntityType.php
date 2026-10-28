<?php

namespace Drupal\donation\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Donation type entity.
 *
 * @ConfigEntityType(
 *   id = "donation_type",
 *   label = @Translation("Donation type"),
 *   label_singular = @Translation("Donation type"),
 *   label_plural = @Translation("Donation types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count donation type",
 *     plural = "@count donation types",
 *   ),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\donation\ListBuilder\DonationEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\donation\Form\DonationEntityTypeForm",
 *       "edit" = "Drupal\donation\Form\DonationEntityTypeForm",
 *       "delete" = "Drupal\donation\Form\DonationEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\donation\Routing\DonationEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "donation_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "donation",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/donations/donation_type/{donation_type}",
 *     "add-form" = "/admin/donations/donation_type/add",
 *     "edit-form" = "/admin/donations/donation_type/{donation_type}/edit",
 *     "delete-form" = "/admin/donations/donation_type/{donation_type}/delete",
 *     "collection" = "/admin/donations/donation_type"
 *   }
 * )
 */
class DonationEntityType extends ConfigEntityBundleBase implements DonationEntityTypeInterface {

  /**
   * The Donation type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Donation type label.
   *
   * @var string
   */
  protected $label;

}
