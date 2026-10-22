<?php
/**
 * Database Handler
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_DB Class.
 */
class USSA_DB {

    /**
     * Subscribers DB handler.
     *
     * @var USSA_Subscribers_DB
     */
    public $subscribers;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->subscribers = new USSA_Subscribers_DB();
    }
}
