<?php namespace MorganRowse\LaravelCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {model}';
    protected $description = 'Generate crud routing, views and controllers from a model';

    protected $stub_path, $view_path, $model_view_path, $controller_path, $schema, $model;

    public function __construct()
    {
        parent::__construct();
        $this->stub_path = base_path("vendor\\morganrowse\\laravelcrud\\src\\Stubs");
        $this->view_path = "resources\\views\\";
        $this->model_view_path = "";
        $this->controller_path = base_path("app\\Http\\Controllers\\");
        $this->schema = '';
        $this->model = '';

    }

    public function handle()
    {
        $this->model = $this->argument('model');

        $this->schema = DB::connection()->getDoctrineSchemaManager()->listTableColumns($this->model);

        $this->model_view_path = "resources\\views\\" . $this->model;
        $this->makeDirectory($this->model_view_path);

        $this->makeController($this->getControllerContents());
        $this->makeView('create', $this->getCreateViewContents());
        $this->makeView('edit', $this->getEditViewContents());
        $this->makeView('index', $this->getIndexViewContents());
        $this->makeView('show', $this->getShowViewContents());
    }

    public function makeDirectory($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return null;
    }

    public function makeController($contents)
    {
        file_put_contents($this->controller_path . $this->getControllerName() . '.php', $contents);

        return null;
    }

    public function getControllerName()
    {
        return ucwords(strtr($this->model, ['_' => ''])) . 'Controller';
    }

    public function getControllerContents()
    {
        return file_get_contents($this->stub_path . "\\controller.stub");
    }

    public function makeView($view_type, $contents)
    {
        file_put_contents($this->model_view_path ."\\". $view_type . '.blade.php', $contents);
    }

    public function getCreateViewContents()
    {
        return file_get_contents($this->stub_path . "\\create.stub");
    }

    public function getEditViewContents()
    {
        return file_get_contents($this->stub_path . "\\edit.stub");
    }

    public function getIndexViewContents()
    {
        $contents = file_get_contents($this->stub_path . "\\index.stub");

        $model_table_head = '';
        $model_item_table_row = '';

        $model_plural = ucfirst($this->model);
        $model_items = strtolower($this->model);
        $model_item = strtolower(str_singular($this->model));

        foreach ($this->schema as $column) {
            $model_table_head .= '<th>' . $column->getName() . '</th>';
            $model_item_table_row .= '<td>{{$' . $model_item . '->' . $column->getName() . '}}</td>';
        }

        $model_item_table_row .= '<td><a href=" {{route(\"$model.show\",' . $model_item . '->id)}} "</td>';

        $search_replace = [
            '%model_plural%' => $model_plural,
            '%model_items%' => '$' . $model_items,
            '%model_item%' => '$' . $model_item,
            '%model_table_head%' => $model_table_head,
            '%model_item_table_row%' => $model_item_table_row,
        ];

        return strtr($contents, $search_replace);
    }

    public function getShowViewContents()
    {
        return file_get_contents($this->stub_path . "\\show.stub");
    }
}
