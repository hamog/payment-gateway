<?php

namespace Hamog\Payment\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Hamog\Payment\Contracts\PaymentGatewayInterface gateway(string $gateway = null)
 * @method static array availableGateways()
 * @method static bool hasGateway(string $gateway)
 *
 * @see \App\Services\Payment\PaymentService
 */
class Payment extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'payment';
    }
}
