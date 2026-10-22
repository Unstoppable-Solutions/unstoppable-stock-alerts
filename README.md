# Unstoppable Inventory Alerts for WooCommerce

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/unstoppable-stock-alerts.svg)](https://wordpress.org/plugins/unstoppable-stock-alerts/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/unstoppable-stock-alerts.svg)](https://wordpress.org/plugins/unstoppable-stock-alerts/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPLv2+-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)

Back-in-stock email notifications for WooCommerce. Customers sign up on the product page; your store emails them the moment the product is back. Includes a full subscriber dashboard, CSV export, and configurable email templates.

> **End users:** install from the WordPress Plugin Directory — [wordpress.org/plugins/unstoppable-stock-alerts](https://wordpress.org/plugins/unstoppable-stock-alerts/)

## Features

- **One-click subscribe on out-of-stock products** — AJAX form, no page reload
- **Automatic send on restock** — triggers the moment inventory flips back
- **Subscriber dashboard** — see who's waiting on which product
- **CSV export** — pull the waitlist for your marketing tools
- **Reliable delivery** — standard `wp_mail` with your SMTP plugin of choice
- **Variation-aware** — subscribes to the specific variation, not just the parent product

## Development

### Release workflow

Releases are deployed to WordPress.org automatically by GitHub Actions:

1. Finish the feature work on `main`
2. Bump the version in `unstoppable-stock-alerts.php` (header + any constant), `readme.txt` (Stable tag), and add a Changelog entry
3. Commit and push `main`
4. On GitHub, create a new **Release** tagged `vX.Y.Z` — the deploy workflow fires and pushes to WordPress.org SVN

### CI

Every push and pull request runs the official WordPress Plugin Check against the codebase.

## License

GPL-2.0-or-later. See the `License` header in `unstoppable-stock-alerts.php`.

## Author

Developed and maintained by [Unstoppable Solutions](https://unstoppablesolutions.app).
