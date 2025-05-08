<?php

namespace Hamog\Payment\Gateways;

use Hamog\Payment\AbstractPaymentGateway;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeGateway extends AbstractPaymentGateway
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    protected function formatAmount(float $amount): int
    {
        // Stripe requires amounts in cents/pennies
        return (int) ($amount * 100);
    }

    public function charge(float $amount, array $paymentData, array $metadata = []): array
    {
        try {
            $formattedAmount = $this->formatAmount($amount);

            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $formattedAmount,
                'currency' => config('services.stripe.currency', 'usd'),
                'payment_method' => $paymentData['payment_method_id'] ?? null,
                'confirm' => true,
                'metadata' => $metadata,
            ]);

            $this->logTransaction('charge', [
                'amount' => $amount,
                'transaction_id' => $paymentIntent->id,
                'reference_id' => $metadata['order_id'] ?? null,
                'status' => $paymentIntent->status,
            ]);

            return [
                'success' => $paymentIntent->status === 'succeeded',
                'transaction_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'gateway_response' => $paymentIntent,
            ];
        } catch (ApiErrorException $e) {
            return $this->handleApiError($e);
        }
    }

    public function refund(string $transactionId, ?float $amount = null): array
    {
        try {
            $refundData = ['payment_intent' => $transactionId];

            if ($amount !== null) {
                $refundData['amount'] = $this->formatAmount($amount);
            }

            $refund = $this->stripe->refunds->create($refundData);

            $this->logTransaction('refund', [
                'transaction_id' => $transactionId,
                'refund_id' => $refund->id,
                'amount' => $amount,
                'status' => $refund->status,
            ]);

            return [
                'success' => $refund->status === 'succeeded',
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'gateway_response' => $refund,
            ];
        } catch (ApiErrorException $e) {
            return $this->handleApiError($e);
        }
    }

    public function getStatus(string $transactionId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($transactionId);

            return [
                'success' => true,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100, // Convert back from cents
                'gateway_response' => $paymentIntent,
            ];
        } catch (ApiErrorException $e) {
            return $this->handleApiError($e);
        }
    }

    public function createIntent(float $amount, array $metadata = []): array
    {
        try {
            $formattedAmount = $this->formatAmount($amount);

            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $formattedAmount,
                'currency' => config('services.stripe.currency', 'usd'),
                'metadata' => $metadata,
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'intent_id' => $paymentIntent->id,
                'gateway' => 'stripe',
                'publishable_key' => config('services.stripe.key'),
            ];
        } catch (ApiErrorException $e) {
            return $this->handleApiError($e);
        }
    }

    public function handleWebhook(array $payload): array
    {
        try {
            $webhookSecret = config('payment.settings.stripe.webhook_secret');

            // Get the signature from the headers
            $signature = request()->header('Stripe-Signature');

            // Verify webhook signature
            $event = Webhook::constructEvent(
                request()->getContent(),
                $signature,
                $webhookSecret
            );

            // Handle the event based on its type
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    // Handle successful payment
                    $this->logTransaction('webhook', [
                        'type' => $event->type,
                        'transaction_id' => $paymentIntent->id,
                        'status' => $paymentIntent->status,
                        'amount' => $paymentIntent->amount / 100,
                    ]);
                    break;

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    // Handle failed payment
                    $this->logTransaction('webhook', [
                        'type' => $event->type,
                        'transaction_id' => $paymentIntent->id,
                        'status' => $paymentIntent->status,
                        'amount' => $paymentIntent->amount / 100,
                        'error' => $paymentIntent->last_payment_error,
                    ]);
                    break;

                // More event types...

                default:
                    // Log but don't process other events
                    $this->logTransaction('webhook', [
                        'type' => $event->type,
                        'id' => $event->id,
                    ]);
                    break;
            }

            return [
                'success' => true,
                'message' => 'Webhook processed successfully',
                'event_type' => $event->type,
            ];
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            return [
                'success' => false,
                'error' => 'Invalid signature',
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return $this->handleApiError($e);
        }
    }
}
