<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$autoplay                = ! empty( $settings['autoplay'] ) ? 'true' : 'false';
$autoplay_speed          = ! empty( $settings['autoplay_speed'] ) ? intval( $settings['autoplay_speed'] ) : 3000;
$slide_gap               = ! empty( $settings['slide_gap'] ) ? intval( $settings['slide_gap'] ) : 20;
$slides_per_view         = ! empty( $settings['slides_per_view'] ) ? intval( $settings['slides_per_view'] ) : 3;
$slides_per_view_tablet  = ! empty( $settings['slides_per_view_tablet'] ) ? intval( $settings['slides_per_view_tablet'] ) : max( 1, $slides_per_view - 1 );
$slides_per_view_mobile  = ! empty( $settings['slides_per_view_mobile'] ) ? intval( $settings['slides_per_view_mobile'] ) : 1;
$slides_per_scroll       = ! empty( $settings['slides_per_scroll'] ) ? intval( $settings['slides_per_scroll'] ) : 1;
$slides_per_scroll_tablet = ! empty( $settings['slides_per_scroll_tablet'] ) ? intval( $settings['slides_per_scroll_tablet'] ) : 1;
$slides_per_scroll_mobile = ! empty( $settings['slides_per_scroll_mobile'] ) ? intval( $settings['slides_per_scroll_mobile'] ) : 1;
$equal_height            = ! empty( $settings['equal_height'] );
$pause_on_hover          = isset( $settings['pause_on_hover'] ) ? ( $settings['pause_on_hover'] ? 'true' : 'false' ) : 'true';
$pause_on_interaction    = isset( $settings['pause_on_interaction'] ) ? ( $settings['pause_on_interaction'] ? 'true' : 'false' ) : 'true';
$infinite_scroll         = isset( $settings['infinite_scroll'] ) ? ( $settings['infinite_scroll'] ? 'true' : 'false' ) : 'true';
$transition_duration     = ! empty( $settings['transition_duration'] ) ? intval( $settings['transition_duration'] ) : 300;
$direction               = ! empty( $settings['direction'] ) ? $settings['direction'] : 'ltr';
$offset_sides            = ! empty( $settings['offset_sides'] ) ? $settings['offset_sides'] : 'none';
$offset_width            = ! empty( $settings['offset_width'] ) ? intval( $settings['offset_width'] ) : 0;
$offset_width_tablet     = ! empty( $settings['offset_width_tablet'] ) ? intval( $settings['offset_width_tablet'] ) : 0;
$offset_width_mobile     = ! empty( $settings['offset_width_mobile'] ) ? intval( $settings['offset_width_mobile'] ) : 0;
$show_navigation         = ! isset( $settings['show_navigation'] ) || $settings['show_navigation'];
$show_pagination         = ! isset( $settings['show_pagination'] ) || $settings['show_pagination'];

// Arrow icons (Elementor ICONS control returns array with 'value' and 'library').
$arrow_icon_prev = ! empty( $settings['arrow_icon_prev']['value'] ) ? $settings['arrow_icon_prev'] : array( 'value' => 'fas fa-chevron-left', 'library' => 'fa-solid' );
$arrow_icon_next = ! empty( $settings['arrow_icon_next']['value'] ) ? $settings['arrow_icon_next'] : array( 'value' => 'fas fa-chevron-right', 'library' => 'fa-solid' );

$block_to_file = array(
    'photo'  => 'reviewer-photo.php',
    'name'   => 'reviewer-name.php',
    'rating' => 'reviewer-rating.php',
    'text'   => 'review-text.php',
    'date'   => 'review-date.php',
);

// Build offset CSS classes.
$slider_classes = 'devsroom-greviews devsroom-greviews-slider swiper';
if ( 'none' !== $offset_sides ) {
    $slider_classes .= ' devsroom-greviews-slider--offset-' . $offset_sides;
}
if ( $equal_height ) {
    $slider_classes .= ' devsroom-greviews-slider--equal-height';
}

// Build inline style for offset.
$offset_style = '';
if ( 'none' !== $offset_sides && $offset_width > 0 ) {
    $offset_style .= '--greviews-offset-width: ' . esc_attr( $offset_width ) . 'px;';
    $offset_style .= '--greviews-offset-width-tablet: ' . esc_attr( $offset_width_tablet ) . 'px;';
    $offset_style .= '--greviews-offset-width-mobile: ' . esc_attr( $offset_width_mobile ) . 'px;';
}

// Render an icon tag (SVG or Font Awesome).
$render_icon = function( $icon ) {
    if ( empty( $icon['value'] ) ) {
        return '';
    }
    // Check if it's an SVG icon.
    if ( isset( $icon['library'] ) && 'svg' === $icon['library'] ) {
        $svg_data = get_option( 'elementor_icons_data_' . $icon['value'] );
        if ( ! empty( $svg_data ) ) {
            return '<i class="' . esc_attr( $icon['value'] ) . '"></i>';
        }
    }
    return '<i class="' . esc_attr( $icon['value'] ) . '"></i>';
};
?>
<div class="<?php echo esc_attr( $slider_classes ); ?>"
     data-autoplay="<?php echo esc_attr( $autoplay ); ?>"
     data-speed="<?php echo esc_attr( $autoplay_speed ); ?>"
     data-gap="<?php echo esc_attr( $slide_gap ); ?>"
     data-slides-per-view="<?php echo esc_attr( $slides_per_view ); ?>"
     data-slides-per-view-tablet="<?php echo esc_attr( $slides_per_view_tablet ); ?>"
     data-slides-per-view-mobile="<?php echo esc_attr( $slides_per_view_mobile ); ?>"
     data-slides-per-scroll="<?php echo esc_attr( $slides_per_scroll ); ?>"
     data-slides-per-scroll-tablet="<?php echo esc_attr( $slides_per_scroll_tablet ); ?>"
     data-slides-per-scroll-mobile="<?php echo esc_attr( $slides_per_scroll_mobile ); ?>"
     data-equal-height="<?php echo esc_attr( $equal_height ? 'true' : 'false' ); ?>"
     data-pause-on-hover="<?php echo esc_attr( $pause_on_hover ); ?>"
     data-pause-on-interaction="<?php echo esc_attr( $pause_on_interaction ); ?>"
     data-infinite-scroll="<?php echo esc_attr( $infinite_scroll ); ?>"
     data-transition-duration="<?php echo esc_attr( $transition_duration ); ?>"
     data-direction="<?php echo esc_attr( $direction ); ?>"
     <?php if ( ! empty( $offset_style ) ) : ?>
     style="<?php echo esc_attr( $offset_style ); ?>"
     <?php endif; ?>
     dir="<?php echo esc_attr( $direction ); ?>">
    <div class="swiper-wrapper">
        <?php foreach ( $reviews as $review ) : ?>
            <div class="swiper-slide">
                <div class="devsroom-greviews-card">
                    <?php foreach ( $order as $block ) : ?>
                        <?php
                        if ( isset( $block_to_file[ $block ] ) ) {
                            include DEVSROOM_GREVIEWS_DIR . 'templates/parts/' . $block_to_file[ $block ];
                        }
                        ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if ( $show_pagination ) : ?>
    <div class="swiper-pagination"></div>
    <?php endif; ?>
    <?php if ( $show_navigation ) : ?>
    <div class="swiper-button-prev"><?php echo $render_icon( $arrow_icon_prev ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
    <div class="swiper-button-next"><?php echo $render_icon( $arrow_icon_next ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
    <?php endif; ?>
</div>
