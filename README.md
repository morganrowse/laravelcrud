# Laravel CRUD

<a href="https://packagist.org/packages/morganrowse/laravelcrud"><img src="https://img.shields.io/packagist/dt/morganrowse/laravelcrud.svg?style=for-the-badge" alt="Downloads"></a>
<a href="https://packagist.org/packages/morganrowse/laravelcrud"><img src="https://img.shields.io/packagist/v/morganrowse/laravelcrud.svg?style=for-the-badge" alt="Version"></a>
<a href="https://travis-ci.org/morganrowse/laravelcrud"><img src="https://img.shields.io/travis/morganrowse/laravelcrud.svg?style=for-the-badge" alt="Travis"></a>
<a href="https://www.codacy.com/app/morgan.rowse/laravelcrud"><img src="https://img.shields.io/codacy/grade/b996342a8a36472aaed723137cbff2a5.svg?style=for-the-badge" alt="Codacy grade"></a>

This composer package adds artisan commands which create Models, Views, Controllers and Request Validation for crud operation based off of a database table schema.

## Installation

![Subheader Image](https://user-images.githubusercontent.com/17880010/32118361-aab588f8-bb51-11e7-95ef-6462dd720179.png)

First add the package via composer

```bash
$ composer require morganrowse/laravelcrud dev-master
```

_Use dev-master as I currently don't push tags_

## Usage

![Subheader Image](https://user-images.githubusercontent.com/17880010/32118361-aab588f8-bb51-11e7-95ef-6462dd720179.png)

First have your database setup as desired following laravel naming convention (such as a table called posts).

Next run the command via artisan

```bash
$ php artisan make:crud posts
```

This will create:

```
app
│   Post.php
└───Http
│   └───Controllers
│   │   │   PostController.php
│   |   └───View
│   │   │   |   PostController.php
│   └───Requests
│   │   └───Post
│   │   │   │   DestroyPost.php
│   │   │   │   StorePost.php
│   │   │   │   UpdatePost.php
│   └───Resources
│   │   │   PostResource.php
resources
└───views
│   └───posts
│   │   │   create.blade.php
│   │   │   edit.blade.php
│   │   │   index.blade.php
│   │   │   show.blade.php
```

Now add the view routes to your **web.php**

```php
...
Route::resource('posts','View\\PostController');
...
```

Finally add the api routes to your **api.php**

```php
...
Route::apiResource('posts','PostController');
...
```

![Subheader Image](https://user-images.githubusercontent.com/17880010/32118361-aab588f8-bb51-11e7-95ef-6462dd720179.png)
