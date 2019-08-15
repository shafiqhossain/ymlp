<?php

namespace Drupal\ymlp;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Ymlp subscriber entity.
 *
 * @see \Drupal\ymlp\Entity\YmlpSubscriber.
 */
class YmlpSubscriberAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\ymlp\Entity\YmlpSubscriberInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished ymlp subscribers');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published ymlp subscribers');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit ymlp subscribers');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete ymlp subscribers');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add ymlp subscribers');
  }

}
