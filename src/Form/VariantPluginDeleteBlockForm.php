<?php

/**
 * @file
 * Contains \Drupal\panels\Form\VariantPluginDeleteBlockForm.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\Entity\DisplayVariantInterface;

/**
 * Provides a form for deleting an access condition.
 */
class VariantPluginDeleteBlockForm extends ConfirmFormBase {

  /**
   * The display variant.
   *
   * @var \Drupal\panels\Entity\DisplayVariantInterface
   */
  protected $displayVariant;

  /**
   * The plugin being configured.
   *
   * @var \Drupal\Core\Block\BlockPluginInterface
   */
  protected $block;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_display_variant_delete_block_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the block %label?', ['%label' => $this->block->label()]);
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
  public function buildForm(array $form, FormStateInterface $form_state, DisplayVariantInterface $display_variant = NULL, $block_id = NULL) {
    $this->displayVariant = $display_variant;
    $this->block = $this->getVariantPlugin()->getBlock($block_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->getVariantPlugin()->removeBlock($this->block->getConfiguration()['uuid']);
    $this->displayVariant->save();
    drupal_set_message($this->t('The block %label has been removed.', ['%label' => $this->block->label()]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

  /**
   * Gets the variant plugin for this display variant entity.
   *
   * @return \Drupal\ctools\Plugin\BlockVariantInterface
   */
  protected function getVariantPlugin() {
    return $this->displayVariant->getVariantPlugin();
  }

}
