<?php

namespace MorganRowse\LaravelCrud;

use Illuminate\Support\ServiceProvider;

class LaravelCrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            Commands\MakeCrud::class,
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->offerPublishing();
    }

    /**
     * Setup the resource publishing groups for Passport.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/laravelcrud.php' => config_path('laravelcurd.php'),
            ], 'laravelcrud-config');
        }
    }
}
