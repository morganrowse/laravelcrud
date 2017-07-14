<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeCrud extends Command
{
    protected $signature = 'make:crud';
    protected $description = 'Generate crud routing, views and controllers from a model';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        Storage::disk('views')->put('hello.blade.php',"woaw");
    }
}
