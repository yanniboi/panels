<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Form\MiniPanelEditForm.
 */

namespace Drupal\panels_mini\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ctools\Form\AjaxFormTrait;
use Drupal\panels\Form\DisplayEditForm;

/**
 * Provides a form for editing a mini panel entity.
 */
class MiniPanelEditForm extends DisplayEditForm {

  use AjaxFormTrait;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // @TODO remove once entity.{entity_type}.variant_select exists.
    $form['variant_section']['add_new_variant']['#url'] = Url::fromRoute('panels_mini.variant_select', [
      $this->entity->getEntityTypeId() => $this->entity->id(),
    ]);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    drupal_set_message($this->t('The %label mini panel has been updated.', ['%label' => $this->entity->label()]));
  }

}
