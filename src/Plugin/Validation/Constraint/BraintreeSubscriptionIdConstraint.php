<?php

namespace Drupal\braintree_cashier\Plugin\Validation\Constraint;

use Drupal\Core\Entity\Plugin\Validation\Constraint\CompositeConstraintBase;

/**
 * Validates that the period end date is set when cancel at period end is true.
 *
 * @Constraint(
 *   id = "BraintreeSubscriptionId",
 *   label = @Translation("Braintree Subscription ID constraint", context = "Validation"),
 *   type = "entity:subscription"
 * )
 *
 * @deprecated This will be removed in the 8.4.x branch of Braintree Cashier.
 */
class BraintreeSubscriptionIdConstraint extends CompositeConstraintBase {

  public $message = "The Braintree subscription ID field must be filled for the selected subscription type.";

  /**
   * {@inheritdoc}
   */
  public function coversFields() {
    return ['braintree_subscription_id', 'subscription_type'];
  }

}
