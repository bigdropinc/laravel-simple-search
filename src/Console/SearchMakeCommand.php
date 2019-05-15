<?php

namespace bigdropinc\LaravelSimpleSearch\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class SearchMakeCommand
 * @package bigdropinc\LaravelSimpleSearch\Console
 */
class SearchMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:search';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new search class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Search';

    public function handle()
    {
        if (parent::handle() === false) {
            return;
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/builder.stub';
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);
        $name = Str::studly(class_basename($this->argument('name')));

        return str_replace('DummySearch', $name, $stub);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Search';
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the contract.'],
        ];
    }
}
