<?php

namespace Drupal\braintree_cashier\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Discount entities.
 *
 * @deprecated This will be removed in the 8.4.x branch of Braintree Cashier.
 */
class DiscountViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}
