<?php

namespace JackBayliss\DuskParallel;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as DuskTestCase;

abstract class TestCase extends DuskTestCase
{
    public static function startChromeDriver(array $arguments = []): void
    {
        parent::startChromeDriver(ParallelDriver::resolveDriverArguments($arguments));
    }

    protected function driver(): RemoteWebDriver
    {
        return RemoteWebDriver::create(
            ParallelDriver::resolveDriverUrl(),
            DesiredCapabilities::chrome()
        );
    }
}
