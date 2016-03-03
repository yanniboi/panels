<?php

/**
 * @file
 * Contains \Drupal\panels\Form\DisplayEditForm.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ctools\Form\AjaxFormTrait;

/**
 * Provides a form for editing a display entity.
 */
abstract class DisplayEditForm extends DisplayFormBase {

  use AjaxFormTrait;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $attributes = $this->getAjaxAttributes();
    $add_button_attributes = $this->getAjaxButtonAttributes();

    $form['parameters_section'] = $this->buildParametersForm($add_button_attributes);

    $form['variant_section'] = [
      '#type' => 'details',
      '#title' => $this->t('Variants'),
      '#open' => TRUE,
    ];
    $form['variant_section']['add_new_variant'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new variant'),
      '#url' => Url::fromRoute('entity.display_variant.' . $this->entity->getEntityTypeId() . '_select', [
        $this->entity->getEntityTypeId() => $this->entity->id(),
      ]),
      '#attributes' => $add_button_attributes,
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];
    $form['variant_section']['variants'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Label'),
        $this->t('Plugin'),
        $this->t('Weight'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('There are no variants.'),
      '#tabledrag' => [[
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'variant-weight',
      ]],
    ];
    /** @var \Drupal\panels\Entity\DisplayVariantInterface $display_variant */
    foreach ($this->entity->getVariants() as $display_variant) {
      $row = [
        '#attributes' => [
          'class' => ['draggable'],
        ],
      ];
      $row['label']['#markup'] = $display_variant->label();
      $row['id']['#markup'] = $display_variant->getVariantPlugin()->adminLabel();
      $row['weight'] = [
        '#type' => 'weight',
        '#default_value' => $display_variant->getWeight(),
        '#title' => $this->t('Weight for @display_variant variant', ['@display_variant' => $display_variant->label()]),
        '#title_display' => 'invisible',
        '#attributes' => [
          'class' => ['variant-weight'],
        ],
      ];
      $operations = [];
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'url' => $display_variant->toUrl($this->entity->getEntityTypeId() . '-edit-form'),
      ];
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'url' => $display_variant->toUrl($this->entity->getEntityTypeId() . '-delete-form'),
      ];
      $row['operations'] = [
        '#type' => 'operations',
        '#links' => $operations,
      ];
      $form['variant_section']['variants'][$display_variant->id()] = $row;
    }

    if ($access_conditions = $this->entity->getAccessConditions()) {
      $form['access_section_section'] = [
        '#type' => 'details',
        '#title' => $this->t('Access Conditions'),
        '#open' => TRUE,
      ];
      $form['access_section_section']['add'] = [
        '#type' => 'link',
        '#title' => $this->t('Add new access condition'),
        '#url' => Url::fromRoute('entity.' . $this->entity->getEntityTypeId() . '.access_condition_select', [
          $this->entity->getEntityTypeId() => $this->entity->id(),
        ]),
        '#attributes' => $add_button_attributes,
        '#attached' => [
          'library' => [
            'core/drupal.ajax',
          ],
        ],
      ];
      $form['access_section_section']['access_section'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Label'),
          $this->t('Description'),
          $this->t('Operations'),
        ],
        '#empty' => $this->t('There are no access conditions.'),
      ];

      $form['access_section_section']['access_logic'] = [
        '#type' => 'radios',
        '#options' => [
          'and' => $this->t('All conditions must pass'),
          'or' => $this->t('Only one condition must pass'),
        ],
        '#default_value' => $this->entity->getAccessLogic(),
      ];

      $form['access_section_section']['access'] = [
        '#tree' => TRUE,
      ];
      foreach ($access_conditions as $access_id => $access_condition) {
        $row = [];
        $row['label']['#markup'] = $access_condition->getPluginDefinition()['label'];
        $row['description']['#markup'] = $access_condition->summary();
        $operations = [];
        $operations['edit'] = [
          'title' => $this->t('Edit'),
          'url' => Url::fromRoute('entity.' . $this->entity->getEntityTypeId() . '.access_condition_edit', [
            $this->entity->getEntityTypeId() => $this->entity->id(),
            'condition_id' => $access_id,
          ]),
          'attributes' => $attributes,
        ];
        $operations['delete'] = [
          'title' => $this->t('Delete'),
          'url' => Url::fromRoute('entity.' . $this->entity->getEntityTypeId() . '.access_condition_delete', [
            $this->entity->getEntityTypeId() => $this->entity->id(),
            'condition_id' => $access_id,
          ]),
          'attributes' => $attributes,
        ];
        $row['operations'] = [
          '#type' => 'operations',
          '#links' => $operations,
        ];
        $form['access_section_section']['access_section'][$access_id] = $row;
      }
    }

    return $form;
  }

  /**
   * Builds the parameters form for a display entity.
   *
   * @return array
   */
  protected function buildParametersForm($add_button_attributes) {
    $form = [
      '#type' => 'details',
      '#title' => $this->t('Parameters'),
      '#open' => TRUE,
    ];
    $form['add_new_parameter'] = [
      '#type' => 'link',
      '#title' => $this->t('Add new parameter'),
      '#url' => Url::fromRoute('entity.' . $this->entity->getEntityTypeId() . '.parameter_add', [
        $this->entity->getEntityTypeId() => $this->entity->id(),
      ]),
      '#attributes' => $add_button_attributes,
      '#attached' => [
        'library' => [
          'core/drupal.ajax',
        ],
      ],
    ];
    $form['parameters'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Machine name'),
        $this->t('Label'),
        $this->t('Type'),
        $this->t('Operations'),
      ],
      '#empty' => $this->t('There are no parameters.'),
    ];
    foreach ($this->entity->getParameters() as $parameter) {
      $row = [];
      $row['machine_name'] = $parameter['machine_name'];
      if ($label = $parameter['label']) {
        $row['label'] = $label;
      }
      else {
        $row['type']['colspan'] = 2;
      }
      $row['type']['data'] = $parameter['type'] ?: $this->t('<em>No context assigned</em>');



      $operations = [];
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'url' => Url::fromRoute('entity.' . $this->entity->getEntityTypeId() . '.parameter_edit', [
          $this->entity->getEntityTypeId() => $this->entity->id(),
          'name' => $parameter['machine_name'],
        ]),
        'attributes' => $this->getAjaxAttributes(),
      ];
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute('entity.' . $this->entity->getEntityTypeId() . '.parameter_delete', [
          $this->entity->getEntityTypeId() => $this->entity->id(),
          'name' => $parameter['machine_name'],
        ]),
      ];
      $row['operations']['data'] = [
        '#type' => 'operations',
        '#links' => $operations,
      ];

      $form['parameters']['#rows'][$parameter['machine_name']] = $row;
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if (!$form_state->isValueEmpty('variants')) {
      foreach ($form_state->getValue('variants') as $variant_id => $data) {
        if ($variant_entity = $this->entity->getVariant($variant_id)) {
          $variant_entity->setWeight($data['weight']);
          $variant_entity->save();
        }
      }
    }
    parent::save($form, $form_state);
    $form_state->setRedirect('entity.' . $this->entity->getEntityTypeId() . '.collection');
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $keys_to_ignore = ['variants', 'parameters'];
    $values_to_restore = [];
    foreach ($keys_to_ignore as $key) {
      $values_to_restore[$key] = $form_state->getValue($key);
      $form_state->unsetValue($key);
    }
    parent::copyFormValuesToEntity($entity, $form, $form_state);
    foreach ($values_to_restore as $key => $value) {
      $form_state->setValue($key, $value);
    }
  }

}
