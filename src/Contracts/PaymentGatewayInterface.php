<?php

namespace Hamog\Payment\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Process a payment
     *
     * @param float $amount Amount to charge
     * @param array $paymentData Payment details (card info, etc)
     * @param array $metadata Additional data
     * @return array Payment response with transaction ID and status
     */
    public function charge(float $amount, array $paymentData, array $metadata = []): array;

    /**
     * Refund a payment
     *
     * @param string $transactionId Original transaction ID
     * @param float|null $amount Amount to refund (null for full refund)
     * @return array Refund response with refund ID and status
     */
    public function refund(string $transactionId, ?float $amount = null): array;

    /**
     * Get payment status
     *
     * @param string $transactionId Transaction ID to check
     * @return array Payment status response
     */
    public function getStatus(string $transactionId): array;

    /**
     * Create payment intent/setup for client side processing
     *
     * @param float $amount Amount to charge
     * @param array $metadata Additional data
     * @return array Intent/setup data for the client
     */
    public function createIntent(float $amount, array $metadata = []): array;

    /**
     * Process webhook data from the payment provider
     *
     * @param array $payload The webhook payload
     * @return array Processing result
     */
    public function handleWebhook(array $payload): array;
}
