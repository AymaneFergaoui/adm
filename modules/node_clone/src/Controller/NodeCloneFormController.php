<?php

namespace Drupal\node_clone\Controller;

use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultForbidden;
use Drupal\Core\Controller\FormController;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\node\Access\NodeAddAccessCheck;
use Drupal\node\Entity\NodeType;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Wrapping controller for entity forms that serve as the main page body.
 *
 * This class is very similar to \Drupal\Core\Entity\HtmlEntityFormController
 */
class NodeCloneFormController extends FormController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\node\Access\NodeAddAccessCheck
   */
  protected $nodeAddAccessCheck;

  /**
   * Constructs a new NodeCloneFormController object.
   */
  public function __construct($argument_resolver, FormBuilderInterface $form_builder, EntityTypeManagerInterface $manager) {
    parent::__construct($argument_resolver, $form_builder);
    $this->entityTypeManager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_kernel.controller.argument_resolver'),
      $container->get('form_builder'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormArgument(RouteMatchInterface $route_match) {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  protected function getFormObject(RouteMatchInterface $route_match, $form_arg) {
    // If no operation is provided, use 'default'.
    $form_arg .= '.default';
    list ($entity_type_id, $operation) = explode('.', $form_arg);

    $form_object = $this->entityTypeManager->getFormObject($entity_type_id, $operation);

    // Allow the entity form to determine the entity object from a given route
    // match.
    $entity = $form_object->getEntityFromRouteMatch($route_match, $entity_type_id);
    // Clone the entity using the awesome createDuplicate() core function
    $new_entity = $entity->createDuplicate();
    $new_entity->setTitle($this->t('Clone of ') . $new_entity->getTitle());
    $form_object->setEntity($new_entity);

    return $form_object;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @param \Drupal\node\NodeInterface $node
   * @return \Drupal\Core\Access\AccessResultInterface
   */
  public function access(AccountInterface $account, NodeInterface $node) {
    if ($account->hasPermission('clone node') || ($node->uid->value === $account->id() && $account->hasPermission('clone own nodes'))) {
      $access = new AccessResultAllowed();
    }
    else {
      $access = new AccessResultForbidden();
    }
    $access->addCacheableDependency($node);
    $access->cachePerPermissions();
    if ($access->isAllowed()) {
      $access = $access->andIf($node->access('view', $account, TRUE));
    }
    if ($access->isAllowed()) {
      $access = $access->andIf($this->entityTypeManager->getAccessControlHandler('node')->createAccess($node->getType(), $account, [], TRUE));
    }
    return $access;
  }

}
