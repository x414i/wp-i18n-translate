<?php
/**
 * Plugin Name: i18n Translate
 * Description: Translation management with JSON-key translations, language switching via ?lang=, REST/AJAX endpoints, and page builder compatibility.
 * Version: 1.0.0
 * Author: Satus
 * License: GPLv2 or later
 * Text Domain: i18n-translate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'I18N_TRANSLATE_VERSION', '1.0.0' );
define( 'I18N_TRANSLATE_PATH', plugin_dir_path( __FILE__ ) );
define( 'I18N_TRANSLATE_URL', plugin_dir_url( __FILE__ ) );
define( 'I18N_TRANSLATE_DB_VERSION', '3' );

require_once I18N_TRANSLATE_PATH . 'includes/Runtime/Plugin.php';

function i18n_translate() {
	return \I18nTranslate\Runtime\Plugin::instance();
}

i18n_translate()->boot();
