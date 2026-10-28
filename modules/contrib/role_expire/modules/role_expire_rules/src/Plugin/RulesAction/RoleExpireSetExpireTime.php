<?php

namespace Drupal\role_expire_rules\Plugin\RulesAction;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\role_expire\RoleExpireApiService;
use Drupal\rules\Core\RulesActionBase;
use Drupal\rules\Exception\InvalidArgumentException;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Set expire time' action.
 *
 * @RulesAction(
 *   id = "role_expire_set_expire_time",
 *   label = @Translation("Set expire time for user roles"),
 *   category = @Translation("User"),
 *   context_definitions = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User")
 *     ),
 *     "roles" = @ContextDefinition("string",
 *       label = @Translation("Roles ID"),
 *       multiple = TRUE
 *     ),
 *     "date" = @ContextDefinition("string",
 *       label = @Translation("Roles expiry date"),
 *       description = @Translation("Enter date and time in format <em>YYYY-MM-DD HH:MM:SS</em> or use relative time i.e. 1 day, 2 months, 1 year, 3 years.")
 *     )
 *   }
 * )
 */
class RoleExpireSetExpireTime extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * Role expire API service.
   *
   * @var \Drupal\role_expire\Plugin\RulesAction\RoleExpireApiService
   */
  private $roleExpireApiService;

  /**
   * Constructs an EntityCreate object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\role_expire\Plugin\RulesAction\RoleExpireApiService $roleExpireApiService
   *   Role expire API service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RoleExpireApiService $roleExpireApiService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->roleExpireApiService = $roleExpireApiService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('role_expire.api')
    );
  }

  /**
   * Assign expire time for user and role.
   *
   * @param \Drupal\user\UserInterface $user
   *   User object.
   * @param array $roles
   *   Array of User roles ID.
   * @param string $date
   *   Date when the roles will expire.
   *
   * @throws \Drupal\rules\Exception\InvalidArgumentException
   */
  protected function doExecute(UserInterface $user, array $roles, $date): void {
    foreach ($roles as $role) {
      // Skip adding the expire time for the role if user doesn't have it.
      if ($user->hasRole($role)) {
        try {
          $time = strtotime($date);
          if (!empty($time)) {
            $this->roleExpireApiService->writeRecord($user->id(), $role, $time);
          }
          else {
            throw new InvalidArgumentException($this->t('Invalid date format.'));
          }
        }
        catch (\InvalidArgumentException $e) {
          throw new InvalidArgumentException($e->getMessage());
        }
      }
    }
  }

}
