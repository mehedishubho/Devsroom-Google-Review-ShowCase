<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Devsroom_GReviews_Render {

    /**
     * Render reviews based on settings.
     *
     * @param array $settings Layout and display settings.
     * @return string HTML output.
     */
    public function render( $settings ) {
        $reviews = $this->get_reviews( $settings );

        if ( empty( $reviews ) ) {
            return '';
        }

        // Determine content order.
        $ordering  = new Devsroom_GReviews_Ordering();
        $order_key = ! empty( $settings['order'] ) ? $settings['order'] : 'content_top';
        $order     = $ordering->get_order( $order_key );

        // Apply visibility.
        $visibility = array(
            'show_photo'  => ! isset( $settings['show_photo'] ) || 'yes' === $settings['show_photo'] || true === $settings['show_photo'],
            'show_name'   => ! isset( $settings['show_name'] ) || 'yes' === $settings['show_name'] || true === $settings['show_name'],
            'show_rating' => ! isset( $settings['show_rating'] ) || 'yes' === $settings['show_rating'] || true === $settings['show_rating'],
            'show_text'   => true,
            'show_date'   => ! isset( $settings['show_date'] ) || 'yes' === $settings['show_date'] || true === $settings['show_date'],
        );
        $order = $ordering->apply_visibility( $order, $visibility );

        // Normalize show_more.
        if ( isset( $settings['show_more'] ) ) {
            $settings['show_more'] = ( 'yes' === $settings['show_more'] || true === $settings['show_more'] );
        }

        // Enqueue assets.
        $layout = ! empty( $settings['layout'] ) ? $settings['layout'] : 'grid';
        $this->enqueue_assets( $layout );

        // Load layout template.
        ob_start();
        $template = $this->get_layout_template( $layout );
        include $template;
        return ob_get_clean();
    }

    /**
     * Get reviews and apply filters.
     *
     * Uses OAuth stored reviews when in OAuth mode, otherwise falls back
     * to the API Key + Place ID fetch method.
     *
     * @param array $settings Filter settings.
     * @return array Filtered reviews.
     */
    public function get_reviews( $settings ) {
        $mode = get_option( 'devsroom_greviews_connection_mode', 'api_key' );

        if ( 'oauth' === $mode ) {
            $google_api = new Devsroom_GReviews_Google_API();
            $reviews    = $google_api->get_stored_reviews();
        } else {
            $api     = new Devsroom_GReviews_API_Fetch();
            $reviews = $api->fetch_reviews();
        }

        if ( is_wp_error( $reviews ) || empty( $reviews ) ) {
            return array();
        }

        // Filter by minimum rating (0 = show all).
        $min_rating = isset( $settings['rating'] ) ? intval( $settings['rating'] ) : 0;
        if ( $min_rating > 0 ) {
            $reviews = array_filter( $reviews, function( $review ) use ( $min_rating ) {
                return intval( $review['rating'] ) >= $min_rating;
            } );
        }

        // Sort.
        $sort_order = ! empty( $settings['sort_order'] ) ? $settings['sort_order'] : 'newest';
        usort( $reviews, function( $a, $b ) use ( $sort_order ) {
            switch ( $sort_order ) {
                case 'oldest':
                    return $a['time'] - $b['time'];
                case 'highest_rated':
                    return $b['rating'] - $a['rating'];
                case 'newest':
                default:
                    return $b['time'] - $a['time'];
            }
        } );

        // Limit.
        $limit = ! empty( $settings['limit'] ) ? intval( $settings['limit'] ) : 5;
        $reviews = array_slice( $reviews, 0, $limit );

        return $reviews;
    }

    /**
     * Get the layout template file path.
     *
     * @param string $layout Layout type.
     * @return string File path.
     */
    public function get_layout_template( $layout ) {
        $templates = array(
            'slider'  => DEVSROOM_GREVIEWS_DIR . 'templates/slider.php',
            'grid'    => DEVSROOM_GREVIEWS_DIR . 'templates/grid.php',
            'masonry' => DEVSROOM_GREVIEWS_DIR . 'templates/masonry.php',
            'list'    => DEVSROOM_GREVIEWS_DIR . 'templates/list.php',
        );

        return isset( $templates[ $layout ] ) ? $templates[ $layout ] : $templates['grid'];
    }

    /**
     * Enqueue assets based on layout type.
     *
     * @param string $layout Layout type.
     */
    public function enqueue_assets( $layout ) {
        // Main CSS — always needed.
        wp_enqueue_style( 'devsroom-greviews' );

        // Frontend JS — always needed.
        wp_enqueue_script( 'devsroom-greviews-frontend' );

        // Layout-specific JS.
        switch ( $layout ) {
            case 'slider':
                wp_enqueue_style( 'swiper-css' );
                wp_enqueue_script( 'devsroom-greviews-slider' );
                break;
            case 'masonry':
                wp_enqueue_script( 'devsroom-greviews-masonry' );
                break;
        }
    }
}
