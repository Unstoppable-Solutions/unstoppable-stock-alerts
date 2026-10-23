# Contributing to Unstoppable Inventory Alerts

Thanks for your interest! This plugin lives on GitHub for development and on WordPress.org for distribution. Issues, pull requests, and discussions all happen here on GitHub.

## Code of Conduct

Please be respectful and considerate of others. We're all here to build something great together.

## Reporting Bugs

Open an issue with:

- WordPress version
- WooCommerce version
- PHP version
- Plugin version
- Steps to reproduce
- Expected vs. actual behavior
- Any relevant error messages

## Suggesting Features

1. Check if the feature has already been suggested
2. Create a new issue with:
   - A clear description of the feature
   - The problem it solves
   - How it should work
   - Any mockups or examples (if applicable)

## Pull Requests

1. Branch from `main`, submit a PR back to `main`
2. The Plugin Check workflow runs on every PR and **must pass**
3. Match the existing code style — four-space tabs for PHP, PSR-12-adjacent for general structure
4. Inline documentation on every function and class
5. Prefix all new classes, functions, and constants with `USSA_` or `ussa_`

### Security Requirements

All code must follow WordPress security best practices:

1. **Sanitization**: Sanitize all user input
   ```php
   $email = sanitize_email( $_POST['email'] );
   $text = sanitize_text_field( $_POST['text'] );
   ```

2. **Escaping**: Escape all output
   ```php
   echo esc_html( $text );
   echo esc_attr( $attribute );
   echo esc_url( $url );
   ```

3. **Nonces**: Verify nonces on all forms
   ```php
   wp_nonce_field( 'ussa_action', 'ussa_nonce' );
   wp_verify_nonce( $_POST['ussa_nonce'], 'ussa_action' );
   ```

4. **Capabilities**: Check user capabilities
   ```php
   if ( ! current_user_can( 'manage_woocommerce' ) ) {
       wp_die( 'Unauthorized' );
   }
   ```

5. **Database**: Use prepared statements
   ```php
   $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id );
   ```

## Testing

Before submitting a PR, test:
- Fresh installation
- Upgrade from previous version
- All admin pages
- Frontend subscription form
- Email sending
- CSV exports
- With multiple themes
- With WooCommerce latest version

## Security Issues

Please report security issues **privately** rather than opening a public issue. See `SECURITY.md` if present.

## License

By contributing, you agree that your contributions will be licensed under the GPL-2.0-or-later license.

---

Thank you for contributing! 🎉
