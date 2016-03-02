<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Form\AccessConditionEditForm.
 */

namespace Drupal\panels_mini\Form;

/**
 * Provides a form for editing an access condition.
 */
class AccessConditionEditForm extends AccessConditionFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_panels_mini_access_condition_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareCondition($condition_id) {
    // Load the access condition directly from the mini_panel entity.
    return $this->mini_panel->getAccessCondition($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitButtonText() {
    return $this->t('Update access condition');
  }

  /**
   * {@inheritdoc}
   */
  protected function submitMessageText() {
    return $this->t('The %label access condition has been updated.', ['%label' => $this->condition->getPluginDefinition()['label']]);
  }

}
