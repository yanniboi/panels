<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Entity\MiniPanel.
 */

namespace Drupal\panels_mini\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\ctools\Entity\DisplayBase;

/**
 * Defines a Mini Panel entity class.
 *
 * @ConfigEntityType(
 *   id = "mini_panel",
 *   label = @Translation("MiniPanel"),
 *   handlers = {
 *     "list_builder" = "Drupal\panels_mini\Entity\MiniPanelsListBuilder",
 *     "access" = "Drupal\ctools\Entity\DisplayAccess",
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

  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Invalidate the block cache to update derivatives.
    \Drupal::service('plugin.manager.block')->clearCachedDefinitions();
  }

  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    // Invalidate the block cache to update derivatives.
    \Drupal::service('plugin.manager.block')->clearCachedDefinitions();
  }

}
