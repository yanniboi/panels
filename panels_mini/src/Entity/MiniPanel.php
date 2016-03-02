<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Entity\MiniPanel.
 */

namespace Drupal\panels_mini\Entity;

use Drupal\ctools\Entity\DisplayBase;
use Drupal\Component\Plugin\Context\ContextInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\Context;

/**
 * Defines a Mini Panel entity class.
 *
 * @ConfigEntityType(
 *   id = "mini_panel",
 *   label = @Translation("MiniPanel"),
 *   handlers = {
 *     "list_builder" = "Drupal\panels_mini\Entity\MiniPanelsListBuilder",
 *     "access" = "Drupal\panels_mini\Entity\MiniPanelAccess",
 *     "form" = {
 *       "add" = "Drupal\panels_mini\Form\MiniPanelAddForm",
 *       "edit" = "Drupal\panels_mini\Form\MiniPanelEditForm",
 *       "delete" = "Drupal\panels_mini\Form\MiniPanelDeleteForm",
 *     }
 *   },
 *   admin_permission = "administer blocks",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/block/mini-panels/manage/{mini_panel}/delete",
 *     "edit-form" = "/admin/structure/block/mini-panels/manage/{mini_panel}"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "access_logic",
 *     "access_conditions",
 *     "parameters",
 *   },
 * )
 */
class MiniPanel extends DisplayBase {

  /**
   * {@inheritdoc}
   */
  public function getParameters() {
    return $this->parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getParameter($name) {
    if (!isset($this->parameters[$name])) {
      $this->setParameter($name, '');
    }
    return $this->parameters[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function setParameter($name, $type, $label = '') {
    $this->parameters[$name] = [
      'machine_name' => $name,
      'type' => $type,
      'label' => $label,
    ];
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeParameter($name) {
    unset($this->parameters[$name]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addContext($name, ContextInterface $value) {
    $this->contexts[$name] = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    return $this->contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function setContexts(array $contexts = []) {
    $this->contexts = $contexts;
  }
}
