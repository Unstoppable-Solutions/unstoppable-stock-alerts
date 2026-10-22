<?php
/**
 * Installation Handler
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Install Class.
 */
class USSA_Install {

    /**
     * DB updates that need to be run per version.
     *
     * @var array
     */
    private static $db_updates = array(
        '1.0.0' => array(
            'ussa_create_tables',
        ),
    );

    /**
     * Install.
     */
    public static function install() {
        if ( ! is_blog_installed() ) {
            return;
        }

        // Check if we are not already running.
        if ( get_transient( 'ussa_installing' ) === 'yes' ) {
            return;
        }

        set_transient( 'ussa_installing', 'yes', MINUTE_IN_SECONDS * 10 );

        self::create_tables();
        self::create_options();
        self::update_version();

        delete_transient( 'ussa_installing' );

        do_action( 'ussa_installed' );
    }

    /**
     * Create database tables.
     */
    private static function create_tables() {
        global $wpdb;

        $wpdb->hide_errors();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $table_name      = $wpdb->prefix . 'ussa_subscribers';

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            product_id bigint(20) UNSIGNED NOT NULL,
            variation_id bigint(20) UNSIGNED DEFAULT 0,
            user_id bigint(20) UNSIGNED DEFAULT 0,
            email varchar(200) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            is_verified varchar(3) NOT NULL DEFAULT 'no',
            verification_key varchar(64) DEFAULT NULL,
            subscribe_date datetime NOT NULL,
            notified_date datetime DEFAULT NULL,
            queued_date datetime DEFAULT NULL,
            is_queued varchar(3) NOT NULL DEFAULT 'no',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY variation_id (variation_id),
            KEY user_id (user_id),
            KEY email (email),
            KEY status (status),
            KEY is_queued (is_queued)
        ) $charset_collate;";

        dbDelta( $sql );

        // Activity log table.
        $activity_table = $wpdb->prefix . 'ussa_activity';

        $sql = "CREATE TABLE {$activity_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            subscriber_id bigint(20) UNSIGNED NOT NULL,
            type varchar(50) NOT NULL,
            message text,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY subscriber_id (subscriber_id),
            KEY type (type)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Create default options.
     */
    private static function create_options() {
        $defaults = array(
            'ussa_form_title'              => __( 'Get notified when this product is back in stock!', 'unstoppable-stock-alerts' ),
            'ussa_button_text'             => __( 'Notify Me', 'unstoppable-stock-alerts' ),
            'ussa_success_message'         => __( 'You will be notified when this product is available.', 'unstoppable-stock-alerts' ),
            'ussa_show_subscriber_count'   => 'no',
            'ussa_subscriber_count_text'   => __( '{count} customers are waiting for this product.', 'unstoppable-stock-alerts' ),
            'ussa_require_login'           => 'no',
            'ussa_double_optin'            => 'no',
            'ussa_email_from_name'         => get_bloginfo( 'name' ),
            'ussa_email_from_address'      => get_option( 'admin_email' ),
            'ussa_batch_size'              => 50,
            'ussa_throttle_hours'          => 24,
        );

        foreach ( $defaults as $key => $value ) {
            if ( get_option( $key ) === false ) {
                update_option( $key, $value );
            }
        }
    }

    /**
     * Update DB version.
     */
    private static function update_version() {
        update_option( 'ussa_version', USSA_VERSION );
    }
}
