<?php

namespace Drupal\term_access_by_domain;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the taxonomy term entity type.
 *
 * @see \Drupal\taxonomy\Entity\Term
 */
class TABDControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) { 
    
	
	
	if ($account->hasPermission('administer taxonomy')) {
      return AccessResult::allowed()->cachePerPermissions();
    }

	//return AccessResult::forbidden()->setReason("check $operation");
	
    switch ($operation) {
      case 'view':
			$domain = \Drupal::request()->getHost();
			$uid = \Drupal::currentUser()->id();
			$vid = $entity->bundle();
	  
		if($vid == 'key_people' || $vid == 'key_people_atoz_services' || $vid == 'gdpr_client'){
			
			//return AccessResult::forbidden()->setReason("check $vid and $domain");
			
			if($vid == 'key_people' && strpos($domain,'atoz.lu') === false && strpos($domain,'atoz.smartpixel.lu') === false){
				return AccessResult::forbidden()->setReason("The 'key_people' Taxonomy is only available on ATOZ domain.");
				//$this->messenger()->addMessage("The 'key_people' Taxonomy is only available on ATOZ domain.");
				//return $this->redirect('https://atoz.smartpixel.lu/people');
			} 
			else if($vid == 'key_people_atoz_services' && strpos($domain,'atoz-services.lu') === false){
				return AccessResult::forbidden()->setReason("The 'key_people_atoz_service' Taxonomy is only available on ATOZ-Services domain.");
				//$this->messenger()->addMessage("The 'key_people' Taxonomy is only available on ATOZ domain.");
				//return $this->redirect('https://www.atoz-services.lu/atoz-services-people');
			}
			else if($vid == 'gdpr_client' && $uid != 48){ // $account.getRoles ...
				return AccessResult::forbidden()->setReason("This Taxonomy is not available.");
			}
		}

        $access_result = AccessResult::allowedIf($account->hasPermission('access content') && $entity->isPublished())
          ->cachePerPermissions()
          ->addCacheableDependency($entity);
        if (!$access_result->isAllowed()) {
          $access_result->setReason("The 'access content' permission is required and the taxonomy term must be published.");
        }
        return $access_result;

      case 'update':
        if ($account->hasPermission("edit terms in {$entity->bundle()}")) {
          return AccessResult::allowed()->cachePerPermissions();
        }

        return AccessResult::neutral()->setReason("The following permissions are required: 'edit terms in {$entity->bundle()}' OR 'administer taxonomy'.");

      case 'delete':
        if ($account->hasPermission("delete terms in {$entity->bundle()}")) {
          return AccessResult::allowed()->cachePerPermissions();
        }

        return AccessResult::neutral()->setReason("The following permissions are required: 'delete terms in {$entity->bundle()}' OR 'administer taxonomy'.");

      default:
        // No opinion.
        return AccessResult::neutral()->cachePerPermissions();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ["create terms in $entity_bundle", 'administer taxonomy'], 'OR');
  }

}