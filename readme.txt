=== Devsroom Google Review ShowCase ===
Contributors: devsroom
Tags: google reviews, google my business, testimonials, elementor, shortcode, oauth
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 0.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Plugin Name: Devsroom Google Review ShowCase
Plugin Title: Devsroom Google Review ShowCase
Plugin URI: https://wordpress.org/plugins/devsroom-google-review-showcase/
Author: WPMHS <mhs@wpmhs.com>
Author URI: https://www.wpmhs.com/
Version: 0.0.2

Devsroom Google Review ShowCase embed Google reviews or Google My Business reviews on your WordPress site via shortcode and Elementor widget. Supports two connection modes: API Key + Place ID or Google OAuth 2.0.



**Features:**

* Two connection modes: API Key + Place ID or Google OAuth 2.0
* Google Places API integration with smart caching
* Google Business Profile OAuth — fetch ALL reviews from your account
* Automatic background sync via WP Cron (every 6 hours, daily, or weekly)
* Four layout types: Slider, Grid, Masonry, List
* Dynamic content ordering — reorder photo, name, rating, text, date
* Elementor widget with full Content and Style controls
* Shortcode support with flexible attributes
* Conditional asset loading for optimal performance
* Admin settings with Test Fetch, Sync Now, and Clear Cache
* AES-256-CBC encrypted token storage for OAuth mode

**Shortcode Usage:**

    [devsroom_greviews layout="grid" limit="6" rating="4" order="content_top"]

**Shortcode Attributes:**

* `layout` — slider, grid, masonry, list (default: grid)
* `order` — content_top, content_bottom, name_top, name_bottom (default: content_top)
* `limit` — Number of reviews to show (default: 5)
* `rating` — Minimum rating filter 1-5 (default: 1)
* `show_photo` — yes/no (default: yes)
* `show_name` — yes/no (default: yes)
* `show_rating` — yes/no (default: yes)
* `show_date` — yes/no (default: yes)
* `show_more` — yes/no (default: no)

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate through the Plugins menu
3. Go to Settings → Devsroom Google Reviews
4. Choose a connection method:
   * **API Key mode** — Enter your Google API Key and Place ID
   * **OAuth mode** — Enter Client ID and Client Secret, then click Connect Google Account
5. Use the shortcode or Elementor widget to display reviews

== Connection Modes ==

= Mode 1: API Key + Place ID =

The original method. Enter your Google Cloud API Key and Place ID in settings. Uses the Google Places API to fetch reviews. Cached for the configured duration (default 24 hours).

= Mode 2: Connect Google Account (OAuth 2.0) =

Connect directly to your Google Business Profile using OAuth 2.0. Fetches ALL reviews from your business location with no limit. Supports automatic background syncing.

**Setup steps:**
1. Go to Google Cloud Console → APIs & Services → Library
2. Enable the **Google Business Profile API**
3. Go to Credentials → Create OAuth client ID (Web application)
4. Add the authorized redirect URI shown in the plugin settings
5. Copy the Client ID and Client Secret into the plugin settings
6. Click **Connect Google Account** and grant access

**Features:**
* Fetches all reviews (not limited to 5)
* Automatic sync via WP Cron (every 6 hours, daily, weekly, or manual)
* Sync Now button for immediate manual sync
* Business location selector (if you have multiple locations)
* Disconnect button to revoke access
* Tokens encrypted with AES-256-CBC before storage

== Frequently Asked Questions ==

= Where do I get a Google API Key? =
Visit the Google Cloud Console, enable the Places API, and create an API key.

= Where do I find my Place ID? =
Use the Google Place ID finder tool to locate your business Place ID.

= What is the difference between API Key and OAuth modes? =
API Key mode uses the Google Places API and is limited to reviews available through that API. OAuth mode connects directly to your Google Business Profile and fetches ALL reviews with no limit. OAuth mode also supports automatic background syncing.

= Does this require Elementor? =
No. The plugin works via shortcode on any WordPress site. The Elementor widget is an optional enhancement.

= Is the OAuth connection secure? =
Yes. OAuth tokens are encrypted with AES-256-CBC using your WordPress salt before being stored in the database. All API calls use WordPress core HTTP functions (no curl). Nonces are verified on all admin actions.

= Can I use both connection modes at the same time? =
No. You select one mode via the radio toggle in settings. Only the active mode is used to fetch reviews for display.

= What happens to my reviews if I disconnect my Google account? =
Disconnecting revokes the OAuth token and clears stored credentials, but existing synced reviews are kept in the database and continue to display on your site.

== Changelog ==

= 0.0.2 =
* Added OAuth 2.0 connection mode (Connect Google Account)
* Google Business Profile API integration for fetching all reviews
* Automatic background sync via WP Cron (6 hours, daily, weekly, manual)
* Business location selector for multi-location businesses
* AES-256-CBC encrypted token storage
* Mode toggle in settings (API Key vs OAuth)
* Sync Now and Disconnect buttons
* Updated admin settings page with dual-mode UI
* Updated User Guide with OAuth setup instructions

= 0.0.1 =
* Initial release
* Google Places API integration
* Four layout types (Slider, Grid, Masonry, List)
* Dynamic content ordering
* Elementor widget
* Admin settings page
* Smart caching system
