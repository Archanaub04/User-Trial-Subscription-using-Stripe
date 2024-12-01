<?php

namespace App\Services;

use App\Helpers\SubscriptionHelper;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected $stripe;
    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET_KEY'));
    }

    public function getOrCreateCustomer(User $user, $stripeData)
    {
        $stripeCustomerId = $user->stripe_customer_id;
        // Verify if the customer exists in Stripe
        if ($stripeCustomerId) {
            try {
                $customer = $this->stripe->customers->retrieve($stripeCustomerId);
                // If the customer is retrieved successfully, return the ID
                return $stripeCustomerId;
            } catch (ApiErrorException $e) {
                // Customer ID is invalid, proceed to create a new customer
                Log::error("Stripe customer retrieval failed: " . $e->getMessage());
            }
        }
        // If no valid Stripe customer ID, create a new customer
        $customer = $this->createCustomer($stripeData['id'], $user);
        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    public function createCustomer($token_id, $user)
    {
        return $this->stripe->customers->create([
            'name' => $user->name,
            'email' => $user->email,
            'source' => $token_id,
        ]);
    }

    // charge pending fee
    public function createStripeCharge($amountForRest, $customer_id, $user_name, $description)
    {
        return $this->stripe->charges->create([
            'amount' => $amountForRest * 100,
            'currency' => 'usd',
            'customer' => $customer_id,
            'description' => $description,
            'shipping' => [
                'name' => $user_name,
                'address' => [
                    'line1' => '123 Main Station',
                    'line2' => 'Apt 1',
                    'city' => 'Anytown',
                    'state' => 'NY',
                    'postal_code' => '123456',
                    'country' => 'US'
                ]
            ]
        ]);
    }

    // create subscription
    public function createSubscription($customer_id, $subscriptionPlan, $current_period_start)
    {
        return $this->stripe->subscriptions->create([
            'customer' => $customer_id,
            'items' => [
                [
                    'price' => $subscriptionPlan->stripe_price_id
                ],
            ],
            'billing_cycle_anchor' => strtotime($current_period_start), // timestamp - takes milling seconds date - not starting biling immediately - if immediate bill - then git null
            'proration_behavior' => 'none'
        ]);

        /*
        * billing_cycle_anchor - it will gave when the subscription need to start
        * so if it provide no immediate billing occur - payment
        * if want immediate villing not provide this or give it as null
        */
    }

    // Retrieve Plan details after susbcription
    public function retrievePlanDetails($planID)
    {
        return $this->stripe->plans->retrieve($planID, []);
    }

    // Retrieve subscription
    public function retrieveSubscriptionData($subscriptionID)
    {
        return $this->stripe->subscriptions->retrieve($subscriptionID);
    }

    // cancel current stripe subscription

    public function cancelSubscription($subscriptionID)
    {
        $subscription = $this->retrieveSubscriptionData($subscriptionID)->cancel();
        Log::info($subscription);
        return $subscription;
    }

    /* --------------- STRIPE WEBHOOK -------------------------------- */

    // stripe webhook verification
    public function stripeWebhookVerification()
    {
        $endpoint_secret = env('STRIPE_ENDPOINT_SECRET');
        $payload = @file_get_contents('php://input');
        $event = null;

        try {
            $event = \Stripe\Event::constructFrom(
                json_decode($payload, true)
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        }

        if ($endpoint_secret) {
            // Only verify the event if there is an endpoint secret defined
            // Otherwise use the basic decoded event
            $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $sig_header,
                    $endpoint_secret
                );
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                // Invalid signature
                http_response_code(400);
                exit();
            }
        }
        return $event;
    }
    
}
