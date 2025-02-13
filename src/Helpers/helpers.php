<?php

use hexydec\css\cssdoc;
use hexydec\jslite\jslite;
use Illuminate\Support\Facades\File;
use Debjyotikar001\AssetOptimise\Helpers\JSEncrypt;

if (!function_exists('mergeAssets')) {
  /**
   * Merge and optimise CSS/JS files into a single file.
   *
   * @param array $filePaths Array of file paths to merge
   * @param string $outputFileName Name of the output file (without extension)
   * @param string $type Type of files ('css' or 'js')
   * @param bool $jsEncrypt Whether to encrypt JavaScript for added security
   * @param int $cacheTime Time in minutes to check for file updates
   * @return string Path to the merged file
   */
  function mergeAssets(array $filePaths, string $outputFileName, string $type, bool $jsEncrypt = false, int $cacheTime = 1440)
  {
    $mergedContent = '';
    foreach ($filePaths as $item) {
      if (pathinfo($item)['extension'] !== $type) {
        throw new \Exception("Given file type and file is not same. File: " . $item . " Type: " . $type);
      }

      $publicPath = public_path($item);
      $resourcePath = resource_path($type . '/' . $item);

      if (File::exists($publicPath)) {
        $mergedContent .= File::get($publicPath) . "\n";
      } elseif (File::exists($resourcePath)) {
        $mergedContent .= File::get($resourcePath) . "\n";
      } else {
        throw new \Exception("File does not exist: " . $item);
      }
    }

    return processAndStoreAsset($mergedContent, $outputFileName, $type, $cacheTime, $jsEncrypt);
  }
}

if (!function_exists('minifyAsset')) {
  /**
   * Minify a CSS/JS file.
   *
   * @param string $filePath File path to minify
   * @param string $type Type of file ('css' or 'js')
   * @param bool $jsEncrypt Whether to encrypt JavaScript for added security
   * @param int $cacheTime Time in minutes to check for file updates
   * @param string|null $outputFileName Name of the output file (optional)
   * @return string Path to the minified file
   */
  function minifyAsset(string $filePath, string $type, bool $jsEncrypt = false, int $cacheTime = 1440, string $outputFileName = null)
  {
    if (pathinfo($filePath)['extension'] !== $type) {
      throw new \Exception("Given file type ({$type}) and file is not same. File: {$filePath}");
    }

    $outputFileName = $outputFileName ?? pathinfo($filePath)['filename'];
    $publicPath = public_path($filePath);
    $resourcePath = resource_path($type . '/' . $filePath);

    if (File::exists($publicPath)) {
      $fileContent = File::get($publicPath) . "\n";
    } elseif (File::exists($resourcePath)) {
      $fileContent = File::get($resourcePath) . "\n";
    } else {
      throw new \Exception("File does not exist: " . $filePath);
    }

    return processAndStoreAsset($fileContent, $outputFileName, $type, $cacheTime, $jsEncrypt);
  }
}

if (!function_exists('processAndStoreAsset')) {
  /**
   * Minify and optionally encrypt a CSS/JS asset, then store it.
   *
   * @param string $content File content to process
   * @param string $outputFileName Name of the output file (without extension)
   * @param string $type Type of file ('css' or 'js')
   * @param int $cacheTime Cache expiration time in minutes
   * @param bool $jsEncrypt Whether to encrypt JavaScript
   * @return string Path to the processed file
   */
  function processAndStoreAsset(string $content, string $outputFileName, string $type, int $cacheTime, bool $jsEncrypt)
  {
    $file = "minified/{$type}/{$outputFileName}.min.{$type}";
    $storePath = storage_path('app/public/' . $file);
    $assetPath = 'storage/' . $file;

    // Check cache validity
    if (File::exists($storePath) && (time() - File::lastModified($storePath)) < ($cacheTime * 60)) { return asset($assetPath); }

    if (!File::isDirectory(dirname($storePath))) { File::makeDirectory(dirname($storePath), 0755, true); }

    // Minify content
    if ($type === 'css') {
      $doc = new cssdoc();
    } elseif ($type === 'js') {
      $doc = new jslite();
    } else {
      throw new \Exception("Unsupported file type: " . $type);
    }

    $doc->load($content);
    $doc->minify();
    $content = $doc->compile();

    // Encrypt JavaScript if enabled
    if ($type === 'js' && $jsEncrypt) {
      $JsEncrypt = new JSEncrypt($content);
      $JsEncrypt->setExpiration("+{$cacheTime} minutes");
      $domains = config('assetoptimise.js_encrypt_domains', [request()->getHost()]);

      foreach ((array) $domains as $domain) {
        $JsEncrypt->addDomainName($domain);
      }

      $content = $JsEncrypt->Obfuscate();
    }

    // Store processed file
    File::put($storePath, $content);
    return asset($assetPath);
  }
}
