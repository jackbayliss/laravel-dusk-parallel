# Laravel Dusk Parallel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jackbayliss/laravel-dusk-parallel.svg?style=flat-square)](https://packagist.org/packages/jackbayliss/laravel-dusk-parallel)
[![PHP Version](https://img.shields.io/packagist/php-v/jackbayliss/laravel-dusk-parallel.svg?style=flat-square)](composer.json)
[![License](https://img.shields.io/packagist/l/jackbayliss/laravel-dusk-parallel.svg?style=flat-square)](LICENSE)

Run your [Laravel Dusk](https://laravel.com/docs/dusk) browser tests in parallel — multiple ChromeDriver instances, one CI runner, faster feedback.

> **This package is currently in beta.** It may not work, so give it a try and create an issue if something is broken - a failing test would be fantastic!
---

## Why parallel Dusk tests?

Dusk tests are slow by nature: each test launches a real browser, navigates to a page, and waits for it to render. A suite of 20–30 tests can easily take several minutes when run sequentially.

The common workaround is splitting tests across multiple CI runners — but that means paying for extra parallel jobs. This package takes a different approach: it runs multiple ChromeDriver instances on the **same** runner, splitting your test suite across them automatically. You get the speed of parallelism without the added infrastructure cost.

## Requirements

- PHP 8.1+
- Laravel 10+
- Laravel Dusk 8+
- [ParaTest](https://github.com/paratestphp/paratest) (`brianium/paratest`)

## Installation

```bash
composer require --dev jackbayliss/laravel-dusk-parallel
composer require --dev brianium/paratest
```

## Setup

### 1. Start ChromeDriver instances

You need one ChromeDriver instance per parallel process. By default the package assigns ports starting from `9515`. Start a few more than you intend to use, as ParaTest's token assignment can vary by environment:

```bash
chromedriver --port=9515 &
chromedriver --port=9516 &
chromedriver --port=9517 &
chromedriver --port=9518 &
```

### 2. Run your tests

```bash
php artisan dusk:parallel
```

By default this uses 2 parallel processes. Pass `--processes` to change it:

```bash
php artisan dusk:parallel --processes=4
```

## Configuration

### Changing the base port

If port `9515` is already in use, set `DUSK_DRIVER_BASE_PORT` in your `.env`:

```env
DUSK_DRIVER_BASE_PORT=9600
```

Worker processes will use ports `9600`, `9601`, etc. Remember to start ChromeDriver on those ports.

### Using a remote WebDriver

For Selenium Grid, BrowserStack, or any other remote WebDriver, set `DUSK_DRIVER_URL` and the port logic is bypassed entirely:

```env
DUSK_DRIVER_URL=http://selenium-grid:4444
```

### Customising Chrome options

Extend the package's `TestCase` in your `tests/DuskTestCase.php` and override the `driver()` method:

```php
use JackBayliss\DuskParallel\ParallelDriver;
use JackBayliss\DuskParallel\TestCase as ParallelTestCase;

abstract class DuskTestCase extends ParallelTestCase
{
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--headless=new',
            '--no-sandbox',
            '--disable-dev-shm-usage',
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
```

> Extending the package's `TestCase` is optional — it works with a standard Laravel Dusk setup out of the box.

## CI — GitHub Actions

```yaml
- name: Start ChromeDriver instances
  run: |
    chromedriver --port=9515 &
    chromedriver --port=9516 &
    chromedriver --port=9517 &
    chromedriver --port=9518 &
    sleep 2

- name: Run Dusk tests
  run: php artisan dusk:parallel --processes=2
```

All ChromeDriver instances run on the same runner, so there is no additional CI cost compared to running Dusk sequentially.

## How it works

ParaTest splits your test suite across multiple worker processes. Each worker receives a `TEST_TOKEN` environment variable (`0`, `1`, `2` …) which the package uses to:

1. Route that worker's ChromeDriver to a unique port (`basePort + TEST_TOKEN`).
2. Set a `dusk_db_token` cookie on the browser so every HTTP request is handled by the correct per-worker test database.

Each worker gets a fully independent browser session and database — all within a single CI job.

## Available commands

| Script | Description |
|---|---|
| `composer test` | Run the test suite |
| `composer lint` | Fix code style with Pint |
| `composer analyse` | Static analysis with PHPStan |

## Example project

See [dusk-parallel-demo](https://github.com/jackbayliss/dusk-parallel-demo) for a working Laravel application with passing parallel Dusk tests and a complete GitHub Actions workflow.

## License

MIT
