<?php

namespace Drupal\braintree_cashier\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;

/**
 * Validates that the period end date is set when cancel at period end is true.
 *
 * @Constraint(
 *   id ="PeriodEndDateNotEmpty",
 *   label = @Translation("Period end date constraint", context = "Validation"),
 *   type = "entity:subscription"
 * )
 *
 * @deprecated This will be removed in the 8.4.x branch of Braintree Cashier.
 */
class PeriodEndDateNotEmptyConstraint extends CompositeConstraintBase {

  public $message = "The period end date must be set if the subscription will cancel at period end";

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return ['period_end_date', 'cancel_at_period_end', 'subscription_type'];
  }

}
