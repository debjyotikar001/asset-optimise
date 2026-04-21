# Changelog

## [1.0.0] - 2024-09-17
### Added
- Initial release of AssetOptimise.

## [2.0.0] - 2024-09-21
### New Features
- Skip Comment.
- Skip Routes Urls.
- Environment Control.

## [2.1.0] - 2024-09-22
### New Feature
- Email Optimise.

## [2.2.0] - 2024-09-22
### New Feature
- Merge and Minify multiple assets.
- Minify asset.

## [2.2.1] - 2025-02-13
### New Feature
- JavaScript encryption for more security.

## [3.0.0] - 2025-03-16
### Laravel 12 Support
- Laravel 12 Support added.

## [4.0.0] - 2025-11-17
### Optimized
- Overall performance significantly improved.
- Consolidated `mergeAssets` and `minifyAsset` into a single unified method: `minifyAssets`.
- Enhanced cache handling — `ttl` now supports `minutes`, `hours`, `days`, and `years`.
### Added
- Asset versioning support.

## [4.0.1] - 2026-02-10
### Added
- Minified assets clearing support.

## [4.1.0] - 2026-04-21
### Added
- Configurable hashing strategy (`strict_hash`) to control asset hash generation:
  - `filemtime`-based hashing (default, faster)
  - content-based hashing using `md5_file()` (reliable)

### Improved
- Asset cache invalidation is now more reliable — file changes are detected automatically without requiring manual version updates.
- File naming strategy updated to include content-aware hash, preventing filename collisions across different asset paths.
- Reduced filesystem I/O by resolving asset paths once and reusing them across processing steps.
- Optimized file processing to avoid redundant file existence checks and repeated path resolution.

### Performance
- Improved concurrency handling with exponential backoff in file generation wait loop, reducing CPU and disk pressure under high load.
- Reduced unnecessary disk reads during asset processing, improving efficiency for repeated requests.

### Internal
- Refactored asset build pipeline to use resolved file paths instead of repeated path lookups.
- Improved overall maintainability and scalability of asset processing logic.

### Laravel 13 Support
- Laravel 13 Support added.
