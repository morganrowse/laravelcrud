<?php namespace MorganRowse\LaravelCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {model}';
    protected $description = 'Generate crud routing, views and controllers from a model';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $path = "resources/views/" . $this->argument('model');
        $file_type = ".blade.php";

        $this->makeDirectory($path);
        file_put_contents($path . "/create" . $file_type, $this->getViewContents("create"));
        file_put_contents($path . "/index" . $file_type, $this->getViewContents("index"));
        file_put_contents($path . "/edit" . $file_type, $this->getViewContents("edit"));
        file_put_contents($path . "/show" . $file_type, $this->getViewContents("show"));
    }

    public function getViewContents($view_type)
    {
        $path = base_path("vendor\morganrowse\laravelcrud\src\Stubs");

        $model = $this->argument('model');

        $schema = DB::connection()->getDoctrineSchemaManager()->listTableColumns($model);

        switch ($view_type) {
            case 'create':
                return file_get_contents($path . "\create.stub");
                break;
            case 'index':
                $contents = file_get_contents($path . "\index.stub");

                $model_table_head = '';
                $model_item_table_row = '';

                $model_plural = ucfirst($model);
                $model_items = strtolower($model);
                $model_item = strtolower(str_singular($model));

                foreach ($schema as $column) {
                    $model_table_head .= '<th>' . $column->getName() . '</th>';
                    $model_item_table_row .= '<td>{{$' . $model_item . '->' . $column->getName() . '}}</td>';
                }

                $model_item_table_row .='<td><a href=" {{route(\"$model.show\",'.$model_item.'->id)}} "</td>';

                $search_replace = [
                    '%model_plural%' => $model_plural,
                    '%model_items%' => '$' . $model_items,
                    '%model_item%' => '$' . $model_item,
                    '%model_table_head%' => $model_table_head,
                    '%model_item_table_row%' => $model_item_table_row,
                ];

                $contents = strtr($contents, $search_replace);

                return $contents;
                break;
            case 'edit':
                return file_get_contents($path . "\\edit.stub");
                break;
            case 'show':
                return file_get_contents($path . "\show.stub");
                break;
            default:
                return "stub not found";
                break;
        }

    }

    public function makeDirectory($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }
}
