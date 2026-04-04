<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$columns        = ! empty( $settings['columns'] ) ? intval( $settings['columns'] ) : 3;
$columns_tablet = ! empty( $settings['columns_tablet'] ) ? intval( $settings['columns_tablet'] ) : max( 1, $columns - 1 );
$columns_mobile = ! empty( $settings['columns_mobile'] ) ? intval( $settings['columns_mobile'] ) : 1;
$card_gap       = ! empty( $settings['card_gap'] ) ? intval( $settings['card_gap'] ) : 20;

$block_to_file = array(
    'photo'  => 'reviewer-photo.php',
    'name'   => 'reviewer-name.php',
    'rating' => 'reviewer-rating.php',
    'text'   => 'review-text.php',
    'date'   => 'review-date.php',
);
?>
<div class="devsroom-greviews devsroom-greviews-masonry"
     data-masonry="true"
     style="--greviews-columns: <?php echo esc_attr( $columns ); ?>; --greviews-columns-tablet: <?php echo esc_attr( $columns_tablet ); ?>; --greviews-columns-mobile: <?php echo esc_attr( $columns_mobile ); ?>; --greviews-gap: <?php echo esc_attr( $card_gap ); ?>px;">
    <?php foreach ( $reviews as $review ) : ?>
        <div class="devsroom-greviews-card devsroom-greviews-card--masonry">
            <?php foreach ( $order as $block ) : ?>
                <?php
                if ( isset( $block_to_file[ $block ] ) ) {
                    include DEVSROOM_GREVIEWS_DIR . 'templates/parts/' . $block_to_file[ $block ];
                }
                ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
