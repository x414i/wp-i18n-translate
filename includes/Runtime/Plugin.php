<?php

namespace I18nTranslate\Runtime;

use I18nTranslate\Db\Installer;
use I18nTranslate\I18n\Locale;
use I18nTranslate\I18n\PublicApi;
use I18nTranslate\I18n\TitleKeySupport;
use I18nTranslate\Http\Endpoints;
use I18nTranslate\Admin\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	private static ?Plugin $instance = null;
	private ?Services $services = null;

	public static function instance(): Plugin {
		if ( self::$instance === null ) {
			self::$instance = new Plugin();
		}
		return self::$instance;
	}

	public function boot(): void {
		$this->register_activation_hooks();
		add_action( 'plugins_loaded', [ $this, 'load' ], 1 );
	}

	public function load(): void {
		require_once I18N_TRANSLATE_PATH . 'includes/Db/Installer.php';
		require_once I18N_TRANSLATE_PATH . 'includes/I18n/compat.php';
		require_once I18N_TRANSLATE_PATH . 'includes/I18n/Locale.php';
		require_once I18N_TRANSLATE_PATH . 'includes/I18n/PublicApi.php';
		require_once I18N_TRANSLATE_PATH . 'includes/I18n/TitleKeySupport.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Http/Endpoints.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Admin/LanguagesPage.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Admin/TranslationsPage.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Admin/ContentPage.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Admin/UsagePage.php';
                require_once I18N_TRANSLATE_PATH . 'includes/Admin/SettingsPage.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Admin/DashboardPage.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Admin/ScannerPage.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Admin/ImportExportPage.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Admin/Admin.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Runtime/Services.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Runtime/Strings.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Runtime/Render.php';
		require_once I18N_TRANSLATE_PATH . 'includes/Runtime/LanguageSwitcherWidget.php';

		( new Installer() )->maybe_upgrade();
		$this->services = new Services();

		( new Locale() )->register();
		( new PublicApi() )->register();
		( new TitleKeySupport() )->register();
		( new Endpoints() )->register();

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend' ], 5 );
		add_action( 'widgets_init', function() {
			register_widget( \I18nTranslate\Runtime\LanguageSwitcherWidget::class );
		} );
		$this->register_content_filters();

		if ( is_admin() ) {
			( new Admin() )->register();
		}

		$this->register_blocks();
	}

	public function locale(): Locale {
		return $this->services()->locale();
	}

	public function strings(): Strings {
		return $this->services()->strings();
	}

	public function render(): Render {
		return $this->services()->render();
	}

	private function services(): Services {
		if ( $this->services === null ) {
			$this->services = new Services();
		}
		return $this->services;
	}

	private function register_activation_hooks(): void {
		register_activation_hook( I18N_TRANSLATE_PATH . 'i18n-translate.php', function() {
			require_once I18N_TRANSLATE_PATH . 'includes/Db/Installer.php';
			( new Installer() )->install();
		} );
	}

	private function register_blocks(): void {
		if ( function_exists( 'register_block_type' ) ) {
			register_block_type( I18N_TRANSLATE_PATH . 'blocks/language-switcher' );
		}
	}

	private function register_content_filters(): void {
		add_filter( 'the_title', [ $this, 'filter_the_title' ], 10, 2 );
		add_filter( 'the_content', [ $this, 'filter_the_content' ], 10 );
		add_filter( 'get_the_excerpt', [ $this, 'filter_the_excerpt' ], 10, 2 );
		add_filter( 'term_name', [ $this, 'filter_term_name' ], 10, 3 );
		add_filter( 'term_description', [ $this, 'filter_term_description' ], 10, 2 );
		add_filter( 'widget_title', [ $this, 'filter_widget_title' ], 10, 3 );

		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'woocommerce_product_get_name', [ $this, 'filter_wc_product_name' ], 10, 2 );
			add_filter( 'woocommerce_product_get_description', [ $this, 'filter_wc_product_description' ], 10, 2 );
			add_filter( 'woocommerce_product_get_short_description', [ $this, 'filter_wc_product_short_description' ], 10, 2 );
			add_filter( 'woocommerce_attribute_label', [ $this, 'filter_wc_attribute_label' ], 10, 3 );
			add_filter( 'woocommerce_checkout_fields', [ $this, 'filter_wc_checkout_fields' ] );
		}
	}

	public function filter_the_title( string $title, int $post_id = 0 ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $title;
		}
		if ( $post_id <= 0 ) {
			return $title;
		}
		return $this->strings()->translate_field( 'post', $post_id, 'title', $title );
	}

	public function filter_the_content( string $content ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $content;
		}
		global $post;
		if ( ! $post instanceof \WP_Post ) {
			return $content;
		}
		return $this->strings()->translate_field( 'post', (int) $post->ID, 'content', $content );
	}

	public function filter_the_excerpt( string $excerpt, $post = null ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $excerpt;
		}
		if ( $post instanceof \WP_Post ) {
			return $this->strings()->translate_field( 'post', (int) $post->ID, 'excerpt', $excerpt );
		}
		return $excerpt;
	}

	public function filter_term_name( string $name, \WP_Term $term, string $taxonomy ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $name;
		}
		return $this->strings()->translate_field( 'term', (int) $term->term_id, 'name', $name );
	}

	public function filter_term_description( string $description, \WP_Term $term ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $description;
		}
		return $this->strings()->translate_field( 'term', (int) $term->term_id, 'description', $description );
	}

	public function filter_widget_title( string $title, array $instance, string $id_base ): string {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $title;
		}
		if ( $title === '' ) {
			return $title;
		}
		$key = 'widget.title.' . sanitize_key( $id_base ) . '.' . sanitize_title( $title );
		return json_i18n_translate( $key, $title, 'widgets' );
	}

	public function filter_wc_product_name( string $name, $product ): string {
		if ( ! $product instanceof \WC_Product ) {
			return $name;
		}
		return $this->strings()->translate_field( 'post', (int) $product->get_id(), 'title', $name );
	}

	public function filter_wc_product_description( string $description, $product ): string {
		if ( ! $product instanceof \WC_Product ) {
			return $description;
		}
		return $this->strings()->translate_field( 'post', (int) $product->get_id(), 'content', $description );
	}

	public function filter_wc_product_short_description( string $description, $product ): string {
		if ( ! $product instanceof \WC_Product ) {
			return $description;
		}
		return $this->strings()->translate_field( 'post', (int) $product->get_id(), 'excerpt', $description );
	}

	public function filter_wc_attribute_label( string $label, string $name, $product ): string {
		$key = 'wc.attribute.' . sanitize_key( $name );
		return json_i18n_translate( $key, $label, 'woocommerce' );
	}

	public function filter_wc_checkout_fields( array $fields ): array {
		foreach ( $fields as $section_key => $section ) {
			foreach ( $section as $field_key => $field ) {
				if ( ! isset( $field['label'] ) ) {
					continue;
				}
				$label_key = 'wc.checkout.' . sanitize_key( $section_key . '.' . $field_key . '.label' );
				$fields[ $section_key ][ $field_key ]['label'] = json_i18n_translate( $label_key, (string) $field['label'], 'woocommerce' );
			}
		}
		return $fields;
	}

	public function enqueue_frontend(): void {
		wp_register_script( 'i18n-translate-runtime', I18N_TRANSLATE_URL . 'assets/runtime.js', [], I18N_TRANSLATE_VERSION, true );

		wp_localize_script( 'i18n-translate-runtime', 'wpTemplateI18n', [
			'translations'  => function_exists( 'json_i18n_get_translations' ) ? json_i18n_get_translations() : [],
			'current_lang'  => function_exists( 'json_i18n_get_current_language' ) ? json_i18n_get_current_language() : 'en',
			'languages'     => function_exists( 'json_i18n_get_available_languages' ) ? json_i18n_get_available_languages() : [],
			'ajax_url'      => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'wp_template_nonce' ),
		] );

		wp_enqueue_script( 'i18n-translate-runtime' );
	}
}
