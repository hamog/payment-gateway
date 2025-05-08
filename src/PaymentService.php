<?php

namespace Hamog\Payment;

use Hamog\Payment\Contracts\PaymentGatewayInterface;
use InvalidArgumentException;

class PaymentService
{
    /**
     * Get a payment gateway instance
     *
     * @param string|null $gateway Gateway name (stripe, paypal, etc.)
     * @return PaymentGatewayInterface
     * @throws InvalidArgumentException If the gateway is not supported
     */
    public function gateway(?string $gateway = null): PaymentGatewayInterface
    {
        // Use default gateway if none specified
        $gateway = $gateway ?? config('payment.default_gateway');

        // Get available gateways from config
        $gateways = config('payment.gateways', []);

        if (!isset($gateways[$gateway])) {
            throw new InvalidArgumentException("Payment gateway [{$gateway}] is not supported.");
        }

        $gatewayClass = $gateways[$gateway];

        return app($gatewayClass);
    }

    /**
     * List all available payment gateways
     *
     * @return array Array of available gateway names
     */
    public function availableGateways(): array
    {
        return array_keys(config('payment.gateways', []));
    }

    /**
     * Check if a gateway is available
     *
     * @param string $gateway Gateway name to check
     * @return bool True if gateway is available
     */
    public function hasGateway(string $gateway): bool
    {
        $gateways = config('payment.gateways', []);
        return isset($gateways[$gateway]);
    }
}
