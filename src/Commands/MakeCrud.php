<?php

namespace MorganRowse\LaravelCrud\Commands;

use Illuminate\Console\Command;
use MorganRowse\LaravelCrud\Generator;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {model : The schema name} {--force}';
    protected $description = 'Generate CRUD from a table schema';

    protected $generator;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->generator = new Generator($this->argument('model'));

        if ($this->option('force')) {
            $this->line('Removing previous CRUD...');
            $this->generator->removeExistingCrud();
            $this->line('Previous CRUD removed!');
        }

        if ($this->generator->hasExistingCrud()) {
            $this->error('CRUD already exists!');
            $this->line('Use --force to overwrite existing CRUD');
            return;
        } else {
            $this->generator->generate();
            $this->line("CRUD created successfully.");
        }
    }
}
