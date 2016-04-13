<?php
/**
 * @file
 * Contains \Drupal\panels\Form\LayoutChangeRegions.
 */

namespace Drupal\panels\Form;


use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\layout_plugin\Layout;
use Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface;
use Drupal\user\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LayoutChangeRegions extends FormBase {

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
    return 'panels_layout_regions_form';
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

    $old_layout = Layout::layoutPluginManager()->createInstance($cached_values['layout_change']['old_layout'], []);
    $new_layout = Layout::layoutPluginManager()->createInstance($cached_values['layout_change']['new_layout'], []);



    if ($block_assignments = $variant_plugin->getRegionAssignments()) {
      // Build a table of all blocks used by this variant.

      $form['blocks'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Label'),
          $this->t('Region'),
          $this->t('Weight'),
        ],
        '#empty' => $this->t('There are no regions for blocks.'),
      ];

      // Loop through the blocks per region.
      foreach ($new_layout->getPluginDefinition()['region_names'] as $region => $label) {

        // Add a section for each region and allow blocks to be dragged between
        // them.
        $form['blocks']['#tabledrag'][] = [
          'action' => 'match',
          'relationship' => 'sibling',
          'group' => 'block-region-select',
          'subgroup' => 'block-region-' . $region,
          'hidden' => FALSE,
        ];
        $form['blocks']['#tabledrag'][] = [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'block-weight',
          'subgroup' => 'block-weight-' . $region,
        ];
        $form['blocks'][$region] = [
          '#attributes' => [
            'class' => ['region-title', 'region-title-' . $region],
            'no_striping' => TRUE,
          ],
        ];
        $form['blocks'][$region]['title'] = [
          '#markup' => $label,
          '#wrapper_attributes' => [
            'colspan' => 3,
          ],
        ];
        $form['blocks'][$region . '-message'] = [
          '#attributes' => [
            'class' => [
              'region-message',
              'region-' . $region . '-message',
              empty($blocks) ? 'region-empty' : 'region-populated',
            ],
          ],
        ];
        $form['blocks'][$region . '-message']['message'] = [
          '#markup' => '<em>' . $this->t('No blocks in this region') . '</em>',
          '#wrapper_attributes' => [
            'colspan' => 3,
          ],
        ];
      }


      /** @var \Drupal\Core\Block\BlockPluginInterface[] $blocks */
      foreach ($old_layout->getPluginDefinition()['region_names'] as $region => $label) {

        if (empty($block_assignments[$region])) {
          continue;
        }

        // Prevent region names clashing with new regions.
        $region_id = 'old_'.$region;


        $row = [
          '#attributes' => [
            'class' => ['draggable'],
          ],
        ];
        $row['label']['#markup'] = $label;
        // Allow the region to be changed for each block.
        $row['region'] = [
          '#title' => $this->t('Region'),
          '#title_display' => 'invisible',
          '#type' => 'select',
          '#options' => $new_layout->getPluginDefinition()['region_names'],
          //'#default_value' => $variant_plugin->getRegionAssignment($block_id),
          '#default_value' => 'left',
          '#attributes' => [
            'class' => ['block-region-select', 'block-region-' . $region],
          ],
        ];
        // Allow the weight to be changed for each block.
        //$configuration = $block->getConfiguration();
        $row['weight'] = [
          '#type' => 'weight',
          //'#default_value' => isset($configuration['weight']) ? $configuration['weight'] : 0,
          '#default_value' => 0,
          '#title' => $this->t('Weight for @block block', ['@block' => $label]),
          '#title_display' => 'invisible',
          '#attributes' => [
            'class' => ['block-weight', 'block-weight-' . $region],
          ],
        ];
        $form['blocks'][$region_id] = $row;
      }
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

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    /* @var $variant_plugin \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant */
    $variant_plugin = $cached_values['plugin'];
    $variant_plugin->setLayout($cached_values['layout_change']['new_layout']);
    $cached_values['plugin'] = $variant_plugin;

    $page_tempstore = $this->tempstore->get('page_manager.page')->get($cached_values['page']->id());
    $page_tempstore['page_variant'] = $cached_values['page_variant'];
    $this->tempstore->get('page_manager.page')->set($cached_values['page']->id(), $page_tempstore);
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
