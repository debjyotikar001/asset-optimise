<?php

namespace Debjyotikar001\AssetOptimise\Helpers;

use hexydec\css\cssdoc;
use hexydec\jslite\jslite;
use Debjyotikar001\AssetOptimise\Helpers\JSEncrypt;

class FileHelper
{
  /**
   * Minify and optionally merge CSS/JS assets.
   *
   * @param array $filePaths Array of file paths (one or multiple)
   * @param string $type Type of files ('css' or 'js')
   * @param array $options Optional settings:
   *     [
   *       'jsEncrypt' => bool (default: false),
   *       'ttl' => string (default: 7d → 7 days),
   *       'name' => string|null (output file name, no extension or path) (default: auto-generated),
   *       'version' => string|int|null (file version, e.g., 2 → "bundle.v2.css" or beta → "bundle.beta.css")
   *     ]
   * 
   * @return string Path to the processed file
   */
  public static function minifyAssets(array $filePaths, string $type, array $options = []): string
  {
    $ttlSeconds = self::ttl($options['ttl'] ?? '7d'); // cache time
    $resolvedPaths = self::resolvePaths($filePaths, $type); // resolve full file system paths
    $buildFileInfo = self::buildFileInfo($filePaths, $resolvedPaths, $type, $ttlSeconds, $options); // build file paths
    $assetPath = $buildFileInfo['assetPath'];

    // Return asset path, if file was valid
    if ($buildFileInfo['exists']) return $assetPath;

    // Process files
    self::processFiles($resolvedPaths, $type, $buildFileInfo['storePath'], $ttlSeconds, $options['jsEncrypt'] ?? false);

    // Return asset path
    return $assetPath;
  }

  /**
   * Resolve full file system paths from given asset paths.
   *
   * This method checks both public/ and resources/{type}/ directories
   * and returns absolute file paths. This avoids resolving paths multiple
   * times across different methods and reduces filesystem I/O.
   *
   * @param array  $filePaths Input asset paths
   * @param string $type      Asset type ('css' or 'js')
   *
   * @return array            Resolved absolute file paths
   * @throws \Exception       If file does not exist
   */
  private static function resolvePaths(array $filePaths, string $type): array
  {
    $resolved = [];

    foreach ($filePaths as $path) {
      $publicPath = public_path($path);
      $resourcePath = resource_path("{$type}/{$path}");

      if (is_file($publicPath)) {
        $resolved[] = $publicPath;
      } elseif (is_file($resourcePath)) {
        $resolved[] = $resourcePath;
      } else {
        throw new \Exception("File does not exist: {$path}");
      }
    }

    return $resolved;
  }

  /**
   * Convert TTL string (e.g., "1d", "90m") into seconds.
   *
   * Format: {num}{unit}
   * Units:
   *   m = minutes (max 1440 → 1 day)
   *   h = hours   (max 720  → 30 days)
   *   d = days    (max 365  → 1 year)
   *   y = years   (max 5    → 5 years)
   *
   * @param string $value
   * 
   * @return int TTL in seconds
   * @throws \InvalidArgumentException
   */
  private static function ttl(string $value): int
  {
    // Ensure at least 2 chars (e.g. "1d")
    if (strlen($value) < 2) {
      throw new \InvalidArgumentException("Invalid TTL format: $value");
    }

    // Extract number and unit
    $num  = (int) substr($value, 0, -1);
    $unit = strtolower(substr($value, -1));

    // Must be > 0
    if ($num <= 0) {
      throw new \InvalidArgumentException("TTL number must be greater than 0: $value");
    }

    // Convert into seconds
    return match ($unit) {
      'm' => ($num <= 1440) ? $num * 60 : throw new \InvalidArgumentException("Minutes cannot exceed 1440 (1 day)"),
      'h' => ($num <= 720)  ? $num * 3600 : throw new \InvalidArgumentException("Hours cannot exceed 720 (30 days)"),
      'd' => ($num <= 365)  ? $num * 86400 : throw new \InvalidArgumentException("Days cannot exceed 365 (1 year)"),
      'y' => ($num <= 5)    ? $num * 31536000 : throw new \InvalidArgumentException("Years cannot exceed 5"),
      default => throw new \InvalidArgumentException("Invalid TTL unit: $unit"),
    };
  }

  /**
   * Build file info for minified/merged assets.
   *
   * Responsibilities:
   * - Determine output filename using base name + hash
   * - Hash is generated using either:
   *      - filemtime (fast, default)
   *      - md5_file (strict mode, reliable)
   * - Append version if provided:
   *      - numeric (e.g. "1.0.1") → becomes ".v101"
   *      - string (e.g. "beta")   → becomes ".beta"
   * - Apply default TTL of 7d if not set
   * - Check if stored file exists and is still valid (not expired)
   * - Return file paths and "exists" boolean for cache re-use
   *
   * @param array  $filePaths     Original input file paths (for naming)
   * @param array  $resolvedPaths Resolved absolute file paths (for processing)
   * @param string $type          Asset type ('css' or 'js')
   * @param int    $ttlSeconds    Cache time-to-live in seconds
   * @param array  $options       User options: name, version
   * 
   * @return array {
   *   @var string storePath Full storage path
   *   @var string assetPath Public asset path
   *   @var bool   exists    True if file exists and not expired
   * }
   * 
   * @throws \Exception
   */
  private static function buildFileInfo(array $filePaths, array $resolvedPaths, string $type, int $ttlSeconds, array $options = []): array
  {
    // Resolve name
    // Step 1: base name (for readability)
    if (!empty($options['name'])) {
      $baseName = $options['name'];
    } else {
      $baseName = (count($filePaths) === 1)
        ? pathinfo($filePaths[0], PATHINFO_FILENAME)
        : 'bundle';
    }

    // Step 2: generate hash
    $hashSource = '';
    $latestSourceMTime = 0;

    foreach ($resolvedPaths as $index => $fullPath) {
      $mtime = filemtime($fullPath);
    
      // Optional strict mode
      if (config('assetoptimise.strict_hash')) {
        $hashSource .= md5_file($fullPath);
      } else {
        $hashSource .= $filePaths[$index] . $mtime;
      }
    
      $latestSourceMTime = max($latestSourceMTime, $mtime);
    }

    // short hash (8 chars is enough)
    $hash = substr(md5($hashSource), 0, 8);

    // FINAL NAME
    $name = "{$baseName}-{$hash}";

    // Handle version
    $version = $options['version'] ?? null;
    if ($version !== null && $version !== '') {
      $version = str_replace('.', '', (string) $version); // remove dots
      if (ctype_digit($version)) {
        $version = 'v' . $version;
      }
      $name = "{$name}.{$version}";
    }

    // Build paths
    $file = "minified/{$type}/{$name}.min.{$type}";
    $storePath = storage_path("app/public/{$file}");
    $assetPath = asset("storage/{$file}");

    // Check file existence + expiry
    $isValid = is_file($storePath) && (
      filemtime($storePath) >= $latestSourceMTime &&
      filemtime($storePath) + $ttlSeconds >= time()
    );

    // Always return
    return [
      'storePath' => $storePath,
      'assetPath' => $assetPath,
      'exists' => $isValid,
    ];
  }

  /**
   * Minify merged content and optionally encrypt JavaScript.
   *
   * @param string $content     The merged content (CSS/JS)
   * @param string $type        Asset type ('css' or 'js')
   * @param int    $ttlSeconds  Cache time-to-live in seconds
   * @param bool   $jsEncrypt   Whether to apply JS encryption/obfuscation
   * 
   * @return string             Minified (and maybe encrypted) content
   * @throws \Exception
   */
  private static function minifyContent(string $content, string $type, int $ttlSeconds, bool $jsEncrypt): string
  {
    if (trim($content) === '') {
      return '';
    }

    // Minify content
    if ($type === 'css') {
      $doc = new cssdoc();
    } elseif ($type === 'js') {
      $doc = new jslite();
    } else {
      throw new \Exception("Unsupported asset type for minification: {$type}");
    }

    $doc->load($content);
    $doc->minify();
    $content = $doc->compile();

    // Encrypt JavaScript if enabled
    if ($type === 'js' && $jsEncrypt) {
      $JsEncrypt = new JSEncrypt($content);

      // Convert ttlSeconds → minutes for expiration
      $ttlMinutes = max(1, (int) ceil($ttlSeconds / 60));
      $JsEncrypt->setExpiration("+{$ttlMinutes} minutes");

      $domains = config('assetoptimise.js_encrypt_domains', [request()->getHost()]);
      foreach ((array) $domains as $domain) {
        $JsEncrypt->addDomainName($domain);
      }

      $content = $JsEncrypt->Obfuscate();
    }

    return $content;
  }

  /**
   * Load, merge and save assets with atomic file swap.
   *
   * This method uses resolved absolute file paths to avoid repeated
   * filesystem lookups. It ensures safe concurrent builds using file locks
   * and minimizes CPU/disk usage under high load using exponential backoff.
   * 
   * @param array  $resolvedPaths Resolved absolute file paths
   * @param string $type          Asset type ('css' or 'js')
   * @param string $storePath     Destination path to save merged file
   * @param int    $ttlSeconds    Cache time-to-live in seconds
   * @param bool   $jsEncrypt     Whether to apply JS encryption/obfuscation
   * 
   * @return void
   * @throws \Exception         If file type mismatch or file not found
   */
  private static function processFiles(array $resolvedPaths, string $type, string $storePath, int $ttlSeconds, bool $jsEncrypt): void
  {
    // Ensure directory exists
    $dir = dirname($storePath);
    if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
    }

    // If valid file already exists, nothing to do
    if (is_file($storePath)) {
      return;
    }

    $tmpPath = $storePath . '.tmp'; // tmp path

    // Try to become the builder
    $fp = @fopen($tmpPath, 'c'); // temp file, not the final
    
    if ($fp && flock($fp, LOCK_EX | LOCK_NB)) {
      try {
        // Double-check after acquiring lock, maybe another request finished while we waited
        if (is_file($storePath)) {
          return;
        }

        // Merge file contents
        $mergedContent = '';

        foreach ($resolvedPaths as $item) {
          // Validate file extension
          $ext = pathinfo($item, PATHINFO_EXTENSION);
          if ($ext !== $type) {
            throw new \Exception("File type mismatch: Expected {$type}, got {$ext} in {$item}");
          }

          // Read file
          $mergedContent .= file_get_contents($item) . "\n";
        }

        // Minify + optional encryption
        $minifiedContent = self::minifyContent($mergedContent, $type, $ttlSeconds, $jsEncrypt);

        // Write to temp file
        ftruncate($fp, 0);
        fwrite($fp, $minifiedContent);
        fflush($fp);

        // Atomic swap
        rename($tmpPath, $storePath);
      } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
      }
    } else {
      // Another process is building → wait
      $maxWait = 3000; // 3 seconds
      $step = 100;     // start with 100ms
      $waited = 0;

      while ($waited < $maxWait) {
        if (is_file($storePath)) {
          return; // file is ready
        }

        usleep($step * 1000); // sleep 100ms
        $waited += $step;

        // Exponential backoff
        $step = min($step * 2, 500);
      }

      // Timeout handling
      if (app()->environment('production')) {
        logger()->warning("AssetOptimise: Timed out waiting for asset build: {$storePath}");
        return; // soft skip
      } else {
        throw new \Exception("Timed out waiting for asset build: {$storePath}");
      }
    }
  }
}
