<?php

/**
 * @file
 * Contains \Drupal\panels\Controller\DisplayVariantController.
 */

namespace Drupal\panels\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Url;
use Drupal\ctools\Form\AjaxFormTrait;
use Drupal\panels\Entity\DisplayVariantInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides route controllers for Display Variant operations.
 */
class DisplayVariantController extends ControllerBase {

  use AjaxFormTrait;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The condition manager.
   *
   * @var \Drupal\Core\Plugin\Context\ContextAwarePluginManagerInterface
   */
  protected $conditionManager;

  /**
   * The variant manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $variantManager;

  /**
   * The context handler.
   *
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface
   */
  protected $contextHandler;

  /**
   * Constructs a new VariantPluginEditForm.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $condition_manager
   *   The condition manager.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $variant_manager
   *   The variant manager.
   * @param \Drupal\Core\Plugin\Context\ContextHandlerInterface $context_handler
   *   The context handler.
   */
  public function __construct(BlockManagerInterface $block_manager, PluginManagerInterface $condition_manager, PluginManagerInterface $variant_manager, ContextHandlerInterface $context_handler) {
    $this->blockManager = $block_manager;
    $this->conditionManager = $condition_manager;
    $this->variantManager = $variant_manager;
    $this->contextHandler = $context_handler;
    $this->entityTypeManager = $this->entityTypeManager();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('plugin.manager.condition'),
      $container->get('plugin.manager.display_variant'),
      $container->get('context.handler')
    );
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\panels\Entity\DisplayVariantInterface $display_variant
   *   The display variant entity.
   *
   * @return string
   *   The title for the display variant edit form.
   */
  public function editDisplayVariantTitle(DisplayVariantInterface $display_variant) {
    return $this->t('Edit %label variant', ['%label' => $display_variant->label()]);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\panels\Entity\DisplayVariantInterface $display_variant
   *   The display variant entity.
   * @param string $condition_id
   *   The selection condition ID.
   *
   * @return string
   *   The title for the selection condition edit form.
   */
  public function editSelectionConditionTitle(DisplayVariantInterface $display_variant, $condition_id) {
    $selection_condition = $display_variant->getSelectionCondition($condition_id);
    return $this->t('Edit %label selection condition', ['%label' => $selection_condition->getPluginDefinition()['label']]);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\panels\Entity\DisplayVariantInterface $display_variant
   *   The display variant entity.
   * @param string $name
   *   The static context name.
   *
   * @return string
   *   The title for the static context edit form.
   */
  public function editStaticContextTitle(DisplayVariantInterface $display_variant, $name) {
    $static_context = $display_variant->getStaticContext($name);
    return $this->t('Edit @label static context', ['@label' => $static_context['label']]);
  }

  /**
   * Presents a list of selection conditions to add to the display entity.
   *
   * @param \Drupal\panels\Entity\DisplayVariantInterface $display_variant
   *   The display variant entity.
   *
   * @return array
   *   The selection condition selection page.
   */
  public function selectSelectionCondition(DisplayVariantInterface $display_variant) {
    $build = [
      '#theme' => 'links',
      '#links' => [],
    ];
    $available_plugins = $this->conditionManager->getDefinitionsForContexts($display_variant->getContexts());
    foreach ($available_plugins as $selection_id => $selection_condition) {
      $build['#links'][$selection_id] = [
        'title' => $selection_condition['label'],
        'url' => Url::fromRoute('entity.display_variant.selection_condition_add', [
          //'entity_type' => $display_variant->get('display_entity_type'),
          //'entity' => $display_variant->get('display_entity_id'),
          'display_variant' => $display_variant->id(),
          'condition_id' => $selection_id,
        ]),
        'attributes' => $this->getAjaxAttributes(),
      ];
    }
    return $build;
  }

  /**
   * Presents a list of blocks to add to the variant.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   * @param \Drupal\panels\Entity\DisplayVariantInterface $display_variant
   *   The display variant entity.
   *
   * @return array
   *   The block selection page.
   */
  public function selectBlock(Request $request, DisplayVariantInterface $display_variant) {
    // Add a section containing the available blocks to be added to the variant.
    $build = [
      '#type' => 'container',
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];
    $available_plugins = $this->blockManager->getDefinitionsForContexts($display_variant->getContexts());
    // Order by category, and then by admin label.
    $available_plugins = $this->blockManager->getSortedDefinitions($available_plugins);
    foreach ($available_plugins as $plugin_id => $plugin_definition) {
      // Make a section for each region.
      $category = $plugin_definition['category'];
      $category_key = 'category-' . $category;
      if (!isset($build[$category_key])) {
        $build[$category_key] = [
          '#type' => 'fieldgroup',
          '#title' => $category,
          'content' => [
            '#theme' => 'links',
          ],
        ];
      }
      // Add a link for each available block within each region.
      $build[$category_key]['content']['#links'][$plugin_id] = [
        'title' => $plugin_definition['admin_label'],
        'url' => Url::fromRoute('entity.display_variant.add_block', [
          'display_variant' => $display_variant->id(),
          'block_id' => $plugin_id,
          'region' => $request->query->get('region'),
        ]),
        'attributes' => $this->getAjaxAttributes(),
      ];
    }
    return $build;
  }

  /**
   * Build the mini_panel variant entity add form.
   *
   * @param string $entity_type
   *   The entity type this display variant belongs to.
   * @param string $entity
   *   The entity this display variant belongs to.
   * @param string $variant_plugin_id
   *   The variant plugin ID.
   *
   * @return array
   *   The mini_panel variant entity add form.
   */
  public function addDisplayVariantEntityForm($entity_type, $entity, $variant_plugin_id) {
    // Create a mini_panel variant entity.
    $variant_entity = $this->entityTypeManager()->getStorage('display_variant')->create([
      'display_entity_type' => $entity_type,
      'display_entity_id' => $entity,
      'variant' => $variant_plugin_id,
    ]);

    return $this->entityFormBuilder()->getForm($variant_entity, 'add');
  }

}
