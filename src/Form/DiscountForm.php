<?php

namespace Drupal\braintree_cashier\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Discount edit forms.
 *
 * @ingroup braintree_cashier
 */
class DiscountForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\braintree_cashier\Entity\Discount */
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger->addStatus($this->t('Created the %label Discount.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger->addStatus($this->t('Saved the %label Discount.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.discount.canonical', ['discount' => $entity->id()]);
  }

}
