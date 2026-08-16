=== WP Multilingual ===
Contributors: antigravity
Tags: multilingual, translation, languages, polylang, wpml, switcher, hreflang
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A production-ready, lightweight, and independent WordPress multilingual translation and language management plugin.

== Description ==

**WP Multilingual** is a modern, high-performance multilingual plugin for WordPress built from scratch. It allows administrators to create and manage multilingual versions of Posts, Pages, Custom Post Types, Categories, Tags, and Custom Taxonomies with full slug independence and URL routing.

### Key Features:
* **True Content Separation**: Uses standard WordPress native posts, postmeta, and taxonomies without bloating the database schema.
* **Translation Groups**: Links translations of the same content seamlessly across languages.
* **Language-Specific Slugs**: Full independence for post slugs across languages (e.g., `/en/about-us/` and `/ar/من-نحن/`).
* **Configurable URL Structures**: Mode A (`/en/slug/`) or Mode B (hide default language prefix).
* **Automated SEO & hreflang**: Outputs `<link rel="alternate" hreflang="..." />` and canonical tags with zero configuration.
* **Selective Synchronization**: Sync featured images, page templates, custom fields, and taxonomies while protecting translated text.
* **REST API & Gutenberg**: Native block editor support, REST endpoints (`/wp-json/wpm/v1/`), and REST field exposures.
* **Developer First**: Comprehensive PHP procedural and OOP API (`wpm_get_languages()`, `wpm_get_translation()`, hooks, and filters).

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to **Settings → Languages** to add your site's languages.
4. Go to **Settings → Multilingual Settings** to configure URL modes and translatable types.

== Frequently Asked Questions ==

= Does it support RTL languages like Arabic or Persian? =
Yes! Languages can be configured as LTR or RTL, and the plugin sets appropriate text directions for switchers and editor screens.

= Does it conflict with Yoast SEO or Rank Math? =
No. It hooks cleanly into `wpseo_canonical`, `rank_math/frontend/canonical`, and provides standard `hreflang` tags.

== Changelog ==

= 1.0.0 =
* Initial production release.
