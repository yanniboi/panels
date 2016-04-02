<?php

/**
 * @file
 * Contains \Drupal\panels\Tests\PanelsTest.
 */

namespace Drupal\panels\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests using PanelsVariant with page_manager.
 *
 * @group panels
 */
class PanelsTest extends WebTestBase {

  /**
   * {@inheritdoc}
   */
  protected $strictConfigSchema = FALSE;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['block', 'page_manager', 'page_manager_ui', 'panels_test', 'layout_plugin_example'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('system_branding_block');
    $this->drupalPlaceBlock('page_title_block');

    \Drupal::service('theme_handler')->install(['bartik', 'classy']);
    $this->config('system.theme')->set('admin', 'classy')->save();

    $this->drupalLogin($this->drupalCreateUser(['administer pages', 'access administration pages', 'view the administration theme']));
  }

  /**
   * Tests adding a layout with settings.
   */
  public function testLayoutSettings() {
    // Create new page.
    $this->drupalGet('admin/structure/page_manager/add');
    $edit = [
      'id' => 'foo',
      'label' => 'foo',
      'path' => 'testing',
      'variant_plugin_id' => 'panels_variant',
    ];
    $this->drupalPostForm(NULL, $edit, 'Next');

    // Parameters step
    // @TODO remove once parameters step is contextual.
    $this->drupalPostForm(NULL, [], 'Next');

    // Add variant with a layout that has settings.
    $edit = [
      'page_variant_label' => 'Default',
    ];
    $this->drupalPostForm(NULL, $edit, 'Next');

    // Choose a layout.
    $edit = [
      'layout' => 'layout_example_test',
    ];
    $this->drupalPostForm(NULL, $edit, 'Next');

    // Add a block.
    $this->clickLink('Add new block');
    $this->clickLink('Powered by Drupal');
    $edit = [
      'region' => 'top',
    ];
    $this->drupalPostForm(NULL, $edit, 'Add block');

    // Finish the page add wizard.
    $this->drupalPostForm(NULL, [], 'Finish');

    // Go to the layout step for the variant.
    $this->drupalGet('admin/structure/page_manager/manage/foo/page_variant__foo-panels_variant-0__layout');

    // Check the default value and change a layout setting.
    $this->assertText('Blah');
    $this->assertFieldByName("layout_settings[setting_1]", "Default");
    $edit = [
      'layout_settings[setting_1]' => 'Abracadabra',
    ];

    // Save page form.
    $this->drupalPostForm(NULL, $edit, 'Update and save');

    // Go back to the variant edit form and see that the setting stuck.
    $this->drupalGet('admin/structure/page_manager/manage/foo/page_variant__foo-panels_variant-0__layout');
    $this->assertFieldByName("layout_settings[setting_1]", "Abracadabra");

    // View the page and make sure the setting is present.
    $this->drupalGet('testing');
    $this->assertText('Blah:');
    $this->assertText('Abracadabra');
    $this->assertText('Powered by Drupal');
  }

}
