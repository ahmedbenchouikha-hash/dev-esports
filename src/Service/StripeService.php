<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Customer;
use Stripe\Refund;
use App\Entity\Ticket;

class StripeService
{
    private string $stripeApiKey;

    public function __construct(string $stripeApiKey)
    {
        $this->stripeApiKey = $stripeApiKey;
        Stripe::setApiKey($this->stripeApiKey);
    }

    /**
     * Create a payment intent for ticket purchase
     * 
     * @param float $amount Amount in dollars
     * @param string $currency Currency code (usd, eur, etc)
     * @param Ticket $ticket The ticket being purchased
     * @param string $customerEmail Customer email address
     * @return PaymentIntent
     */
    public function createPaymentIntent(
        float $amount,
        string $currency,
        Ticket $ticket,
        string $customerEmail
    ): PaymentIntent
    {
        return PaymentIntent::create([
            'amount' => (int)($amount * 100), // Stripe uses cents
            'currency' => $currency,
            'description' => sprintf(
                'Ticket Purchase: %s (%s)',
                $ticket->getTicketNumber(),
                $ticket->getType()
            ),
            'receipt_email' => $customerEmail,
            'metadata' => [
                'ticket_id' => $ticket->getId(),
                'ticket_number' => $ticket->getTicketNumber(),
                'ticket_type' => $ticket->getType(),
                'game_id' => $ticket->getGame()?->getId(),
                'customer_email' => $customerEmail,
            ],
        ]);
    }

    /**
     * Retrieve an existing payment intent
     */
    public function getPaymentIntent(string $intentId): PaymentIntent
    {
        return PaymentIntent::retrieve($intentId);
    }

    /**
     * Confirm payment (called after client-side payment)
     */
    public function confirmPayment(string $paymentIntentId): PaymentIntent
    {
        $intent = $this->getPaymentIntent($paymentIntentId);

        if ($intent->status === 'requires_payment_method') {
            // Payment needs to be confirmed by customer
            return $intent;
        }

        return $intent;
    }

    /**
     * Check if payment succeeded
     */
    public function isPaymentSucceeded(string $paymentIntentId): bool
    {
        $intent = $this->getPaymentIntent($paymentIntentId);
        return $intent->status === 'succeeded';
    }

    /**
     * Process refund for a payment
     */
    public function refundPayment(string $paymentIntentId, ?float $amount = null): Refund
    {
        $intent = $this->getPaymentIntent($paymentIntentId);

        if (!$intent->charges->data) {
            throw new \Exception('No charges found for this payment intent');
        }

        $chargeId = $intent->charges->data[0]->id;

        return Refund::create([
            'charge' => $chargeId,
            'amount' => $amount ? (int)($amount * 100) : null,
        ]);
    }

    /**
     * Create a customer for recurring payments
     */
    public function createCustomer(string $email, string $name = ''): Customer
    {
        return Customer::create([
            'email' => $email,
            'name' => $name,
        ]);
    }

    /**
     * Get Stripe publishable key for frontend
     */
    public function getPublishableKey(): string
    {
        // Get from environment or config
        return $_ENV['STRIPE_PUBLIC_KEY'] ?? '';
    }
}
