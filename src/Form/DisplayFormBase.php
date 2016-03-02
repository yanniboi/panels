<?php

/**
 * @file
 * Contains \Drupal\panels\Form\DisplayFormBase.
 */

namespace Drupal\panels\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base form for editing and adding a page entity.
 */
abstract class DisplayFormBase extends EntityForm {

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\panels\Entity\DisplayInterface
   */
  protected $entity;

  /**
   * The entity query factory.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Construct a new PageFormBase.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   The entity query factory.
   */
  public function __construct(QueryFactory $entity_query) {
    $this->entityQuery = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t(''),
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
      '#maxlength' => '255',
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#disabled' => !$this->entity->isNew(),
      '#maxlength' => 64,
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => [$this, 'exists'],
      ],
    ];

    return parent::form($form, $form_state);
  }

  /**
   * Determines if the page entity already exists.
   *
   * @param string $id
   *   The page entity ID.
   *
   * @return bool
   *   TRUE if the format exists, FALSE otherwise.
   */
  public function exists($id) {
    return (bool) $this->entityQuery->get($this->entity->getEntityTypeId())
      ->condition('id', $id)
      ->execute();
  }

}
