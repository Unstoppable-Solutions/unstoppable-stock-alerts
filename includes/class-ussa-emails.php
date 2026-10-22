<?php
/**
 * Email Handler
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Emails Class.
 */
class USSA_Emails {

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'woocommerce_email_classes', array( $this, 'register_emails' ) );
    }

    /**
     * Register WooCommerce email classes.
     *
     * @param array $emails Email classes.
     * @return array
     */
    public function register_emails( $emails ) {
        $emails['USSA_Email_Back_In_Stock']   = include USSA_PLUGIN_DIR . 'includes/emails/class-ussa-email-back-in-stock.php';
        $emails['USSA_Email_Confirmation']    = include USSA_PLUGIN_DIR . 'includes/emails/class-ussa-email-confirmation.php';
        $emails['USSA_Email_Verification']    = include USSA_PLUGIN_DIR . 'includes/emails/class-ussa-email-verification.php';
        return $emails;
    }

    /**
     * Send back in stock notification.
     *
     * @param int $subscriber_id Subscriber ID.
     * @return bool
     */
    public function send_back_in_stock( $subscriber_id ) {
        $subscriber = USSA()->db->subscribers->get( $subscriber_id );
        if ( ! $subscriber ) {
            return false;
        }

        $product = wc_get_product( $subscriber->variation_id ? $subscriber->variation_id : $subscriber->product_id );
        if ( ! $product ) {
            return false;
        }

        $mailer = WC()->mailer();
        $email  = $mailer->emails['USSA_Email_Back_In_Stock'];

        if ( $email ) {
            return $email->trigger( $subscriber_id );
        }

        return false;
    }

    /**
     * Send confirmation email.
     *
     * @param int $subscriber_id Subscriber ID.
     * @return bool
     */
    public function send_confirmation( $subscriber_id ) {
        $subscriber = USSA()->db->subscribers->get( $subscriber_id );
        if ( ! $subscriber ) {
            return false;
        }

        $mailer = WC()->mailer();
        $email  = $mailer->emails['USSA_Email_Confirmation'];

        if ( $email ) {
            return $email->trigger( $subscriber_id );
        }

        return false;
    }

    /**
     * Send verification email.
     *
     * @param int $subscriber_id Subscriber ID.
     * @return bool
     */
    public function send_verification( $subscriber_id ) {
        $subscriber = USSA()->db->subscribers->get( $subscriber_id );
        if ( ! $subscriber ) {
            return false;
        }

        $mailer = WC()->mailer();
        $email  = $mailer->emails['USSA_Email_Verification'];

        if ( $email ) {
            return $email->trigger( $subscriber_id );
        }

        return false;
    }

    /**
     * Get email headers.
     *
     * @return string
     */
    public function get_headers() {
        $from_name  = get_option( 'ussa_email_from_name', get_bloginfo( 'name' ) );
        $from_email = get_option( 'ussa_email_from_address', get_option( 'admin_email' ) );

        return 'Content-Type: text/html; charset=UTF-8' . "\r\n" .
               'From: ' . $from_name . ' <' . $from_email . '>' . "\r\n";
    }

    /**
     * Get unsubscribe URL.
     *
     * @param object $subscriber Subscriber object.
     * @return string
     */
    public function get_unsubscribe_url( $subscriber ) {
        return add_query_arg( array(
            'ussa_action'   => 'unsubscribe',
            'subscriber_id' => $subscriber->id,
            'key'           => $subscriber->verification_key,
        ), home_url() );
    }
}
