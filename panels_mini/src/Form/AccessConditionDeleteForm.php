<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Form\AccessConditionDeleteForm.
 */

namespace Drupal\panels_mini\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\panels\Entity\DisplayInterface;

/**
 * Provides a form for deleting an access condition.
 */
class AccessConditionDeleteForm extends ConfirmFormBase {

  /**
   * The mini_panel entity this selection condition belongs to.
   *
   * @var \Drupal\panels\Entity\DisplayInterface
   */
  protected $mini_panel;

  /**
   * The access condition used by this form.
   *
   * @var \Drupal\Core\Condition\ConditionInterface
   */
  protected $accessCondition;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_panels_mini_access_condition_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the access condition %name?', ['%name' => $this->accessCondition->getPluginDefinition()['label']]);
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
  public function buildForm(array $form, FormStateInterface $form_state, DisplayInterface $mini_panel = NULL, $condition_id = NULL) {
    $this->mini_panel = $mini_panel;
    $this->accessCondition = $mini_panel->getAccessCondition($condition_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->mini_panel->removeAccessCondition($this->accessCondition->getConfiguration()['uuid']);
    $this->mini_panel->save();
    drupal_set_message($this->t('The access condition %name has been removed.', ['%name' => $this->accessCondition->getPluginDefinition()['label']]));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
