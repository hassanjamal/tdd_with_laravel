<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use Tests\TestCase;
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
}
