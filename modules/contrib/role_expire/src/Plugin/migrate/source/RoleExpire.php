<?php

namespace Drupal\role_expire\Plugin\migrate\source;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Take values from role_expire table.
 *
 * @MigrateSource(
 *   id = "role_expire",
 *   source_module = "role_expire",
 * )
 */
class RoleExpire extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $query = $this->select('role_expire', 're')
      ->fields('re', [
        'uid',
        'rid',
        'expiry_timestamp',
      ]);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(): array {
    $fields = [
      'uid' => $this->t('User ID'),
      'rid'   => $this->t('Role IDs'),
      'expiry_timestamp'    => $this->t('Expiry Datetime'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds(): array {
    return [
      'uid' => [
        'type' => 'integer',
        'alias' => 're',
      ],
    ];
  }

}
