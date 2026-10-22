<?php
/**
 * Cron Handler for processing email queue
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Cron Class.
 */
class USSA_Cron {

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
        add_action( 'ussa_process_queue', array( $this, 'process_queue' ) );
    }

    /**
     * Add custom cron schedules.
     *
     * @param array $schedules Existing schedules.
     * @return array
     */
    public function add_cron_schedules( $schedules ) {
        $schedules['every_five_minutes'] = array(
            'interval' => 5 * MINUTE_IN_SECONDS,
            'display'  => __( 'Every 5 minutes', 'unstoppable-stock-alerts' ),
        );
        return $schedules;
    }

    /**
     * Process the notification queue.
     */
    public function process_queue() {
        $batch_size  = absint( get_option( 'ussa_batch_size', 50 ) );
        $subscribers = USSA()->db->subscribers->get_queued( $batch_size );

        if ( empty( $subscribers ) ) {
            return;
        }

        $processed = 0;
        $failed    = 0;

        foreach ( $subscribers as $subscriber ) {
            // Check if product is still in stock.
            $product_id = $subscriber->variation_id ? $subscriber->variation_id : $subscriber->product_id;
            $product    = wc_get_product( $product_id );

            if ( ! $product || ! $product->is_in_stock() ) {
                // Product went out of stock again, remove from queue.
                USSA()->db->subscribers->update( $subscriber->id, array(
                    'is_queued' => 'no',
                ) );
                continue;
            }

            // Send notification.
            $result = USSA()->emails->send_back_in_stock( $subscriber->id );

            if ( $result ) {
                USSA()->db->subscribers->mark_notified( $subscriber->id );
                $processed++;

                do_action( 'ussa_notification_sent', $subscriber->id );
            } else {
                $failed++;

                // Remove from queue to prevent infinite retries.
                USSA()->db->subscribers->update( $subscriber->id, array(
                    'is_queued' => 'no',
                ) );

                do_action( 'ussa_notification_failed', $subscriber->id );
            }

            // Small delay to prevent overwhelming mail server.
            usleep( 100000 ); // 0.1 seconds
        }

        // Log results.
        if ( $processed > 0 || $failed > 0 ) {
            USSA()->log(
                sprintf(
                    'Queue processed: %d sent, %d failed',
                    $processed,
                    $failed
                ),
                'info'
            );
        }
    }

    /**
     * Manually trigger queue processing.
     *
     * @return array Results.
     */
    public static function run_now() {
        $instance = new self();
        ob_start();
        $instance->process_queue();
        ob_end_clean();

        return array(
            'success' => true,
            'message' => __( 'Queue processing completed.', 'unstoppable-stock-alerts' ),
        );
    }
}
