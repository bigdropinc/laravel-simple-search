<?php

namespace bigdropinc\LaravelSimpleSearch;

use bigdropinc\LaravelSimpleSearch\Console\SearchMakeCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class SearchServiceProvider
 * @package bigdropinc\LaravelSimpleSearch
 */
class SearchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            SearchMakeCommand::class,
        ]);
    }
}
