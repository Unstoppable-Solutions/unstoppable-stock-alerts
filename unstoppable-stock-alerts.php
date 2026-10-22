<?php
/**
 * Plugin Name: Unstoppable Inventory Alerts for WooCommerce
 * Plugin URI: https://unstoppablesolutions.app/plugins/unstoppable-stock-alerts
 * Description: Modern back-in-stock notifications for WooCommerce with AJAX forms, comprehensive dashboard, CSV exports, and reliable email delivery.
 * Version: 1.1.7
 * Author: Unstoppable Solutions
 * Author URI: https://unstoppablesolutions.app
 * Text Domain: unstoppable-stock-alerts
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 7.1
 * Requires PHP: 7.4
 * WC requires at least: 8.0
 * WC tested up to: 9.8
 * Requires Plugins: woocommerce
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Unstoppable_Stock_Alerts
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'USSA_VERSION', '1.1.7' );
define( 'USSA_PLUGIN_FILE', __FILE__ );
define( 'USSA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'USSA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'USSA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'USSA_PLUGIN_SLUG', 'unstoppable-stock-alerts' );
define( 'USSA_OPTION_PREFIX', 'ussa' );

/**
 * Declare compatibility with WooCommerce features.
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        // Declare HPOS (High-Performance Order Storage) compatibility.
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        
        // Declare Cart & Checkout Blocks compatibility.
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
    }
} );

/*
 * ============================================================================
 * UNSTOPPABLE OPTIONS STUDIO - SHARED SHELL SYSTEM
 * ============================================================================
 */

if ( ! function_exists( 'unstoppable_options_studio_init' ) ) {

    function unstoppable_options_studio_init() {
        add_action( 'admin_menu', 'unstoppable_options_studio_create_menu', 9 );
        add_action( 'admin_enqueue_scripts', 'unstoppable_options_studio_admin_styles' );
        add_action( 'admin_bar_menu', 'unstoppable_options_studio_admin_bar', 100 );
        add_action( 'wp_enqueue_scripts', 'unstoppable_options_studio_frontend_bar_styles' );
    }

    function unstoppable_options_studio_create_menu() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        global $menu;
        foreach ( $menu as $item ) {
            if ( isset( $item[2] ) && $item[2] === 'unstoppable-studio' ) {
                return;
            }
        }

        add_menu_page(
            __( 'Options Studio', 'unstoppable-stock-alerts' ),
            __( 'Options Studio', 'unstoppable-stock-alerts' ),
            'manage_woocommerce',
            'unstoppable-studio',
            'unstoppable_options_studio_dashboard',
            'dashicons-screenoptions',
            56
        );

        add_submenu_page(
            'unstoppable-studio',
            __( 'Dashboard', 'unstoppable-stock-alerts' ),
            __( 'Dashboard', 'unstoppable-stock-alerts' ),
            'manage_woocommerce',
            'unstoppable-studio',
            'unstoppable_options_studio_dashboard'
        );
    }

    function unstoppable_options_studio_dashboard() {
        $plugins = apply_filters( 'unstoppable_studio_registered_plugins', array() );
        ?>
        <div class="wrap unstoppable-studio-dashboard">
            <h1><?php esc_html_e( 'Options Studio', 'unstoppable-stock-alerts' ); ?></h1>
            <p class="description"><?php esc_html_e( 'Your suite of WooCommerce enhancement tools by Unstoppable Solutions.', 'unstoppable-stock-alerts' ); ?></p>
            
            <div class="unstoppable-studio-plugins">
                <?php if ( empty( $plugins ) ) : ?>
                    <p><?php esc_html_e( 'No plugins registered yet.', 'unstoppable-stock-alerts' ); ?></p>
                <?php else : ?>
                    <?php foreach ( $plugins as $plugin ) : ?>
                        <div class="unstoppable-plugin-card">
                            <h3><?php echo esc_html( $plugin['name'] ); ?></h3>
                            <p><?php echo esc_html( $plugin['description'] ); ?></p>
                            <?php if ( ! empty( $plugin['menu_slug'] ) ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $plugin['menu_slug'] ) ); ?>" class="button button-primary">
                                    <?php esc_html_e( 'Open', 'unstoppable-stock-alerts' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="unstoppable-studio-footer">
                <p>
                    <a href="https://unstoppablesolutions.app" target="_blank"><?php esc_html_e( 'Visit Unstoppable Solutions', 'unstoppable-stock-alerts' ); ?></a> | 
                    <a href="https://unstoppablesolutions.app/support" target="_blank"><?php esc_html_e( 'Get Support', 'unstoppable-stock-alerts' ); ?></a>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin styles for Options Studio menu and admin bar.
     */
    function unstoppable_options_studio_admin_styles() {
        wp_register_style( 'unstoppable-studio-admin', false, array(), '1.0.0' );
        wp_enqueue_style( 'unstoppable-studio-admin' );

        $ussa_studio_css = '
            #adminmenu .toplevel_page_unstoppable-studio .wp-menu-image:before { color: #f0b849; }
            #adminmenu .toplevel_page_unstoppable-studio:hover .wp-menu-image:before,
            #adminmenu .toplevel_page_unstoppable-studio.current .wp-menu-image:before { color: #fff; }
            #wpadminbar #wp-admin-bar-unstoppable-studio > .ab-item .ab-icon:before { content: "\f180"; top: 2px; }
            #wpadminbar #wp-admin-bar-unstoppable-studio > .ab-item .ab-icon { margin-right: 6px; }
            #wpadminbar #wp-admin-bar-unstoppable-studio > .ab-item { color: #f0b849 !important; }
            #wpadminbar #wp-admin-bar-unstoppable-studio:hover > .ab-item { color: #fff !important; background: #32373c !important; }
            .unstoppable-studio-plugins { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
            .unstoppable-plugin-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px; width: 300px; }
            .unstoppable-plugin-card h3 { margin-top: 0; }
            .unstoppable-plugin-card p { color: #666; }
            .unstoppable-studio-footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ccd0d4; }
        ';

        wp_add_inline_style( 'unstoppable-studio-admin', $ussa_studio_css );
    }

    /**
     * Enqueue frontend styles for admin bar when visible.
     */
    function unstoppable_options_studio_frontend_bar_styles() {
        if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        wp_register_style( 'unstoppable-studio-bar', false, array(), '1.0.0' );
        wp_enqueue_style( 'unstoppable-studio-bar' );

        $ussa_bar_css = '
            #wpadminbar #wp-admin-bar-unstoppable-studio > .ab-item .ab-icon:before { content: "\f180"; top: 2px; }
            #wpadminbar #wp-admin-bar-unstoppable-studio > .ab-item .ab-icon { margin-right: 6px; }
            #wpadminbar #wp-admin-bar-unstoppable-studio > .ab-item { color: #f0b849 !important; }
            #wpadminbar #wp-admin-bar-unstoppable-studio:hover > .ab-item { color: #fff !important; background: #32373c !important; }
        ';

        wp_add_inline_style( 'unstoppable-studio-bar', $ussa_bar_css );
    }

    function unstoppable_options_studio_admin_bar( $wp_admin_bar ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }
        
        $wp_admin_bar->add_node( array(
            'id'    => 'unstoppable-studio',
            'title' => '<span class="ab-icon dashicons dashicons-screenoptions"></span>' . __( 'Options Studio', 'unstoppable-stock-alerts' ),
            'href'  => admin_url( 'admin.php?page=unstoppable-studio' ),
        ) );
        
        $plugins = apply_filters( 'unstoppable_studio_registered_plugins', array() );
        
        foreach ( $plugins as $plugin_id => $plugin ) {
            if ( ! empty( $plugin['menu_slug'] ) ) {
                $wp_admin_bar->add_node( array(
                    'id'     => 'unstoppable-studio-' . sanitize_key( $plugin_id ),
                    'parent' => 'unstoppable-studio',
                    'title'  => $plugin['name'],
                    'href'   => admin_url( 'admin.php?page=' . $plugin['menu_slug'] ),
                ) );
            }
        }
    }
}

unstoppable_options_studio_init();

/*
 * ============================================================================
 * END SHARED SHELL SYSTEM
 * ============================================================================
 */

/**
 * Main Plugin Class
 */
final class Unstoppable_Stock_Alerts {

    /**
     * Single instance of the class.
     *
     * @var Unstoppable_Stock_Alerts
     */
    private static $instance = null;

    /**
     * Database handler.
     *
     * @var USSA_DB
     */
    public $db;

    /**
     * Email handler.
     *
     * @var USSA_Emails
     */
    public $emails;

    /**
     * Product handler.
     *
     * @var USSA_Product
     */
    public $product;

    /**
     * Get single instance.
     *
     * @return Unstoppable_Stock_Alerts
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ), 10 );
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Initialize plugin.
     */
    public function init() {
        // Check WooCommerce.
        if ( ! class_exists( 'WooCommerce' ) ) {
            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
            return;
        }

        $this->includes();
        $this->init_hooks();
        $this->init_classes();
    }

    /**
     * Include required files.
     */
    private function includes() {
        // Core.
        require_once USSA_PLUGIN_DIR . 'includes/class-ussa-install.php';
        require_once USSA_PLUGIN_DIR . 'includes/class-ussa-ajax.php';
        require_once USSA_PLUGIN_DIR . 'includes/class-ussa-product.php';
        require_once USSA_PLUGIN_DIR . 'includes/class-ussa-emails.php';
        require_once USSA_PLUGIN_DIR . 'includes/class-ussa-cron.php';
        require_once USSA_PLUGIN_DIR . 'includes/class-ussa-admin-notices.php';

        // Database.
        require_once USSA_PLUGIN_DIR . 'includes/db/class-ussa-db.php';
        require_once USSA_PLUGIN_DIR . 'includes/db/class-ussa-subscribers-db.php';

        // Data stores.
        require_once USSA_PLUGIN_DIR . 'includes/data-stores/class-ussa-subscriber.php';

        // Admin.
        if ( is_admin() ) {
            require_once USSA_PLUGIN_DIR . 'includes/admin/class-ussa-admin.php';
            require_once USSA_PLUGIN_DIR . 'includes/admin/class-ussa-admin-subscribers.php';
            require_once USSA_PLUGIN_DIR . 'includes/admin/class-ussa-admin-export.php';
        }
    }

    /**
     * Initialize hooks.
     */
    private function init_hooks() {
        add_filter( 'unstoppable_studio_registered_plugins', array( $this, 'register_with_studio' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
    }

    /**
     * Initialize classes.
     */
    private function init_classes() {
        $this->db      = new USSA_DB();
        $this->emails  = new USSA_Emails();
        $this->product = new USSA_Product();

        new USSA_Ajax();
        new USSA_Cron();

        if ( is_admin() ) {
            new USSA_Admin();
            new USSA_Admin_Notices();
        }
    }

    /**
     * Register with Options Studio.
     *
     * @param array $plugins Registered plugins.
     * @return array
     */
    public function register_with_studio( $plugins ) {
        $plugins['unstoppable-stock-alerts'] = array(
            'name'        => __( 'Product Alerts', 'unstoppable-stock-alerts' ),
            'description' => __( 'Back-in-stock notifications with AJAX forms and exports.', 'unstoppable-stock-alerts' ),
            'menu_slug'   => 'ussa-dashboard',
            'version'     => USSA_VERSION,
        );
        return $plugins;
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_frontend_assets() {
        if ( ! is_product() && ! is_shop() && ! is_product_category() ) {
            return;
        }

        wp_enqueue_style(
            'ussa-frontend',
            USSA_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            USSA_VERSION
        );

        wp_enqueue_script(
            'ussa-frontend',
            USSA_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery' ),
            USSA_VERSION,
            true
        );

        wp_localize_script( 'ussa-frontend', 'ussa_params', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'ussa_subscribe' ),
            'i18n'     => array(
                'subscribing'         => __( 'Subscribing...', 'unstoppable-stock-alerts' ),
                'success'             => __( 'You will be notified when this product is back in stock!', 'unstoppable-stock-alerts' ),
                'already'             => __( 'You are already subscribed to this product.', 'unstoppable-stock-alerts' ),
                'error'               => __( 'An error occurred. Please try again.', 'unstoppable-stock-alerts' ),
                'invalid_email'       => __( 'Please enter a valid email address.', 'unstoppable-stock-alerts' ),
                'confirm_unsubscribe' => __( 'Are you sure you want to unsubscribe from notifications?', 'unstoppable-stock-alerts' ),
                'unsubscribing'       => __( 'Unsubscribing...', 'unstoppable-stock-alerts' ),
                'unsubscribe'         => __( 'Unsubscribe', 'unstoppable-stock-alerts' ),
            ),
        ) );
    }

    /**
     * Activation.
     */
    public function activate() {
        require_once USSA_PLUGIN_DIR . 'includes/class-ussa-install.php';
        USSA_Install::install();

        // Store install date for admin notices.
        if ( ! get_option( 'ussa_install_date' ) ) {
            update_option( 'ussa_install_date', time() );
        }

        // Schedule cron.
        if ( ! wp_next_scheduled( 'ussa_process_queue' ) ) {
            wp_schedule_event( time(), 'every_five_minutes', 'ussa_process_queue' );
        }
    }

    /**
     * Deactivation.
     */
    public function deactivate() {
        wp_clear_scheduled_hook( 'ussa_process_queue' );
    }

    /**
     * WooCommerce missing notice.
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'Unstoppable Stock Alerts requires WooCommerce to be installed and active.', 'unstoppable-stock-alerts' ); ?></p>
        </div>
        <?php
    }
}

/**
 * Get main plugin instance.
 *
 * @return Unstoppable_Stock_Alerts
 */
function USSA() {
    return Unstoppable_Stock_Alerts::instance();
}

// Initialize.
USSA();
