<?php

/**
 * @file
 * Contains \Drupal\panels\Routing\DisplayRouteSubscriberBase.
 */

namespace Drupal\panels\Routing;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Field UI routes.
 */
abstract class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Get the entity type id of the display entity.
   *
   * @return string
   */
  abstract protected function getEntityTypeId();

  /**
   * Get the base path for managing this display entity.
   *
   * This will be used for the list page and other pages will exist underneath.
   *
   * @return string
   */
  abstract protected function getBasePath();

  /**
   * The entity type manager
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $manager;

  /**
   * Constructs a RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Display routes.
    $this->addDisplayRoutes($collection);
    $this->addParameterRoutes($collection);
    $this->addAccessRoutes($collection);

    // Variant routes.
    $this->addVariantRoutes($collection);
    $this->addStaticContextRoutes($collection);
    $this->addSelectionConditionRoutes($collection);
  }

  /**
   * Add routes for the display itself.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function addDisplayRoutes(RouteCollection $collection) {
    $entity_type_id = $this->getEntityTypeId();
    $definition = $this->manager->getDefinition($entity_type_id);
    $path = $this->getBasePath();

    $route = new Route(
      $path,
      [
        '_entity_list' => $entity_type_id,
        // @todo: '_title'
      ],
      ['_permission' => $definition->getAdminPermission()],
      []
    );
    $collection->add("entity.{$entity_type_id}.collection", $route);

    $route = new Route(
      "{$path}/add",
      [
        '_entity_form' => "{$entity_type_id}.add",
        // @todo: '_title'
      ],
      ['_entity_create_access' => $entity_type_id],
      []
    );
    $collection->add("entity.{$entity_type_id}.add_form", $route);

    $route = new Route(
      "{$path}/manage/{$entity_type_id}",
      [
        '_entity_form' => "{$entity_type_id}.edit",
        // @todo: '_title'
      ],
      ['_entity_access' => "{$entity_type_id}.update"],
      []
    );
    $collection->add("entity.{$entity_type_id}.edit_form", $route);

    $route = new Route(
      "{$path}/manage/{$entity_type_id}/delete",
      [
        '_entity_form' => "{$entity_type_id}.delete",
        // @todo: '_title'
      ],
      ['_entity_access' => "{$entity_type_id}.delete"],
      []
    );
    $collection->add("entity.{$entity_type_id}.delete_form", $route);

    // @todo: enable

    // @todo: disable
  }

  /**
   * Add routes for the access pages.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function addAccessRoutes(RouteCollection $collection) {
    $entity_type_id = $this->getEntityTypeId();

    // We'll use 'entity' in the path so controllers/forms can use $entity. To
    // do this, we need to set up the parameter upcasting.
    $path = $this->getBasePath() . '/manage/{entity}/access';
    $options = [
      'parameters' => [
        'entity' => [
          'type' => 'entity:' . $entity_type_id,
        ]
      ]
    ];

    // All our requirements are the same, so let's set them up once.
    $requirements = ['_entity_access' => "{$entity_type_id}.update"];

    // @todo: access_condition_select

    $route = new Route(
      "{$path}/add/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\AccessConditionAddForm',
        '_title' => 'Add new access condition',
      ],
      $requirements,
      $options
    );
    $collection->add("entity.{$entity_type_id}.access_condition_add", $route);

    $route = new Route(
      "{$path}/edit/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\AccessConditionEditForm',
        // @todo: '_title'
      ],
      $requirements,
      $options
    );
    $collection->add("entity.{$entity_type_id}.access_condition_edit", $route);

    $route = new Route(
      "{$path}/delete/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\AccessConditionDeleteForm',
        // @todo: '_title'
      ],
      $requirements,
      $options
    );
    $collection->add("entity.{$entity_type_id}.access_condition_delete", $route);
  }

  /**
   * Add routes for the parameter pages.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function addParameterRoutes(RouteCollection $collection) {
    $entity_type_id = $this->getEntityTypeId();

    // We'll use 'entity' in the path so controllers/forms can use $entity. To
    // do this, we need to set up the parameter upcasting.
    $path = $this->getBasePath() . '/manage/{entity}/parameter';
    $options = [
      'parameters' => [
        'entity' => [
          'type' => 'entity:' . $entity_type_id,
        ]
      ]
    ];

    // All our requirements are the same, so let's set them up once.
    $requirements = ['_entity_access' => "{$entity_type_id}.update"];

    // @todo: parameter_add

    // @todo: parameter_edit

    // @todo: parameter_delete
  }

  /**
   * Add routes for the variant pages.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function addVariantRoutes(RouteCollection $collection) {
    $entity_type_id = $this->getEntityTypeId();

    // We'll use 'entity' in the path so controllers/forms can use $entity. To
    // do this, we need to set up the parameter upcasting.
    $path = $this->getBasePath() . '/manage/{entity}';
    $options = [
      'parameters' => [
        'entity' => [
          'type' => 'entity:' . $entity_type_id,
        ]
      ]
    ];

    // @todo: *_variant_select

    // @todo: *_variant_add

    // The remaining requirements are the same, so let's set them up once.
    $requirements = ['_entity_access' => 'display_variant.update'];

    $route = new Route(
      "{$path}/variant/{display_variant}/edit",
      [
        '_entity_form' => 'display_variant.edit',
        // @todo: '_title'
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_edit_form", $route);

    $route = new Route(
      "{$path}/variant/{display_variant}/delete",
      [
        '_entity_form' => 'display_variant.delete',
        '_title' => 'Delete variant'
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_delete_form", $route);

    // Variant block routes.
    // @todo: *_variant_select_block

    $route = new Route(
      "{$path}/variant/{display_variant}/block/add/{block_id}",
      [
        '_form' => '\Drupal\panels\Form\VariantPluginAddBlockForm',
        '_title' => 'Add block to variant'
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_add_block", $route);

    $route = new Route(
      "{$path}/variant/{display_variant}/block/edit/{block_id}",
      [
        '_form' => '\Drupal\panels\Form\VariantPluginEditBlockForm',
        '_title' => 'Edit block in variant'
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_edit_block", $route);

    $route = new Route(
      "{$path}/variant/{display_variant}/block/delete/{block_id}",
      [
        '_form' => '\Drupal\panels\Form\VariantPluginDeleteBlockForm',
        '_title' => 'Delete block in variant'
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_delete_block", $route);
  }

  /**
   * Add routes for the static context pages.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function addStaticContextRoutes(RouteCollection $collection) {
    $entity_type_id = $this->getEntityTypeId();

    // We'll use 'entity' in the path so controllers/forms can use $entity. To
    // do this, we need to set up the parameter upcasting.
    $path = $this->getBasePath() . '/manage/{entity}/variant/{display_variant}/context';
    $options = [
      'parameters' => [
        'entity' => [
          'type' => 'entity:' . $entity_type_id,
        ]
      ]
    ];

    // All our requirements are the same, so let's set them up once.
    $requirements = ['_entity_access' => 'display_variant.update'];

    $route = new Route(
      "{$path}/add",
      [
        '_form' => '\Drupal\panels\Form\StaticContextAddForm',
        '_title' => 'Add new static context',
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_static_context_add_form", $route);

    $route = new Route(
      "{$path}/edit",
      [
        '_form' => '\Drupal\panels\Form\StaticContextEditForm',
        // @todo: '_title'
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_static_context_edit_form", $route);

    $route = new Route(
      "{$path}/delete",
      [
        '_form' => '\Drupal\panels\Form\StaticContextDeleteForm',
        '_title' => 'Delete static context',
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_static_context_delete_form", $route);
  }

  /**
   * Add routes for the selection condition pages.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   */
  protected function addSelectionConditionRoutes(RouteCollection $collection) {
    $entity_type_id = $this->getEntityTypeId();

    // We'll use 'entity' in the path so controllers/forms can use $entity. To
    // do this, we need to set up the parameter upcasting.
    $path = $this->getBasePath() . '/manage/{entity}/variant/{display_variant}/selection';
    $options = [
      'parameters' => [
        'entity' => [
          'type' => 'entity:' . $entity_type_id,
        ]
      ]
    ];

    // All our requirements are the same, so let's set them up once.
    $requirements = ['_entity_access' => 'display_variant.update'];

    // @todo: *_selection_condition_select

    $route = new Route(
      "{$path}/add/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\SelectionConditionAddForm',
        '_title' => 'Add new selection condition',
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_selection_condition_add_form", $route);

    $route = new Route(
      "{$path}/edit/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\SelectionConditionEditForm',
        // @todo: '_title'
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_selection_condition_editform", $route);

    $route = new Route(
      "{$path}/delete/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\SelectionConditionDeleteForm',
        '_title' => 'Delete selection condition',
      ],
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_selection_condition_delete_form", $route);
  }

}
