<?php



namespace Drupal\stripe_payment\Controller;



use Drupal\Core\Controller\ControllerBase;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;

use Stripe\Stripe;

use Stripe\Charge;



/**

 * Handles the Stripe payment process.

 */

class StripePaymentController extends ControllerBase {



  /**

   * Renders the payment form.

   */

  public function paymentForm() {

    // Replace this with your actual publishable key from Stripe.

    $stripe_publishable_key = 'pk_test_51PvxzOBIKXoN1zhwC8PPhaSYO6m6TakNMFlEHzlcNjkdCPRtxH6Ity9pIjvXgjXYucGSviG8wlWFg8KkfzFLqYzb00y3dsTnmo';



    return [

      '#theme' => 'stripe_payment_form',

      '#stripe_publishable_key' => $stripe_publishable_key,

      '#attached' => [

        'library' => [

          'stripe_payment/stripe_payment',

        ],

      ],

    ];

  }



  /**

   * Handles the payment request.

   */

   public function processPayment(Request $request) {

    $stripe_secret_key = 'sk_test_51PvxzOBIKXoN1zhwNnujVMA7PWh6ymDmNdNgVAxfssc8jhm2rD4ppTEGAA6DUOgf3l7eWgjEPHd9H8cV5mt7AAgf00nQbOxnFZ';



    Stripe::setApiKey($stripe_secret_key);

    $stripeToken = $request->request->get('stripeToken');
    $amount = $request->request->get('amount');

    if (!$stripeToken || !$amount) {
      return new JsonResponse(['error' => 'Missing required payment data']);
    }

    try {
      $amount_in_cents = (int) $amount * 100;

      $charge = Charge::create([
        'amount' => $amount_in_cents,
        'currency' => 'usd',
        'source' => $stripeToken,
        'description' => 'Test payment',
      ]);

      return new JsonResponse(['success' => true, 'charge_id' => $charge->id]);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => $e->getMessage()]);
    }

  }

}

