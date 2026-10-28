<?php

namespace Drupal\parallax_bg\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Parallax element entity.
 *
 * @ConfigEntityType(
 *   id = "parallax_element",
 *   label = @Translation("Parallax element"),
 *   handlers = {
 *     "list_builder" = "Drupal\parallax_bg\ParallaxElementListBuilder",
 *     "form" = {
 *       "default"  = "Drupal\parallax_bg\Form\ParallaxElementForm",
 *       "add" = "Drupal\parallax_bg\Form\ParallaxElementForm",
 *       "edit" = "Drupal\parallax_bg\Form\ParallaxElementForm",
 *       "delete" = "Drupal\parallax_bg\Form\ParallaxElementDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\parallax_bg\ParallaxElementHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "parallax_element",
 *   admin_permission = "administer parallax elements",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "position" = "position",
 *     "speed" = "speed",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *      "id",
 *      "label",
 *      "description",
 *      "position",
 *      "speed",
 *      "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/parallax_element/{parallax_element}",
 *     "add-form" = "/admin/structure/parallax_element/add",
 *     "edit-form" = "/admin/structure/parallax_element/{parallax_element}/edit",
 *     "delete-form" = "/admin/structure/parallax_element/{parallax_element}/delete",
 *     "delete-multiple-form" = "/admin/structure/parallax_element/delete",
 *     "collection" = "/admin/structure/parallax_element"
 *   }
 * )
 */
class ParallaxElement extends ConfigEntityBase implements ParallaxElementInterface {

  /**
   * The Parallax element ID.
   *
   * @var string
   */
  protected $id;

  /**
   * Selector.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of the parallax element.
   *
   * @var string
   */
  protected $description;

  /**
   * Position.
   *
   * @var string
   */
  protected $position;

  /**
   * Relative speed.
   *
   * @var string
   */
  protected $speed;

  /**
   * {@inheritdoc}
   */
  public function getPosition() {
    return $this->position;
  }

  /**
   * {@inheritdoc}
   */
  public function getSpeed() {
    return $this->speed;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

}
