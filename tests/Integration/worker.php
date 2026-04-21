<?php

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use JackBayliss\DuskParallel\ParallelDriver;

require __DIR__.'/../../vendor/autoload.php';

$token = (int) $argv[1];

$_ENV['TEST_TOKEN'] = (string) $token;

$options = (new ChromeOptions)
    ->addArguments(['--headless=new', '--no-sandbox', '--disable-gpu']);

$capabilities = DesiredCapabilities::chrome()
    ->setCapability(ChromeOptions::CAPABILITY, $options);

$driver = RemoteWebDriver::create(
    ParallelDriver::resolveDriverUrl(),
    $capabilities
);

$driver->get('about:blank');
$driver->executeScript('document.body.innerHTML = "<h1 id=\'w\'>Worker '.$token.'</h1>"');

$text = $driver->findElement(WebDriverBy::id('w'))->getText();

$driver->quit();

if ($text !== 'Worker '.$token) {
    fwrite(STDERR, "Expected 'Worker {$token}', got '{$text}'");
    exit(1);
}

echo $driver->getSessionID();
