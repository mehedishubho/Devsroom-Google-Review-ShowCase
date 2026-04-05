# Devsroom Google Review ShowCase

> Fetch and display verified Google My Business reviews on your WordPress site via shortcode and Elementor widget. Supports two connection modes: API Key + Place ID or Google OAuth 2.0.

## Features

- **Two connection modes** — API Key + Place ID or Google OAuth 2.0
- Google Places API integration with smart caching
- Google Business Profile OAuth — fetch ALL reviews from your account
- Automatic background sync via WP Cron (every 6 hours, daily, or weekly)
- Four layout types: **Slider**, **Grid**, **Masonry**, **List**
- Advanced slider settings — responsive slides on display/scroll, autoplay, pause on hover/interaction, infinite scroll, transition duration, direction (LTR/RTL), offset sides
- Dynamic content ordering — reorder photo, name, rating, text, date
- Elementor widget with full Content and Style controls
- Shortcode support with flexible attributes
- Conditional asset loading for optimal performance
- Admin settings with Test Fetch, Sync Now, and Clear Cache
- AES-256-CBC encrypted token storage for OAuth mode

---

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate through the **Plugins** menu
3. Go to **Google Reviews** (top-level menu in admin sidebar)
4. Choose a connection method:
   - **API Key mode** — Enter your Google API Key and Place ID
   - **OAuth mode** — Enter Client ID and Client Secret, then click Connect Google Account
5. Use the shortcode or Elementor widget to display reviews

---

## Connection Modes

### Mode 1: API Key + Place ID

The original method. Enter your Google Cloud API Key and Place ID in settings. Uses the Google Places API to fetch reviews. Cached for the configured duration (default 24 hours).

#### Getting Your Google API Credentials

**Step 1: Create a Google API Key**

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select an existing one)
3. Navigate to **APIs & Services → Library**
4. Search for **Places API** and click **Enable**
5. Go to **APIs & Services → Credentials**
6. Click **Create Credentials → API Key**
7. Copy the generated API key
8. (Recommended) Restrict the key to **Places API** only

**Step 2: Find Your Place ID**

1. Go to the [Google Place ID Finder](https://developers.google.com/maps/documentation/places/web-service/place-id)
2. Enter your business name in the search box
3. Select your business from the results
4. Copy the **Place ID** (format: `ChIJ...`)

### Mode 2: Connect Google Account (OAuth 2.0)

Connect directly to your Google Business Profile using OAuth 2.0. Fetches ALL reviews from your business location with no limit. Supports automatic background syncing.

#### Setup Steps

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project (or create a new one)
3. Navigate to **APIs & Services → Library**
4. Enable the **Google Business Profile API**
5. Go to **APIs & Services → Credentials**
6. Click **Create Credentials → OAuth client ID**
7. Application type: **Web application**
8. Authorized redirect URIs: add the URI shown in the plugin settings
9. Copy the **Client ID** and **Client Secret** into the plugin settings
10. Click **Save Changes**, then click **Connect Google Account**
11. Grant access to your Google Business Profile

#### OAuth Features

- Fetches all reviews (not limited to 5)
- Automatic sync via WP Cron (every 6 hours, daily, weekly, or manual)
- Sync Now button for immediate manual sync
- Business location selector (if you have multiple locations)
- Disconnect button to revoke access
- Tokens encrypted with AES-256-CBC before storage

---

## Plugin Settings

Navigate to **Google Reviews** (top-level menu in admin sidebar) in your WordPress dashboard.

### Connection Method

Choose between the two modes via radio toggle:

- **API Key + Place ID** — Uses Google Places API
- **Connect Google Account (OAuth)** — Uses Google Business Profile API

### API Key Mode Settings

| Field | Description |
|-------|-------------|
| **Google API Key** | Your Google Cloud API key with Places API enabled |
| **Google Place ID** | Your business Place ID from Google |
| **Cache Duration** | How long to cache reviews (in hours). Default: 24 hours |

### OAuth Mode Settings

| Field | Description |
|-------|-------------|
| **Client ID** | OAuth 2.0 Client ID from Google Cloud Console |
| **Client Secret** | OAuth 2.0 Client Secret from Google Cloud Console |
| **Business Location** | Select which business location to fetch reviews from |
| **Sync Interval** | How often to sync reviews (Every 6 Hours, Daily, Weekly, Manual Only) |

### Action Buttons

- **Test Fetch** (API Key mode) — Tests your API connection and shows how many reviews were found
- **Clear Cache** (API Key mode) — Forces a fresh fetch on the next page load
- **Connect Google Account** (OAuth mode) — Opens Google consent screen to authorize access
- **Sync Now** (OAuth mode) — Immediately syncs reviews from your Google Business Profile
- **Disconnect** (OAuth mode) — Revokes OAuth access and clears stored tokens

### Logs

The logs section shows:

- **Last Fetch Time** — When reviews were last fetched from Google
- **Status** — Success or Error
- **Message** — Details about the last fetch attempt

---

## Using the Shortcode

Add the following shortcode to any page, post, or widget area:

```
[devsroom_greviews]
```

This will display reviews using default settings (grid layout, 5 reviews, minimum 1-star rating). Works with both connection modes — no changes needed to your shortcode when switching modes.

### Shortcode Attributes

Customize the output with attributes:

```
[devsroom_greviews layout="slider" limit="10" rating="4" order="name_top"]
```

### All Available Attributes

| Attribute | Values | Default | Description |
|-----------|--------|---------|-------------|
| `layout` | `slider`, `grid`, `masonry`, `list` | `grid` | Display layout type |
| `order` | `content_top`, `content_bottom`, `name_top`, `name_bottom` | `content_top` | Content order within cards |
| `limit` | `1` – `50` | `5` | Number of reviews to display |
| `rating` | `1` – `5` | `1` | Minimum star rating filter |
| `show_photo` | `yes`, `no` | `yes` | Show/hide reviewer photo |
| `show_name` | `yes`, `no` | `yes` | Show/hide reviewer name |
| `show_rating` | `yes`, `no` | `yes` | Show/hide star rating |
| `show_date` | `yes`, `no` | `yes` | Show/hide review date |
| `show_more` | `yes`, `no` | `no` | Enable "Read more" for long reviews |

### Shortcode Examples

**Slider with 8 reviews, 4+ stars:**

```
[devsroom_greviews layout="slider" limit="8" rating="4"]
```

**Grid layout, name on top, 6 reviews, no photos:**

```
[devsroom_greviews layout="grid" order="name_top" limit="6" show_photo="no"]
```

**Masonry layout with read more enabled:**

```
[devsroom_greviews layout="masonry" show_more="yes" limit="12"]
```

**List layout, 3+ stars, hide dates:**

```
[devsroom_greviews layout="list" rating="3" show_date="no"]
```

**Minimal — text only, no photo/name/rating/date:**

```
[devsroom_greviews show_photo="no" show_name="no" show_rating="no" show_date="no"]
```

---

## Using the Elementor Widget

### Adding the Widget

1. Edit a page with **Elementor**
2. Search for **"Google Review ShowCase"** in the widget panel
3. Drag it to your page

The widget works with both connection modes. It reads from the same review source regardless of how the reviews were fetched.

### Content Tab

#### Layout Section

- **Layout Type** — Choose Slider, Grid, Masonry, or List
- **Columns** — Number of columns for Grid, Masonry, and List layouts (1–6, responsive)

#### Slider Settings (slider layout only)

- **Slides on Display** — Number of visible slides per view (responsive for desktop/tablet/mobile)
- **Slides on Scroll** — Number of slides to scroll at once (responsive for desktop/tablet/mobile)
- **Equal Height** — Make all slide cards the same height
- **Autoplay** — Enable automatic slide rotation
- **Scroll Speed (ms)** — Delay between slide transitions when autoplay is on
- **Pause on Hover** — Pause autoplay when hovering over the slider
- **Pause on Interaction** — Pause autoplay after user interaction (swipe/click)
- **Infinite Scroll** — Loop slides continuously
- **Transition Duration (ms)** — Speed of the slide transition animation
- **Slide Gap (px)** — Space between slides
- **Direction** — Left to Right or Right to Left
- **Offset Sides** — Add offset to slider edges: none, both, left, or right
- **Offset Width (px)** — Width of the offset (responsive for desktop/tablet/mobile)

#### Query Settings

- **Limit** — Number of reviews (1–50)
- **Minimum Rating** — Filter by star rating (1+ to 5)
- **Sort Order** — Newest First, Oldest First, or Highest Rated

#### Element Visibility

Toggle each element on/off:

- Reviewer Photo
- Reviewer Name
- Rating Stars
- Review Date
- Read More Button

#### Content Order

Control the position of elements within each review card:

- **Content Top** — Review text appears first
- **Content Bottom** — Review text appears last
- **Name + Image Top** — Reviewer info at the top
- **Name + Image Bottom** — Reviewer info at the bottom

### Style Tab

#### Card Styling

- Background color
- Padding
- Border radius
- Border (style, width, color)
- Box shadow
- Card gap (spacing between cards)

#### Reviewer Styling

- Photo size (24px–120px)
- Photo shape (round or square)
- Name typography (font family, size, weight, etc.)
- Name color

#### Review Text Styling

- Typography controls
- Text color
- Line limit (truncate text after N lines)

#### Rating Stars Styling

- Star size
- Star color
- Star spacing

#### Date Styling

- Typography controls
- Date color
- Date format:
  - Relative (e.g., "3 weeks ago")
  - Month Day, Year
  - YYYY-MM-DD
  - MM/DD/YYYY
  - DD/MM/YYYY

#### Slider Controls (slider layout only)

- Custom Previous/Next arrow icon (Elementor icon picker)
- Arrow color
- Arrow background color
- Arrow size
- Dot color — Normal
- Dot color — Active
- Dot size

---

## Layout Types

### Grid

Displays reviews in a responsive CSS grid. Cards have equal width with automatic height. Adjust columns from the settings.

### Slider

A carousel powered by Swiper.js with:

- Previous/Next navigation arrows (customizable icons)
- Pagination dots (separate normal/active colors)
- Autoplay with configurable speed, pause on hover, pause on interaction
- Infinite scroll (loop)
- Configurable transition duration
- Responsive slides on display and slides on scroll
- Direction control (LTR/RTL)
- Offset sides (none/both/left/right) with responsive width
- Equal height mode

### Masonry

A Pinterest-style layout where cards have varying heights based on their content. Longer reviews create taller cards while shorter reviews stay compact.

### List

A vertical list where each review card displays horizontally — reviewer photo on the left, content on the right. Supports responsive columns for multi-column layouts. Responsive: stacks vertically on mobile.

---

## Content Ordering

The plugin allows you to control the position of elements within each review card. Each review card has 5 possible elements:

1. **Photo** — Reviewer's profile image
2. **Name** — Reviewer's name
3. **Rating** — Star rating icons
4. **Text** — The review content
5. **Date** — When the review was posted

### Order Presets

| Preset | Element Order (top to bottom) |
|--------|-------------------------------|
| Content Top | Text → Name → Photo → Rating → Date |
| Content Bottom | Photo → Name → Rating → Date → Text |
| Name + Image Top | Photo → Name → Rating → Text → Date |
| Name + Image Bottom | Text → Rating → Photo → Name → Date |

Elements hidden via visibility settings are automatically removed from the order.

---

## Troubleshooting

### No reviews showing

- **API Key mode:** Verify your API Key and Place ID, click Test Fetch
- **OAuth mode:** Verify your connection is active, click Sync Now
- Ensure the correct API is enabled in Google Cloud Console (Places API or Google Business Profile API)
- Check that your business has reviews on Google

### "API Key or Place ID is not configured" error

- Go to **Google Reviews** in the admin sidebar
- Make sure both fields are filled and saved

### Reviews not updating

- **API Key mode:** Reviews are cached for the duration set in settings. Click Clear Cache to force a fresh fetch.
- **OAuth mode:** Click Sync Now for immediate sync, or check the sync interval setting for automatic updates.

### "Invalid JSON response" error

- Your API key may be invalid or restricted
- Check that the Places API (not Maps JavaScript API) is enabled

### OAuth connection failed

- Verify Client ID and Client Secret are correct
- Ensure the redirect URI in Google Cloud Console matches the one shown in plugin settings
- Check that the Google Business Profile API is enabled
- Make sure you selected **Web application** as the OAuth client type

### Styling looks broken

- Ensure your theme is not overriding plugin styles
- Check for JavaScript errors in browser console (F12)
- The plugin loads assets only when a shortcode or widget is present on the page

### Elementor widget shows a placeholder

- Make sure your connection is configured in plugin settings (either mode)
- The placeholder is only visible in the Elementor editor

---

## FAQ

**Does this plugin require Elementor?**
No. The plugin works via shortcode on any WordPress site. The Elementor widget is an optional enhancement for Elementor users.

**What is the difference between API Key and OAuth modes?**
API Key mode uses the Google Places API and is limited to reviews available through that API. OAuth mode connects directly to your Google Business Profile and fetches ALL reviews with no limit. OAuth mode also supports automatic background syncing via WP Cron.

**How often are reviews refreshed?**
- **API Key mode:** Reviews are cached for the duration set in your settings (default: 24 hours). You can change this or clear the cache manually.
- **OAuth mode:** Reviews are synced automatically based on your chosen interval (every 6 hours, daily, weekly, or manual only).

**Can I use both connection modes at the same time?**
No. You select one mode via the radio toggle in settings. Only the active mode is used to fetch reviews for display.

**Is the OAuth connection secure?**
Yes. OAuth tokens are encrypted with AES-256-CBC using your WordPress salt before being stored in the database. All API calls use WordPress core HTTP functions (no curl). Nonces are verified on all admin actions.

**What happens to my reviews if I disconnect my Google account?**
Disconnecting revokes the OAuth token and clears stored credentials, but existing synced reviews are kept in the database and continue to display on your site.

**Can I show reviews from multiple locations?**
- **API Key mode:** Supports one Place ID at a time.
- **OAuth mode:** If you have multiple business locations, use the location selector in settings to choose which one to fetch reviews from.

**Does this plugin slow down my site?**
No. Reviews are cached in the database, so no API calls are made on page load. CSS and JavaScript are only loaded on pages that contain the shortcode or widget.

**What happens if the Google API is down?**
The plugin continues to display cached reviews. If the cache expires and the API is unavailable, reviews will not display until the API is accessible again and the cache is refreshed.

**Can I style the reviews to match my theme?**
Yes. If using Elementor, use the Style tab controls. For shortcode usage, you can add custom CSS targeting the plugin's BEM classes (e.g., `.devsroom-greviews-card`, `.devsroom-greviews-card__name`).

**Is the Google API free?**
Google offers a monthly free tier for the Places API. Check [Google's pricing page](https://developers.google.com/maps/billing/usage-and-billing) for current limits. With caching enabled, you'll only make 1 API call per cache refresh cycle.

---

## Changelog

### 0.0.3

- Added Slider Settings section in Elementor widget with advanced controls
- Added responsive Slides on Display control for slider
- Added responsive Slides on Scroll control for slider
- Added Equal Height toggle for slider cards
- Added Autoplay with Scroll Speed, Pause on Hover, and Pause on Interaction
- Added Infinite Scroll toggle
- Added Transition Duration control
- Added Direction control (LTR/RTL) for slider
- Added Offset Sides (none/both/left/right) with responsive Offset Width for slider
- Added custom icon picker for Previous/Next arrow icons
- Added separate Dot Color controls for normal and active states
- Added responsive Columns control for List layout
- Moved plugin menu from Settings submenu to top-level admin menu

### 0.0.2

- Added OAuth 2.0 connection mode (Connect Google Account)
- Google Business Profile API integration for fetching all reviews
- Automatic background sync via WP Cron (6 hours, daily, weekly, manual)
- Business location selector for multi-location businesses
- AES-256-CBC encrypted token storage
- Mode toggle in settings (API Key vs OAuth)
- Sync Now and Disconnect buttons
- Updated admin settings page with dual-mode UI
- Updated User Guide with OAuth setup instructions

### 0.0.1

- Initial release
- Google Places API integration
- Four layout types (Slider, Grid, Masonry, List)
- Dynamic content ordering
- Elementor widget
- Admin settings page
- Smart caching system
