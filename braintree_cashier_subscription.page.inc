<?php

/**
 * @file
 * Contains subscription.page.inc.
 *
 * Page callback for Subscription entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Subscription templates.
 *
 * Default template: braintree_cashier_subscription.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_braintree_cashier_subscription(array &$variables) {
  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
