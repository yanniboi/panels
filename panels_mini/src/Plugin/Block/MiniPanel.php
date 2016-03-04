<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Plugin\Block\EntityView.
 */

namespace Drupal\panels_mini\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\panels\Entity\DisplayVariantInterface;

/**
 * Provides a block to view a specific entity.
 *
 * @Block(
 *   id = "mini_panel",
 *   deriver = "Drupal\panels_mini\Plugin\Deriver\MiniPanelDeriver",
 * )
 */
class MiniPanel extends BlockBase implements ContextAwarePluginInterface, ContainerFactoryPluginInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a new EntityView.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'view_mode' => 'default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->entityManager->getViewModeOptions($this->getDerivativeId()),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->configuration['view_mode'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $mini_panel = $this->entityManager->getStorage('mini_panel')->load($this->getDerivativeId());
    return $mini_panel->access('view', $account, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var $mini_panel \Drupal\panels\Entity\DisplayInterface */
    $mini_panel = $this->entityManager->getStorage('mini_panel')->load($this->getDerivativeId());
    $mini_panel->setContexts($this->getContexts());

    $variants = $mini_panel->getVariants();
    $variants = $this->filterDisplayVariants($variants);
    $variant = reset($variants);

    $view_builder = $this->entityManager->getViewBuilder($variant->getEntityTypeId());
    $build = $view_builder->view($variant);

//    CacheableMetadata::createFromObject($this->getContext('mini_panel'))
//      ->applyTo($build);

    return $build;
  }

  /**
   * Filter the list of variants and return the first accessible variant.
   */
  public function filterDisplayVariants($variants) {
    // Only proceed if the array is non-empty.
    if (!count($variants)) {
      return $variants;
    }

    // First sort the variants by variant weight.
    uasort($variants, [$this, 'variantWeightSort']);

    // Find the first variant that is accessible.
    $accessible_variant_name = NULL;
    foreach ($variants as $name => $variant) {
      if ($this->checkDisplayVariantAccess($name)) {
        // Access granted, use this variant.
        $accessible_variant_name = $name;
        break;
      }
    }

    // Remove all other variants.
    foreach ($variants as $name => $variant) {
      if ($accessible_variant_name !== $name) {
        unset($variants[$name]);
      }
    }

    return $variants;
  }

  /**
   * Sort callback for routes based on the variant weight.
   */
  protected function variantWeightSort(DisplayVariantInterface $a, DisplayVariantInterface $b) {
    $a_weight = $a->getWeight();
    $b_weight = $b->getWeight();
    if ($a_weight === $b_weight) {
      return 0;
    }
    elseif ($a_weight === NULL) {
      return 1;
    }
    elseif ($b_weight === NULL) {
      return -1;
    }

    return ($a_weight < $b_weight) ? -1 : 1;
  }


  /**
   * Checks access of a display variant.
   *
   * @param string $display_variant_id
   *   The display variant ID.
   *
   * @return bool
   *   TRUE if the variant is accessible, FALSE otherwise.
   */
  protected function checkDisplayVariantAccess($display_variant_id) {
    /** @var \Drupal\panels\Entity\DisplayVariantInterface $variant */
    $variant = \Drupal::entityTypeManager()->getStorage('display_variant')->load($display_variant_id);

    try {
      $access = $variant && $variant->access('view');
    }
    // Since access checks can throw a context exception, consider that as
    // a disallowed variant.
    catch (ContextException $e) {
      $access = FALSE;
    }

    return $access;
  }

}
