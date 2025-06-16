<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class IndexTest extends DuskTestCase
{
    /**
     * A Dusk test example.
     *
     * @return void
     */
    use DatabaseMigrations;
    public function testCurrentTime()
    {
        $user = User::factory()->create()->first();
        $this->browse(function (Browser $browser)  use ($user) {
            $browser->loginAs($user)->visit('/')
                ->pause(1000)
                ->assertSeeIn('@date-display', now()->format('Y年n月j日'))
                ->assertSeeIn('@time-display', now()->format('H:i'));
        });
    }
}
