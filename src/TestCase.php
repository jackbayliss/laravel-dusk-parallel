<?php

namespace JackBayliss\DuskParallel;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\ParallelTesting;
use Laravel\Dusk\Browser as DuskBrowser;
use Laravel\Dusk\TestCase as DuskTestCase;

abstract class TestCase extends DuskTestCase
{
    /**
     * Tracks whether the per-process parallel setup has already been triggered for this worker.
     * Static so it persists across test instances within the same PHP process.
     */
    protected static bool $parallelProcessSetUp = false;

    public static function startChromeDriver(array $arguments = []): void
    {
        parent::startChromeDriver(ParallelDriver::resolveDriverArguments($arguments));
    }

    /**
     * Refresh the application and, on the first test of each paratest worker, fire the
     * Laravel parallel-testing process callbacks (e.g. creating the per-worker database).
     *
     * paratest sets TEST_TOKEN per-worker but never calls callSetUpProcessCallbacks(),
     * so we do it here — once per process, after the app is booted so DB connections work.
     * The built-in setUpTestCase callbacks then switch the default connection to the
     * per-worker database, and any mirroring callbacks (e.g. for mysql-elevated) follow.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        if (ParallelDriver::runningInParallel() && ! static::$parallelProcessSetUp) {
            ParallelTesting::callSetUpProcessCallbacks();
            static::$parallelProcessSetUp = true;
        }
    }

    protected function newBrowser($driver): DuskBrowser
    {
        return new Browser($driver);
    }

    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--headless=new',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-gpu',
        ]);

        return RemoteWebDriver::create(
            ParallelDriver::resolveDriverUrl(),
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }
}
