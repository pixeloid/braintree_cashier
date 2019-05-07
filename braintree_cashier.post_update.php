<?php

/**
 * @file
 * Post update hook implementations for braintree_cashier.
 */

use Drupal\field\Entity\FieldStorageConfig;

/**
 * Update configuration for new entity types.
 *
 * You must manually migrate data to the new entity types. Please consult
 * the 8.3.0 release notes for instructions.
 */
function braintree_cashier_post_update_new_entity_types() {
  // Clear caches so Drupal picks up new entity definitions.
  drupal_flush_all_caches();

  $entity_update_manager = \Drupal::entityDefinitionUpdateManager();

  $target_type_map = [
    'billing_plan' => 'braintree_cashier_billing_plan',
    'discount' => 'braintree_cashier_discount',
    'subscription' => 'braintree_cashier_subscription',
  ];

  $entity_types = $entity_update_manager->getEntityTypes();

  $old_types_exist = FALSE;
  foreach (array_keys($target_type_map) as $old_entity_type) {
    if (in_array($old_entity_type, array_keys($entity_types))) {
      $old_types_exist = TRUE;
    }
  }

  if ($old_types_exist) {

    // Update the bundle field storage configuration for reference fields that
    // have their target type set to one of the entity types that will be
    // uninstalled.
    $field_query = Drupal::entityQuery('field_storage_config');
    $conditions = $field_query->orConditionGroup()
      ->condition('settings.target_type', 'billing_plan')
      ->condition('settings.target_type', 'discount')
      ->condition('settings.target_type', 'subscription');
    $field_query_result = $field_query
      ->condition($conditions)
      ->execute();

    foreach ($field_query_result as $result) {
      $result_array = explode('.', $result);
      $entity_type = $result_array[0];
      $field_name = $result_array[1];
      $field_storage_config = FieldStorageConfig::loadByName($entity_type, $field_name);
      $field_target_type = $field_storage_config->getSetting('target_type');
      $field_storage_config->setSetting('target_type', $target_type_map[$field_target_type]);
      $field_storage_config->trustData();
      $field_storage_config->save();
    }

    $config_factory = \Drupal::configFactory();
    // Update billing plan overview config.
    $billing_plan_overview = $config_factory->getEditable('core.entity_view_mode.billing_plan.overview');
    $billing_plan_overview->set('targetEntityType', 'braintree_cashier_billing_plan');
    $billing_plan_overview->set('id', 'braintree_cashier_billing_plan.overview');
    $billing_plan_overview->save();
    $config_factory->rename('core.entity_view_mode.billing_plan.overview', 'core.entity_view_mode.braintree_cashier_billing_plan.overview');

    // Update views config.
    $base_table_map = [
      'subscription' => 'braintree_cashier_subscription',
      'billing_plan_field_data' => 'braintree_cashier_billing_plan_field_data',
      'discount' => 'braintree_cashier_discount',
      'discount__billing_plan' => 'braintree_cashier_discount__billing_plan',
    ];
    $views = [
      'subscription_list' => [],
      'billing_plan_list' => [],
      'discount_list' => [],
      'plans_overview' => [
        'row_type' => 'entity:braintree_cashier_billing_plan',
        'config_dependencies' => [
          'add' => [
            'core.entity_view_mode.braintree_cashier_billing_plan.overview',
          ],
          'remove' => [
            'core.entity_view_mode.billing_plan.overview',
          ],
        ],

      ],
      'sandbox_plans_overview' => [
        'row_type' => 'entity:braintree_cashier_billing_plan',
        'config_dependencies' => [
          'add' => [
            'core.entity_view_mode.braintree_cashier_billing_plan.overview',
          ],
          'remove' => [
            'core.entity_view_mode.billing_plan.overview',
          ],
        ],

      ],
    ];
    foreach ($views as $view_name => $config) {
      $view = $config_factory->getEditable('views.view.' . $view_name);
      if (!empty($base_table_map[$view->get('base_table')])) {
        $view->set('base_table', $base_table_map[$view->get('base_table')]);
      }
      $view->set('status', TRUE);
      $displays = $view->get('display');
      $display_section_names = [
        'fields',
        'filters',
        'sorts',
      ];
      foreach ($displays as $display_name => $display) {
        $base_display_options = "display.$display_name.display_options";
        foreach ($display_section_names as $display_section_name) {
          if (!empty($display['display_options'][$display_section_name])) {
            foreach ($display['display_options'][$display_section_name] as $section_member_name => $section_member) {
              $config_base_name = "$base_display_options.$display_section_name.$section_member_name";
              // Update table key.
              if (!empty($section_member['table']) && !empty($base_table_map[$section_member['table']])) {
                $view->set($config_base_name . '.table', $base_table_map[$section_member['table']]);
              }
              // Update entity_type key.
              if (!empty($section_member['entity_type'] && !empty($target_type_map[$section_member['entity_type']]))) {
                $view->set($config_base_name . '.entity_type', $target_type_map[$section_member['entity_type']]);
              }

            }
          }
        }
        if (isset($config['row_type'])) {
          $view->set($base_display_options . '.row.type', $config['row_type']);
        }
        // Update paths of the admin views to override the collection routes
        // of the new entity types.
        switch ($view_name) {
          case 'billing_plan_list':
            if ($display_name == 'page_1') {
              $view->set('display.page_1.display_options.path', 'admin/braintree-cashier/braintree_cashier_billing_plans');
            }
            break;

          case 'subscription_list':
            if ($display_name == 'page_1') {
              $view->set('display.page_1.display_options.path', 'admin/braintree-cashier/braintree_cashier_subscriptions');
            }
            break;

          case 'discount_list':
            if ($display_name == 'page_1') {
              $view->set('display.page_1.display_options.path', 'admin/braintree-cashier/braintree_cashier_discounts');
            }
            break;
        }
      }
      if (isset($config['config_dependencies'])) {
        $config_dependencies = $view->get('dependencies.config');
        foreach ($config['config_dependencies']['remove'] as $remove) {
          if (($key = array_search($remove, $config_dependencies)) !== FALSE) {
            unset($config_dependencies[$key]);
          }
        }
        foreach ($config['config_dependencies']['add'] as $add) {
          $config_dependencies[] = $add;
        }
        $new_config_dependencies = [];
        // The config dependencies array needs to be non-associative.
        foreach ($config_dependencies as $key => $value) {
          $new_config_dependencies[] = $value;
        }
        $view->set('dependencies.config', $new_config_dependencies);
      }
      $view->save(TRUE);
    }

    // To ensure the updated views override entity type collection routes.
    drupal_flush_all_caches();
  }
}
