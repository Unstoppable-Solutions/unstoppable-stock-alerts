<?php
/**
 * Email Verification Email (HTML)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/ussa-verification.php
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

<p><?php esc_html_e( 'Almost there!', 'unstoppable-stock-alerts' ); ?></p>

<p>
    <?php
    printf(
        /* translators: %s: product name */
        esc_html__( 'Please confirm your email address to receive stock notifications for %s.', 'unstoppable-stock-alerts' ),
        '<strong>' . esc_html( $product_name ) . '</strong>'
    );
    ?>
</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?php echo esc_url( $verification_url ); ?>" style="background-color: #7f54b3; color: #ffffff; padding: 15px 40px; text-decoration: none; border-radius: 4px; display: inline-block; font-weight: bold; font-size: 16px;">
        <?php esc_html_e( 'Confirm Email Address', 'unstoppable-stock-alerts' ); ?>
    </a>
</p>

<p style="text-align: center; color: #666;"><?php esc_html_e( 'Or copy and paste this link into your browser:', 'unstoppable-stock-alerts' ); ?></p>
<p style="text-align: center; word-break: break-all;"><a href="<?php echo esc_url( $verification_url ); ?>"><?php echo esc_url( $verification_url ); ?></a></p>

<?php if ( $product_image ) : ?>
<div style="text-align: center; margin: 20px 0;">
    <a href="<?php echo esc_url( $product_url ); ?>">
        <?php echo wp_kses_post( $product_image ); ?>
    </a>
</div>
<?php endif; ?>

<div style="background-color: #fff8e5; border: 1px solid #ffcc00; padding: 15px 20px; border-radius: 4px; margin: 20px 0;">
    <p style="margin: 0; color: #856404;">
        <strong><?php esc_html_e( 'Note:', 'unstoppable-stock-alerts' ); ?></strong>
        <?php esc_html_e( "If you didn't request this notification, you can safely ignore this email.", 'unstoppable-stock-alerts' ); ?>
    </p>
</div>

<p style="font-size: 12px; color: #666; margin-top: 30px;">
    <?php esc_html_e( 'This verification link will expire in 24 hours.', 'unstoppable-stock-alerts' ); ?>
</p>

<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
