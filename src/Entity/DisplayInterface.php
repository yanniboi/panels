<?php

/**
 * @file
 * Contains \Drupal\panels\Entity\DisplayInterface.
 */

namespace Drupal\panels\Entity;

use Drupal\Component\Plugin\Context\ContextInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;

/**
 * Provides an interface for page entities.
 */
interface DisplayInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  /**
   * Returns whether the page entity is enabled.
   *
   * @return bool
   *   Whether the page entity is enabled or not.
   */
  public function status();

  /**
   * Adds a variant to this page.
   *
   * @param \Drupal\panels\Entity\DisplayVariantInterface $variant
   *   A page variant entity.
   *
   * @return $this
   */
  public function addVariant(DisplayVariantInterface $variant);

  /**
   * Retrieves a specific variant.
   *
   * @param string $variant_id
   *   The variant ID.
   *
   * @return \Drupal\panels\Entity\DisplayVariantInterface
   *   The variant object.
   */
  public function getVariant($variant_id);

  /**
   * Removes a specific variant.
   *
   * @param string $variant_id
   *   The variant ID.
   *
   * @return $this
   */
  public function removeVariant($variant_id);

  /**
   * Returns the variants available for the entity.
   *
   * @return \Drupal\panels\Entity\DisplayVariantInterface[]
   *   An array of the variants.
   */
  public function getVariants();

  /**
   * Returns the conditions used for determining access for this page entity.
   *
   * @return \Drupal\Core\Condition\ConditionInterface[]|\Drupal\Core\Condition\ConditionPluginCollection
   *   An array of configured condition plugins.
   */
  public function getAccessConditions();

  /**
   * Adds a new access condition to the page entity.
   *
   * @param array $configuration
   *   An array of configuration for the new access condition.
   *
   * @return string
   *   The access condition ID.
   */
  public function addAccessCondition(array $configuration);

  /**
   * Retrieves a specific access condition.
   *
   * @param string $condition_id
   *   The access condition ID.
   *
   * @return \Drupal\Core\Condition\ConditionInterface
   *   The access condition object.
   */
  public function getAccessCondition($condition_id);

  /**
   * Removes a specific access condition.
   *
   * @param string $condition_id
   *   The access condition ID.
   *
   * @return $this
   */
  public function removeAccessCondition($condition_id);

  /**
   * Returns the logic used to compute access, either 'and' or 'or'.
   *
   * @return string
   *   The string 'and', or the string 'or'.
   */
  public function getAccessLogic();

  /**
   * Returns the parameter context value objects for this page entity.
   *
   * @return array[]
   *   An array of parameter context arrays, keyed by parameter name.
   */
  public function getParameters();

  /**
   * Retrieves a specific parameter context.
   *
   * @param string $name
   *   The parameter context's unique name.
   *
   * @return array
   *   The parameter context array.
   */
  public function getParameter($name);

  /**
   * Adds/updates a given parameter context.
   *
   * @param string $name
   *   The parameter context name.
   * @param string $type
   *   The parameter context type.
   * @param string $label
   *   (optional) The parameter context label.
   *
   * @return $this
   */
  public function setParameter($name, $type, $label = '');

  /**
   * Removes a specific parameter context.
   *
   * @param string $name
   *   The parameter context's unique machine name.
   *
   * @return $this
   */
  public function removeParameter($name);

  /**
   * Gets the values for all defined contexts.
   *
   * @return \Drupal\Core\Plugin\Context\ContextInterface[]
   *   An array of set context values, keyed by context name.
   */
  public function getContexts();

  /**
   * Sets the values for all defined contexts.
   *
   * @param \Drupal\Component\Plugin\Context\ContextInterface[] $contexts
   *   An array of set context values, keyed by context name.
   */
  public function setContexts(array $contexts);

  /**
   * Sets the context for a given name.
   *
   * @param string $name
   *   The name of the context.
   * @param \Drupal\Component\Plugin\Context\ContextInterface $value
   *   The context to add.
   *
   * @return $this
   */
  public function addContext($name, ContextInterface $value);

}
