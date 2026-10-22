<?php
/**
 * Admin Subscribers View - Complete subscriber list (addressing competitor's weakness)
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Display-only filters, no state changes
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables

$subscribers  = USSA_Admin_Subscribers::get_subscribers();
$total        = USSA_Admin_Subscribers::get_total_count();
$current_url  = admin_url( 'admin.php?page=ussa-dashboard&tab=subscribers' );

// Get sanitized filter values.
$ussa_filter_status  = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
$ussa_search_value   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$ussa_product_filter = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;
?>

<div class="ussa-subscribers">
    <!-- Filters and Search -->
    <div class="ussa-toolbar">
        <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="ussa-search-form">
            <input type="hidden" name="page" value="ussa-dashboard">
            <input type="hidden" name="tab" value="subscribers">
            
            <div class="ussa-filter-group">
                <label for="ussa-status-filter"><?php esc_html_e( 'Status:', 'unstoppable-stock-alerts' ); ?></label>
                <select name="status" id="ussa-status-filter">
                    <option value=""><?php esc_html_e( 'All Statuses', 'unstoppable-stock-alerts' ); ?></option>
                    <option value="active" <?php selected( $ussa_filter_status === 'active' ); ?>><?php esc_html_e( 'Active', 'unstoppable-stock-alerts' ); ?></option>
                    <option value="unsubscribed" <?php selected( $ussa_filter_status === 'unsubscribed' ); ?>><?php esc_html_e( 'Unsubscribed', 'unstoppable-stock-alerts' ); ?></option>
                </select>
            </div>

            <div class="ussa-filter-group">
                <label for="ussa-search"><?php esc_html_e( 'Search:', 'unstoppable-stock-alerts' ); ?></label>
                <input type="search" name="s" id="ussa-search" value="<?php echo esc_attr( $ussa_search_value ); ?>" placeholder="<?php esc_attr_e( 'Search by email...', 'unstoppable-stock-alerts' ); ?>">
            </div>

            <button type="submit" class="button"><?php esc_html_e( 'Filter', 'unstoppable-stock-alerts' ); ?></button>
            
            <?php if ( ! empty( $ussa_filter_status ) || ! empty( $ussa_search_value ) || ! empty( $ussa_product_filter ) ) : ?>
                <a href="<?php echo esc_url( $current_url ); ?>" class="button"><?php esc_html_e( 'Clear Filters', 'unstoppable-stock-alerts' ); ?></a>
            <?php endif; ?>
        </form>

        <div class="ussa-toolbar-actions">
            <a href="<?php echo esc_url( USSA_Admin_Export::get_export_url( 'subscribers', array(
                'product_id' => $ussa_product_filter,
                'status'     => $ussa_filter_status,
            ) ) ); ?>" class="button">
                <span class="dashicons dashicons-download"></span>
                <?php esc_html_e( 'Export CSV', 'unstoppable-stock-alerts' ); ?>
            </a>
        </div>
    </div>

    <?php if ( ! empty( $ussa_product_filter ) ) : 
        $filtered_product = wc_get_product( $ussa_product_filter );
        if ( $filtered_product ) :
    ?>
        <div class="ussa-filter-notice">
            <?php printf(
                /* translators: %s: product name */
                esc_html__( 'Showing subscribers for: %s', 'unstoppable-stock-alerts' ),
                '<strong>' . esc_html( $filtered_product->get_name() ) . '</strong>'
            ); ?>
            <a href="<?php echo esc_url( $current_url ); ?>"><?php esc_html_e( '(Clear)', 'unstoppable-stock-alerts' ); ?></a>
        </div>
    <?php 
        endif;
    endif; 
    ?>

    <!-- Pagination Top -->
    <div class="tablenav top">
        <?php USSA_Admin_Subscribers::render_pagination( $total ); ?>
    </div>

    <!-- Subscribers Table -->
    <?php if ( empty( $subscribers ) ) : ?>
        <div class="ussa-empty-state">
            <span class="dashicons dashicons-groups"></span>
            <h3><?php esc_html_e( 'No subscribers found', 'unstoppable-stock-alerts' ); ?></h3>
            <p><?php esc_html_e( 'Subscribers will appear here when customers sign up for stock notifications.', 'unstoppable-stock-alerts' ); ?></p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped ussa-subscribers-table">
            <thead>
                <tr>
                    <th class="column-email"><?php esc_html_e( 'Email', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-product"><?php esc_html_e( 'Product', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-status"><?php esc_html_e( 'Status', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-verified"><?php esc_html_e( 'Verified', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-date"><?php esc_html_e( 'Subscribed', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-notified"><?php esc_html_e( 'Notified', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-actions"><?php esc_html_e( 'Actions', 'unstoppable-stock-alerts' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $subscribers as $subscriber ) :
                    $product_id = $subscriber->variation_id ? $subscriber->variation_id : $subscriber->product_id;
                    $product    = wc_get_product( $product_id );
                ?>
                    <tr data-subscriber-id="<?php echo esc_attr( $subscriber->id ); ?>">
                        <td class="column-email">
                            <strong><?php echo esc_html( $subscriber->email ); ?></strong>
                            <?php if ( $subscriber->user_id ) : ?>
                                <br><small>
                                    <a href="<?php echo esc_url( get_edit_user_link( $subscriber->user_id ) ); ?>">
                                        <?php esc_html_e( 'View User', 'unstoppable-stock-alerts' ); ?>
                                    </a>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td class="column-product">
                            <?php if ( $product ) : ?>
                                <a href="<?php echo esc_url( get_edit_post_link( $subscriber->product_id ) ); ?>">
                                    <?php echo esc_html( $product->get_name() ); ?>
                                </a>
                                <?php if ( $product->get_sku() ) : ?>
                                    <br><small><?php echo esc_html( $product->get_sku() ); ?></small>
                                <?php endif; ?>
                            <?php else : ?>
                                <span class="ussa-deleted"><?php esc_html_e( 'Deleted Product', 'unstoppable-stock-alerts' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-status">
                            <?php if ( $subscriber->status === 'active' ) : ?>
                                <span class="ussa-status active"><?php esc_html_e( 'Active', 'unstoppable-stock-alerts' ); ?></span>
                            <?php else : ?>
                                <span class="ussa-status inactive"><?php echo esc_html( ucfirst( $subscriber->status ) ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-verified">
                            <?php if ( $subscriber->is_verified === 'yes' ) : ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-warning" style="color: #f0b849;"></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-date">
                            <?php 
                            $subscribe_date = strtotime( $subscriber->subscribe_date );
                            echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $subscribe_date ) );
                            ?>
                        </td>
                        <td class="column-notified">
                            <?php if ( $subscriber->notified_date ) : ?>
                                <?php 
                                $notified_date = strtotime( $subscriber->notified_date );
                                echo esc_html( date_i18n( get_option( 'date_format' ), $notified_date ) );
                                ?>
                            <?php else : ?>
                                <span class="ussa-waiting"><?php esc_html_e( 'Waiting', 'unstoppable-stock-alerts' ); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-actions">
                            <?php if ( $subscriber->status === 'active' && ! $subscriber->notified_date && $product && $product->is_in_stock() ) : ?>
                                <button type="button" class="button button-small ussa-send-notification" data-id="<?php echo esc_attr( $subscriber->id ); ?>">
                                    <span class="dashicons dashicons-email-alt"></span>
                                </button>
                            <?php endif; ?>
                            <button type="button" class="button button-small ussa-delete-subscriber" data-id="<?php echo esc_attr( $subscriber->id ); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Pagination Bottom -->
    <div class="tablenav bottom">
        <?php USSA_Admin_Subscribers::render_pagination( $total ); ?>
    </div>
</div>
