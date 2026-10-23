=== Unstoppable Inventory Alerts for WooCommerce ===
Contributors: paramedic192
Tags: woocommerce, back-in-stock, product-notifications, waitlist, email-alerts
Requires at least: 6.0
Tested up to: 7.1.3
Stable tag: 1.1.7
Requires PHP: 7.4
WC requires at least: 8.0
WC tested up to: 9.8
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Modern back-in-stock email notifications for WooCommerce with AJAX forms, comprehensive dashboard, CSV exports, and reliable email delivery.

== Description ==

**Unstoppable Inventory Alerts** is a powerful WooCommerce extension that notifies customers when out-of-stock products become available again. Built with modern standards and best practices for a reliable notification experience.

= Key Features =

* **Full AJAX Support** - Subscription forms work without page reload for a seamless customer experience
* **Comprehensive Dashboard** - View all subscribers, products, and statistics at a glance with beautiful charts
* **Complete Subscriber List** - See all subscribers with search, filter, and pagination
* **CSV Export** - Export subscribers and product data with flexible filters
* **Reliable Email Queue** - Batch processing with throttle protection ensures emails are delivered
* **Variable Product Support** - Works with simple and variable products including variations
* **Double Opt-in** - Optional email verification for GDPR compliance
* **WooCommerce Email Integration** - Uses WooCommerce email templates that can be customized

= Highlights =

1. **AJAX Forms** - No page reload required when subscribing
2. **Full Visibility** - Complete subscriber lists with advanced filtering
3. **Export Capability** - Export all data to CSV with flexible options
4. **Reliable Delivery** - Cron-based queue system ensures emails are sent when products return to stock
5. **Modern UI** - Clean, intuitive admin interface

= Options Studio Integration =

Part of the Unstoppable Solutions plugin family. Works seamlessly with other Options Studio plugins under a unified admin menu.

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/unstoppable-stock-alerts/` or install through the WordPress plugins screen
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Options Studio → Stock Alerts to configure settings
4. Customize form text, email settings, and notification preferences

== Frequently Asked Questions ==

= Does this work with variable products? =

Yes! The plugin supports both simple and variable products. For variable products, the subscription form appears when an out-of-stock variation is selected.

= Are emails sent automatically when products are restocked? =

Yes. When a product's stock status changes to "In Stock", all subscribers are automatically queued for notification. A background process sends emails in batches to ensure reliable delivery.

= Can I export subscriber data? =

Yes. You can export all subscribers to CSV from the Subscribers tab. Filters can be applied before export to get specific data sets.

= Is there double opt-in support? =

Yes. Enable double opt-in in settings to require email verification before subscriptions are confirmed. This helps with GDPR compliance and ensures valid email addresses.

= Can logged-in users subscribe without entering email? =

Yes. For logged-in users, their email is automatically detected and the email field is pre-filled.

= Does it work with WooCommerce email templates? =

Yes. All emails integrate with WooCommerce's email system and can be customized through WooCommerce → Settings → Emails.

== Screenshots ==

1. Dashboard with statistics and charts
2. Subscribers list with search and filter
3. Products tab showing subscriber counts
4. Settings page
5. Frontend subscription form
6. Email notification example

== Changelog ==

= 1.1.7 =
* Changed: Plugin name to "Unstoppable Inventory Alerts for WooCommerce" for clarity

= 1.1.6 =
* Security: Added nonce verification for unsubscribe endpoint CSRF protection
* Updated: Chart.js to v4.5.1 (latest stable)

= 1.1.5 =
* Security: Fixed CSV formula injection vulnerability (CWE-1236) in export functions
* Added sanitization for all CSV output to prevent spreadsheet formula execution

= 1.1.4 =
* Fixed: Removed comparative language from plugin description
* Fixed: Moved inline chart script to use wp_add_inline_script() and wp_localize_script()
* Updated: Chart.js to v4.4.8 (latest stable)

= 1.1.3 =
* Fixed: Added phpcs:disable comments for expected warnings (template variables, WooCommerce hooks, direct DB queries)
* Fixed: Added wp_unslash() to all input sanitization
* Fixed: Added phpcs:ignore for dynamic SQL building (safe patterns)
* Fixed: Escaped all translatable strings in wp_die() calls
* Fixed: Added phpcs:ignore for fclose() on php://output streams

= 1.1.2 =
* Fixed: Additional escaping on products view and export
* Fixed: wp_unslash on export parameters
* Fixed: phpcs compliance for CSV file operations

= 1.1.1 =
* Fixed: Escaping on pagination output
* Fixed: Added translators comments for placeholder strings

= 1.1.0 =
* Prepared for WordPress.org submission
* Updated: WordPress 7.0 compatibility
* Updated: WooCommerce 9.8 compatibility
* Improved: Security hardening with wp_unslash() sanitization
* Improved: Converted inline styles to use wp_add_inline_style()
* Improved: Bundled Chart.js locally instead of CDN

= 1.0.3 - 2025-12-24 =
* Changed: Renamed from "Stock Alerts" to "Product Alerts" to avoid stock market confusion
* Fixed: Chart containers now have proper height constraints to prevent page growth
* Fixed: Added Chart.js load verification before rendering charts

= 1.0.2 - 2025-12-24 =
* Fixed: Database table auto-creation if missing
* Fixed: Added safety checks to prevent errors when table doesn't exist
* Fixed: All database query methods now return proper empty arrays instead of null

= 1.0.1 - 2025-12-24 =
* Added: WooCommerce HPOS (High-Performance Order Storage) compatibility declaration
* Added: WooCommerce Cart & Checkout Blocks compatibility declaration

= 1.0.0 =
* Initial release
* Full AJAX support for subscription forms
* Comprehensive admin dashboard with Chart.js
* Complete subscribers list with search, filter, and pagination
* CSV export for subscribers and products
* Email queue system with cron processing
* Variable product support
* Double opt-in option
* WooCommerce email integration
* Options Studio integration

== Upgrade Notice ==

= 1.1.1 =
Fixed escaping and translators comments for WordPress.org compliance.
