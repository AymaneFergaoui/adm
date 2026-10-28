<?php

namespace Drupal\commerce_product\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Ensures that a product only references its own variations.
 *
 * @Constraint(
 *   id = "ProductVariations",
 *   label = @Translation("The variations of the product.", context = "Validation")
 * )
 */
class ProductVariationsConstraint extends Constraint {

  public $message = 'The variation %label belongs to another product.';

}
