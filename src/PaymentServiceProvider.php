<?php

namespace Hamog\Payment;

use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register the payment service as a singleton
        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService();
        });

        // Register the facade
        $this->app->bind('payment', function ($app) {
            return $app->make(PaymentService::class);
        });

        // Register gateway implementations
        $gateways = config('payment.gateways', []);

        foreach ($gateways as $name => $implementation) {
            $this->app->bind($implementation, function ($app) use ($implementation) {
                return new $implementation();
            });
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish the config file
        $this->publishes([
            __DIR__.'/../config/payment.php' => config_path('payment.php'),
        ], 'payment-config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'payment-migrations');
    }
}
