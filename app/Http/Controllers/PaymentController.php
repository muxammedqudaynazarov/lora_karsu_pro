<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function index()
    {
        return view('payment.index');
    }

    public function store(Request $request)
    {
        $orderId = time();
        $amount = $request->amount;
        $paymentUrl = '#';

        if ($request->operator == 'click') {
            $serviceId = env('CLICK_SERVICE_ID');
            $merchantId = env('CLICK_MERCHANT_ID');
            $paymentUrl = "https://my.click.uz/services/pay?" . http_build_query([
                    'service_id' => $serviceId,
                    'merchant_id' => $merchantId,
                    'amount' => $amount,
                    'transaction_param' => $orderId,
                    'return_url' => 'http://127.0.0.1:8000/payment/' . $orderId . '/callback',
                ]);
        }
        return redirect($paymentUrl);
    }

}
