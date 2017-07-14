<?php

namespace App\Providers;

use App\Console\Commands\MakeCrud;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        $this->commands([
            MakeCrud::class,
        ]);
    }
}
