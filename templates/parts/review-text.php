<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text       = $review['text'];
$show_more  = ! empty( $settings['show_more'] ) ? $settings['show_more'] : false;
$line_limit = ! empty( $settings['text_line_limit'] ) ? intval( $settings['text_line_limit'] ) : 0;

if ( $show_more && mb_strlen( $text ) > 150 ) :
    $truncated = mb_substr( $text, 0, 150 );
?>
    <div class="devsroom-greviews-card__text devsroom-greviews-card__text--truncated">
        <span class="devsroom-greviews-text-short"><?php echo esc_html( $truncated ); ?>...</span>
        <span class="devsroom-greviews-text-full" style="display:none;"><?php echo esc_html( $text ); ?></span>
        <a href="#" class="devsroom-greviews-read-more"><?php esc_html_e( 'Read more', 'devsroom-google-review-showcase' ); ?></a>
    </div>
<?php else : ?>
    <div class="devsroom-greviews-card__text"<?php if ( $line_limit > 0 ) : ?> style="--greviews-line-limit: <?php echo esc_attr( $line_limit ); ?>;"<?php endif; ?>>
        <?php echo esc_html( $text ); ?>
    </div>
<?php endif; ?>
