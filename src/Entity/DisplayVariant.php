<?php

/**
 * @file
 * Contains Drupal\panels\Entity\DisplayVariant.
 */

namespace Drupal\panels\Entity;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Defines the display variant entity.
 *
 * @ConfigEntityType(
 *   id = "display_variant",
 *   label = @Translation("Display Variant"),
 *   handlers = {
 *     "view_builder" = "Drupal\panels\Entity\DisplayVariantViewBuilder",
 *     "access" = "Drupal\panels\Entity\DisplayVariantAccess",
 *     "form" = {
 *       "add" = "Drupal\panels\Form\DisplayVariantAddForm",
 *       "edit" = "Drupal\panels\Form\DisplayVariantEditForm",
 *       "delete" = "Drupal\panels\Form\DisplayVariantDeleteForm",
 *     }
 *   },
 *   admin_permission = "administer blocks",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "variant",
 *     "variant_settings",
 *     "display_entity_type",
 *     "display_entity_id",
 *     "weight",
 *     "selection_criteria",
 *     "selection_logic",
 *     "static_context",
 *   },
 *   lookup_keys = {
 *     "display_entity_id"
 *   }
 * )
 */
class DisplayVariant extends ConfigEntityBase implements DisplayVariantInterface {

  /**
   * The ID of the display variant entity.
   *
   * @var string
   */
  protected $id;

  /**
   * The label of the display variant entity.
   *
   * @var string
   */
  protected $label;

  /**
   * The weight of the display variant entity.
   *
   * @var int
   */
  protected $weight = 0;

  /**
   * The UUID of the display variant entity.
   *
   * @var string
   */
  protected $uuid;

  /**
   * The ID of the variant plugin.
   *
   * @var string
   */
  protected $variant;

  /**
   * The plugin configuration for the variant plugin.
   *
   * @var array
   */
  protected $variant_settings = [];

  /**
   * The entity type of the entity this display variant entity belongs to.
   *
   * @var string
   */
  protected $display_entity_type;

  /**
   * The ID of the entity this display variant entity belongs to.
   *
   * @var string
   */
  protected $display_entity_id;

  /**
   * The display entity this variant is for.
   *
   * @var \Drupal\panels\Entity\DisplayInterface
   */
  protected $displayEntity;

  /**
   * The plugin configuration for the selection criteria condition plugins.
   *
   * @var array
   */
  protected $selection_criteria = [];

  /**
   * The selection logic for this display variant entity (either 'and' or 'or').
   *
   * @var string
   */
  protected $selection_logic = 'and';

  /**
   * An array of collected contexts.
   *
   * @var \Drupal\Component\Plugin\Context\ContextInterface[]|null
   */
  protected $contexts = NULL;

  /**
   * Static context references.
   *
   * A list of arrays with the keys name, label, type and value.
   *
   * @var array[]
   */
  protected $static_context = [];

  /**
   * The plugin collection that holds the single variant plugin instance.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $variantPluginCollection;

  /**
   * The plugin collection that holds the selection condition plugins.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $selectionConditionCollection;

  /**
   * {@inheritdoc}
   */
  protected function invalidateTagsOnSave($update) {
    parent::invalidateTagsOnSave($update);

    // The parent doesn't invalidate the entity cache tags on save because the
    // config system will invalidate them, but since we're using the parent
    // page's cache tags, we need to invalidate them special.
    Cache::invalidateTags($this->getCacheTagsToInvalidate());
  }

  /**
   * {@inheritdoc}
   */
  protected static function invalidateTagsOnDelete(EntityTypeInterface $entity_type, array $entities) {
    parent::invalidateTagsOnDelete($entity_type, $entities);

    // The parent doesn't invalidate the entity cache tags on delete because the
    // config system will invalidate them, but since we're using the parent
    // page's cache tags, we need to invalidate them special.
    $tags = [];
    foreach ($entities as $entity) {
      /* @var \Drupal\Core\Entity\EntityInterface $entity */
      $tags = Cache::mergeTags($tags, $entity->getCacheTagsToInvalidate());
    }
    Cache::invalidateTags($tags);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTagsToInvalidate() {
    // We use the same cache tags as the parent page.
    return $this->getDisplayEntity()->getCacheTagsToInvalidate();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    $this->addDependency('config', $this->getDisplayEntity()->getConfigDependencyName());

    foreach ($this->getSelectionConditions() as $instance) {
      $this->calculatePluginDependencies($instance);
    }

    return $this->getDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'selection_criteria' => $this->getSelectionConditions(),
      'variant_settings' => $this->getVariantPluginCollection(),
    ];
  }

  /**
   * Get the plugin collection that holds the single variant plugin instance.
   *
   * @return \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   *   The plugin collection that holds the single variant plugin instance.
   */
  protected function getVariantPluginCollection() {
    if (!$this->variantPluginCollection) {
      $this->variantPluginCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.display_variant'), $this->variant, $this->variant_settings);
    }
    return $this->variantPluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariantPlugin() {
    return $this->getVariantPluginCollection()->get($this->variant);
  }

  /**
   * {@inheritdoc}
   */
  public function getVariantPluginId() {
    return $this->variant;
  }

  /**
   * {@inheritdoc}
   */
  public function getDisplayEntity() {
    if (!isset($this->displayEntity)) {
      if (!$this->display_entity_type || !$this->display_entity_id) {
        throw new \UnexpectedValueException('The display variant has no associated entity');
      }
      $this->displayEntity = $this->getDisplayEntityStorage()->load($this->display_entity_id);
    }
    return $this->displayEntity;
  }

  /**
   * {@inheritdoc}
   */
  public function setDisplayEntity(DisplayInterface $display_entity) {
    $this->displayEntity = $display_entity;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {
    if (is_null($this->contexts)) {
      $static_contexts = $this->getContextMapper()->getContextValues($this->getStaticContexts());
      $display_contexts = $this->getDisplayEntity()->getContexts();
      $this->contexts = array_merge($static_contexts, $display_contexts);
    }
    return $this->contexts;
  }

  /**
   * Resets the collected contexts.
   *
   * @return $this
   */
  protected function resetCollectedContexts() {
    $this->contexts = NULL;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionLogic() {
    return $this->get('selection_logic');
  }

  /**
   * {@inheritdoc}
   */
  protected function getSelectionConfiguration() {
    return $this->get('selection_criteria');
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionConditions() {
    if (!$this->selectionConditionCollection) {
      $this->selectionConditionCollection = new ConditionPluginCollection($this->getConditionManager(), $this->getSelectionConfiguration());
    }
    return $this->selectionConditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addSelectionCondition(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getSelectionConditions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionCondition($condition_id) {
    return $this->getSelectionConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removeSelectionCondition($condition_id) {
    $this->getSelectionConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getStaticContexts() {
    return $this->static_context;
  }

  /**
   * {@inheritdoc}
   */
  public function getStaticContext($name) {
    if (isset($this->static_context[$name])) {
      return $this->static_context[$name];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function setStaticContext($name, $configuration) {
    $this->static_context[$name] = $configuration;
    $this->resetCollectedContexts();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeStaticContext($name) {
    unset($this->static_context[$name]);
    $this->resetCollectedContexts();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function linkTemplates() {
    $link_templates = parent::linkTemplates();
    $link_templates["{$this->display_entity_type}-edit-form"] = 'entity.display_variant.' . $this->display_entity_type . '_edit_form';
    $link_templates["{$this->display_entity_type}-delete-form"] = 'entity.display_variant.' . $this->display_entity_type . '_delete_form';
    return $link_templates;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $parameters = parent::urlRouteParameters($rel);
    $parameters[$this->get('display_entity_type')] = $this->get('display_entity_id');
    return $parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    static::routeBuilder()->setRebuildNeeded();
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    static::routeBuilder()->setRebuildNeeded();
  }

  /**
   * Wraps the route builder.
   *
   * @return \Drupal\Core\Routing\RouteBuilderInterface
   *   An object for state storage.
   */
  protected static function routeBuilder() {
    return \Drupal::service('router.builder');
  }

  /**
   * Wraps the condition plugin manager.
   *
   * @return \Drupal\Core\Condition\ConditionManager
   */
  protected function getConditionManager() {
    return \Drupal::service('plugin.manager.condition');
  }

  /**
   * Wraps the context mapper.
   *
   * @return \Drupal\page_manager\ContextMapperInterface
   */
  protected function getContextMapper() {
    return \Drupal::service('page_manager.context_mapper');
  }

  /**
   * Wraps the entity storage.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  protected function getDisplayEntityStorage() {
    if (!$this->display_entity_type) {
      throw new \UnexpectedValueException('The display variant has no associated entity');
    }
    return \Drupal::entityTypeManager()->getStorage($this->display_entity_type);
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $vars = parent::__sleep();
    // Gathered contexts objects should not be serialized.
    unset($vars[array_search('contexts', $vars)]);
    return $vars;
  }

}
