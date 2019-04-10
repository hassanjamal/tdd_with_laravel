<?php

namespace Tests\Feature;

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTicketTest extends TestCase
{
    use DatabaseMigrations;
    /** @test */
    public function customer_can_purchase_concert_tickets()
    {

        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 6770
        ]);

        // Act
        // Purchase the ticket
        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'hs.jamal@gmail.com',
            'ticket_quantity' => 2,
            'payment_token' => $paymentGateway->getValidTestToken()
        ]);

        // Assert
        // Make sure the customer was charged with correct amount
        $this->assertEquals(13540 , $paymentGateway->totalCharge());

        $order = $concert->orders()->where('email' , 'hs.jamal@gmail.com')->first();

        // Make sure that order exists for this customer
        $this->assertNotNull($order);
        // we can also verify tickets counts
        $this->assertEquals(2 , $order->tickets->counts());
    }
}
