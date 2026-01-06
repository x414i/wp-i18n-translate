<?php

/**
 * Compatibility layer for the old json-i18n public API.
 *
 * These are intentionally declared in the global namespace so existing theme code,
 * shortcodes, and docs continue to work without edits.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'json_i18n_get_current_language' ) ) {
	function json_i18n_get_current_language(): string {
		return i18n_translate()->locale()->get_current_language_code();
	}
}

if ( ! function_exists( 'json_i18n_get_available_languages' ) ) {
	function json_i18n_get_available_languages(): array {
		$available = i18n_translate()->locale()->get_languages();
		return apply_filters( 'json_i18n_available_languages', $available );
	}
}

if ( ! function_exists( 'json_i18n_current_language_info' ) ) {
	function json_i18n_current_language_info(): array {
		$code = json_i18n_get_current_language();
		$lang = i18n_translate()->locale()->get_language( $code );
		return [
			'code'      => $code,
			'locale'    => $lang['locale'] ?? 'en_US',
			'name'      => $lang['name'] ?? $code,
			'native'    => $lang['native_name'] ?? ( $lang['name'] ?? $code ),
			'is_rtl'    => (bool) ( $lang['rtl'] ?? false ),
			'flag'      => (string) ( $lang['flag'] ?? '' ),
		];
	}
}

if ( ! function_exists( 'json_i18n_set_language' ) ) {
	function json_i18n_set_language( string $language_code ): bool {
		$language_code = sanitize_key( $language_code );
		$old           = json_i18n_get_current_language();
		$ok            = i18n_translate()->locale()->set_language_code( $language_code );
		if ( $ok ) {
			do_action( 'json_i18n_after_language_change', $old, $language_code );
		}
		return $ok;
	}
}

if ( ! function_exists( 'json_i18n_translate' ) ) {
	function json_i18n_translate( string $key, string $default = '', string $domain = 'default' ): string {
		do_action( 'json_i18n_before_translate', $key, $default, $domain );
		$translation = i18n_translate()->strings()->translate( $domain, $key, $default );
		return apply_filters( 'json_i18n_translation', $translation, $key, $default, $domain );
	}
}

if ( ! function_exists( 'json_i18n_get_translations' ) ) {
	function json_i18n_get_translations( string $domain = '' ): array {
		return i18n_translate()->strings()->get_translations_for_js( $domain );
	}
}

if ( ! function_exists( 'json_i18n_language_switcher' ) ) {
	function json_i18n_language_switcher( string $style = 'dropdown', bool $show_flags = true, bool $show_names = true ): void {
		echo i18n_translate()->render()->language_switcher( $style, $show_flags, $show_names );
	}
}

if ( ! function_exists( 'json_i18n_load_textdomain' ) ) {
	/**
	 * Best-effort helper used by some demo themes.
	 *
	 * @param string $domain Text domain.
	 * @param string $path   Absolute path to a directory containing MO files.
	 */
	function json_i18n_load_textdomain( string $domain, string $path ): bool {
		$domain = sanitize_text_field( $domain );
		$path   = untrailingslashit( $path );
		$lang   = json_i18n_get_current_language();
		$info   = i18n_translate()->locale()->get_language( $lang );
		$locale = $info['locale'] ?? 'en_US';
		$mofile = $path . '/' . $domain . '-' . $locale . '.mo';
		if ( file_exists( $mofile ) ) {
			return load_textdomain( $domain, $mofile );
		}
		return false;
	}
}

if ( ! function_exists( '__t' ) ) {
function __t( string $key, string $domain = 'default', array $placeholders = [], string $default = '' ): string {
$translation = json_i18n_translate( $key, $default, $domain );
if ( empty( $placeholders ) ) {
return $translation;
}
foreach ( $placeholders as $placeholder => $value ) {
$translation = str_replace( '{' . sanitize_key( $placeholder ) . '}', (string) $value, $translation );
}
return $translation;
}
}

if ( ! function_exists( '__img' ) ) {
        /**
         * Translate an image by key. Returns <img> tag.
         *
         * @param string $key Translation key holding image URL or Attachment ID.
         * @param string $size Image size (if ID).
         * @param string $class CSS class.
         * @return string Image HTML.
         */
        function __img( string $key, string $size = 'full', string $class = '' ): string {
                $val = json_i18n_translate( $key );
                if ( ! $val ) {
                        return '';
                }
                if ( is_numeric( $val ) && (int) $val > 0 ) {
                        return wp_get_attachment_image( (int) $val, $size, false, [ 'class' => esc_attr( $class ) ] );
                }
                return sprintf( '<img src="%s" class="%s" alt="">', esc_url( $val ), esc_attr( $class ) );
        }
}

if ( ! function_exists( '__lang' ) ) {
        /**
         * Get current language code.
         *
         * @return string Language code (e.g., 'en', 'fr').
         */
        function __lang(): string {
                return json_i18n_get_current_language();
        }
}

if ( ! function_exists( '__switcher' ) ) {
        /**
         * Output language switcher.
         *
         * @param string $style 'dropdown' or 'list'.
         * @param bool $show_flags Show flag emojis.
         * @param bool $show_names Show language names.
         */
        function __switcher( string $style = 'dropdown', bool $show_flags = true, bool $show_names = true ): void {
                json_i18n_language_switcher( $style, $show_flags, $show_names );
        }
}
