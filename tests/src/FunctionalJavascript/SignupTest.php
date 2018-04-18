<?php

namespace Drupal\Tests\braintree_cashier\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\JavascriptTestBase;

/**
 * Class SignupTest.
 *
 * @package Drupal\Tests\braintree_cashier\FunctionalJavascript
 *
 * @group braintree_cashier
 */
class SignupTest extends JavascriptTestBase {

  use BraintreeCashierTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['braintree_cashier'];

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * The account created for the test.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->setupBraintreeApi();
    $this->createMonthlyBillingPlan();

    $this->account = $this->drupalCreateUser();
    $this->drupalLogin($this->account);
    $this->drupalGet(Url::fromRoute('braintree_cashier.signup_form'));
  }

  /**
   * Test that a new logged in user may signup for a subscription.
   */
  public function testCardSignup() {
    $this->fillInCardForm($this, [
      'card_number' => '4242424242424242',
      'expiration' => '1123',
      'cvv' => '123',
      'postal_code' => '12345',
    ]);

    $this->getSession()->getPage()->find('css', '#submit-button')->click();
    $this->assertSession()->waitForElementVisible('css', '.messages--status', 30000);
    $this->assertSession()->pageTextContains('You have been signed up for the CI Monthly plan. Thank you, and enjoy your subscription!');
  }

  /**
   * Test that the invoice amounts are correct after signup.
   */
  public function testInvoiceAmount() {
    $this->testCardSignup();
    $this->drupalGet(Url::fromRoute('braintree_cashier.invoices', [
      'user' => $this->account->id(),
    ]));
    $this->assertSession()->elementTextContains('css', '.payment-history', '$12.00');
    $this->assertSession()->elementTextContains('css', '.upcoming-invoice', '$12.00');
  }

  /**
   * Tests an alternative checkout flow where the payment method is added first.
   *
   * After the payment method is added, then the user selects a subscription
   * from the My Subscription tab.
   */
  public function testAlternativeCheckoutFlow() {
    $this->drupalGet('user');
    $this->getSession()->getPage()->clickLink('Subscription');
    $this->getSession()->getPage()->clickLink('Payment method');
    $this->fillInCardForm($this, [
      'card_number' => '4242424242424242',
      'expiration' => '1123',
      'cvv' => '123',
      'postal_code' => '12345',
    ]);
    $this->getSession()->getPage()->find('css', '#submit-button')->click();
    $this->assertSession()->waitForElementVisible('css', '.messages--status', 20000);
    $this->assertSession()->pageTextContains('Your payment method has been updated successfully!');

    $this->getSession()->getPage()->clickLink('My Subscription');

    $this->getSession()->getPage()->pressButton('Update plan');

    $this->assertSession()->pageTextContains('Your new plan will be: CI Monthly for $12 / month');
    $this->getSession()->getPage()->pressButton('Confirm');

    $this->assertSession()->waitForElementVisible('css', '.messages--status', 20000);
    $this->assertSession()->pageTextContains('Your subscription has been updated!');
  }

}
