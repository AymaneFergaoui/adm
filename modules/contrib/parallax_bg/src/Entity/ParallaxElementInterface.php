<?php

namespace Drupal\parallax_bg\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;

/**
 * Provides an interface for defining Parallax element entities.
 */
interface ParallaxElementInterface extends ConfigEntityInterface, EntityDescriptionInterface {

  /**
   * Helper to get element position.
   *
   * @return string
   *   Element position.
   */
  public function getPosition();

  /**
   * Helper to get element speed.
   *
   * @return string
   *   Element speed.
   */
  public function getSpeed();

}
