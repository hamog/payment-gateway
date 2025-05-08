# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hamog/payment.svg?style=flat-square)](https://packagist.org/packages/hamog/payment)
[![Total Downloads](https://img.shields.io/packagist/dt/hamog/payment.svg?style=flat-square)](https://packagist.org/packages/hamog/payment)
![GitHub Actions](https://github.com/hamog/payment/actions/workflows/main.yml/badge.svg)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require hamog/payment
```

## Usage

```php
use Hamog\Payment\Facades\Payment;

$paymentMethods = Payment::availableGateways();

// Create a payment intent for client-side processing
$intent = Payment::gateway('stripe')->createIntent(100.00, [
    'order_id' => 'ORD-123',
]);

// Process a payment server-side
$payment = Payment::gateway('stripe')->charge(100.00, [
    'payment_method_id' => 'pm_...',
], [
    'order_id' => 'ORD-123',
]);

// Check if payment was successful
if ($payment['success']) {
    // Payment succeeded
    $transactionId = $payment['transaction_id'];
}
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email hashemm364@gmail.com instead of using the issue tracker.

## Credits

-   [Hashem Moghaddari](https://github.com/hamog)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
