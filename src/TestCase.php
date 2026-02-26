<?php

namespace JackBayliss\DuskParallel;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\NoAlertOpenException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
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

    /**
     * When DUSK_DRIVER_URL is set we're using an external Selenium/WebDriver service,
     * so there's no need to launch a local ChromeDriver process.
     */
    public static function startChromeDriver(array $arguments = []): void
    {
        if (ParallelDriver::hasExplicitDriverUrl()) {
            return;
        }

        parent::startChromeDriver(ParallelDriver::resolveDriverArguments($arguments));
    }

    /**
     * Refresh the application and, when paratest is detected, wire up Laravel's parallel
     * testing machinery that paratest doesn't trigger itself.
     *
     * paratest sets TEST_TOKEN per-worker but never sets LARAVEL_PARALLEL_TESTING nor calls
     * callSetUpProcessCallbacks(). We do both here so the package works out of the box
     * without requiring anything extra in phpunit.dusk.xml.
     *
     * LARAVEL_PARALLEL_TESTING must be set before callSetUpProcessCallbacks() and before
     * callSetUpTestCaseCallbacks() (called by the parent immediately after this method),
     * because both guard themselves with inParallel() which checks that flag.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        if (ParallelDriver::runningInParallel()) {
            $_SERVER['LARAVEL_PARALLEL_TESTING'] = '1';

            if (! static::$parallelProcessSetUp) {
                ParallelTesting::callSetUpProcessCallbacks();
                static::$parallelProcessSetUp = true;
            }
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
