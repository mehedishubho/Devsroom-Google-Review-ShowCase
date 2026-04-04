<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Devsroom_GReviews_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     */
    public function get_name() {
        return 'devsroom-google-reviews';
    }

    /**
     * Get widget title.
     */
    public function get_title() {
        return __( 'Google Review ShowCase', 'devsroom-google-review-showcase' );
    }

    /**
     * Get widget icon.
     */
    public function get_icon() {
        return 'eicon-google-maps';
    }

    /**
     * Get widget categories.
     */
    public function get_categories() {
        return array( 'devsroom-widgets' );
    }

    /**
     * Get widget keywords.
     */
    public function get_keywords() {
        return array( 'google', 'reviews', 'testimonial', 'rating', 'devsroom' );
    }

    /**
     * Register widget controls.
     */
    protected function register_controls() {
        $this->register_content_controls();
        $this->register_style_controls();
    }

    /**
     * Content tab controls.
     */
    private function register_content_controls() {
        // Layout Section.
        $this->start_controls_section(
            'section_layout',
            array(
                'label' => __( 'Layout', 'devsroom-google-review-showcase' ),
            )
        );

        $this->add_control(
            'layout_type',
            array(
                'label'   => __( 'Layout Type', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => array(
                    'slider'  => __( 'Slider', 'devsroom-google-review-showcase' ),
                    'grid'    => __( 'Grid', 'devsroom-google-review-showcase' ),
                    'masonry' => __( 'Masonry', 'devsroom-google-review-showcase' ),
                    'list'    => __( 'List', 'devsroom-google-review-showcase' ),
                ),
            )
        );

        $this->add_control(
            'columns',
            array(
                'label'     => __( 'Columns', 'devsroom-google-review-showcase' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 3,
                'options'   => array( 1 => '1', 2 => '2', 3 => '3', 4 => '4' ),
                'condition' => array( 'layout_type' => array( 'grid', 'masonry' ) ),
            )
        );

        $this->end_controls_section();

        // Query Section.
        $this->start_controls_section(
            'section_query',
            array(
                'label' => __( 'Query Settings', 'devsroom-google-review-showcase' ),
            )
        );

        $this->add_control(
            'limit',
            array(
                'label'   => __( 'Limit', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min'     => 1,
                'max'     => 50,
            )
        );

        $this->add_control(
            'min_rating',
            array(
                'label'   => __( 'Minimum Rating', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 1,
                'options' => array(
                    1 => '1+',
                    2 => '2+',
                    3 => '3+',
                    4 => '4+',
                    5 => '5',
                ),
            )
        );

        $this->add_control(
            'sort_order',
            array(
                'label'   => __( 'Sort Order', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'newest',
                'options' => array(
                    'newest'        => __( 'Newest First', 'devsroom-google-review-showcase' ),
                    'oldest'        => __( 'Oldest First', 'devsroom-google-review-showcase' ),
                    'highest_rated' => __( 'Highest Rated', 'devsroom-google-review-showcase' ),
                ),
            )
        );

        $this->end_controls_section();

        // Visibility Section.
        $this->start_controls_section(
            'section_visibility',
            array(
                'label' => __( 'Element Visibility', 'devsroom-google-review-showcase' ),
            )
        );

        $this->add_control(
            'show_photo',
            array(
                'label'   => __( 'Reviewer Photo', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_name',
            array(
                'label'   => __( 'Reviewer Name', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_rating',
            array(
                'label'   => __( 'Rating Stars', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_date',
            array(
                'label'   => __( 'Review Date', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_more',
            array(
                'label'   => __( 'Read More Button', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
            )
        );

        $this->end_controls_section();

        // Content Order Section.
        $this->start_controls_section(
            'section_order',
            array(
                'label' => __( 'Content Order', 'devsroom-google-review-showcase' ),
            )
        );

        $this->add_control(
            'content_order',
            array(
                'label'   => __( 'Content Order', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'content_top',
                'options' => array(
                    'content_top'    => __( 'Content Top (text first)', 'devsroom-google-review-showcase' ),
                    'content_bottom' => __( 'Content Bottom (text last)', 'devsroom-google-review-showcase' ),
                    'name_top'       => __( 'Name + Image Top', 'devsroom-google-review-showcase' ),
                    'name_bottom'    => __( 'Name + Image Bottom', 'devsroom-google-review-showcase' ),
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Style tab controls.
     */
    private function register_style_controls() {
        // Card General.
        $this->start_controls_section(
            'section_style_card',
            array(
                'label' => __( 'Card', 'devsroom-google-review-showcase' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'card_bg_color',
            array(
                'label'     => __( 'Background Color', 'devsroom-google-review-showcase' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .devsroom-greviews-card' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_responsive_control(
            'card_padding',
            array(
                'label'      => __( 'Padding', 'devsroom-google-review-showcase' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .devsroom-greviews-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'card_border_radius',
            array(
                'label'      => __( 'Border Radius', 'devsroom-google-review-showcase' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px', '%' ),
                'range'      => array( 'px' => array( 'max' => 50 ) ),
                'selectors'  => array(
                    '{{WRAPPER}} .devsroom-greviews-card' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'     => 'card_border',
                'selector' => '{{WRAPPER}} .devsroom-greviews-card',
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'card_box_shadow',
                'selector' => '{{WRAPPER}} .devsroom-greviews-card',
            )
        );

        $this->add_control(
            'card_gap',
            array(
                'label'      => __( 'Card Gap', 'devsroom-google-review-showcase' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array( 'px' => array( 'max' => 60 ) ),
                'default'    => array( 'size' => 20 ),
                'selectors'  => array(
                    '{{WRAPPER}} .devsroom-greviews-grid, {{WRAPPER}} .devsroom-greviews-masonry' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .devsroom-greviews-list' => 'gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .devsroom-greviews-slider .swiper-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Reviewer Section.
        $this->start_controls_section(
            'section_style_reviewer',
            array(
                'label' => __( 'Reviewer', 'devsroom-google-review-showcase' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'photo_size',
            array(
                'label'      => __( 'Photo Size', 'devsroom-google-review-showcase' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array( 'px' => array( 'min' => 24, 'max' => 120 ) ),
                'default'    => array( 'size' => 48 ),
                'selectors'  => array(
                    '{{WRAPPER}} .devsroom-greviews-card__photo img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .devsroom-greviews-card__photo--placeholder' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'photo_shape',
            array(
                'label'   => __( 'Photo Shape', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'round',
                'options' => array(
                    'round'  => __( 'Round', 'devsroom-google-review-showcase' ),
                    'square' => __( 'Square', 'devsroom-google-review-showcase' ),
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'name_typography',
                'label'    => __( 'Name Typography', 'devsroom-google-review-showcase' ),
                'selector' => '{{WRAPPER}} .devsroom-greviews-card__name',
            )
        );

        $this->add_control(
            'name_color',
            array(
                'label'     => __( 'Name Color', 'devsroom-google-review-showcase' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .devsroom-greviews-card__name' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // Review Text.
        $this->start_controls_section(
            'section_style_text',
            array(
                'label' => __( 'Review Text', 'devsroom-google-review-showcase' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'text_typography',
                'label'    => __( 'Text Typography', 'devsroom-google-review-showcase' ),
                'selector' => '{{WRAPPER}} .devsroom-greviews-card__text',
            )
        );

        $this->add_control(
            'text_color',
            array(
                'label'     => __( 'Text Color', 'devsroom-google-review-showcase' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .devsroom-greviews-card__text' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'text_line_limit',
            array(
                'label'       => __( 'Line Limit', 'devsroom-google-review-showcase' ),
                'type'        => \Elementor\Controls_Manager::NUMBER,
                'default'     => 0,
                'description' => __( 'Set to 0 for no limit.', 'devsroom-google-review-showcase' ),
            )
        );

        $this->end_controls_section();

        // Rating Stars.
        $this->start_controls_section(
            'section_style_stars',
            array(
                'label' => __( 'Rating Stars', 'devsroom-google-review-showcase' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'star_size',
            array(
                'label'      => __( 'Star Size', 'devsroom-google-review-showcase' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array( 'px' => array( 'min' => 10, 'max' => 40 ) ),
                'default'    => array( 'size' => 18 ),
                'selectors'  => array(
                    '{{WRAPPER}} .devsroom-greviews-star' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'star_color',
            array(
                'label'     => __( 'Star Color', 'devsroom-google-review-showcase' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'default'   => '#f59e0b',
                'selectors' => array(
                    '{{WRAPPER}} .devsroom-greviews-star--filled' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'star_spacing',
            array(
                'label'      => __( 'Star Spacing', 'devsroom-google-review-showcase' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px' ),
                'range'      => array( 'px' => array( 'max' => 10 ) ),
                'default'    => array( 'size' => 2 ),
                'selectors'  => array(
                    '{{WRAPPER}} .devsroom-greviews-rating' => 'gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Date.
        $this->start_controls_section(
            'section_style_date',
            array(
                'label' => __( 'Date', 'devsroom-google-review-showcase' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name'     => 'date_typography',
                'label'    => __( 'Date Typography', 'devsroom-google-review-showcase' ),
                'selector' => '{{WRAPPER}} .devsroom-greviews-card__date',
            )
        );

        $this->add_control(
            'date_color',
            array(
                'label'     => __( 'Date Color', 'devsroom-google-review-showcase' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .devsroom-greviews-card__date' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'date_format',
            array(
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
            )
        );

        $this->end_controls_section();

        // Slider Only.
        $this->start_controls_section(
            'section_style_slider',
            array(
                'label'     => __( 'Slider', 'devsroom-google-review-showcase' ),
                'tab'       => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array( 'layout_type' => 'slider' ),
            )
        );

        $this->add_control(
            'arrow_color',
            array(
                'label'     => __( 'Arrow Color', 'devsroom-google-review-showcase' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .swiper-button-prev, {{WRAPPER}} .swiper-button-next' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'dot_color',
            array(
                'label'     => __( 'Dot Color', 'devsroom-google-review-showcase' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .swiper-pagination-bullet' => 'background: {{VALUE}};',
                    '{{WRAPPER}} .swiper-pagination-bullet-active' => 'background: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'autoplay',
            array(
                'label'   => __( 'Autoplay', 'devsroom-google-review-showcase' ),
                'type'    => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
            )
        );

        $this->add_control(
            'autoplay_speed',
            array(
                'label'     => __( 'Autoplay Speed (ms)', 'devsroom-google-review-showcase' ),
                'type'      => \Elementor\Controls_Manager::NUMBER,
                'default'   => 3000,
                'min'       => 1000,
                'max'       => 10000,
                'condition' => array( 'autoplay' => 'yes' ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend.
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        // Check if API is configured.
        if ( empty( get_option( 'devsroom_greviews_api_key' ) ) || empty( get_option( 'devsroom_greviews_place_id' ) ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div style="padding:20px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;text-align:center;">';
                esc_html_e( 'Please configure your Google API Key and Place ID in Settings → Devsroom Google Reviews.', 'devsroom-google-review-showcase' );
                echo '</div>';
            }
            return;
        }

        // Map Elementor settings to render settings.
        $render_settings = array(
            'layout'         => $settings['layout_type'],
            'order'          => $settings['content_order'],
            'limit'          => intval( $settings['limit'] ),
            'rating'         => intval( $settings['min_rating'] ),
            'sort_order'     => $settings['sort_order'],
            'show_photo'     => 'yes' === $settings['show_photo'],
            'show_name'      => 'yes' === $settings['show_name'],
            'show_rating'    => 'yes' === $settings['show_rating'],
            'show_date'      => 'yes' === $settings['show_date'],
            'show_more'      => 'yes' === $settings['show_more'],
            'columns'        => intval( $settings['columns'] ),
            'photo_size'     => intval( $settings['photo_size']['size'] ),
            'photo_shape'    => $settings['photo_shape'],
            'star_size'      => intval( $settings['star_size']['size'] ),
            'star_color'     => $settings['star_color'],
            'date_format'    => $settings['date_format'],
            'text_line_limit' => intval( $settings['text_line_limit'] ),
        );

        // Slider-specific settings.
        if ( 'slider' === $settings['layout_type'] ) {
            $render_settings['autoplay']       = 'yes' === $settings['autoplay'];
            $render_settings['autoplay_speed'] = intval( $settings['autoplay_speed'] );
        }

        // Card gap.
        if ( ! empty( $settings['card_gap']['size'] ) ) {
            $render_settings['card_gap'] = intval( $settings['card_gap']['size'] );
        }

        $render = new Devsroom_GReviews_Render();
        echo $render->render( $render_settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Render widget output in Elementor editor (live preview).
     */
    protected function _content_template() {
        ?>
        <div style="padding:20px;background:#f8f9fa;border:1px dashed #ccc;border-radius:8px;text-align:center;">
            <span style="font-size:24px;">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
            <p style="margin:8px 0 0;color:#666;"><?php esc_html_e( 'Google Reviews will display here.', 'devsroom-google-review-showcase' ); ?></p>
        </div>
        <?php
    }
}
