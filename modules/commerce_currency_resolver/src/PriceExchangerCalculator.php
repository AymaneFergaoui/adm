<?php

namespace Drupal\commerce_currency_resolver;

use Drupal\commerce_exchanger\AbstractExchangerCalculator;
use Drupal\commerce_exchanger\ExchangerManagerInterface;
use Drupal\commerce_price\RounderInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class PriceExchangerCalculator.
 *
 * @package Drupal\commerce_currency_resolver
 */
class PriceExchangerCalculator extends AbstractExchangerCalculator {

  /**
   * The exchanger manager.
   *
   * @var \Drupal\commerce_exchanger\ExchangerManagerInterface|null
   */
  protected $exchangerManager;

  /**
   * Constructs a PriceExchangerCalculator object.
   */
  public function __construct(mixed ...$args) {
    foreach ($args as $arg) {
      if ($arg instanceof EntityTypeManagerInterface) {
        $this->entityTypeManager = $arg;
        try {
          $this->providers = $arg->getStorage('commerce_exchange_rates')->loadMultiple();
        }
        catch (\Throwable $e) {
        }
      }
      elseif ($arg instanceof ConfigFactoryInterface || $arg instanceof ConfigFactory) {
        $this->configFactory = $arg;
      }
      elseif ($arg instanceof RounderInterface) {
        $this->rounder = $arg;
      }
      elseif ($arg instanceof ExchangerManagerInterface) {
        $this->exchangerManager = $arg;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getExchangerId() {
    if (!$this->configFactory) {
      return NULL;
    }
    $resolver_exchanger_id = $this->configFactory->get('commerce_currency_resolver.settings')->get('currency_exchange_rates');
    if (isset($this->providers[$resolver_exchanger_id]) && $this->providers[$resolver_exchanger_id]->status()) {
      return $this->providers[$resolver_exchanger_id]->getExchangerConfigName();
    }

    return NULL;
  }

}
