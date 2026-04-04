<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$columns = ! empty( $settings['columns'] ) ? intval( $settings['columns'] ) : 3;
$card_gap = ! empty( $settings['card_gap'] ) ? intval( $settings['card_gap'] ) : 20;

$block_to_file = array(
    'photo'  => 'reviewer-photo.php',
    'name'   => 'reviewer-name.php',
    'rating' => 'reviewer-rating.php',
    'text'   => 'review-text.php',
    'date'   => 'review-date.php',
);
?>
<div class="devsroom-greviews devsroom-greviews-grid"
     style="--greviews-columns: <?php echo esc_attr( $columns ); ?>; --greviews-gap: <?php echo esc_attr( $card_gap ); ?>px;">
    <?php foreach ( $reviews as $review ) : ?>
        <div class="devsroom-greviews-card">
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
