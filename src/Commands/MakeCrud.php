<?php

namespace MorganRowse\LaravelCrud\Commands;

use Illuminate\Console\Command;
use MorganRowse\LaravelCrud\Generator;

class MakeCrud extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'make:crud {model : The schema name} 
                                      {--F|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD from a table schema';

    /**
     * @Generator
     */
    protected $generator;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->generator = new Generator($this->argument('model'));

        if ($this->option('force')) {
            $this->line('Removing previous CRUD...');
            $this->generator->removeExistingCrud();
            $this->line('Previous CRUD removed!');
        }

        if ($this->generator->hasExistingCrud()) {
            $this->error('CRUD already exists! Use --force to overwrite existing CRUD');
            $this->line('Use --force to overwrite existing CRUD');
            return;
        }

        $this->generator->generate();
        $this->line("CRUD created successfully.");
    }
}
