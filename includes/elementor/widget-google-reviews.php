<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Devsroom_GReviews_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'devsroom-google-reviews';
    }

    public function get_title() {
        return __( 'Google Review ShowCase', 'devsroom-google-review-showcase' );
    }

    public function get_icon() {
        return 'eicon-google-maps';
    }

    public function get_categories() {
        return array( 'devsroom-widgets' );
    }

    public function get_keywords() {
        return array( 'google', 'reviews', 'testimonial', 'rating', 'devsroom' );
    }

    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    /**
     * Safely get a slider control size value.
     */
    private function get_slider_size( $settings, $key, $default = 0 ) {
        if ( ! isset( $settings[ $key ] ) ) {
            return $default;
        }
        if ( is_array( $settings[ $key ] ) && isset( $settings[ $key ]['size'] ) ) {
            return intval( $settings[ $key ]['size'] );
        }
        return $default;
    }

    /* ============================================
       CONTENT TAB
       ============================================ */

    private function register_content_controls() {

        // --- Layout ---
        $this->start_controls_section( 'section_layout', array(
            'label' => __( 'Layout', 'devsroom-google-review-showcase' ),
        ) );

        $this->add_control( 'layout_type', array(
            'label'   => __( 'Layout Type', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'grid',
            'options' => array(
                'slider'  => __( 'Slider', 'devsroom-google-review-showcase' ),
                'grid'    => __( 'Grid', 'devsroom-google-review-showcase' ),
                'masonry' => __( 'Masonry', 'devsroom-google-review-showcase' ),
                'list'    => __( 'List', 'devsroom-google-review-showcase' ),
            ),
        ) );

        $this->add_responsive_control( 'columns', array(
            'label'     => __( 'Columns', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::SELECT,
            'default'   => '3',
            'options'   => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
            'condition' => array( 'layout_type' => array( 'grid', 'masonry', 'list' ) ),
        ) );

        $this->end_controls_section();

        // --- Slider Settings ---
        $this->start_controls_section( 'section_slider_settings', array(
            'label'     => __( 'Slider Settings', 'devsroom-google-review-showcase' ),
            'condition' => array( 'layout_type' => 'slider' ),
        ) );

        $this->add_responsive_control( 'slides_per_view', array(
            'label'   => __( 'Slides on Display', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => '3',
            'options' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
        ) );

        $this->add_responsive_control( 'slides_per_scroll', array(
            'label'   => __( 'Slides on Scroll', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => '1',
            'options' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
        ) );

        $this->add_control( 'equal_height', array(
            'label'        => __( 'Equal Height', 'devsroom-google-review-showcase' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'no',
            'label_on'     => __( 'On', 'devsroom-google-review-showcase' ),
            'label_off'    => __( 'Off', 'devsroom-google-review-showcase' ),
            'return_value' => 'yes',
        ) );

        $this->add_control( 'slider_autoplay', array(
            'label'        => __( 'Autoplay', 'devsroom-google-review-showcase' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'no',
            'label_on'     => __( 'On', 'devsroom-google-review-showcase' ),
            'label_off'    => __( 'Off', 'devsroom-google-review-showcase' ),
            'return_value' => 'yes',
        ) );

        $this->add_control( 'autoplay_speed', array(
            'label'     => __( 'Scroll Speed (ms)', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::NUMBER,
            'default'   => 3000,
            'min'       => 100,
            'max'       => 10000,
            'condition' => array( 'slider_autoplay' => 'yes' ),
        ) );

        $this->add_control( 'pause_on_hover', array(
            'label'        => __( 'Pause on Hover', 'devsroom-google-review-showcase' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => __( 'On', 'devsroom-google-review-showcase' ),
            'label_off'    => __( 'Off', 'devsroom-google-review-showcase' ),
            'return_value' => 'yes',
            'condition'    => array( 'slider_autoplay' => 'yes' ),
        ) );

        $this->add_control( 'pause_on_interaction', array(
            'label'        => __( 'Pause on Interaction', 'devsroom-google-review-showcase' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => __( 'On', 'devsroom-google-review-showcase' ),
            'label_off'    => __( 'Off', 'devsroom-google-review-showcase' ),
            'return_value' => 'yes',
            'condition'    => array( 'slider_autoplay' => 'yes' ),
        ) );

        $this->add_control( 'infinite_scroll', array(
            'label'        => __( 'Infinite Scroll', 'devsroom-google-review-showcase' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => __( 'On', 'devsroom-google-review-showcase' ),
            'label_off'    => __( 'Off', 'devsroom-google-review-showcase' ),
            'return_value' => 'yes',
        ) );

        $this->add_control( 'transition_duration', array(
            'label'   => __( 'Transition Duration (ms)', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 300,
            'min'     => 100,
            'max'     => 3000,
        ) );

        $this->add_control( 'slide_gap', array(
            'label'      => __( 'Slide Gap (px)', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'max' => 60 ) ),
            'default'    => array( 'size' => 20 ),
        ) );

        $this->add_control( 'slider_direction', array(
            'label'   => __( 'Direction', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'ltr',
            'options' => array(
                'ltr' => __( 'Left to Right', 'devsroom-google-review-showcase' ),
                'rtl' => __( 'Right to Left', 'devsroom-google-review-showcase' ),
            ),
        ) );

        $this->add_control( 'offset_sides', array(
            'label'   => __( 'Offset Sides', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'none',
            'options' => array(
                'none'  => __( 'None', 'devsroom-google-review-showcase' ),
                'both'  => __( 'Both', 'devsroom-google-review-showcase' ),
                'left'  => __( 'Left', 'devsroom-google-review-showcase' ),
                'right' => __( 'Right', 'devsroom-google-review-showcase' ),
            ),
        ) );

        $this->add_responsive_control( 'offset_width', array(
            'label'      => __( 'Offset Width (px)', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
            'default'    => array( 'size' => 0 ),
            'condition'  => array( 'offset_sides!' => 'none' ),
        ) );

        $this->end_controls_section();

        // --- Query Settings ---
        $this->start_controls_section( 'section_query', array(
            'label' => __( 'Query Settings', 'devsroom-google-review-showcase' ),
        ) );

        $this->add_control( 'limit', array(
            'label'   => __( 'Limit', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 5,
            'min'     => 1,
            'max'     => 50,
        ) );

        $this->add_control( 'min_rating', array(
            'label'   => __( 'Minimum Rating', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 0,
            'options' => array(
                0 => __( 'None (Show All)', 'devsroom-google-review-showcase' ),
                1 => '1+',
                2 => '2+',
                3 => '3+',
                4 => '4+',
                5 => '5',
            ),
        ) );

        $this->add_control( 'sort_order', array(
            'label'   => __( 'Sort Order', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'newest',
            'options' => array(
                'newest'        => __( 'Newest First', 'devsroom-google-review-showcase' ),
                'oldest'        => __( 'Oldest First', 'devsroom-google-review-showcase' ),
                'highest_rated' => __( 'Highest Rated', 'devsroom-google-review-showcase' ),
            ),
        ) );

        $this->end_controls_section();

        // --- Element Visibility ---
        $this->start_controls_section( 'section_visibility', array(
            'label' => __( 'Element Visibility', 'devsroom-google-review-showcase' ),
        ) );

        $this->add_control( 'show_photo', array(
            'label'   => __( 'Reviewer Photo', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ) );

        $this->add_control( 'show_name', array(
            'label'   => __( 'Reviewer Name', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ) );

        $this->add_control( 'show_rating', array(
            'label'   => __( 'Rating Stars', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ) );

        $this->add_control( 'show_date', array(
            'label'   => __( 'Review Date', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ) );

        $this->add_control( 'show_more', array(
            'label'   => __( 'Read More Button', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'no',
        ) );

        $this->end_controls_section();

        // --- Content Order ---
        $this->start_controls_section( 'section_order', array(
            'label' => __( 'Content Order', 'devsroom-google-review-showcase' ),
        ) );

        $this->add_control( 'content_order', array(
            'label'   => __( 'Content Order', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'content_top',
            'options' => array(
                'content_top'    => __( 'Content Top (text first)', 'devsroom-google-review-showcase' ),
                'content_bottom' => __( 'Content Bottom (text last)', 'devsroom-google-review-showcase' ),
                'name_top'       => __( 'Name + Image Top', 'devsroom-google-review-showcase' ),
                'name_bottom'    => __( 'Name + Image Bottom', 'devsroom-google-review-showcase' ),
            ),
        ) );

        $this->end_controls_section();
    }

    /* ============================================
       STYLE TAB
       ============================================ */

    private function register_style_controls() {

        // --- Card ---
        $this->start_controls_section( 'section_style_card', array(
            'label' => __( 'Card', 'devsroom-google-review-showcase' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ) );

        $this->add_control( 'card_bg_color', array(
            'label'     => __( 'Background Color', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => array(
                '{{WRAPPER}} .devsroom-greviews-card' => 'background-color: {{VALUE}};',
            ),
        ) );

        $this->add_responsive_control( 'card_padding', array(
            'label'      => __( 'Padding', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => array( 'px', 'em', '%' ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ),
        ) );

        $this->add_control( 'card_border_radius', array(
            'label'      => __( 'Border Radius', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px', '%' ),
            'range'      => array( 'px' => array( 'max' => 50 ) ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-card' => 'border-radius: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), array(
            'name'     => 'card_border',
            'selector' => '{{WRAPPER}} .devsroom-greviews-card',
        ) );

        $this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), array(
            'name'     => 'card_box_shadow',
            'selector' => '{{WRAPPER}} .devsroom-greviews-card',
        ) );

        $this->add_control( 'card_gap', array(
            'label'      => __( 'Card Gap', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'max' => 60 ) ),
            'default'    => array( 'size' => 20 ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-grid, {{WRAPPER}} .devsroom-greviews-masonry' => 'gap: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .devsroom-greviews-list' => 'gap: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->add_control( 'card_alignment', array(
            'label'       => __( 'Content Alignment', 'devsroom-google-review-showcase' ),
            'type'        => \Elementor\Controls_Manager::CHOOSE,
            'label_block' => false,
            'options'     => array(
                'left'   => array( 'title' => __( 'Left', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-left' ),
                'center' => array( 'title' => __( 'Center', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-center' ),
                'right'  => array( 'title' => __( 'Right', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-right' ),
            ),
            'default'     => 'left',
            'selectors'   => array(
                '{{WRAPPER}} .devsroom-greviews-card' => 'text-align: {{VALUE}};',
            ),
        ) );

        $this->end_controls_section();

        // --- Reviewer ---
        $this->start_controls_section( 'section_style_reviewer', array(
            'label' => __( 'Reviewer', 'devsroom-google-review-showcase' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ) );

        $this->add_control( 'photo_size', array(
            'label'      => __( 'Photo Size', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'min' => 24, 'max' => 120 ) ),
            'default'    => array( 'size' => 48 ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-card__photo img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .devsroom-greviews-card__photo--placeholder' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->add_control( 'photo_shape', array(
            'label'   => __( 'Photo Shape', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'round',
            'options' => array(
                'round'  => __( 'Round', 'devsroom-google-review-showcase' ),
                'square' => __( 'Square', 'devsroom-google-review-showcase' ),
            ),
        ) );

        $this->add_control( 'reviewer_alignment', array(
            'label'       => __( 'Alignment', 'devsroom-google-review-showcase' ),
            'type'        => \Elementor\Controls_Manager::CHOOSE,
            'label_block' => false,
            'options'     => array(
                'left'   => array( 'title' => __( 'Left', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-left' ),
                'center' => array( 'title' => __( 'Center', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-center' ),
                'right'  => array( 'title' => __( 'Right', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-right' ),
            ),
            'default'     => '',
            'selectors'   => array(
                '{{WRAPPER}} .devsroom-greviews-card__photo' => 'text-align: {{VALUE}};',
                '{{WRAPPER}} .devsroom-greviews-card__name' => 'text-align: {{VALUE}};',
            ),
        ) );

        $this->add_responsive_control( 'reviewer_spacing', array(
            'label'      => __( 'Spacing', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'max' => 30 ) ),
            'default'    => array( 'size' => 0 ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-card__name' => 'margin-top: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
            'name'     => 'name_typography',
            'label'    => __( 'Name Typography', 'devsroom-google-review-showcase' ),
            'selector' => '{{WRAPPER}} .devsroom-greviews-card__name',
        ) );

        $this->add_control( 'name_color', array(
            'label'     => __( 'Name Color', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => array(
                '{{WRAPPER}} .devsroom-greviews-card__name' => 'color: {{VALUE}};',
            ),
        ) );

        $this->end_controls_section();

        // --- Review Text ---
        $this->start_controls_section( 'section_style_text', array(
            'label' => __( 'Review Text', 'devsroom-google-review-showcase' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ) );

        $this->add_control( 'text_alignment', array(
            'label'       => __( 'Alignment', 'devsroom-google-review-showcase' ),
            'type'        => \Elementor\Controls_Manager::CHOOSE,
            'label_block' => false,
            'options'     => array(
                'left'    => array( 'title' => __( 'Left', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-left' ),
                'center'  => array( 'title' => __( 'Center', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-center' ),
                'right'   => array( 'title' => __( 'Right', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-right' ),
                'justify' => array( 'title' => __( 'Justify', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-justify' ),
            ),
            'default'     => '',
            'selectors'   => array(
                '{{WRAPPER}} .devsroom-greviews-card__text' => 'text-align: {{VALUE}};',
            ),
        ) );

        $this->add_responsive_control( 'text_spacing', array(
            'label'      => __( 'Spacing', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'max' => 30 ) ),
            'default'    => array( 'size' => 0 ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-card__text' => 'margin-top: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
            'name'     => 'text_typography',
            'label'    => __( 'Text Typography', 'devsroom-google-review-showcase' ),
            'selector' => '{{WRAPPER}} .devsroom-greviews-card__text',
        ) );

        $this->add_control( 'text_color', array(
            'label'     => __( 'Text Color', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => array(
                '{{WRAPPER}} .devsroom-greviews-card__text' => 'color: {{VALUE}};',
            ),
        ) );

        $this->add_control( 'text_line_limit', array(
            'label'       => __( 'Line Limit', 'devsroom-google-review-showcase' ),
            'type'        => \Elementor\Controls_Manager::NUMBER,
            'default'     => 0,
            'description' => __( 'Set to 0 for no limit.', 'devsroom-google-review-showcase' ),
        ) );

        $this->end_controls_section();

        // --- Rating Stars ---
        $this->start_controls_section( 'section_style_stars', array(
            'label' => __( 'Rating Stars', 'devsroom-google-review-showcase' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ) );

        $this->add_control( 'rating_alignment', array(
            'label'                => __( 'Alignment', 'devsroom-google-review-showcase' ),
            'type'                 => \Elementor\Controls_Manager::CHOOSE,
            'label_block'          => false,
            'options'              => array(
                'left'   => array( 'title' => __( 'Left', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-left' ),
                'center' => array( 'title' => __( 'Center', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-center' ),
                'right'  => array( 'title' => __( 'Right', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-right' ),
            ),
            'default'              => '',
            'selectors'            => array(
                '{{WRAPPER}} .devsroom-greviews-card__rating' => 'justify-content: {{VALUE}};',
            ),
            'selectors_dictionary' => array(
                'left'   => 'flex-start',
                'center' => 'center',
                'right'  => 'flex-end',
            ),
        ) );

        $this->add_responsive_control( 'rating_spacing', array(
            'label'      => __( 'Spacing', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'max' => 30 ) ),
            'default'    => array( 'size' => 0 ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-card__rating' => 'margin-top: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->add_control( 'star_size', array(
            'label'      => __( 'Star Size', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'min' => 10, 'max' => 40 ) ),
            'default'    => array( 'size' => 18 ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-star' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->add_control( 'star_color', array(
            'label'     => __( 'Star Color', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#f59e0b',
            'selectors' => array(
                '{{WRAPPER}} .devsroom-greviews-star--filled' => 'color: {{VALUE}};',
            ),
        ) );

        $this->add_control( 'star_spacing', array(
            'label'      => __( 'Star Spacing', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'max' => 10 ) ),
            'default'    => array( 'size' => 2 ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-rating' => 'gap: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->end_controls_section();

        // --- Date ---
        $this->start_controls_section( 'section_style_date', array(
            'label' => __( 'Date', 'devsroom-google-review-showcase' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ) );

        $this->add_control( 'date_alignment', array(
            'label'       => __( 'Alignment', 'devsroom-google-review-showcase' ),
            'type'        => \Elementor\Controls_Manager::CHOOSE,
            'label_block' => false,
            'options'     => array(
                'left'   => array( 'title' => __( 'Left', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-left' ),
                'center' => array( 'title' => __( 'Center', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-center' ),
                'right'  => array( 'title' => __( 'Right', 'devsroom-google-review-showcase' ), 'icon' => 'eicon-text-align-right' ),
            ),
            'default'     => '',
            'selectors'   => array(
                '{{WRAPPER}} .devsroom-greviews-card__date' => 'text-align: {{VALUE}};',
            ),
        ) );

        $this->add_responsive_control( 'date_spacing', array(
            'label'      => __( 'Spacing', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'max' => 30 ) ),
            'default'    => array( 'size' => 0 ),
            'selectors'  => array(
                '{{WRAPPER}} .devsroom-greviews-card__date' => 'margin-top: {{SIZE}}{{UNIT}};',
            ),
        ) );

        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array(
            'name'     => 'date_typography',
            'label'    => __( 'Date Typography', 'devsroom-google-review-showcase' ),
            'selector' => '{{WRAPPER}} .devsroom-greviews-card__date',
        ) );

        $this->add_control( 'date_color', array(
            'label'     => __( 'Date Color', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'selectors' => array(
                '{{WRAPPER}} .devsroom-greviews-card__date' => 'color: {{VALUE}};',
            ),
        ) );

        $this->add_control( 'date_format', array(
            'label'   => __( 'Date Format', 'devsroom-google-review-showcase' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'relative',
            'options' => array(
                'relative'    => __( 'Relative (e.g., "3 weeks ago")', 'devsroom-google-review-showcase' ),
                'F j, Y'     => __( 'Month Day, Year', 'devsroom-google-review-showcase' ),
                'Y-m-d'      => __( 'YYYY-MM-DD', 'devsroom-google-review-showcase' ),
                'm/d/Y'      => __( 'MM/DD/YYYY', 'devsroom-google-review-showcase' ),
                'd/m/Y'      => __( 'DD/MM/YYYY', 'devsroom-google-review-showcase' ),
            ),
        ) );

        $this->end_controls_section();

        // --- Slider Only ---
        $this->start_controls_section( 'section_style_slider', array(
            'label'     => __( 'Slider', 'devsroom-google-review-showcase' ),
            'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => array( 'layout_type' => 'slider' ),
        ) );

        $this->add_control( 'slider_navigation', array(
            'label'        => __( 'Show Navigation Arrows', 'devsroom-google-review-showcase' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => __( 'Yes', 'devsroom-google-review-showcase' ),
            'label_off'    => __( 'No', 'devsroom-google-review-showcase' ),
            'return_value' => 'yes',
        ) );

        $this->add_control( 'slider_pagination', array(
            'label'        => __( 'Show Pagination Dots', 'devsroom-google-review-showcase' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'default'      => 'yes',
            'label_on'     => __( 'Yes', 'devsroom-google-review-showcase' ),
            'label_off'    => __( 'No', 'devsroom-google-review-showcase' ),
            'return_value' => 'yes',
        ) );

        $this->add_control( 'arrow_icon_prev', array(
            'label'                  => __( 'Previous Arrow Icon', 'devsroom-google-review-showcase' ),
            'type'                   => \Elementor\Controls_Manager::ICONS,
            'default'                => array(
                'value'   => 'fas fa-chevron-left',
                'library' => 'fa-solid',
            ),
            'recommended'            => array(
                'fa-solid' => array( 'chevron-left', 'arrow-left', 'angle-left', 'caret-left', 'long-arrow-alt-left' ),
            ),
            'condition'              => array( 'slider_navigation' => 'yes' ),
            'fa4CompatibilityValue'  => 'fa fa-chevron-left',
        ) );

        $this->add_control( 'arrow_icon_next', array(
            'label'                  => __( 'Next Arrow Icon', 'devsroom-google-review-showcase' ),
            'type'                   => \Elementor\Controls_Manager::ICONS,
            'default'                => array(
                'value'   => 'fas fa-chevron-right',
                'library' => 'fa-solid',
            ),
            'recommended'            => array(
                'fa-solid' => array( 'chevron-right', 'arrow-right', 'angle-right', 'caret-right', 'long-arrow-alt-right' ),
            ),
            'condition'              => array( 'slider_navigation' => 'yes' ),
            'fa4CompatibilityValue'  => 'fa fa-chevron-right',
        ) );

        $this->add_control( 'arrow_color', array(
            'label'     => __( 'Arrow Color', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#6b7280',
            'selectors' => array(
                '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'color: {{VALUE}};',
                '{{WRAPPER}} .swiper-button-prev svg, {{WRAPPER}} .swiper-button-next svg' => 'fill: {{VALUE}};',
            ),
            'condition' => array( 'slider_navigation' => 'yes' ),
        ) );

        $this->add_control( 'arrow_bg_color', array(
            'label'     => __( 'Arrow Background', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '',
            'selectors' => array(
                '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'background-color: {{VALUE}};',
            ),
            'condition' => array( 'slider_navigation' => 'yes' ),
        ) );

        $this->add_control( 'arrow_size', array(
            'label'      => __( 'Arrow Size', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'min' => 20, 'max' => 60 ) ),
            'default'    => array( 'size' => 44 ),
            'selectors'  => array(
                '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ),
            'condition'  => array( 'slider_navigation' => 'yes' ),
        ) );

        $this->add_control( 'dot_color_normal', array(
            'label'     => __( 'Dot Color - Normal', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#6b7280',
            'selectors' => array(
                '{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}};',
            ),
            'condition' => array( 'slider_pagination' => 'yes' ),
        ) );

        $this->add_control( 'dot_color_active', array(
            'label'     => __( 'Dot Color - Active', 'devsroom-google-review-showcase' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#6b7280',
            'selectors' => array(
                '{{WRAPPER}} .swiper-pagination-bullet-active' => 'background: {{VALUE}};',
            ),
            'condition' => array( 'slider_pagination' => 'yes' ),
        ) );

        $this->add_control( 'dot_size', array(
            'label'      => __( 'Dot Size', 'devsroom-google-review-showcase' ),
            'type'       => \Elementor\Controls_Manager::SLIDER,
            'size_units' => array( 'px' ),
            'range'      => array( 'px' => array( 'min' => 4, 'max' => 16 ) ),
            'default'    => array( 'size' => 8 ),
            'selectors'  => array(
                '{{WRAPPER}} .swiper-pagination-bullet' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
            ),
            'condition'  => array( 'slider_pagination' => 'yes' ),
        ) );

        $this->end_controls_section();
    }

    /* ============================================
       RENDER
       ============================================ */

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Check if API is configured.
        if ( empty( get_option( 'devsroom_greviews_api_key' ) ) || empty( get_option( 'devsroom_greviews_place_id' ) ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div style="padding:20px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;text-align:center;">';
                esc_html_e( 'Please configure your Google API Key and Place ID in Google Reviews settings.', 'devsroom-google-review-showcase' );
                echo '</div>';
            }
            return;
        }

        // Map Elementor settings to render settings — safely access slider values.
        $render_settings = array(
            'layout'          => $settings['layout_type'],
            'order'           => $settings['content_order'],
            'limit'           => intval( $settings['limit'] ),
            'rating'          => intval( $settings['min_rating'] ),
            'sort_order'      => $settings['sort_order'],
            'show_photo'      => 'yes' === $settings['show_photo'],
            'show_name'       => 'yes' === $settings['show_name'],
            'show_rating'     => 'yes' === $settings['show_rating'],
            'show_date'       => 'yes' === $settings['show_date'],
            'show_more'       => 'yes' === $settings['show_more'],
            'columns'         => intval( $settings['columns'] ),
            'columns_tablet'  => ! empty( $settings['columns_tablet'] ) ? intval( $settings['columns_tablet'] ) : 2,
            'columns_mobile'  => ! empty( $settings['columns_mobile'] ) ? intval( $settings['columns_mobile'] ) : 1,
            'photo_size'      => $this->get_slider_size( $settings, 'photo_size', 48 ),
            'photo_shape'     => $settings['photo_shape'],
            'star_size'       => $this->get_slider_size( $settings, 'star_size', 18 ),
            'star_color'      => isset( $settings['star_color'] ) ? $settings['star_color'] : '#f59e0b',
            'date_format'     => $settings['date_format'],
            'text_line_limit' => intval( $settings['text_line_limit'] ),
        );

        // Slider-specific settings.
        if ( 'slider' === $settings['layout_type'] ) {
            $render_settings['autoplay']                = 'yes' === $settings['slider_autoplay'];
            $render_settings['autoplay_speed']          = intval( $settings['autoplay_speed'] );
            $render_settings['slides_per_view']         = intval( $settings['slides_per_view'] );
            $render_settings['slides_per_view_tablet']  = ! empty( $settings['slides_per_view_tablet'] ) ? intval( $settings['slides_per_view_tablet'] ) : 2;
            $render_settings['slides_per_view_mobile']  = ! empty( $settings['slides_per_view_mobile'] ) ? intval( $settings['slides_per_view_mobile'] ) : 1;
            $render_settings['slides_per_scroll']       = intval( $settings['slides_per_scroll'] );
            $render_settings['slides_per_scroll_tablet'] = ! empty( $settings['slides_per_scroll_tablet'] ) ? intval( $settings['slides_per_scroll_tablet'] ) : 1;
            $render_settings['slides_per_scroll_mobile'] = ! empty( $settings['slides_per_scroll_mobile'] ) ? intval( $settings['slides_per_scroll_mobile'] ) : 1;
            $render_settings['equal_height']            = 'yes' === $settings['equal_height'];
            $render_settings['pause_on_hover']          = 'yes' === $settings['pause_on_hover'];
            $render_settings['pause_on_interaction']    = 'yes' === $settings['pause_on_interaction'];
            $render_settings['infinite_scroll']         = 'yes' === $settings['infinite_scroll'];
            $render_settings['transition_duration']     = intval( $settings['transition_duration'] );
            $render_settings['direction']               = $settings['slider_direction'];
            $render_settings['offset_sides']            = $settings['offset_sides'];
            $render_settings['offset_width']            = $this->get_slider_size( $settings, 'offset_width', 0 );
            $render_settings['offset_width_tablet']     = $this->get_slider_size( $settings, 'offset_width_tablet', 0 );
            $render_settings['offset_width_mobile']     = $this->get_slider_size( $settings, 'offset_width_mobile', 0 );
            $render_settings['show_navigation']         = ! isset( $settings['slider_navigation'] ) || 'yes' === $settings['slider_navigation'];
            $render_settings['show_pagination']         = ! isset( $settings['slider_pagination'] ) || 'yes' === $settings['slider_pagination'];
            $render_settings['arrow_icon_prev']         = $settings['arrow_icon_prev'];
            $render_settings['arrow_icon_next']         = $settings['arrow_icon_next'];
            $slide_gap = $this->get_slider_size( $settings, 'slide_gap', 20 );
            if ( $slide_gap ) {
                $render_settings['slide_gap'] = $slide_gap;
            }
        }

        // Card gap.
        $card_gap = $this->get_slider_size( $settings, 'card_gap', 20 );
        if ( $card_gap ) {
            $render_settings['card_gap'] = $card_gap;
        }

        $render = new Devsroom_GReviews_Render();
        echo $render->render( $render_settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    protected function _content_template() {
        ?>
        <div style="padding:20px;background:#f8f9fa;border:1px dashed #ccc;border-radius:8px;text-align:center;">
            <span style="font-size:24px;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            <p style="margin:8px 0 0;color:#666;"><?php esc_html_e( 'Google Reviews will display here.', 'devsroom-google-review-showcase' ); ?></p>
        </div>
        <?php
    }
}
