<?php
/**
 * @file
 * Contains \Drupal\panels\Form\LayoutChangeSettings.
 */

namespace Drupal\panels\Form;


use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_plugin\Layout;
use Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LayoutChangeSettings extends FormBase {

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
    return 'panels_layout_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    /* @var $variant_plugin \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant */
    $variant_plugin = $cached_values['plugin'];

    $form['old_layout'] = [
      '#title' => $this->t('Old Layout'),
      '#type' => 'select',
      '#options' => Layout::getLayoutOptions(['group_by_category' => TRUE]),
      '#default_value' => $cached_values['layout_change']['old_layout'],
      '#disabled' => TRUE,
    ];

    $form['new_layout'] = [
      '#title' => $this->t('New Layout'),
      '#type' => 'select',
      '#options' => Layout::getLayoutOptions(['group_by_category' => TRUE]),
      '#default_value' => $cached_values['layout_change']['new_layout'],
      '#disabled' => TRUE,
    ];

    // If a layout is already selected, show the layout settings.
    $form['layout_settings_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Layout settings'),
    ];

    $layout = Layout::layoutPluginManager()->createInstance($cached_values['layout_change']['new_layout'], []);
    $form['layout_settings_wrapper']['layout_settings'] = $layout->buildConfigurationForm([], $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');
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
