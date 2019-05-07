<?php

namespace Drupal\braintree_cashier\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Billing plan entities.
 *
 * @deprecated This will be removed in the 8.4.x branch of Braintree Cashier.
 */
class BillingPlanViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    return $data;
  }

}
