# CHANGELOG

## unreleased

Run `drush entity-updates` to pick up new date fields.
Run `drush updb` to enqueue populating the new date fields with data.

* make additional fields visible when viewing a Subscription.
* Add date fields to record date free trial started, date free trial ended,
  date subscription canceled by user, and date subscription ended.
* Create update hook to populate new date fields on existing subscriptions
  with QueueWorker on cron.


## 8.x-2.0-beta14

* There is a new setting to enable the coupon field on the signup form.
  Run `drush updb` to import this setting.

* [#3018032]
* [#3015823]
