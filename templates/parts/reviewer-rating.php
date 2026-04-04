<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$rating = intval( $review['rating'] );
$star_size = ! empty( $settings['star_size'] ) ? intval( $settings['star_size'] ) : 18;
$star_color = ! empty( $settings['star_color'] ) ? $settings['star_color'] : '#f59e0b';
?>
<div class="devsroom-greviews-card__rating" style="--greviews-star-color: <?php echo esc_attr( $star_color ); ?>; --greviews-star-size: <?php echo esc_attr( $star_size ); ?>px;">
    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
        <?php if ( $i <= $rating ) : ?>
            <svg class="devsroom-greviews-star devsroom-greviews-star--filled" width="<?php echo esc_attr( $star_size ); ?>" height="<?php echo esc_attr( $star_size ); ?>" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
        <?php else : ?>
            <svg class="devsroom-greviews-star devsroom-greviews-star--empty" width="<?php echo esc_attr( $star_size ); ?>" height="<?php echo esc_attr( $star_size ); ?>" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
        <?php endif; ?>
    <?php endfor; ?>
</div>
