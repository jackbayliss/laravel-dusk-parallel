<?php

namespace JackBayliss\DuskParallel\Console;

use Laravel\Dusk\Console\DuskCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'dusk:parallel')]
class DuskParallelCommand extends DuskCommand
{
    protected $description = 'Run the Dusk tests in parallel';

    protected function configure(): void
    {
        parent::configure();

        $this->setName('dusk:parallel');
        $this->addOption('processes', null, InputOption::VALUE_REQUIRED, 'Number of parallel processes to use', 2);
        $this->addOption('stop-on-failure', null, InputOption::VALUE_NONE, 'Stop execution upon first test failure');
        $this->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop execution upon first test error');
        $this->addOption('functional', 'f', InputOption::VALUE_NONE, 'Run each test method in a separate process');
    }

    protected function binary(): array
    {
        $paratestPath = base_path('vendor/bin/paratest');

        if (! file_exists($paratestPath)) {
            $this->error('paratest not found. Install it with: composer require --dev brianium/paratest');

            exit(1);
        }

        return [PHP_BINARY, $paratestPath];
    }

    protected function phpunitArguments($options): array
    {
        $args = array_values(array_filter(
            parent::phpunitArguments($options),
            fn ($arg) => ! str_starts_with($arg, '--no-output')
                && ! str_starts_with($arg, '--processes=')
                && ! str_starts_with($arg, '--runner=')
                && $arg !== '--stop-on-failure'
                && $arg !== '--stop-on-error'
                && $arg !== '--functional'
                && $arg !== '-f',
        ));

        $args[] = '--processes';
        $args[] = $this->option('processes');
        $args[] = '--runner';
        $args[] = 'WrapperRunner';

        if ($this->option('stop-on-failure')) {
            $args[] = '--stop-on-failure';
        }

        if ($this->option('stop-on-error')) {
            $args[] = '--stop-on-error';
        }

        if ($this->option('functional')) {
            $args[] = '--functional';
        }

        return $args;
    }
}
