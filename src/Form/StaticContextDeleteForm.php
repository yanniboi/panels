<?php

/**
 * @file
 * Contains \Drupal\panels\Form\StaticContextDeleteForm.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\panels\Entity\DisplayVariantInterface;

/**
 * Provides a form for deleting an access condition.
 */
class StaticContextDeleteForm extends ConfirmFormBase {

  /**
   * The display variant entity this selection condition belongs to.
   *
   * @var \Drupal\panels\Entity\DisplayVariantInterface
   */
  protected $displayVariant;

  /**
   * The static context's machine name.
   *
   * @var array
   */
  protected $staticContext;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_display_variant_static_context_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the static context %label?', ['%label' => $this->displayVariant->getStaticContext($this->staticContext)['label']]);
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
  public function buildForm(array $form, FormStateInterface $form_state, DisplayVariantInterface $display_variant = NULL, $name = NULL) {
    $this->displayVariant = $display_variant;
    $this->staticContext = $name;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message($this->t('The static context %label has been removed.', ['%label' => $this->displayVariant->getStaticContext($this->staticContext)['label']]));
    $this->displayVariant->removeStaticContext($this->staticContext);
    $this->displayVariant->save();
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
