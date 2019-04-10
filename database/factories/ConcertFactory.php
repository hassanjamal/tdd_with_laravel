<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Concert::class, function (Faker $faker) {
    return [
        'title' => 'The Red Fake Chord',
        'subTitle' => 'With Animosity Fake  and  FakeLethargy',
        'date' => Carbon::parse('April 28, 2019 8:00pm'),
        'ticket_price' => 3450,
        'venue' => 'The Mosh Pit Fake',
        'venue_address' => '123, Example lane Fake',
        'city' => "Hassan City Fake",
        'state' => 'Fake OST',
        'zip' => '99999',
        'additional_information' => "For ticket make a fake call 989898-9898"
    ];
});

$factory->state(App\Concert::class, 'published', function (Faker $faker){
    return [
        'published_at' => Carbon::parse('-1 week')
    ];
});
