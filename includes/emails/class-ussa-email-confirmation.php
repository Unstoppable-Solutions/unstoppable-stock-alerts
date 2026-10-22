<?php
/**
 * Subscription Confirmation Email
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Email_Confirmation Class.
 */
class USSA_Email_Confirmation extends WC_Email {

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
        $this->id             = 'ussa_confirmation';
        $this->customer_email = true;
        $this->title          = __( 'Stock Alert Subscription Confirmation', 'unstoppable-stock-alerts' );
        $this->description    = __( 'Confirmation email sent when a customer subscribes to stock alerts.', 'unstoppable-stock-alerts' );
        $this->template_html  = 'emails/ussa-confirmation.php';
        $this->template_plain = 'emails/plain/ussa-confirmation.php';
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
        return __( 'You are subscribed to {product_name} stock alerts', 'unstoppable-stock-alerts' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Stock Alert Confirmed', 'unstoppable-stock-alerts' );
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
                'subscriber'     => $this->subscriber,
                'product'        => $this->product,
                'email_heading'  => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'  => false,
                'plain_text'     => false,
                'email'          => $this,
                'unsubscribe_url' => USSA()->emails->get_unsubscribe_url( $this->subscriber ),
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
                'subscriber'     => $this->subscriber,
                'product'        => $this->product,
                'email_heading'  => $this->get_heading(),
                'additional_content' => $this->get_additional_content(),
                'sent_to_admin'  => false,
                'plain_text'     => true,
                'email'          => $this,
                'unsubscribe_url' => USSA()->emails->get_unsubscribe_url( $this->subscriber ),
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

return new USSA_Email_Confirmation();
