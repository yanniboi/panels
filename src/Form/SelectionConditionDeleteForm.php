<?php

/**
 * @file
 * Contains \Drupal\panels\Form\SelectionConditionDeleteForm.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\panels\Entity\DisplayVariantInterface;

/**
 * Provides a form for deleting a selection condition.
 */
class SelectionConditionDeleteForm extends ConfirmFormBase {

  /**
   * The display variant entity this selection condition belongs to.
   *
   * @var \Drupal\panels\Entity\DisplayVariantInterface
   */
  protected $displayVariant;

  /**
   * The selection condition used by this form.
   *
   * @var \Drupal\Core\Condition\ConditionInterface
   */
  protected $selectionCondition;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_display_variant_selection_condition_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the selection condition %name?', ['%name' => $this->selectionCondition->getPluginDefinition()['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->displayVariant->toUrl('edit-form');
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
  public function buildForm(array $form, FormStateInterface $form_state, DisplayVariantInterface $display_variant = NULL, $condition_id = NULL) {
    $this->displayVariant = $display_variant;
    $this->selectionCondition = $display_variant->getSelectionCondition($condition_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->displayVariant->removeSelectionCondition($this->selectionCondition->getConfiguration()['uuid']);
    $this->displayVariant->save();
    drupal_set_message($this->t('The selection condition %name has been removed.', ['%name' => $this->selectionCondition->getPluginDefinition()['label']]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
