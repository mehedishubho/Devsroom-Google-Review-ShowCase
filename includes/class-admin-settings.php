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
        add_action( 'wp_ajax_devsroom_greviews_test_fetch', array( $this, 'ajax_test_fetch' ) );
        add_action( 'wp_ajax_devsroom_greviews_clear_cache', array( $this, 'ajax_clear_cache' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    /**
     * Add settings page under Settings menu.
     */
    public function add_settings_page() {
        add_options_page(
            __( 'Devsroom Google Reviews', 'devsroom-google-review-showcase' ),
            __( 'Devsroom Google Reviews', 'devsroom-google-review-showcase' ),
            'manage_options',
            'devsroom-google-reviews',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register plugin settings.
     */
    public function register_settings() {
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
    }

    /**
     * Enqueue admin JS for AJAX buttons.
     */
    public function enqueue_admin_assets( $hook ) {
        if ( 'settings_page_devsroom-google-reviews' !== $hook ) {
            return;
        }

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

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Devsroom Google Review ShowCase Settings', 'devsroom-google-review-showcase' ); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields( 'devsroom_greviews_settings' ); ?>

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

                <?php submit_button(); ?>
            </form>

            <hr />

            <h2><?php esc_html_e( 'Actions', 'devsroom-google-review-showcase' ); ?></h2>

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

            <hr />

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

            <h3><?php esc_html_e( 'Shortcode Usage', 'devsroom-google-review-showcase' ); ?></h3>
            <p>
                <code>[devsroom_greviews layout="grid" limit="6" rating="4" order="content_top"]</code>
            </p>
            <p class="description">
                <?php esc_html_e( 'Attributes: layout (slider/grid/masonry/list), order (content_top/content_bottom/name_top/name_bottom), limit (1-50), rating (1-5), show_photo (yes/no), show_name (yes/no), show_rating (yes/no), show_date (yes/no), show_more (yes/no)', 'devsroom-google-review-showcase' ); ?>
            </p>
        </div>
        <?php
    }
}
