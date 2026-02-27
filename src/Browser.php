<?php

namespace JackBayliss\DuskParallel;

use Laravel\Dusk\Browser as DuskBrowser;

class Browser extends DuskBrowser
{
    public function loginAs($userId, $guard = null): static
    {
        // Visit the token route first so the server receives the dusk_db_token cookie
        // and switches to the correct per-worker database before creating the session.
        $token = $_SERVER['TEST_TOKEN'] ?? $_ENV['TEST_TOKEN'] ?? null;

        if ($token !== null) {
            $this->visit('/__dusk/use-database/'.$token);
        }

        parent::loginAs($userId, $guard);

        return $this;
    }
}
