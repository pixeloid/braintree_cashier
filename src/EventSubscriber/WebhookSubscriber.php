<?php

namespace Drupal\braintree_cashier\EventSubscriber;

use Drupal\braintree_api\Event\BraintreeApiEvents;
use Drupal\braintree_api\Event\BraintreeApiWebhookEvent;
use Drupal\braintree_cashier\BraintreeCashierService;
use Drupal\braintree_cashier\Entity\SubscriptionInterface;
use Drupal\braintree_cashier\SubscriptionService;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\braintree_api\BraintreeApiService;
use Drupal\Core\Logger\LoggerChannel;

/**
 * Class WebhookSubscriber.
 */
class WebhookSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\braintree_api\BraintreeApiService definition.
   *
   * @var \Drupal\braintree_api\BraintreeApiService
   */
  protected $braintreeApi;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Logger\LoggerChannel definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannel
   */
  protected $logger;

  /**
   * The subscription service.
   *
   * @var \Drupal\braintree_cashier\SubscriptionService
   */
  protected $subscriptionService;

  /**
   * The braintree cashier service.
   *
   * @var \Drupal\braintree_cashier\BraintreeCashierService
   */
  protected $bcService;

  /**
   * Constructs a new WebhookSubscriber object.
   */
  public function __construct(BraintreeApiService $braintree_api_braintree_api, EntityTypeManagerInterface $entity_type_manager, LoggerChannel $logger_channel_braintree_cashier, SubscriptionService $subscriptionService, BraintreeCashierService $bcService) {
    $this->braintreeApi = $braintree_api_braintree_api;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger_channel_braintree_cashier;
    $this->subscriptionService = $subscriptionService;
    $this->bcService = $bcService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[BraintreeApiEvents::WEBHOOK] = ['handleWebhook'];

    return $events;
  }

  /**
   * The event handler.
   *
   * This method is called whenever the
   * braintree_api.webhook_notification_received event is dispatched. This
   * occurs when Braintree sends a webhook to this website.
   *
   * @param \Drupal\braintree_api\Event\BraintreeApiWebhookEvent $event
   *   The BraintreeApiWebhookEvent.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function handleWebhook(BraintreeApiWebhookEvent $event) {
    $subscription_webhooks = [
      \Braintree_WebhookNotification::SUBSCRIPTION_CANCELED,
      \Braintree_WebhookNotification::SUBSCRIPTION_EXPIRED,
      \Braintree_WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY,
    ];

    if (in_array($event->getKind(), $subscription_webhooks)) {
      $braintree_subscription = $event->getWebhookNotification()->subscription;
      // Only process renewals. The local subscription entity might not have
      // been created yet. Check if the currentBillingCycle property exists
      // since test webhooks don't have that property.
      // @see \Braintree\WebhookTesting::_subscriptionChargedSuccessfullySampleXml.
      $is_first_billing_period = !empty($braintree_subscription->currentBillingCycle) && $braintree_subscription->currentBillingCycle < 2;
      if ($event->getKind() == \Braintree_WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY && $is_first_billing_period) {
        return;
      }
      try {
        $subscription_entity = $this->subscriptionService->findSubscriptionEntity($braintree_subscription->id);
      }
      catch (\Exception $e) {
        $this->logger->emergency($e->getMessage());
        $this->bcService->sendAdminErrorEmail($e->getMessage());
        return;
      }

      // Process an expired or canceled subscription.
      if (\in_array($event->getKind(), [
        \Braintree_WebhookNotification::SUBSCRIPTION_CANCELED,
        \Braintree_WebhookNotification::SUBSCRIPTION_EXPIRED,
      ], TRUE)) {
        $subscription_entity->setStatus(SubscriptionInterface::CANCELED);
        $subscription_entity->save();
      }

      // Process a renewal. Update the period end date.
      if ($event->getKind() === \Braintree_WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY) {
        // Note that an upgrade mid-billing-cycle would also trigger this
        // webhook.
        $subscription_entity->setPeriodEndDate($braintree_subscription->billingPeriodEndDate->getTimestamp());
        $subscription_entity->save();
      }
    }
  }

}
