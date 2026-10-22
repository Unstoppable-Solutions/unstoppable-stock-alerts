<?php
/**
 * Back In Stock Notification Email (HTML)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/ussa-back-in-stock.php
 *
 * @package Unstoppable_Stock_Alerts
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hooks

// Get product details
$product_name  = $product->get_name();
$product_url   = $product->get_permalink();
$product_image = $product->get_image( 'woocommerce_thumbnail' );

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( 'Great news!', 'unstoppable-stock-alerts' ); ?></p>

<p>
    <?php
    printf(
        /* translators: %s: product name */
        esc_html__( 'The product you were waiting for is now back in stock: %s', 'unstoppable-stock-alerts' ),
        '<strong>' . esc_html( $product_name ) . '</strong>'
    );
    ?>
</p>

<?php if ( $product_image ) : ?>
<div style="text-align: center; margin: 20px 0;">
    <a href="<?php echo esc_url( $product_url ); ?>">
        <?php echo wp_kses_post( $product_image ); ?>
    </a>
</div>
<?php endif; ?>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?php echo esc_url( $product_url ); ?>" style="background-color: #7f54b3; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold;">
        <?php esc_html_e( 'Buy Now', 'unstoppable-stock-alerts' ); ?>
    </a>
</p>

<p><?php esc_html_e( "Don't wait too long – popular items sell out quickly!", 'unstoppable-stock-alerts' ); ?></p>

<?php if ( $unsubscribe_url ) : ?>
<p style="font-size: 12px; color: #666; margin-top: 30px;">
    <?php
    printf(
        /* translators: %s: unsubscribe link */
        esc_html__( 'No longer interested? %s from stock notifications for this product.', 'unstoppable-stock-alerts' ),
        '<a href="' . esc_url( $unsubscribe_url ) . '">' . esc_html__( 'Unsubscribe', 'unstoppable-stock-alerts' ) . '</a>'
    );
    ?>
</p>
<?php endif; ?>

<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
