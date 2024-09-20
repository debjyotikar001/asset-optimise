<?php
/*
 * This file is part of AssetOptimise.
 *
 * (c) 2024 Debjyoti Kar <debjyotikar001@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
  /*
  |--------------------------------------------------------------------------
  | Enable Asset Optimise
  |--------------------------------------------------------------------------
  |
  | This option controls whether the asset optimization features are enabled
  | throughout your application. You may disable it in specific environments
  | such as during local development or testing to simplify debugging.
  |
  | Default: true
  |
  */
  'enabled' => env('ASSETOPTIMISE_ENABLED', true),

  /*
  |--------------------------------------------------------------------------
  | Skip Inline CSS Optimization
  |--------------------------------------------------------------------------
  |
  | If set to true, inline CSS (within <style> tags in your HTML) will not
  | be optimized. Use this option if you have dynamically generated or 
  | critical inline CSS that should remain untouched.
  |
  | Default: false
  |
  */
  'skip_css' => env('ASSETOPTIMISE_SKIP_CSS', false),

  /*
  |--------------------------------------------------------------------------
  | Skip Inline JavaScript Optimization
  |--------------------------------------------------------------------------
  |
  | If set to true, inline JavaScript (within <script> tags in your HTML)
  | will be excluded from minification. This is useful when you have 
  | inline scripts that should not be altered for any reason.
  |
  | Default: false
  |
  */
  'skip_js' => env('ASSETOPTIMISE_SKIP_JS', false),

  /*
  |--------------------------------------------------------------------------
  | Skip HTML Comments
  |--------------------------------------------------------------------------
  |
  | This option will skip all HTML comments from the output.
  |
  | Default: false
  |
  */
  'skip_comment' => env('ASSETOPTIMISE_SKIP_COMMENT', false),

  /*
  |--------------------------------------------------------------------------
  | Skip or Ignore Routes Urls
  |--------------------------------------------------------------------------
  |
  | Here you can specify routes urls paths, which you don't want to optimise.
  | You can use '*' as wildcard.
  |
  */
  'skip_urls' => [
      // '/',
      // 'about',
      // 'user/*',
      // '*_dashboard',
      // '*/download/*',
    ],

];
