<?php
/**
 * Admin Subscribers Handler
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Admin_Subscribers Class.
 */
class USSA_Admin_Subscribers {

    /**
     * Constructor.
     */
    public function __construct() {
        // List table is loaded in the view.
    }

    /**
     * Get subscribers for display.
     *
     * @param array $args Query args.
     * @return array
     */
    public static function get_subscribers( $args = array() ) {
        $per_page = 20;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading pagination for display only.
        $page     = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
        $offset   = ( $page - 1 ) * $per_page;

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Reading filters for display only.
        $defaults = array(
            'limit'   => $per_page,
            'offset'  => $offset,
            'orderby' => isset( $_GET['orderby'] ) ? sanitize_key( wp_unslash( $_GET['orderby'] ) ) : 'id',
            'order'   => isset( $_GET['order'] ) ? strtoupper( sanitize_key( wp_unslash( $_GET['order'] ) ) ) : 'DESC',
        );

        // Filters.
        if ( ! empty( $_GET['product_id'] ) ) {
            $defaults['product_id'] = absint( $_GET['product_id'] );
        }

        if ( ! empty( $_GET['status'] ) ) {
            $defaults['status'] = sanitize_key( wp_unslash( $_GET['status'] ) );
        }

        if ( ! empty( $_GET['s'] ) ) {
            $defaults['search'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $args = wp_parse_args( $args, $defaults );

        return USSA()->db->subscribers->query( $args );
    }

    /**
     * Get total count for pagination.
     *
     * @param array $args Query args.
     * @return int
     */
    public static function get_total_count( $args = array() ) {
        $defaults = array(
            'count' => true,
        );

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Reading filters for display only.
        // Filters.
        if ( ! empty( $_GET['product_id'] ) ) {
            $defaults['product_id'] = absint( $_GET['product_id'] );
        }

        if ( ! empty( $_GET['status'] ) ) {
            $defaults['status'] = sanitize_key( wp_unslash( $_GET['status'] ) );
        }

        if ( ! empty( $_GET['s'] ) ) {
            $defaults['search'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $args = wp_parse_args( $args, $defaults );

        return USSA()->db->subscribers->query( $args );
    }

    /**
     * Render pagination.
     *
     * @param int $total Total items.
     * @param int $per_page Items per page.
     */
    public static function render_pagination( $total, $per_page = 20 ) {
        $total_pages = ceil( $total / $per_page );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading pagination for display only.
        $current     = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

        if ( $total_pages <= 1 ) {
            return;
        }

        $base_url = admin_url( 'admin.php?page=ussa-dashboard&tab=subscribers' );

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Reading filters for display only.
        // Preserve filters.
        if ( ! empty( $_GET['product_id'] ) ) {
            $base_url = add_query_arg( 'product_id', absint( $_GET['product_id'] ), $base_url );
        }
        if ( ! empty( $_GET['status'] ) ) {
            $base_url = add_query_arg( 'status', sanitize_key( wp_unslash( $_GET['status'] ) ), $base_url );
        }
        if ( ! empty( $_GET['s'] ) ) {
            $base_url = add_query_arg( 's', sanitize_text_field( wp_unslash( $_GET['s'] ) ), $base_url );
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        echo '<div class="tablenav-pages">';
        echo '<span class="displaying-num">' . esc_html( sprintf(
            /* translators: %s: number of items */
            _n( '%s item', '%s items', $total, 'unstoppable-stock-alerts' ),
            number_format_i18n( $total )
        ) ) . '</span>';

        echo '<span class="pagination-links">';

        // First page.
        if ( $current > 1 ) {
            echo '<a class="first-page button" href="' . esc_url( $base_url ) . '"><span class="screen-reader-text">' . esc_html__( 'First page', 'unstoppable-stock-alerts' ) . '</span><span aria-hidden="true">«</span></a>';
            echo '<a class="prev-page button" href="' . esc_url( add_query_arg( 'paged', $current - 1, $base_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'Previous page', 'unstoppable-stock-alerts' ) . '</span><span aria-hidden="true">‹</span></a>';
        } else {
            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>';
            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>';
        }

        echo '<span class="paging-input">';
        echo '<span class="tablenav-paging-text">' . esc_html( $current ) . ' of <span class="total-pages">' . esc_html( $total_pages ) . '</span></span>';
        echo '</span>';

        // Next/Last pages.
        if ( $current < $total_pages ) {
            echo '<a class="next-page button" href="' . esc_url( add_query_arg( 'paged', $current + 1, $base_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'Next page', 'unstoppable-stock-alerts' ) . '</span><span aria-hidden="true">›</span></a>';
            echo '<a class="last-page button" href="' . esc_url( add_query_arg( 'paged', $total_pages, $base_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'Last page', 'unstoppable-stock-alerts' ) . '</span><span aria-hidden="true">»</span></a>';
        } else {
            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>';
            echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>';
        }

        echo '</span>';
        echo '</div>';
    }
}
