<?php

namespace MorganRowse\LaravelCrud;

use Illuminate\Support\ServiceProvider;

class LaravelCrudServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            Commands\MakeCrud::class,
        ]);
    }
}
