<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Controller\MiniPanelController.
 */

namespace Drupal\panels_mini\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Plugin\Context\ContextHandlerInterface;
use Drupal\Core\Url;
use Drupal\ctools\Form\AjaxFormTrait;
use Drupal\ctools\Entity\DisplayInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\ctools\Entity\DisplayVariantInterface;

/**
 * Provides route controllers for Page Manager.
 */
class MiniPanelController extends ControllerBase {

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
   * @param \Drupal\ctools\Entity\DisplayInterface $mini_panel
   *   The page entity.
   *
   * @return string
   *   The title for the page edit form.
   */
  public function editMiniPanelTitle(DisplayInterface $mini_panel) {
    return $this->t('Edit %label mini panel', ['%label' => $mini_panel->label()]);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\ctools\Entity\DisplayVariantInterface $display_variant
   *   The page variant entity.
   *
   * @return string
   *   The title for the page variant edit form.
   */
  public function editMiniPanelVariantTitle(DisplayVariantInterface $display_variant) {
    return $this->t('Edit %label variant', ['%label' => $display_variant->label()]);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\ctools\Entity\DisplayInterface $mini_panel
   *   The mini_panel entity.
   * @param string $condition_id
   *   The access condition ID.
   *
   * @return string
   *   The title for the access condition edit form.
   */
  public function editAccessConditionTitle(DisplayInterface $mini_panel, $condition_id) {
    $access_condition = $mini_panel->getAccessCondition($condition_id);
    return $this->t('Edit %label access condition', ['%label' => $access_condition->getPluginDefinition()['label']]);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\ctools\Entity\DisplayVariantInterface $display_variant
   *   The page variant entity.
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
   * @param \Drupal\ctools\Entity\DisplayVariantInterface $display_variant
   *   The page variant entity.
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
   * Route title callback.
   *
   * @param \Drupal\ctools\Entity\DisplayInterface $mini_panel
   *   The page entity.
   * @param string $name
   *   The parameter context name.
   *
   * @return string
   *   The title for the parameter edit form.
   */
  public function editParameterTitle(DisplayInterface $mini_panel, $name) {
    return $this->t('Edit @label parameter', ['@label' => $mini_panel->getParameter($name)['label']]);
  }

  /**
   * Enables or disables a Mini panel.
   *
   * @param \Drupal\ctools\Entity\DisplayInterface $mini_panel
   *   The mini_panel entity.
   * @param string $op
   *   The operation to perform, usually 'enable' or 'disable'.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect back to the mini panels list page.
   */
  public function performMiniPanelOperation(DisplayInterface $mini_panel, $op) {
    $mini_panel->$op()->save();

    if ($op == 'enable') {
      drupal_set_message($this->t('The %label mini panel has been enabled.', ['%label' => $mini_panel->label()]));
    }
    elseif ($op == 'disable') {
      drupal_set_message($this->t('The %label mini panel has been disabled.', ['%label' => $mini_panel->label()]));
    }

    return $this->redirect('entity.mini_panel.collection');
  }

  /**
   * Presents a list of variants to add to the mini_panel entity.
   *
   * @param \Drupal\ctools\Entity\DisplayInterface $mini_panel
   *   The mini_panel entity.
   *
   * @return array
   *   The variant selection page.
   */
  public function selectVariant(DisplayInterface $mini_panel) {
    $build = [
      '#theme' => 'links',
      '#links' => [],
    ];
    foreach ($this->variantManager->getDefinitions() as $variant_plugin_id => $variant_plugin) {
      // The following two variants are provided by Drupal Core. They are not
      // configurable and therefore not compatible with Page Manager but have
      // similar and confusing labels. Skip them so that they are not shown in
      // the UI.
      if (in_array($variant_plugin_id, ['simple_page', 'block_page'])) {
        continue;
      }

      $build['#links'][$variant_plugin_id] = [
        'title' => $variant_plugin['admin_label'],
        'url' => Url::fromRoute('entity.display_variant.add_form', [
          'entity_type' => $mini_panel->getEntityTypeId(),
          'entity' => $mini_panel->id(),
          'variant_plugin_id' => $variant_plugin_id,
        ]),
        'attributes' => $this->getAjaxAttributes(),
      ];
    }
    return $build;
  }

  /**
   * Presents a list of access conditions to add to the mini_panel entity.
   *
   * @param \Drupal\ctools\Entity\DisplayInterface $mini_panel
   *   The mini_panel entity.
   *
   * @return array
   *   The access condition selection page.
   */
  public function selectAccessCondition(DisplayInterface $mini_panel) {
    $build = [
      '#theme' => 'links',
      '#links' => [],
    ];
    $available_plugins = $this->conditionManager->getDefinitionsForContexts($mini_panel->getContexts());
    foreach ($available_plugins as $access_id => $access_condition) {
      $build['#links'][$access_id] = [
        'title' => $access_condition['label'],
        'url' => Url::fromRoute('entity.mini_panel.access_condition_add', [
          'mini_panel' => $mini_panel->id(),
          'condition_id' => $access_id,
        ]),
        'attributes' => $this->getAjaxAttributes(),
      ];
    }
    return $build;
  }

  /**
   * Presents a list of selection conditions to add to the page entity.
   *
   * @param \Drupal\ctools\Entity\DisplayVariantInterface $display_variant
   *   The page variant entity.
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
          //'entity' => $display_variant->get('entity'),
          'display_variant' => $display_variant->id(),
          'condition_id' => $selection_id,
        ]),
        'attributes' => $this->getAjaxAttributes(),
      ];
    }
    return $build;
  }
}