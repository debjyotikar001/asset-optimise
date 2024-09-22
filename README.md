# Asset Optimise for Laravel

Asset Optimise is a powerful and lightweight Laravel package designed to enhance the performance of your web applications by optimizing front-end assets. This package automatically minifies HTML, CSS, and JavaScript code to reduce file sizes and improve page load times and save bandwidth.

## Key Features:

1. Minifies HTML, CSS, and JavaScript for faster loading.
2. Configurable to skip minification for inline CSS, inline JavaScript and HTML comments.
3. Supports excluding specific sections of code from being minified using custom HTML comments.
4. Supports excluding specific routes urls paths from being minified.
5. Configurable to skip minification for specific application Environment.
6. Easy integration with Laravel's middleware system.
7. Supports Email (HTML, CSS, and JavaScript) minification.
8. Extensible for future updates, including image compression and CDN integration.

## Installation

Asset Optimise for Laravel requires PHP 8.0 or higher. This particular version supports Laravel 9.x, 10.x, and 11.x.

To get the latest version, simply require the project using [Composer](https://getcomposer.org):

```sh
composer require debjyotikar001/asset-optimise
```

## Configuration

Asset Optimise for Laravel supports optional configuration. To get started, you'll need to publish all vendor assets:

```sh
php artisan vendor:publish --provider="Debjyotikar001\AssetOptimise\AssetOptimiseServiceProvider"
```

This will create a `config/assetoptimise.php` file in your app that you can modify to set your configuration. Also, make sure you check for changes to the original config file in this package.

## Register the Middleware
In order to add asset optimization functionality in Laravel, you need to add the Minifier middleware.

### (Laravel 10 or older)
In `app/Http/Kernel.php` file:

```php
protected $middleware = [
  // other middleware
  \Debjyotikar001\AssetOptimise\Middleware\Minifier::class,
];
```
or 
```php
protected $middleware = [
  // other middleware
  'assetOptimise',
];
```
You can use the middleware class or middleware alias `assetOptimise` in web middleware or route.

### (Laravel 11 or newer)
In `bootstrap/app.php` file:

```php
->withMiddleware(function (Middleware $middleware) {
  $middleware->web(append: [
    // other middleware
    \Debjyotikar001\AssetOptimise\Middleware\Minifier::class,
  ]);
})
```
or 
```php
->withMiddleware(function (Middleware $middleware) {
  $middleware->web(append: [
    // other middleware
    'assetOptimise',
  ]);
})
```
You can use the middleware class or middleware alias `assetOptimise` in web middleware or route.

## Usage
This is how you can use AssetOptimise for Laravel in your project.

### Enable
You must set `true` on `enabled` in the `config/assetoptimise.php` file enable asset optimization functionality. For example:

```php
'enabled' => env('ASSETOPTIMISE_ENABLED', true),
```

### Allowed Environments
If you want to disable it in specific environments such as during local development or testing to simplify debugging. Then set environments values in a comma (`,`) separated string in the `config/assetoptimise.php` file, default `local,production,staging`. For example:

```php
'allowed_envs' => env('ASSETOPTIMISE_ALLOWED_ENVS', 'local,production,staging'),
```

### Skip specific sections of code
If you want to skip specific sections of code from being minified using `<!-- no-optimise --> ... <!-- /no-optimise -->` HTML comments. This sections supports HTML, CSS and JavaScript code. For example:

```html
<!-- no-optimise -->
<style>
  /* Don't optimise this CSS code */
  h1 {
    color: black;
    font-size: 40px;
  }

  p {
    color: red;
    font-size: 24px;
  }
</style>

<h1>Asset Optimise</h1>
<p>Don't optimise this HTML code</p>

<script>
  // Don't optimise this JavaScript code
  alert("Asset Optimise: Don't optimise this JavaScript code");
</script>
<!-- /no-optimise -->
```

### Skip Inline CSS
If you want to skip inline CSS (within `<style>` tags in your HTML) optimization. Then set `true` on `skip_css` in the `config/assetoptimise.php` file, default `false`. For example:

```php
'skip_css' => env('ASSETOPTIMISE_SKIP_CSS', true),
```

### Skip Inline JavaScript
If you want to skip inline JavaScript (within `<script>` tags in your HTML) optimization. Then set `true` on `skip_js` in the `config/assetoptimise.php` file, default `false`. For example:

```php
'skip_js' => env('ASSETOPTIMISE_SKIP_JS', true),
```

### Skip HTML Comments
If you want to skip all HTML comments from the output. Then set `true` on `skip_comment` in the `config/assetoptimise.php` file, default `false`. For example:

```php
'skip_comment' => env('ASSETOPTIMISE_SKIP_COMMENT', true),
```

### Skip or Ignore specific Routes Urls
If you want to skip or ignore specific routes urls, then you have to set paths in the `config/assetoptimise.php` file. You can use '*' as wildcard. For example:

```php
'skip_urls' => [
    '/',
    'about',
    'user/*',
    '*_dashboard',
    '*/download/*',
  ],
```
#### Example URLs:
- `/`: Home URL will be excluded from minification.
- `about`: This exact URL will be excluded from minification.
- `user/*`: Any URL starting with `user/` (like `user/profile`, `user/settings`) will be excluded.
- `*_dashboard`: Any URL ending with `_dashboard` (like `admin_dashboard`, `user_dashboard`) will be excluded.
- `*/download/*`: Any URL has `download` (like `pdf/download/001`, `image/download/debjyotikar001`) will be excluded.


### Enable Email Optimise
You must set `true` on `email_enabled` in the `config/assetoptimise.php` file to enable asset optimization functionality for emails. For example:

```php
'email_enabled' => env('ASSETOPTIMISE_EMAIL_ENABLED', true),
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details on how to get started.

## License

Asset Optimise is licensed under the [MIT license](LICENSE).

## Credits

This package uses the [hexydec/htmldoc](https://github.com/hexydec/htmldoc) library for HTML, CSS and JavaScript minification.

## Support

If you are having general issues with this package, feel free to contact us on [debjyotikar001@gmail.com](mailto:debjyotikar001@gmail.com)
