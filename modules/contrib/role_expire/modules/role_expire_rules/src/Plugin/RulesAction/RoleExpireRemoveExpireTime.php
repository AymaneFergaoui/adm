<?php

namespace Drupal\role_expire_rules\Plugin\RulesAction;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\role_expire\RoleExpireApiService;
use Drupal\rules\Core\RulesActionBase;
use Drupal\rules\Exception\InvalidArgumentException;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Remove expire time' action.
 *
 * @RulesAction(
 *   id = "role_expire_remove_expire_time",
 *   label = @Translation("Remove expire time for user roles"),
 *   category = @Translation("User"),
 *   context_definitions = {
 *     "user" = @ContextDefinition("entity:user",
 *       label = @Translation("User")
 *     ),
 *     "roles" = @ContextDefinition("string",
 *       label = @Translation("Roles ID"),
 *       multiple = TRUE
 *     )
 *   }
 * )
 */
class RoleExpireRemoveExpireTime extends RulesActionBase implements ContainerFactoryPluginInterface {

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
   *
   * @throws \Drupal\rules\Exception\InvalidArgumentException
   */
  protected function doExecute(UserInterface $user, array $roles): void {
    foreach ($roles as $role) {
      // Skip adding the expire time for the role if user doesn't have it.
      if ($user->hasRole($role)) {
        try {
          $this->roleExpireApiService->deleteRecord($user->id(), $role);
        }
        catch (\InvalidArgumentException $e) {
          throw new InvalidArgumentException($e->getMessage());
        }
      }
    }
  }

}
