<?php

namespace App\Payment;

use App\Interfaces\PaymentService;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SuperPaymentProvider implements PaymentService
{
    private $data = [];

    public function makePayment(Order $order, $data): array
    {
        $this->data = $data;
        $url = 'https://superpay.view.agentur-loop.com/pay';

        $response = Http::withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
            ->post($url, $data);

        if ($response->successful()) {

            $responseArray = $response->json();

            if ($responseArray['message'] == "Insufficient Funds") {
                return $this->InsufficientFund();
            } elseif ($responseArray['message'] == "Payment Successful") {
                return $this->SuccessPayment($order);
            }
        }
        return $this->FailedPayment();
    }

    public function FailedPayment() : array
    {
        Log::channel('payment_logs')->error("Payment Error: Gateway API", $this->data);
        return [
            'status' => false,
            'message' => 'Error Processing the Payment'
        ];
    }

    public function InsufficientFund():array
    {
        Log::channel('payment_logs')->error("Payment Failed: Insufficient Fund ", $this->data);
        return [
            'status' => false,
            'message' => 'Insufficient Funds'
        ];
    }

    public function SuccessPayment($order) : array
    {
        Log::channel('payment_logs')->info("Payment Success", $this->data);

        $order->payed = true ;
        $order->save();
        return [
            'status' => true,
            'message' => 'Payment Successful'
        ];
    }

}
