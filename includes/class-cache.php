<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Devsroom_GReviews_Cache {

    /**
     * Option key for cached data.
     */
    const CACHE_KEY = 'devsroom_greviews_cache';

    /**
     * Get cached reviews.
     *
     * @return array|false Cached data or false if expired/missing.
     */
    public function get() {
        $cached = get_option( self::CACHE_KEY, false );

        if ( ! $cached || ! is_array( $cached ) ) {
            return false;
        }

        if ( ! $this->is_valid() ) {
            return false;
        }

        return isset( $cached['data'] ) ? $cached['data'] : false;
    }

    /**
     * Store data in cache with timestamp.
     *
     * @param array $data Review data to cache.
     */
    public function set( $data ) {
        update_option( self::CACHE_KEY, array(
            'data'      => $data,
            'timestamp' => time(),
        ), false );
    }

    /**
     * Check if cache is still valid.
     *
     * @return bool
     */
    public function is_valid() {
        $cached = get_option( self::CACHE_KEY, false );

        if ( ! $cached || ! is_array( $cached ) || ! isset( $cached['timestamp'] ) ) {
            return false;
        }

        $duration = $this->get_cache_duration();
        $elapsed  = time() - $cached['timestamp'];

        return $elapsed < $duration;
    }

    /**
     * Clear the cache.
     */
    public function clear() {
        delete_option( self::CACHE_KEY );
    }

    /**
     * Get cache duration in seconds from settings.
     *
     * @return int Duration in seconds. Default 86400 (24 hours).
     */
    public function get_cache_duration() {
        $hours = get_option( 'devsroom_greviews_cache_duration', 24 );
        return absint( $hours ) * HOUR_IN_SECONDS;
    }

    /**
     * Get the timestamp of the last cache write.
     *
     * @return int|false Unix timestamp or false if no cache.
     */
    public function get_timestamp() {
        $cached = get_option( self::CACHE_KEY, false );

        if ( ! $cached || ! is_array( $cached ) || ! isset( $cached['timestamp'] ) ) {
            return false;
        }

        return $cached['timestamp'];
    }
}
