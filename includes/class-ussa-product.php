<?php
/**
 * Product Handler
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Product Class.
 */
class USSA_Product {

    /**
     * Constructor.
     */
    public function __construct() {
        // Display form on product pages.
        add_action( 'woocommerce_single_product_summary', array( $this, 'display_form' ), 31 );
        add_action( 'woocommerce_after_variations_form', array( $this, 'display_variation_form' ) );

        // Stock change hooks.
        add_action( 'woocommerce_product_set_stock_status', array( $this, 'stock_status_changed' ), 10, 3 );
        add_action( 'woocommerce_variation_set_stock_status', array( $this, 'stock_status_changed' ), 10, 3 );
    }

    /**
     * Display notification form for simple products.
     */
    public function display_form() {
        global $product;

        if ( ! $product ) {
            return;
        }

        // Only for simple products that are out of stock.
        if ( $product->is_type( 'variable' ) ) {
            return;
        }

        if ( $product->is_in_stock() ) {
            return;
        }

        $this->render_form( $product->get_id(), 0 );
    }

    /**
     * Display form for variable products.
     */
    public function display_variation_form() {
        global $product;

        if ( ! $product || ! $product->is_type( 'variable' ) ) {
            return;
        }

        // Container for AJAX-loaded form.
        ?>
        <div id="ussa-variation-form-container" class="ussa-variation-form-container" style="display: none;">
            <?php $this->render_form( $product->get_id(), 0, true ); ?>
        </div>
        <?php
    }

    /**
     * Render the notification form.
     *
     * @param int  $product_id   Product ID.
     * @param int  $variation_id Variation ID.
     * @param bool $is_variable  Is variable product.
     */
    public function render_form( $product_id, $variation_id = 0, $is_variable = false ) {
        $product = wc_get_product( $product_id );
        if ( ! $product ) {
            return;
        }

        // Template variables
        $form_title              = get_option( 'ussa_form_title', __( 'Get notified when this product is back in stock!', 'unstoppable-stock-alerts' ) );
        $button_text             = get_option( 'ussa_button_text', __( 'Notify Me', 'unstoppable-stock-alerts' ) );
        $success_message         = get_option( 'ussa_success_message', __( 'You will be notified when this product is back in stock!', 'unstoppable-stock-alerts' ) );
        $show_subscriber_count   = get_option( 'ussa_show_subscriber_count', 'no' );
        /* translators: %s: number of people waiting */
        $subscriber_count_text   = get_option( 'ussa_subscriber_count_text', __( '%s people are waiting for this product', 'unstoppable-stock-alerts' ) );
        $require_login           = get_option( 'ussa_require_login', 'no' ) === 'yes';

        // User email
        $user_email = '';
        if ( is_user_logged_in() ) {
            $user       = wp_get_current_user();
            $user_email = $user->user_email;
        }

        // Get subscriber count
        $subscriber_count = 0;
        if ( 'yes' === $show_subscriber_count && ! $is_variable ) {
            $subscriber_count = USSA()->db->subscribers->get_count_by_product( $product_id, $variation_id );
        }

        // Check if already subscribed
        $is_subscribed = false;
        if ( ! empty( $user_email ) ) {
            $existing = USSA()->db->subscribers->get_by_email_and_product( $user_email, $product_id, $variation_id );
            if ( $existing && $existing->status === 'active' ) {
                $is_subscribed = true;
            }
        }

        // Load template
        include USSA_PLUGIN_DIR . 'templates/single-product/stock-alert-form.php';
    }

    /**
     * Handle stock status change.
     *
     * @param int    $product_id Product ID.
     * @param string $status     New stock status.
     * @param object $product    Product object.
     */
    public function stock_status_changed( $product_id, $status, $product ) {
        // Only trigger when product becomes in stock.
        if ( $status !== 'instock' ) {
            return;
        }

        // Check if this is a variation.
        $variation_id = 0;
        $parent_id    = $product_id;

        if ( $product->is_type( 'variation' ) ) {
            $variation_id = $product_id;
            $parent_id    = $product->get_parent_id();
        }

        // Queue notifications.
        $queued = USSA()->db->subscribers->queue_for_notification( $parent_id, $variation_id );

        if ( $queued > 0 ) {
            do_action( 'ussa_notifications_queued', $parent_id, $variation_id, $queued );
        }
    }

    /**
     * Get product display name with variation.
     *
     * @param int $product_id   Product ID.
     * @param int $variation_id Variation ID.
     * @return string
     */
    public static function get_product_name( $product_id, $variation_id = 0 ) {
        $product = wc_get_product( $variation_id ? $variation_id : $product_id );

        if ( ! $product ) {
            return '';
        }

        $name = $product->get_name();

        if ( $variation_id && $product->is_type( 'variation' ) ) {
            $attributes = $product->get_variation_attributes();
            if ( ! empty( $attributes ) ) {
                $attr_display = array();
                foreach ( $attributes as $key => $value ) {
                    $attr_display[] = wc_attribute_label( str_replace( 'attribute_', '', $key ) ) . ': ' . $value;
                }
                $name .= ' - ' . implode( ', ', $attr_display );
            }
        }

        return $name;
    }
}
