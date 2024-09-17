# Asset Optimise for Laravel

Asset Optimise is a powerful and lightweight Laravel package designed to enhance the performance of your web applications by optimizing front-end assets. This package automatically minifies HTML, CSS, and JavaScript code to reduce file sizes and improve page load times and save bandwidth.

## Key Features:

1. Minifies HTML, CSS, and JavaScript for faster loading.
2. Configurable to skip minification for inline CSS and JavaScript, with options set in the configuration file.
3. Supports excluding specific sections of code from being minified using custom HTML comments.
4. Easy integration with Laravel's middleware system.
5. Extensible for future updates, including image compression and CDN integration.

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
If you want to skip inline CSS (within `<style>` tags in your HTML) optimization, then set `true` on `inline_css` in the `config/assetoptimise.php` file, default `false`. For example:

```php
'inline_css' => env('ASSETOPTIMISE_INLINE_CSS', true),
```

### Skip Inline JavaScript
If you want to skip inline JavaScript (within `<script>` tags in your HTML) optimization, then set `true` on `inline_js` in the `config/assetoptimise.php` file, default `false`. For example:

```php
'inline_js' => env('ASSETOPTIMISE_INLINE_JS', true),
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
