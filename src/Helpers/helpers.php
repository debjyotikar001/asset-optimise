<?php

use Illuminate\Support\Facades\File;
use hexydec\jslite\jslite;
use hexydec\css\cssdoc;

if (!function_exists('mergeAssets')) {
  /**
   * Merge and optimise CSS/JS files into a single file.
   *
   * @param array $filePaths Array of file paths to merge
   * @param string $outputFileName Name of the output file (without extension)
   * @param string $type Type of files ('css' or 'js')
   * @param int $cacheTime Time in minutes to check for file updates
   * @return string Path to the merged file
   */
  function mergeAssets(array $filePaths, string $outputFileName, string $type, int $cacheTime = 1440)
  {
    $file = "minified/{$type}/{$outputFileName}.min.{$type}";
    $storePath = storage_path('app/public/' . $file);
    $assetPath = 'storage/' . $file;

    // output file exists and last modified
    if (File::exists($storePath)) {
      if ((time() - File::lastModified($storePath)) < ($cacheTime * 60)) { return asset($assetPath); }
    }

    // merge files
    $mergedContent = '';
    foreach ($filePaths as $item) {
      $publicPath = public_path($item);
      $resourcePath = resource_path($type . '/' . $item);

      if (File::exists($publicPath)) {
        $mergedContent .= File::get($publicPath) . "\n";
      } elseif (File::exists($resourcePath)) {
        $mergedContent .= File::get($resourcePath) . "\n";
      }
    }

    if (!File::isDirectory(dirname($storePath))) { File::makeDirectory(dirname($storePath), 0755, true); }

    // minify
    if ($type === 'css') {
      $doc = new cssdoc();
    } elseif ($type === 'js') {
      $doc = new jslite();
    }

    $doc->load($mergedContent);
    $doc->minify();
    $mergedContent = $doc->compile();

    // output file
    File::put($storePath, $mergedContent);

    return asset($assetPath);
  }
}
