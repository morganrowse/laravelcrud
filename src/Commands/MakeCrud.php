<?php namespace MorganRowse\LaravelCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MakeCrud extends Command
{
    protected $signature = 'make:crud {model}';
    protected $description = 'Generate crud routing, views and controllers from a model';

    protected $stub_path, $view_path, $model_view_path, $model_path, $controller_path, $schema, $model;

    public function __construct()
    {
        parent::__construct();
        $this->stub_path = base_path("vendor\\morganrowse\\laravelcrud\\src\\Stubs");
        $this->view_path = "resources\\views\\";
        $this->model_view_path = "";
        $this->model_path = base_path("app\\");
        $this->controller_path = base_path("app\\Http\\Controllers\\");
        $this->schema = '';
        $this->model = '';
    }

    public function handle()
    {
        $this->model = $this->argument('model');

        $this->schema = DB::connection()->getDoctrineSchemaManager()->listTableColumns($this->model);

        $this->model_view_path = "resources\\views\\" . strtr($this->model, ['_' => '']);
        $this->makeDirectory($this->model_view_path);

        $this->makeModel($this->getModelContents());
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

    public function makeModel($contents)
    {
        file_put_contents($this->model_path . $this->getClassName() . '.php', $contents);

        return null;
    }

    public function getModelContents()
    {
        $contents = file_get_contents($this->stub_path . "\\model.stub");

        $model_relationship_functions = '';

        foreach ($this->schema as $column) {
            if (substr($column->getName(), -3) == '_id') {
                $model_relationship_functions .= 'public function ' . lcfirst(strtr(ucwords(strtr(substr($column->getName(), 0, -3), ['_' => ' '])), [' ' => ''])) . '() { return $this->belongsTo(\'App\\' . strtr(ucwords(strtr(substr($column->getName(), 0, -3), ['_' => ' '])), [' ' => '']) . '\'); } ';
            }
        }

        $search_replace = [
            '%model_class%' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']),
            '%model_relationship_functions%' => $model_relationship_functions,
        ];

        return strtr($contents, $search_replace);
    }

    public function makeController($contents)
    {
        file_put_contents($this->controller_path . $this->getClassName() . 'Controller.php', $contents);

        return null;
    }

    public function getClassName()
    {
        return strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']);
    }

    public function getControllerContents()
    {
        $contents = file_get_contents($this->stub_path . "\\controller.stub");

        $model_fill_fields = '';

        foreach ($this->schema as $column) {
            $model_fill_fields .= '$' . str_singular($this->model) . '->' . $column->getName() . ' = $request->input("' . $column->getName() . '");';
        }

        $model_fill_fields .= '$' . str_singular($this->model) . '->save();';

        $search_replace = [
            '%model_class%' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']),
            '%model%' => $this->model,
            '%model_view_path%' => strtr($this->model, ['_' => '']),
            '%model_items%' => $this->model,
            '%model_item%' => str_singular($this->model),
            '%model_fill_fields%' => $model_fill_fields,
            '%model_destroy%' => '$' . str_singular($this->model) . '->delete();'
        ];

        return strtr($contents, $search_replace);
    }

    public function makeView($view_type, $contents)
    {
        file_put_contents($this->model_view_path . "\\" . $view_type . '.blade.php', $contents);
    }

    public function getCreateViewContents()
    {
        $contents = file_get_contents($this->stub_path . "\\create.stub");

        $model_form_fields = '';

        foreach ($this->schema as $column) {
            $model_form_fields .= '<div><label>' . strtr(ucfirst($column->getName()), ['_' => ' ']) . '</label><input name="' . $column->getName() . '" type="' . $this->doctrineToHtmlInput($column->getType()) . '"></div>';
        }

        $search_replace = [
            '%model_singular%' => strtr(str_singular($this->model), ['_' => ' ']),
            '%model_form_fields%' => $model_form_fields,
            '%model_store_route%' => strtr($this->model, ['_' => '']) . '.store',
        ];

        return strtr($contents, $search_replace);
    }

    public function getEditViewContents()
    {
        $contents = file_get_contents($this->stub_path . "\\edit.stub");

        $model_form_fields = '';

        foreach ($this->schema as $column) {
            $model_form_fields .= '<div><label>' . strtr(ucfirst($column->getName()), ['_' => ' ']) . '</label><input name="' . $column->getName() . '" type="' . $this->doctrineToHtmlInput($column->getType()) . '" value="{{$' . str_singular($this->model) . '->' . $column->getName() . '}}"></div>';
        }

        $search_replace = [
            '%model_singular%' => strtr(str_singular($this->model), ['_' => ' ']),
            '%model_item%' => str_singular($this->model),
            '%model_form_fields%' => $model_form_fields,
            '%model_update_route%' => strtr($this->model, ['_' => '']) . '.update',
        ];

        return strtr($contents, $search_replace);
    }

    public function getIndexViewContents()
    {
        $contents = file_get_contents($this->stub_path . "\\index.stub");

        $model_table_head = '';
        $model_item_table_row = '';

        $model_plural = strtr(ucfirst($this->model), ['_' => ' ']);
        $model_items = $this->model;
        $model_item = str_singular($this->model);

        foreach ($this->schema as $column) {
            $column_heading = $column->getName();

            if (substr($column->getName(), -3) == '_id') {
                $column_heading = substr($column->getName(), 0, -3);
            }
            $model_table_head .= '<th>' . strtr(ucfirst($column_heading), ['_' => ' ']) . '</th>';
            $model_item_table_row .= '<td>{{$' . $model_item . '->' . $column->getName() . '}}</td>';
        }

        $model_item_table_row .= '<td><a class="btn" href="{{route(\'' . strtr($this->model, ['_' => '']) . '.show\',$' . $model_item . '->id)}}">View</a><a class="btn" href="{{route(\'' . strtr($this->model, ['_' => '']) . '.edit\',$' . $model_item . '->id)}}">Edit</a><form method="POST" action="{{route(\'' . strtr($this->model, ['_' => '']) . '.destroy\',$' . $model_item . ')}}">{{method_field(\'DELETE\')}}{{csrf_field()}}<button class="btn" type="submit">Delete</button></form></td>';

        $search_replace = [
            '%model_plural%' => $model_plural,
            '%model_create_route%' => strtr($this->model, ['_' => '']) . '.create',
            '%model_items%' => '$' . $model_items,
            '%model_item%' => '$' . $model_item,
            '%model_table_head%' => $model_table_head,
            '%model_item_table_row%' => $model_item_table_row,
        ];

        return strtr($contents, $search_replace);
    }

    public function getShowViewContents()
    {
        $contents = file_get_contents($this->stub_path . "\\show.stub");

        $search_replace = [
            '%model_item%' => '$' . str_singular($this->model),
        ];

        return strtr($contents, $search_replace);
    }

    public function doctrineToHtmlInput($doctrine_type)
    {
        switch ($doctrine_type) {
            case 'Integer':
                return 'number';
                break;
            case 'String':
                return 'text';
                break;
            case 'DateTime':
                return 'datetime-local';
                break;
            default:
                return $doctrine_type;
                break;
        }
    }
}
