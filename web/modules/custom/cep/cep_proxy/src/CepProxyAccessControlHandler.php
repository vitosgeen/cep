<?php

namespace Drupal\cep_proxy;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the CEP proxy entity.
 *
 * @see \Drupal\cep_proxy\Entity\CepProxy.
 */
class CepProxyAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\cep_proxy\Entity\CepProxyInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished CEP proxy entities');
        }

        return AccessResult::allowedIfHasPermission($account, 'view published CEP proxy entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit CEP proxy entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete CEP proxy entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add CEP proxy entities');
  }

}
