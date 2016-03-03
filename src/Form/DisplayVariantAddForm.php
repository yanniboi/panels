<?php

/**
 * @file
 * Contains Drupal\panels\Form\DisplayVariantAddForm.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\Form\DisplayVariantFormBase;

/**
 * Provides a form for adding a variant.
 */
class DisplayVariantAddForm extends DisplayVariantFormBase {

  /**
   * {@inheritdoc}
   */
  protected function submitText() {
    return $this->t('Add variant');
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    $form_state->setRedirectUrl($this->getEntity()->toUrl($this->getEntity()->get('display_entity_type') . '-edit-form'));
  }

}
