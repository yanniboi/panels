<?php

/**
 * @file
 * Contains \Drupal\panels\Entity\DisplayVariantViewBuilder.
 */

namespace Drupal\panels\Entity;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Display\ContextAwareVariantInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Provides a view builder for display variant entities.
 */
class DisplayVariantViewBuilder implements EntityViewBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    /** @var \Drupal\panels\Entity\DisplayVariantInterface $entity */
    $variant_plugin = $entity->getVariantPlugin();
    if ($variant_plugin instanceof ContextAwareVariantInterface) {
      $variant_plugin->setContexts($entity->getContexts());
    }
    if ($variant_plugin instanceof RefinableCacheableDependencyInterface) {
      $variant_plugin->addCacheableDependency($entity);
    }
    return $variant_plugin->build();
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = 'full', $langcode = NULL) {
    $build = [];
    foreach ($entities as $key => $entity) {
      $build[$key] = $this->view($entity, $view_mode, $langcode);
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $entities = NULL) {
    // Intentionally empty.
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Intentionally empty.
  }

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    throw new \LogicException();
  }

  /**
   * {@inheritdoc}
   */
  public function viewField(FieldItemListInterface $items, $display_options = array()) {
    throw new \LogicException();
  }

  /**
   * {@inheritdoc}
   */
  public function viewFieldItem(FieldItemInterface $item, $display_options = array()) {
    throw new \LogicException();
  }

}
