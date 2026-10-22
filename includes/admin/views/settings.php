<?php
/**
 * Admin Settings View
 *
 * @package Unstoppable_Stock_Alerts
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variables

// Handle settings save.
if ( isset( $_POST['ussa_save_settings'] ) && isset( $_POST['ussa_settings_nonce'] ) ) {
    if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ussa_settings_nonce'] ) ), 'ussa_save_settings' ) && current_user_can( 'manage_woocommerce' ) ) {
        
        // Form settings.
        update_option( 'ussa_form_title', isset( $_POST['ussa_form_title'] ) ? sanitize_text_field( wp_unslash( $_POST['ussa_form_title'] ) ) : '' );
        update_option( 'ussa_button_text', isset( $_POST['ussa_button_text'] ) ? sanitize_text_field( wp_unslash( $_POST['ussa_button_text'] ) ) : '' );
        update_option( 'ussa_success_message', isset( $_POST['ussa_success_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ussa_success_message'] ) ) : '' );
        update_option( 'ussa_show_subscriber_count', isset( $_POST['ussa_show_subscriber_count'] ) ? 'yes' : 'no' );
        update_option( 'ussa_subscriber_count_text', isset( $_POST['ussa_subscriber_count_text'] ) ? sanitize_text_field( wp_unslash( $_POST['ussa_subscriber_count_text'] ) ) : '' );
        
        // Behavior settings.
        update_option( 'ussa_require_login', isset( $_POST['ussa_require_login'] ) ? 'yes' : 'no' );
        update_option( 'ussa_double_optin', isset( $_POST['ussa_double_optin'] ) ? 'yes' : 'no' );
        update_option( 'ussa_guest_checkout_only', isset( $_POST['ussa_guest_checkout_only'] ) ? 'yes' : 'no' );
        
        // Email settings.
        update_option( 'ussa_email_from_name', isset( $_POST['ussa_email_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ussa_email_from_name'] ) ) : '' );
        update_option( 'ussa_email_from_address', isset( $_POST['ussa_email_from_address'] ) ? sanitize_email( wp_unslash( $_POST['ussa_email_from_address'] ) ) : '' );
        
        // Queue settings.
        $batch_size = isset( $_POST['ussa_batch_size'] ) ? absint( $_POST['ussa_batch_size'] ) : 50;
        update_option( 'ussa_batch_size', max( 10, min( 200, $batch_size ) ) );
        
        $throttle_hours = isset( $_POST['ussa_throttle_hours'] ) ? absint( $_POST['ussa_throttle_hours'] ) : 24;
        update_option( 'ussa_throttle_hours', max( 1, min( 168, $throttle_hours ) ) );
        
        // Auto-delete settings.
        update_option( 'ussa_auto_delete_notified', isset( $_POST['ussa_auto_delete_notified'] ) ? 'yes' : 'no' );
        $delete_after = isset( $_POST['ussa_delete_after_days'] ) ? absint( $_POST['ussa_delete_after_days'] ) : 30;
        update_option( 'ussa_delete_after_days', max( 1, min( 365, $delete_after ) ) );
        
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved successfully.', 'unstoppable-stock-alerts' ) . '</p></div>';
    }
}

// Get current settings.
$form_title            = get_option( 'ussa_form_title', __( 'Get notified when this product is back in stock', 'unstoppable-stock-alerts' ) );
$button_text           = get_option( 'ussa_button_text', __( 'Notify Me', 'unstoppable-stock-alerts' ) );
$success_message       = get_option( 'ussa_success_message', __( 'You will be notified when this product is back in stock.', 'unstoppable-stock-alerts' ) );
$show_subscriber_count = get_option( 'ussa_show_subscriber_count', 'no' );
$subscriber_count_text = get_option( 'ussa_subscriber_count_text', __( '{count} people are waiting for this product', 'unstoppable-stock-alerts' ) );
$require_login         = get_option( 'ussa_require_login', 'no' );
$double_optin          = get_option( 'ussa_double_optin', 'no' );
$guest_checkout_only   = get_option( 'ussa_guest_checkout_only', 'no' );
$email_from_name       = get_option( 'ussa_email_from_name', get_bloginfo( 'name' ) );
$email_from_address    = get_option( 'ussa_email_from_address', get_option( 'admin_email' ) );
$batch_size            = get_option( 'ussa_batch_size', 50 );
$throttle_hours        = get_option( 'ussa_throttle_hours', 24 );
$auto_delete_notified  = get_option( 'ussa_auto_delete_notified', 'no' );
$delete_after_days     = get_option( 'ussa_delete_after_days', 30 );
?>

<div class="ussa-settings-view">
    <form method="post" action="" class="ussa-settings-form">
        <?php wp_nonce_field( 'ussa_save_settings', 'ussa_settings_nonce' ); ?>
        
        <!-- Form Appearance Settings -->
        <div class="ussa-settings-section">
            <h2>
                <span class="dashicons dashicons-admin-appearance"></span>
                <?php esc_html_e( 'Form Appearance', 'unstoppable-stock-alerts' ); ?>
            </h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ussa_form_title"><?php esc_html_e( 'Form Title', 'unstoppable-stock-alerts' ); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="ussa_form_title" 
                               id="ussa_form_title" 
                               value="<?php echo esc_attr( $form_title ); ?>" 
                               class="regular-text">
                        <p class="description"><?php esc_html_e( 'The heading displayed above the notification form.', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ussa_button_text"><?php esc_html_e( 'Button Text', 'unstoppable-stock-alerts' ); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="ussa_button_text" 
                               id="ussa_button_text" 
                               value="<?php echo esc_attr( $button_text ); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ussa_success_message"><?php esc_html_e( 'Success Message', 'unstoppable-stock-alerts' ); ?></label>
                    </th>
                    <td>
                        <textarea name="ussa_success_message" 
                                  id="ussa_success_message" 
                                  class="large-text" 
                                  rows="3"><?php echo esc_textarea( $success_message ); ?></textarea>
                        <p class="description"><?php esc_html_e( 'Message shown after a successful subscription.', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Show Subscriber Count', 'unstoppable-stock-alerts' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="ussa_show_subscriber_count" 
                                   value="1" 
                                   <?php checked( $show_subscriber_count, 'yes' ); ?>>
                            <?php esc_html_e( 'Display how many people are waiting for this product', 'unstoppable-stock-alerts' ); ?>
                        </label>
                    </td>
                </tr>
                <tr class="ussa-conditional" data-depends="ussa_show_subscriber_count">
                    <th scope="row">
                        <label for="ussa_subscriber_count_text"><?php esc_html_e( 'Count Text', 'unstoppable-stock-alerts' ); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="ussa_subscriber_count_text" 
                               id="ussa_subscriber_count_text" 
                               value="<?php echo esc_attr( $subscriber_count_text ); ?>" 
                               class="regular-text">
                        <p class="description"><?php esc_html_e( 'Use {count} as a placeholder for the number. Example: "{count} people are waiting"', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Subscription Behavior Settings -->
        <div class="ussa-settings-section">
            <h2>
                <span class="dashicons dashicons-admin-users"></span>
                <?php esc_html_e( 'Subscription Behavior', 'unstoppable-stock-alerts' ); ?>
            </h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Login Required', 'unstoppable-stock-alerts' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="ussa_require_login" 
                                   value="1" 
                                   <?php checked( $require_login, 'yes' ); ?>>
                            <?php esc_html_e( 'Users must be logged in to subscribe', 'unstoppable-stock-alerts' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'If enabled, guests will be prompted to log in or register.', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Double Opt-In', 'unstoppable-stock-alerts' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="ussa_double_optin" 
                                   value="1" 
                                   <?php checked( $double_optin, 'yes' ); ?>>
                            <?php esc_html_e( 'Require email verification before subscribing', 'unstoppable-stock-alerts' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'Subscribers will receive a verification email to confirm their subscription.', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Guest Checkout Only', 'unstoppable-stock-alerts' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="ussa_guest_checkout_only" 
                                   value="1" 
                                   <?php checked( $guest_checkout_only, 'yes' ); ?>>
                            <?php esc_html_e( 'Only show form when product allows guest checkout', 'unstoppable-stock-alerts' ); ?>
                        </label>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Email Settings -->
        <div class="ussa-settings-section">
            <h2>
                <span class="dashicons dashicons-email-alt"></span>
                <?php esc_html_e( 'Email Settings', 'unstoppable-stock-alerts' ); ?>
            </h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ussa_email_from_name"><?php esc_html_e( 'From Name', 'unstoppable-stock-alerts' ); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               name="ussa_email_from_name" 
                               id="ussa_email_from_name" 
                               value="<?php echo esc_attr( $email_from_name ); ?>" 
                               class="regular-text">
                        <p class="description"><?php esc_html_e( 'The name that appears in the "From" field of notification emails.', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ussa_email_from_address"><?php esc_html_e( 'From Email', 'unstoppable-stock-alerts' ); ?></label>
                    </th>
                    <td>
                        <input type="email" 
                               name="ussa_email_from_address" 
                               id="ussa_email_from_address" 
                               value="<?php echo esc_attr( $email_from_address ); ?>" 
                               class="regular-text">
                        <p class="description"><?php esc_html_e( 'The email address used to send notifications.', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Email Templates', 'unstoppable-stock-alerts' ); ?></th>
                    <td>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=email' ) ); ?>" class="button">
                            <?php esc_html_e( 'Manage Email Templates', 'unstoppable-stock-alerts' ); ?>
                        </a>
                        <p class="description"><?php esc_html_e( 'Customize email templates in WooCommerce → Settings → Emails.', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Queue & Performance Settings -->
        <div class="ussa-settings-section">
            <h2>
                <span class="dashicons dashicons-performance"></span>
                <?php esc_html_e( 'Queue & Performance', 'unstoppable-stock-alerts' ); ?>
            </h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="ussa_batch_size"><?php esc_html_e( 'Batch Size', 'unstoppable-stock-alerts' ); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               name="ussa_batch_size" 
                               id="ussa_batch_size" 
                               value="<?php echo esc_attr( $batch_size ); ?>" 
                               min="10" 
                               max="200" 
                               step="10" 
                               class="small-text">
                        <p class="description"><?php esc_html_e( 'Number of emails to send per cron run. Higher values send faster but may hit server limits. (10-200)', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="ussa_throttle_hours"><?php esc_html_e( 'Notification Throttle', 'unstoppable-stock-alerts' ); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               name="ussa_throttle_hours" 
                               id="ussa_throttle_hours" 
                               value="<?php echo esc_attr( $throttle_hours ); ?>" 
                               min="1" 
                               max="168" 
                               class="small-text"> <?php esc_html_e( 'hours', 'unstoppable-stock-alerts' ); ?>
                        <p class="description"><?php esc_html_e( 'Minimum hours between notifications for the same product. Prevents spam if stock fluctuates. (1-168)', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Data Management Settings -->
        <div class="ussa-settings-section">
            <h2>
                <span class="dashicons dashicons-database"></span>
                <?php esc_html_e( 'Data Management', 'unstoppable-stock-alerts' ); ?>
            </h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Auto-Delete Notified', 'unstoppable-stock-alerts' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   name="ussa_auto_delete_notified" 
                                   value="1" 
                                   <?php checked( $auto_delete_notified, 'yes' ); ?>>
                            <?php esc_html_e( 'Automatically delete subscribers after they have been notified', 'unstoppable-stock-alerts' ); ?>
                        </label>
                    </td>
                </tr>
                <tr class="ussa-conditional" data-depends="ussa_auto_delete_notified">
                    <th scope="row">
                        <label for="ussa_delete_after_days"><?php esc_html_e( 'Delete After', 'unstoppable-stock-alerts' ); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               name="ussa_delete_after_days" 
                               id="ussa_delete_after_days" 
                               value="<?php echo esc_attr( $delete_after_days ); ?>" 
                               min="1" 
                               max="365" 
                               class="small-text"> <?php esc_html_e( 'days after notification', 'unstoppable-stock-alerts' ); ?>
                        <p class="description"><?php esc_html_e( 'Notified subscribers will be deleted after this many days.', 'unstoppable-stock-alerts' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="ussa-settings-submit">
            <button type="submit" name="ussa_save_settings" class="button button-primary button-large">
                <span class="dashicons dashicons-saved" style="margin-top: 4px;"></span>
                <?php esc_html_e( 'Save Settings', 'unstoppable-stock-alerts' ); ?>
            </button>
        </div>
    </form>
</div>
