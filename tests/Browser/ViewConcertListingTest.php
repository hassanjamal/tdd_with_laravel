<?php

namespace Tests\Browser;

use App\Concert;
use Carbon\Carbon;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListingTest extends DuskTestCase
{
    use DatabaseMigrations;
    /**
     * A Dusk test example.
     *
     * @return void
     */
    public function testUserCanViewAPublishedConcert()
    {
        $concert = factory(Concert::class)->states('published')->create([
            'title' => 'The Red Chord',
            'subTitle' => 'With Animosity and Lethargy',
            'date' => Carbon::parse('April 21, 2019 8:00pm'),
            'ticket_price' => 3550,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123, Example lane',
            'city' => "Hassan City",
            'state' => 'OST',
            'zip' => '45453',
            'additional_information' => "For ticket call 989898-9898"
        ]);

        $this->browse(function (Browser $browser) use ($concert) {
            $browser
                ->visit('concerts/' . $concert->id)
                ->assertSee('The Red Chord')
                ->assertSee('With Animosity and Lethargy')
                ->assertSee('April 21, 2019')
                ->assertSee('8:00pm')
                ->assertSee('35.50')
                ->assertSee('The Mosh Pit')
                ->assertSee('123, Example lane')
                ->assertSee("Hassan City, OST, 45453")
                ->assertSee('For ticket call 989898-9898');
        });
    }
}