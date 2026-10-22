<?php
/**
 * Subscription Confirmation Email (Plain Text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/ussa-confirmation.php
 *
 * @package Unstoppable_Stock_Alerts
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hooks

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html__( "You're all set!", 'unstoppable-stock-alerts' ) . "\n\n";

printf(
    /* translators: %s: product name */
    esc_html__( "We'll notify you as soon as %s is back in stock.", 'unstoppable-stock-alerts' ),
    esc_html( $product_name )
);

echo "\n\n";

echo esc_html__( 'Subscription Details:', 'unstoppable-stock-alerts' ) . "\n";
echo "---\n";
/* translators: %s: product name */
printf( esc_html__( 'Product: %s', 'unstoppable-stock-alerts' ), esc_html( $product_name ) );
echo "\n";
/* translators: %s: subscriber email address */
printf( esc_html__( 'Email: %s', 'unstoppable-stock-alerts' ), esc_html( $subscriber_email ) );
echo "\n\n";

echo esc_html__( 'View the product:', 'unstoppable-stock-alerts' ) . "\n";
echo esc_url( $product_url ) . "\n\n";

echo esc_html__( 'In the meantime, feel free to browse our other products:', 'unstoppable-stock-alerts' ) . "\n";
echo esc_url( wc_get_page_permalink( 'shop' ) ) . "\n\n";

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
