<?php

namespace Drupal\commerce_exchanger;

/**
 * Interface ExchangerManagerInterface.
 */
interface ExchangerManagerInterface {

  /**
   * Gets latest exchange rates.
   */
  public function getLatest(string $exchanger_id = ''): array;

  /**
   * Sets latest exchange rates.
   */
  public function setLatest(string $exchanger_id, array $rates): void;

}
