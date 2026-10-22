<?php
/**
 * Email Verification Email (Plain Text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/ussa-verification.php
 *
 * @package Unstoppable_Stock_Alerts
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hooks

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html__( 'Almost there!', 'unstoppable-stock-alerts' ) . "\n\n";

printf(
    /* translators: %s: product name */
    esc_html__( 'Please confirm your email address to receive stock notifications for %s.', 'unstoppable-stock-alerts' ),
    esc_html( $product_name )
);

echo "\n\n";

echo esc_html__( 'Click the link below to confirm your email:', 'unstoppable-stock-alerts' ) . "\n";
echo esc_url( $verification_url ) . "\n\n";

echo "---\n";
echo esc_html__( 'Note: If you didn\'t request this notification, you can safely ignore this email.', 'unstoppable-stock-alerts' ) . "\n\n";

echo esc_html__( 'This verification link will expire in 24 hours.', 'unstoppable-stock-alerts' ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
