<?php

namespace App\Http\Controllers;

use App\Concert;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;

class ConcertOrderController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $ticketQuantity = \request('ticket_quantity');
        $concert = Concert::find($concertId);
        $amount = $ticketQuantity * $concert->ticket_price;
        $this->paymentGateway->charge($amount, \request('payment_token'));

        $order = $concert->orders()->create([
            'email' => \request('email')
        ]);

        foreach (range(1, $ticketQuantity) as $i) {
            $order->tickets()->create([]);
        }

        return response()->json([], 200);
    }
}
