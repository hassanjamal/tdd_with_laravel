<?php

namespace Tests\Feature;

use App\Concert;
use Tests\TestCase;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
    public function customer_can_purchase_published_concert_tickets()
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
    public function no_order_is_created_for_an_unpublished_concert()
    {
        // $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->states('unpublished')->create();
        $response = $this->post("/concerts/{$concert->id}/orders", [
            'email' => 'hs.jamal@gmail.com',
            'ticket_quantity' => 2,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharge());
    }

    /** @test */
    public function email_is_required_to_purchase_a_ticket()
    {
        // $this->withoutExceptionHandling();
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTicket($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'email');
    }

    /** @test */
    public function email_is_a_valid_email_address()
    {
        $concert = factory(Concert::class)->states('published')->create();

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
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTicket($concert, [
            'email' => 'demo@exmaple.com',
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /** @test */
    public function ticket_quantity_must_be_atleast_1_to_purchase_the_ticket()
    {
        $concert = factory(Concert::class)->states('published')->create();

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
        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTicket($concert, [
            'email' => 'demo@exmaple.com',
            'ticket_quantity' => 0,
        ]);

        $this->assertValidationError($response, 'payment_token');
    }

    /** @test */
    public function an_order_is_not_created_if_payment_token_is_invalid()
    {

        $concert = factory(Concert::class)->states('published')->create();

        $response = $this->orderTicket($concert, [
            'email' => 'demo@exmaple.com',
            'ticket_quantity' => 1,
            'payment_token' => 'some-invalid-token'
        ]);

        $response->assertStatus(422);
        $order = $concert->orders()->where('email', 'demo@example.com')->first();
        $this->assertNull($order);
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
    private function assertValidationError($response, $field): void
    {
        $response->assertStatus(422);
        $this->assertArrayHasKey($field, (array)json_decode($response->getContent())->errors);
    }
}
