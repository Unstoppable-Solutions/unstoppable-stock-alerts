/**
 * Unstoppable Stock Alerts - Admin JavaScript
 *
 * Handles admin dashboard, subscriber management, and charts
 *
 * @package Unstoppable_Stock_Alerts
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Admin Handler
     */
    var USSA_Admin = {
        
        /**
         * Chart instances
         */
        charts: {},

        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initCharts();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Delete subscriber
            $(document).on('click', '.ussa-delete-btn', this.handleDelete);
            
            // Send notification
            $(document).on('click', '.ussa-send-btn', this.handleSendNotification);
            
            // Bulk actions
            $(document).on('click', '#ussa-bulk-apply', this.handleBulkAction);
            
            // Select all checkbox
            $(document).on('change', '#ussa-select-all', this.handleSelectAll);
            
            // Filter form
            $(document).on('submit', '.ussa-filter-form', this.handleFilter);
            
            // Export buttons
            $(document).on('click', '.ussa-export-btn', this.handleExport);
        },

        /**
         * Initialize charts on dashboard
         */
        initCharts: function() {
            // Subscriptions chart
            var $subChart = $('#ussa-subscriptions-chart');
            if ($subChart.length && typeof Chart !== 'undefined') {
                this.initSubscriptionsChart($subChart);
            }
            
            // Notifications chart
            var $notifChart = $('#ussa-notifications-chart');
            if ($notifChart.length && typeof Chart !== 'undefined') {
                this.initNotificationsChart($notifChart);
            }
        },

        /**
         * Initialize subscriptions chart
         */
        initSubscriptionsChart: function($canvas) {
            var data = $canvas.data('chart');
            if (!data) return;
            
            var ctx = $canvas[0].getContext('2d');
            
            this.charts.subscriptions = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: ussa_admin.i18n.subscriptions || 'Subscriptions',
                        data: data.values,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        },

        /**
         * Initialize notifications chart
         */
        initNotificationsChart: function($canvas) {
            var data = $canvas.data('chart');
            if (!data) return;
            
            var ctx = $canvas[0].getContext('2d');
            
            this.charts.notifications = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: ussa_admin.i18n.notifications || 'Notifications',
                        data: data.values,
                        backgroundColor: 'rgba(79, 172, 254, 0.8)',
                        borderColor: '#4facfe',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });
        },

        /**
         * Handle delete subscriber
         */
        handleDelete: function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $row = $btn.closest('tr');
            var subscriberId = $btn.data('id');
            
            // Confirm deletion
            if (!confirm(ussa_admin.i18n.confirm_delete)) {
                return;
            }
            
            // Show loading state
            $btn.prop('disabled', true).text(ussa_admin.i18n.deleting);
            $row.addClass('ussa-loading');
            
            // Make AJAX request
            $.ajax({
                url: ussa_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'ussa_admin_delete',
                    nonce: ussa_admin.nonce,
                    subscriber_id: subscriberId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove row with animation
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            USSA_Admin.updateRowCount(-1);
                        });
                    } else {
                        alert(response.data.message || 'Error deleting subscriber');
                        $btn.prop('disabled', false).text(ussa_admin.i18n.delete || 'Delete');
                        $row.removeClass('ussa-loading');
                    }
                },
                error: function() {
                    alert('Error deleting subscriber');
                    $btn.prop('disabled', false).text(ussa_admin.i18n.delete || 'Delete');
                    $row.removeClass('ussa-loading');
                }
            });
        },

        /**
         * Handle send notification
         */
        handleSendNotification: function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $row = $btn.closest('tr');
            var subscriberId = $btn.data('id');
            
            // Confirm send
            if (!confirm(ussa_admin.i18n.confirm_notify)) {
                return;
            }
            
            // Show loading state
            $btn.prop('disabled', true).text(ussa_admin.i18n.sending);
            $row.addClass('ussa-loading');
            
            // Make AJAX request
            $.ajax({
                url: ussa_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'ussa_admin_send',
                    nonce: ussa_admin.nonce,
                    subscriber_id: subscriberId
                },
                success: function(response) {
                    if (response.success) {
                        // Update row status
                        $row.find('.ussa-status-badge')
                            .removeClass('ussa-status-active')
                            .addClass('ussa-status-notified')
                            .text('Notified');
                        
                        // Disable send button
                        $btn.hide();
                        $row.removeClass('ussa-loading');
                        
                        // Show success notice
                        USSA_Admin.showNotice('success', response.data.message || 'Notification sent!');
                    } else {
                        alert(response.data.message || 'Error sending notification');
                        $btn.prop('disabled', false).text(ussa_admin.i18n.send || 'Send');
                        $row.removeClass('ussa-loading');
                    }
                },
                error: function() {
                    alert('Error sending notification');
                    $btn.prop('disabled', false).text(ussa_admin.i18n.send || 'Send');
                    $row.removeClass('ussa-loading');
                }
            });
        },

        /**
         * Handle bulk action
         */
        handleBulkAction: function(e) {
            e.preventDefault();
            
            var action = $('#ussa-bulk-action').val();
            if (!action) {
                alert('Please select an action');
                return;
            }
            
            var $checkboxes = $('input[name="subscriber_ids[]"]:checked');
            if ($checkboxes.length === 0) {
                alert('Please select at least one subscriber');
                return;
            }
            
            // Confirm action
            var confirmMsg = action === 'delete' 
                ? 'Delete ' + $checkboxes.length + ' subscriber(s)?'
                : 'Send notification to ' + $checkboxes.length + ' subscriber(s)?';
            
            if (!confirm(confirmMsg)) {
                return;
            }
            
            var ids = [];
            $checkboxes.each(function() {
                ids.push($(this).val());
            });
            
            // Show loading
            $('#ussa-bulk-apply').prop('disabled', true).text('Processing...');
            
            $.ajax({
                url: ussa_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'ussa_admin_bulk',
                    nonce: ussa_admin.nonce,
                    bulk_action: action,
                    subscriber_ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error processing bulk action');
                        $('#ussa-bulk-apply').prop('disabled', false).text('Apply');
                    }
                },
                error: function() {
                    alert('Error processing bulk action');
                    $('#ussa-bulk-apply').prop('disabled', false).text('Apply');
                }
            });
        },

        /**
         * Handle select all checkbox
         */
        handleSelectAll: function() {
            var isChecked = $(this).prop('checked');
            $('input[name="subscriber_ids[]"]').prop('checked', isChecked);
        },

        /**
         * Handle filter form
         */
        handleFilter: function(e) {
            // Let the form submit normally for server-side filtering
        },

        /**
         * Handle export
         */
        handleExport: function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var type = $btn.data('type');
            var format = $btn.data('format') || 'csv';
            
            // Build export URL
            var url = ussa_admin.ajax_url + '?action=ussa_export&type=' + type + '&format=' + format + '&nonce=' + ussa_admin.nonce;
            
            // Add current filters
            var product = $('#ussa-filter-product').val();
            var status = $('#ussa-filter-status').val();
            
            if (product) url += '&product_id=' + product;
            if (status) url += '&status=' + status;
            
            // Trigger download
            window.location.href = url;
        },

        /**
         * Update row count display
         */
        updateRowCount: function(change) {
            var $count = $('.ussa-pagination-info .count');
            if ($count.length) {
                var current = parseInt($count.text(), 10) || 0;
                $count.text(current + change);
            }
        },

        /**
         * Show admin notice
         */
        showNotice: function(type, message) {
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible ussa-admin-notice"><p>' + message + '</p></div>');
            
            $('.ussa-admin-wrap h1').after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        USSA_Admin.init();
    });

})(jQuery);
