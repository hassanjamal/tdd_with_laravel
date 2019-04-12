<?php

namespace App\Http\Controllers;

use App\Concert;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Billing\PaymentGatewayException;

class ConcertOrderController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $concert = Concert::published()->findOrFail($concertId);

        $this->validate(request(), [
            'email' => 'required|email',
            'ticket_quantity' => 'required|numeric|min:1',
            'payment_token' => 'required'
        ]);
        
        try {
            $this->paymentGateway->charge(\request('ticket_quantity') * $concert->ticket_price, \request('payment_token'));
            $concert->orderTickets(\request('email'), \request('ticket_quantity'));
            return response()->json([], 200);
        } catch (PaymentGatewayException $e) {
            return response()->json([], 422);
        }
    }
}
