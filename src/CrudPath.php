<?php

namespace MorganRowse\LaravelCrud;

class CrudPath
{
    public $controller;
    public $viewController;
    public $model;
    public $request;
    public $resource;
    public $view;

    public function __construct(string $model)
    {
        $this->controller = app_path('Http/Controllers/');
        $this->viewController = app_path('Http/Controllers/View/');
        $this->model = app_path() . '/';
        $this->request = app_path('Http/Requests/');
        $this->resource = app_path('Http/Resources/');
        $this->view = resource_path('views/' . strtr($model, ['_' => '']) . '/');
        $this->component = resource_path('views/components/');
    }
}
