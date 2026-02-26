<?php

namespace JackBayliss\DuskParallel\Tests\Unit;

use JackBayliss\DuskParallel\ParallelDriver;
use PHPUnit\Framework\TestCase;

class ParallelDriverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        unset($_ENV['TEST_TOKEN'], $_ENV['DUSK_DRIVER_URL']);
        unset($_SERVER['TEST_TOKEN'], $_SERVER['DUSK_DRIVER_URL']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['TEST_TOKEN'], $_ENV['DUSK_DRIVER_URL']);
        unset($_SERVER['TEST_TOKEN'], $_SERVER['DUSK_DRIVER_URL']);
    }

    public function test_not_running_in_parallel_by_default(): void
    {
        $this->assertFalse(ParallelDriver::runningInParallel());
    }

    public function test_running_in_parallel_when_test_token_set(): void
    {
        $_ENV['TEST_TOKEN'] = '1';

        $this->assertTrue(ParallelDriver::runningInParallel());
    }

    public function test_worker_zero_uses_default_port(): void
    {
        $_ENV['TEST_TOKEN'] = '0';

        $this->assertSame(9515, ParallelDriver::parallelDriverPort());
    }

    public function test_worker_one_uses_next_port(): void
    {
        $_ENV['TEST_TOKEN'] = '1';

        $this->assertSame(9516, ParallelDriver::parallelDriverPort());
    }

    public function test_worker_three_uses_correct_port(): void
    {
        $_ENV['TEST_TOKEN'] = '3';

        $this->assertSame(9518, ParallelDriver::parallelDriverPort());
    }

    public function test_detects_explicit_port_argument(): void
    {
        $this->assertTrue(ParallelDriver::hasExplicitPort(['--port=9999']));
        $this->assertTrue(ParallelDriver::hasExplicitPort(['--headless', '--port=1234', '--disable-gpu']));
    }

    public function test_no_explicit_port_when_absent(): void
    {
        $this->assertFalse(ParallelDriver::hasExplicitPort([]));
        $this->assertFalse(ParallelDriver::hasExplicitPort(['--headless', '--disable-gpu']));
    }

    public function test_detects_explicit_driver_url(): void
    {
        $_ENV['DUSK_DRIVER_URL'] = 'http://localhost:4444';

        $this->assertTrue(ParallelDriver::hasExplicitDriverUrl());
    }

    public function test_no_explicit_driver_url_by_default(): void
    {
        $this->assertFalse(ParallelDriver::hasExplicitDriverUrl());
    }

    public function test_no_port_injected_when_not_parallel(): void
    {
        $this->assertSame(['--headless'], ParallelDriver::resolveDriverArguments(['--headless']));
    }

    public function test_port_injected_for_worker_zero(): void
    {
        $_ENV['TEST_TOKEN'] = '0';

        $this->assertContains('--port=9515', ParallelDriver::resolveDriverArguments([]));
    }

    public function test_port_reflects_worker_token(): void
    {
        $_ENV['TEST_TOKEN'] = '2';

        $this->assertContains('--port=9517', ParallelDriver::resolveDriverArguments([]));
    }

    public function test_no_port_injected_when_explicit_port_already_provided(): void
    {
        $_ENV['TEST_TOKEN'] = '0';

        $this->assertSame(['--port=9999'], ParallelDriver::resolveDriverArguments(['--port=9999']));
    }

    public function test_no_port_injected_when_explicit_driver_url_set(): void
    {
        $_ENV['TEST_TOKEN'] = '0';
        $_ENV['DUSK_DRIVER_URL'] = 'http://selenium:4444';

        $this->assertSame([], ParallelDriver::resolveDriverArguments([]));
    }

    public function test_resolves_standard_url_when_not_parallel(): void
    {
        $this->assertSame('http://localhost:9515', ParallelDriver::resolveDriverUrl());
    }

    public function test_resolves_worker_url_when_parallel(): void
    {
        $_ENV['TEST_TOKEN'] = '1';

        $this->assertSame('http://localhost:9516', ParallelDriver::resolveDriverUrl());
    }

    public function test_explicit_driver_url_takes_precedence(): void
    {
        $_ENV['TEST_TOKEN'] = '0';
        $_ENV['DUSK_DRIVER_URL'] = 'http://selenium:4444';

        $this->assertSame('http://selenium:4444', ParallelDriver::resolveDriverUrl());
    }
}
