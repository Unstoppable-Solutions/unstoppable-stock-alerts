<?php
/**
 * Admin Handler
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * USSA_Admin Class.
 */
class USSA_Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ), 20 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_init', array( $this, 'save_settings' ) );

        new USSA_Admin_Subscribers();
        new USSA_Admin_Export();
    }

    /**
     * Add admin menu.
     */
    public function add_menu() {
        add_submenu_page(
            'unstoppable-studio',
            __( 'Product Alerts', 'unstoppable-stock-alerts' ),
            __( 'Product Alerts', 'unstoppable-stock-alerts' ),
            'manage_woocommerce',
            'ussa-dashboard',
            array( $this, 'render_page' )
        );
    }

    /**
     * Get current tab.
     *
     * @return string
     */
    private function get_current_tab() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading tab for display only.
        return isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard';
    }

    /**
     * Get tabs.
     *
     * @return array
     */
    private function get_tabs() {
        return array(
            'dashboard'   => array(
                'label' => __( 'Dashboard', 'unstoppable-stock-alerts' ),
                'icon'  => 'dashicons-chart-bar',
            ),
            'subscribers' => array(
                'label' => __( 'Subscribers', 'unstoppable-stock-alerts' ),
                'icon'  => 'dashicons-groups',
            ),
            'products'    => array(
                'label' => __( 'Products', 'unstoppable-stock-alerts' ),
                'icon'  => 'dashicons-products',
            ),
            'settings'    => array(
                'label' => __( 'Settings', 'unstoppable-stock-alerts' ),
                'icon'  => 'dashicons-admin-generic',
            ),
        );
    }

    /**
     * Render page.
     */
    public function render_page() {
        $current_tab = $this->get_current_tab();
        $tabs        = $this->get_tabs();
        ?>
        <div class="wrap ussa-admin-wrap">
            <h1><?php esc_html_e( 'Product Alerts', 'unstoppable-stock-alerts' ); ?></h1>

            <nav class="ussa-tabs-nav">
                <?php foreach ( $tabs as $tab_id => $tab ) :
                    $url       = admin_url( 'admin.php?page=ussa-dashboard&tab=' . $tab_id );
                    $is_active = $current_tab === $tab_id;
                ?>
                    <a href="<?php echo esc_url( $url ); ?>" 
                       class="ussa-tab-link <?php echo $is_active ? 'active' : ''; ?>">
                        <span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
                        <?php echo esc_html( $tab['label'] ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="ussa-tab-content">
                <?php
                switch ( $current_tab ) {
                    case 'subscribers':
                        $this->render_subscribers_tab();
                        break;
                    case 'products':
                        $this->render_products_tab();
                        break;
                    case 'settings':
                        $this->render_settings_tab();
                        break;
                    case 'dashboard':
                    default:
                        $this->render_dashboard_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render dashboard tab.
     */
    private function render_dashboard_tab() {
        $stats = USSA()->db->subscribers->get_stats();
        $top_products = USSA()->db->subscribers->get_top_products( 10 );
        $chart_subscriptions = USSA()->db->subscribers->get_subscription_chart_data( 30 );
        $chart_notifications = USSA()->db->subscribers->get_notification_chart_data( 30 );

        include USSA_PLUGIN_DIR . 'includes/admin/views/dashboard.php';
    }

    /**
     * Render subscribers tab.
     */
    private function render_subscribers_tab() {
        include USSA_PLUGIN_DIR . 'includes/admin/views/subscribers.php';
    }

    /**
     * Render products tab.
     */
    private function render_products_tab() {
        $products = USSA()->db->subscribers->get_all_products_with_counts();
        include USSA_PLUGIN_DIR . 'includes/admin/views/products.php';
    }

    /**
     * Render settings tab.
     */
    private function render_settings_tab() {
        include USSA_PLUGIN_DIR . 'includes/admin/views/settings.php';
    }

    /**
     * Enqueue admin scripts.
     *
     * @param string $hook Current page hook.
     */
    public function enqueue_scripts( $hook ) {
        if ( strpos( $hook, 'ussa-dashboard' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'ussa-admin',
            USSA_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            USSA_VERSION
        );

        wp_enqueue_script(
            'ussa-admin',
            USSA_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery', 'wp-util' ),
            USSA_VERSION,
            true
        );

        wp_localize_script( 'ussa-admin', 'ussa_admin', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'ussa_admin_nonce' ),
            'i18n'     => array(
                'confirm_delete' => __( 'Are you sure you want to delete this subscriber?', 'unstoppable-stock-alerts' ),
                'confirm_notify' => __( 'Send notification to this subscriber now?', 'unstoppable-stock-alerts' ),
                'deleting'       => __( 'Deleting...', 'unstoppable-stock-alerts' ),
                'sending'        => __( 'Sending...', 'unstoppable-stock-alerts' ),
            ),
        ) );

        // Chart.js for dashboard (bundled locally).
        wp_enqueue_script(
            'chartjs',
            USSA_PLUGIN_URL . 'assets/js/chart.min.js',
            array(),
            '4.5.1',
            true
        );

        // Add chart data for dashboard tab.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading tab for display only.
        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'dashboard';
        if ( 'dashboard' === $current_tab ) {
            $this->enqueue_chart_scripts();
        }
    }

    /**
     * Enqueue chart initialization scripts.
     */
    private function enqueue_chart_scripts() {
        $chart_subscriptions = USSA()->db->subscribers->get_subscription_chart_data( 30 );
        $chart_notifications = USSA()->db->subscribers->get_notification_chart_data( 30 );

        $subs_data = array_map( function( $row ) {
            return array(
                'date'  => $row->date,
                'count' => (int) $row->count,
            );
        }, $chart_subscriptions );

        $notif_data = array_map( function( $row ) {
            return array(
                'date'  => $row->date,
                'count' => (int) $row->count,
            );
        }, $chart_notifications );

        wp_localize_script( 'chartjs', 'ussa_chart_data', array(
            'subscriptions' => $subs_data,
            'notifications' => $notif_data,
            'i18n'          => array(
                'subscriptions' => __( 'Subscriptions', 'unstoppable-stock-alerts' ),
                'notifications' => __( 'Notifications', 'unstoppable-stock-alerts' ),
            ),
        ) );

        $chart_init_script = "
        jQuery(document).ready(function($) {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded');
                return;
            }

            var subsCtx = document.getElementById('ussa-subscriptions-chart');
            if (subsCtx && typeof ussa_chart_data !== 'undefined') {
                new Chart(subsCtx, {
                    type: 'bar',
                    data: {
                        labels: ussa_chart_data.subscriptions.map(function(d) { return d.date; }),
                        datasets: [{
                            label: ussa_chart_data.i18n.subscriptions,
                            data: ussa_chart_data.subscriptions.map(function(d) { return d.count; }),
                            backgroundColor: 'rgba(34, 113, 177, 0.5)',
                            borderColor: 'rgb(34, 113, 177)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                        plugins: { legend: { display: false } }
                    }
                });
            }

            var notifCtx = document.getElementById('ussa-notifications-chart');
            if (notifCtx && typeof ussa_chart_data !== 'undefined') {
                new Chart(notifCtx, {
                    type: 'bar',
                    data: {
                        labels: ussa_chart_data.notifications.map(function(d) { return d.date; }),
                        datasets: [{
                            label: ussa_chart_data.i18n.notifications,
                            data: ussa_chart_data.notifications.map(function(d) { return d.count; }),
                            backgroundColor: 'rgba(46, 204, 113, 0.5)',
                            borderColor: 'rgb(46, 204, 113)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                        plugins: { legend: { display: false } }
                    }
                });
            }
        });
        ";

        wp_add_inline_script( 'chartjs', $chart_init_script );
    }

    /**
     * Save settings.
     */
    public function save_settings() {
        if ( ! isset( $_POST['ussa_save_settings'] ) ) {
            return;
        }

        if ( ! isset( $_POST['ussa_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ussa_settings_nonce'] ) ), 'ussa_save_settings' ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $settings = array(
            'ussa_form_title',
            'ussa_button_text',
            'ussa_success_message',
            'ussa_show_subscriber_count',
            'ussa_subscriber_count_text',
            'ussa_require_login',
            'ussa_double_optin',
            'ussa_email_from_name',
            'ussa_email_from_address',
            'ussa_batch_size',
            'ussa_throttle_hours',
        );

        foreach ( $settings as $setting ) {
            if ( isset( $_POST[ $setting ] ) ) {
                $value = sanitize_text_field( wp_unslash( $_POST[ $setting ] ) );
                update_option( $setting, $value );
            } else {
                // Checkbox not checked.
                if ( in_array( $setting, array( 'ussa_show_subscriber_count', 'ussa_require_login', 'ussa_double_optin' ), true ) ) {
                    update_option( $setting, 'no' );
                }
            }
        }

        add_action( 'admin_notices', function() {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e( 'Settings saved successfully.', 'unstoppable-stock-alerts' ); ?></p>
            </div>
            <?php
        } );
    }
}
