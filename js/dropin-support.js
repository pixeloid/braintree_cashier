/**
 * @file
 * Supports the signup form created with Braintree's Drop-in UI.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Callback for the click event on the visible submit button.
   */
  function onInitialButtonClick(event) {
    event.preventDefault();

    event.data.buttonInitial.prop('disabled', true)
      .addClass('is-disabled');

    event.data.instance.requestPaymentMethod(function (requestPaymentMethodErr, payload) {
      if (requestPaymentMethodErr) {
        event.data.buttonInitial.prop('disabled', false)
          .removeClass('is-disabled');
        return;
      }
      event.data.nonceField.val(payload.nonce);
      event.data.buttonFinal.click();
    });
  }

  /**
   * Callback for after the Dropin UI instance is created.
   */
  function onInstanceCreate(createErr, instance) {
    var buttonInitial = $('#submit-button');
    var buttonFinal = $('#final-submit');
    var nonceField = $('#payment-method-nonce');

    buttonInitial.prop('disabled', false)
      .removeClass('is-disabled')
      .click({
        instance: instance,
        buttonInitial: buttonInitial,
        buttonFinal: buttonFinal,
        nonceField: nonceField
      }, onInitialButtonClick);
  }

  Drupal.behaviors.signupForm = {
    attach: function (context, settings) {

      var createParams = {
        authorization: drupalSettings.braintree_cashier.authorization,
        container: '#dropin-container'
      };

      if (drupalSettings.braintree_cashier.acceptPaypal) {
        createParams.paypal = {
          flow: 'vault'
        };
      }

      braintree.dropin.create(createParams, onInstanceCreate);
    }
  };


})(jQuery, Drupal, drupalSettings);
