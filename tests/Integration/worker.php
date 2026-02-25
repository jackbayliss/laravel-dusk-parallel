<?php

require __DIR__.'/../../vendor/autoload.php';

$token = (int) $argv[1];

$_ENV['TEST_TOKEN'] = (string) $token;

$options = (new Facebook\WebDriver\Chrome\ChromeOptions)
    ->addArguments(['--headless=new', '--no-sandbox', '--disable-gpu']);

$capabilities = Facebook\WebDriver\Remote\DesiredCapabilities::chrome()
    ->setCapability(Facebook\WebDriver\Chrome\ChromeOptions::CAPABILITY, $options);

$driver = Facebook\WebDriver\Remote\RemoteWebDriver::create(
    JackBayliss\DuskParallel\ParallelDriver::resolveDriverUrl(),
    $capabilities
);

$driver->get('about:blank');
$driver->executeScript('document.body.innerHTML = "<h1 id=\'w\'>Worker '.$token.'</h1>"');

$text = $driver->findElement(Facebook\WebDriver\WebDriverBy::id('w'))->getText();

$driver->quit();

if ($text !== 'Worker '.$token) {
    fwrite(STDERR, "Expected 'Worker {$token}', got '{$text}'");
    exit(1);
}

echo $driver->getSessionID();
