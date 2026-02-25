<?php

namespace JackBayliss\DuskParallel\Tests\Integration\Fixtures;

use Laravel\Dusk\Browser;

class DashboardTest extends DuskTestCase
{
    public function test_dashboard_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard.html')
                ->assertTitle('Dashboard - Test App')
                ->assertSee('You are logged in.');
        });
    }

    public function test_dashboard_has_home_link(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard.html')
                ->assertSeeLink('Home');
        });
    }
}
