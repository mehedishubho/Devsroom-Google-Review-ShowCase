<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$autoplay      = ! empty( $settings['autoplay'] ) ? 'true' : 'false';
$autoplay_speed = ! empty( $settings['autoplay_speed'] ) ? intval( $settings['autoplay_speed'] ) : 3000;
$slide_gap      = ! empty( $settings['slide_gap'] ) ? intval( $settings['slide_gap'] ) : 20;

$block_to_file = array(
    'photo'  => 'reviewer-photo.php',
    'name'   => 'reviewer-name.php',
    'rating' => 'reviewer-rating.php',
    'text'   => 'review-text.php',
    'date'   => 'review-date.php',
);
?>
<div class="devsroom-greviews devsroom-greviews-slider swiper"
     data-autoplay="<?php echo esc_attr( $autoplay ); ?>"
     data-speed="<?php echo esc_attr( $autoplay_speed ); ?>"
     data-gap="<?php echo esc_attr( $slide_gap ); ?>">
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
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
</div>
