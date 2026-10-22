<?php
/**
 * Stock Alert Subscription Form Template
 *
 * This template displays the stock alert subscription form on out-of-stock products.
 * Override this template by copying it to yourtheme/unstoppable-stock-alerts/single-product/stock-alert-form.php
 *
 * @package Unstoppable_Stock_Alerts
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables

// Variables available:
// $product - WC_Product object
// $product_id - Product ID
// $variation_id - Variation ID (if applicable, 0 for simple products)
// $form_title - Form title text
// $button_text - Submit button text
// $success_message - Success message text
// $show_subscriber_count - Whether to show subscriber count
// $subscriber_count_text - Subscriber count text template
// $subscriber_count - Number of active subscribers
// $require_login - Whether login is required
// $user_email - Current user's email (if logged in)
// $is_subscribed - Whether current user is already subscribed

$wrapper_class = 'ussa-stock-alert-form';
if ( $is_subscribed ) {
    $wrapper_class .= ' ussa-already-subscribed';
}
?>

<div class="<?php echo esc_attr( $wrapper_class ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" data-variation-id="<?php echo esc_attr( $variation_id ); ?>">
    
    <?php if ( ! empty( $form_title ) ) : ?>
        <h4 class="ussa-form-title"><?php echo esc_html( $form_title ); ?></h4>
    <?php endif; ?>

    <?php if ( $is_subscribed ) : ?>
        
        <div class="ussa-subscribed-message">
            <span class="ussa-icon ussa-icon-check">✓</span>
            <p><?php esc_html_e( "You're already subscribed to receive notifications when this product is back in stock.", 'unstoppable-stock-alerts' ); ?></p>
            <button type="button" class="ussa-unsubscribe-btn button">
                <?php esc_html_e( 'Unsubscribe', 'unstoppable-stock-alerts' ); ?>
            </button>
        </div>

    <?php elseif ( $require_login && ! is_user_logged_in() ) : ?>
        
        <div class="ussa-login-required">
            <p>
                <?php 
                printf(
                    /* translators: %1$s: login link start, %2$s: login link end */
                    esc_html__( 'Please %1$slog in%2$s to receive stock notifications.', 'unstoppable-stock-alerts' ),
                    '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">',
                    '</a>'
                ); 
                ?>
            </p>
        </div>

    <?php else : ?>
        
        <form class="ussa-subscribe-form" method="post">
            <div class="ussa-form-row">
                <label for="ussa-email-<?php echo esc_attr( $product_id ); ?>" class="ussa-label screen-reader-text">
                    <?php esc_html_e( 'Email address', 'unstoppable-stock-alerts' ); ?>
                </label>
                <input 
                    type="email" 
                    id="ussa-email-<?php echo esc_attr( $product_id ); ?>" 
                    name="ussa_email" 
                    class="ussa-email-input" 
                    placeholder="<?php esc_attr_e( 'Enter your email address', 'unstoppable-stock-alerts' ); ?>"
                    value="<?php echo esc_attr( $user_email ); ?>"
                    required
                    <?php echo ! empty( $user_email ) ? 'readonly' : ''; ?>
                />
                <button type="submit" class="ussa-submit-btn button alt">
                    <span class="ussa-btn-text"><?php echo esc_html( $button_text ); ?></span>
                    <span class="ussa-btn-loading" style="display:none;">
                        <span class="ussa-spinner"></span>
                        <?php esc_html_e( 'Subscribing...', 'unstoppable-stock-alerts' ); ?>
                    </span>
                </button>
            </div>
            
            <div class="ussa-form-messages" style="display:none;">
                <div class="ussa-message ussa-message-success" style="display:none;"></div>
                <div class="ussa-message ussa-message-error" style="display:none;"></div>
            </div>

            <input type="hidden" name="ussa_product_id" value="<?php echo esc_attr( $product_id ); ?>" />
            <input type="hidden" name="ussa_variation_id" value="<?php echo esc_attr( $variation_id ); ?>" />
            <?php wp_nonce_field( 'ussa_subscribe', 'ussa_nonce' ); ?>
        </form>

        <?php if ( 'yes' === $show_subscriber_count && $subscriber_count > 0 ) : ?>
            <p class="ussa-subscriber-count">
                <?php 
                echo esc_html( 
                    sprintf( 
                        $subscriber_count_text, 
                        number_format_i18n( $subscriber_count ) 
                    ) 
                ); 
                ?>
            </p>
        <?php endif; ?>

    <?php endif; ?>

</div>
