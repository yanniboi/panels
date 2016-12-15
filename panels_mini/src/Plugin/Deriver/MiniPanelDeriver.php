<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Plugin\Derivative\MiniPanelDeriver.
 */

namespace Drupal\panels_mini\Plugin\Deriver;

use Drupal\panels_mini\Entity\MiniPanel;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Plugin\Context\ContextDefinition;

/**
 * Provides entity view block definitions for each entity type.
 */
class MiniPanelDeriver extends DeriverBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach (MiniPanel::loadMultiple() as $id => $mini_panel) {
      /** @var \Drupal\panels\Entity\DisplayInterface $mini_panel */
      $this->derivatives[$id] = $base_plugin_definition;
      $this->derivatives[$id]['admin_label'] = $this->t('Mini Panel (@label)', ['@label' => $mini_panel->label()]);
      foreach ($mini_panel->getParameters() as $parameter) {
        $this->derivatives[$id]['context'][$parameter['machine_name']] = new ContextDefinition($parameter['type'], $parameter['label'], TRUE);
      }
    }
    return $this->derivatives;
  }

}
