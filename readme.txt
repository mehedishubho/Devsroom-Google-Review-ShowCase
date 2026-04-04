=== Devsroom Google Review ShowCase ===
Contributors: devsroom
Tags: google reviews, google my business, testimonials, elementor, shortcode
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 0.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Plugin Name: Devsroom Google Review ShowCase
Plugin Title: Devsroom Google Review ShowCase
Plugin URI: https://wordpress.org/plugins/devsroom-google-review-showcase/
Author: WPMHS <mhs@wpmhs.com>
Author URI: https://www.wpmhs.com/
Version: 0.0.1

Devsroom Google Review ShowCase Embed Google reviews or Google My Business reviews fast and easily into on your WordPress site via shortcode and Elementor widget.




**Features:**

* Google Places API integration with smart caching
* Four layout types: Slider, Grid, Masonry, List
* Dynamic content ordering — reorder photo, name, rating, text, date
* Elementor widget with full Content and Style controls
* Shortcode support with flexible attributes
* Conditional asset loading for optimal performance
* Admin settings with Test Fetch and Clear Cache

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
4. Enter your Google API Key and Place ID
5. Use the shortcode or Elementor widget to display reviews

== Frequently Asked Questions ==

= Where do I get a Google API Key? =
Visit the Google Cloud Console, enable the Places API, and create an API key.

= Where do I find my Place ID? =
Use the Google Place ID finder tool to locate your business Place ID.

= Does this require Elementor? =
No. The plugin works via shortcode on any WordPress site. The Elementor widget is an optional enhancement.

== Changelog ==

= 0.0.1 =
* Initial release
* Google Places API integration
* Four layout types (Slider, Grid, Masonry, List)
* Dynamic content ordering
* Elementor widget
* Admin settings page
* Smart caching system
