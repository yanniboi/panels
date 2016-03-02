<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Routing\RouteSubscriber.
 */

namespace Drupal\panels_mini\Routing;

use Drupal\panels\Routing\DisplayRouteSubscriberBase;

class RouteSubscriber extends DisplayRouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId() {
    return 'mini_panel';
  }

  /**
   * {@inheritdoc}
   */
  protected function getBasePath() {
    return '/admin/structure/block/mini-panels';
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionTitle() {
    return 'Mini panels library';
  }

  /**
   * {@inheritdoc}
   */
  protected function getAddTitle() {
    return 'Add mini panel';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDeleteTitle() {
    return 'Delete mini panel';
  }

}
