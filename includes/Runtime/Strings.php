<?php

namespace I18nTranslate\Runtime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Strings {
	public function translate( string $domain, string $key, string $default = '' ): string {
		$domain = $domain !== '' ? sanitize_text_field( $domain ) : 'default';
		$key    = sanitize_text_field( $key );

		if ( $key === '' ) {
			return $default !== '' ? $default : '';
		}

		$lang = json_i18n_get_current_language();

		$cache_key = "t:{$lang}:{$domain}:{$key}";
		$cached    = wp_cache_get( $cache_key, 'i18n_translate' );
		if ( $cached !== false ) {
			return (string) $cached;
		}

		global $wpdb;
		$strings_table = $wpdb->prefix . 'i18n_strings';
		$tr_table      = $wpdb->prefix . 'i18n_translations';

		$string_id = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM {$strings_table} WHERE domain = %s AND string_key = %s",
			$domain,
			$key
		) );

		if ( $string_id === 0 ) {
			$wpdb->insert( $strings_table, [
				'domain'       => $domain,
				'string_key'   => $key,
				'default_text' => $default,
			] );
			$string_id = (int) $wpdb->insert_id;
		}

		$translation = $wpdb->get_var( $wpdb->prepare(
			"SELECT translation_text FROM {$tr_table} WHERE string_id = %d AND lang_code = %s",
			$string_id,
			$lang
		) );

		if ( is_string( $translation ) && $translation !== '' ) {
			wp_cache_set( $cache_key, $translation, 'i18n_translate', HOUR_IN_SECONDS );
			return $translation;
		}

		$result = $default !== '' ? $default : $key;
		wp_cache_set( $cache_key, $result, 'i18n_translate', HOUR_IN_SECONDS );
		return $result;
	}

	public function get_translations_for_js( string $domain = '' ): array {
		$lang = json_i18n_get_current_language();

		$cache_key = 'js:' . $lang . ':' . ( $domain !== '' ? $domain : '*' );
		$cached    = wp_cache_get( $cache_key, 'i18n_translate' );
		if ( $cached !== false ) {
			return (array) $cached;
		}

		global $wpdb;
		$strings_table = $wpdb->prefix . 'i18n_strings';
		$tr_table      = $wpdb->prefix . 'i18n_translations';

		$params = [ $lang ];
		$where  = '';
		if ( $domain !== '' ) {
			$where   = ' AND s.domain = %s';
			$params[] = $domain;
		}

		$query = $wpdb->prepare(
			"SELECT s.domain, s.string_key, COALESCE(t.translation_text, s.default_text, s.string_key) AS value
			 FROM {$strings_table} s
			 LEFT JOIN {$tr_table} t ON t.string_id = s.id AND t.lang_code = %s
			 WHERE 1=1 {$where}",
			...$params
		);

		$rows = $wpdb->get_results( $query, ARRAY_A );
		$out  = [];
		foreach ( $rows as $row ) {
			$dom = $row['domain'] ?? 'default';
			if ( ! isset( $out[ $dom ] ) ) {
				$out[ $dom ] = [];
			}
			$out[ $dom ][ $row['string_key'] ] = $row['value'];
		}

		wp_cache_set( $cache_key, $out, 'i18n_translate', HOUR_IN_SECONDS );
		return $out;
	}

	public function translate_field( string $object_type, int $object_id, string $field_key, string $default = '', string $lang = '' ): string {
		$object_type = sanitize_key( $object_type );
		$field_key   = sanitize_text_field( $field_key );
		if ( $object_type === '' || $object_id <= 0 || $field_key === '' ) {
			return $default;
		}

		$lang = $lang !== '' ? sanitize_key( $lang ) : json_i18n_get_current_language();
		$cache_key = "f:{$lang}:{$object_type}:{$object_id}:{$field_key}";
		$cached    = wp_cache_get( $cache_key, 'i18n_translate' );
		if ( $cached !== false ) {
			return (string) $cached;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'i18n_field_translations';
		$translation = $wpdb->get_var( $wpdb->prepare(
			"SELECT translation_text FROM {$table} WHERE object_type = %s AND object_id = %d AND field_key = %s AND lang_code = %s",
			$object_type,
			$object_id,
			$field_key,
			$lang
		) );

		if ( is_string( $translation ) && $translation !== '' ) {
			wp_cache_set( $cache_key, $translation, 'i18n_translate', HOUR_IN_SECONDS );
			return $translation;
		}

		wp_cache_set( $cache_key, $default, 'i18n_translate', HOUR_IN_SECONDS );
		return $default;
	}

	public function get_field_translations( string $object_type, int $object_id, array $field_keys = [], string $lang = '' ): array {
		$object_type = sanitize_key( $object_type );
		if ( $object_type === '' || $object_id <= 0 ) {
			return [];
		}

		$lang = $lang !== '' ? sanitize_key( $lang ) : json_i18n_get_current_language();
		global $wpdb;
		$table = $wpdb->prefix . 'i18n_field_translations';

		$params = [ $object_type, $object_id, $lang ];
		$where  = '';
		if ( ! empty( $field_keys ) ) {
			$field_keys = array_values( array_filter( array_map( 'sanitize_text_field', $field_keys ) ) );
			if ( empty( $field_keys ) ) {
				return [];
			}
			$placeholders = implode( ',', array_fill( 0, count( $field_keys ), '%s' ) );
			$where = " AND field_key IN ({$placeholders})";
			$params = array_merge( $params, $field_keys );
		}

		$query = $wpdb->prepare(
			"SELECT field_key, translation_text FROM {$table} WHERE object_type = %s AND object_id = %d AND lang_code = %s{$where}",
			...$params
		);
		$rows = $wpdb->get_results( $query, ARRAY_A );
		$out  = [];
		foreach ( $rows as $row ) {
			if ( isset( $row['field_key'] ) ) {
				$out[ $row['field_key'] ] = (string) ( $row['translation_text'] ?? '' );
			}
		}
		return $out;
	}

	public function save_field_translation( string $object_type, int $object_id, string $field_key, string $lang, string $translation ): bool {
		$object_type = sanitize_key( $object_type );
		$field_key   = sanitize_text_field( $field_key );
		$lang        = sanitize_key( $lang );
		if ( $object_type === '' || $object_id <= 0 || $field_key === '' || $lang === '' ) {
			return false;
		}
		if ( ! i18n_translate()->locale()->is_valid_language_code( $lang ) ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'i18n_field_translations';
		$exists = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE object_type = %s AND object_id = %d AND field_key = %s AND lang_code = %s",
			$object_type,
			$object_id,
			$field_key,
			$lang
		) );

		if ( $exists > 0 ) {
			$wpdb->update( $table, [
				'translation_text' => $translation,
				'updated_at'       => current_time( 'mysql' ),
			], [
				'object_type' => $object_type,
				'object_id'   => $object_id,
				'field_key'   => $field_key,
				'lang_code'   => $lang,
			] );
		} else {
			$wpdb->insert( $table, [
				'object_type'      => $object_type,
				'object_id'        => $object_id,
				'field_key'        => $field_key,
				'lang_code'        => $lang,
				'translation_text' => $translation,
				'updated_at'       => current_time( 'mysql' ),
			] );
		}

		$cache_key = "f:{$lang}:{$object_type}:{$object_id}:{$field_key}";
		wp_cache_delete( $cache_key, 'i18n_translate' );
		return true;
	}

	public function delete_field_translation( string $object_type, int $object_id, string $field_key, string $lang ): bool {
		$object_type = sanitize_key( $object_type );
		$field_key   = sanitize_text_field( $field_key );
		$lang        = sanitize_key( $lang );
		if ( $object_type === '' || $object_id <= 0 || $field_key === '' || $lang === '' ) {
			return false;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'i18n_field_translations';
		$wpdb->delete( $table, [
			'object_type' => $object_type,
			'object_id'   => $object_id,
			'field_key'   => $field_key,
			'lang_code'   => $lang,
		] );

		$cache_key = "f:{$lang}:{$object_type}:{$object_id}:{$field_key}";
		wp_cache_delete( $cache_key, 'i18n_translate' );
		return true;
	}
}
