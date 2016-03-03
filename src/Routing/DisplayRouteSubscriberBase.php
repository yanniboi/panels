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
abstract class DisplayRouteSubscriberBase extends RouteSubscriberBase {

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
   * Get the title for the display collection route.
   *
   * @return string
   */
  abstract protected function getCollectionTitle();

  /**
   * Get the title for the add display route.
   *
   * @return string
   */
  abstract protected function getAddTitle();

  /**
   * Get the title for the delete display route.
   *
   * @return string
   */
  abstract protected function getDeleteTitle();

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
    $path = $this->getBasePath();
    $entity_type_id = $this->getEntityTypeId();

    // Display routes.
    $this->addDisplayRoutes($collection, $path);
    $this->addParameterRoutes($collection, $path . "/manage/{{$entity_type_id}}/parameter");
    $this->addAccessRoutes($collection, $path . "/manage/{{$entity_type_id}}/access");

    // Variant routes.
    $this->addVariantRoutes($collection, $path . "/manage/{{$entity_type_id}}");
    $this->addStaticContextRoutes($collection, $path . "/manage/{{$entity_type_id}}/variant/{display_variant}/context");
    $this->addSelectionConditionRoutes($collection, $path . "/manage/{{$entity_type_id}}/variant/{display_variant}/selection");
  }

  /**
   * Get the defaults array to merge in for route enhancer support.
   *
   * To allow abstracted controllers etc, we use a route enhancer to copy the
   * entity parameter into the 'entity' default.
   *
   * @return array
   *   The defaults array suitable for adding/merging.
   *
   * @see \Drupal\panels\Routing\Enhancer\DisplayRouteEnhancer
   */
  protected function getDefaults() {
    return ['_display_entity_type' => $this->getEntityTypeId()];
  }

  /**
   * Get the parameter array to merge in for display upcasting support.
   *
   * We can't always depend on the automatic entity upcasting as the controller
   * is abstracted for any display entity, so we have to add it manually.
   *
   * @return array
   *   The options.parameters array suitable for adding/merging.
   */
  protected function getParameters() {
    $entity_type_id = $this->getEntityTypeId();
    return [$entity_type_id => ['type' => 'entity:' . $entity_type_id]];
  }

  /**
   * Add routes for the display itself.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   * @param string $path
   *   The base path to use for the display routes.
   */
  protected function addDisplayRoutes(RouteCollection $collection, $path) {
    $entity_type_id = $this->getEntityTypeId();
    $definition = $this->manager->getDefinition($entity_type_id);
    $defaults = $this->getDefaults();

    $route = new Route(
      $path,
      [
        '_entity_list' => $entity_type_id,
        '_title' => $this->getCollectionTitle(),
      ],
      ['_permission' => $definition->getAdminPermission()]
    );
    $collection->add("entity.{$entity_type_id}.collection", $route);

    $route = new Route(
      "{$path}/add",
      [
        '_entity_form' => "{$entity_type_id}.add",
        '_title' => $this->getAddTitle(),
      ],
      ['_entity_create_access' => $entity_type_id]
    );
    $collection->add("entity.{$entity_type_id}.add_form", $route);

    $route = new Route(
      "{$path}/manage/{{$entity_type_id}}",
      [
        '_entity_form' => "{$entity_type_id}.edit",
        '_title_callback' => '\Drupal\panels\Controller\DisplayController::editDisplayTitle',
      ] + $defaults,
      ['_entity_access' => "{$entity_type_id}.update"]
    );
    $collection->add("entity.{$entity_type_id}.edit_form", $route);

    $route = new Route(
      "{$path}/manage/{{$entity_type_id}}/delete",
      [
        '_entity_form' => "{$entity_type_id}.delete",
        '_title' => $this->getDeleteTitle(),
      ] + $defaults,
      ['_entity_access' => "{$entity_type_id}.delete"]
    );
    $collection->add("entity.{$entity_type_id}.delete_form", $route);

    $route = new Route(
      "{$path}/manage/{{$entity_type_id}}/enable",
      [
        '_controller' => '\Drupal\panels\Controller\DisplayController::performDisplayOperation',
        'op' => 'enable',
      ] + $defaults,
      ['_entity_access' => "{$entity_type_id}.update"]
    );
    $collection->add("entity.{$entity_type_id}.enable", $route);

    $route = new Route(
      "{$path}/manage/{{$entity_type_id}}/disable",
      [
        '_controller' => '\Drupal\panels\Controller\DisplayController::performDisplayOperation',
        'op' => 'disable',
      ] + $defaults,
      ['_entity_access' => "{$entity_type_id}.update"]
    );
    $collection->add("entity.{$entity_type_id}.disable", $route);
  }

  /**
   * Add routes for the access pages.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   * @param string $path
   *   The base path to use for the display access routes.
   */
  protected function addAccessRoutes(RouteCollection $collection, $path) {
    $entity_type_id = $this->getEntityTypeId();
    $defaults = $this->getDefaults();
    $options = ['parameters' => $this->getParameters()];

    // All our requirements are the same, so let's set them up once.
    $requirements = ['_entity_access' => "{$entity_type_id}.update"];

    $route = new Route(
      "{$path}/select",
      [
        '_controller' => '\Drupal\panels\Controller\DisplayController::selectAccessCondition',
        '_title' => 'Select access condition',
      ] + $defaults,
      $requirements,
      $options
    );
    $collection->add("entity.{$entity_type_id}.access_condition_select", $route);

    $route = new Route(
      "{$path}/add/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\AccessConditionAddForm',
        '_title' => 'Add new access condition',
      ] + $defaults,
      $requirements,
      $options
    );
    $collection->add("entity.{$entity_type_id}.access_condition_add", $route);

    $route = new Route(
      "{$path}/edit/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\AccessConditionEditForm',
        '_title_callback' => '\Drupal\panels\Controller\DisplayController::editAccessConditionTitle',
      ] + $defaults,
      $requirements,
      $options
    );
    $collection->add("entity.{$entity_type_id}.access_condition_edit", $route);

    $route = new Route(
      "{$path}/delete/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\AccessConditionDeleteForm',
        '_title' => 'Delete access condition',
      ] + $defaults,
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
   * @param string $path
   *   The base path to use for the display parameter routes.
   */
  protected function addParameterRoutes(RouteCollection $collection, $path) {
    // @todo: parameter_add

    // @todo: parameter_edit

    // @todo: parameter_delete
  }

  /**
   * Add routes for the variant pages.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   * @param string $path
   *   The base path to use for the display variant routes.
   */
  protected function addVariantRoutes(RouteCollection $collection, $path) {
    $entity_type_id = $this->getEntityTypeId();
    $defaults = $this->getDefaults();
    $options = ['parameters' =>$this->getParameters()];

    $route = new Route(
      "{$path}/add",
      [
        '_controller' => '\Drupal\panels\Controller\DisplayController::selectVariant',
        '_title' => 'Select variant',
      ] + $defaults,
      ['_entity_access' => "{$entity_type_id}.update"],
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_select", $route);

    $route = new Route(
      "{$path}/add/{variant_plugin_id}",
      [
        '_controller' => '\Drupal\panels\Controller\DisplayController::addDisplayVariantEntityForm',
        '_title' => 'Add variant',
      ] + $defaults,
      ['_entity_create_access' => "display_variant"],
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_add_form", $route);

    // The remaining requirements are the same, so let's set them up once.
    $requirements = ['_entity_access' => 'display_variant.update'];

    $route = new Route(
      "{$path}/variant/{display_variant}/edit",
      [
        '_entity_form' => 'display_variant.edit',
        '_title_callback' => '\Drupal\panels\Controller\DisplayController::editDisplayVariantTitle',
      ] + $defaults,
      $requirements,
      $options
    );
    $collection->add("entity.display_variant.{$entity_type_id}_edit_form", $route);

    $route = new Route(
      "{$path}/variant/{display_variant}/delete",
      [
        '_entity_form' => 'display_variant.delete',
        '_title' => 'Delete variant'
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_delete_form", $route);

    // Variant block routes.
    $route = new Route(
      "{$path}/variant/{display_variant}/block/select",
      [
        '_controller' => '\Drupal\panels\Controller\DisplayController::selectBlock',
        '_title' => 'Seelct block',
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_select_block", $route);

    $route = new Route(
      "{$path}/variant/{display_variant}/block/add/{block_id}",
      [
        '_form' => '\Drupal\panels\Form\VariantPluginAddBlockForm',
        '_title' => 'Add block to variant'
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_add_block", $route);

    $route = new Route(
      "{$path}/variant/{display_variant}/block/edit/{block_id}",
      [
        '_form' => '\Drupal\panels\Form\VariantPluginEditBlockForm',
        '_title' => 'Edit block in variant'
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_edit_block", $route);

    $route = new Route(
      "{$path}/variant/{display_variant}/block/delete/{block_id}",
      [
        '_form' => '\Drupal\panels\Form\VariantPluginDeleteBlockForm',
        '_title' => 'Delete block in variant'
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_delete_block", $route);
  }

  /**
   * Add routes for the static context pages.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   * @param string $path
   *   The base path to use for the variant static context routes.
   */
  protected function addStaticContextRoutes(RouteCollection $collection, $path) {
    $entity_type_id = $this->getEntityTypeId();
    $defaults = $this->getDefaults();

    // All our requirements are the same, so let's set them up once.
    $requirements = ['_entity_access' => 'display_variant.update'];

    $route = new Route(
      "{$path}/add",
      [
        '_form' => '\Drupal\panels\Form\StaticContextAddForm',
        '_title' => 'Add new static context',
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_static_context_add_form", $route);

    $route = new Route(
      "{$path}/edit/{name}",
      [
        '_form' => '\Drupal\panels\Form\StaticContextEditForm',
        '_title_callback' => '\Drupal\panels\Controller\DisplayController::editStaticContextTitle',
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_static_context_edit_form", $route);

    $route = new Route(
      "{$path}/delete/{name}",
      [
        '_form' => '\Drupal\panels\Form\StaticContextDeleteForm',
        '_title' => 'Delete static context',
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_static_context_delete_form", $route);
  }

  /**
   * Add routes for the selection condition pages.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection for adding routes.
   * @param string $path
   *   The base path to use for the variant selection condition routes.
   */
  protected function addSelectionConditionRoutes(RouteCollection $collection, $path) {
    $entity_type_id = $this->getEntityTypeId();
    $defaults = $this->getDefaults();

    // All our requirements are the same, so let's set them up once.
    $requirements = ['_entity_access' => 'display_variant.update'];

    $route = new Route(
      "{$path}/select",
      [
        '_controller' => '\Drupal\panels\Controller\DisplayController::selectSelectionCondition',
        '_title' => 'Select selection condition',
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_selection_condition_select", $route);

    $route = new Route(
      "{$path}/add/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\SelectionConditionAddForm',
        '_title' => 'Add new selection condition',
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_selection_condition_add_form", $route);

    $route = new Route(
      "{$path}/edit/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\SelectionConditionEditForm',
        '_title_callback' => '\Drupal\panels\Controller\DisplayController::editSelectionConditionTitle',
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_selection_condition_edit_form", $route);

    $route = new Route(
      "{$path}/delete/{condition_id}",
      [
        '_form' => '\Drupal\panels\Form\SelectionConditionDeleteForm',
        '_title' => 'Delete selection condition',
      ] + $defaults,
      $requirements
    );
    $collection->add("entity.display_variant.{$entity_type_id}_selection_condition_delete_form", $route);
  }

}
