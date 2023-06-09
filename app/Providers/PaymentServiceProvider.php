<?php

namespace App\Providers;

use App\Interfaces\PaymentService;
use App\Payment\SuperPaymentProvider;

class PaymentServiceProvider extends AppServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {

        $this->app->bind(PaymentService::class, function ($app) {

            // Determine which payment gateway to use based on request params,
            // if no key is attached in payment gateway such as stripe,
            // paypal then we will use the default super payment gateway
            $gateway = request()->payment_method ?? 'default';

            // Bind the corresponding payment gateway class
            switch ($gateway) {
//                case 'stripe':
//                    return new StripePaymentGateway();
//                case 'paypal':
//                    return new PayPalPaymentGateway();
                default:
                    return new SuperPaymentProvider();
            }
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
