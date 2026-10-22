<?php
/**
 * Subscribers Database Handler
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

/**
 * USSA_Subscribers_DB Class.
 */
class USSA_Subscribers_DB {

    /**
     * Table name.
     *
     * @var string
     */
    private $table;

    /**
     * Whether table exists.
     *
     * @var bool|null
     */
    private $table_exists = null;

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'ussa_subscribers';
    }

    /**
     * Check if table exists and create if missing.
     *
     * @return bool
     */
    public function ensure_table_exists() {
        if ( $this->table_exists !== null ) {
            return $this->table_exists;
        }

        global $wpdb;

        // Check if table exists.
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $this->table
            )
        );

        if ( $table_exists ) {
            $this->table_exists = true;
            return true;
        }

        // Table doesn't exist, create it.
        require_once USSA_PLUGIN_DIR . 'includes/class-ussa-install.php';
        USSA_Install::install();

        $this->table_exists = true;
        return true;
    }

    /**
     * Get table name.
     *
     * @return string
     */
    public function get_table() {
        return $this->table;
    }

    /**
     * Add a subscriber.
     *
     * @param array $data Subscriber data.
     * @return int|false Subscriber ID or false on failure.
     */
    public function add( $data ) {
        global $wpdb;

        $defaults = array(
            'product_id'       => 0,
            'variation_id'     => 0,
            'user_id'          => 0,
            'email'            => '',
            'status'           => 'active',
            'is_verified'      => 'no',
            'verification_key' => '',
            'subscribe_date'   => current_time( 'mysql' ),
            'notified_date'    => null,
            'queued_date'      => null,
            'is_queued'        => 'no',
            'created_at'       => current_time( 'mysql' ),
            'updated_at'       => current_time( 'mysql' ),
        );

        $data = wp_parse_args( $data, $defaults );

        $result = $wpdb->insert(
            $this->table,
            array(
                'product_id'       => absint( $data['product_id'] ),
                'variation_id'     => absint( $data['variation_id'] ),
                'user_id'          => absint( $data['user_id'] ),
                'email'            => sanitize_email( $data['email'] ),
                'status'           => sanitize_key( $data['status'] ),
                'is_verified'      => $data['is_verified'],
                'verification_key' => sanitize_text_field( $data['verification_key'] ),
                'subscribe_date'   => $data['subscribe_date'],
                'notified_date'    => $data['notified_date'],
                'queued_date'      => $data['queued_date'],
                'is_queued'        => $data['is_queued'],
                'created_at'       => $data['created_at'],
                'updated_at'       => $data['updated_at'],
            ),
            array( '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( $result ) {
            return $wpdb->insert_id;
        }

        return false;
    }

    /**
     * Get a subscriber by ID.
     *
     * @param int $id Subscriber ID.
     * @return object|null
     */
    public function get( $id ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE id = %d",
                absint( $id )
            )
        );
    }

    /**
     * Get subscriber by email and product.
     *
     * @param string $email      Email address.
     * @param int    $product_id Product ID.
     * @param int    $variation_id Variation ID.
     * @return object|null
     */
    public function get_by_email_and_product( $email, $product_id, $variation_id = 0 ) {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} 
                WHERE email = %s 
                AND product_id = %d 
                AND variation_id = %d 
                AND status = 'active'",
                sanitize_email( $email ),
                absint( $product_id ),
                absint( $variation_id )
            )
        );
    }

    /**
     * Update a subscriber.
     *
     * @param int   $id   Subscriber ID.
     * @param array $data Data to update.
     * @return bool
     */
    public function update( $id, $data ) {
        global $wpdb;

        $data['updated_at'] = current_time( 'mysql' );

        $formats = array();
        foreach ( $data as $key => $value ) {
            if ( in_array( $key, array( 'product_id', 'variation_id', 'user_id' ), true ) ) {
                $formats[] = '%d';
            } else {
                $formats[] = '%s';
            }
        }

        return $wpdb->update(
            $this->table,
            $data,
            array( 'id' => absint( $id ) ),
            $formats,
            array( '%d' )
        );
    }

    /**
     * Delete a subscriber.
     *
     * @param int $id Subscriber ID.
     * @return bool
     */
    public function delete( $id ) {
        global $wpdb;

        return $wpdb->delete(
            $this->table,
            array( 'id' => absint( $id ) ),
            array( '%d' )
        );
    }

    /**
     * Query subscribers.
     *
     * @param array $args Query arguments.
     * @return array|int
     */
    public function query( $args = array() ) {
        $this->ensure_table_exists();
        
        global $wpdb;

        $defaults = array(
            'product_id'   => 0,
            'variation_id' => null,
            'user_id'      => null,
            'email'        => '',
            'status'       => '',
            'is_queued'    => '',
            'is_verified'  => '',
            'search'       => '',
            'orderby'      => 'id',
            'order'        => 'DESC',
            'limit'        => 20,
            'offset'       => 0,
            'count'        => false,
        );

        $args = wp_parse_args( $args, $defaults );

        // Build query.
        $select = $args['count'] ? 'COUNT(*)' : '*';
        $sql    = "SELECT {$select} FROM {$this->table} WHERE 1=1";
        $values = array();

        if ( $args['product_id'] ) {
            $sql     .= ' AND product_id = %d';
            $values[] = absint( $args['product_id'] );
        }

        if ( $args['variation_id'] !== null ) {
            $sql     .= ' AND variation_id = %d';
            $values[] = absint( $args['variation_id'] );
        }

        if ( $args['user_id'] !== null ) {
            $sql     .= ' AND user_id = %d';
            $values[] = absint( $args['user_id'] );
        }

        if ( $args['email'] ) {
            $sql     .= ' AND email = %s';
            $values[] = sanitize_email( $args['email'] );
        }

        if ( $args['status'] ) {
            $sql     .= ' AND status = %s';
            $values[] = sanitize_key( $args['status'] );
        }

        if ( $args['is_queued'] ) {
            $sql     .= ' AND is_queued = %s';
            $values[] = $args['is_queued'];
        }

        if ( $args['is_verified'] ) {
            $sql     .= ' AND is_verified = %s';
            $values[] = $args['is_verified'];
        }

        if ( $args['search'] ) {
            $sql     .= ' AND email LIKE %s';
            $values[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        }

        // Prepare if we have values.
        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL is built safely with placeholders above
            $sql = $wpdb->prepare( $sql, $values );
        }

        if ( $args['count'] ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL built safely above
            return (int) $wpdb->get_var( $sql );
        }

        // Add ordering.
        $allowed_orderby = array( 'id', 'email', 'product_id', 'subscribe_date', 'created_at' );
        $orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'id';
        $order           = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
        $sql            .= " ORDER BY {$orderby} {$order}";

        // Add limit.
        if ( $args['limit'] > 0 ) {
            $sql .= $wpdb->prepare( ' LIMIT %d OFFSET %d', $args['limit'], $args['offset'] );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL built safely above
        $results = $wpdb->get_results( $sql );

        return is_array( $results ) ? $results : array();
    }

    /**
     * Get subscribers count by product.
     *
     * @param int $product_id   Product ID.
     * @param int $variation_id Variation ID.
     * @return int
     */
    public function get_count_by_product( $product_id, $variation_id = 0 ) {
        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table} 
                WHERE product_id = %d 
                AND variation_id = %d 
                AND status = 'active'
                AND (notified_date IS NULL OR subscribe_date > notified_date)",
                absint( $product_id ),
                absint( $variation_id )
            )
        );
    }

    /**
     * Get all subscribers for a product awaiting notification.
     *
     * @param int $product_id   Product ID.
     * @param int $variation_id Variation ID.
     * @return array
     */
    public function get_awaiting_notification( $product_id, $variation_id = 0 ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} 
                WHERE product_id = %d 
                AND variation_id = %d 
                AND status = 'active'
                AND is_queued = 'no'
                AND (notified_date IS NULL OR subscribe_date > notified_date)
                ORDER BY subscribe_date ASC",
                absint( $product_id ),
                absint( $variation_id )
            )
        );
    }

    /**
     * Queue subscribers for notification.
     *
     * @param int $product_id   Product ID.
     * @param int $variation_id Variation ID.
     * @return int Number of queued subscribers.
     */
    public function queue_for_notification( $product_id, $variation_id = 0 ) {
        global $wpdb;

        return $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->table} 
                SET is_queued = 'yes', queued_date = %s, updated_at = %s
                WHERE product_id = %d 
                AND variation_id = %d 
                AND status = 'active'
                AND is_queued = 'no'
                AND (notified_date IS NULL OR subscribe_date > notified_date)",
                current_time( 'mysql' ),
                current_time( 'mysql' ),
                absint( $product_id ),
                absint( $variation_id )
            )
        );
    }

    /**
     * Get queued subscribers.
     *
     * @param int $limit Limit.
     * @return array
     */
    public function get_queued( $limit = 50 ) {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} 
                WHERE is_queued = 'yes' 
                AND status = 'active'
                ORDER BY queued_date ASC
                LIMIT %d",
                absint( $limit )
            )
        );
    }

    /**
     * Mark as notified.
     *
     * @param int $id Subscriber ID.
     * @return bool
     */
    public function mark_notified( $id ) {
        global $wpdb;

        return $wpdb->update(
            $this->table,
            array(
                'notified_date' => current_time( 'mysql' ),
                'is_queued'     => 'no',
                'updated_at'    => current_time( 'mysql' ),
            ),
            array( 'id' => absint( $id ) ),
            array( '%s', '%s', '%s' ),
            array( '%d' )
        );
    }

    /**
     * Get statistics.
     *
     * @return array
     */
    public function get_stats() {
        $this->ensure_table_exists();
        
        global $wpdb;

        $today      = current_time( 'Y-m-d' );
        $month_ago  = gmdate( 'Y-m-d', strtotime( '-30 days' ) );

        return array(
            'total_active'        => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$this->table} WHERE status = 'active'"
            ),
            'total_queued'        => (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$this->table} WHERE is_queued = 'yes'"
            ),
            'subscribed_today'    => (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE DATE(subscribe_date) = %s",
                    $today
                )
            ),
            'subscribed_month'    => (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE subscribe_date >= %s",
                    $month_ago . ' 00:00:00'
                )
            ),
            'notified_today'      => (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE DATE(notified_date) = %s",
                    $today
                )
            ),
            'notified_month'      => (int) $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$this->table} WHERE notified_date >= %s",
                    $month_ago . ' 00:00:00'
                )
            ),
            'unique_products'     => (int) $wpdb->get_var(
                "SELECT COUNT(DISTINCT product_id) FROM {$this->table} WHERE status = 'active'"
            ),
        );
    }

    /**
     * Get top products by subscriber count.
     *
     * @param int $limit Limit.
     * @return array
     */
    public function get_top_products( $limit = 10 ) {
        $this->ensure_table_exists();
        
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    product_id,
                    variation_id,
                    COUNT(*) as subscriber_count,
                    MAX(subscribe_date) as last_subscription
                FROM {$this->table} 
                WHERE status = 'active'
                AND (notified_date IS NULL OR subscribe_date > notified_date)
                GROUP BY product_id, variation_id
                ORDER BY subscriber_count DESC
                LIMIT %d",
                absint( $limit )
            )
        );

        return is_array( $results ) ? $results : array();
    }

    /**
     * Get all products with subscribers for export.
     *
     * @return array
     */
    public function get_all_products_with_counts() {
        $this->ensure_table_exists();
        
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT 
                product_id,
                variation_id,
                COUNT(*) as total_subscribers,
                SUM(CASE WHEN status = 'active' AND (notified_date IS NULL OR subscribe_date > notified_date) THEN 1 ELSE 0 END) as waiting_count,
                SUM(CASE WHEN notified_date IS NOT NULL THEN 1 ELSE 0 END) as notified_count
            FROM {$this->table} 
            GROUP BY product_id, variation_id
            ORDER BY total_subscribers DESC"
        );

        return is_array( $results ) ? $results : array();
    }

    /**
     * Get chart data for subscriptions.
     *
     * @param int $days Number of days.
     * @return array
     */
    public function get_subscription_chart_data( $days = 30 ) {
        $this->ensure_table_exists();
        
        global $wpdb;

        $start_date = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    DATE(subscribe_date) as date,
                    COUNT(*) as count
                FROM {$this->table}
                WHERE subscribe_date >= %s
                GROUP BY DATE(subscribe_date)
                ORDER BY date ASC",
                $start_date . ' 00:00:00'
            )
        );

        return is_array( $results ) ? $results : array();
    }

    /**
     * Get chart data for notifications sent.
     *
     * @param int $days Number of days.
     * @return array
     */
    public function get_notification_chart_data( $days = 30 ) {
        $this->ensure_table_exists();
        
        global $wpdb;

        $start_date = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    DATE(notified_date) as date,
                    COUNT(*) as count
                FROM {$this->table}
                WHERE notified_date >= %s
                GROUP BY DATE(notified_date)
                ORDER BY date ASC",
                $start_date . ' 00:00:00'
            )
        );

        return is_array( $results ) ? $results : array();
    }

    /**
     * Export all subscribers to array.
     *
     * @param array $args Filter arguments.
     * @return array
     */
    public function export( $args = array() ) {
        $this->ensure_table_exists();
        
        global $wpdb;

        $sql = "SELECT 
            s.*,
            p.post_title as product_name
        FROM {$this->table} s
        LEFT JOIN {$wpdb->posts} p ON s.product_id = p.ID
        WHERE 1=1";

        $values = array();

        if ( ! empty( $args['product_id'] ) ) {
            $sql     .= ' AND s.product_id = %d';
            $values[] = absint( $args['product_id'] );
        }

        if ( ! empty( $args['status'] ) ) {
            $sql     .= ' AND s.status = %s';
            $values[] = sanitize_key( $args['status'] );
        }

        if ( ! empty( $args['date_from'] ) ) {
            $sql     .= ' AND s.subscribe_date >= %s';
            $values[] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
        }

        if ( ! empty( $args['date_to'] ) ) {
            $sql     .= ' AND s.subscribe_date <= %s';
            $values[] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
        }

        $sql .= ' ORDER BY s.subscribe_date DESC';

        if ( ! empty( $values ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL is built safely with placeholders above
            $sql = $wpdb->prepare( $sql, $values );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- SQL built safely above
        $results = $wpdb->get_results( $sql, ARRAY_A );

        return is_array( $results ) ? $results : array();
    }
}
