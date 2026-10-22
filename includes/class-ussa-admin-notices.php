<?php
/**
 * Admin Notices Class
 *
 * Handles all admin dashboard notices for the plugin.
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin Notices Class.
 */
class USSA_Admin_Notices {

    /**
     * Plugin prefix.
     *
     * @var string
     */
    private $prefix = 'ussa';

    /**
     * Plugin name.
     *
     * @var string
     */
    private $plugin_name = 'Unstoppable Stock Alerts';

    /**
     * Dashboard URL.
     *
     * @var string
     */
    private $dashboard_url = 'https://unstoppablesolutions.app/Dashboard';

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_notices', array( $this, 'display_notices' ) );
        add_action( 'admin_notices', array( $this, 'display_flash_notices' ) );
        add_action( 'wp_ajax_' . $this->prefix . '_dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Enqueue admin scripts for notice dismissal.
     *
     * @param string $hook Current page hook.
     */
    public function enqueue_scripts( $hook ) {
        // Only load on relevant pages.
        $allowed_pages = array( 'dashboard', 'plugins.php', 'index.php' );
        $is_plugin_page = strpos( $hook, $this->prefix ) !== false || strpos( $hook, 'unstoppable-studio' ) !== false;
        $is_allowed = in_array( basename( $hook ), $allowed_pages, true ) || $is_plugin_page;

        if ( ! $is_allowed ) {
            return;
        }

        wp_add_inline_script( 'jquery', $this->get_dismiss_script() );
    }

    /**
     * Get dismiss script.
     *
     * @return string JavaScript for notice dismissal.
     */
    private function get_dismiss_script() {
        $nonce = wp_create_nonce( $this->prefix . '_dismiss_notice' );
        return "
        jQuery(document).ready(function($) {
            $(document).on('click', '.{$this->prefix}-notice .notice-dismiss, .{$this->prefix}-dismiss-notice', function(e) {
                var \$notice = $(this).closest('.{$this->prefix}-notice');
                var noticeType = \$notice.data('notice');
                var announcementId = \$notice.data('announcement-id') || '';

                if (noticeType) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: '{$this->prefix}_dismiss_notice',
                            notice: noticeType,
                            announcement_id: announcementId,
                            nonce: '{$nonce}'
                        }
                    });
                }

                if ($(this).hasClass('{$this->prefix}-dismiss-notice')) {
                    e.preventDefault();
                    \$notice.fadeOut();
                }
            });
        });
        ";
    }

    /**
     * Display all active notices.
     */
    public function display_notices() {
        // Welcome/Setup notice (shows once after activation).
        $this->maybe_show_welcome_notice();

        // Review request (shows after 7 days of use).
        $this->maybe_show_review_notice();

        // Important announcements.
        $this->maybe_show_announcement_notice();
    }

    /**
     * Welcome/Setup notice after plugin activation.
     */
    private function maybe_show_welcome_notice() {
        // Check if already dismissed.
        if ( get_option( $this->prefix . '_welcome_notice_dismissed' ) ) {
            return;
        }

        // Only show to admins.
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        // Only show on dashboard, WooCommerce, and Options Studio pages.
        $screen = get_current_screen();
        if ( ! $screen ) {
            return;
        }

        $is_allowed = $screen->id === 'dashboard'
            || strpos( $screen->id, $this->prefix ) !== false
            || strpos( $screen->id, 'unstoppable-studio' ) !== false
            || strpos( $screen->id, 'woocommerce' ) !== false;

        if ( ! $is_allowed ) {
            return;
        }

        ?>
        <div class="notice notice-success is-dismissible <?php echo esc_attr( $this->prefix ); ?>-notice" data-notice="welcome">
            <p>
                <strong><?php echo esc_html( $this->plugin_name ); ?></strong> —
                <?php esc_html_e( 'Thanks for installing! Get started by configuring your settings.', 'unstoppable-stock-alerts' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=ussa-dashboard&tab=settings' ) ); ?>" class="button button-primary" style="margin-left: 10px;">
                    <?php esc_html_e( 'Go to Settings', 'unstoppable-stock-alerts' ); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Review request notice after 7 days.
     */
    private function maybe_show_review_notice() {
        // Check if already dismissed.
        if ( get_option( $this->prefix . '_review_notice_dismissed' ) ) {
            return;
        }

        // Check install date.
        $install_date = get_option( $this->prefix . '_install_date' );
        if ( ! $install_date ) {
            return;
        }

        // Show after 7 days.
        if ( time() < ( $install_date + ( 7 * DAY_IN_SECONDS ) ) ) {
            return;
        }

        // Only show to admins.
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        // Only show on plugin or Options Studio pages.
        $screen = get_current_screen();
        if ( ! $screen || ( strpos( $screen->id, $this->prefix ) === false && strpos( $screen->id, 'unstoppable-studio' ) === false ) ) {
            return;
        }

        ?>
        <div class="notice notice-info is-dismissible <?php echo esc_attr( $this->prefix ); ?>-notice" data-notice="review">
            <p>
                <?php
                printf(
                    /* translators: %s: Plugin name */
                    esc_html__( 'Enjoying %s? Please consider leaving a review to help others discover the plugin!', 'unstoppable-stock-alerts' ),
                    '<strong>' . esc_html( $this->plugin_name ) . '</strong>'
                );
                ?>
                <a href="https://wordpress.org/support/plugin/unstoppable-stock-alerts/reviews/#new-post" target="_blank" class="button button-primary" style="margin-left: 10px;">
                    <?php esc_html_e( 'Leave a Review', 'unstoppable-stock-alerts' ); ?>
                </a>
                <button type="button" class="button <?php echo esc_attr( $this->prefix ); ?>-dismiss-notice" data-notice="review" style="margin-left: 5px;">
                    <?php esc_html_e( 'Maybe Later', 'unstoppable-stock-alerts' ); ?>
                </button>
            </p>
        </div>
        <?php
    }

    /**
     * Important announcements (security updates, breaking changes, etc.)
     */
    private function maybe_show_announcement_notice() {
        // Get announcement from options (can be set via remote API or manually).
        $announcement = get_option( $this->prefix . '_announcement' );

        if ( empty( $announcement ) || empty( $announcement['message'] ) ) {
            return;
        }

        // Check if this announcement was dismissed.
        $dismissed_id = get_option( $this->prefix . '_announcement_dismissed_id' );
        if ( ! empty( $announcement['id'] ) && $dismissed_id === $announcement['id'] ) {
            return;
        }

        // Only show to admins.
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $type = isset( $announcement['type'] ) ? $announcement['type'] : 'info';
        $allowed_types = array( 'info', 'warning', 'error', 'success' );
        if ( ! in_array( $type, $allowed_types, true ) ) {
            $type = 'info';
        }

        ?>
        <div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible <?php echo esc_attr( $this->prefix ); ?>-notice" data-notice="announcement" data-announcement-id="<?php echo esc_attr( $announcement['id'] ?? '' ); ?>">
            <p>
                <strong><?php echo esc_html( $this->plugin_name ); ?></strong> —
                <?php echo wp_kses_post( $announcement['message'] ); ?>
                <?php if ( ! empty( $announcement['link'] ) && ! empty( $announcement['link_text'] ) ) : ?>
                    <a href="<?php echo esc_url( $announcement['link'] ); ?>" target="_blank" class="button" style="margin-left: 10px;">
                        <?php echo esc_html( $announcement['link_text'] ); ?>
                    </a>
                <?php endif; ?>
            </p>
        </div>
        <?php
    }

    /**
     * AJAX handler to dismiss notices.
     */
    public function ajax_dismiss_notice() {
        // Verify nonce.
        check_ajax_referer( $this->prefix . '_dismiss_notice', 'nonce' );

        // Verify permissions.
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $notice = isset( $_POST['notice'] ) ? sanitize_key( wp_unslash( $_POST['notice'] ) ) : '';

        if ( empty( $notice ) ) {
            wp_send_json_error( 'Invalid notice.' );
        }

        // Save dismissal.
        update_option( $this->prefix . '_' . $notice . '_notice_dismissed', true );

        // For notices that re-show after a period, save the date.
        if ( in_array( $notice, array( 'upgrade' ), true ) ) {
            update_option( $this->prefix . '_' . $notice . '_notice_dismissed_date', time() );
        }

        // For announcements, save the specific announcement ID.
        if ( $notice === 'announcement' ) {
            $announcement_id = isset( $_POST['announcement_id'] ) ? sanitize_key( wp_unslash( $_POST['announcement_id'] ) ) : '';
            if ( $announcement_id ) {
                update_option( $this->prefix . '_announcement_dismissed_id', $announcement_id );
            }
        }

        wp_send_json_success();
    }

    /**
     * Display flash notices (success/error messages).
     */
    public function display_flash_notices() {
        $notices = get_transient( $this->prefix . '_flash_notices' );

        if ( empty( $notices ) || ! is_array( $notices ) ) {
            return;
        }

        foreach ( $notices as $notice ) {
            $type    = isset( $notice['type'] ) ? $notice['type'] : 'info';
            $message = isset( $notice['message'] ) ? $notice['message'] : '';

            if ( empty( $message ) ) {
                continue;
            }

            $allowed_types = array( 'success', 'error', 'warning', 'info' );
            if ( ! in_array( $type, $allowed_types, true ) ) {
                $type = 'info';
            }

            ?>
            <div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
                <p><?php echo wp_kses_post( $message ); ?></p>
            </div>
            <?php
        }

        // Clear flash notices after displaying.
        delete_transient( $this->prefix . '_flash_notices' );
    }

    /**
     * Add a flash notice to be displayed on next page load.
     *
     * @param string $message The notice message.
     * @param string $type    Notice type: success, error, warning, info.
     */
    public static function add_flash_notice( $message, $type = 'success' ) {
        $prefix  = 'ussa';
        $notices = get_transient( $prefix . '_flash_notices' );

        if ( ! is_array( $notices ) ) {
            $notices = array();
        }

        $notices[] = array(
            'message' => $message,
            'type'    => $type,
        );

        set_transient( $prefix . '_flash_notices', $notices, 60 );
    }
}
