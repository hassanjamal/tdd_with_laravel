<?php
/**
 * Created by PhpStorm.
 * User: hassanjamal
 * Date: 11/04/19
 * Time: 12:19 AM
 */

namespace App\Billing;


class FakePaymentGateway implements PaymentGateway
{
    private $charge;

    public function __construct()
    {
        $this->charge = collect();
    }

    public function getValidTestToken()
    {
        return 'some-valid-token';
    }

    public function charge($amount, $token)
    {
        if ($token !== $this->getValidTestToken()) {
            throw new PaymentGatewayException;
        }
        $this->charge[] = $amount;
    }

    public function totalCharge()
    {
        return $this->charge->sum();
    }
}