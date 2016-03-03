<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Form\AccessConditionFormBase.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\panels\Entity\DisplayInterface;

/**
 * Provides a base form for editing and adding an access condition.
 */
abstract class AccessConditionFormBase extends ConditionFormBase {

  /**
   * The display entity this condition belongs to.
   *
   * @var \Drupal\panels\Entity\DisplayInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, DisplayInterface $entity = NULL, $condition_id = NULL) {
    $this->entity = $entity;
    return parent::buildForm($form, $form_state, $condition_id, $entity->getContexts());
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $configuration = $this->condition->getConfiguration();
    // If this access condition is new, add it to the mini_panel.
    if (!isset($configuration['uuid'])) {
      $this->entity->addAccessCondition($configuration);
    }

    // Save the mini_panel entity.
    $this->entity->save();

    $form_state->setRedirectUrl($this->entity->toUrl('edit-form'));
  }

}
