<?php

namespace Tests\Feature;

use App\Concert;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ViewConcertListingTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_not_view_unpublished_concert()
    {

        $concert = factory(Concert::class)->create([
            'published_at' => null
        ]);

        $this->get('/concerts/'.$concert->id)
            ->assertStatus(404);

    }
}
