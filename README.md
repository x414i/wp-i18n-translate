# WP I18N Translate

[![WordPress Plugin](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/plugins/i18n-translate/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://opensource.org/licenses/GPL-2.0)
[![Version](https://img.shields.io/badge/Version-1.0.0-orange.svg)](https://github.com/yourusername/wp-i18n-translate)

> A modern translation management plugin for WordPress with a card-based admin
> interface.

## Overview ⏩️

This repository provides a robust and scalable translation management plugin for
WordPress. It comes pre-configured with essential features to ensure seamless
internationalization, allowing you to focus on building multilingual sites
instead of managing translations manually.

## Table of Contents 📄

- [Overview ⏩️](#overview-️)
- [Core Features ✨](#core-features-)
- [Getting Started ☣️](#getting-started-️)
- [Usage Guide 📖](#usage-guide-)
- [Contributing 🤝](#contributing-)
- [Future Enhancements 🔮](#future-enhancements-)
- [Getting Help 🆘](#getting-help-)
- [License 📜](#license-)

## Core Features ✨

This plugin includes a suite of powerful tools to streamline your translation
workflow:

- **Card-based Languages Management** 🗂️: Beautiful grid layout with emoji flags
  for easy language overview.
- **Translations Editor** ✏️: Inline editing with search and pagination for
  efficient translation management.
- **String Management** 🔧: Full CRUD operations with bulk add support for
  translation keys.
- **Language Switcher** 🌐: Integrates with admin bar, widgets, menus, and
  shortcodes.
- **Automatic Fallback** 🔄: Falls back to default language if translation is
  missing.
- **Settings Page** ⚙️: Configure default language and auto-detection.
- **REST API** 🔌: Full API for programmatic access to translations.
- **Import/Export** 📥📤: Support for CSV and JSON formats for backup and
  migration.
- **RTL Support** ↔️: Automatic RTL handling for Arabic, Hebrew, and other RTL
  languages.
- **Gutenberg Blocks** 🧱: Custom blocks for language switcher and translated
  text.
- **Comprehensive Usage Guide** 📚: Built-in documentation with code examples
  and tutorials.

## Getting Started ☣️

### Prerequisites

- WordPress 5.0 or higher
- PHP 7.4 or higher

### Installation

1. **Download the Plugin**  
   Download the latest release from
   [GitHub Releases](https://github.com/yourusername/wp-i18n-translate/releases)
   or the
   [WordPress Plugin Directory](https://wordpress.org/plugins/i18n-translate/).

2. **Install via WordPress Admin**

   - Go to **Plugins > Add New** in your WordPress dashboard.
   - Click **Upload Plugin** and select the downloaded ZIP file.
   - Click **Install Now** and then **Activate**.

3. **Install via FTP**

   - Upload the `wp-i18n-translate` folder to `/wp-content/plugins/`.
   - Activate the plugin through the **Plugins** menu in WordPress.

4. **Initial Setup**
   - Navigate to **i18n Translate > Languages** and add your supported
     languages.
   - Go to **Settings** to configure the default language and auto-detection
     options.

Start translating! 🎉

## Usage Guide 📖

### Who Is This For?

- **Editors:** Use blocks or shortcodes to insert translation keys in content.
- **Store Owners:** Use keys for WooCommerce UI text and marketing copy.
- **Developers:** Use PHP helpers to translate template labels and components.

If you want more in‑app guidance, open **i18n Translate → Usage Guide** inside
WordPress.

### Editor Quick Start (Blocks, Classic, Builders)

1. Create a translation key (e.g., `home.hero.title`).
2. Add translations for each language.
3. Insert the key using a block or shortcode.
4. Preview with `?lang=fr` and add a language switcher.

### Which Editor Method Should I Use?

| Method         | Best For                                  | Example                          |
| -------------- | ----------------------------------------- | -------------------------------- |
| Block Editor   | Modern sites and FSE templates            | i18n Text block in header/footer |
| Classic Editor | Legacy posts/pages                        | `[i18n "home.title"]` in content |
| Page Builders  | Elementor/Divi/WPBakery                   | Shortcode widget/module          |
| PHP Helpers    | Theme templates and WooCommerce overrides | `echo __t( 'nav.home' );`        |

### Adding Translations

1. Go to **i18n Translate > Translations** in your admin dashboard.
2. Click **Add New** to create a translation key (e.g., `home.welcome`).
3. Enter translations for each language.
4. Use the key in your content.

### Shortcodes

| Shortcode                             | Purpose             |
| ------------------------------------- | ------------------- |
| `[i18n "key"]`                        | Translate text      |
| `[i18n "key" default="Fallback"]`     | With fallback       |
| `[i18n "key" tag="h1" class="title"]` | With HTML wrapper   |
| `[i18n_image "key"]`                  | Translate image     |
| `[i18n_switcher]`                     | Language switcher   |
| `[i18n_switcher style="list"]`        | List-style switcher |

### eCommerce (WooCommerce)

Use translation keys for **static UI text** in your store (headings, labels, CTA
buttons), and keep **dynamic product data** (price, stock, SKU) managed by
WooCommerce.

**Translate vs Keep in WooCommerce**:

- ✅ Translate: headings, button labels, badge text, help messages, trust copy.
- ⛔ Keep in WooCommerce: product titles, prices, stock, attributes, variations,
  reviews, order data.

**Recommended key groups**:

- `shop.*` (shop filters, sorting, badges)
- `product.*` (product headings, badges, CTA labels)
- `cart.*` (cart labels, empty cart messages)
- `checkout.*` (checkout headings, field labels, messages)

**Examples**:

```php
// In a WooCommerce template (single-product.php, archive-product.php)
echo __t( 'product.add_to_cart', 'Add to cart' );
echo __t( 'product.featured_badge', 'Featured' );
echo __t( 'shop.sort_by', 'Sort by' );
echo __t( 'cart.empty', 'Your cart is empty' );
```

```html
<!-- In product short description or builder content -->
[i18n "product.shipping_note" default="Free shipping over $50"] [i18n
"checkout.secure" default="Secure checkout"]
```

**Page-by-page key map**:

- **Shop (archive-product.php):** `shop.title`, `shop.sort_by`, `shop.filter_by`
- **Product (single-product.php):** `product.add_to_cart`, `product.tabs.*`
- **Cart (cart.php):** `cart.title`, `cart.empty`, `cart.continue_shopping`
- **Checkout (checkout.php):** `checkout.title`, `checkout.secure`,
  `checkout.notice.*`
- **Thank You (thankyou.php):** `checkout.thank_you`, `checkout.order_summary`

**Product images per language**:

```php
// Use i18n image keys for localized product graphics
echo __img( 'product.hero_image', 'large' );
```

### Blogs & Posts

For blogs, use keys for **recurring UI text** (read more, share labels,
headings) and keep post content in the editor as normal. This keeps templates
consistent across all posts.

**Translate vs Keep in Content**:

- ✅ Translate: template labels, buttons, CTAs, archive headings, empty states.
- ⛔ Keep in post content: article body text, quotes, and custom one‑off copy.

**Recommended key groups**:

- `blog.*` (read more, categories, tags, author labels)
- `post.*` (share labels, table-of-contents headings)
- `archive.*` (archive titles, filters, empty states)

**Examples**:

```php
// In archive.php or single.php
echo __t( 'blog.read_more', 'Read more' );
echo __t( 'blog.published_on', 'Published on' );
echo __t( 'archive.no_results', 'No posts found' );
```

```html
<!-- In post content or pattern -->
[i18n "blog.subscribe_cta" default="Subscribe for updates"]
```

**Template key map**:

- **Archive (archive.php):** `archive.title`, `archive.no_results`,
  `blog.read_more`
- **Single post (single.php):** `blog.published_on`, `post.share`, `post.author`
- **Pagination:** `archive.prev`, `archive.next`
- **Author box:** `post.about_author`, `post.author_posts`

### PHP Helper Functions

```php
// Translate text
echo __t( 'nav.home' );
echo __t( 'nav.home', 'Default Text', 'theme' );

// Translate image
echo __img( 'hero.image', 'large' );
echo __img( 'hero.image', 'full', ['class' => 'hero-img'] );

// Get current language
$lang = __lang();

// Output switcher
__switcher( 'dropdown' );
__switcher( 'list', ['show_flags' => true] );
```

### Hooks & Filters

```php
// Modify translation output
add_filter( 'json_i18n_translation', function( $translation, $key, $default, $domain ) {
    return $translation;
}, 10, 4 );

// Filter available languages
add_filter( 'json_i18n_available_languages', function( $languages ) {
    return $languages;
} );

// Customize language switcher
add_filter( 'json_i18n_language_switcher_args', function( $args ) {
    return $args;
} );

// Action after language change
add_action( 'json_i18n_after_language_change', function( $old, $new ) {
    // Handle language switch
}, 10, 2 );
```

### Menu Integration

Add a **Custom Link** with URL `#i18n-switcher` to any menu. Works in Classic
and Block Themes.

### Configuration

Go to **i18n Translate → Settings** to configure:

- **Default Language**: Fallback when no translation exists
- **Auto Detect**: Detect language from browser

The plugin includes a comprehensive **Usage Guide** (i18n Translate → Usage
Guide) with tutorials, integrations, and troubleshooting.

## Contributing 🤝

Contributions are welcome! If you have an improvement or a new feature, please
follow these steps:

1. Fork the repository.
2. Create a new branch for your feature or fix.
3. Add your changes and commit them with a conventional commit message.
4. Submit a pull request with a clear description of your changes.

✨ Contributors

Made with [contrib.rocks](https://contrib.rocks).

## Future Enhancements 🔮

We have a few ideas for future enhancements. Feel free to contribute or suggest
new ones!

- **Advanced Import/Export**: Support for more formats like XLIFF or PO files.
- **Machine Translation Integration**: Integrate with services like Google
  Translate or DeepL for automatic translations.
- **Multisite Support**: Enhanced features for WordPress Multisite networks.
- **Performance Optimizations**: Caching layers and lazy loading for better
  performance on large sites.
- **Theme Integration**: Deeper integration with popular themes and page
  builders.
- **Analytics Dashboard**: Track translation usage and missing translations.
- **CLI Tools**: Command-line interface for bulk operations and migrations.

## Getting Help 🆘

If you encounter any issues or have questions, please:

- Check the built-in **Usage Guide** in the plugin.
- Open an issue on the
  [GitHub repository](https://github.com/yourusername/wp-i18n-translate/issues).
- Join the discussion in the
  [WordPress support forum](https://wordpress.org/support/plugin/i18n-translate/).

## License 📜

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE)
file for details.

---
