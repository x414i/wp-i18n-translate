<?php

namespace I18nTranslate\Http;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Endpoints {
	public function register(): void {
		add_action( 'rest_api_init', [ $this, 'register_rest' ] );
		add_action( 'wp_ajax_change_language', [ $this, 'ajax_change_language' ] );
		add_action( 'wp_ajax_nopriv_change_language', [ $this, 'ajax_change_language' ] );
		add_action( 'wp_ajax_search_suggestions', [ $this, 'ajax_search_suggestions' ] );
		add_action( 'wp_ajax_nopriv_search_suggestions', [ $this, 'ajax_search_suggestions' ] );
		
		// Admin AJAX endpoints for inline editing
		add_action( 'wp_ajax_i18n_save_translation', [ $this, 'ajax_save_translation' ] );
		add_action( 'wp_ajax_i18n_save_string', [ $this, 'ajax_save_string' ] );
		add_action( 'wp_ajax_i18n_delete_string', [ $this, 'ajax_delete_string' ] );
		add_action( 'wp_ajax_i18n_toggle_language', [ $this, 'ajax_toggle_language' ] );
		add_action( 'wp_ajax_i18n_save_language', [ $this, 'ajax_save_language' ] );
		add_action( 'wp_ajax_i18n_delete_language', [ $this, 'ajax_delete_language' ] );
	}

	public function register_rest(): void {
		register_rest_route( 'json-i18n/v1', '/change-language', [
			'methods'             => 'POST',
			'permission_callback' => '__return_true',
			'callback'            => [ $this, 'rest_change_language' ],
			'args'                => [
				'language' => [ 'type' => 'string', 'required' => true ],
				'nonce'    => [ 'type' => 'string', 'required' => false ],
			],
		] );

		register_rest_route( 'json-i18n/v1', '/translations', [
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => [ $this, 'rest_translations' ],
			'args'                => [
				'domain' => [ 'type' => 'string', 'required' => false ],
			],
		] );

		register_rest_route( 'json-i18n/v1', '/field-translations', [
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => [ $this, 'rest_field_translations' ],
			'args'                => [
				'object_type' => [ 'type' => 'string', 'required' => true ],
				'object_id'   => [ 'type' => 'integer', 'required' => true ],
				'fields'      => [ 'type' => 'string', 'required' => false ],
				'lang'        => [ 'type' => 'string', 'required' => false ],
			],
		] );

		register_rest_route( 'json-i18n/v1', '/field-translations', [
			'methods'             => 'POST',
			'permission_callback' => [ $this, 'rest_can_translate' ],
			'callback'            => [ $this, 'rest_save_field_translation' ],
			'args'                => [
				'object_type' => [ 'type' => 'string', 'required' => true ],
				'object_id'   => [ 'type' => 'integer', 'required' => true ],
				'field_key'   => [ 'type' => 'string', 'required' => true ],
				'lang'        => [ 'type' => 'string', 'required' => true ],
				'translation' => [ 'type' => 'string', 'required' => true ],
			],
		] );

		register_rest_route( 'json-i18n/v1', '/field-translations', [
			'methods'             => 'DELETE',
			'permission_callback' => [ $this, 'rest_can_translate' ],
			'callback'            => [ $this, 'rest_delete_field_translation' ],
			'args'                => [
				'object_type' => [ 'type' => 'string', 'required' => true ],
				'object_id'   => [ 'type' => 'integer', 'required' => true ],
				'field_key'   => [ 'type' => 'string', 'required' => true ],
				'lang'        => [ 'type' => 'string', 'required' => true ],
			],
		] );
	}

	public function rest_change_language( \WP_REST_Request $request ) {
		$lang = sanitize_key( (string) $request->get_param( 'language' ) );
		$ok   = json_i18n_set_language( $lang );
		if ( ! $ok ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Invalid language' ], 400 );
		}
		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [ 'message' => 'Language changed successfully', 'new_language' => $lang ],
		] );
	}

	public function rest_translations( \WP_REST_Request $request ) {
		$domain = (string) $request->get_param( 'domain' );
		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'current_lang' => json_i18n_get_current_language(),
				'languages'    => json_i18n_get_available_languages(),
				'translations' => json_i18n_get_translations( $domain ),
			],
		] );
	}

	public function rest_field_translations( \WP_REST_Request $request ) {
		$object_type = sanitize_key( (string) $request->get_param( 'object_type' ) );
		$object_id   = (int) $request->get_param( 'object_id' );
		$lang        = sanitize_key( (string) $request->get_param( 'lang' ) );
		$fields      = $request->get_param( 'fields' );

		$field_keys = [];
		if ( is_string( $fields ) && $fields !== '' ) {
			$field_keys = array_map( 'trim', explode( ',', $fields ) );
		} elseif ( is_array( $fields ) ) {
			$field_keys = $fields;
		}

		$translations = i18n_translate()->strings()->get_field_translations( $object_type, $object_id, $field_keys, $lang );
		if ( $lang === '' ) {
			$lang = json_i18n_get_current_language();
		}

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'object_type'  => $object_type,
				'object_id'    => $object_id,
				'lang'         => $lang,
				'translations' => $translations,
			],
		] );
	}

	public function rest_save_field_translation( \WP_REST_Request $request ) {
		$object_type = sanitize_key( (string) $request->get_param( 'object_type' ) );
		$object_id   = (int) $request->get_param( 'object_id' );
		$field_key   = sanitize_text_field( (string) $request->get_param( 'field_key' ) );
		$lang        = sanitize_key( (string) $request->get_param( 'lang' ) );
		$translation = (string) $request->get_param( 'translation' );

		$ok = i18n_translate()->strings()->save_field_translation( $object_type, $object_id, $field_key, $lang, $translation );
		if ( ! $ok ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Invalid parameters' ], 400 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'object_type' => $object_type,
				'object_id'   => $object_id,
				'field_key'   => $field_key,
				'lang'        => $lang,
			],
		] );
	}

	public function rest_delete_field_translation( \WP_REST_Request $request ) {
		$object_type = sanitize_key( (string) $request->get_param( 'object_type' ) );
		$object_id   = (int) $request->get_param( 'object_id' );
		$field_key   = sanitize_text_field( (string) $request->get_param( 'field_key' ) );
		$lang        = sanitize_key( (string) $request->get_param( 'lang' ) );

		$ok = i18n_translate()->strings()->delete_field_translation( $object_type, $object_id, $field_key, $lang );
		if ( ! $ok ) {
			return new \WP_REST_Response( [ 'success' => false, 'message' => 'Invalid parameters' ], 400 );
		}

		return new \WP_REST_Response( [
			'success' => true,
			'data'    => [
				'object_type' => $object_type,
				'object_id'   => $object_id,
				'field_key'   => $field_key,
				'lang'        => $lang,
			],
		] );
	}

	public function rest_can_translate(): bool {
		return current_user_can( 'i18n_translate_translate' );
	}

	public function ajax_change_language(): void {
		check_ajax_referer( 'wp_template_nonce', 'nonce' );

		$lang = isset( $_POST['language'] ) ? sanitize_key( wp_unslash( $_POST['language'] ) ) : '';
		if ( $lang === '' || ! json_i18n_set_language( $lang ) ) {
			wp_send_json_error( [ 'message' => 'Invalid language' ], 400 );
		}

		wp_send_json_success( [ 'new_language' => $lang ] );
	}

	public function ajax_search_suggestions(): void {
		check_ajax_referer( 'wp_template_nonce', 'nonce' );

		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		if ( $query === '' ) {
			wp_send_json_success( [ 'suggestions' => [] ] );
		}

		$results = new \WP_Query( [
			's'              => $query,
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'no_found_rows'  => true,
		] );

		$suggestions = [];
		foreach ( $results->posts as $post ) {
			$suggestions[] = [
				'id'    => (int) $post->ID,
				'title' => get_the_title( $post ),
				'url'   => get_permalink( $post ),
			];
		}

		wp_send_json_success( [ 'suggestions' => $suggestions ] );
	}

	public function ajax_save_translation(): void {
		check_ajax_referer( 'i18n_translate_nonce', 'nonce' );

		if ( ! current_user_can( 'i18n_translate_translate' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
		}

		$string_id = isset( $_POST['string_id'] ) ? (int) $_POST['string_id'] : 0;
		$lang_code = isset( $_POST['lang_code'] ) ? sanitize_key( wp_unslash( $_POST['lang_code'] ) ) : '';
		$translation = isset( $_POST['translation'] ) ? sanitize_textarea_field( wp_unslash( $_POST['translation'] ) ) : '';

		if ( $string_id <= 0 || $lang_code === '' ) {
			wp_send_json_error( [ 'message' => 'Invalid parameters' ], 400 );
		}

		global $wpdb;
		$tr_table = $wpdb->prefix . 'i18n_translations';

		$exists = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$tr_table} WHERE string_id = %d AND lang_code = %s",
			$string_id,
			$lang_code
		) );

		if ( $exists > 0 ) {
			$wpdb->update( $tr_table, [ 'translation_text' => $translation ], [ 'string_id' => $string_id, 'lang_code' => $lang_code ] );
		} else {
			$wpdb->insert( $tr_table, [
				'string_id'        => $string_id,
				'lang_code'        => $lang_code,
				'translation_text' => $translation,
			] );
		}

		wp_cache_flush();
		wp_send_json_success( [ 'message' => 'Translation saved' ] );
	}

	public function ajax_save_string(): void {
		check_ajax_referer( 'i18n_translate_nonce', 'nonce' );

		if ( ! current_user_can( 'i18n_translate_manage' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
		}

		$string_id = isset( $_POST['string_id'] ) ? (int) $_POST['string_id'] : 0;
		$domain = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : 'default';
		$string_key = isset( $_POST['string_key'] ) ? sanitize_text_field( wp_unslash( $_POST['string_key'] ) ) : '';
		$default_text = isset( $_POST['default_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['default_text'] ) ) : '';

		if ( $string_key === '' ) {
			wp_send_json_error( [ 'message' => 'String key is required' ], 400 );
		}

		global $wpdb;
		$strings_table = $wpdb->prefix . 'i18n_strings';

		if ( $string_id > 0 ) {
			$wpdb->update( $strings_table, [
				'domain'       => $domain,
				'string_key'   => $string_key,
				'default_text' => $default_text,
			], [ 'id' => $string_id ] );
		} else {
			$wpdb->insert( $strings_table, [
				'domain'       => $domain,
				'string_key'   => $string_key,
				'default_text' => $default_text,
			] );
			$string_id = (int) $wpdb->insert_id;
		}

		wp_cache_flush();
		wp_send_json_success( [ 'message' => 'String saved', 'string_id' => $string_id ] );
	}

	public function ajax_delete_string(): void {
		check_ajax_referer( 'i18n_translate_nonce', 'nonce' );

		if ( ! current_user_can( 'i18n_translate_manage' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
		}

		$string_id = isset( $_POST['string_id'] ) ? (int) $_POST['string_id'] : 0;

		if ( $string_id <= 0 ) {
			wp_send_json_error( [ 'message' => 'Invalid string ID' ], 400 );
		}

		global $wpdb;
		$strings_table = $wpdb->prefix . 'i18n_strings';
		$tr_table = $wpdb->prefix . 'i18n_translations';

		// Delete translations first
		$wpdb->delete( $tr_table, [ 'string_id' => $string_id ] );
		// Delete string
		$wpdb->delete( $strings_table, [ 'id' => $string_id ] );

		wp_cache_flush();
		wp_send_json_success( [ 'message' => 'String deleted' ] );
	}

	public function ajax_toggle_language(): void {
		check_ajax_referer( 'i18n_translate_nonce', 'nonce' );

		if ( ! current_user_can( 'i18n_translate_manage' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
		}

		$code = isset( $_POST['code'] ) ? sanitize_key( wp_unslash( $_POST['code'] ) ) : '';
		$enabled = isset( $_POST['enabled'] ) ? (int) $_POST['enabled'] : 0;

		if ( $code === '' ) {
			wp_send_json_error( [ 'message' => 'Invalid language code' ], 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'i18n_languages';
		$wpdb->update( $table, [ 'enabled' => $enabled ], [ 'code' => $code ] );

		wp_cache_flush();
		wp_send_json_success( [ 'message' => 'Language updated', 'enabled' => $enabled ] );
	}

	public function ajax_save_language(): void {
		check_ajax_referer( 'i18n_translate_nonce', 'nonce' );

		if ( ! current_user_can( 'i18n_translate_manage' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
		}

		$code        = isset( $_POST['code'] ) ? sanitize_key( wp_unslash( $_POST['code'] ) ) : '';
		$locale      = isset( $_POST['locale'] ) ? sanitize_text_field( wp_unslash( $_POST['locale'] ) ) : '';
		$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$native_name = isset( $_POST['native_name'] ) ? sanitize_text_field( wp_unslash( $_POST['native_name'] ) ) : '';
		$rtl         = isset( $_POST['rtl'] ) ? (int) $_POST['rtl'] : 0;
		$flag        = isset( $_POST['flag'] ) ? sanitize_text_field( wp_unslash( $_POST['flag'] ) ) : '';
		$enabled     = isset( $_POST['enabled'] ) ? (int) $_POST['enabled'] : 1;
		$sort_order  = isset( $_POST['sort_order'] ) ? (int) $_POST['sort_order'] : 999;

		if ( $code === '' || $locale === '' || $name === '' || $native_name === '' ) {
			wp_send_json_error( [ 'message' => 'All fields are required' ], 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'i18n_languages';

		$exists = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE code = %s", $code ) );

		$data = [
			'code'        => $code,
			'locale'      => $locale,
			'name'        => $name,
			'native_name' => $native_name,
			'rtl'         => $rtl,
			'flag'        => $flag,
			'enabled'     => $enabled,
			'sort_order'  => $sort_order,
		];

		if ( $exists > 0 ) {
			$wpdb->update( $table, $data, [ 'code' => $code ] );
		} else {
			$wpdb->insert( $table, $data );
		}

		wp_cache_flush();
		wp_send_json_success( [ 'message' => 'Language saved' ] );
	}

	public function ajax_delete_language(): void {
		check_ajax_referer( 'i18n_translate_nonce', 'nonce' );

		if ( ! current_user_can( 'i18n_translate_manage' ) ) {
			wp_send_json_error( [ 'message' => 'Permission denied' ], 403 );
		}

		$code = isset( $_POST['code'] ) ? sanitize_key( wp_unslash( $_POST['code'] ) ) : '';

		if ( $code === '' ) {
			wp_send_json_error( [ 'message' => 'Invalid language code' ], 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'i18n_languages';
		$tr_table = $wpdb->prefix . 'i18n_translations';

		// Delete translations for this language
		$wpdb->delete( $tr_table, [ 'lang_code' => $code ] );
		// Delete language
		$wpdb->delete( $table, [ 'code' => $code ] );

		wp_cache_flush();
		wp_send_json_success( [ 'message' => 'Language deleted' ] );
	}
}
