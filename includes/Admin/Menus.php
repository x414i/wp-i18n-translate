<?php
namespace I18nTranslate\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Menus {

    public function register() {
        add_action( 'admin_init', [ $this, 'add_meta_box' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function add_meta_box() {
        add_meta_box(
            'i18n-language-switcher',
            __( 'Language Switcher', 'i18n-translate' ),
            [ $this, 'render_meta_box' ],
            'nav-menus',
            'side',
            'default'
        );
    }

    public function render_meta_box() {
        ?>
        <div id="i18n-lang-switcher-metabox">
            <p><?php _e( 'Add a language switcher to your menu.', 'i18n-translate' ); ?></p>
            <p>
                <button type="button" class="button button-secondary" id="i18n-add-language-switcher">
                    <?php _e( 'Add Language Switcher', 'i18n-translate' ); ?>
                </button>
            </p>
        </div>
        <?php
    }

    public function enqueue_scripts( $hook ) {
        if ( $hook !== 'nav-menus.php' ) {
            return;
        }
        wp_enqueue_script( 'i18n-menus', I18N_TRANSLATE_URL . 'assets/admin-menus.js', [ 'jquery', 'nav-menu' ], I18N_TRANSLATE_VERSION, true );
        wp_localize_script( 'i18n-menus', 'i18nMenus', [
            'languageText' => __( 'Languages', 'i18n-translate' ),
        ] );
    }
}