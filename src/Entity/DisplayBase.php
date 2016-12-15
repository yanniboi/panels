<?php

/**
 * @file
 * Contains \Drupal\panels\Entity\DisplayBase.
 */

namespace Drupal\panels\Entity;

use Drupal\Component\Plugin\Context\ContextInterface;
use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\panels\Entity\DisplayVariantInterface;

/**
 * Defines a base class for Display config entities.
 */
abstract class DisplayBase extends ConfigEntityBase implements DisplayInterface {

  /**
   * The ID of the display entity.
   *
   * @var string
   */
  protected $id;

  /**
   * The label of the display entity.
   *
   * @var string
   */
  protected $label;

  /**
   * The display variant entities.
   *
   * @var \Drupal\panels\Entity\DisplayVariantInterface[].
   */
  protected $variants;

  /**
   * An array of collected contexts.
   *
   * @var \Drupal\Component\Plugin\Context\ContextInterface[]
   */
  protected $contexts = [];

  /**
   * The configuration of access conditions.
   *
   * @var array
   */
  protected $access_conditions = [];

  /**
   * Tracks the logic used to compute access, either 'and' or 'or'.
   *
   * @var string
   */
  protected $access_logic = 'and';

  /**
   * The plugin collection that holds the access conditions.
   *
   * @var \Drupal\Component\Plugin\LazyPluginCollection
   */
  protected $accessConditionCollection;

  /**
   * Parameter context configuration.
   *
   * An associative array keyed by parameter name, which contains associative
   * arrays with the following keys:
   * - machine_name: Machine-readable context name.
   * - label: Human-readable context name.
   * - type: Context type.
   *
   * @var array[]
   */
  protected $parameters = [];

  /**
   * Wraps the entity storage for display variants.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   */
  protected function variantStorage() {
    return \Drupal::service('entity_type.manager')->getStorage('display_variant');
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'access_conditions' => $this->getAccessConditions(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessConditions() {
    if (!$this->accessConditionCollection) {
      $this->accessConditionCollection = new ConditionPluginCollection(\Drupal::service('plugin.manager.condition'), $this->get('access_conditions'));
    }
    return $this->accessConditionCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function addAccessCondition(array $configuration) {
    $configuration['uuid'] = $this->uuidGenerator()->generate();
    $this->getAccessConditions()->addInstanceId($configuration['uuid'], $configuration);
    return $configuration['uuid'];
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessCondition($condition_id) {
    return $this->getAccessConditions()->get($condition_id);
  }

  /**
   * {@inheritdoc}
   */
  public function removeAccessCondition($condition_id) {
    $this->getAccessConditions()->removeInstanceId($condition_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessLogic() {
    return $this->access_logic;
  }

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

  /**
   * {@inheritdoc}
   */
  public function addVariant(DisplayVariantInterface $variant) {
    $this->variants[$variant->id()] = $variant;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariant($variant_id) {
    $variants = $this->getVariants();
    if (!isset($variants[$variant_id])) {
      throw new \UnexpectedValueException('The requested variant does not exist or is not associated with this display');
    }
    return $variants[$variant_id];
  }

  /**
   * {@inheritdoc}
   */
  public function removeVariant($variant_id) {
    $this->getVariant($variant_id)->delete();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariants() {
    if (!isset($this->variants)) {
      $this->variants = [];
      /** @var \Drupal\panels\Entity\DisplayVariantInterface $variant */
      foreach ($this->variantStorage()->loadByProperties(['display_entity_id' => $this->id()]) as $variant) {
        // Set the display entity on the loaded variants.
        $this->variants[$variant->id()] = $variant->setDisplayEntity($this);
      }
      // Suppress errors because of https://bugs.php.net/bug.php?id=50688.
      @uasort($this->variants, [$this, 'variantSortHelper']);
    }
    return $this->variants;
  }

  /**
   * {@inheritdoc}
   */
  public function variantSortHelper($a, $b) {
    $a_weight = $a->getWeight();
    $b_weight = $b->getWeight();
    if ($a_weight == $b_weight) {
      return 0;
    }

    return ($a_weight < $b_weight) ? -1 : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $vars = parent::__sleep();

    // Ensure any plugin collections are stored correctly before serializing.
    // @todo Let https://www.drupal.org/node/2650588 handle this instead.
    foreach ($this->getPluginCollections() as $plugin_config_key => $plugin_collection) {
      $this->set($plugin_config_key, $plugin_collection->getConfiguration());
    }

    // Avoid serializing plugin collections and entities as they might contain
    // references to a lot of objects including the container.
    $unset_vars = [
      'variants',
      'accessConditionCollection',
    ];
    foreach ($unset_vars as $unset_var) {
      if (!empty($this->{$unset_var})) {
        unset($vars[array_search($unset_var, $vars)]);
      }
    }

    return $vars;
  }

}
