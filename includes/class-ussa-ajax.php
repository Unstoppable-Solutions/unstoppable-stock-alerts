<?php
/**
 * AJAX Handler - Full AJAX support for forms
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Ajax Class.
 */
class USSA_Ajax {

    /**
     * Constructor.
     */
    public function __construct() {
        // Frontend AJAX actions.
        add_action( 'wp_ajax_ussa_subscribe', array( $this, 'subscribe' ) );
        add_action( 'wp_ajax_nopriv_ussa_subscribe', array( $this, 'subscribe' ) );
        add_action( 'wp_ajax_ussa_unsubscribe', array( $this, 'unsubscribe' ) );
        add_action( 'wp_ajax_nopriv_ussa_unsubscribe', array( $this, 'unsubscribe' ) );
        add_action( 'wp_ajax_ussa_verify', array( $this, 'verify' ) );
        add_action( 'wp_ajax_nopriv_ussa_verify', array( $this, 'verify' ) );

        // Admin AJAX actions.
        add_action( 'wp_ajax_ussa_admin_delete', array( $this, 'admin_delete_subscriber' ) );
        add_action( 'wp_ajax_ussa_admin_send', array( $this, 'admin_send_notification' ) );
        add_action( 'wp_ajax_ussa_admin_bulk', array( $this, 'admin_bulk_action' ) );
        add_action( 'wp_ajax_ussa_export', array( $this, 'admin_export' ) );
        add_action( 'wp_ajax_ussa_get_variation_form', array( $this, 'get_variation_form' ) );
    }

    /**
     * Subscribe to stock notification.
     */
    public function subscribe() {
        // Verify nonce.
        if ( ! check_ajax_referer( 'ussa_subscribe', 'nonce', false ) ) {
            wp_send_json_error( array(
                'message' => __( 'Security check failed. Please refresh the page and try again.', 'unstoppable-stock-alerts' ),
            ) );
        }

        // Get data.
        $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $email        = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

        // Validate product.
        if ( ! $product_id ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid product.', 'unstoppable-stock-alerts' ),
            ) );
        }

        $product = wc_get_product( $variation_id ? $variation_id : $product_id );
        if ( ! $product ) {
            wp_send_json_error( array(
                'message' => __( 'Product not found.', 'unstoppable-stock-alerts' ),
            ) );
        }

        // Check if product is in stock.
        if ( $product->is_in_stock() ) {
            wp_send_json_error( array(
                'message' => __( 'This product is already in stock!', 'unstoppable-stock-alerts' ),
            ) );
        }

        // Get user email.
        $user_id = 0;
        if ( is_user_logged_in() ) {
            $user    = wp_get_current_user();
            $user_id = $user->ID;
            $email   = $user->user_email;
        }

        // Validate email.
        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( array(
                'message' => __( 'Please enter a valid email address.', 'unstoppable-stock-alerts' ),
            ) );
        }

        // Check if already subscribed.
        $existing = USSA()->db->subscribers->get_by_email_and_product( $email, $product_id, $variation_id );
        if ( $existing ) {
            wp_send_json_error( array(
                'message' => __( 'You are already subscribed to notifications for this product.', 'unstoppable-stock-alerts' ),
                'already_subscribed' => true,
            ) );
        }

        // Generate verification key if double opt-in is enabled.
        $verification_key = '';
        $is_verified      = 'yes';
        if ( get_option( 'ussa_double_optin', 'no' ) === 'yes' && ! is_user_logged_in() ) {
            $verification_key = wp_generate_password( 32, false );
            $is_verified      = 'no';
        }

        // Create subscription.
        $subscriber_id = USSA()->db->subscribers->add( array(
            'product_id'       => $product_id,
            'variation_id'     => $variation_id,
            'user_id'          => $user_id,
            'email'            => $email,
            'verification_key' => $verification_key,
            'is_verified'      => $is_verified,
        ) );

        if ( ! $subscriber_id ) {
            wp_send_json_error( array(
                'message' => __( 'An error occurred. Please try again.', 'unstoppable-stock-alerts' ),
            ) );
        }

        // Send verification or confirmation email.
        if ( $is_verified === 'no' ) {
            USSA()->emails->send_verification( $subscriber_id );
            $message = __( 'Please check your email to confirm your subscription.', 'unstoppable-stock-alerts' );
        } else {
            USSA()->emails->send_confirmation( $subscriber_id );
            $message = get_option( 'ussa_success_message', __( 'You will be notified when this product is available.', 'unstoppable-stock-alerts' ) );
        }

        // Log activity.
        do_action( 'ussa_subscriber_created', $subscriber_id, $product_id, $variation_id, $email );

        wp_send_json_success( array(
            'message'       => $message,
            'subscriber_id' => $subscriber_id,
        ) );
    }

    /**
     * Unsubscribe from notification.
     */
    public function unsubscribe() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading to check if provided
        $subscriber_id = isset( $_REQUEST['subscriber_id'] ) ? absint( $_REQUEST['subscriber_id'] ) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading to check if provided
        $key           = isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading to check if provided
        $nonce         = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';

        if ( ! $subscriber_id ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid request.', 'unstoppable-stock-alerts' ),
            ) );
        }

        $subscriber = USSA()->db->subscribers->get( $subscriber_id );
        if ( ! $subscriber ) {
            wp_send_json_error( array(
                'message' => __( 'Subscription not found.', 'unstoppable-stock-alerts' ),
            ) );
        }

        // Verify authorization - must have valid nonce OR valid verification key.
        $is_authorized = false;

        // Method 1: Valid nonce (for AJAX calls, CSRF protected).
        if ( $nonce && wp_verify_nonce( $nonce, 'ussa_unsubscribe_' . $subscriber_id ) ) {
            // Nonce is valid - check ownership for logged-in users.
            if ( is_user_logged_in() ) {
                $user = wp_get_current_user();
                if ( $subscriber->email === $user->user_email || (int) $subscriber->user_id === $user->ID ) {
                    $is_authorized = true;
                }
            }
        }

        // Method 2: Valid verification key (for email links).
        if ( ! $is_authorized && $key && hash_equals( $subscriber->verification_key, $key ) ) {
            $is_authorized = true;
        }

        if ( ! $is_authorized ) {
            wp_send_json_error( array(
                'message' => __( 'You do not have permission to unsubscribe.', 'unstoppable-stock-alerts' ),
            ) );
        }

        // Update status.
        USSA()->db->subscribers->update( $subscriber_id, array(
            'status' => 'unsubscribed',
        ) );

        do_action( 'ussa_subscriber_unsubscribed', $subscriber_id );

        wp_send_json_success( array(
            'message' => __( 'You have been unsubscribed successfully.', 'unstoppable-stock-alerts' ),
        ) );
    }

    /**
     * Verify email subscription.
     */
    public function verify() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Uses verification key instead of nonce for email links
        $subscriber_id = isset( $_REQUEST['subscriber_id'] ) ? absint( $_REQUEST['subscriber_id'] ) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Uses verification key instead of nonce for email links
        $key           = isset( $_REQUEST['key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['key'] ) ) : '';

        if ( ! $subscriber_id || ! $key ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid verification link.', 'unstoppable-stock-alerts' ),
            ) );
        }

        $subscriber = USSA()->db->subscribers->get( $subscriber_id );
        if ( ! $subscriber ) {
            wp_send_json_error( array(
                'message' => __( 'Subscription not found.', 'unstoppable-stock-alerts' ),
            ) );
        }

        if ( $subscriber->is_verified === 'yes' ) {
            wp_send_json_success( array(
                'message' => __( 'Your email is already verified.', 'unstoppable-stock-alerts' ),
            ) );
        }

        if ( ! hash_equals( $subscriber->verification_key, $key ) ) {
            wp_send_json_error( array(
                'message' => __( 'Invalid verification key.', 'unstoppable-stock-alerts' ),
            ) );
        }

        // Verify.
        USSA()->db->subscribers->update( $subscriber_id, array(
            'is_verified' => 'yes',
        ) );

        // Send confirmation.
        USSA()->emails->send_confirmation( $subscriber_id );

        do_action( 'ussa_subscriber_verified', $subscriber_id );

        wp_send_json_success( array(
            'message' => __( 'Your email has been verified! You will be notified when the product is back in stock.', 'unstoppable-stock-alerts' ),
        ) );
    }

    /**
     * Admin: Delete subscriber.
     */
    public function admin_delete_subscriber() {
        check_ajax_referer( 'ussa_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'unstoppable-stock-alerts' ) ) );
        }

        $subscriber_id = isset( $_POST['subscriber_id'] ) ? absint( $_POST['subscriber_id'] ) : 0;

        if ( ! $subscriber_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid subscriber.', 'unstoppable-stock-alerts' ) ) );
        }

        $result = USSA()->db->subscribers->delete( $subscriber_id );

        if ( $result ) {
            wp_send_json_success( array( 'message' => __( 'Subscriber deleted.', 'unstoppable-stock-alerts' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to delete subscriber.', 'unstoppable-stock-alerts' ) ) );
        }
    }

    /**
     * Admin: Send notification manually.
     */
    public function admin_send_notification() {
        check_ajax_referer( 'ussa_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'unstoppable-stock-alerts' ) ) );
        }

        $subscriber_id = isset( $_POST['subscriber_id'] ) ? absint( $_POST['subscriber_id'] ) : 0;

        if ( ! $subscriber_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid subscriber.', 'unstoppable-stock-alerts' ) ) );
        }

        $subscriber = USSA()->db->subscribers->get( $subscriber_id );
        if ( ! $subscriber ) {
            wp_send_json_error( array( 'message' => __( 'Subscriber not found.', 'unstoppable-stock-alerts' ) ) );
        }

        $result = USSA()->emails->send_back_in_stock( $subscriber_id );

        if ( $result ) {
            USSA()->db->subscribers->mark_notified( $subscriber_id );
            wp_send_json_success( array( 'message' => __( 'Notification sent.', 'unstoppable-stock-alerts' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to send notification.', 'unstoppable-stock-alerts' ) ) );
        }
    }

    /**
     * Sanitize a value for CSV export to prevent formula injection (CWE-1236).
     *
     * @param mixed $value The value to sanitize.
     * @return string Sanitized value safe for CSV.
     */
    private function sanitize_csv_value( $value ) {
        if ( ! is_string( $value ) || '' === $value ) {
            return $value;
        }

        $dangerous_chars = array( '=', '+', '-', '@', "\t", "\r", "\n" );
        $first_char      = substr( $value, 0, 1 );

        if ( in_array( $first_char, $dangerous_chars, true ) ) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Admin: Export subscribers.
     */
    public function admin_export() {
        check_ajax_referer( 'ussa_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'unstoppable-stock-alerts' ) );
        }

        $product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
        $status     = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
        $date_from  = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
        $date_to    = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';

        $subscribers = USSA()->db->subscribers->export( array(
            'product_id' => $product_id,
            'status'     => $status,
            'date_from'  => $date_from,
            'date_to'    => $date_to,
        ) );

        // Generate CSV.
        $filename = 'stock-alerts-export-' . gmdate( 'Y-m-d-His' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );

        // CSV headers.
        fputcsv( $output, array(
            'ID',
            'Email',
            'Product ID',
            'Product Name',
            'Variation ID',
            'User ID',
            'Status',
            'Verified',
            'Subscribe Date',
            'Notified Date',
        ) );

        // CSV rows - sanitize to prevent formula injection.
        foreach ( $subscribers as $subscriber ) {
            fputcsv( $output, array_map( array( $this, 'sanitize_csv_value' ), array(
                $subscriber['id'],
                $subscriber['email'],
                $subscriber['product_id'],
                $subscriber['product_name'],
                $subscriber['variation_id'],
                $subscriber['user_id'],
                $subscriber['status'],
                $subscriber['is_verified'],
                $subscriber['subscribe_date'],
                $subscriber['notified_date'],
            ) ) );
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Writing to php://output for CSV download
        fclose( $output );
        exit;
    }

    /**
     * Get form for variation.
     */
    public function get_variation_form() {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public frontend endpoint, no state change
        $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public frontend endpoint, no state change
        $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

        if ( ! $variation_id || ! $product_id ) {
            wp_send_json_error();
        }

        $variation = wc_get_product( $variation_id );
        if ( ! $variation || $variation->is_in_stock() ) {
            wp_send_json_success( array( 'show_form' => false ) );
        }

        // Get subscriber count.
        $count = USSA()->db->subscribers->get_count_by_product( $product_id, $variation_id );

        wp_send_json_success( array(
            'show_form'        => true,
            'subscriber_count' => $count,
        ) );
    }

    /**
     * Admin: Bulk action.
     */
    public function admin_bulk_action() {
        check_ajax_referer( 'ussa_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'unstoppable-stock-alerts' ) ) );
        }

        $action = isset( $_POST['bulk_action'] ) ? sanitize_key( wp_unslash( $_POST['bulk_action'] ) ) : '';
        $ids    = isset( $_POST['subscriber_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['subscriber_ids'] ) ) : array();

        if ( empty( $action ) || empty( $ids ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid request.', 'unstoppable-stock-alerts' ) ) );
        }

        $success_count = 0;

        foreach ( $ids as $subscriber_id ) {
            switch ( $action ) {
                case 'delete':
                    if ( USSA()->db->subscribers->delete( $subscriber_id ) ) {
                        $success_count++;
                    }
                    break;

                case 'send':
                    $subscriber = USSA()->db->subscribers->get( $subscriber_id );
                    if ( $subscriber && USSA()->emails->send_back_in_stock( $subscriber_id ) ) {
                        USSA()->db->subscribers->mark_notified( $subscriber_id );
                        $success_count++;
                    }
                    break;
            }
        }

        $message = sprintf(
            /* translators: %d: number of items processed */
            __( 'Successfully processed %d subscriber(s).', 'unstoppable-stock-alerts' ),
            $success_count
        );

        wp_send_json_success( array( 'message' => $message ) );
    }
}
