<?php

namespace JackBayliss\DuskParallel\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ParallelConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        unset($_ENV['TEST_TOKEN'], $_ENV['DUSK_DRIVER_URL']);

        if (! $this->portListening(9515) || ! $this->portListening(9516)) {
            $this->markTestSkipped('ChromeDriver must be running on ports 9515 and 9516.');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['TEST_TOKEN'], $_ENV['DUSK_DRIVER_URL']);
    }

    public function test_workers_open_independent_sessions_in_parallel(): void
    {
        $worker = __DIR__.'/worker.php';

        $process0 = new Process([PHP_BINARY, $worker, '0']);
        $process1 = new Process([PHP_BINARY, $worker, '1']);

        $process0->start();
        $process1->start();

        $process0->wait();
        $process1->wait();

        $this->assertSame(0, $process0->getExitCode(), $process0->getErrorOutput());
        $this->assertSame(0, $process1->getExitCode(), $process1->getErrorOutput());
        $this->assertNotSame($process0->getOutput(), $process1->getOutput());
    }

    public function test_dusk_suite_runs_in_parallel(): void
    {
        $paratest = realpath(__DIR__.'/../../vendor/bin/paratest');

        if (! $paratest) {
            $this->markTestSkipped('paratest is not installed.');
        }

        if (! $this->portListening(8000)) {
            $this->markTestSkipped('Test server must be running on port 8000.');
        }

        $process = new Process(
            [PHP_BINARY, $paratest, '-c', __DIR__.'/Fixtures/phpunit.xml', '--processes=2', '--runner=WrapperRunner'],
            realpath(__DIR__.'/../..')
        );

        $process->setTimeout(120)->run();

        $this->assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
    }

    private function portListening(int $port): bool
    {
        $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);

        if ($connection) {
            fclose($connection);

            return true;
        }

        return false;
    }
}
