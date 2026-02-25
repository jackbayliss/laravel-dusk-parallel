<?php

namespace JackBayliss\DuskParallel\Tests\Integration\Fixtures;

use Laravel\Dusk\Browser;

class LoginTest extends DuskTestCase
{
    public function test_login_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login.html')
                    ->assertTitle('Sign in - Test App')
                    ->assertSee('Sign in');
        });
    }

    public function test_login_form_accepts_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login.html')
                    ->type('email', 'taylor@laravel.com')
                    ->type('password', 'secret')
                    ->assertInputValue('email', 'taylor@laravel.com')
                    ->assertPresent('button[type="submit"]');
        });
    }
}
