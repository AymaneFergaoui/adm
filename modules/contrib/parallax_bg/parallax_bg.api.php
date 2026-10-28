<?php

/**
 * @file
 * Hooks for the parallax_bg module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the parallax settings.
 *
 * @param array $settings
 *   An array of all parallax settings.
 */
function hook_parallax_bg_settings_alter(array &$settings) {
  foreach ($settings as $key => &$value) {
    if ('#myid' == $value['selector']) {
      $value['speed'] = '1.8';
    }
  }
}

/**
 * @} End of "addtogroup hooks".
 */
