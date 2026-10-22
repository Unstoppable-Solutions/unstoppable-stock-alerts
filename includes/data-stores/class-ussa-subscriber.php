<?php
/**
 * Subscriber Data Object
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Subscriber Class.
 */
class USSA_Subscriber {

    /**
     * Subscriber data.
     *
     * @var object
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param int|object $subscriber Subscriber ID or object.
     */
    public function __construct( $subscriber = 0 ) {
        if ( is_numeric( $subscriber ) && $subscriber > 0 ) {
            $this->data = USSA()->db->subscribers->get( $subscriber );
        } elseif ( is_object( $subscriber ) ) {
            $this->data = $subscriber;
        }
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function get_id() {
        return isset( $this->data->id ) ? absint( $this->data->id ) : 0;
    }

    /**
     * Get product ID.
     *
     * @return int
     */
    public function get_product_id() {
        return isset( $this->data->product_id ) ? absint( $this->data->product_id ) : 0;
    }

    /**
     * Get variation ID.
     *
     * @return int
     */
    public function get_variation_id() {
        return isset( $this->data->variation_id ) ? absint( $this->data->variation_id ) : 0;
    }

    /**
     * Get user ID.
     *
     * @return int
     */
    public function get_user_id() {
        return isset( $this->data->user_id ) ? absint( $this->data->user_id ) : 0;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function get_email() {
        return isset( $this->data->email ) ? $this->data->email : '';
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function get_status() {
        return isset( $this->data->status ) ? $this->data->status : 'active';
    }

    /**
     * Is verified.
     *
     * @return bool
     */
    public function is_verified() {
        return isset( $this->data->is_verified ) && $this->data->is_verified === 'yes';
    }

    /**
     * Is queued.
     *
     * @return bool
     */
    public function is_queued() {
        return isset( $this->data->is_queued ) && $this->data->is_queued === 'yes';
    }

    /**
     * Get subscribe date.
     *
     * @return string
     */
    public function get_subscribe_date() {
        return isset( $this->data->subscribe_date ) ? $this->data->subscribe_date : '';
    }

    /**
     * Get notified date.
     *
     * @return string
     */
    public function get_notified_date() {
        return isset( $this->data->notified_date ) ? $this->data->notified_date : '';
    }

    /**
     * Was notified.
     *
     * @return bool
     */
    public function was_notified() {
        return ! empty( $this->data->notified_date );
    }

    /**
     * Get product.
     *
     * @return WC_Product|null
     */
    public function get_product() {
        $product_id = $this->get_variation_id() ? $this->get_variation_id() : $this->get_product_id();
        return wc_get_product( $product_id );
    }

    /**
     * Get product name.
     *
     * @return string
     */
    public function get_product_name() {
        return USSA_Product::get_product_name( $this->get_product_id(), $this->get_variation_id() );
    }

    /**
     * Exists.
     *
     * @return bool
     */
    public function exists() {
        return $this->get_id() > 0;
    }
}
