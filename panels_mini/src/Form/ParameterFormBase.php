<?php

/**
 * @file
 * Contains \Drupal\panels_mini\Form\ParameterFormBase.
 */

namespace Drupal\panels_mini\Form;

use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\ctools\Entity\DisplayInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for editing a parameter.
 */
abstract class ParameterFormBase extends FormBase {

  /**
   * The form key for unsetting a parameter context.
   *
   * @var string
   */
  const NO_CONTEXT_KEY = '__no_context';

  /**
   * The mini_panel entity this static context belongs to.
   *
   * @var \Drupal\ctools\Entity\DisplayInterface
   */
  protected $mini_panel;

  /**
   * The parameter configuration.
   *
   * @var array
   */
  protected $parameter;

  /**
   * The entity type repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  protected $entityTypeRepository;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * Returns the text to use for the submit button.
   *
   * @return string
   *   The submit button text.
   */
  abstract protected function submitButtonText();

  /**
   * Returns the text to use for the submit message.
   *
   * @return string
   *   The submit message text.
   */
  abstract protected function submitMessageText();

  /**
   * Constructs a new ParameterEditForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeRepositoryInterface $entity_type_repository
   *   The entity type repository.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   */
  public function __construct(EntityTypeRepositoryInterface $entity_type_repository, TypedDataManagerInterface $typed_data_manager) {
    $this->entityTypeRepository = $entity_type_repository;
    $this->typedDataManager = $typed_data_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.repository'),
      $container->get('typed_data_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, DisplayInterface $mini_panel = NULL, $name = '') {
    $this->mini_panel = $mini_panel;
    $this->parameter = $this->mini_panel->getParameter($name);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->parameter['label'] ?: ucfirst($this->parameter['machine_name']),
      '#states' => [
        'invisible' => [
          ':input[name="type"]' => ['value' => static::NO_CONTEXT_KEY],
        ],
      ],
    ];

    $form['machine_name'] = [
      '#type' => 'machine_name',
      '#maxlength' => 64,
      '#required' => TRUE,
      '#machine_name' => [
        'source' => ['label'],
      ],
      '#default_value' => $name,
    ];

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#required' => TRUE,
      '#options' => $this->buildParameterTypeOptions(),
      '#default_value' => $this->parameter['type'],
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->submitButtonText(),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * Builds an array of options for the parameter type.
   *
   * @return array[]
   *   A multidimensional array. The top level is keyed by group ('Content',
   *   'Configuration', 'Typed Data'). Those values are an array of type labels,
   *   keyed by the machine name.
   */
  protected function buildParameterTypeOptions() {
    $options = [static::NO_CONTEXT_KEY => $this->t('No context selected')];

    // Make a grouped, sorted list of entity type options. Key the inner array
    // to use the typed data format of 'entity:$entity_type_id'.
    foreach ($this->entityTypeRepository->getEntityTypeLabels(TRUE) as $group_label => $grouped_options) {
      foreach ($grouped_options as $key => $label) {
        $options[$group_label]['entity:' . $key] = $label;
      }
    }

    $primitives_label = (string) $this->t('Primitives');
    foreach ($this->typedDataManager->getDefinitions() as $key => $definition) {
      if (is_subclass_of($definition['class'], PrimitiveInterface::class)) {
        $options[$primitives_label][$key] = $definition['label'];
      }
    }
    asort($options[$primitives_label], SORT_NATURAL);

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $name = $form_state->getValue('machine_name');
    $type = $form_state->getValue('type');

    if ($type === static::NO_CONTEXT_KEY) {
      $this->mini_panel->removeParameter($name);
      $label = NULL;
    }
    else {
      $label = $form_state->getValue('label');
      $this->mini_panel->setParameter($name, $type, $label);
    }
    $this->mini_panel->save();

    // Set the submission message.
    drupal_set_message($this->submitMessageText());

    $form_state->setRedirectUrl($this->mini_panel->toUrl('edit-form'));
  }

}
