<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Devsroom_GReviews_Elementor_Init {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
        add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );
    }

    /**
     * Register the Devsroom widget category.
     *
     * @param \Elementor\Elements_Manager $elements_manager
     */
    public function register_category( $elements_manager ) {
        $elements_manager->add_category(
            'devsroom-widgets',
            array(
                'title' => __( 'Devsroom', 'devsroom-google-review-showcase' ),
                'icon'  => 'fa fa-plug',
            )
        );
    }

    /**
     * Register the Google Reviews widget.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     */
    public function register_widget( $widgets_manager ) {
        require_once DEVSROOM_GREVIEWS_DIR . 'includes/elementor/widget-google-reviews.php';

        $widgets_manager->register( new Devsroom_GReviews_Widget() );
    }
}
