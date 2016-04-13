<?php
/**
 * @file
 * Contains \Drupal\page_manager_ui\Wizard\PageVariantAddWizard.
 */

namespace Drupal\panels\Wizard;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\Core\Url;
use Drupal\ctools\Event\WizardEvent;
use Drupal\ctools\Wizard\FormWizardBase;
use Drupal\ctools\Wizard\FormWizardInterface;
use Drupal\panels\Form\LayoutChangeRegions;
use Drupal\panels\Form\LayoutChangeSettings;

class LayoutChangeWizard extends FormWizardBase {

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    return 'panels.layout.change_form';
  }

  /**
   * {@inheritdoc}
   */
  public function initValues() {
    $values = $this->getTempstore()->get($this->getMachineName());
    $event = new WizardEvent($this, $values);
    $this->dispatcher->dispatch(FormWizardInterface::LOAD_VALUES, $event);
    return $event->getValues();
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations($cached_values) {
    $operations = [];
    $operations['settings'] = [
      'title' => $this->t('Settings'),
      'form' => LayoutChangeSettings::class,
    ];
    $operations['regions'] = [
      'title' => $this->t('Regions'),
      'form' => LayoutChangeRegions::class,
    ];

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  protected function customizeForm(array $form, FormStateInterface $form_state) {
    $form = parent::customizeForm($form, $form_state);

    // We set the variant id as part of form submission.
    if ($this->step == 'type' && isset($form['name']['id'])) {
      unset($form['name']['id']);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getNextParameters($cached_values) {
    $parameters = parent::getNextParameters($cached_values);

    // Add the page to the url parameters.
    $parameters['tempstore_id'] = $cached_values['layout_change']['tempstore_id'];
    return $parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreviousParameters($cached_values) {
    $parameters = parent::getPreviousParameters($cached_values);

    // Add the page to the url parameters.
    $parameters['tempstore_id'] = $cached_values['layout_change']['tempstore_id'];
    return $parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function finish(array &$form, FormStateInterface $form_state) {
    $cached_values = $form_state->getTemporaryValue('wizard');

    // Add the variant to the parent page tempstore.
    $page_tempstore = $this->tempstore->get('page_manager.page')->get($cached_values['page']->id());
    $page_tempstore['page']->addVariant($cached_values['page_variant']);
    $this->tempstore->get('page_manager.page')->set($cached_values['page']->id(), $page_tempstore);

    $variant_plugin = $cached_values['page_variant']->getVariantPlugin();
    drupal_set_message($this->t('The %label @entity_type has been added to the page, but has not been saved. Please save the page to store changes.', array(
      '%label' => $cached_values['page_variant']->label(),
      '@entity_type' => $variant_plugin->adminLabel(),
    )));

    $form_state->setRedirectUrl(new Url('entity.page.edit_form', [
      'machine_name' => $cached_values['page']->id(),
      'step' => 'general',
    ]));
  }

}
