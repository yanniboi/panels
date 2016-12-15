<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Form\MiniPanelAddForm.
 */

namespace Drupal\panels_mini\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Form\DisplayFormBase;

/**
 * Provides a form for adding a new mini panel entity.
 */
class MiniPanelAddForm extends DisplayFormBase {

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return array
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['label']['#description'] = $this->t('The label for this mini panel.');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    drupal_set_message($this->t('The %label mini panel has been added.', ['%label' => $this->entity->label()]));
    $form_state->setRedirect('entity.mini_panel.edit_form', [
      'mini_panel' => $this->entity->id(),
    ]);
  }

}
