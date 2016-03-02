<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Form\ParameterDeleteForm.
 */

namespace Drupal\panels_mini\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\panels\Entity\DisplayInterface;
/**
 * Provides a form for deleting an access condition.
 */
class ParameterDeleteForm extends ConfirmFormBase {

  /**
   * The mini_panel entity this static context belongs to.
   *
   * @var \Drupal\panels\Entity\DisplayInterface
   */
  protected $mini_panel;

  /**
   * The parameter configuration.
   *
   * @var array
   */
  protected $parameter;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_panels_mini_parameter_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the parameter %label?', ['%label' => $this->mini_panel->getParameter($this->parameter)['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->mini_panel->toUrl('edit-form');
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
  public function buildForm(array $form, FormStateInterface $form_state, DisplayInterface $mini_panel = NULL, $name = NULL) {
    $this->mini_panel = $mini_panel;
    $this->parameter = $name;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message($this->t('The parameter %label has been removed.', ['%label' => $this->mini_panel->getParameter($this->parameter)['label']]));
    $this->mini_panel->removeParameter($this->parameter);
    $this->mini_panel->save();
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
