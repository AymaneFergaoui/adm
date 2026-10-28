<?php

namespace Drupal\parallax_bg;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Parallax element entities.
 */
class ParallaxElementListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Selector');
    $header['description'] = $this->t('Description');
    $header['position'] = $this->t('Position');
    $header['speed'] = $this->t('Speed');
    $header['status'] = $this->t('Published');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\parallax_bg\Entity\ParallaxElementInterface $entity */
    $row['label'] = $entity->label();
    $row['description'] = $entity->getDescription();
    $row['position'] = $entity->getPosition();
    $row['speed'] = $entity->getSpeed();
    $row['status'] = $entity->status() ? $this->t('Published') : $this->t('Unpublished');

    return $row + parent::buildRow($entity);
  }

}
