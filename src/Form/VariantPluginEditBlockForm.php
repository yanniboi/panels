<?php

/**
 * @file
 * Contains \Drupal\panels\Form\VariantPluginEditBlockForm.
 */

namespace Drupal\panels\Form;

/**
 * Provides a form for editing a block plugin of a variant.
 */
class VariantPluginEditBlockForm extends VariantPluginConfigureBlockFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_display_variant_edit_block_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareBlock($block_id) {
    return $this->getVariantPlugin()->getBlock($block_id);
  }

  /**
   * {@inheritdoc}
   */
  protected function submitText() {
    return $this->t('Update block');
  }

}
