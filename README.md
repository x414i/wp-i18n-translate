# i18n Translate

A modern translation management plugin for WordPress with a card-based admin interface.

## Features

- **Card-based Languages Management** - Beautiful grid layout with emoji flags
- **Translations Editor** - Inline editing with search and pagination
- **String Management** - Full CRUD with bulk add support
- **Language Switcher** - Admin bar, widgets, menus, shortcodes
- **Automatic Fallback** - Falls back to default language if translation missing
- **Settings Page** - Configure default language and auto-detection
- **REST API** - Full API for programmatic access
- **Import/Export** - CSV and JSON format for backup
- **RTL Support** - Automatic RTL handling for Arabic, Hebrew, etc.
- **Comprehensive Usage Guide** - Built-in documentation with code examples

## Quick Start

1. Create a key in **Translations** (e.g., `home.title`)
2. Add translations for each language
3. Use `[i18n "home.title"]` anywhere

## Shortcodes

| Shortcode | Purpose |
|-----------|---------|
| `[i18n "key"]` | Translate text |
| `[i18n "key" default="Fallback"]` | With fallback |
| `[i18n "key" tag="h1" class="title"]` | With HTML wrapper |
| `[i18n_image "key"]` | Translate image |
| `[i18n_switcher]` | Language switcher |
| `[i18n_switcher style="list"]` | List-style switcher |

## PHP Helper Functions

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

## Hooks & Filters

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

## Menu Integration

Add a **Custom Link** with URL `#i18n-switcher` to any menu. Works in Classic and Block Themes.

## Configuration

Go to **i18n Translate → Settings** to configure:
- **Default Language**: Fallback when no translation exists
- **Auto Detect**: Detect language from browser

## Usage Guide

The plugin includes a comprehensive **Usage Guide** (i18n Translate → Usage Guide) with:
- Getting Started tutorial
- Block Editor & Classic Editor integration
- Page Builders support (Elementor, Beaver Builder, Divi)
- Shortcode reference with examples
- Images & Media translation
- PHP developer reference
- Import/Export documentation
- RTL language support
- Migration guides (WPML, Polylang, Loco Translate)
- Troubleshooting FAQ

## Database Tables

- `wp_i18n_languages` - Language definitions
- `wp_i18n_strings` - Translation keys
- `wp_i18n_translations` - Translations per language
- `wp_i18n_media` - Media translations

