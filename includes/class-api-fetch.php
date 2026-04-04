<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Devsroom_GReviews_API_Fetch {

    /**
     * @var Devsroom_GReviews_Cache
     */
    private $cache;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->cache = new Devsroom_GReviews_Cache();
    }

    /**
     * Fetch reviews — returns cached data if valid, otherwise fetches from API.
     *
     * @return array|WP_Error Normalized reviews array or WP_Error.
     */
    public function fetch_reviews() {
        // Try cache first.
        $cached = $this->cache->get();
        if ( false !== $cached ) {
            return $cached;
        }

        // Fetch from API.
        return $this->fetch_from_api();
    }

    /**
     * Fetch reviews from the Google Places API.
     *
     * @return array|WP_Error
     */
    public function fetch_from_api() {
        $api_key  = get_option( 'devsroom_greviews_api_key', '' );
        $place_id = get_option( 'devsroom_greviews_place_id', '' );

        if ( empty( $api_key ) || empty( $place_id ) ) {
            $this->log_fetch( 'error', __( 'API Key or Place ID is not configured.', 'devsroom-google-review-showcase' ) );
            return new WP_Error( 'missing_config', __( 'API Key or Place ID is not configured.', 'devsroom-google-review-showcase' ) );
        }

        $url = add_query_arg( array(
            'place_id' => sanitize_text_field( $place_id ),
            'fields'   => 'reviews,rating,user_ratings_total',
            'key'      => sanitize_text_field( $api_key ),
        ), 'https://maps.googleapis.com/maps/api/place/details/json' );

        $response = wp_remote_get( $url, array(
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) {
            $this->log_fetch( 'error', $response->get_error_message() );
            return $response;
        }

        $parsed = $this->parse_response( $response );

        if ( is_wp_error( $parsed ) ) {
            return $parsed;
        }

        // Cache the result.
        $this->cache->set( $parsed );
        $this->log_fetch( 'success', sprintf(
            /* translators: %d: number of reviews fetched */
            __( 'Successfully fetched %d reviews.', 'devsroom-google-review-showcase' ),
            count( $parsed )
        ) );

        return $parsed;
    }

    /**
     * Parse the API response.
     *
     * @param array $response wp_remote_get response.
     * @return array|WP_Error
     */
    private function parse_response( $response ) {
        $code = wp_remote_retrieve_response_code( $response );

        if ( 200 !== $code ) {
            $this->log_fetch( 'error', sprintf(
                /* translators: %d: HTTP status code */
                __( 'API returned HTTP status %d.', 'devsroom-google-review-showcase' ),
                $code
            ) );
            return new WP_Error( 'api_error', sprintf(
                __( 'API returned HTTP status %d.', 'devsroom-google-review-showcase' ),
                $code
            ) );
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $data ) ) {
            $this->log_fetch( 'error', __( 'Invalid JSON response from API.', 'devsroom-google-review-showcase' ) );
            return new WP_Error( 'json_error', __( 'Invalid JSON response from API.', 'devsroom-google-review-showcase' ) );
        }

        // Check API status.
        $status = isset( $data['status'] ) ? $data['status'] : '';

        if ( 'OK' !== $status && 'ZERO_RESULTS' !== $status ) {
            $error_msg = isset( $data['error_message'] ) ? $data['error_message'] : $status;
            $this->log_fetch( 'error', $error_msg );
            return new WP_Error( 'api_status', $error_msg );
        }

        // Extract and normalize reviews.
        $raw_reviews = isset( $data['result']['reviews'] ) ? $data['result']['reviews'] : array();

        if ( empty( $raw_reviews ) ) {
            $this->log_fetch( 'success', __( 'No reviews found.', 'devsroom-google-review-showcase' ) );
            return array();
        }

        $reviews = array();
        foreach ( $raw_reviews as $raw ) {
            $reviews[] = $this->normalize_review( $raw );
        }

        return $reviews;
    }

    /**
     * Normalize a single review from the Google API format.
     *
     * @param array $raw Raw review from Google API.
     * @return array Normalized review.
     */
    private function normalize_review( $raw ) {
        return array(
            'author_name'                => isset( $raw['author_name'] ) ? sanitize_text_field( $raw['author_name'] ) : '',
            'author_photo'               => isset( $raw['profile_photo_url'] ) ? esc_url_raw( $raw['profile_photo_url'] ) : '',
            'rating'                     => isset( $raw['rating'] ) ? absint( $raw['rating'] ) : 0,
            'text'                       => isset( $raw['text'] ) ? wp_kses_post( $raw['text'] ) : '',
            'time'                       => isset( $raw['time'] ) ? absint( $raw['time'] ) : 0,
            'relative_time_description'  => isset( $raw['relative_time_description'] ) ? sanitize_text_field( $raw['relative_time_description'] ) : '',
        );
    }

    /**
     * Log a fetch attempt.
     *
     * @param string $status  'success' or 'error'.
     * @param string $message Log message.
     */
    private function log_fetch( $status, $message ) {
        update_option( 'devsroom_greviews_last_fetch', array(
            'time'    => current_time( 'mysql' ),
            'status'  => sanitize_text_field( $status ),
            'message' => sanitize_text_field( $message ),
        ), false );
    }

    /**
     * Test fetch — used by the admin "Test Fetch" button via AJAX.
     *
     * @return array Result with success flag, message, and review count.
     */
    public function test_fetch() {
        // Clear cache to force a fresh fetch.
        $this->cache->clear();

        $result = $this->fetch_from_api();

        if ( is_wp_error( $result ) ) {
            return array(
                'success' => false,
                'message' => $result->get_error_message(),
                'count'   => 0,
            );
        }

        return array(
            'success' => true,
            'message' => sprintf(
                /* translators: %d: number of reviews */
                __( 'Successfully connected! Found %d reviews.', 'devsroom-google-review-showcase' ),
                count( $result )
            ),
            'count'   => count( $result ),
        );
    }
}
