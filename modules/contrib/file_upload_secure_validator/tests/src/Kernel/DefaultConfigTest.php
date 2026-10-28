<?php

namespace Drupal\Tests\file_upload_secure_validator\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that the installed config matches the default config.
 *
 * @group Config
 */
class DefaultConfigTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['file_upload_secure_validator'];

  /**
   * Tests if installed config is equal to the exported config.
   */
  public function testDefaultConfigurationExists() {
    $this->installConfig(['file_upload_secure_validator']);
    $config = $this->config('file_upload_secure_validator.settings');
    $this->assertNotNull($config->get('mime_types_equivalence_groups'));
  }

}
