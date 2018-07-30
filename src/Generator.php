<?php

namespace MorganRowse\LaravelCrud;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\DetectsApplicationNamespace;

class Generator
{
    use DetectsApplicationNamespace;

    protected $model;
    protected $stubPath;
    protected $crudPath;
    protected $componentPath;
    protected $ignoredFields;
    protected $files;
    protected $indentCount;
    protected $schema;

    public function __construct($model)
    {
        $this->model = $model;

        $this->stubPath = new StubPath; //set paths for stubbed resources
        $this->crudPath = new CrudPath($model); //set paths for generated resources

        //set ignored database columns
        $this->ignoredFields = [
            'id',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        //set paths for all generated resources
        $this->files = [
            $this->viewPath . 'create.blade.php',
            $this->viewPath . 'edit.blade.php',
            $this->viewPath . 'index.blade.php',
            $this->viewPath . 'show.blade.php',
            $this->controllerPath . $this->getClassName() . 'Controller.php',
            $this->viewControllerPath . $this->getClassName() . 'Controller.php',
            $this->modelPath . $this->getClassName() . '.php',
            $this->resourcePath . $this->getClassName() . 'Resource.php',
            $this->requestPath . 'Destroy' . $this->getClassName() . '.php',
            $this->requestPath . 'Store' . $this->getClassName() . '.php',
            $this->requestPath . 'Update' . $this->getClassName() . '.php',
        ];

        //set number of spaces for indentation
        $this->indentCount = config('laravelcrud.indentCount', 4);
    }

    public function generate()
    {
        $this->schema = DB::connection()->getDoctrineSchemaManager()->listTableColumns($this->model);

        if ($this->tableExists()) {
            echo 'Table: ' . $this->model . ' does not exist.';
            return;
        }

        //dd($this->schema);

        $this->makeModel($this->getModelContents());

        $this->makeDirectory($this->crudPath->resource);
        $this->makeResource($this->getResourceContents());

        $this->makeDirectory($this->crudPath->request);
        $this->makeRequest('Destroy', $this->getDestroyRequestContents());
        $this->makeRequest('Store', $this->getStoreRequestContents());
        $this->makeRequest('Update', $this->getUpdateRequestContents());

        $this->makeDirectory($this->crudPath->controller);
        $this->makeController($this->getControllerContents());

        $this->makeDirectory($this->crudPath->viewController);
        $this->makeViewController($this->getViewControllerContents());

        $this->makeDirectory($this->viewPath);
        $this->makeView('create', $this->getCreateViewContents());
        $this->makeView('edit', $this->getEditViewContents());
        $this->makeView('index', $this->getIndexViewContents());
        $this->makeView('show', $this->getShowViewContents());

        $this->makeDirectory($this->crudPath->component);
        $this->copyComponents();
    }

    public function tableExists()
    {
        return empty($this->schema);
    }

    public function insertTab($count = 1)
    {
        return str_repeat(str_repeat(' ', $this->indentCount), $count);
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
        return (array_search($field, $this->ignoredFields) !== false);
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
        $name = $column->getName();
        $label = strtr(ucfirst($this->getRelationFieldName($column->getName())), ['_' => ' ']);
        $type = $this->doctrineToHtmlInput($column->getType());
        $value = (($old) ? ',\'value\'=>$' . str_singular($this->model) . '->' . $column->getName() : '');

        return '@component(\'components.input\',[\'name\'=>\'' . $name . '\',\'label\'=>\'' . $label . '\',\'type\'=>\'' . $type . '\'' . $value . ']) @endcomponent';
    }

    public function getModelRelationFunction($column)
    {
        return '/**' . PHP_EOL . $this->insertTab() . ' * @return \Illuminate\Database\Eloquent\Relations\BelongsTo' . PHP_EOL . $this->insertTab() . ' */' . PHP_EOL . $this->insertTab() . 'public function ' . lcfirst(strtr(ucwords(strtr($this->getRelationFieldName($column->getName()), ['_' => ' '])), [' ' => ''])) . '()' . PHP_EOL . $this->insertTab() . '{' . PHP_EOL . $this->insertTab(2) . 'return $this->belongsTo(' . strtr(str_singular(ucwords(strtr(substr($column->getName(), 0, -3), ['_' => ' ']))), [' ' => '']) . '::class);' . PHP_EOL . $this->insertTab() . '}';
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
        file_put_contents($this->crudPath->model . $this->getClassName() . '.php', $contents);

        return null;
    }

    public function getModelContents()
    {
        $contents = file_get_contents($this->stubPath->model . 'model.stub');

        $fillableFields = '';
        $ruleFields = '';
        $relationshipFunctions = '';

        foreach ($this->schema as $column) {
            if (!$this->isIgnoredField($column->getName())) {
                if ($fillableFields != '') {
                    $fillableFields .= ',' . PHP_EOL . $this->insertTab(2);
                }
                $fillableFields .= '\'' . $column->getName() . '\'';
                if ($ruleFields != '') {
                    $ruleFields .= ',' . PHP_EOL . $this->insertTab(2);
                }
                $ruleFields .= '\'' . $column->getName() . '\' => \'' . $this->getFieldRules($column) . '\'';
            }

            if ($this->isRelationField($column->getName())) {
                if ($relationshipFunctions != '') {
                    $relationshipFunctions .= PHP_EOL . PHP_EOL . $this->insertTab();
                }

                $relationshipFunctions .= $this->getModelRelationFunction($column);
            }
        }

        $searchReplace = [
            '{{namespace}}' => rtrim($this->getAppNamespace(), '\\'),
            '{{model_class}}' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']),
            '{{fillable_fields}}' => $fillableFields,
            '{{rule_fields}}' => $ruleFields,
            '{{relationship_functions}}' => $relationshipFunctions,
        ];

        return strtr($contents, $searchReplace);
    }

    public function makeResource($contents)
    {
        file_put_contents($this->crudPath->resource . $this->getClassName() . 'Resource.php', $contents);

        return null;
    }

    public function getResourceContents()
    {
        $contents = file_get_contents($this->stubPath->resource . 'resource.stub');

        $searchReplace = [
            '{{namespace}}' => rtrim($this->getAppNamespace(), '\\'),
            '{{model_class}}' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']),
        ];

        return strtr($contents, $searchReplace);
    }

    public function makeRequest($request_type, $contents)
    {
        file_put_contents($this->crudPath->request . '/' . $request_type . $this->getClassName() . '.php', $contents);
    }

    public function getDestroyRequestContents()
    {
        $contents = file_get_contents($this->stubPath->request . 'destroy_request.stub');

        $searchReplace = [
            '{{namespace}}' => $this->getAppNamespace(),
            '{{model_class}}' => $this->getClassName(),
        ];

        return strtr($contents, $searchReplace);
    }

    public function getStoreRequestContents()
    {
        $contents = file_get_contents($this->stubPath->request . 'store_request.stub');

        $searchReplace = [
            '{{namespace}}' => $this->getAppNamespace(),
            '{{model_class}}' => $this->getClassName(),
        ];

        return strtr($contents, $searchReplace);
    }

    public function getUpdateRequestContents()
    {
        $contents = file_get_contents($this->stubPath->request . 'update_request.stub');

        $searchReplace = [
            '{{namespace}}' => $this->getAppNamespace(),
            '{{model_class}}' => $this->getClassName(),
        ];

        return strtr($contents, $searchReplace);
    }

    public function makeController($contents)
    {
        file_put_contents($this->crudPath->controller . $this->getClassName() . 'Controller.php', $contents);

        return null;
    }

    public function getControllerContents()
    {
        $contents = file_get_contents($this->stubPath->controller . 'controller.stub');

        $searchReplace = [
            '{{namespace}}' => $this->getAppNamespace(),
            '{{model_class}}' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']),
            '{{view_path}}' => strtr($this->model, ['_' => '']),
            '{{model_items}}' => camel_case($this->model),
            '{{model_item}}' => camel_case(str_singular($this->model)),
            '{{model_plural}}' => str_plural(str_singular(strtr($this->model, ['_' => ' ']))),
            '{{model_singular}}' => str_singular(strtr($this->model, ['_' => ' '])),
        ];

        return strtr($contents, $searchReplace);
    }

    public function makeViewController($contents)
    {
        file_put_contents($this->crudPath->viewController . $this->getClassName() . 'Controller.php', $contents);

        return null;
    }

    public function getViewControllerContents()
    {
        $contents = file_get_contents($this->stubPath->viewController . 'controller.stub');

        $searchReplace = [
            '{{namespace}}' => $this->getAppNamespace(),
            '{{model_class}}' => strtr(str_singular(ucwords(strtr($this->model, ['_' => ' ']))), [' ' => '']),
            '{{view_path}}' => strtr($this->model, ['_' => '']),
            '{{model_items}}' => camel_case($this->model),
            '{{model_item}}' => camel_case(str_singular($this->model)),
            '{{model_plural}}' => str_plural(str_singular(strtr($this->model, ['_' => ' ']))),
            '{{model_singular}}' => str_singular(strtr($this->model, ['_' => ' '])),
            '{{model_confirmation}}' => ucfirst(str_singular(strtr($this->model, ['_' => ' ']))),
        ];

        return strtr($contents, $searchReplace);
    }

    public function makeView($view_type, $contents)
    {
        file_put_contents($this->crudPath->view . '/' . $view_type . '.blade.php', $contents);
    }

    public function getCreateViewContents()
    {
        $contents = file_get_contents($this->stubPath->view . 'create.stub');

        $model_form_fields = '';

        foreach ($this->schema as $column) {
            if (!$this->isIgnoredField($column->getName())) {
                if ($model_form_fields != '') {
                    $model_form_fields .= PHP_EOL . $this->insertTab(7);
                }
                $model_form_fields .= $this->getFormElement($column);
            }
        }

        $searchReplace = [
            '{{model_singular}}' => strtr(str_singular($this->model), ['_' => ' ']),
            '{{model_form_fields}}' => $model_form_fields,
            '{{model_store_route}}' => strtr($this->model, ['_' => '']) . '.store',
        ];

        return strtr($contents, $searchReplace);
    }

    public function getEditViewContents()
    {
        $contents = file_get_contents($this->stubPath->view . 'edit.stub');

        $model_form_fields = '';

        foreach ($this->schema as $column) {
            if (!$this->isIgnoredField($column->getName())) {
                if ($model_form_fields != '') {
                    $model_form_fields .= PHP_EOL . $this->insertTab(7);
                }
                $model_form_fields .= $this->getFormElement($column, true);
            }
        }

        $searchReplace = [
            '{{model_singular}}' => strtr(str_singular($this->model), ['_' => ' ']),
            '{{model_item}}' => str_singular($this->model),
            '{{model_form_fields}}' => $model_form_fields,
            '{{model_update_route}}' => strtr($this->model, ['_' => '']) . '.update',
        ];

        return strtr($contents, $searchReplace);
    }

    public function getIndexViewContents()
    {
        $contents = file_get_contents($this->stubPath->view . 'index.stub');

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

            if ($column->getName() == 'id') {
                $model_table_head .= '<th>#</th>';
            } else {
                $model_table_head .= '<th>' . strtr(ucfirst($column_heading), ['_' => ' ']) . '</th>';
            }
        }

        $model_item_table_row .= PHP_EOL . $this->insertTab(10) . '<td>' . PHP_EOL . $this->insertTab(11) . '<a class="btn" href="{{route(\'' . strtr($this->model, ['_' => '']) . '.show\',$' . $model_item . '->id)}}">View</a>' . PHP_EOL . $this->insertTab(11) . '<a class="btn" href="{{route(\'' . strtr($this->model, ['_' => '']) . '.edit\',$' . $model_item . '->id)}}">Edit</a>' . PHP_EOL . $this->insertTab(11) . '<a class="btn" href="#" onclick="event.preventDefault(); document.getElementById(\'delete_' . $this->model . '_form-{{$' . $model_item . '->id}}\').submit();">Delete</a>' . PHP_EOL . $this->insertTab(11) . '<form id="delete_' . $this->model . '_form-{{$' . $model_item . '->id}}" method="POST" action="{{route(\'' . strtr($this->model, ['_' => '']) . '.destroy\',$' . $model_item . ')}}">' . PHP_EOL . $this->insertTab(12) . '{{method_field(\'DELETE\')}}' . PHP_EOL . $this->insertTab(12) . '{{csrf_field()}}' . PHP_EOL . $this->insertTab(11) . '</form>' . PHP_EOL . $this->insertTab(10) . '</td>';

        $searchReplace = [
            '{{model_plural}}' => $model_plural,
            '{{model_create_route}}' => strtr($this->model, ['_' => '']) . '.create',
            '{{model_items}}' => '$' . $model_items,
            '{{model_item}}' => '$' . $model_item,
            '{{model_table_head}}' => $model_table_head,
            '{{model_item_table_row}}' => $model_item_table_row,
        ];

        return strtr($contents, $searchReplace);
    }

    public function getShowViewContents()
    {
        $contents = file_get_contents($this->stubPath->view . 'show.stub');

        $model_fields = '';

        foreach ($this->schema as $column) {
            $model_fields .= '<dt>' . strtr(ucfirst($column->getName()), ['_' => ' ']) . '</dt><dd>{{$' . str_singular($this->model) . '->' . $column->getName() . '}}</dd>';
        }

        $searchReplace = [
            '{{model_singular}}' => ucfirst(strtr(str_singular($this->model), ['_' => ' '])),
            '{{model_item}}' => '$' . str_singular($this->model),
            '{{model_fields}}' => $model_fields,
        ];

        return strtr($contents, $searchReplace);
    }

    public function doctrineToHtmlInput($doctrineType)
    {
        switch ($doctrineType) {
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
                return $doctrineType;
                break;
        }
    }

    public function doctrineToValidationRuleType($doctrineType)
    {
        switch ($doctrineType) {
            case 'Integer':
                return 'integer';
                break;
            case 'String':
                return 'string';
                break;
            case 'DateTime':
                return 'date';
                break;
            default:
                return null;
                break;
        }
    }

    public function copyComponents()
    {
        $components = [
            'input',
            'select',
        ];

        foreach ($components as $component) {
            copy($this->stubPath->component . $component . '.blade.php', $this->crudPath->component . $component . '.blade.php');
        }
    }

    public function addRulePipe($rules)
    {
        if ($rules != '') {
            return $rules . '|';
        } else {
            return $rules;
        }
    }

    public function getFieldRules($column)
    {
        $rules = '';

        if ($column->getNotnull()) {
            $rules = $this->addRulePipe($rules);

            $rules .= 'required';
        }

        if (!is_null($this->doctrineToValidationRuleType($column->getType()))) {
            $rules = $this->addRulePipe($rules);

            $rules .= $this->doctrineToValidationRuleType($column->getType());
        }

        if ($column->getLength() > 0) {
            $rules = $this->addRulePipe($rules);

            $rules .= 'max:' . $column->getLength();
        }

        return $rules;
    }
}
