<?php
/**
 * @file
 * Contains \Drupal\panels\Form\LayoutPluginSelector.
 */

namespace Drupal\panels\Form;


use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_plugin\Layout;
use Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LayoutPluginSelector extends FormBase {

  /**
   * @var \Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface
   */
  protected $manager;

  /**
   * @var \Drupal\user\SharedTempStoreFactory
   */
  protected $tempstore;

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.layout_plugin'),
      $container->get('user.shared_tempstore')
    );
  }

  public function __construct(LayoutPluginManagerInterface $manager, SharedTempStoreFactory $tempstore) {
    $this->manager = $manager;
    $this->tempstore = $tempstore;
  }

  public function getFormId() {
    return 'panels_layout_selection_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    /* @var $variant_plugin \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant */
    $variant_plugin = $cached_values['plugin'];
    $options = [];
    foreach ($this->manager->getDefinitions() as $plugin_id => $definition) {
      $options[$plugin_id] = $definition['label'];
    }
    $form['layout'] = [
      '#title' => $this->t('Layout'),
      '#type' => 'select',
      '#options' => Layout::getLayoutOptions(['group_by_category' => TRUE]),
      '#default_value' => $variant_plugin->getConfiguration()['layout'] ?: NULL,
    ];

    if (!empty($variant_plugin->getConfiguration()['layout'])) {
      $form['layout']['#ajax'] = [
        'callback' => '::layoutSettingsAjaxCallback',
        'wrapper' => 'layout-settings-wrapper',
        'effect' => 'fade',
      ];

      // If a layout is already selected, show the layout settings.
      $form['layout_settings_wrapper'] = [
        '#type' => 'fieldset',
        '#title' => $this->t('Layout settings'),
        '#prefix' => '<div id="layout-settings-wrapper">',
        '#suffix' => '</div>',
      ];
      $form['layout_settings_wrapper']['layout_settings'] = [];
    }

    return $form;
  }

  /**
   * Render API callback: gets the layout settings elements.
   */
  public function layoutSettingsAjaxCallback(array $form, FormStateInterface $form_state) {
    $variant_array_parents = $form['#variant_array_parents'];
    return NestedArray::getValue($form, array_merge($variant_array_parents, ['layout_settings_wrapper']));
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    /* @var $variant_plugin \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant */
    $variant_plugin = $cached_values['plugin'];
    $variant_plugin->setLayout($form_state->getValue('layout'), $form_state->getValue('layout_settings'));
    $cached_values['plugin'] = $variant_plugin;

    $page_tempstore = $this->tempstore->get('page_manager.page')->get($cached_values['page']->id());
    $page_tempstore['page_variant'] = $cached_values['page_variant'];
    $this->tempstore->get('page_manager.page')->set($cached_values['page']->id(), $page_tempstore);
  }

}
