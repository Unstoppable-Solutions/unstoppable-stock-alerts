<?php
/**
 * Email Verification Email
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Email_Verification Class.
 */
class USSA_Email_Verification extends WC_Email {

    /**
     * Subscriber object.
     *
     * @var object
     */
    public $subscriber;

    /**
     * Product object.
     *
     * @var WC_Product
     */
    public $product;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'ussa_verification';
        $this->customer_email = true;
        $this->title          = __( 'Stock Alert Email Verification', 'unstoppable-stock-alerts' );
        $this->description    = __( 'Verification email sent to confirm email address for stock alerts.', 'unstoppable-stock-alerts' );
        $this->template_html  = 'emails/ussa-verification.php';
        $this->template_plain = 'emails/plain/ussa-verification.php';
        $this->template_base  = USSA_PLUGIN_DIR . 'templates/';
        $this->placeholders   = array(
            '{product_name}' => '',
            '{site_title}'   => $this->get_blogname(),
        );

        parent::__construct();
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( 'Please verify your email for {product_name} stock alerts', 'unstoppable-stock-alerts' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Verify Your Email Address', 'unstoppable-stock-alerts' );
    }

    /**
     * Get verification URL.
     *
     * @return string
     */
    public function get_verification_url() {
        return add_query_arg( array(
            'ussa_action'   => 'verify',
            'subscriber_id' => $this->subscriber->id,
            'key'           => $this->subscriber->verification_key,
        ), home_url() );
    }

    /**
     * Trigger the email.
     *
     * @param int $subscriber_id Subscriber ID.
     * @return bool
     */
    public function trigger( $subscriber_id ) {
        $this->setup_locale();

        $this->subscriber = USSA()->db->subscribers->get( $subscriber_id );
        if ( ! $this->subscriber ) {
            return false;
        }

        $product_id     = $this->subscriber->variation_id ? $this->subscriber->variation_id : $this->subscriber->product_id;
        $this->product  = wc_get_product( $product_id );

        if ( ! $this->product ) {
            return false;
        }

        $this->recipient = $this->subscriber->email;
        $this->placeholders['{product_name}'] = $this->product->get_name();

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $result = $this->send(
                $this->get_recipient(),
                $this->get_subject(),
                $this->get_content(),
                $this->get_headers(),
                $this->get_attachments()
            );

            $this->restore_locale();
            return $result;
        }

        $this->restore_locale();
        return false;
    }

    /**
     * Get content HTML.
     *
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            array(
                'subscriber'       => $this->subscriber,
                'product'          => $this->product,
                'email_heading'    => $this->get_heading(),
                'verification_url' => $this->get_verification_url(),
                'sent_to_admin'    => false,
                'plain_text'       => false,
                'email'            => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html(
            $this->template_plain,
            array(
                'subscriber'       => $this->subscriber,
                'product'          => $this->product,
                'email_heading'    => $this->get_heading(),
                'verification_url' => $this->get_verification_url(),
                'sent_to_admin'    => false,
                'plain_text'       => true,
                'email'            => $this,
            ),
            '',
            $this->template_base
        );
    }

    /**
     * Initialize settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __( 'Enable/Disable', 'unstoppable-stock-alerts' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'unstoppable-stock-alerts' ),
                'default' => 'yes',
            ),
            'subject'    => array(
                'title'       => __( 'Subject', 'unstoppable-stock-alerts' ),
                'type'        => 'text',
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'unstoppable-stock-alerts' ),
                'type'        => 'text',
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'email_type' => array(
                'title'   => __( 'Email type', 'unstoppable-stock-alerts' ),
                'type'    => 'select',
                'default' => 'html',
                'options' => $this->get_email_type_options(),
            ),
        );
    }
}

return new USSA_Email_Verification();
