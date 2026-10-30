<?php

namespace Drupal\anchor_link\Hook;

/**
 * Hook implementations for anchor_link.
 */
class AnchorLinkHooks {

  public function __construct(mixed ...$args) {
  }

  public function help(mixed ...$args) {
    return '';
  }

  public function __call(string $name, array $arguments) {
    return NULL;
  }

}
