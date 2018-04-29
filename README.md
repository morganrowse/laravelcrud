# Laravel CRUD


[![Packagist](https://img.shields.io/packagist/dt/morganrowse/laravelcrud.svg?style=for-the-badge)](https://packagist.org/packages/morganrowse/laravelcrud)
![Packagist](https://img.shields.io/packagist/v/morganrowse/laravelcrud.svg?style=for-the-badge)

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
│   └───Requests
│   │   └───Post
│   │   │   │   DestroyPostRequest.php
│   │   │   │   StorePostRequest.php
│   │   │   │   UpdatePostRequest.php
resources
└───views
│   └───posts
│   │   │   create.blade.php
│   │   │   edit.blade.php
│   │   │   index.blade.php
│   │   │   show.blade.php
```

Now add the resource route to your **web.php**

```
...
Route::resource('posts','PostController');
...
```

![Subheader Image](https://user-images.githubusercontent.com/17880010/32118361-aab588f8-bb51-11e7-95ef-6462dd720179.png)
