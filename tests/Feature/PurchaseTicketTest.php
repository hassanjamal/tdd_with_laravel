<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTicketTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp():void
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function customer_can_purchase_concert_tickets()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 6770
        ]);

        $this->post("/concerts/{$concert->id}/orders", [
            'email' => 'hs.jamal@gmail.com',
            'ticket_quantity' => 2,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ])->assertStatus(200);

        $this->assertEquals(13540, $this->paymentGateway->totalCharge());

        $order = $concert->orders()->where('email', 'hs.jamal@gmail.com')->first();

        // Make sure that order exists for this customer
        $this->assertNotNull($order);
        // we can also verify tickets counts
        $this->assertEquals(2, $order->tickets->count());
    }

    /** @test */
    public function email_is_required_to_purchase_a_ticket()
    {
        // $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function email_is_a_valid_email_address()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert, [
            'email' => 'some_random_email_address',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_a_ticket()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert, [
            'email' => 'demo@exmaple.com',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /** @test */
    public function ticket_quantity_must_be_atleast_1_to_purchase_the_ticket()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert, [
            'email' => 'demo@exmaple.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /** @test */
    public function payment_token_is_required_to_purchase_the_ticket()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTicket($concert, [
            'email' => 'demo@exmaple.com',
            'ticket_quantity' => 0,
        ]);

        $this->assertValidationError($response, 'payment_token');
    }

    /**
     * @param $concert
     * @param $params
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    private function orderTicket($concert, $params): \Illuminate\Foundation\Testing\TestResponse
    {
        $response = $this->json('POST', "/concerts/{$concert->id}/orders", $params);
        return $response;
    }

    /**
     * @param $response
     */
    private function assertValidationError($response , $field): void
    {
        $response->assertStatus(422);
        $this->assertArrayHasKey($field, (array)json_decode($response->getContent())->errors);
    }
}
