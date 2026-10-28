<?php

namespace Drupal\donation\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Donation entities.
 *
 * @ingroup donation
 */
class DonationEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['id'] = $this->t('Donation ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [];
    /* @var \Drupal\donation\Entity\DonationEntity $entity */
    $row['id'] = $entity->toLink($entity->id());
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.donation.edit_form',
      ['donation' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
