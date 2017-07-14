# laravelcrud

This composer package adds artisan commands which create views, controllers and resource routing for crud operation based off a database  table schema.

Add to your app providers

```bash
$ composer require morganrowse/laravelcrud
```

Next, add the `ArtisanViewServiceProvider` to your `providers` array in `config/app.php`:

```php
// config/app.php
'providers' => [
    ...
    MorganRowse\LaravelCrud\ServiceProvider::class
];
```
