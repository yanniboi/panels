<?php

/**
 * @file
 * Contains \Drupal\panels\Form\ParameterEditForm.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\Entity\DisplayInterface;

/**
 * Provides a form for editing a parameter.
 */
class ParameterEditForm extends ParameterFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_panels_mini_parameter_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function submitButtonText() {
    return $this->t('Update Parameter');
  }

  /**
   * {@inheritdoc}
   */
  protected function submitMessageText() {
    return $this->t('The %label parameter has been updated.', ['%label' => $this->parameter['label']]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, DisplayInterface $mini_panel = NULL, $name = '') {
    $this->mini_panel = $mini_panel;
    $this->parameter = $this->mini_panel->getParameter($name);
    $form = parent::buildForm($form, $form_state, $mini_panel, $name);
    // The machine name of an existing context is read-only.
    $form['machine_name'] = array(
      '#type' => 'value',
      '#value' => $name,
    );
    return $form;
  }

}
