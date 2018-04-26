<?php namespace MorganRowse\LaravelCrud\Commands;

use Illuminate\Console\Command;
use MorganRowse\LaravelCrud\BootstrapGenerator;
use MorganRowse\LaravelCrud\Generator;

class MakeCrud extends Command
{

    protected $signature = 'make:crud {model : The schema name} {preset : The preset type (none, bootstrap, vue)} {--force}';
    protected $description = 'Generate CRUD from a table schema';

    protected $stub_path, $view_path, $model_view_path, $model_path, $controller_path, $schema, $model;
    protected $generator;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        switch ($this->argument('preset')) {
            case 'none':
                $this->line('Using vanilla CRUD template...');
                $this->generator = new Generator($this->argument('model'));
                break;
            case 'bootstrap':
                $this->line('Using bootstrap CRUD template...');
                $this->generator = new BootstrapGenerator($this->argument('model'));
                break;
            case 'vue':
                $this->line('Using vue CRUD template...');
                $this->generator = new Generator($this->argument('model'));
                break;
        }


        if ($this->option('force')) {
            $this->line('Removing previous CRUD...');
            $this->generator->removeExistingCrud();
            $this->line('Previous CRUD removed!');
        }

        if ($this->generator->hasExistingCrud()) {
            $this->error('CRUD already exists!');
            return;
        } else {
            $this->generator->generate();
            $this->line("CRUD created successfully.");
        }
    }
}
