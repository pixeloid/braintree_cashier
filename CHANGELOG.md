# CHANGELOG

## unreleased

* [#3014489]
* [#3041217]
* [#3041619]

Please be sure to rebuild the cache to pick up the new Cron service.

## 8.x-2.1

* Prevent a user from updating their currently active subscription 
  to the same billing plan, unless the subscription is on a grace 
  period (ie. will cancel at period end).

* prevent duplicate submission of the Subscription Update form.

## 8.x-2.0

* Initial stable release.

## 8.x-2.0-rc3

WARNING: backup your database before updating since this update
involves transferring data to a new field type for the
Period End Date field.

* The `datetime` module will automatically be enabled if it isn't
  already.
* All pending entity definition updates will be automatically
  applied during the course of updating the base field type
  for the period_end_date field.

### update tasks
* Clear the cache to pick up the new QueueWorker plugin and route
  paths.
* Update the URL's for the Discount List, Subscription List, and 
  Billing Plan List Views to reflect the new collection URL's for
  these entity types. The URL's have changed to a plural suffix,
  replacing "-list" with "s", as in 
  "admin/braintree-cashier/billing-plan-list" to 
  "admin/braintree-cashier/billing-plans". See the patch in
  [#3021086] for more details.

### changes

* [#3021594]
* [#3021334]
* move processing Braintree webhooks into a Queue to avoid a race of 
  processing the same subscription simultaneously. This means webhooks
  will be processed during cron runs, not at the time the webhooks
  are received.
* [#3021086]

## 8.x-2.0-rc2

### Update tasks

Run `drush entity-updates` to pick up new date fields, and the discount
entity reference field.

Run `drush updb` to enqueue populating the new date fields with data.

The following new configuration has been added for Message templates:

field.field.message.duplicate_payment_method.field_duplicate_user.yml
field.field.message.free_trial_ended.field_subscription.yml
field.field.message.free_trial_started.field_subscription.yml
field.field.message.subscription_canceled_by_webhook.field_subscription.yml
field.field.message.subscription_ended.field_subscription.yml
field.field.message.subscription_expired_by_webhook.field_subscription.yml
field.storage.message.field_duplicate_user.yml
message.template.duplicate_payment_method.yml
message.template.free_trial_ended.yml
message.template.free_trial_started.yml
message.template.subscription_canceled_by_webhook.yml
message.template.subscription_ended.yml
message.template.subscription_expired_by_webhook.yml

Import each new configuration using the Configuration Update Manager
module: https://www.drupal.org/project/config_update
Import configuration in order according to prefix:
1) message.template.*
2) field.storage.*
3) field.field.*


Due to changing routes from /braintree-cashier to /admin/braintree-cashier,
you will need to modify the path for any View that begins with 
/braintree-cashier and change it to begin with /admin/braintree-cashier

### Other changes
* make additional fields visible when viewing a Subscription.
* Add date fields to record date free trial started, date free trial ended,
  date subscription canceled by user, and date subscription ended.
* Create update hook to populate new date fields on existing subscriptions
  with QueueWorker on cron.
* [#3016219]
* remove field_permissions dependency from duplicate_user field in Message
  template.
* fix an issue where the drop-in would be undefined if the "Confirm coupon" 
  button was pressed.
* record which discount was applied to which subscription with an 
  entity_reference field on the Subscription entity.
* replace deprecated drupal_set_message().

## 8.x-2.0-rc1

Do not use this release since it throws exceptions due to an error 
while refactoring drupal_set_message().

## 8.x-2.0-beta14

* There is a new setting to enable the coupon field on the signup form.
  Run `drush updb` to import this setting.

* [#3018032]
* [#3015823]
