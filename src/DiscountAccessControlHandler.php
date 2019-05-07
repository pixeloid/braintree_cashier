<?php

namespace Drupal\braintree_cashier;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Discount entity.
 *
 * @see \Drupal\braintree_cashier\Entity\Discount.
 *
 * @deprecated This will be removed in the 8.4.x branch of Braintree Cashier.
 */
class DiscountAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\braintree_cashier\Entity\DiscountInterface $entity */
    return AccessResult::allowedIfHasPermission($account, 'administer braintree cashier');
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer braintree cashier');
  }

}
