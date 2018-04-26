<?php

namespace MorganRowse\LaravelCrud;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\DetectsApplicationNamespace;

class BootstrapGenerator extends Generator
{
    public function __construct($model)
    {
        parent::__construct($model);
        $this->view_stub_path .= "\\bootstrap";
    }

    public function getFormElement($column, $hasOld = false)
    {
        return '<div class="form-group">' . PHP_EOL . $this->insertTab(3) . '<label>' . strtr(ucfirst($column->getName()), ['_' => ' ']) . '</label>' . PHP_EOL . $this->insertTab(3) . '<input name="' . $column->getName() . '" type="' . $this->doctrineToHtmlInput($column->getType()) . '" class="form-control {{($errors->has(\'' . $column->getName() . '\') ? \' is-invalid\' : \'\')}}" value="{{old(\'' . $column->getName() . '\')}}">' . PHP_EOL . $this->insertTab(3) . '@foreach($errors->get(\'' . $column->getName() . '\') as $error)' . PHP_EOL . $this->insertTab(4) . '<div class="invalid-feedback">' . PHP_EOL . $this->insertTab(5) . '{{$error}}' . PHP_EOL . $this->insertTab(4) . '</div>' . PHP_EOL . $this->insertTab(3) . '@endforeach' . PHP_EOL . $this->insertTab(2) . '</div>';
    }

}