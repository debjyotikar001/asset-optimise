<?php

namespace Debjyotikar001\AssetOptimise;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Debjyotikar001\AssetOptimise\Middleware\Minifier;

class AssetOptimiseServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap the application events.
   */
  public function boot(Router $router)
  {
    $router->aliasMiddleware('assetOptimise', Minifier::class);
    $this->publishes([
      __DIR__.'/../config/assetoptimise.php' => config_path('assetoptimise.php'),
    ], 'config');
  }

  /**
   * Register the service provider.
   */
  public function register()
  {
    $this->mergeConfigFrom(__DIR__.'/../config/assetoptimise.php', 'assetoptimise.php');
  }
}
