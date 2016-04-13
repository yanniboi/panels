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

  /**
   * {@inheritdoc}
   */
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

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'panels_layout_selection_form';
  }

  /**
   * {@inheritdoc}
   */
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
      $form['update_layout'] = [
        '#type' => 'submit',
        '#value' => 'Change Layout',
        '#validate' => [
          [$this, 'validateForm'],
        ],
        '#submit' => [
          [$this, 'submitForm'],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    if ((string)$form_state->getValue('op') == $this->t('Change Layout')) {
      $variant_plugin = $cached_values['plugin'];
      $cached_values['layout_change'] = [];
      $cached_values['layout_change']['plugin'] = $variant_plugin;
      $cached_values['layout_change']['old_layout'] = $variant_plugin->getConfiguration()['layout'];
      $cached_values['layout_change']['new_layout'] = $form_state->getValue('layout');
      $cached_values['layout_change']['tempstore_id'] = $form_state->get('wizard')->getTempstoreId();
      $cached_values['layout_change']['destination'] = [
        $form_state->get('wizard')->getRouteName(),
        ['step' => 'content'] + $form_state->get('wizard')->getNextParameters($cached_values),
      ];
      //$form_state->setTemporaryValue('wizard', $cached_values);
      $form_state->get('wizard')->getTempstore()->set($form_state->get('wizard')->getMachineName(), $cached_values);

      $form_state->setRedirect('panels.layout.change_form', [
        'tempstore_id' => $form_state->get('wizard')->getTempstoreId(),
        'machine_name' => $form_state->get('wizard')->getMachineName()
      ]);
    }
    else {
      /* @var $variant_plugin \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant */
      $variant_plugin = $cached_values['plugin'];
      $variant_plugin->setLayout($form_state->getValue('layout'), $form_state->getValue('layout_settings'));
      $cached_values['plugin'] = $variant_plugin;

      $page_tempstore = $this->tempstore->get('page_manager.page')->get($cached_values['page']->id());
      $page_tempstore['page_variant'] = $cached_values['page_variant'];
      $this->tempstore->get('page_manager.page')->set($cached_values['page']->id(), $page_tempstore);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    /* @var $variant_plugin \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant */
    $variant_plugin = $cached_values['plugin'];

    if ($variant_plugin->getConfiguration()['layout'] == $form_state->getValue('layout')) {
      $form_state->setErrorByName('layout', $this->t('You must select a different layout if you wish to change layouts.'));
    }
  }

}
