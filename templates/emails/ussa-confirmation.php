<?php
/**
 * Subscription Confirmation Email (HTML)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/ussa-confirmation.php
 *
 * @package Unstoppable_Stock_Alerts
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hooks

// Get product and subscriber details
$product_name     = $product->get_name();
$product_url      = $product->get_permalink();
$product_image    = $product->get_image( 'woocommerce_thumbnail' );
$subscriber_email = $subscriber->email;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php esc_html_e( "You're all set!", 'unstoppable-stock-alerts' ); ?></p>

<p>
    <?php
    printf(
        /* translators: %s: product name */
        esc_html__( "We'll notify you as soon as %s is back in stock.", 'unstoppable-stock-alerts' ),
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

<div style="background-color: #f7f7f7; padding: 15px 20px; border-radius: 4px; margin: 20px 0;">
    <p style="margin: 0;"><strong><?php esc_html_e( 'Product:', 'unstoppable-stock-alerts' ); ?></strong> <?php echo esc_html( $product_name ); ?></p>
    <p style="margin: 10px 0 0;"><strong><?php esc_html_e( 'Email:', 'unstoppable-stock-alerts' ); ?></strong> <?php echo esc_html( $subscriber_email ); ?></p>
</div>

<p><?php esc_html_e( 'In the meantime, feel free to browse our other products:', 'unstoppable-stock-alerts' ); ?></p>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" style="background-color: #7f54b3; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold;">
        <?php esc_html_e( 'Continue Shopping', 'unstoppable-stock-alerts' ); ?>
    </a>
</p>

<?php if ( $unsubscribe_url ) : ?>
<p style="font-size: 12px; color: #666; margin-top: 30px;">
    <?php
    printf(
        /* translators: %s: unsubscribe link */
        esc_html__( 'Changed your mind? %s from stock notifications for this product.', 'unstoppable-stock-alerts' ),
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
