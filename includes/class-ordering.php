<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Devsroom_GReviews_Ordering {

    /**
     * Order presets.
     */
    const PRESETS = array(
        'content_top'    => array( 'text', 'name', 'photo', 'rating', 'date' ),
        'content_bottom' => array( 'photo', 'name', 'rating', 'date', 'text' ),
        'name_top'       => array( 'photo', 'name', 'rating', 'text', 'date' ),
        'name_bottom'    => array( 'text', 'rating', 'photo', 'name', 'date' ),
    );

    /**
     * Get the ordered array of content blocks.
     *
     * @param string $order_key Preset key.
     * @return array Ordered content block keys.
     */
    public function get_order( $order_key ) {
        if ( isset( self::PRESETS[ $order_key ] ) ) {
            return self::PRESETS[ $order_key ];
        }

        return $this->get_default_order();
    }

    /**
     * Get the default content block order.
     *
     * @return array
     */
    public function get_default_order() {
        return array( 'photo', 'name', 'rating', 'text', 'date' );
    }

    /**
     * Apply visibility settings to the order array.
     *
     * @param array $order      Ordered content blocks.
     * @param array $visibility Associative array of show_* flags.
     * @return array Filtered order.
     */
    public function apply_visibility( $order, $visibility ) {
        $block_to_visibility = array(
            'photo'  => 'show_photo',
            'name'   => 'show_name',
            'rating' => 'show_rating',
            'text'   => 'show_text',
            'date'   => 'show_date',
        );

        return array_filter( $order, function( $block ) use ( $visibility, $block_to_visibility ) {
            $key = isset( $block_to_visibility[ $block ] ) ? $block_to_visibility[ $block ] : null;
            if ( null === $key ) {
                return true;
            }
            return ! empty( $visibility[ $key ] );
        } );
    }
}
