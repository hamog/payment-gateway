<?php

namespace App\Services\Payment;

use Hamog\Payment\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Log;
use Hamog\Payment\Models\PaymentTransaction;

abstract class AbstractPaymentGateway implements PaymentGatewayInterface
{
    /**
     * Format money amount according to gateway requirements
     */
    protected function formatAmount(float $amount): int|float
    {
        // Default implementation - can be overridden by gateways
        return $amount;
    }

    /**
     * Log payment transaction
     */
    protected function logTransaction(string $type, array $data): void
    {
        // Skip logging if disabled
        if (!config('payment.logging.enabled', true)) {
            return;
        }

        // Log to configured channel
        $channel = config('payment.logging.channel', 'stack');
        Log::channel($channel)->info("Payment {$type}: ", $data);

        // Create transaction record in database
        try {
            PaymentTransaction::query()->create([
                'type' => $type,
                'gateway' => $this->getGatewayName(),
                'amount' => $data['amount'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'status' => $data['status'] ?? null,
                'metadata' => json_encode($data),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to save payment transaction: " . $e->getMessage());
        }
    }

    /**
     * Handle API errors in a consistent way
     */
    protected function handleApiError(\Exception $e): array
    {
        Log::error("Payment API Error: " . $e->getMessage(), [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'gateway' => $this->getGatewayName(),
        ]);

        return [
            'success' => false,
            'error' => $e->getMessage(),
            'error_code' => $e->getCode(),
        ];
    }

    /**
     * Get the name of the current gateway
     */
    protected function getGatewayName(): string
    {
        // Extract gateway name from class name
        $class = get_class($this);
        $parts = explode('\\', $class);
        $className = end($parts);

        return str_replace('Gateway', '', $className);
    }
}
