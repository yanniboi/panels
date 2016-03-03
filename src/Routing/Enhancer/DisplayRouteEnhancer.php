<?php

/**
 * @file
 * Contains \Drupal\panels\Routing\Enhancer\DisplayRouteEnhancer
 */

namespace Drupal\panels\Routing\Enhancer;

use Drupal\Core\Routing\Enhancer\RouteEnhancerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

class DisplayRouteEnhancer implements RouteEnhancerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return $route->getDefault('_display_entity_type') !== NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function enhance(array $defaults, Request $request) {
    $entity_type = $defaults['_display_entity_type'];
    if (isset($defaults[$entity_type])) {
      $defaults['entity'] = $defaults[$entity_type];
    }
    return $defaults;
  }

}
