<?php

namespace Drupal\Tests\role_expire\Traits;

/**
 * Adds helper functions.
 */
trait AssertRoleExpireTrait {

  /**
   * Writes and then gets role expiration for a given user and role ID.
   *
   * @param int $account_id
   *   User ID.
   * @param string $rid
   *   Role ID.
   * @param int $expiration
   *   The expiration timestamp.
   *
   * @return mixed
   *   Data with the expiration time.
   */
  protected function setAndGetExpiration(int $account_id, string $rid, int $expiration): mixed {
    $this->apiService->writeRecord($account_id, $rid, $expiration);
    $saved_expiration = $this->apiService->getUserRoleExpiryTime($account_id, $rid);

    return $saved_expiration;
  }

  /**
   * Creates a role with optional expiration via UI.
   *
   * @param string $roleName
   *   Role name.
   * @param string $rid
   *   Role ID.
   * @param string $expiration
   *   The strtotime-compatible duration string.
   */
  protected function createRoleWithOptionalExpirationUi(string $roleName, string $rid, string $expiration = ''): void {
    $this->drupalGet('admin/people/roles/add');
    $this->getSession()->getPage()->fillField('Role name', $roleName);
    $this->getSession()->getPage()->fillField('Machine-readable name', $rid);
    if (!empty($expiration)) {
      $this->getSession()->getPage()->fillField('Default duration for the role', $expiration);
    }
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->statusCodeEquals(200);
  }

}
