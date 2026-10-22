<?php
/**
 * Back In Stock Notification Email (Plain Text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/ussa-back-in-stock.php
 *
 * @package Unstoppable_Stock_Alerts
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hooks

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html__( 'Great news!', 'unstoppable-stock-alerts' ) . "\n\n";

printf(
    /* translators: %s: product name */
    esc_html__( 'The product you were waiting for is now back in stock: %s', 'unstoppable-stock-alerts' ),
    esc_html( $product_name )
);

echo "\n\n";

echo esc_html__( 'Buy it now before it sells out again:', 'unstoppable-stock-alerts' ) . "\n";
echo esc_url( $product_url ) . "\n\n";

echo esc_html__( "Don't wait too long – popular items sell out quickly!", 'unstoppable-stock-alerts' ) . "\n\n";

if ( $unsubscribe_url ) {
    echo "---\n";
    printf(
        /* translators: %s: unsubscribe URL */
        esc_html__( 'To unsubscribe from notifications for this product, visit: %s', 'unstoppable-stock-alerts' ),
        esc_url( $unsubscribe_url )
    );
    echo "\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
