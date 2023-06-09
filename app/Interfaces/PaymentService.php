<?php

namespace App\Interfaces;

use App\Models\Order;

interface PaymentService
{
    public function makePayment(Order $order, $data) : array;
    public function SuccessPayment($order):array;
    public function FailedPayment():array;
    public function InsufficientFund():array;
}
