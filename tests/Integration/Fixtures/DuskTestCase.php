<?php

namespace JackBayliss\DuskParallel\Tests\Integration\Fixtures;

use JackBayliss\DuskParallel\TestCase;
use Orchestra\Testbench\Concerns\CreatesApplication;

abstract class DuskTestCase extends TestCase
{
    use CreatesApplication;

    protected function baseUrl(): string
    {
        return 'http://localhost:8000';
    }
}
