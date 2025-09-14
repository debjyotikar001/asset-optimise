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
   *       'version' => string|int|null (file version, e.g., 2 → "bundle_v2.css" or beta → "bundle_beta.css")
   *     ]
   * @return string Path to the processed file
   */
  public static function minifyAssets(array $filePaths, string $type, array $options = []): string
  {
    $ttlSeconds = self::ttl($options['ttl'] ?? '7d'); // cache time
    $buildFileInfo = self::buildFileInfo($filePaths, $type, $ttlSeconds, $options); // build file paths
    $assetPath = $buildFileInfo['assetPath'];

    // Return asset path, if file was valid
    if ($buildFileInfo['exists']) return $assetPath;

    // Process files
    self::processFiles($filePaths, $type, $buildFileInfo['storePath'], $ttlSeconds, $options['jsEncrypt'] ?? false);

    // Return asset path
    return $assetPath;
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
   * - Determine output filename based on $options['name'] or fallback:
   *      - single file → use its basename
   *      - multiple files → md5 hash of all paths
   * - Append version if provided:
   *      - numeric (e.g. "1.0.1") → becomes ".v101"
   *      - string (e.g. "beta")   → becomes ".beta"
   * - Apply default TTL of 7d if not set
   * - Check if stored file exists and is still valid (not expired)
   * - Return file paths and "exists" boolean for cache re-use
   *
   * @param array  $filePaths Input file paths (single or multiple)
   * @param string $type    Asset type ('css' or 'js')
   * @param int    $ttlSeconds  Cache time-to-live in seconds
   * @param array  $options User options: name, version
   * @return array {
   *   @var string storePath Full storage path
   *   @var string assetPath Public asset path
   *   @var bool   exists    True if file exists and not expired
   * }
   */
  private static function buildFileInfo(array $filePaths, string $type, int $ttlSeconds, array $options = []): array
  {
    // Resolve name
    if (!empty($options['name'])) {
      $name = $options['name'];
    } else {
      $name = (count($filePaths) === 1)
        ? pathinfo($filePaths[0], PATHINFO_FILENAME) // single file → take basename without extension
        : md5(implode('|', $filePaths)); // multiple files → md5 of concatenated names
    }

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
    $isValid = is_file($storePath) && (filemtime($storePath) + $ttlSeconds >= time());

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
   * @param array  $filePaths   Input file paths relative to public/ or resources/
   * @param string $type        Asset type ('css' or 'js')
   * @param string $storePath   Destination path to save merged file
   * @param int    $ttlSeconds  Cache time-to-live in seconds
   * @param bool   $jsEncrypt   Whether to apply JS encryption/obfuscation
   * @throws \Exception         If file type mismatch or file not found
   * @return void
   */
  private static function processFiles(array $filePaths, string $type, string $storePath, int $ttlSeconds, bool $jsEncrypt): void
  {
    // Ensure directory exists
    $dir = dirname($storePath);
    if (!is_dir($dir)) {
      mkdir($dir, 0755, true);
    }

    // If valid file already exists, nothing to do
    if (is_file($storePath) && (filemtime($storePath) + $ttlSeconds >= time())) {
      return;
    }

    $tmpPath = $storePath . '.tmp'; // tmp path

    // Try to become the builder
    $fp = @fopen($tmpPath, 'c'); // temp file, not the final
    if ($fp && flock($fp, LOCK_EX | LOCK_NB)) {
      try {
        // Double-check: maybe another request finished while we waited
        if (is_file($storePath) && (filemtime($storePath) + $ttlSeconds >= time())) {
          return;
        }

        // Merge file contents
        $mergedContent = '';
        foreach ($filePaths as $item) {
          // Validate file extension
          $ext = pathinfo($item, PATHINFO_EXTENSION);
          if ($ext !== $type) {
            throw new \Exception("File type mismatch: Expected {$type}, got {$ext} in {$item}");
          }

          // Build possible paths
          $publicPath = public_path($item);
          $resourcePath = resource_path("{$type}/{$item}");

          // Read file (prefer public/, fallback to resources/)
          if (is_file($publicPath)) {
            $mergedContent .= file_get_contents($publicPath) . "\n";
          } elseif (is_file($resourcePath)) {
            $mergedContent .= file_get_contents($resourcePath) . "\n";
          } else {
            throw new \Exception("File does not exist: {$item}");
          }
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
      // We are not the builder → wait for the final file to appear
      $maxWait = 3000; // ms → 3 sec
      $step = 100;     // ms
      $waited = 0;

      while ($waited < $maxWait) {
        if (is_file($storePath) && (filemtime($storePath) + $ttlSeconds >= time())) {
          return; // file ready
        }
        usleep($step * 1000); // sleep 100ms
        $waited += $step;
      }

      if (app()->environment('production')) {
        logger()->warning("AssetOptimise: Timed out waiting for asset build: {$storePath}");
        return; // soft skip
      } else {
        throw new \Exception("Timed out waiting for asset build: {$storePath}");
      }
    }
  }
}
