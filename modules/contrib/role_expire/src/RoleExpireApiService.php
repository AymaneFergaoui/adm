<?php

namespace Drupal\role_expire;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Role expire module API service.
 */
class RoleExpireApiService {

  /**
   * Configuration factory.
   *
   * @var Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * Database connection.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Session manager.
   *
   * @var Drupal\Core\Session\SessionManager
   */
  protected $sessionManager;

  /**
   * Module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * A loggerFactory instance.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new RoleExpireApiService object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   Configuration object.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\Session\SessionManagerInterface $session_manager
   *   The session manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current account.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   A logger instance.
   */
  public function __construct(ConfigFactory $configFactory, Connection $connection, SessionManagerInterface $session_manager, ModuleHandlerInterface $moduleHandler, TimeInterface $time, AccountProxyInterface $current_user, LoggerChannelFactoryInterface $logger_factory) {
    $this->config = $configFactory;
    $this->database = $connection;
    $this->sessionManager = $session_manager;
    $this->moduleHandler = $moduleHandler;
    $this->time = $time;
    $this->currentUser = $current_user;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Get expiration time of a user role.
   *
   * @param int $uid
   *   User ID.
   * @param string $rid
   *   Role ID.
   *
   * @return mixed
   *   Array with the expiration time or empty string.
   */
  public function getUserRoleExpiryTime(int $uid, string $rid): mixed {

    $query = $this->database->select('role_expire', 'n');
    $query->fields('n', ['expiry_timestamp']);
    $query->condition('n.uid', $uid, '=');
    $query->condition('n.rid', $rid, '=');
    $result = $query->execute()->fetchField();

    return (!empty($result)) ? $result : '';
  }

  /**
   * Get expiration of all roles of a user.
   *
   * @param int $uid
   *   User ID.
   *
   * @return array
   *   Array with the expiration time.
   */
  public function getAllUserRecords(int $uid): array {

    $query = $this->database->select('role_expire', 'n');
    $query->fields('n', [
      'rid',
      'expiry_timestamp',
    ]
    );
    $query->condition('n.uid', $uid, '=');
    $result = $query->execute()->fetchAll();

    $return = [];
    foreach ($result as $row) {
      $return[$row->rid] = $row->expiry_timestamp;
    }

    return $return;
  }

  /**
   * Delete a record from the database.
   *
   * @param int $uid
   *   User ID.
   * @param string $rid
   *   Role ID.
   * @param bool $delete_session
   *   Whether to terminate user session or not.
   */
  public function deleteRecord(int $uid, string $rid, bool $delete_session = TRUE): void {
    $query = $this->database->delete('role_expire');
    $query->condition('uid', $uid)->condition('rid', $rid);
    $query->execute();

    if ($delete_session) {
      /*
       * Delete the user's sessions, so they have login again with their
       * new access.
       */
      $this->sessionManager->delete($uid);
    }
  }

  /**
   * Delete all records for role.
   *
   * @param string $rid
   *   Role ID.
   */
  public function deleteRoleRecords(string $rid): void {
    $this->database->delete('role_expire')->condition('rid', $rid)->execute();
  }

  /**
   * Delete all user expirations.
   *
   * @param int $uid
   *   User ID.
   * @param bool $delete_session
   *   Whether to terminate user session or not.
   */
  public function deleteUserRecords(int $uid, bool $delete_session = TRUE): void {
    $this->database->delete('role_expire')->condition('uid', $uid)->execute();

    if ($delete_session) {
      /*
       * Delete the user's sessions, so they have login again with their new
       * access.
       */
      $this->sessionManager->delete($uid);
    }
  }

  /**
   * Insert or update a record in the database.
   *
   * @param int $uid
   *   User ID.
   * @param string $rid
   *   Role ID.
   * @param int $expiry_timestamp
   *   The expiration timestamp.
   * @param bool $delete_session
   *   Whether to terminate user session or not.
   */
  public function writeRecord(int $uid, string $rid, int $expiry_timestamp, bool $delete_session = FALSE): void {

    // Delete previous expiry for user and role if it exists.
    $this->deleteRecord($uid, $rid, $delete_session);

    // Insert new expiry for user and role.
    $query = $this->database->insert('role_expire');
    $query->fields(['uid', 'rid', 'expiry_timestamp']);
    $query->values(['uid' => $uid, 'rid' => $rid, 'expiry_timestamp' => $expiry_timestamp]);
    $query->execute();
  }

  /**
   * Get the default duration for a role.
   *
   * @param string $rid
   *   Required. The role_id to check.
   *
   * @return string
   *   String containing the strtotime compatible default duration of the role
   *   or empty string if not set.
   */
  public function getDefaultDuration(string $rid): string {

    $values_raw = $this->config->get('role_expire.config')->get('role_expire_default_duration_roles');
    $values = empty($values_raw) ? [] : $values_raw;
    $result = $values[$rid] ?? '';
    return (!empty($result)) ? $result : '';
  }

  /**
   * Insert or update the default expiry duration for a role.
   *
   * @param string $rid
   *   Role ID.
   * @param string $duration
   *   The strtotime-compatible duration string.
   */
  public function setDefaultDuration(string $rid, string $duration): void {

    if (!empty($duration)) {
      // Insert new default duration.
      $config = $this->config->getEditable('role_expire.config');
      $values_raw = $config->get('role_expire_default_duration_roles');
      $values = empty($values_raw) ? [] : $values_raw;
      $values[$rid] = Html::escape($duration);
      $config->set('role_expire_default_duration_roles', $values)->save();
    }
  }

  /**
   * Delete default duration(s) for a role.
   *
   * @param string $rid
   *   Required. The role_id to remove.
   */
  public function deleteDefaultDuration(string $rid): void {
    $config = $this->config->getEditable('role_expire.config');
    $values_raw = $config->get('role_expire_default_duration_roles');
    $values = empty($values_raw) ? [] : $values_raw;
    if (isset($values[$rid])) {
      unset($values[$rid]);
    }
    $config->set('role_expire_default_duration_roles', $values)->save();
  }

  /**
   * Get all records that should be expired.
   *
   * @param ?int $time
   *   Optional. The time to check, if not set it will check current time.
   *
   * @return array
   *   All expired roles.
   */
  public function getExpired(?int $time = NULL): array {
    $return = [];
    if (!$time) {
      date_default_timezone_set(date_default_timezone_get());
      $time = $this->time->getRequestTime();
    }

    $query = $this->database->select('role_expire', 'n');
    $query->fields('n', [
      'rid',
      'uid',
      'expiry_timestamp',
    ]
    );
    $query->condition('n.expiry_timestamp', $time, '<=');
    $result = $query->execute()->fetchAll();

    foreach ($result as $row) {
      $return[] = $row;
    }
    return $return;
  }

  /**
   * Get roles to assign on expiration (global configuration).
   *
   * @return array|null
   *   Returns an array where the key is the original rid and the value the
   *   one that has to be assigned on expiration. The array will be empty if
   *   configuration is not set.
   */
  public function getRolesAfterExpiration(): ?array {
    $values_raw = $this->config->get('role_expire.config')->get('role_expire_default_roles');
    $values = empty($values_raw) ? [] : json_decode($values_raw, TRUE);
    return $values;
  }

  /**
   * Get role expiration status for each role.
   *
   * @return array|null
   *   Returns an array where the key is the original rid and the value
   *   is 0 if role should have expiration and 1 if it shouldn't.
   */
  public function getRolesExpirationStatus(): ?array {
    $values_raw = $this->config->get('role_expire.config')->get('role_expire_disabled_roles');
    $values = empty($values_raw) ? [] : json_decode($values_raw, TRUE);
    return $values;
  }

  /**
   * Get rid of all enabled roles.
   *
   * @return array
   *   Returns an array where the values are the enabled roles.
   */
  public function getEnabledExpirationRoles(): array {
    $out = [];
    $roleExpirationStatus = $this->getRolesExpirationStatus();
    foreach ($roleExpirationStatus as $rid => $disabled) {
      if ($disabled == 0) {
        $out[] = $rid;
      }
    }

    if (empty($out)) {
      /*
       * If the module is just installed, configuration could be empty.
       * We should return all roles to have role expiration.
       */
      $roles = Role::loadMultiple();
      unset($roles[RoleInterface::ANONYMOUS_ID]);
      unset($roles[RoleInterface::AUTHENTICATED_ID]);
      foreach ($roles as $role) {
        $out[] = $role->id();
      }
    }

    return $out;
  }

  /**
   * Sets the default role duration for the current user/role combination.
   *
   * It won't override the current expiration time for user's role.
   *
   * @param string $role_id
   *   The ID of the role.
   * @param int $uid
   *   The user ID.
   */
  public function processDefaultRoleDurationForUser(string $role_id, int $uid): void {
    // Does a default expiry exist for this role?
    $default_duration = $this->getDefaultDuration($role_id);
    if ($default_duration) {
      $user_role_expiry = $this->getUserRoleExpiryTime($uid, $role_id);
      // If the expiry is empty then we act!.
      if (!$user_role_expiry) {
        // Use strtotime of default duration.
        $this->writeRecord($uid, $role_id, strtotime($default_duration));
        $this->loggerFactory->get('role_expire')->notice(t('Added default duration @default_duration to role @role to user @account.',
          [
            '@default_duration' => $default_duration,
            '@role' => $role_id,
            '@account' => $uid,
          ]
        )
        );
      }
    }
  }

  /**
   * On user form save we decide whether to delete role expiration or not.
   *
   * @param string $rid
   *   Role ID.
   *
   * @return bool
   *   Return TRUE if expiration for role can be deleted.
   */
  public function roleExpirationCanBeDeletedOnUserEditSave(string $rid): bool {
    $currentUser = $this->currentUser;
    if ($currentUser->hasPermission('administer permissions')) {
      /*
       * spell-checker:disable.
       * User with this permission won't be limited by roleassign and
       * role_delegation modules.
       */
      return TRUE;
    }

    if ($this->moduleHandler->moduleExists('roleassign')) {
      if ($currentUser->hasPermission('assign roles')) {
        $roleassign_config = $this->config->get('roleassign.settings')
          ->get('roleassign_roles');
        $assignable_roles = array_values($roleassign_config);
        // spell-checker:enable.
        if (!in_array($rid, $assignable_roles)) {
          /*
           * Current user doesn't have permission to assign this role. So,
           * we shouldn't delete its expiration time only because they
           * don't see it.
           */
          return FALSE;
        }
      }
    }

    if ($this->moduleHandler->moduleExists('role_delegation')) {
      if (!$currentUser->hasPermission(sprintf('assign %s role', $rid))) {
        /*
         * Current user doesn't have permission to assign this role. So,
         * we shouldn't delete its expiration time only because they
         * don't see it.
         */
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Check if the user role expiration fields should be collapsed.
   *
   * @return bool
   *   TRUE if the fields should be collapsed, FALSE otherwise.
   */
  public function expirationExpanded(): bool {
    $expirationVisibility = $this
      ->config
      ->get('role_expire.config')
      ->get('role_expire_expiration_details_expanded');
    return $expirationVisibility ?? FALSE;
  }

}
