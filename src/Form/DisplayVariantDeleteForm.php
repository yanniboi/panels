<?php

/**
 * @file
 * Contains Drupal\panels\Form\DisplayVariantDeleteForm.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete a DisplayVariant.
 */
class DisplayVariantDeleteForm extends EntityConfirmFormBase {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\panels\Entity\DisplayVariantInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete %name?', ['%name' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $entity_id = $this->entity->get('display_entity_id');
    $entity_type = $this->entity->get('display_entity_type');
    return new Url('entity.' . $entity_type . '.edit_form', [
      $entity_type => $entity_id,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();

    drupal_set_message($this->t('The variant %label has been removed.', [
      '%label' => $this->entity->label(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
