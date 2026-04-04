<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$photo_url = ! empty( $review['author_photo'] ) ? $review['author_photo'] : '';
$photo_size = ! empty( $settings['photo_size'] ) ? intval( $settings['photo_size'] ) : 48;
$photo_shape = ! empty( $settings['photo_shape'] ) ? $settings['photo_shape'] : 'round';

$shape_class = 'round' === $photo_shape ? 'devsroom-greviews-card__photo--round' : 'devsroom-greviews-card__photo--square';

if ( $photo_url ) :
?>
    <div class="devsroom-greviews-card__photo <?php echo esc_attr( $shape_class ); ?>">
        <img src="<?php echo esc_url( $photo_url ); ?>"
             alt="<?php echo esc_attr( $review['author_name'] ); ?>"
             width="<?php echo esc_attr( $photo_size ); ?>"
             height="<?php echo esc_attr( $photo_size ); ?>"
             loading="lazy" />
    </div>
<?php else : ?>
    <div class="devsroom-greviews-card__photo <?php echo esc_attr( $shape_class ); ?> devsroom-greviews-card__photo--placeholder">
        <span><?php echo esc_html( mb_substr( $review['author_name'], 0, 1 ) ); ?></span>
    </div>
<?php endif; ?>
