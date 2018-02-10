<?php

namespace Drupal\braintree_cashier\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Ensure that the Braintree billing plan ID is unique per environment.
 */
class BillingPlanEnvironmentConstraintValidator extends ConstraintValidator {

  /**
   * Validator 2.5 and upwards compatible execution context.
   *
   * @var \Symfony\Component\Validator\Context\ExecutionContextInterface
   */
  protected $context;

  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    /** @var \Drupal\braintree_cashier\Entity\BillingPlanInterface $entity */
    if (empty($entity->getBraintreePlanId())) {
      return;
    }

    $value_taken = (bool) \Drupal::entityQuery('billing_plan')
      ->condition('environment', $entity->getEnvironment())
      ->condition('braintree_plan_id', $entity->getBraintreePlanId())
      // The id could be NULL, so we cast it to 0 in that case.
      ->condition('id', (int) $entity->id(), '<>')
      ->range(0, 1)
      ->count()
      ->execute();

    if ($value_taken) {
      $this->context->buildViolation($constraint->message, [
        '%plan_id' => $entity->getBraintreePlanId(),
        '%environment' => $entity->getEnvironment(),
      ])
        ->atPath('braintree_plan_id')
        ->addViolation();
    }
  }

}
