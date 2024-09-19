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
        
      $no_optimise = $this->replace('/<!--\s*no-optimise\s*-->(.*?)<!--\s*\/no-optimise\s*-->/is', $html, 'NO_OPTIMISE', 1);
      $html = $no_optimise['html'];

      if (config('assetoptimise.skip_comment')) {
        $skip_comment = $this->replace('/<!--(.*?)-->/is', $html, 'SKIP_COMMENT');
        $html = $skip_comment['html'];
      }

      if (config('assetoptimise.skip_css')) {
        $skip_css = $this->replace('/<style\b[^>]*>(.*?)<\/style>/is', $html, 'SKIP_CSS');
        $html = $skip_css['html'];
      }

      if (config('assetoptimise.skip_js')) {
        $skip_js = $this->replace('/<script\b[^>]*>(.*?)<\/script>/is', $html, 'SKIP_JS');
        $html = $skip_js['html'];
      }

      // minify
      $doc = new htmldoc();
      $doc->load($html);
      $doc->minify();
      $html = $doc->save();

      $html = $this->restore($no_optimise['matches'], $html, 'NO_OPTIMISE');

      if (config('assetoptimise.skip_comment')) {
        $html = $this->restore($skip_comment['matches'], $html, 'SKIP_COMMENT');
      }

      if (config('assetoptimise.skip_css')) {
        $html = $this->restore($skip_css['matches'], $html, 'SKIP_CSS');
      }

      if (config('assetoptimise.skip_js')) {
        $html = $this->restore($skip_js['matches'], $html, 'SKIP_JS');
      }

      $response->setContent($html);
    }

    return $response;
  }

  /**
   * Replace HTML with placeholder
   */
  protected function replace($pattern, $html, $placeholder, $match_index = 0) {
    preg_match_all($pattern, $html, $matches);
    foreach ($matches[$match_index] as $index => $fullMatch) {
      $html = str_replace($fullMatch, "###" . $placeholder . "_PLACEHOLDER_$index###", $html);
    }
    return ['html' => $html, 'matches' => $matches[$match_index]];
  }

  /**
   * Restore placeholder with HTML
   */
  protected function restore($matches, $html, $placeholder) {
    foreach ($matches as $index => $fullMatch) {
      $html = str_replace("###" . $placeholder . "_PLACEHOLDER_$index###", $fullMatch, $html);
    }
    return $html;
  }
}
