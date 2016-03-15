<?php
/**
 * @file
 * Contains LayoutPluginSelector.php
 */

namespace Drupal\panels\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LayoutPluginSelector extends FormBase {

  /**
   * @var \Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface
   */
  protected $manager;

  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.layout_plugin'));
  }

  public function __construct(LayoutPluginManagerInterface $manager) {
    $this->manager = $manager;
  }

  public function getFormId() {
    return 'panels_layout_selection_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $variant_plugin = $cached_values['plugin'];
    $options = [];
    foreach ($this->manager->getDefinitions() as $plugin_id => $definition) {
      $options[$plugin_id] = $definition['label'];
    }
    $form['layout'] = [
      '#title' => $this->t('Choose a layout'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $variant_plugin->getConfiguration()['layout'],
    ];
    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
    $variant_plugin = $cached_values['plugin'];
    $variant_plugin->setLayout($form_state->getValue('layout'));
  }

}
