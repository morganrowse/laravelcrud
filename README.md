# Laravel CRUD

This composer package adds artisan commands which create views, controllers and resource routing for crud operation based off a database  table schema.

## Installation

First add the package via composer

```bash
$ composer require morganrowse/laravelcrud
```

## Usage

First have your database setup as desired following laravel naming convention.

Next run the command via artisan

```bash
$ php artisan make:crud posts
```

This will create 

├── 

├── app

|   ├── Http

|       └── Controllers

|           └── PhotoController.php

├── resources

|   ├── views

|       └── posts

|           └── create.blade.php

|           └── edit.blade.php

|           └── index.blade.php

|           └── show.blade.php

as well as adding the route resource to your routes file
