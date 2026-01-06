<?php

namespace I18nTranslate\I18n;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Locale {
    private const COOKIE = 'i18n_translate_lang';
    private ?string $current_language_code = null;

    public function register(): void {
        add_filter( 'pre_determine_locale', [ $this, 'filter_pre_determine_locale' ], 1 );
        add_action( 'init', [ $this, 'maybe_switch_to_locale' ], 1 );
        add_filter( 'language_attributes', [ $this, 'filter_language_attributes' ], 10, 2 );
    }

    public function get_current_language_code(): string {
        // 1. Return if already set
        if ( $this->current_language_code !== null ) {
            return $this->current_language_code;
        }

        // 0. Check URL parameter
        $requested = $this->get_requested_language_code();
        if ( $requested !== null ) {
             $this->current_language_code = $requested;
             return $requested;
        }

        // 2. Check cookie
        if ( isset( $_COOKIE[ self::COOKIE ] ) ) {
            $cookie = sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) );
            if ( $this->is_valid_language_code( $cookie ) ) {
                $this->current_language_code = $cookie;
                return $cookie;
            }
        }

        // 3. Check default language option
        $default_lang = get_option( 'i18n_translate_default_language', '' );
        if ( $default_lang !== '' && $this->is_valid_language_code( $default_lang ) ) {
            $this->current_language_code = $default_lang;
            return $default_lang;
        }

        // 4. Auto-detect from browser (if enabled and no cookie set)
        if ( ! isset( $_COOKIE[ self::COOKIE ] ) && get_option( 'i18n_translate_auto_detect', false ) ) {
            $detected = $this->auto_detect_language();
            if ( $detected !== null ) {
                $this->set_language_code( $detected );
                return $detected;
            }
        }

        // 5. Use first enabled language
        $languages = $this->get_languages();
        if ( ! empty( $languages ) ) {
            $first = reset( $languages );
            $this->current_language_code = $first['code'];
            return $first['code'];
        }

        // 6. Final fallback
        $this->current_language_code = 'en';
        return 'en';
    }

    public function set_language_code( string $code ): bool {
        $code = sanitize_key( $code );
        if ( ! $this->is_valid_language_code( $code ) ) {
            return false;
        }
        
        $this->current_language_code = $code;

        setcookie( self::COOKIE, $code, [
            'expires'  => time() + DAY_IN_SECONDS * 30,
            'path'     => COOKIEPATH ?: '/',
            'domain'   => COOKIE_DOMAIN,
            'samesite' => 'Lax',
            'secure'   => is_ssl(),
            'httponly' => false,
        ] );

        $_COOKIE[ self::COOKIE ] = $code;
        return true;
    }

    public function get_current_locale(): string {
        $lang = $this->get_current_language_code();
        $entry = $this->get_language( $lang );
        return $entry['locale'] ?? 'en_US';
    }

    public function get_languages(): array {
        global $wpdb;
        $table = $wpdb->prefix . 'i18n_languages';

        // Check if table exists before querying to avoid fatal errors during activation/early load if not installed
        // However, usually installer runs first. Assuming table exists or empty return.
        // Actually, simple valid query:
        
        $rows = $wpdb->get_results( "SELECT code, locale, name, native_name, rtl, flag, enabled, sort_order FROM {$table} WHERE enabled = 1 ORDER BY sort_order ASC", ARRAY_A );
        
        if ( empty( $rows ) ) {
            return [];
        }

        $languages = [];
        foreach ( $rows as $row ) {
            $languages[ $row['code'] ] = [
                'code'        => $row['code'],
                'locale'      => $row['locale'],
                'name'        => $row['name'],
                'native_name' => $row['native_name'],
                'rtl'         => (bool) $row['rtl'],
                'flag'        => (string) ( $row['flag'] ?? '' ),
            ];
        }

        return $languages;
    }

    public function get_language( string $code ): ?array {
        $code = sanitize_key( $code );
        $languages = $this->get_languages();
        return $languages[ $code ] ?? null;
    }

    public function is_rtl(): bool {
        $lang = $this->get_current_language_code();
        $entry = $this->get_language( $lang );
        return (bool) ( $entry['rtl'] ?? false );
    }

    public function filter_pre_determine_locale( $locale ) {
        return $this->get_current_locale();
    }

    public function maybe_switch_to_locale(): void {
        $current = $this->get_current_locale();
        $target = determine_locale();
        if ( $target && $target !== $current ) {
            switch_to_locale( $target );
        }
    }

    public function filter_language_attributes( string $output, string $doctype ): string {
        if ( $this->is_rtl() ) {
            if ( ! str_contains( $output, 'dir=' ) ) {
                $output .= ' dir="rtl"';
            }
        }
        return $output;
    }

    /**
     * Auto-detect language from HTTP_ACCEPT_LANGUAGE header.
     *
     * @return string|null Detected language code or null if no match.
     */
    private function auto_detect_language(): ?string {
        if ( ! isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
            return null;
        }
        $accept_language = sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) );
        $languages       = $this->get_languages();
        if ( empty( $languages ) ) {
            return null;
        }
        // Parse Accept-Language header (e.g., "en-US,en;q=0.9,es;q=0.8")
        $browser_langs = [];
        $parts = explode( ',', $accept_language );
        foreach ( $parts as $part ) {
            $part = trim( $part );
            if ( strpos( $part, ';' ) !== false ) {
                list( $lang, $quality ) = explode( ';', $part, 2 );
                $quality = (float) str_replace( 'q=', '', $quality );
            } else {
                $lang = $part;
                $quality = 1.0;
            }
            // Extract base language code (e.g., "en" from "en-US")
            $lang = strtolower( trim( $lang ) );
            if ( strpos( $lang, '-' ) !== false ) {
                $lang = substr( $lang, 0, strpos( $lang, '-' ) );
            }
            $browser_langs[ $lang ] = $quality;
        }
        // Sort by quality (highest first)
        arsort( $browser_langs );
        // Match against enabled languages
        foreach ( $browser_langs as $browser_lang => $quality ) {
            if ( isset( $languages[ $browser_lang ] ) ) {
                return $browser_lang;
            }
        }
        return null;
    }

    private function get_requested_language_code(): ?string {
        if ( isset( $_GET['lang'] ) ) {
            $code = sanitize_key( wp_unslash( $_GET['lang'] ) );
            if ( $this->is_valid_language_code( $code ) ) {
                $this->set_language_code( $code );
                return $code;
            }
        }
        return null;
    }

    public function is_valid_language_code( string $code ): bool {
        $languages = $this->get_languages();
        return isset( $languages[ $code ] );
    }
}
