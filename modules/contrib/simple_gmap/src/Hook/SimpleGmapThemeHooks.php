<?php

namespace Drupal\simple_gmap\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for simple_gmap.
 */
class SimpleGmapThemeHooks {

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'simple_gmap_output' => [
        'variables' => [
          'include_map' => TRUE,
          'include_static_map' => FALSE,
          'include_link' => FALSE,
          'include_text' => FALSE,
          'iframe_height' => '200',
          'iframe_width' => '200',
          'iframe_title' => '',
          'static_scale' => 1,
          'url_suffix' => NULL,
          'zoom' => 14,
          'link_text' => 'View larger map',
          'address_text' => '',
          'map_type' => 'm',
          'langcode' => 'en',
          'static_map_type' => NULL,
          'apikey' => '',
          'alt_text' => '',
        ],
        'template' => 'simple-gmap-output',
      ],
    ];
  }

}
