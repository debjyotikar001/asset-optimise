<?php

use Debjyotikar001\AssetOptimise\Helpers\FileHelper;

if (!function_exists('minifyAssets')) {
  /**
   * Helper function of minifyAssets to minify and optionally merge CSS/JS assets.
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
  function minifyAssets(array $filePaths, string $type, array $options = []): string
  {
    return FileHelper::minifyAssets($filePaths, $type, $options);
  }
}
