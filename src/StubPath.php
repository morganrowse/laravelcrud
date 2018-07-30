<?php

namespace MorganRowse\LaravelCrud;


class StubPath
{
    public $controller;
    public $viewController;
    public $model;
    public $request;
    public $resource;
    public $view;
    public $component;

    public function __construct()
    {
        $this->controller = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Controllers/');
        $this->viewController = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Controllers/View/');
        $this->model = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Models/');
        $this->request = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Requests/');
        $this->resource = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Resources/');
        $this->view = base_path('vendor/morganrowse/laravelcrud/src/Stubs/Views/');
        $this->componenth = base_path('vendor/morganrowse/laravelcrud/src/resources/views/components/');
    }
}
