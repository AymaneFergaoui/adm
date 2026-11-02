<?php

namespace Drupal\commerce_exchanger;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Fallback ExchangerManager service for compatibility with commerce_exchanger 2.x.
 */
class ExchangerManager implements ExchangerManagerInterface {

  /**
   * Constructs an ExchangerManager object.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ConfigFactoryInterface $configFactory,
    mixed ...$args
  ) {
  }

  /**
   * Gets latest exchange rates.
   */
  public function getLatest(string $exchanger_id = ''): array {
    return [];
  }

  /**
   * Sets latest exchange rates.
   */
  public function setLatest(string $exchanger_id, array $rates): void {
  }

  /**
   * Magic caller for any other methods.
   */
  public function __call(string $name, array $arguments) {
    return NULL;
  }

}
