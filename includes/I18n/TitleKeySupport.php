<?php

namespace I18nTranslate\I18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optional convention for translating titles via JSON-key translations.
 *
 * Title formats supported:
 * - i18n:<key>                (domain: default)
 * - i18n:<domain>:<key>       (domain: <domain>)
 * - {{i18n:<domain>:<key>}}   (wrapped, useful for some editors)
 */
final class TitleKeySupport {
	public function register(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		add_filter( 'the_title', [ $this, 'filter_title' ], 10, 2 );
		add_filter( 'single_post_title', [ $this, 'filter_single_post_title' ], 10, 2 );
	}

	public function filter_title( $title, $post_id = 0 ) {
		if ( ! is_string( $title ) || $title === '' ) {
			return $title;
		}

		return $this->maybe_translate( $title );
	}

	public function filter_single_post_title( $title, $post = 0 ) {
		if ( ! is_string( $title ) || $title === '' ) {
			return $title;
		}

		return $this->maybe_translate( $title );
	}

	private function maybe_translate( string $title ): string {
		$title = trim( $title );

		// Optional wrapper: {{i18n:...}}
		if ( str_starts_with( $title, '{{' ) && str_ends_with( $title, '}}' ) ) {
			$title = trim( substr( $title, 2, -2 ) );
		}

		if ( ! str_starts_with( $title, 'i18n:' ) ) {
			return $title;
		}

		$payload = trim( substr( $title, 5 ) );
		if ( $payload === '' ) {
			return $title;
		}

		$domain = 'default';
		$key    = $payload;

		// i18n:<domain>:<key>
		if ( str_contains( $payload, ':' ) ) {
			[ $maybe_domain, $maybe_key ] = array_pad( explode( ':', $payload, 2 ), 2, '' );
			$maybe_domain = trim( (string) $maybe_domain );
			$maybe_key    = trim( (string) $maybe_key );
			if ( $maybe_domain !== '' && $maybe_key !== '' ) {
				$domain = sanitize_text_field( $maybe_domain );
				$key    = sanitize_text_field( $maybe_key );
			}
		}

		$key = trim( (string) $key );
		if ( $key === '' ) {
			return $title;
		}

		$fallback = $this->fallback_from_key( $key );

		if ( function_exists( 'json_i18n_translate' ) ) {
			return (string) json_i18n_translate( $key, $fallback, $domain );
		}

		return $fallback;
	}

	private function fallback_from_key( string $key ): string {
		// Reasonable display fallback if no translation exists.
		$fallback = str_replace( [ '.', '_', '-' ], ' ', $key );
		$fallback = preg_replace( '/\s+/', ' ', $fallback ) ?? $fallback;
		return trim( $fallback );
	}
}
