<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Devsroom_GReviews_Admin_Settings {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );

        // API Key mode AJAX handlers.
        add_action( 'wp_ajax_devsroom_greviews_test_fetch', array( $this, 'ajax_test_fetch' ) );
        add_action( 'wp_ajax_devsroom_greviews_clear_cache', array( $this, 'ajax_clear_cache' ) );

        // OAuth mode AJAX handlers.
        add_action( 'wp_ajax_devsroom_greviews_oauth_start', array( $this, 'ajax_oauth_start' ) );
        add_action( 'wp_ajax_devsroom_greviews_oauth_callback', array( $this, 'ajax_oauth_callback' ) );
        add_action( 'wp_ajax_devsroom_greviews_sync_now', array( $this, 'ajax_sync_now' ) );
        add_action( 'wp_ajax_devsroom_greviews_oauth_disconnect', array( $this, 'ajax_oauth_disconnect' ) );
        add_action( 'wp_ajax_devsroom_greviews_fetch_locations', array( $this, 'ajax_fetch_locations' ) );
        add_action( 'wp_ajax_devsroom_greviews_save_oauth_location', array( $this, 'ajax_save_oauth_location' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

        // Schedule / unschedule cron on settings save.
        add_action( 'update_option_devsroom_greviews_sync_interval', array( $this, 'handle_sync_interval_change' ), 10, 2 );
        add_action( 'update_option_devsroom_greviews_connection_mode', array( $this, 'handle_connection_mode_change' ), 10, 2 );
    }

    /**
     * Add top-level menu page.
     */
    public function add_settings_page() {
        add_menu_page(
            __( 'Devsroom Google Reviews', 'devsroom-google-review-showcase' ),
            __( 'Google Reviews', 'devsroom-google-review-showcase' ),
            'manage_options',
            'devsroom-google-reviews',
            array( $this, 'render_settings_page' ),
            'dashicons-star-half',
            80
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
        // API Key mode settings.
        register_setting( 'devsroom_greviews_settings', 'devsroom_greviews_api_key', array(
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        register_setting( 'devsroom_greviews_settings', 'devsroom_greviews_place_id', array(
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        register_setting( 'devsroom_greviews_settings', 'devsroom_greviews_cache_duration', array(
            'sanitize_callback' => 'absint',
            'default'           => 24,
        ) );

        // Connection mode.
        register_setting( 'devsroom_greviews_settings', 'devsroom_greviews_connection_mode', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'api_key',
        ) );

        // OAuth settings.
        register_setting( 'devsroom_greviews_settings', 'devsroom_greviews_oauth_client_id', array(
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        register_setting( 'devsroom_greviews_settings', 'devsroom_greviews_oauth_client_secret', array(
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        register_setting( 'devsroom_greviews_settings', 'devsroom_greviews_sync_interval', array(
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => 'daily',
        ) );

        register_setting( 'devsroom_greviews_settings', 'devsroom_greviews_oauth_location_name', array(
            'sanitize_callback' => 'sanitize_text_field',
        ) );
    }

    /**
     * Enqueue admin JS for AJAX buttons.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'toplevel_page_devsroom-google-reviews' !== $hook ) {
            return;
        }

        // OAuth connect JS.
        wp_enqueue_script(
            'devsroom-greviews-oauth-connect',
            DEVSROOM_GREVIEWS_URL . 'assets/js/oauth-connect.js',
            array( 'jquery' ),
            DEVSROOM_GREVIEWS_VERSION,
            true
        );

        wp_localize_script( 'devsroom-greviews-oauth-connect', 'devsroom_greviews_admin', array(
            'sync_nonce'       => wp_create_nonce( 'devsroom_greviews_sync_now' ),
            'disconnect_nonce' => wp_create_nonce( 'devsroom_greviews_oauth_disconnect' ),
            'locations_nonce'  => wp_create_nonce( 'devsroom_greviews_fetch_locations' ),
        ) );

        // Inline JS for API Key mode buttons (Test Fetch, Clear Cache).
        wp_add_inline_script( 'jquery', '
            jQuery(document).ready(function($) {
                // Test Fetch
                $("#devsroom-greviews-test-fetch").on("click", function(e) {
                    e.preventDefault();
                    var btn = $(this);
                    btn.prop("disabled", true).text("' . esc_js( __( 'Fetching...', 'devsroom-google-review-showcase' ) ) . '");
                    var resultEl = $("#devsroom-greviews-test-result");

                    $.post(ajaxurl, {
                        action: "devsroom_greviews_test_fetch",
                        nonce: "' . esc_js( wp_create_nonce( 'devsroom_greviews_test_fetch' ) ) . '"
                    }, function(response) {
                        btn.prop("disabled", false).text("' . esc_js( __( 'Test Fetch', 'devsroom-google-review-showcase' ) ) . '");
                        if (response.success) {
                            resultEl.html("<span style=\"color:green;\">" + response.data.message + "</span>");
                        } else {
                            resultEl.html("<span style=\"color:red;\">" + response.data.message + "</span>");
                        }
                    }).fail(function() {
                        btn.prop("disabled", false).text("' . esc_js( __( 'Test Fetch', 'devsroom-google-review-showcase' ) ) . '");
                        resultEl.html("<span style=\"color:red;\">' . esc_js( __( 'Request failed.', 'devsroom-google-review-showcase' ) ) . '</span>");
                    });
                });

                // Clear Cache
                $("#devsroom-greviews-clear-cache").on("click", function(e) {
                    e.preventDefault();
                    var btn = $(this);
                    btn.prop("disabled", true).text("' . esc_js( __( 'Clearing...', 'devsroom-google-review-showcase' ) ) . '");
                    var resultEl = $("#devsroom-greviews-cache-result");

                    $.post(ajaxurl, {
                        action: "devsroom_greviews_clear_cache",
                        nonce: "' . esc_js( wp_create_nonce( 'devsroom_greviews_clear_cache' ) ) . '"
                    }, function(response) {
                        btn.prop("disabled", false).text("' . esc_js( __( 'Clear Cache', 'devsroom-google-review-showcase' ) ) . '");
                        if (response.success) {
                            resultEl.html("<span style=\"color:green;\">" + response.data.message + "</span>");
                        } else {
                            resultEl.html("<span style=\"color:red;\">" + response.data.message + "</span>");
                        }
                    }).fail(function() {
                        btn.prop("disabled", false).text("' . esc_js( __( 'Clear Cache', 'devsroom-google-review-showcase' ) ) . '");
                        resultEl.html("<span style=\"color:red;\">' . esc_js( __( 'Request failed.', 'devsroom-google-review-showcase' ) ) . '</span>");
                    });
                });
            });
        ' );
    }

    // =========================================================================
    // AJAX Handlers — API Key Mode
    // =========================================================================

    /**
     * AJAX handler for Test Fetch.
     */
    public function ajax_test_fetch() {
        check_ajax_referer( 'devsroom_greviews_test_fetch', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'devsroom-google-review-showcase' ) ) );
        }

        $api = new Devsroom_GReviews_API_Fetch();
        $result = $api->test_fetch();

        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }

    /**
     * AJAX handler for Clear Cache.
     */
    public function ajax_clear_cache() {
        check_ajax_referer( 'devsroom_greviews_clear_cache', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'devsroom-google-review-showcase' ) ) );
        }

        $cache = new Devsroom_GReviews_Cache();
        $cache->clear();

        wp_send_json_success( array(
            'message' => __( 'Cache cleared successfully.', 'devsroom-google-review-showcase' ),
        ) );
    }

    // =========================================================================
    // AJAX Handlers — OAuth Mode
    // =========================================================================

    /**
     * AJAX handler — start OAuth flow (redirect to Google consent screen).
     */
    public function ajax_oauth_start() {
        $nonce = isset( $_GET['nonce'] ) ? sanitize_text_field( $_GET['nonce'] ) : '';
        if ( ! wp_verify_nonce( $nonce, 'devsroom_greviews_oauth_start' ) ) {
            wp_die( __( 'Security check failed.', 'devsroom-google-review-showcase' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized.', 'devsroom-google-review-showcase' ) );
        }

        $client_id = get_option( 'devsroom_greviews_oauth_client_id', '' );
        if ( empty( $client_id ) ) {
            wp_die( __( 'Client ID is not configured. Please save your settings first.', 'devsroom-google-review-showcase' ) );
        }

        $redirect_uri = admin_url( 'admin-ajax.php?action=devsroom_greviews_oauth_callback' );
        $state        = wp_create_nonce( 'devsroom_greviews_oauth_state' );

        $auth_url = add_query_arg( array(
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/business.manage https://www.googleapis.com/auth/userinfo.profile',
            'state'         => $state,
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ), 'https://accounts.google.com/o/oauth2/v2/auth' );

        wp_redirect( $auth_url );
        exit;
    }

    /**
     * AJAX handler — OAuth callback (Google redirects here after consent).
     */
    public function ajax_oauth_callback() {
        $state = isset( $_GET['state'] ) ? sanitize_text_field( $_GET['state'] ) : '';
        if ( ! wp_verify_nonce( $state, 'devsroom_greviews_oauth_state' ) ) {
            wp_die( __( 'Security check failed. Please try connecting again.', 'devsroom-google-review-showcase' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized.', 'devsroom-google-review-showcase' ) );
        }

        $code = isset( $_GET['code'] ) ? sanitize_text_field( $_GET['code'] ) : '';
        if ( empty( $code ) ) {
            $error = isset( $_GET['error'] ) ? sanitize_text_field( $_GET['error'] ) : 'unknown';
            $this->oauth_callback_close( false, __( 'Google authorization was denied or failed.', 'devsroom-google-review-showcase' ) );
            return;
        }

        $oauth  = new Devsroom_GReviews_OAuth();
        $result = $oauth->exchange_code( $code );

        if ( is_wp_error( $result ) ) {
            $this->oauth_callback_close( false, $result->get_error_message() );
            return;
        }

        // Fetch user info.
        $oauth->fetch_user_info();

        // Fetch accounts and locations.
        $api      = new Devsroom_GReviews_Google_API();
        $accounts = $api->get_accounts();

        if ( is_wp_error( $accounts ) ) {
            $this->oauth_callback_close( false, $accounts->get_error_message() );
            return;
        }

        $account_name = $accounts[0]['name'];
        update_option( 'devsroom_greviews_oauth_account_name', $account_name, false );

        $locations = $api->get_locations( $account_name );

        if ( is_wp_error( $locations ) || empty( $locations ) ) {
            $this->oauth_callback_close( false, __( 'No business locations found for this account.', 'devsroom-google-review-showcase' ) );
            return;
        }

        // Show location picker inside the popup.
        $user_name  = get_option( 'devsroom_greviews_oauth_user_name', '' );
        $user_email = get_option( 'devsroom_greviews_oauth_user_email', '' );
        $this->oauth_render_location_picker( $user_name, $user_email, $locations );
    }

    /**
     * Close the OAuth popup — call the parent's JS callback and die.
     */
    private function oauth_callback_close( $success, $error_message = '' ) {
        $settings_url = admin_url( 'admin.php?page=devsroom-google-reviews' );
        ?>
        <!DOCTYPE html>
        <html><head><title><?php $success ? esc_html_e( 'Connected!', 'devsroom-google-review-showcase' ) : esc_html_e( 'Connection Failed', 'devsroom-google-review-showcase' ); ?></title></head>
        <body>
        <p><?php $success ? esc_html_e( 'Connected successfully!', 'devsroom-google-review-showcase' ) : esc_html_e( 'Connection failed.', 'devsroom-google-review-showcase' ); ?></p>
        <?php if ( ! $success && $error_message ) : ?>
            <p style="color:red;"><?php echo esc_html( $error_message ); ?></p>
        <?php endif; ?>
        <script>
            if (window.opener && window.opener.devsroom_greviews_oauth_complete) {
                window.opener.devsroom_greviews_oauth_complete(<?php echo $success ? 'true' : 'false'; ?>);
            }
            window.close();
            setTimeout(function() { window.location.href = <?php echo wp_json_encode( $settings_url ); ?>; }, 2000);
        </script>
        </body></html>
        <?php
        exit;
    }

    /**
     * Render the location picker page inside the OAuth popup.
     */
    private function oauth_render_location_picker( $user_name, $user_email, $locations ) {
        $save_nonce   = wp_create_nonce( 'devsroom_greviews_save_oauth_location' );
        $ajax_url     = admin_url( 'admin-ajax.php' );
        $settings_url = admin_url( 'admin.php?page=devsroom-google-reviews' );

        // Build locations JS array.
        $js_locations = array();
        foreach ( $locations as $loc ) {
            $js_locations[] = array(
                'name'  => isset( $loc['name'] ) ? $loc['name'] : '',
                'title' => isset( $loc['title'] ) ? $loc['title'] : '',
            );
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title><?php esc_html_e( 'Select Business Location', 'devsroom-google-review-showcase' ); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: #f9fafb;
                    color: #1f2937;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    padding: 24px;
                }
                .picker-card {
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 4px 24px rgba(0,0,0,0.1);
                    padding: 32px;
                    max-width: 420px;
                    width: 100%;
                }
                .picker-card h2 {
                    font-size: 18px;
                    margin-bottom: 4px;
                }
                .picker-card .user-info {
                    color: #6b7280;
                    font-size: 13px;
                    margin-bottom: 20px;
                }
                .picker-card label {
                    display: block;
                    font-weight: 600;
                    font-size: 14px;
                    margin-bottom: 6px;
                }
                .picker-card select {
                    width: 100%;
                    padding: 10px 12px;
                    border: 1px solid #d1d5db;
                    border-radius: 6px;
                    font-size: 14px;
                    background: #fff;
                    margin-bottom: 20px;
                    cursor: pointer;
                }
                .picker-card select:focus {
                    outline: none;
                    border-color: #3b82f6;
                    box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
                }
                .picker-card .btn {
                    display: block;
                    width: 100%;
                    padding: 12px;
                    background: #2563eb;
                    color: #fff;
                    border: none;
                    border-radius: 6px;
                    font-size: 15px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: background 0.2s;
                }
                .picker-card .btn:hover { background: #1d4ed8; }
                .picker-card .btn:disabled { background: #93c5fd; cursor: not-allowed; }
                .picker-card .status {
                    text-align: center;
                    margin-top: 12px;
                    font-size: 13px;
                    color: #6b7280;
                }
                .picker-card .status.error { color: #ef4444; }
                .picker-card .success-icon {
                    width: 48px; height: 48px;
                    background: #10b981;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 16px;
                }
                .picker-card .success-icon svg {
                    width: 24px; height: 24px;
                    fill: none;
                    stroke: #fff;
                    stroke-width: 3;
                    stroke-linecap: round;
                    stroke-linejoin: round;
                }
            </style>
        </head>
        <body>
        <div class="picker-card">
            <div class="success-icon">
                <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <h2><?php esc_html_e( 'Connected Successfully', 'devsroom-google-review-showcase' ); ?></h2>
            <p class="user-info">
                <?php
                if ( $user_name ) {
                    echo esc_html( $user_name );
                    if ( $user_email ) {
                        echo ' (' . esc_html( $user_email ) . ')';
                    }
                }
                ?>
            </p>

            <label for="location-select"><?php esc_html_e( 'Select your business location', 'devsroom-google-review-showcase' ); ?></label>
            <select id="location-select">
                <?php foreach ( $locations as $i => $loc ) : ?>
                    <option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( isset( $loc['title'] ) ? $loc['title'] : $loc['name'] ); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="button" id="save-location-btn" class="btn"><?php esc_html_e( 'Select & Continue', 'devsroom-google-review-showcase' ); ?></button>
            <p id="save-status" class="status"></p>
        </div>

        <script>
            (function() {
                var locations = <?php echo wp_json_encode( $js_locations ); ?>;
                var ajaxUrl = <?php echo wp_json_encode( $ajax_url ); ?>;
                var nonce = <?php echo wp_json_encode( $save_nonce ); ?>;
                var settingsUrl = <?php echo wp_json_encode( $settings_url ); ?>;

                var btn = document.getElementById('save-location-btn');
                var select = document.getElementById('location-select');
                var status = document.getElementById('save-status');

                btn.addEventListener('click', function() {
                    var idx = select.value;
                    var loc = locations[idx];
                    if (!loc) return;

                    btn.disabled = true;
                    btn.textContent = 'Saving...';
                    status.textContent = '';
                    status.className = 'status';

                    var params = 'action=devsroom_greviews_save_oauth_location'
                        + '&nonce=' + encodeURIComponent(nonce)
                        + '&location_name=' + encodeURIComponent(loc.name)
                        + '&location_title=' + encodeURIComponent(loc.title);

                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', ajaxUrl, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        try {
                            var resp = JSON.parse(xhr.responseText);
                            if (resp.success) {
                                status.textContent = 'Done! Closing...';
                                status.className = 'status';
                                if (window.opener && window.opener.devsroom_greviews_oauth_complete) {
                                    window.opener.devsroom_greviews_oauth_complete(true);
                                }
                                window.close();
                                setTimeout(function() { window.location.href = settingsUrl; }, 2000);
                            } else {
                                status.textContent = resp.data && resp.data.message ? resp.data.message : 'Failed to save.';
                                status.className = 'status error';
                                btn.disabled = false;
                                btn.textContent = 'Select & Continue';
                            }
                        } catch (e) {
                            status.textContent = 'Unexpected error.';
                            status.className = 'status error';
                            btn.disabled = false;
                            btn.textContent = 'Select & Continue';
                        }
                    };
                    xhr.onerror = function() {
                        status.textContent = 'Request failed.';
                        status.className = 'status error';
                        btn.disabled = false;
                        btn.textContent = 'Select & Continue';
                    };
                    xhr.send(params);
                });
            })();
        </script>
        </body></html>
        <?php
        exit;
    }

    /**
     * AJAX handler — save the selected OAuth location from the popup.
     */
    public function ajax_save_oauth_location() {
        check_ajax_referer( 'devsroom_greviews_save_oauth_location', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'devsroom-google-review-showcase' ) ) );
        }

        $location_name  = isset( $_POST['location_name'] ) ? sanitize_text_field( $_POST['location_name'] ) : '';
        $location_title = isset( $_POST['location_title'] ) ? sanitize_text_field( $_POST['location_title'] ) : '';

        if ( empty( $location_name ) ) {
            wp_send_json_error( array( 'message' => __( 'No location selected.', 'devsroom-google-review-showcase' ) ) );
        }

        update_option( 'devsroom_greviews_oauth_location_name', $location_name, false );
        update_option( 'devsroom_greviews_oauth_business_name', $location_title, false );

        wp_send_json_success( array( 'message' => __( 'Location saved.', 'devsroom-google-review-showcase' ) ) );
    }

    /**
     * AJAX handler — sync reviews now.
     */
    public function ajax_sync_now() {
        check_ajax_referer( 'devsroom_greviews_sync_now', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'devsroom-google-review-showcase' ) ) );
        }

        $api    = new Devsroom_GReviews_Google_API();
        $result = $api->sync_reviews();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => sprintf(
                /* translators: %1$d: total reviews, %2$d: added, %3$d: updated */
                __( 'Sync complete. Total: %1$d, Added: %2$d, Updated: %3$d', 'devsroom-google-review-showcase' ),
                $result['total'],
                $result['added'],
                $result['updated']
            ),
        ) );
    }

    /**
     * AJAX handler — disconnect Google account.
     */
    public function ajax_oauth_disconnect() {
        check_ajax_referer( 'devsroom_greviews_oauth_disconnect', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'devsroom-google-review-showcase' ) ) );
        }

        $oauth = new Devsroom_GReviews_OAuth();
        $oauth->disconnect();

        // Clear scheduled cron.
        wp_clear_scheduled_hook( 'devsroom_greviews_cron_sync' );

        wp_send_json_success( array(
            'message' => __( 'Google account disconnected.', 'devsroom-google-review-showcase' ),
        ) );
    }

    /**
     * AJAX handler — fetch business locations.
     */
    public function ajax_fetch_locations() {
        check_ajax_referer( 'devsroom_greviews_fetch_locations', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'devsroom-google-review-showcase' ) ) );
        }

        $api      = new Devsroom_GReviews_Google_API();
        $accounts = $api->get_accounts();

        if ( is_wp_error( $accounts ) ) {
            wp_send_json_error( array( 'message' => $accounts->get_error_message() ) );
        }

        $account_name = $accounts[0]['name'];
        $locations    = $api->get_locations( $account_name );

        if ( is_wp_error( $locations ) ) {
            wp_send_json_error( array( 'message' => $locations->get_error_message() ) );
        }

        $formatted = array();
        foreach ( $locations as $loc ) {
            $formatted[] = array(
                'name'  => $loc['name'],
                'title' => $loc['title'],
            );
        }

        wp_send_json_success( array(
            'locations' => $formatted,
            'message'   => sprintf(
                /* translators: %d: number of locations */
                __( 'Found %d locations.', 'devsroom-google-review-showcase' ),
                count( $formatted )
            ),
        ) );
    }

    // =========================================================================
    // Cron Management
    // =========================================================================

    /**
     * Handle sync interval change — reschedule cron.
     */
    public function handle_sync_interval_change( $old_value, $new_value ) {
        $this->update_cron_schedule( $new_value );
    }

    /**
     * Handle connection mode change — schedule or clear cron.
     */
    public function handle_connection_mode_change( $old_value, $new_value ) {
        if ( 'oauth' === $new_value ) {
            $interval = get_option( 'devsroom_greviews_sync_interval', 'daily' );
            $this->update_cron_schedule( $interval );
        } else {
            wp_clear_scheduled_hook( 'devsroom_greviews_cron_sync' );
        }
    }

    /**
     * Schedule or clear the cron based on interval value.
     */
    private function update_cron_schedule( $interval ) {
        wp_clear_scheduled_hook( 'devsroom_greviews_cron_sync' );

        if ( 'manual' === $interval ) {
            return;
        }

        $wp_interval = 'devsroom_' . $interval;
        wp_schedule_event( time(), $wp_interval, 'devsroom_greviews_cron_sync' );
    }

    // =========================================================================
    // Settings Page Rendering
    // =========================================================================

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Devsroom Google Review ShowCase', 'devsroom-google-review-showcase' ); ?></h1>

            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=devsroom-google-reviews&tab=settings' ) ); ?>" class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'devsroom-google-review-showcase' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=devsroom-google-reviews&tab=user-guide' ) ); ?>" class="nav-tab <?php echo 'user-guide' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'User Guide', 'devsroom-google-review-showcase' ); ?></a>
            </nav>

            <?php if ( 'user-guide' === $active_tab ) : ?>
                <?php $this->render_user_guide(); ?>
            <?php else : ?>
                <?php $this->render_settings_tab(); ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render the Settings tab content.
     */
    private function render_settings_tab() {
        $connection_mode = get_option( 'devsroom_greviews_connection_mode', 'api_key' );
        $oauth           = new Devsroom_GReviews_OAuth();
        $is_connected    = $oauth->is_connected();
        $oauth_email     = get_option( 'devsroom_greviews_oauth_user_email', '' );
        $oauth_name      = get_option( 'devsroom_greviews_oauth_user_name', '' );
        $business_name   = get_option( 'devsroom_greviews_oauth_business_name', '' );
        $last_sync       = get_option( 'devsroom_greviews_oauth_last_sync', '' );
        $location_name   = get_option( 'devsroom_greviews_oauth_location_name', '' );
        $sync_interval   = get_option( 'devsroom_greviews_sync_interval', 'daily' );
        ?>
        <br />

        <form method="post" action="options.php">
            <?php settings_fields( 'devsroom_greviews_settings' ); ?>

            <!-- Connection Mode Toggle -->
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Connection Method', 'devsroom-google-review-showcase' ); ?></th>
                    <td>
                        <fieldset>
                            <label style="margin-right:20px;">
                                <input type="radio" name="devsroom_greviews_connection_mode" value="api_key" <?php checked( $connection_mode, 'api_key' ); ?> />
                                <?php esc_html_e( 'API Key + Place ID', 'devsroom-google-review-showcase' ); ?>
                            </label>
                            <label>
                                <input type="radio" name="devsroom_greviews_connection_mode" value="oauth" <?php checked( $connection_mode, 'oauth' ); ?> />
                                <?php esc_html_e( 'Connect Google Account (OAuth)', 'devsroom-google-review-showcase' ); ?>
                            </label>
                        </fieldset>
                        <p class="description"><?php esc_html_e( 'Choose how to connect to Google. OAuth fetches all reviews from your Business Profile.', 'devsroom-google-review-showcase' ); ?></p>
                    </td>
                </tr>
            </table>

            <!-- API Key Mode Section -->
            <div id="devsroom-greviews-mode-api-key" style="<?php echo 'api_key' === $connection_mode ? '' : 'display:none;'; ?>">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="devsroom_greviews_api_key"><?php esc_html_e( 'Google API Key', 'devsroom-google-review-showcase' ); ?></label>
                        </th>
                        <td>
                            <input type="password"
                                   id="devsroom_greviews_api_key"
                                   name="devsroom_greviews_api_key"
                                   value="<?php echo esc_attr( get_option( 'devsroom_greviews_api_key', '' ) ); ?>"
                                   class="regular-text" />
                            <p class="description">
                                <?php esc_html_e( 'Enter your Google Cloud API key with Places API enabled.', 'devsroom-google-review-showcase' ); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="devsroom_greviews_place_id"><?php esc_html_e( 'Google Place ID', 'devsroom-google-review-showcase' ); ?></label>
                        </th>
                        <td>
                            <input type="text"
                                   id="devsroom_greviews_place_id"
                                   name="devsroom_greviews_place_id"
                                   value="<?php echo esc_attr( get_option( 'devsroom_greviews_place_id', '' ) ); ?>"
                                   class="regular-text" />
                            <p class="description">
                                <?php esc_html_e( 'Your Google My Business Place ID.', 'devsroom-google-review-showcase' ); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="devsroom_greviews_cache_duration"><?php esc_html_e( 'Cache Duration (hours)', 'devsroom-google-review-showcase' ); ?></label>
                        </th>
                        <td>
                            <input type="number"
                                   id="devsroom_greviews_cache_duration"
                                   name="devsroom_greviews_cache_duration"
                                   value="<?php echo esc_attr( get_option( 'devsroom_greviews_cache_duration', 24 ) ); ?>"
                                   min="1"
                                   max="168"
                                   class="small-text" />
                            <p class="description">
                                <?php esc_html_e( 'How long to cache reviews before fetching fresh data (1-168 hours). Default: 24 hours.', 'devsroom-google-review-showcase' ); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- OAuth Mode Section -->
            <div id="devsroom-greviews-mode-oauth" style="<?php echo 'oauth' === $connection_mode ? '' : 'display:none;'; ?>">

                <?php if ( ! $is_connected ) : ?>
                    <!-- Not connected — show Connect button and advanced settings -->
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:30px;text-align:center;max-width:500px;margin:20px 0;">
                        <div style="width:64px;height:64px;background:#f3f4f6;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z" fill="#9ca3af"/>
                            </svg>
                        </div>
                        <h3 style="margin:0 0 6px;font-size:16px;"><?php esc_html_e( 'Connect Your Google Account', 'devsroom-google-review-showcase' ); ?></h3>
                        <p style="color:#6b7280;font-size:13px;margin:0 0 20px;"><?php esc_html_e( 'Connect your Google Business Profile to fetch and display all your reviews.', 'devsroom-google-review-showcase' ); ?></p>
                        <button type="button" id="devsroom-greviews-oauth-connect" class="button button-primary button-hero"
                                data-nonce="<?php echo esc_attr( wp_create_nonce( 'devsroom_greviews_oauth_start' ) ); ?>"
                                style="background:#4285f4;border-color:#4285f4;border-radius:6px;padding:8px 28px;font-size:14px;height:auto;">
                            <span style="display:inline-flex;align-items:center;gap:8px;">
                                <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
                                <?php esc_html_e( 'Connect Google Account', 'devsroom-google-review-showcase' ); ?>
                            </span>
                        </button>
                    </div>

                    <!-- Advanced Settings (collapsed) -->
                    <details style="margin-top:16px;">
                        <summary style="cursor:pointer;color:#6b7280;font-size:13px;outline:none;"><?php esc_html_e( 'Advanced OAuth Settings', 'devsroom-google-review-showcase' ); ?></summary>
                        <table class="form-table" style="margin-top:12px;">
                            <tr>
                                <th scope="row">
                                    <label for="devsroom_greviews_oauth_client_id"><?php esc_html_e( 'Client ID', 'devsroom-google-review-showcase' ); ?></label>
                                </th>
                                <td>
                                    <input type="text"
                                           id="devsroom_greviews_oauth_client_id"
                                           name="devsroom_greviews_oauth_client_id"
                                           value="<?php echo esc_attr( get_option( 'devsroom_greviews_oauth_client_id', '' ) ); ?>"
                                           class="regular-text" />
                                    <p class="description">
                                        <?php esc_html_e( 'OAuth 2.0 Client ID from Google Cloud Console.', 'devsroom-google-review-showcase' ); ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="devsroom_greviews_oauth_client_secret"><?php esc_html_e( 'Client Secret', 'devsroom-google-review-showcase' ); ?></label>
                                </th>
                                <td>
                                    <input type="password"
                                           id="devsroom_greviews_oauth_client_secret"
                                           name="devsroom_greviews_oauth_client_secret"
                                           value="<?php echo esc_attr( get_option( 'devsroom_greviews_oauth_client_secret', '' ) ); ?>"
                                           class="regular-text" />
                                    <p class="description">
                                        <?php esc_html_e( 'OAuth 2.0 Client Secret from Google Cloud Console.', 'devsroom-google-review-showcase' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </details>

                <?php else : ?>
                    <!-- Connected — show account card and controls -->
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:20px;margin:20px 0;max-width:600px;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                            <div style="width:40px;height:40px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div>
                                <strong style="display:block;"><?php echo esc_html( $oauth_name ); ?></strong>
                                <?php if ( $oauth_email ) : ?>
                                    <span style="color:#6b7280;font-size:13px;"><?php echo esc_html( $oauth_email ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ( $business_name ) : ?>
                            <p style="margin:0 0 4px;font-size:13px;color:#374151;">
                                <?php esc_html_e( 'Business:', 'devsroom-google-review-showcase' ); ?>
                                <strong><?php echo esc_html( $business_name ); ?></strong>
                            </p>
                        <?php endif; ?>
                        <?php if ( $last_sync ) : ?>
                            <p style="margin:0;font-size:13px;color:#6b7280;">
                                <?php esc_html_e( 'Last Sync:', 'devsroom-google-review-showcase' ); ?>
                                <?php echo esc_html( $last_sync ); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Connected settings -->
                    <table class="form-table">
                        <!-- Location selector -->
                        <tr>
                            <th scope="row">
                                <label for="devsroom_greviews_oauth_location_name"><?php esc_html_e( 'Business Location', 'devsroom-google-review-showcase' ); ?></label>
                            </th>
                            <td>
                                <select id="devsroom_greviews_oauth_location_name" name="devsroom_greviews_oauth_location_name" class="regular-text">
                                    <option value="<?php echo esc_attr( $location_name ); ?>"><?php echo esc_html( $business_name ? $business_name : $location_name ); ?></option>
                                </select>
                                <button type="button" id="devsroom-greviews-fetch-locations" class="button button-secondary" style="margin-left:8px;">
                                    <?php esc_html_e( 'Refresh', 'devsroom-google-review-showcase' ); ?>
                                </button>
                                <span id="devsroom-greviews-locations-result" style="margin-left:8px;"></span>
                                <p class="description"><?php esc_html_e( 'Select the business location to fetch reviews from.', 'devsroom-google-review-showcase' ); ?></p>
                            </td>
                        </tr>

                        <!-- Sync interval -->
                        <tr>
                            <th scope="row">
                                <label for="devsroom_greviews_sync_interval"><?php esc_html_e( 'Sync Interval', 'devsroom-google-review-showcase' ); ?></label>
                            </th>
                            <td>
                                <select id="devsroom_greviews_sync_interval" name="devsroom_greviews_sync_interval">
                                    <option value="6hours" <?php selected( $sync_interval, '6hours' ); ?>><?php esc_html_e( 'Every 6 Hours', 'devsroom-google-review-showcase' ); ?></option>
                                    <option value="daily" <?php selected( $sync_interval, 'daily' ); ?>><?php esc_html_e( 'Daily', 'devsroom-google-review-showcase' ); ?></option>
                                    <option value="weekly" <?php selected( $sync_interval, 'weekly' ); ?>><?php esc_html_e( 'Weekly', 'devsroom-google-review-showcase' ); ?></option>
                                    <option value="manual" <?php selected( $sync_interval, 'manual' ); ?>><?php esc_html_e( 'Manual Only', 'devsroom-google-review-showcase' ); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>

                    <!-- Action buttons for connected state -->
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Sync Reviews', 'devsroom-google-review-showcase' ); ?></th>
                            <td>
                                <button type="button" id="devsroom-greviews-sync-now" class="button button-secondary">
                                    <?php esc_html_e( 'Sync Now', 'devsroom-google-review-showcase' ); ?>
                                </button>
                                <span id="devsroom-greviews-sync-result" style="margin-left:10px;"></span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e( 'Disconnect', 'devsroom-google-review-showcase' ); ?></th>
                            <td>
                                <button type="button" id="devsroom-greviews-oauth-disconnect" class="button button-link-delete">
                                    <?php esc_html_e( 'Disconnect', 'devsroom-google-review-showcase' ); ?>
                                </button>
                                <span id="devsroom-greviews-disconnect-result" style="margin-left:10px;"></span>
                                <p class="description"><?php esc_html_e( 'Disconnects your Google account. Existing reviews will be kept.', 'devsroom-google-review-showcase' ); ?></p>
                            </td>
                        </tr>
                    </table>
                <?php endif; ?>
            </div>

            <?php submit_button(); ?>
        </form>

        <hr />

        <!-- Actions Section -->
        <h2><?php esc_html_e( 'Actions', 'devsroom-google-review-showcase' ); ?></h2>

        <!-- API Key Actions -->
        <div id="devsroom-greviews-actions-api-key" style="<?php echo 'api_key' === $connection_mode ? '' : 'display:none;'; ?>">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Test Connection', 'devsroom-google-review-showcase' ); ?></th>
                    <td>
                        <button type="button" id="devsroom-greviews-test-fetch" class="button button-secondary">
                            <?php esc_html_e( 'Test Fetch', 'devsroom-google-review-showcase' ); ?>
                        </button>
                        <span id="devsroom-greviews-test-result" style="margin-left:10px;"></span>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Clear Cache', 'devsroom-google-review-showcase' ); ?></th>
                    <td>
                        <button type="button" id="devsroom-greviews-clear-cache" class="button button-secondary">
                            <?php esc_html_e( 'Clear Cache', 'devsroom-google-review-showcase' ); ?>
                        </button>
                        <span id="devsroom-greviews-cache-result" style="margin-left:10px;"></span>
                    </td>
                </tr>
            </table>
        </div>

        <hr />

        <!-- Logs (API Key mode only) -->
        <h2><?php esc_html_e( 'Logs', 'devsroom-google-review-showcase' ); ?></h2>

        <?php
        $last_fetch = get_option( 'devsroom_greviews_last_fetch', false );
        if ( $last_fetch && is_array( $last_fetch ) ) :
        ?>
            <table class="widefat striped">
                <tr>
                    <th><?php esc_html_e( 'Last Fetch Time', 'devsroom-google-review-showcase' ); ?></th>
                    <td><?php echo esc_html( $last_fetch['time'] ); ?></td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Status', 'devsroom-google-review-showcase' ); ?></th>
                    <td>
                        <?php if ( 'success' === $last_fetch['status'] ) : ?>
                            <span style="color:green;"><?php esc_html_e( 'Success', 'devsroom-google-review-showcase' ); ?></span>
                        <?php else : ?>
                            <span style="color:red;"><?php esc_html_e( 'Error', 'devsroom-google-review-showcase' ); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Message', 'devsroom-google-review-showcase' ); ?></th>
                    <td><?php echo esc_html( $last_fetch['message'] ); ?></td>
                </tr>
            </table>
        <?php else : ?>
            <p><?php esc_html_e( 'No fetch logs yet. Click "Test Fetch" to test your connection.', 'devsroom-google-review-showcase' ); ?></p>
        <?php endif; ?>

        <hr />

        <h3><?php esc_html_e( 'Quick Reference — Shortcode', 'devsroom-google-review-showcase' ); ?></h3>
        <p>
            <code>[devsroom_greviews layout="grid" limit="6" rating="4" order="content_top"]</code>
        </p>
        <p class="description">
            <?php esc_html_e( 'Attributes: layout (slider/grid/masonry/list), order (content_top/content_bottom/name_top/name_bottom), limit (1-50), rating (1-5), show_photo (yes/no), show_name (yes/no), show_rating (yes/no), show_date (yes/no), show_more (yes/no)', 'devsroom-google-review-showcase' ); ?>
        </p>
        <p class="description">
            <?php esc_html_e( 'For full documentation, see the User Guide tab.', 'devsroom-google-review-showcase' ); ?>
        </p>
        <?php
    }

    /**
     * Render the User Guide tab content.
     */
    private function render_user_guide() {
        ?>
        <style>
            .devsroom-greviews-guide { max-width: 800px; margin-top: 20px; }
            .devsroom-greviews-guide h2 { margin-top: 30px; padding-bottom: 8px; border-bottom: 1px solid #ddd; }
            .devsroom-greviews-guide h3 { margin-top: 20px; }
            .devsroom-greviews-guide table { border-collapse: collapse; width: 100%; margin: 10px 0 20px; }
            .devsroom-greviews-guide table th,
            .devsroom-greviews-guide table td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
            .devsroom-greviews-guide table th { background: #f9f9f9; }
            .devsroom-greviews-guide table tr:nth-child(even) td { background: #fafafa; }
            .devsroom-greviews-guide code { background: #f1f1f1; padding: 2px 6px; border-radius: 3px; font-size: 13px; }
            .devsroom-greviews-guide pre { background: #f8f9fa; padding: 12px 16px; border-radius: 4px; border: 1px solid #e2e8f0; overflow-x: auto; }
            .devsroom-greviews-guide ul,
            .devsroom-greviews-guide ol { margin-left: 20px; }
            .devsroom-greviews-guide li { margin-bottom: 4px; }
            .devsroom-greviews-guide hr { margin: 25px 0; border: none; border-top: 1px solid #ddd; }
            .devsroom-greviews-guide .guide-nav { background: #f8f9fa; padding: 15px 20px; border-radius: 6px; margin-bottom: 20px; }
            .devsroom-greviews-guide .guide-nav a { text-decoration: none; }
            .devsroom-greviews-guide .guide-nav li { margin-bottom: 6px; }
        </style>

        <div class="devsroom-greviews-guide">

            <h2>Getting Your Google API Credentials</h2>

            <h3>Step 1: Create a Google API Key</h3>
            <ol>
                <li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                <li>Create a new project (or select an existing one)</li>
                <li>Navigate to <strong>APIs &amp; Services &rarr; Library</strong></li>
                <li>Search for <strong>Places API</strong> and click <strong>Enable</strong></li>
                <li>Go to <strong>APIs &amp; Services &rarr; Credentials</strong></li>
                <li>Click <strong>Create Credentials &rarr; API Key</strong></li>
                <li>Copy the generated API key</li>
                <li>(Recommended) Restrict the key to <strong>Places API</strong> only</li>
            </ol>

            <h3>Step 2: Find Your Place ID</h3>
            <ol>
                <li>Go to the <a href="https://developers.google.com/maps/documentation/places/web-service/place-id" target="_blank">Google Place ID Finder</a></li>
                <li>Enter your business name in the search box</li>
                <li>Select your business from the results</li>
                <li>Copy the <strong>Place ID</strong> (format: <code>ChIJ...</code>)</li>
            </ol>

            <hr />

            <h2>OAuth Connection (Connect Google Account)</h2>

            <h3>Step 1: Create OAuth Credentials</h3>
            <ol>
                <li>Go to <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a></li>
                <li>Select your project (or create a new one)</li>
                <li>Navigate to <strong>APIs &amp; Services &rarr; Library</strong></li>
                <li>Enable <strong>Google Business Profile API</strong></li>
                <li>Go to <strong>APIs &amp; Services &rarr; Credentials</strong></li>
                <li>Click <strong>Create Credentials &rarr; OAuth client ID</strong></li>
                <li>Application type: <strong>Web application</strong></li>
                <li>Authorized redirect URIs: add <code><?php echo esc_html( admin_url( 'admin-ajax.php?action=devsroom_greviews_oauth_callback' ) ); ?></code></li>
                <li>Copy the <strong>Client ID</strong> and <strong>Client Secret</strong></li>
            </ol>

            <h3>Step 2: Connect Your Account</h3>
            <ol>
                <li>Paste the Client ID and Client Secret into the settings</li>
                <li>Click <strong>Save Changes</strong></li>
                <li>Click <strong>Connect Google Account</strong></li>
                <li>Grant access to your Google Business Profile</li>
                <li>Reviews will be synced automatically based on your chosen interval</li>
            </ol>

            <hr />

            <h2>Using the Shortcode</h2>

            <p>Add the following shortcode to any page, post, or widget area:</p>
            <pre><code>[devsroom_greviews]</code></pre>
            <p>This displays reviews using default settings (grid layout, 5 reviews, minimum 1-star rating).</p>

            <h3>All Available Attributes</h3>
            <table>
                <tr><th>Attribute</th><th>Values</th><th>Default</th><th>Description</th></tr>
                <tr><td><code>layout</code></td><td><code>slider</code>, <code>grid</code>, <code>masonry</code>, <code>list</code></td><td><code>grid</code></td><td>Display layout type</td></tr>
                <tr><td><code>order</code></td><td><code>content_top</code>, <code>content_bottom</code>, <code>name_top</code>, <code>name_bottom</code></td><td><code>content_top</code></td><td>Content order within cards</td></tr>
                <tr><td><code>limit</code></td><td><code>1</code> &ndash; <code>50</code></td><td><code>5</code></td><td>Number of reviews to display</td></tr>
                <tr><td><code>rating</code></td><td><code>1</code> &ndash; <code>5</code></td><td><code>1</code></td><td>Minimum star rating filter</td></tr>
                <tr><td><code>show_photo</code></td><td><code>yes</code>, <code>no</code></td><td><code>yes</code></td><td>Show/hide reviewer photo</td></tr>
                <tr><td><code>show_name</code></td><td><code>yes</code>, <code>no</code></td><td><code>yes</code></td><td>Show/hide reviewer name</td></tr>
                <tr><td><code>show_rating</code></td><td><code>yes</code>, <code>no</code></td><td><code>yes</code></td><td>Show/hide star rating</td></tr>
                <tr><td><code>show_date</code></td><td><code>yes</code>, <code>no</code></td><td><code>yes</code></td><td>Show/hide review date</td></tr>
                <tr><td><code>show_more</code></td><td><code>yes</code>, <code>no</code></td><td><code>no</code></td><td>Enable "Read more" for long reviews</td></tr>
            </table>

            <h3>Shortcode Examples</h3>

            <p><strong>Slider with 8 reviews, 4+ stars:</strong></p>
            <pre><code>[devsroom_greviews layout="slider" limit="8" rating="4"]</code></pre>

            <p><strong>Grid layout, name on top, 6 reviews, no photos:</strong></p>
            <pre><code>[devsroom_greviews layout="grid" order="name_top" limit="6" show_photo="no"]</code></pre>

            <p><strong>Masonry layout with read more enabled:</strong></p>
            <pre><code>[devsroom_greviews layout="masonry" show_more="yes" limit="12"]</code></pre>

            <p><strong>List layout, 3+ stars, hide dates:</strong></p>
            <pre><code>[devsroom_greviews layout="list" rating="3" show_date="no"]</code></pre>

            <p><strong>Minimal &mdash; text only:</strong></p>
            <pre><code>[devsroom_greviews show_photo="no" show_name="no" show_rating="no" show_date="no"]</code></pre>

            <hr />

            <h2>Using the Elementor Widget</h2>

            <h3>Adding the Widget</h3>
            <ol>
                <li>Edit a page with <strong>Elementor</strong></li>
                <li>Search for <strong>"Google Review ShowCase"</strong> in the widget panel</li>
                <li>Drag it to your page</li>
            </ol>

            <h3>Content Tab</h3>
            <ul>
                <li><strong>Layout</strong> &mdash; Slider, Grid, Masonry, or List. Columns for Grid/Masonry (1&ndash;4)</li>
                <li><strong>Query Settings</strong> &mdash; Limit (1&ndash;50), Minimum Rating (1&ndash;5), Sort Order (Newest/Oldest/Highest)</li>
                <li><strong>Element Visibility</strong> &mdash; Toggle Photo, Name, Rating, Date, Read More</li>
                <li><strong>Content Order</strong> &mdash; Content Top/Bottom, Name Top/Bottom</li>
            </ul>

            <h3>Style Tab</h3>
            <ul>
                <li><strong>Card</strong> &mdash; Background, padding, border radius, border, shadow, gap</li>
                <li><strong>Reviewer</strong> &mdash; Photo size (24&ndash;120px), shape (round/square), name typography &amp; color</li>
                <li><strong>Review Text</strong> &mdash; Typography, color, line limit</li>
                <li><strong>Rating Stars</strong> &mdash; Size, color, spacing</li>
                <li><strong>Date</strong> &mdash; Typography, color, format (relative, YYYY-MM-DD, MM/DD/YYYY, etc.)</li>
                <li><strong>Slider</strong> &mdash; Arrow color, dot color, autoplay, autoplay speed</li>
            </ul>

            <hr />

            <h2>Content Ordering</h2>

            <p>Control the position of elements within each review card. Each card has 5 possible elements: <strong>Photo</strong>, <strong>Name</strong>, <strong>Rating</strong>, <strong>Text</strong>, <strong>Date</strong>.</p>

            <table>
                <tr><th>Preset</th><th>Element Order (top to bottom)</th></tr>
                <tr><td>Content Top</td><td>Text &rarr; Name &rarr; Photo &rarr; Rating &rarr; Date</td></tr>
                <tr><td>Content Bottom</td><td>Photo &rarr; Name &rarr; Rating &rarr; Date &rarr; Text</td></tr>
                <tr><td>Name + Image Top</td><td>Photo &rarr; Name &rarr; Rating &rarr; Text &rarr; Date</td></tr>
                <tr><td>Name + Image Bottom</td><td>Text &rarr; Rating &rarr; Photo &rarr; Name &rarr; Date</td></tr>
            </table>

            <hr />

            <h2>Troubleshooting</h2>

            <h3>No reviews showing</h3>
            <ul>
                <li>Verify your <strong>API Key</strong> and <strong>Place ID</strong> in Settings</li>
                <li>Or verify your OAuth connection is active</li>
                <li>Click <strong>Test Fetch</strong> or <strong>Sync Now</strong> to check the connection</li>
                <li>Ensure the <strong>Places API</strong> or <strong>Google Business Profile API</strong> is enabled in Google Cloud Console</li>
                <li>Check that your business has reviews on Google</li>
            </ul>

            <h3>"API Key or Place ID is not configured" error</h3>
            <ul>
                <li>Go to <strong>Settings &rarr; Devsroom Google Reviews</strong></li>
                <li>Make sure both fields are filled and saved</li>
            </ul>

            <h3>Reviews not updating</h3>
            <ul>
                <li>Reviews are cached for the duration set in settings (default: 24 hours)</li>
                <li>Click <strong>Clear Cache</strong> to force a fresh fetch</li>
                <li>Reduce the cache duration if you need more frequent updates</li>
                <li>For OAuth mode, click <strong>Sync Now</strong> or check the sync interval setting</li>
            </ul>

            <h3>Styling looks broken</h3>
            <ul>
                <li>Ensure your theme is not overriding plugin styles</li>
                <li>Check for JavaScript errors in browser console (F12)</li>
                <li>Assets are only loaded on pages with the shortcode or widget</li>
            </ul>

            <h3>Elementor widget shows a placeholder</h3>
            <ul>
                <li>Make sure your API Key and Place ID are configured in plugin settings</li>
                <li>The placeholder is only visible in the Elementor editor</li>
            </ul>

            <hr />

            <h2>FAQ</h2>

            <p><strong>Does this plugin require Elementor?</strong><br />
            No. The plugin works via shortcode on any WordPress site. Elementor is optional.</p>

            <p><strong>How often are reviews refreshed?</strong><br />
            API Key mode: reviews are cached for the duration set in your settings (default: 24 hours). You can change this or clear the cache manually.<br />
            OAuth mode: reviews are synced automatically based on your chosen interval (every 6 hours, daily, weekly, or manual only).</p>

            <p><strong>Does this plugin slow down my site?</strong><br />
            No. Reviews are cached in the database, so no API calls are made on page load. CSS and JavaScript are only loaded on pages with the shortcode or widget.</p>

            <p><strong>What happens if the Google API is down?</strong><br />
            The plugin continues to display cached reviews. If the cache expires and the API is unavailable, reviews will not display until the API is accessible again.</p>

            <p><strong>Can I style the reviews to match my theme?</strong><br />
            Yes. Use Elementor Style tab controls, or add custom CSS targeting BEM classes like <code>.devsroom-greviews-card</code>, <code>.devsroom-greviews-card__name</code>.</p>

            <p><strong>Is the Google API free?</strong><br />
            Google offers a monthly free tier for the Places API. With caching enabled, you'll only make 1 API call per cache refresh cycle.</p>

            <p><strong>What is the difference between API Key and OAuth modes?</strong><br />
            API Key mode uses the Google Places API and is limited to reviews available through that API. OAuth mode connects directly to your Google Business Profile and fetches ALL reviews with no limit. OAuth mode also supports automatic background syncing.</p>

        </div>
        <?php
    }
}
