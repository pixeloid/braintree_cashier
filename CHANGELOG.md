# CHANGELOG

## unreleased

Run `drush entity-updates` to pick up new date fields.
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

* make additional fields visible when viewing a Subscription.
* Add date fields to record date free trial started, date free trial ended,
  date subscription canceled by user, and date subscription ended.
* Create update hook to populate new date fields on existing subscriptions
  with QueueWorker on cron.
* [#3016219]
* remove field_permissions dependency from duplicate_user field in Message template.


## 8.x-2.0-beta14

* There is a new setting to enable the coupon field on the signup form.
  Run `drush updb` to import this setting.

* [#3018032]
* [#3015823]
