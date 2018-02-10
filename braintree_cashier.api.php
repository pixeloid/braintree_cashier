<?php

/**
 * @file
 * Contains braintree_cashier.api.php.
 */

use Drupal\braintree_cashier\Entity\BillingPlanInterface;
use Drupal\braintree_cashier_enterprise\EnterpriseService;
use Drupal\Core\Form\FormStateInterface;

/**
 * Alter the subscription types that need a Braintree subscription ID.
 *
 * Needed for the BraintreeSubscriptionIdConstraintValidator.
 *
 * @param array $types
 *   An array of subscription type machine names.
 */
function hook_braintree_cashier_subscription_types_need_braintree_id_alter(array &$types) {
  $types[] = EnterpriseService::ENTERPRISE_MANAGER;
}

/**
 * Alter the subscription type options for the subscription entity.
 *
 * @param array $options
 *   An array of subscription types keyed by machinename with human readable
 *   values.
 */
function hook_braintree_cashier_subscription_type_options(array &$options) {
  $options = array_merge([
    EnterpriseService::ENTERPRISE_MANAGER => t('Enterprise Manager'),
    EnterpriseService::ENTERPRISE_INDIVIDUAL => t('Enterprise Individual'),
  ], $options);
}

/**
 * Alter the subscription types that may be created by a billing plan.
 *
 * @param array $options
 *   An array of subscription types keyed by machinename with human readable
 *   values.
 */
function hook_braintree_cashier_billing_plan_subscription_type_options_alter(array &$options) {
  $options = array_merge([
    EnterpriseService::ENTERPRISE_MANAGER => t('Enterprise Manager'),
  ], $options);
}

/**
 * Alter the parameters used to create a new subscription entity.
 *
 * Used when the entity is created by
 * \Drupal\braintree_cashier\SubscriptionService::createSubscriptionEntity.
 *
 * @param array $params
 *   The array of parameters passed to SubscriptionInterface::create().
 * @param \Drupal\braintree_cashier\Entity\BillingPlanInterface $billing_plan
 *   The billing plan entity from which the subscription will be created.
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state of the sign up form.
 */
function hook_braintree_cashier_create_subscription_params_alter(array &$params, BillingPlanInterface &$billing_plan, FormStateInterface $form_state = NULL) {
  if ($billing_plan->getSubscriptionType() == EnterpriseService::ENTERPRISE_MANAGER) {
    $params = array_merge([
      'num_users' => \Drupal::getContainer()->get('braintree_cashier_enterprise.enterprise_service')->getBillingPlanNumUsers($billing_plan),
    ], $params);
  }
}
