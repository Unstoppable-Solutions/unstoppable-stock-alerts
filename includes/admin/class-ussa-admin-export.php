<?php
/**
 * Admin Export Handler
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Admin_Export Class.
 */
class USSA_Admin_Export {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_export' ) );
    }

    /**
     * Sanitize a value for CSV export to prevent formula injection (CWE-1236).
     *
     * Prefixes potentially dangerous characters with a single quote to prevent
     * Excel/LibreOffice from interpreting them as formulas.
     *
     * @param mixed $value The value to sanitize.
     * @return string Sanitized value safe for CSV.
     */
    private function sanitize_csv_value( $value ) {
        if ( ! is_string( $value ) || '' === $value ) {
            return $value;
        }

        // Characters that trigger formula interpretation in spreadsheets.
        $dangerous_chars = array( '=', '+', '-', '@', "\t", "\r", "\n" );

        // Check if value starts with a dangerous character.
        $first_char = substr( $value, 0, 1 );
        if ( in_array( $first_char, $dangerous_chars, true ) ) {
            // Prefix with single quote to force text interpretation.
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Sanitize an array of values for CSV export.
     *
     * @param array $row Array of values.
     * @return array Sanitized array.
     */
    private function sanitize_csv_row( $row ) {
        return array_map( array( $this, 'sanitize_csv_value' ), $row );
    }

    /**
     * Handle export request.
     */
    public function handle_export() {
        if ( ! isset( $_GET['ussa_export'] ) || $_GET['ussa_export'] !== '1' ) {
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ussa_export' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'unstoppable-stock-alerts' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'unstoppable-stock-alerts' ) );
        }

        $product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
        $status     = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
        $date_from  = isset( $_GET['date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['date_from'] ) ) : '';
        $date_to    = isset( $_GET['date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['date_to'] ) ) : '';
        $type       = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'subscribers';

        if ( $type === 'products' ) {
            $this->export_products();
        } else {
            $this->export_subscribers( $product_id, $status, $date_from, $date_to );
        }
    }

    /**
     * Export subscribers to CSV.
     *
     * @param int    $product_id Product filter.
     * @param string $status     Status filter.
     * @param string $date_from  Start date filter.
     * @param string $date_to    End date filter.
     */
    private function export_subscribers( $product_id, $status, $date_from, $date_to ) {
        $subscribers = USSA()->db->subscribers->export( array(
            'product_id' => $product_id,
            'status'     => $status,
            'date_from'  => $date_from,
            'date_to'    => $date_to,
        ) );

        $filename = 'stock-alerts-subscribers-' . gmdate( 'Y-m-d-His' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );

        // BOM for Excel UTF-8 compatibility.
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

        // Headers.
        fputcsv( $output, array(
            __( 'ID', 'unstoppable-stock-alerts' ),
            __( 'Email', 'unstoppable-stock-alerts' ),
            __( 'Product ID', 'unstoppable-stock-alerts' ),
            __( 'Product Name', 'unstoppable-stock-alerts' ),
            __( 'Variation ID', 'unstoppable-stock-alerts' ),
            __( 'User ID', 'unstoppable-stock-alerts' ),
            __( 'Status', 'unstoppable-stock-alerts' ),
            __( 'Verified', 'unstoppable-stock-alerts' ),
            __( 'Subscribe Date', 'unstoppable-stock-alerts' ),
            __( 'Notified Date', 'unstoppable-stock-alerts' ),
        ) );

        foreach ( $subscribers as $subscriber ) {
            // Sanitize all values to prevent CSV formula injection.
            fputcsv( $output, $this->sanitize_csv_row( array(
                $subscriber['id'],
                $subscriber['email'],
                $subscriber['product_id'],
                $subscriber['product_name'] ?? '',
                $subscriber['variation_id'],
                $subscriber['user_id'],
                $subscriber['status'],
                $subscriber['is_verified'],
                $subscriber['subscribe_date'],
                $subscriber['notified_date'],
            ) ) );
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Writing to php://output for CSV download
        fclose( $output );
        exit;
    }

    /**
     * Export products summary to CSV.
     */
    private function export_products() {
        $products = USSA()->db->subscribers->get_all_products_with_counts();

        $filename = 'stock-alerts-products-' . gmdate( 'Y-m-d-His' ) . '.csv';

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );

        // BOM for Excel UTF-8 compatibility.
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

        // Headers.
        fputcsv( $output, array(
            __( 'Product ID', 'unstoppable-stock-alerts' ),
            __( 'Variation ID', 'unstoppable-stock-alerts' ),
            __( 'Product Name', 'unstoppable-stock-alerts' ),
            __( 'SKU', 'unstoppable-stock-alerts' ),
            __( 'Total Subscribers', 'unstoppable-stock-alerts' ),
            __( 'Waiting', 'unstoppable-stock-alerts' ),
            __( 'Notified', 'unstoppable-stock-alerts' ),
            __( 'Stock Status', 'unstoppable-stock-alerts' ),
        ) );

        foreach ( $products as $row ) {
            $product_id = $row->variation_id ? $row->variation_id : $row->product_id;
            $product    = wc_get_product( $product_id );

            $product_name = '';
            $sku          = '';
            $stock_status = '';

            if ( $product ) {
                $product_name = $product->get_name();
                $sku          = $product->get_sku();
                $stock_status = $product->get_stock_status();
            }

            // Sanitize all values to prevent CSV formula injection.
            fputcsv( $output, $this->sanitize_csv_row( array(
                $row->product_id,
                $row->variation_id,
                $product_name,
                $sku,
                $row->total_subscribers,
                $row->waiting_count,
                $row->notified_count,
                $stock_status,
            ) ) );
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Writing to php://output for CSV download
        fclose( $output );
        exit;
    }

    /**
     * Get export URL.
     *
     * @param string $type    Export type.
     * @param array  $filters Optional filters.
     * @return string
     */
    public static function get_export_url( $type = 'subscribers', $filters = array() ) {
        $args = array(
            'ussa_export' => '1',
            'type'        => $type,
            '_wpnonce'    => wp_create_nonce( 'ussa_export' ),
        );

        $args = array_merge( $args, $filters );

        return add_query_arg( $args, admin_url( 'admin.php' ) );
    }
}
