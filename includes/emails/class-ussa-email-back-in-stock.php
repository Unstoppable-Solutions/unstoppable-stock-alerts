<?php
/**
 * Back In Stock Email
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Email_Back_In_Stock Class.
 */
class USSA_Email_Back_In_Stock extends WC_Email {

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
        $this->id             = 'ussa_back_in_stock';
        $this->customer_email = true;
        $this->title          = __( 'Back In Stock Notification', 'unstoppable-stock-alerts' );
        $this->description    = __( 'Notification sent to customers when a product they subscribed to is back in stock.', 'unstoppable-stock-alerts' );
        $this->template_html  = 'emails/ussa-back-in-stock.php';
        $this->template_plain = 'emails/plain/ussa-back-in-stock.php';
        $this->template_base  = USSA_PLUGIN_DIR . 'templates/';
        $this->placeholders   = array(
            '{product_name}' => '',
            '{site_title}'   => $this->get_blogname(),
        );

        // Call parent constructor.
        parent::__construct();
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function get_default_subject() {
        return __( 'Good news! {product_name} is back in stock!', 'unstoppable-stock-alerts' );
    }

    /**
     * Get email heading.
     *
     * @return string
     */
    public function get_default_heading() {
        return __( '{product_name} is back in stock!', 'unstoppable-stock-alerts' );
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
        $placeholder_text = sprintf(
            /* translators: %s: list of placeholders */
            __( 'Available placeholders: %s', 'unstoppable-stock-alerts' ),
            '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>'
        );

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
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'unstoppable-stock-alerts' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'unstoppable-stock-alerts' ),
                'description' => __( 'Text to appear below the main email content.', 'unstoppable-stock-alerts' ),
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'unstoppable-stock-alerts' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ),
            'email_type' => array(
                'title'       => __( 'Email type', 'unstoppable-stock-alerts' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'unstoppable-stock-alerts' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }
}

return new USSA_Email_Back_In_Stock();
