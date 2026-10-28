<?php

namespace Drupal\stripe_donation\Form;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 * This is a user facing form for collecting donations with Stripe
 * @see \Drupal\Core\Form\FormBase
 */
class StripeDonationForm extends FormBase{
    /**
     * Build our Stripe donation form. This is the end user facing form
     *
     * @param array $form
     *   Default form array structure.
     * @param FormStateInterface $form_state
     *   Object containing current form state.
     *
     * @return array
     *   The render array defining the elements of the form.
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['amount'] = [
            '#id' => 'amount',
            '#type' => 'number',
            '#title' => $this->t('Montant du règlement'),
            '#description' => $this->t('Merci de votre soutien. Veuillez nous indiquer le montant du règlement.'),
            '#required' => TRUE,
        ];

        // Add a text area for the message description
        $form['message'] = [
            '#id' => 'message',
            '#type' => 'textarea',
            '#title' => $this->t('Description du message'),
            '#required' => FALSE,
        ];
        
        // Add a hidden field for the Payment Method ID (will be populated by Stripe.js)
        $form['paymentMethodId'] = [
            '#type' => 'hidden',
            '#attributes' => ['id' => 'payment-method-id'],  // This ID is important for Stripe to fill it later
        ];
        
        $form['card-element'] = [
            '#type' => 'markup',
            '#markup' => '<div id="card-element"><!-- Your form goes here --></div>',
        ];
        
        $form['card-errors'] = [
            '#type' => 'markup',
            '#markup' => '<div id="card-errors" role="alert"></div>',
        ];

        $form['actions'] = [
            '#type' => 'actions',
        ];

        $form['actions']['submit'] = [
            '#id' => 'donate',
            '#type' => 'submit',
            '#value' => $this->t('Exécuter règlement'),
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'stripe_donation_form';
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // TODO: Log the purchase amount along with any other information that
        // would be useful to store about the transaction.
        $amount = $form_state->getValue('amount');
        $message = $form_state->getValue('message');  // Get the message description
        
        $stripe_secret_key = \Drupal::config('stripe_donation.settings')->get('stripe_secret_key');
        $stripe_default_currency = \Drupal::config('stripe_donation.settings')->get('stripe_default_currency')  ?: 'EUR' ;
        
        \Stripe\Stripe::setApiKey($stripe_secret_key);

        // Get the paymentMethodId from the form submission
        $paymentMethodId = $form_state->getValue('paymentMethodId');  // Ensure that the payment method ID is passed from the form

        // Check if the paymentMethodId is present
        if (!$paymentMethodId) {
            // Handle the case where paymentMethodId is missing
            \Drupal::messenger()->addError('Payment failed. Payment method is missing.');
            return;
        }

        try {
            // Convert the amount to cents
            $amount_in_cents = (int) $amount * 100;

            // Define your return URL (the page users are redirected to after 3D Secure authentication)
            $return_url = \Drupal\Core\Url::fromRoute('stripe_donation.thank_you', [], ['absolute' => TRUE])->toString();

            // Create a PaymentIntent
            $paymentIntent  = \Stripe\PaymentIntent::create([
                'amount' => $amount_in_cents,
                'currency' => $stripe_default_currency,
                'payment_method' => $paymentMethodId,
                'description' => $message,  // Add the message description to the charge
                'return_url' => $return_url,  // Add the return_url
                'confirmation_method' => 'manual',  // Manual confirmation
                'confirm' => true,  // Immediate confirmation
            ]);

            if ($paymentIntent->status == 'requires_action' || $paymentIntent->status == 'requires_source_action') {
                // Payment requires 3D Secure authentication
                \Drupal::messenger()->addWarning('3D Secure authentication is required. Please complete the authentication to finish the payment.');
                // You will need to handle the frontend part to trigger the 3D Secure authentication here
            } else if ($paymentIntent->status == 'succeeded') {
                // Payment was successful
                \Drupal::messenger()->addStatus('Le don a été envoyé avec succès. Merci pour votre soutien.');
            } else {
                // Handle other statuses or errors
                \Drupal::messenger()->addError('Payment failed or is incomplete. Please try again.');
            }

        } catch(Exception $e) {
            // something went wrong
            \Drupal::logger('stripe_donation')->error('Payment Error: ' . $e->getMessage());
            \Drupal::messenger()->addStatus('Le don a échoué. Veuillez réessayer plus tard.');
        }
    } 

}
