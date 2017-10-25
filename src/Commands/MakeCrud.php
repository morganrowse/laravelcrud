<?php namespace MorganRowse\LaravelCrud\Commands;

use Illuminate\Console\Command;
use MorganRowse\LaravelCrud\Generator;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {model}';
    protected $description = 'Generate CRUD views and controllers from a model';

    protected $stub_path, $view_path, $model_view_path, $model_path, $controller_path, $schema, $model;
    protected $generator;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->generator = new Generator($this->argument('model'));

        if($this->generator->hasExistingCrud()){
            $this->error('CRUD already exists!');
            return;
        } else {
            $this->generator->generate();
            $this->line("CRUD created successfully.");
        }
    }
}
