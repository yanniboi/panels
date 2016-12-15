<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Form\AccessConditionFormBase.
 */

namespace Drupal\panels_mini\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\Entity\DisplayInterface;
use Drupal\panels\Form\ConditionFormBase;

/**
 * Provides a base form for editing and adding an access condition.
 */
abstract class AccessConditionFormBase extends ConditionFormBase {

  /**
   * The mini_panel entity this condition belongs to.
   *
   * @var \Drupal\panels\Entity\DisplayInterface
   */
  protected $mini_panel;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, DisplayInterface $mini_panel = NULL, $condition_id = NULL) {
    $this->mini_panel = $mini_panel;
    return parent::buildForm($form, $form_state, $condition_id, $mini_panel->getContexts());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $configuration = $this->condition->getConfiguration();
    // If this access condition is new, add it to the mini_panel.
    if (!isset($configuration['uuid'])) {
      $this->mini_panel->addAccessCondition($configuration);
    }

    // Save the mini_panel entity.
    $this->mini_panel->save();

    $form_state->setRedirectUrl($this->mini_panel->toUrl('edit-form'));
  }

}
