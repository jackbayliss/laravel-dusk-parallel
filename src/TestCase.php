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
     * Whether the per-process parallel setup has already run for this worker.
     * Static so it persists across test instances within the same PHP process.
     */
    protected static bool $parallelProcessSetUp = false;

    /**
     * Skip launching a local ChromeDriver when using an external WebDriver service.
     */
    public static function startChromeDriver(array $arguments = []): void
    {
        if (ParallelDriver::hasExplicitDriverUrl()) {
            return;
        }

        parent::startChromeDriver(ParallelDriver::resolveDriverArguments($arguments));
    }

    /**
     * Refresh the application and wire up Laravel's parallel testing machinery.
     *
     * ParaTest sets TEST_TOKEN per worker but never sets LARAVEL_PARALLEL_TESTING
     * nor calls callSetUpProcessCallbacks(). We handle both here so the package
     * works without requiring extra phpunit.dusk.xml configuration.
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
