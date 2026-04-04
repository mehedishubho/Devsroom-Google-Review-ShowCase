<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Devsroom_GReviews_Shortcode {

    /**
     * Valid layout values.
     */
    const LAYOUTS = array( 'slider', 'grid', 'masonry', 'list' );

    /**
     * Valid order values.
     */
    const ORDERS = array( 'content_top', 'content_bottom', 'name_top', 'name_bottom' );

    /**
     * Constructor.
     */
    public function __construct() {
        add_shortcode( 'devsroom_greviews', array( $this, 'render_shortcode' ) );
    }

    /**
     * Render the shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'layout'      => 'grid',
            'order'       => 'content_top',
            'limit'       => 5,
            'rating'      => 1,
            'show_photo'  => 'yes',
            'show_name'   => 'yes',
            'show_rating' => 'yes',
            'show_date'   => 'yes',
            'show_more'   => 'no',
        ), $atts, 'devsroom_greviews' );

        // Sanitize attributes.
        $settings = array(
            'layout'      => in_array( $atts['layout'], self::LAYOUTS, true ) ? $atts['layout'] : 'grid',
            'order'       => in_array( $atts['order'], self::ORDERS, true ) ? $atts['order'] : 'content_top',
            'limit'       => min( max( absint( $atts['limit'] ), 1 ), 50 ),
            'rating'      => min( max( absint( $atts['rating'] ), 1 ), 5 ),
            'show_photo'  => in_array( $atts['show_photo'], array( 'yes', 'no' ), true ) ? $atts['show_photo'] : 'yes',
            'show_name'   => in_array( $atts['show_name'], array( 'yes', 'no' ), true ) ? $atts['show_name'] : 'yes',
            'show_rating' => in_array( $atts['show_rating'], array( 'yes', 'no' ), true ) ? $atts['show_rating'] : 'yes',
            'show_date'   => in_array( $atts['show_date'], array( 'yes', 'no' ), true ) ? $atts['show_date'] : 'yes',
            'show_more'   => in_array( $atts['show_more'], array( 'yes', 'no' ), true ) ? $atts['show_more'] : 'no',
        );

        $render = new Devsroom_GReviews_Render();
        return $render->render( $settings );
    }
}
