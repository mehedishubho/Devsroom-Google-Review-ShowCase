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

        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'settings';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Devsroom Google Review ShowCase', 'devsroom-google-review-showcase' ); ?></h1>

            <nav class="nav-tab-wrapper">
                <a href="<?php echo esc_url( admin_url( 'options-general.php?page=devsroom-google-reviews&tab=settings' ) ); ?>" class="nav-tab <?php echo 'settings' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Settings', 'devsroom-google-review-showcase' ); ?></a>
                <a href="<?php echo esc_url( admin_url( 'options-general.php?page=devsroom-google-reviews&tab=user-guide' ) ); ?>" class="nav-tab <?php echo 'user-guide' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'User Guide', 'devsroom-google-review-showcase' ); ?></a>
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
        ?>
        <br />

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
                <li>Click <strong>Test Fetch</strong> to check the connection</li>
                <li>Ensure the <strong>Places API</strong> is enabled in Google Cloud Console</li>
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
            Reviews are cached for the duration set in your settings (default: 24 hours). You can change this or clear the cache manually.</p>

            <p><strong>Does this plugin slow down my site?</strong><br />
            No. Reviews are cached in the database, so no API calls are made on page load. CSS and JavaScript are only loaded on pages with the shortcode or widget.</p>

            <p><strong>What happens if the Google API is down?</strong><br />
            The plugin continues to display cached reviews. If the cache expires and the API is unavailable, reviews will not display until the API is accessible again.</p>

            <p><strong>Can I style the reviews to match my theme?</strong><br />
            Yes. Use Elementor Style tab controls, or add custom CSS targeting BEM classes like <code>.devsroom-greviews-card</code>, <code>.devsroom-greviews-card__name</code>.</p>

            <p><strong>Is the Google API free?</strong><br />
            Google offers a monthly free tier for the Places API. With caching enabled, you'll only make 1 API call per cache refresh cycle.</p>

        </div>
        <?php
    }
}
