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
  'inline_css' => env('ASSETOPTIMISE_INLINE_CSS', false),

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
  'inline_js' => env('ASSETOPTIMISE_INLINE_JS', false),

];