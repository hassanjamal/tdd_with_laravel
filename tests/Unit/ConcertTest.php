<?php

namespace Tests\Unit;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\Billing\NotEnoughTicketException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;
    /** @test */
    public function can_get_formatted_date()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2019-06-01 8:00PM')
        ]);

        $this->assertEquals('June 1, 2019', $concert->formatted_date);
    }

    /** @test */
    public function can_get_formated_time()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2019-06-01 20:00:00')
        ]);

        $this->assertEquals('8:00pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_price_in_dollar()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750
        ]);
        $this->assertEquals('67.50', $concert->price_in_dollar);
    }

    /** @test */
    public function concerts_with_published_at_date_is_published()
    {
        $publishedConcertA = $concert = factory(Concert::class)->create([ 'published_at' => Carbon::parse('-1 week') ]);
        $publishedConcertB = $concert = factory(Concert::class)->create([ 'published_at' => Carbon::parse('-1 week') ]);
        $unPublishedConcert = $concert = factory(Concert::class)->create([ 'published_at' => null ]);

        $concerts = Concert::published()->get();

        $this->assertTrue($concerts->contains($publishedConcertA));
        $this->assertTrue($concerts->contains($publishedConcertB));
        $this->assertFalse($concerts->contains($unPublishedConcert));
    }

    /** @test */
    public function can_order_concert_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(15);
        $order = $concert->orderTickets('hs.jamal@gmail.com', 3);
        $this->assertEquals('hs.jamal@gmail.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    public function can_add_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(10);
        $this->assertEquals(10, $concert->remainingTickets());
    }
    /** @test */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);
        $concert->orderTickets('hs.jamal@gmail.com', 40);

        $this->assertEquals(10, $concert->remainingTickets());
    }
    /** @test */
    public function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);
        try {
            $concert->orderTickets('hs.jamal@gmail.com', 55);
         } catch (NotEnoughTicketException $e) {
             $order = $concert->orders()->whereEmail('hs.jamal@gmail.com')->first();
             $this->assertNull($order);
             $this->assertEquals(50, $concert->remainingTickets());
            return;
        }
        $this->fail('Order succeeded even though there were not enough tickets');
    }
}
