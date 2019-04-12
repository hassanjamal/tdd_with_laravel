<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGatewayException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakePaymentGatewayTest extends TestCase
{
    /** @test */
    public function charges_with_a_valid_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway;

        $paymentGateway->charge('3400', $paymentGateway->getValidTestToken());

        $this->assertEquals(3400, $paymentGateway->totalCharge());

    }

    /** @test */
    function a_charge_can_not_be_created_if_a_invalid_payment_token_is_provided()
    {
        $paymentGateway = new FakePaymentGateway;
        try {
            $paymentGateway->charge('3400', 'some-invalid-token');
        } catch (PaymentGatewayException $e) {
          return;
        }
        $this->fail();
    }
}
