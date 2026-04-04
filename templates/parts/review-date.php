<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$date_format = ! empty( $settings['date_format'] ) ? $settings['date_format'] : 'relative';

if ( 'relative' === $date_format || empty( $review['time'] ) ) :
    $date_string = ! empty( $review['relative_time_description'] ) ? $review['relative_time_description'] : '';
else :
    $date_string = date_i18n( $date_format, $review['time'] );
endif;

if ( $date_string ) :
?>
    <span class="devsroom-greviews-card__date"><?php echo esc_html( $date_string ); ?></span>
<?php endif; ?>
