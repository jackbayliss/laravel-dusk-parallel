<?php

namespace JackBayliss\DuskParallel\Tests\Integration\Fixtures;

use Laravel\Dusk\Browser;

class NavigationTest extends DuskTestCase
{
    public function test_home_page_links_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSeeLink('Sign in')
                ->clickLink('Sign in')
                ->assertSee('Sign in')
                ->assertPresent('#email');
        });
    }

    public function test_dashboard_links_back_to_home(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard.html')
                ->clickLink('Home')
                ->assertSee('Welcome to Test App');
        });
    }
}
