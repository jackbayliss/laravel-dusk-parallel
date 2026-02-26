<?php

namespace JackBayliss\DuskParallel;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser as DuskBrowser;
use Laravel\Dusk\TestCase as DuskTestCase;

abstract class TestCase extends DuskTestCase
{
    public static function startChromeDriver(array $arguments = []): void
    {
        parent::startChromeDriver(ParallelDriver::resolveDriverArguments($arguments));
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
