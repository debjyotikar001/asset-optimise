<?php

namespace Debjyotikar001\AssetOptimise\Middleware;

use Closure;
use hexydec\html\htmldoc;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Minifier
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next): Response
  {
    $response = $next($request);

    if ($response->isSuccessful() && config('assetoptimise.enabled')) {
      $html = $response->getContent();
        
      // Skip sections between <!-- no-optimise --> and <!-- /no-optimise -->
      $pattern = '/<!--\s*no-optimise\s*-->(.*?)<!--\s*\/no-optimise\s*-->/is';
      preg_match_all($pattern, $html, $matches);

      // Replace
      foreach ($matches[1] as $index => $fullMatch) {
        $html = str_replace($fullMatch, "###NO_OPTIMISE_PLACEHOLDER_$index###", $html);
      }
        
      // Skip inline css
      if (config('assetoptimise.inline_css')) {
        $css_pattern = '/<style\b[^>]*>(.*?)<\/style>/is';
        preg_match_all($css_pattern, $html, $css_matches);
        foreach ($css_matches[0] as $index => $fullMatch) {
          $html = str_replace($fullMatch, "###NOT_OPTIMISE_CSS_PLACEHOLDER_$index###", $html);
        }
      }

      // Skip inline js
      if (config('assetoptimise.inline_js')) {
        $js_pattern = '/<script\b[^>]*>(.*?)<\/script>/is';
        preg_match_all($js_pattern, $html, $js_matches);
        foreach ($js_matches[0] as $index => $fullMatch) {
          $html = str_replace($fullMatch, "###NOT_OPTIMISE_JS_PLACEHOLDER_$index###", $html);
        }
      }

      // minify
      $doc = new htmldoc();
      $doc->load($html);
      $doc->minify();
      $html = $doc->save();

      // Restore
      foreach ($matches[1] as $index => $fullMatch) {
        $html = str_replace("###NO_OPTIMISE_PLACEHOLDER_$index###", $fullMatch, $html);
      }

      // inline css
      if (config('assetoptimise.inline_css')) {
        foreach ($css_matches[0] as $index => $fullMatch) {
          $html = str_replace("###NOT_OPTIMISE_CSS_PLACEHOLDER_$index###", $fullMatch, $html);
        }
      }

      // inline js
      if (config('assetoptimise.inline_js')) {
        foreach ($js_matches[0] as $index => $fullMatch) {
          $html = str_replace("###NOT_OPTIMISE_JS_PLACEHOLDER_$index###", $fullMatch, $html);
        }
      }

      $response->setContent($html);
    }

    return $response;
  }
}
