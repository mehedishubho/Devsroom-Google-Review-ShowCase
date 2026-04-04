# Devsroom Google Review ShowCase

> Fetch and display verified Google My Business reviews on your WordPress site via shortcode and Elementor widget.

## Features

- Google Places API integration with smart caching
- Four layout types: **Slider**, **Grid**, **Masonry**, **List**
- Dynamic content ordering — reorder photo, name, rating, text, date
- Elementor widget with full Content and Style controls
- Shortcode support with flexible attributes
- Conditional asset loading for optimal performance
- Admin settings with Test Fetch and Clear Cache

---

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate through the **Plugins** menu
3. Go to **Settings → Devsroom Google Reviews**
4. Enter your Google API Key and Place ID
5. Use the shortcode or Elementor widget to display reviews

---

## Getting Your Google API Credentials

### Step 1: Create a Google API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select an existing one)
3. Navigate to **APIs & Services → Library**
4. Search for **Places API** and click **Enable**
5. Go to **APIs & Services → Credentials**
6. Click **Create Credentials → API Key**
7. Copy the generated API key
8. (Recommended) Restrict the key to **Places API** only

### Step 2: Find Your Place ID

1. Go to the [Google Place ID Finder](https://developers.google.com/maps/documentation/places/web-service/place-id)
2. Enter your business name in the search box
3. Select your business from the results
4. Copy the **Place ID** (format: `ChIJ...`)

---

## Plugin Settings

Navigate to **Settings → Devsroom Google Reviews** in your WordPress dashboard.

### Configuration Fields

| Field | Description |
|-------|-------------|
| **Google API Key** | Your Google Cloud API key with Places API enabled |
| **Google Place ID** | Your business Place ID from Google |
| **Cache Duration** | How long to cache reviews (in hours). Default: 24 hours |

### Action Buttons

- **Test Fetch** — Tests your API connection and shows how many reviews were found
- **Clear Cache** — Forces a fresh fetch on the next page load

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

This will display reviews using default settings (grid layout, 5 reviews, minimum 1-star rating).

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

### Content Tab

#### Layout Section

- **Layout Type** — Choose Slider, Grid, Masonry, or List
- **Columns** — Number of columns for Grid and Masonry (1–4)

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

- Arrow color
- Dot color
- Autoplay on/off
- Autoplay speed (milliseconds)

---

## Layout Types

### Grid

Displays reviews in a responsive CSS grid. Cards have equal width with automatic height. Adjust columns from the settings.

### Slider

A carousel powered by Swiper.js with:

- Previous/Next navigation arrows
- Pagination dots
- Optional autoplay
- Responsive breakpoints (1 column on mobile, 2 on tablet, 3 on desktop)

### Masonry

A Pinterest-style layout where cards have varying heights based on their content. Longer reviews create taller cards while shorter reviews stay compact.

### List

A vertical list where each review card displays horizontally — reviewer photo on the left, content on the right. Responsive: stacks vertically on mobile.

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

- Verify your **API Key** and **Place ID** in Settings
- Click **Test Fetch** to check the connection
- Ensure the **Places API** is enabled in Google Cloud Console
- Check that your business has reviews on Google

### "API Key or Place ID is not configured" error

- Go to **Settings → Devsroom Google Reviews**
- Make sure both fields are filled and saved

### Reviews not updating

- Reviews are cached for the duration set in settings (default: 24 hours)
- Click **Clear Cache** in settings to force a fresh fetch
- Reduce the cache duration if you need more frequent updates

### "Invalid JSON response" error

- Your API key may be invalid or restricted
- Check that the Places API (not Maps JavaScript API) is enabled

### Styling looks broken

- Ensure your theme is not overriding plugin styles
- Check for JavaScript errors in browser console (F12)
- The plugin loads assets only when a shortcode or widget is present on the page

### Elementor widget shows a placeholder

- Make sure your API Key and Place ID are configured in plugin settings
- The placeholder is only visible in the Elementor editor

---

## FAQ

**Does this plugin require Elementor?**
No. The plugin works via shortcode on any WordPress site. The Elementor widget is an optional enhancement for Elementor users.

**How often are reviews refreshed?**
Reviews are cached for the duration set in your settings (default: 24 hours). You can change this or clear the cache manually.

**Can I show reviews from multiple locations?**
The plugin currently supports one Place ID. To show reviews from multiple locations, you would need to change the Place ID in settings.

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

### 0.0.1

- Initial release
- Google Places API integration
- Four layout types (Slider, Grid, Masonry, List)
- Dynamic content ordering
- Elementor widget
- Admin settings page
- Smart caching system
