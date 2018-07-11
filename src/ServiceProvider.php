<?php

namespace MorganRowse\LaravelCrud;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->commands([
            Commands\MakeCrud::class,
        ]);
    }
}
