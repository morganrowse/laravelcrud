<?php

namespace App\Providers;

use App\Console\Commands\MakeCrud;
use Illuminate\Support\ServiceProvider;

class LaravelCrudServiceProvider extends ServiceProvider
{

    public function boot()
    {
        //
    }

    public function register()
    {
        $this->commands([
            MakeCrud::class,
        ]);
    }
}
