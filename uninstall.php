<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete API Key mode options.
delete_option( 'devsroom_greviews_cache' );
delete_option( 'devsroom_greviews_api_key' );
delete_option( 'devsroom_greviews_place_id' );
delete_option( 'devsroom_greviews_cache_duration' );
delete_option( 'devsroom_greviews_last_fetch' );

// Delete connection mode option.
delete_option( 'devsroom_greviews_connection_mode' );

// Delete OAuth mode options.
delete_option( 'devsroom_greviews_oauth_client_id' );
delete_option( 'devsroom_greviews_oauth_client_secret' );
delete_option( 'devsroom_greviews_oauth_access_token' );
delete_option( 'devsroom_greviews_oauth_refresh_token' );
delete_option( 'devsroom_greviews_oauth_token_expiry' );
delete_option( 'devsroom_greviews_oauth_user_email' );
delete_option( 'devsroom_greviews_oauth_user_name' );
delete_option( 'devsroom_greviews_oauth_account_name' );
delete_option( 'devsroom_greviews_oauth_location_name' );
delete_option( 'devsroom_greviews_oauth_business_name' );
delete_option( 'devsroom_greviews_oauth_reviews' );
delete_option( 'devsroom_greviews_oauth_last_sync' );
delete_option( 'devsroom_greviews_sync_interval' );

// Clear scheduled cron.
wp_clear_scheduled_hook( 'devsroom_greviews_cron_sync' );
