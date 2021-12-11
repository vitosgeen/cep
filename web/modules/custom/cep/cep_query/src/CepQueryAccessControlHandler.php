<?php

namespace Drupal\cep_query;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Cep query entity.
 *
 * @see \Drupal\cep_query\Entity\CepQuery.
 */
class CepQueryAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\cep_query\Entity\CepQueryInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished cep query entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published cep query entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit cep query entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete cep query entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add cep query entities');
  }


}
