<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Entity\MiniPanelsListBuilder.
 */

namespace Drupal\panels_mini\Entity;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a list builder for page entities.
 */
class MiniPanelsListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\panels\Entity\DisplayInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are currently no mini panels. <a href=":url">Add a new mini panel.</a>', [':url' => Url::fromRoute('entity.mini_panel.add_form')->toString()]);
    return $build;
  }

}
