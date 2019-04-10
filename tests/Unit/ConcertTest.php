<?php

namespace Tests\Unit;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $this->assertEquals('67.50' , $concert->price_in_dollar);
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
}
