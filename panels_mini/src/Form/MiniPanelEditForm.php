<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Form\MiniPanelEditForm.
 */

namespace Drupal\panels_mini\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ctools\Form\AjaxFormTrait;
use Drupal\ctools\Form\DisplayEditFormBase;

/**
 * Provides a form for editing a mini panel entity.
 */
class MiniPanelEditForm extends DisplayEditFormBase {

  use AjaxFormTrait;

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    drupal_set_message($this->t('The %label mini panel has been updated.', ['%label' => $this->entity->label()]));
  }

}
