<?php

namespace JackBayliss\DuskParallel;

use Illuminate\Support\Facades\Session;
use Laravel\Dusk\Browser as DuskBrowser;

class Browser extends DuskBrowser
{
    public function loginAs($userId, $guard = null): static
    {
        // When running parallel Dusk tests, visit the token route first so the server
        // receives the dusk_db_token cookie and switches to the correct per-worker database.
        // Without this, loginAs hits the server on the wrong database and the user is not found.
        $token = $_SERVER['TEST_TOKEN'] ?? $_ENV['TEST_TOKEN'] ?? null;

        if ($token) {
            $this->visit('/__dusk/use-database/' . $token);
        }

        parent::loginAs($userId, $guard);

        return $this;
    }
}
