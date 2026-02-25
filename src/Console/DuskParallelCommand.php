<?php

namespace JackBayliss\DuskParallel\Console;

use Laravel\Dusk\Console\DuskCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'dusk:parallel')]
class DuskParallelCommand extends DuskCommand
{
    protected $signature = 'dusk:parallel
                {--processes=2 : Number of parallel processes to use}';

    protected $description = 'Run the Dusk tests in parallel';

    protected function binary()
    {
        $paratestPath = base_path('vendor/bin/paratest');

        if (! file_exists($paratestPath)) {
            $this->error('paratest not found. Install it with: composer require --dev brianium/paratest');

            exit(1);
        }

        return [PHP_BINARY, $paratestPath];
    }

    protected function phpunitArguments($options)
    {
        $options = array_values(array_filter($options, function ($option) {
            return ! str_starts_with($option, '--processes=')
                && ! str_starts_with($option, '--runner=');
        }));

        $args = parent::phpunitArguments($options);

        $args[] = '--processes';
        $args[] = $this->option('processes');

        $args[] = '--runner';
        $args[] = 'WrapperRunner';

        return $args;
    }
}
