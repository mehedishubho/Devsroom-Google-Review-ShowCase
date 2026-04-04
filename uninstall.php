<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete plugin options.
delete_option( 'devsroom_greviews_cache' );
delete_option( 'devsroom_greviews_api_key' );
delete_option( 'devsroom_greviews_place_id' );
delete_option( 'devsroom_greviews_cache_duration' );
delete_option( 'devsroom_greviews_last_fetch' );
