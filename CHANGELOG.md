# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

For WordPress.org users, the same changelog lives in `readme.txt`.

## [Unreleased]

### Changed
- Updated "Tested up to" to WordPress 7.1.3

## [1.1.7] - 2026-10-01

### Changed
- Plugin name to "Unstoppable Inventory Alerts for WooCommerce" for clarity

## [1.1.6] - 2026-09-15

### Security
- Added nonce verification for unsubscribe endpoint CSRF protection

### Updated
- Chart.js to v4.5.1 (latest stable)

## [1.1.5] - 2026-09-10

### Security
- Fixed CSV formula injection vulnerability (CWE-1236) in export functions
- Added sanitization for all CSV output to prevent spreadsheet formula execution

## [1.1.4] - 2026-09-05

### Fixed
- Removed comparative language from plugin description
- Moved inline chart script to use `wp_add_inline_script()` and `wp_localize_script()`

### Updated
- Chart.js to v4.4.8 (latest stable)

## [1.1.3] - 2026-08-25

### Fixed
- Added phpcs:disable comments for expected warnings (template variables, WooCommerce hooks, direct DB queries)
- Added `wp_unslash()` to all input sanitization
- Added phpcs:ignore for dynamic SQL building (safe patterns)
- Escaped all translatable strings in `wp_die()` calls
- Added phpcs:ignore for `fclose()` on php://output streams

## [1.1.2] - 2026-08-20

### Fixed
- Additional escaping on products view and export
- `wp_unslash` on export parameters
- PHPCS compliance for CSV file operations

## [1.1.1] - 2026-08-15

### Fixed
- Escaping on pagination output
- Added translators comments for placeholder strings

## [1.1.0] - 2026-08-10

### Changed
- Prepared for WordPress.org submission

### Updated
- WordPress 7.0 compatibility
- WooCommerce 9.8 compatibility

### Improved
- Security hardening with `wp_unslash()` sanitization
- Converted inline styles to use `wp_add_inline_style()`
- Bundled Chart.js locally instead of CDN

## [1.0.3] - 2025-12-24

### Changed
- Renamed from "Stock Alerts" to "Product Alerts" to avoid stock market confusion

### Fixed
- Chart containers now have proper height constraints to prevent page growth
- Added Chart.js load verification before rendering charts

## [1.0.2] - 2025-12-24

### Fixed
- Database table auto-creation if missing
- Added safety checks to prevent errors when table doesn't exist
- All database query methods now return proper empty arrays instead of null

## [1.0.1] - 2025-12-24

### Added
- WooCommerce HPOS (High-Performance Order Storage) compatibility declaration
- WooCommerce Cart & Checkout Blocks compatibility declaration

## [1.0.0] - 2025-12-20

### Added
- Initial release
- Full AJAX support for subscription forms
- Comprehensive admin dashboard with Chart.js visualizations
- Complete subscribers list with search, filter, and pagination
- CSV export for subscribers and products
- Email queue system with cron processing
- Variable product support
- Double opt-in option
- WooCommerce email integration
- Options Studio integration
