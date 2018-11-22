/**
 * @file
 * Supports the signup form created with Braintree's Drop-in UI.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  var dropinInstance;
  var buttonInitialSelector = '#submit-button';
  var buttonInitial;
  var buttonFinal;
  var nonceField;

  /**
   * Callback for the click event on the visible submit button.
   *
   * @param {jQuery.Event} event
   */
  function onInitialButtonClick(event) {
    event.preventDefault();

    buttonInitial.prop('disabled', true)
      .addClass('is-disabled');

    dropinInstance.requestPaymentMethod(function (requestPaymentMethodErr, payload) {
      if (requestPaymentMethodErr) {
        buttonInitial.prop('disabled', false)
          .removeClass('is-disabled')
          .click(onInitialButtonClick);
        return;
      }
      nonceField.val(payload.nonce);
      buttonFinal.click();
    });
    // Remove event handler since it was getting submitted multiple times
    // during automated tests.
    $.off('click', buttonInitialSelector, onInitialButtonClick);
  }

  /**
   * Callback for after the Dropin UI instance is created.
   *
   * @param createErr
   *   The error generated if the Dropin UI could not be created.
   * @param {object} instance
   *   The Braintree Dropin UI instance.
   *
   * @see https://braintree.github.io/braintree-web-drop-in/docs/current/Dropin.html
   */
  function onInstanceCreate(createErr, instance) {
    dropinInstance = instance;

    buttonInitial.prop('disabled', false)
      .removeClass('is-disabled')
      .click(onInitialButtonClick);
  }

  /**
   * Create the Braintree Dropin UI.
   *
   * @type {{attach: Drupal.behaviors.signupForm.attach}}
   */
  Drupal.behaviors.signupForm = {
    attach: function (context, settings) {

      buttonInitial = $(buttonInitialSelector);
      buttonFinal = $('#final-submit');
      nonceField = $('#payment-method-nonce');

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
