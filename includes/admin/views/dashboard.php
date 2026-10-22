<?php
/**
 * Admin Dashboard View
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables
?>

<div class="ussa-dashboard">
    <!-- Stats Cards -->
    <div class="ussa-stats-grid">
        <div class="ussa-stat-card">
            <div class="ussa-stat-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="ussa-stat-content">
                <span class="ussa-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_active'] ) ); ?></span>
                <span class="ussa-stat-label"><?php esc_html_e( 'Active Subscribers', 'unstoppable-stock-alerts' ); ?></span>
            </div>
        </div>

        <div class="ussa-stat-card">
            <div class="ussa-stat-icon pending">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="ussa-stat-content">
                <span class="ussa-stat-value"><?php echo esc_html( number_format_i18n( $stats['total_queued'] ) ); ?></span>
                <span class="ussa-stat-label"><?php esc_html_e( 'In Queue', 'unstoppable-stock-alerts' ); ?></span>
            </div>
        </div>

        <div class="ussa-stat-card">
            <div class="ussa-stat-icon success">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <div class="ussa-stat-content">
                <span class="ussa-stat-value"><?php echo esc_html( number_format_i18n( $stats['notified_month'] ) ); ?></span>
                <span class="ussa-stat-label"><?php esc_html_e( 'Notified (30 days)', 'unstoppable-stock-alerts' ); ?></span>
            </div>
        </div>

        <div class="ussa-stat-card">
            <div class="ussa-stat-icon products">
                <span class="dashicons dashicons-products"></span>
            </div>
            <div class="ussa-stat-content">
                <span class="ussa-stat-value"><?php echo esc_html( number_format_i18n( $stats['unique_products'] ) ); ?></span>
                <span class="ussa-stat-label"><?php esc_html_e( 'Products Tracked', 'unstoppable-stock-alerts' ); ?></span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="ussa-charts-row">
        <div class="ussa-card ussa-chart-card">
            <h3><?php esc_html_e( 'New Subscriptions (30 days)', 'unstoppable-stock-alerts' ); ?></h3>
            <div class="ussa-chart-container">
                <canvas id="ussa-subscriptions-chart"></canvas>
            </div>
        </div>

        <div class="ussa-card ussa-chart-card">
            <h3><?php esc_html_e( 'Notifications Sent (30 days)', 'unstoppable-stock-alerts' ); ?></h3>
            <div class="ussa-chart-container">
                <canvas id="ussa-notifications-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="ussa-card">
        <div class="ussa-card-header">
            <h3><?php esc_html_e( 'Most Requested Products', 'unstoppable-stock-alerts' ); ?></h3>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ussa-dashboard&tab=products' ) ); ?>" class="button">
                <?php esc_html_e( 'View All', 'unstoppable-stock-alerts' ); ?>
            </a>
        </div>
        
        <?php if ( empty( $top_products ) ) : ?>
            <p class="ussa-empty"><?php esc_html_e( 'No subscriber data yet.', 'unstoppable-stock-alerts' ); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Product', 'unstoppable-stock-alerts' ); ?></th>
                        <th width="120"><?php esc_html_e( 'Waiting', 'unstoppable-stock-alerts' ); ?></th>
                        <th width="120"><?php esc_html_e( 'Stock Status', 'unstoppable-stock-alerts' ); ?></th>
                        <th width="140"><?php esc_html_e( 'Actions', 'unstoppable-stock-alerts' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $top_products as $row ) :
                        $product_id = $row->variation_id ? $row->variation_id : $row->product_id;
                        $product    = wc_get_product( $product_id );
                        
                        if ( ! $product ) {
                            continue;
                        }
                    ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url( get_edit_post_link( $product->get_id() ) ); ?>">
                                        <?php echo esc_html( $product->get_name() ); ?>
                                    </a>
                                </strong>
                                <?php if ( $product->get_sku() ) : ?>
                                    <br><small><?php echo esc_html( $product->get_sku() ); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="ussa-badge"><?php echo esc_html( $row->subscriber_count ); ?></span>
                            </td>
                            <td>
                                <?php if ( $product->is_in_stock() ) : ?>
                                    <span class="ussa-status instock"><?php esc_html_e( 'In Stock', 'unstoppable-stock-alerts' ); ?></span>
                                <?php else : ?>
                                    <span class="ussa-status outofstock"><?php esc_html_e( 'Out of Stock', 'unstoppable-stock-alerts' ); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ussa-dashboard&tab=subscribers&product_id=' . $row->product_id ) ); ?>" class="button button-small">
                                    <?php esc_html_e( 'View Subscribers', 'unstoppable-stock-alerts' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Quick Stats Row -->
    <div class="ussa-quick-stats-row">
        <div class="ussa-card ussa-quick-stat">
            <h4><?php esc_html_e( 'Today', 'unstoppable-stock-alerts' ); ?></h4>
            <div class="ussa-quick-stat-row">
                <span><?php esc_html_e( 'New Subscriptions:', 'unstoppable-stock-alerts' ); ?></span>
                <strong><?php echo esc_html( number_format_i18n( $stats['subscribed_today'] ) ); ?></strong>
            </div>
            <div class="ussa-quick-stat-row">
                <span><?php esc_html_e( 'Notifications Sent:', 'unstoppable-stock-alerts' ); ?></span>
                <strong><?php echo esc_html( number_format_i18n( $stats['notified_today'] ) ); ?></strong>
            </div>
        </div>

        <div class="ussa-card ussa-quick-stat">
            <h4><?php esc_html_e( 'Last 30 Days', 'unstoppable-stock-alerts' ); ?></h4>
            <div class="ussa-quick-stat-row">
                <span><?php esc_html_e( 'New Subscriptions:', 'unstoppable-stock-alerts' ); ?></span>
                <strong><?php echo esc_html( number_format_i18n( $stats['subscribed_month'] ) ); ?></strong>
            </div>
            <div class="ussa-quick-stat-row">
                <span><?php esc_html_e( 'Notifications Sent:', 'unstoppable-stock-alerts' ); ?></span>
                <strong><?php echo esc_html( number_format_i18n( $stats['notified_month'] ) ); ?></strong>
            </div>
        </div>

        <div class="ussa-card ussa-quick-stat">
            <h4><?php esc_html_e( 'Quick Actions', 'unstoppable-stock-alerts' ); ?></h4>
            <p>
                <a href="<?php echo esc_url( USSA_Admin_Export::get_export_url( 'subscribers' ) ); ?>" class="button">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e( 'Export Subscribers', 'unstoppable-stock-alerts' ); ?>
                </a>
            </p>
            <p>
                <a href="<?php echo esc_url( USSA_Admin_Export::get_export_url( 'products' ) ); ?>" class="button">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e( 'Export Products', 'unstoppable-stock-alerts' ); ?>
                </a>
            </p>
        </div>
    </div>
</div>
