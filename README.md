# Laravel CRUD

This composer package adds artisan commands which create models, views and controllers for crud operation based off a database table schema.

## Installation

Aadd the package via composer

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

```
app
│   Post.php
└───Http
│   └───Controllers
│       │   PostController.php
└───resources
│   └───views
│   │   └───posts
│   │   │   create.blade.php
│   │   │   edit.blade.php
│   │   │   index.blade.php
│   │   │   show.blade.php
```

Now add the resource route to your **web.php**

```
Route::resource('posts','PostController')
```
