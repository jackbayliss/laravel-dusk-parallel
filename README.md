# Laravel Dusk Parallel

Run your Laravel Dusk browser tests in parallel using [ParaTest](https://github.com/paratestphp/paratest).

> **⚠️ This package is currently in beta.** It may contain bugs and the API is subject to change.

## Why?

Laravel Dusk tests are slow as it requires a real browser, each test navigates to a page, and waits for it to render — even a modest suite of 20-30 tests can take several minutes to run sequentially.

The traditional solution is to split tests across multiple CI runners, but that means paying for additional parallel jobs. This package takes a different approach: run multiple ChromeDriver instances on the same runner, splitting your test suite across them. You get the speed of parallelism without the cost of extra CI infrastructure.

## Requirements

- PHP 8.1+
- Laravel 10+
- Laravel Dusk 7+

## Installation
```bash
composer require --dev jackbayliss/laravel-dusk-parallel
```

## Setup

### 1. Install ParaTest
```bash
composer require --dev brianium/paratest
```

### 2. Start ChromeDriver instances

You need one ChromeDriver instance per parallel process. By default, the package uses ports starting from `9515`. I recommend starting a few more instances than you think you need, as ParaTest's token assignment can be unpredictable depending on your environment:
```bash
chromedriver --port=9515 &
chromedriver --port=9516 &
chromedriver --port=9517 &
chromedriver --port=9518 &
```

### 3. Run your tests
```bash
php artisan dusk:parallel
```

By default, this runs with 2 parallel processes. You can change this:
```bash
php artisan dusk:parallel --processes=4
```

## Configuration

### Changing the base port

If port `9515` is already in use, set `DUSK_DRIVER_BASE_PORT` in your `.env`:
```env
DUSK_DRIVER_BASE_PORT=9600
```

Worker processes will then use ports `9600`, `9601`, etc. Remember to start ChromeDriver on those ports instead.

### Using a custom driver URL

If you're using a remote WebDriver such as Selenium Grid or BrowserStack, set `DUSK_DRIVER_URL` in your `.env` and the port logic will be bypassed entirely:
```env
DUSK_DRIVER_URL=http://selenium-grid:4444
```

### Customising Chrome options

If you need to customise Chrome options, extend the package's `TestCase` in your `tests/DuskTestCase.php`:
```php
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

> **Note:** Extending the package's `TestCase` is entirely optional. The package works with a standard Laravel Dusk setup out of the box.

## CI Usage

### GitHub Actions
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

ParaTest splits your test suite across multiple worker processes. Each worker receives a `TEST_TOKEN` environment variable (`0`, `1`, `2`...) which the package uses to route that worker to its own ChromeDriver instance on a unique port. This means each worker gets a completely independent browser session with no shared state between processes — all within a single CI job.

## Example project

See [dusk-parallel-demo](https://github.com/jackbayliss/dusk-parallel-demo) for a working Laravel app with passing parallel Dusk tests and a full GitHub Actions workflow.

## License

MIT