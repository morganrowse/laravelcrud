<?php

namespace MorganRowse\LaravelCrud;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\DetectsApplicationNamespace;

class Generator
{
    use DetectsApplicationNamespace;

    protected $model, $controller_stub_path, $view_controller_stub_path, $model_stub_path, $request_stub_path, $resource_stub_path, $view_stub_path, $controller_path, $view_controller_path, $model_path, $request_path, $resource_path, $view_path, $ignored_fields, $files, $indent_count, $schema;

    public function __construct($model)
    {
        $this->model = $model;

        //set paths for stubbed resources
        $this->controller_stub_path = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Controllers/');
        $this->view_controller_stub_path = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Controllers/View/');
        $this->model_stub_path = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Models/');
        $this->request_stub_path = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Requests/');
        $this->resource_stub_path = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Resources/');
        $this->view_stub_path = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Views/');

        //set paths for generated resources
        $this->controller_path = app_path('Http/Controllers/');
        $this->view_controller_path = app_path('Http/Controllers/View/');
        $this->model_path = app_path() . '/';
        $this->request_path = app_path('Http/Requests/');
        $this->resource_path = app_path('Http/Resources/');
        $this->view_path = resource_path('views/' . strtr($this->model, ['_' => '']) . '/');

        //set ignored database columns
        $this->ignored_fields = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at'
        ];

        //set paths for all generated resources
        $this->files = [
            $this->view_path . 'create.blade.php',
            $this->view_path . 'edit.blade.php',
            $this->view_path . 'index.blade.php',
            $this->view_path . 'show.blade.php',
            $this->controller_path . $this->getClassName() . 'Controller.php',
            $this->view_controller_path . $this->getClassName() . 'Controller.php',
            $this->model_path . $this->getClassName() . '.php',
            $this->resource_path . $this->getClassName() . 'Resource.php',
            $this->request_path . 'Destroy' . $this->getClassName() . '.php',
            $this->request_path . 'Store' . $this->getClassName() . '.php',
            $this->request_path . 'Update' . $this->getClassName() . '.php'
        ];

        //set number of spaces for indentation
        $this->indent_count = 4;
    }

    public function generate()
    {
        $this->schema = DB::connection()->getDoctrineSchemaManager()->listTableColumns($this->model);

        $this->makeModel($this->getModelContents());

        $this->makeDirectory($this->resource_path);
        $this->makeResource($this->getResourceContents());

        $this->makeDirectory($this->request_path);
        $this->makeRequest('Destroy', $this->getDestroyRequestContents());
        $this->makeRequest('Store', $this->getStoreRequestContents());
        $this->makeRequest('Update', $this->getUpdateRequestContents());

        $this->makeDirectory($this->controller_path);
        $this->makeController($this->getControllerContents());

        $this->makeDirectory($this->view_controller_path);
        $this->makeViewController($this->getViewControllerContents());

        $this->makeDirectory($this->view_path);
        $this->makeView('create', $this->getCreateViewContents());
        $this->makeView('edit', $this->getEditViewContents());
        $this->makeView('index', $this->getIndexViewContents());
        $this->makeView('show', $this->getShowViewContents());
    }

    public function insertTab($count = 1)
    {
        return str_repeat(str_repeat(' ', $this->indent_count), $count);
    }

    public function hasExistingCrud()
    {
        foreach ($this->files as $file) {
            if (file_exists($file)) {
                return true;
            }
        }

        return false;
    }

    public function removeExistingCrud()
    {
        foreach ($this->files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function isIgnoredField($field)
    {
        return (array_search($field, $this->ignored_fields) !== FALSE);
    }

    public function isRelationField($field)
    {
        return (substr($field, -3) == '_id');
    }

    public function getRelationFieldName($field)
    {
        return ($this->isRelationField($field) ? substr($field, 0, -3) : $field);
    }

    public function getClassName()
    {
        return strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']);
    }

    public function getFormElement($column, $old = false)
    {
        return '<div class="form-group row">' . PHP_EOL . $this->insertTab(3) . '<label class="col-md-4 col-form-label text-md-right">' . strtr(ucfirst($this->getRelationFieldName($column->getName())), ['_' => ' ']) . '</label>' . PHP_EOL . $this->insertTab(3) . '<div class="col-md-6"><input name="' . $column->getName() . '" type="' . $this->doctrineToHtmlInput($column->getType()) . '"' . (($old) ? ' value="{{$' . str_singular($this->model) . '->' . $column->getName() . '}}"' : '') . ' class="form-control">' . PHP_EOL . $this->insertTab(2) . '</div>' . PHP_EOL . '</div>';
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
        $contents = file_get_contents($this->model_stub_path . 'model.stub');

        $model_fillable_fields = '';
        $model_rule_fields = '';
        $model_relationship_functions = '';

        foreach ($this->schema as $column) {
            if (!$this->isIgnoredField($column->getName())) {
                if ($model_fillable_fields != '') {
                    $model_fillable_fields .= ',' . PHP_EOL . $this->insertTab(2);
                }
                $model_fillable_fields .= '\'' . $column->getName() . '\'';
                if ($model_rule_fields != '') {
                    $model_rule_fields .= ',' . PHP_EOL . $this->insertTab(2);
                }
                $model_rule_fields .= '\'' . $column->getName() . '\' => \'\'';
            }

            if ($this->isRelationField($column->getName())) {

                $model_relationship_functions .= 'public function ' . lcfirst(strtr(ucwords(strtr($this->getRelationFieldName($column->getName()), ['_' => ' '])), [' ' => ''])) . '()' . PHP_EOL . $this->insertTab() . '{' . PHP_EOL . $this->insertTab(2) . 'return $this->belongsTo(' . strtr(str_singular(ucwords(strtr(substr($column->getName(), 0, -3), ['_' => ' ']))), [' ' => '']) . '::class);' . PHP_EOL . $this->insertTab() . '} ';
            }
        }

        $search_replace = [
            '%namespace%' => rtrim($this->getAppNamespace(), '\\'),
            '%model_class%' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']),
            '%model_fillable_fields%' => $model_fillable_fields,
            '%model_rule_fields%' => $model_rule_fields,
            '%model_relationship_functions%' => $model_relationship_functions
        ];

        return strtr($contents, $search_replace);
    }

    public function makeResource($contents)
    {
        file_put_contents($this->resource_path . $this->getClassName() . 'Resource.php', $contents);

        return null;
    }

    public function getResourceContents()
    {
        $contents = file_get_contents($this->resource_stub_path . 'resource.stub');

        $search_replace = [
            '%namespace%' => rtrim($this->getAppNamespace(), '\\'),
            '%model_class%' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => ''])
        ];

        return strtr($contents, $search_replace);
    }

    public function makeRequest($request_type, $contents)
    {
        file_put_contents($this->request_path . '/' . $request_type . $this->getClassName() . '.php', $contents);
    }

    public function getDestroyRequestContents()
    {
        $contents = file_get_contents($this->request_stub_path . 'destroy_request.stub');

        $search_replace = [
            '%namespace%' => $this->getAppNamespace(),
            '%model_class%' => $this->getClassName(),
        ];

        return strtr($contents, $search_replace);
    }

    public function getStoreRequestContents()
    {
        $contents = file_get_contents($this->request_stub_path . 'store_request.stub');

        $search_replace = [
            '%namespace%' => $this->getAppNamespace(),
            '%model_class%' => $this->getClassName()
        ];

        return strtr($contents, $search_replace);
    }

    public function getUpdateRequestContents()
    {
        $contents = file_get_contents($this->request_stub_path . 'update_request.stub');

        $search_replace = [
            '%namespace%' => $this->getAppNamespace(),
            '%model_class%' => $this->getClassName()
        ];

        return strtr($contents, $search_replace);
    }

    public function makeController($contents)
    {
        file_put_contents($this->controller_path . $this->getClassName() . 'Controller.php', $contents);

        return null;
    }

    public function getControllerContents()
    {
        $contents = file_get_contents($this->controller_stub_path . 'controller.stub');

        $search_replace = [
            '%namespace%' => $this->getAppNamespace(),
            '%model_class%' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']),
            '%view_path%' => strtr($this->model, ['_' => '']),
            '%model_items%' => $this->model,
            '%model_item%' => str_singular($this->model)
        ];

        return strtr($contents, $search_replace);
    }

    public function makeViewController($contents)
    {
        file_put_contents($this->view_controller_path . $this->getClassName() . 'Controller.php', $contents);

        return null;
    }

    public function getViewControllerContents()
    {
        $contents = file_get_contents($this->view_controller_stub_path . 'controller.stub');

        $search_replace = [
            '%namespace%' => $this->getAppNamespace(),
            '%model_class%' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']),
            '%view_path%' => strtr($this->model, ['_' => '']),
            '%model_items%' => $this->model,
            '%model_item%' => str_singular($this->model)
        ];

        return strtr($contents, $search_replace);
    }

    public function makeView($view_type, $contents)
    {
        file_put_contents($this->view_path . '/' . $view_type . '.blade.php', $contents);
    }

    public function getCreateViewContents()
    {
        $contents = file_get_contents($this->view_stub_path . 'create.stub');

        $model_form_fields = '';

        foreach ($this->schema as $column) {
            if (!$this->isIgnoredField($column->getName())) {
                if ($model_form_fields != '') {
                    $model_form_fields .= PHP_EOL . $this->insertTab(2);
                }
                $model_form_fields .= $this->getFormElement($column);
            }
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
        $contents = file_get_contents($this->view_stub_path . 'edit.stub');

        $model_form_fields = '';

        foreach ($this->schema as $column) {
            if (!$this->isIgnoredField($column->getName())) {
                if ($model_form_fields != '') {
                    $model_form_fields .= PHP_EOL . $this->insertTab(2);
                }
                $model_form_fields .= $this->getFormElement($column, true);
            }
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
        $contents = file_get_contents($this->view_stub_path . 'index.stub');

        $model_table_head = '';
        $model_item_table_row = '';

        $model_plural = strtr(ucfirst($this->model), ['_' => ' ']);
        $model_items = $this->model;
        $model_item = str_singular($this->model);

        foreach ($this->schema as $column) {
            $column_heading = $column->getName();

            if ($model_item_table_row != '') {
                $model_item_table_row .= PHP_EOL . $this->insertTab(10);
            }

            if (in_array($column->getName(), ['created_at', 'updated_at', 'deleted_at'])) {
                $model_item_table_row .= '<td>{{$' . $model_item . '->' . $column->getName() . '->diffForHumans()}}</td>';

                $column_heading = substr($column->getName(), 0, -3);
            } else {
                $model_item_table_row .= '<td>{{$' . $model_item . '->' . $column->getName() . '}}</td>';

                if (substr($column->getName(), -3) == '_id') {
                    $column_heading = substr($column->getName(), 0, -3);
                }
            }

            if ($model_table_head != '') {
                $model_table_head .= PHP_EOL . $this->insertTab(9);
            }

            if($column->getName()=='id'){
                $model_table_head .= '<th>#</th>';
            } else {
                $model_table_head .= '<th>' . strtr(ucfirst($column_heading), ['_' => ' ']) . '</th>';
            }
        }

        $model_item_table_row .= PHP_EOL . $this->insertTab(10) . '<td>' . PHP_EOL . $this->insertTab(11) . '<a class="btn" href="{{route(\'' . strtr($this->model, ['_' => '']) . '.show\',$' . $model_item . '->id)}}">View</a>' . PHP_EOL . $this->insertTab(11) . '<a class="btn" href="{{route(\'' . strtr($this->model, ['_' => '']) . '.edit\',$' . $model_item . '->id)}}">Edit</a>' . PHP_EOL . $this->insertTab(11) . '<a class="btn" href="#" onclick="event.preventDefault(); document.getElementById(\'delete_' . $this->model . '_form-{{$' . $model_item . '->id}}\').submit();">Delete</a>' . PHP_EOL . $this->insertTab(11) . '<form id="delete_' . $this->model . '_form-{{$' . $model_item . '->id}}" method="POST" action="{{route(\'' . strtr($this->model, ['_' => '']) . '.destroy\',$' . $model_item . ')}}">' . PHP_EOL . $this->insertTab(12) . '{{method_field(\'DELETE\')}}' . PHP_EOL . $this->insertTab(12) . '{{csrf_field()}}' . PHP_EOL . $this->insertTab(11) . '</form>' . PHP_EOL . $this->insertTab(10) . '</td>';

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
        $contents = file_get_contents($this->view_stub_path . 'show.stub');

        $model_fields = '';

        foreach ($this->schema as $column) {
            $model_fields .= '<dt>' . strtr(ucfirst($column->getName()), ['_' => ' ']) . '</dt><dd>{{$' . str_singular($this->model) . '->' . $column->getName() . '}}</dd>';
        }

        $search_replace = [
            '%model_singular%' => ucfirst(strtr(str_singular($this->model), ['_' => ' '])),
            '%model_item%' => '$' . str_singular($this->model),
            '%model_fields%' => $model_fields
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