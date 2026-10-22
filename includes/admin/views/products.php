<?php
/**
 * Admin Products View
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Display-only filters, no state changes
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables

// Get filters.
$search       = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$stock_filter = isset( $_GET['stock_status'] ) ? sanitize_key( wp_unslash( $_GET['stock_status'] ) ) : '';
$paged        = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
$per_page     = 20;

// Get products with subscribers.
$products = USSA()->db->subscribers->get_all_products_with_counts();

// Apply search filter.
if ( ! empty( $search ) ) {
    $products = array_filter( $products, function( $row ) use ( $search ) {
        $product_id = $row->variation_id ? $row->variation_id : $row->product_id;
        $product    = wc_get_product( $product_id );
        if ( ! $product ) {
            return false;
        }
        $haystack = strtolower( $product->get_name() . ' ' . $product->get_sku() );
        return strpos( $haystack, strtolower( $search ) ) !== false;
    });
}

// Apply stock filter.
if ( ! empty( $stock_filter ) ) {
    $products = array_filter( $products, function( $row ) use ( $stock_filter ) {
        $product_id = $row->variation_id ? $row->variation_id : $row->product_id;
        $product    = wc_get_product( $product_id );
        if ( ! $product ) {
            return false;
        }
        return $product->get_stock_status() === $stock_filter;
    });
}

// Pagination.
$total_items = count( $products );
$total_pages = ceil( $total_items / $per_page );
$offset      = ( $paged - 1 ) * $per_page;
$products    = array_slice( $products, $offset, $per_page );

// Export URL.
$export_url = USSA_Admin_Export::get_export_url( 'products' );
?>

<div class="ussa-products-view">
    <div class="ussa-header-actions">
        <form method="get" action="" class="ussa-filter-form">
            <input type="hidden" name="page" value="ussa-dashboard">
            <input type="hidden" name="tab" value="products">
            
            <input type="search" 
                   name="s" 
                   value="<?php echo esc_attr( $search ); ?>" 
                   placeholder="<?php esc_attr_e( 'Search products...', 'unstoppable-stock-alerts' ); ?>"
                   class="ussa-search-input">
            
            <select name="stock_status" class="ussa-filter-select">
                <option value=""><?php esc_html_e( 'All Stock Status', 'unstoppable-stock-alerts' ); ?></option>
                <option value="outofstock" <?php selected( $stock_filter, 'outofstock' ); ?>><?php esc_html_e( 'Out of Stock', 'unstoppable-stock-alerts' ); ?></option>
                <option value="instock" <?php selected( $stock_filter, 'instock' ); ?>><?php esc_html_e( 'In Stock', 'unstoppable-stock-alerts' ); ?></option>
                <option value="onbackorder" <?php selected( $stock_filter, 'onbackorder' ); ?>><?php esc_html_e( 'On Backorder', 'unstoppable-stock-alerts' ); ?></option>
            </select>
            
            <button type="submit" class="button"><?php esc_html_e( 'Filter', 'unstoppable-stock-alerts' ); ?></button>
            
            <?php if ( ! empty( $search ) || ! empty( $stock_filter ) ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ussa-dashboard&tab=products' ) ); ?>" class="button">
                    <?php esc_html_e( 'Clear', 'unstoppable-stock-alerts' ); ?>
                </a>
            <?php endif; ?>
        </form>
        
        <a href="<?php echo esc_url( $export_url ); ?>" class="button button-secondary">
            <span class="dashicons dashicons-download" style="margin-top: 3px;"></span>
            <?php esc_html_e( 'Export Products', 'unstoppable-stock-alerts' ); ?>
        </a>
    </div>
    
    <div class="ussa-stats-summary">
        <span class="ussa-stat">
            <?php 
            printf( 
                /* translators: %d: total products count */
                esc_html__( '%d products with subscribers', 'unstoppable-stock-alerts' ), 
                absint( $total_items )
            ); 
            ?>
        </span>
    </div>
    
    <?php if ( empty( $products ) ) : ?>
        <div class="ussa-empty-state">
            <span class="dashicons dashicons-products"></span>
            <h3><?php esc_html_e( 'No products found', 'unstoppable-stock-alerts' ); ?></h3>
            <p><?php esc_html_e( 'There are no products with stock alert subscribers yet.', 'unstoppable-stock-alerts' ); ?></p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped ussa-table">
            <thead>
                <tr>
                    <th class="column-product" style="width: 35%;"><?php esc_html_e( 'Product', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-sku" style="width: 12%;"><?php esc_html_e( 'SKU', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-waiting" style="width: 12%;"><?php esc_html_e( 'Waiting', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-notified" style="width: 12%;"><?php esc_html_e( 'Notified', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-total" style="width: 10%;"><?php esc_html_e( 'Total', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-stock" style="width: 12%;"><?php esc_html_e( 'Stock', 'unstoppable-stock-alerts' ); ?></th>
                    <th class="column-actions" style="width: 7%;"><?php esc_html_e( 'Actions', 'unstoppable-stock-alerts' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $products as $row ) : 
                    $product_id = $row->variation_id ? $row->variation_id : $row->product_id;
                    $product    = wc_get_product( $product_id );
                    
                    if ( ! $product ) {
                        continue;
                    }
                    
                    $stock_status = $product->get_stock_status();
                    $stock_class  = 'stock-' . $stock_status;
                    
                    switch ( $stock_status ) {
                        case 'instock':
                            $stock_label = __( 'In Stock', 'unstoppable-stock-alerts' );
                            break;
                        case 'outofstock':
                            $stock_label = __( 'Out of Stock', 'unstoppable-stock-alerts' );
                            break;
                        case 'onbackorder':
                            $stock_label = __( 'Backorder', 'unstoppable-stock-alerts' );
                            break;
                        default:
                            $stock_label = $stock_status;
                    }
                    
                    // View subscribers URL.
                    $view_url = admin_url( 'admin.php?page=ussa-dashboard&tab=subscribers&product_id=' . $product_id );
                    ?>
                    <tr>
                        <td class="column-product">
                            <div class="ussa-product-info">
                                <?php 
                                $image = $product->get_image( array( 40, 40 ) );
                                if ( $image ) {
                                    echo wp_kses_post( $image );
                                }
                                ?>
                                <div class="ussa-product-details">
                                    <strong>
                                        <a href="<?php echo esc_url( get_edit_post_link( $product->get_id() ) ); ?>">
                                            <?php echo esc_html( $product->get_name() ); ?>
                                        </a>
                                    </strong>
                                    <?php if ( $row->variation_id ) : ?>
                                        <span class="ussa-variation-badge"><?php esc_html_e( 'Variation', 'unstoppable-stock-alerts' ); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="column-sku">
                            <code><?php echo esc_html( $product->get_sku() ?: '—' ); ?></code>
                        </td>
                        <td class="column-waiting">
                            <?php if ( $row->waiting_count > 0 ) : ?>
                                <span class="ussa-count ussa-count-waiting"><?php echo esc_html( $row->waiting_count ); ?></span>
                            <?php else : ?>
                                <span class="ussa-count-zero">0</span>
                            <?php endif; ?>
                        </td>
                        <td class="column-notified">
                            <span class="ussa-count-muted"><?php echo esc_html( $row->notified_count ); ?></span>
                        </td>
                        <td class="column-total">
                            <strong><?php echo esc_html( $row->total_subscribers ); ?></strong>
                        </td>
                        <td class="column-stock">
                            <span class="ussa-stock-badge <?php echo esc_attr( $stock_class ); ?>">
                                <?php echo esc_html( $stock_label ); ?>
                            </span>
                        </td>
                        <td class="column-actions">
                            <a href="<?php echo esc_url( $view_url ); ?>" 
                               class="button button-small" 
                               title="<?php esc_attr_e( 'View Subscribers', 'unstoppable-stock-alerts' ); ?>">
                                <span class="dashicons dashicons-visibility" style="margin-top: 3px;"></span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ( $total_pages > 1 ) : ?>
            <div class="ussa-pagination">
                <?php
                $base_url = admin_url( 'admin.php?page=ussa-dashboard&tab=products' );
                if ( ! empty( $search ) ) {
                    $base_url = add_query_arg( 's', $search, $base_url );
                }
                if ( ! empty( $stock_filter ) ) {
                    $base_url = add_query_arg( 'stock_status', $stock_filter, $base_url );
                }
                
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links() output is safe
                echo paginate_links( array(
                    'base'      => add_query_arg( 'paged', '%#%', $base_url ),
                    'format'    => '',
                    'current'   => $paged,
                    'total'     => $total_pages,
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                ) );
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
