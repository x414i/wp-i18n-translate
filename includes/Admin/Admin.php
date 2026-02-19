<?php

namespace I18nTranslate\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Admin {
	private ?DashboardPage $dashboard_page = null;
	private ?LanguagesPage $languages_page = null;
	private ?TranslationsPage $translations_page = null;
	private ?ContentPage $content_page = null;
	private ?SettingsPage $settings_page = null;
	private ?UsagePage $usage_page = null;
	private ?ScannerPage $scanner_page = null;
	private ?ImportExportPage $import_export_page = null;

	public function register(): void {
		$this->dashboard_page    = new DashboardPage();
		$this->languages_page    = new LanguagesPage();
		$this->translations_page = new TranslationsPage();
		$this->content_page      = new ContentPage();
		$this->settings_page     = new SettingsPage();
		$this->usage_page        = new UsagePage();
		$this->scanner_page      = new ScannerPage();
		$this->import_export_page = new ImportExportPage();

		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
                add_action( 'admin_init', [ $this, 'seed_english_translations' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'admin_bar_menu', [ $this, 'admin_bar_language_switcher' ], 100 );
	}

	public function register_settings(): void {
		register_setting( 'i18n_translate_settings', 'i18n_translate_default_language', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'en',
		] );

		register_setting( 'i18n_translate_settings', 'i18n_translate_auto_detect', [
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'default'           => '1',
		] );
	}

	public function menu(): void {
		add_menu_page(
			__( 'i18n Translate', 'i18n-translate' ),
			__( 'i18n Translate', 'i18n-translate' ),
			'i18n_translate_manage',
			'i18n-translate',
			[ $this, 'render_dashboard' ],
			'dashicons-translation',
			58
		);

		add_submenu_page(
			'i18n-translate',
			__( 'Dashboard', 'i18n-translate' ),
			__( 'Dashboard', 'i18n-translate' ),
			'i18n_translate_manage',
			'i18n-translate',
			[ $this, 'render_dashboard' ]
		);

		add_submenu_page(
			'i18n-translate',
			__( 'Languages', 'i18n-translate' ),
			__( 'Languages', 'i18n-translate' ),
			'i18n_translate_manage',
			'i18n-translate-languages',
			[ $this, 'render_languages' ]
		);

		add_submenu_page(
			'i18n-translate',
			__( 'Translations', 'i18n-translate' ),
			__( 'Translations', 'i18n-translate' ),
			'i18n_translate_translate',
			'i18n-translate-translations',
			[ $this, 'render_translations' ]
		);

		add_submenu_page(
			'i18n-translate',
			__( 'Content Translations', 'i18n-translate' ),
			__( 'Content', 'i18n-translate' ),
			'i18n_translate_translate',
			'i18n-translate-content',
			[ $this, 'render_content' ]
		);

		add_submenu_page(
			'i18n-translate',
			__( 'Settings', 'i18n-translate' ),
			__( 'Settings', 'i18n-translate' ),
			'i18n_translate_manage',
			'i18n-translate-settings',
			[ $this, 'render_settings' ]
		);

		add_submenu_page(
			'i18n-translate',
			__( 'Usage Guide', 'i18n-translate' ),
			__( 'Usage Guide', 'i18n-translate' ),
			'i18n_translate_translate',
			'i18n-translate-usage',
			[ $this, 'render_usage' ]
		);

		add_submenu_page(
			'i18n-translate',
			__( 'String Scanner', 'i18n-translate' ),
			__( 'String Scanner', 'i18n-translate' ),
			'i18n_translate_manage',
			'i18n-translate-scanner',
			[ $this, 'render_scanner' ]
		);

		add_submenu_page(
			'i18n-translate',
			__( 'Import / Export', 'i18n-translate' ),
			__( 'Import / Export', 'i18n-translate' ),
			'i18n_translate_manage',
			'i18n-translate-import-export',
			[ $this, 'render_import_export' ]
		);
	}

	public function enqueue_admin_assets( string $hook ): void {
		// Only load on our plugin pages
		$plugin_pages = [
			'toplevel_page_i18n-translate',
			'i18n-translate_page_i18n-translate-languages',
			'i18n-translate_page_i18n-translate-translations',
			'i18n-translate_page_i18n-translate-content',
			'i18n-translate_page_i18n-translate-settings',
			'i18n-translate_page_i18n-translate-usage',
			'i18n-translate_page_i18n-translate-scanner',
			'i18n-translate_page_i18n-translate-import-export',
		];
		
		if ( ! in_array( $hook, $plugin_pages, true ) ) {
			return;
		}

		// Alpine.js
		wp_enqueue_script(
			'alpinejs',
			'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
			[],
			'3.14.3',
			[ 'strategy' => 'defer' ]
		);

		// Admin CSS
		wp_enqueue_style(
			'i18n-translate-admin',
			I18N_TRANSLATE_URL . 'assets/admin.css',
			[],
			I18N_TRANSLATE_VERSION
		);

		// Admin data
		wp_localize_script( 'alpinejs', 'i18nTranslate', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'i18n_translate_nonce' ),
			'restUrl' => rest_url( 'json-i18n/v1/field-translations' ),
			'restNonce' => wp_create_nonce( 'wp_rest' ),
		] );
	}

	public function admin_bar_language_switcher( \WP_Admin_Bar $admin_bar ): void {
		if ( ! current_user_can( 'i18n_translate_translate' ) ) {
			return;
		}

		if ( is_admin() && ! $this->is_plugin_admin_request() ) {
			return;
		}

		$languages = json_i18n_get_available_languages();
		$current   = json_i18n_get_current_language();

		if ( empty( $languages ) ) {
			return;
		}

		$current_info = $languages[ $current ] ?? null;
		$current_label = '🌐 ';
		if ( $current_info ) {
			$current_label = ( $current_info['flag'] ?? '🌐' ) . ' ' . ( $current_info['name'] ?? $current );
		}

		$admin_bar->add_node( [
			'id'    => 'i18n-translate-switcher',
			'title' => $current_label,
			'href'  => '#',
			'meta'  => [
				'title' => __( 'Switch Language', 'i18n-translate' ),
			],
		] );

		foreach ( $languages as $code => $lang ) {
			$url = add_query_arg( 'i18n_lang', $code );
			$label = '';
			if ( ! empty( $lang['flag'] ) ) {
				$label .= $lang['flag'] . ' ';
			}
			$label .= $lang['native_name'] ?? $lang['name'] ?? $code;

			if ( $code === $current ) {
				$label .= ' ✓';
			}

			$admin_bar->add_node( [
				'id'     => 'i18n-translate-lang-' . $code,
				'parent' => 'i18n-translate-switcher',
				'title'  => $label,
				'href'   => $url,
				'meta'   => [
					'class' => $code === $current ? 'i18n-current-lang' : '',
				],
			] );
		}
	}

	public function render_dashboard(): void {
		$this->dashboard_page = $this->dashboard_page ?? new DashboardPage();
		$this->dashboard_page->render();
	}

	public function render_languages(): void {
		$this->languages_page = $this->languages_page ?? new LanguagesPage();
		$this->languages_page->render();
	}

	public function render_translations(): void {
		$this->translations_page = $this->translations_page ?? new TranslationsPage();
		$this->translations_page->render();
	}

	public function render_content(): void {
		$this->content_page = $this->content_page ?? new ContentPage();
		$this->content_page->render();
	}

	public function render_settings(): void {
		$this->settings_page = $this->settings_page ?? new SettingsPage();
		$this->settings_page->render();
	}

	public function render_usage(): void {
		$this->usage_page = $this->usage_page ?? new UsagePage();
		$this->usage_page->render();
	}

	public function render_scanner(): void {
		$this->scanner_page = $this->scanner_page ?? new ScannerPage();
		$this->scanner_page->render();
	}

	public function render_import_export(): void {
		$this->import_export_page = $this->import_export_page ?? new ImportExportPage();
		$this->import_export_page->render();
	}

	public function seed_english_translations(): void {
		if ( get_option( 'i18n_translate_seeded_en_v1' ) ) {
			return;
		}

		global $wpdb;
		$strings_table = $wpdb->prefix . 'i18n_strings';
		$tr_table      = $wpdb->prefix . 'i18n_translations';

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $strings_table ) ) !== $strings_table ) {
			return;
		}

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tr_table ) ) !== $tr_table ) {
			return;
		}

		$strings = $wpdb->get_results( "SELECT id, string_key, default_text FROM {$strings_table}" );

		if ( ! empty( $strings ) ) {
			foreach ( $strings as $s ) {
				$existing = $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM {$tr_table} WHERE string_id = %d AND lang_code = 'en'",
					$s->id
				) );

				if ( ! $existing ) {
					$text = $s->default_text ? $s->default_text : $s->string_key;
					$wpdb->insert( $tr_table, [
						'string_id'        => $s->id,
						'lang_code'        => 'en',
						'translation_text' => $text,
					] );
				}
			}
		}

		update_option( 'i18n_translate_seeded_en_v1', '1' );
	}

	private function is_plugin_admin_request(): bool {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( $page === '' ) {
			return false;
		}

		return str_starts_with( $page, 'i18n-translate' );
	}
}