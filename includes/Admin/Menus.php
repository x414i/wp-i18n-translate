<?php
namespace I18nTranslate\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Menus {

    public function register() {
        add_action( 'admin_init', [ $this, 'add_meta_box' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
            add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'add_custom_fields' ], 10, 4 );
    add_action( 'wp_update_nav_menu_item', [ $this, 'save_custom_fields' ], 10, 2 );
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


 
public function add_custom_fields( $item_id, $item, $depth, $args ) {
    if ( strpos( $item->url ?? '', '#i18n-switcher' ) === false ) {
        return;
    }

    $style      = get_post_meta( $item_id, '_menu_item_i18n_style', true ) ?: 'dropdown';
    $show_flags = get_post_meta( $item_id, '_menu_item_i18n_show_flags', true );
    $show_names = get_post_meta( $item_id, '_menu_item_i18n_show_names', true );

    if ( $show_flags === '' ) $show_flags = 'true';
    if ( $show_names === '' ) $show_names = 'true';

    ?>
    <div class="i18n-switcher-fields" style="clear: both; padding: 10px 0; border-top: 1px solid #ddd; margin-top: 10px;">
        <p><strong><?php _e( 'Language Switcher Settings', 'i18n-translate' ); ?></strong></p>
        
        <p class="description description-wide">
            <label for="edit-menu-item-i18n-style-<?php echo $item_id; ?>">
                <?php _e( 'Style:', 'i18n-translate' ); ?><br />
                <select id="edit-menu-item-i18n-style-<?php echo $item_id; ?>" name="menu-item-i18n-style[<?php echo $item_id; ?>]">
                    <option value="dropdown" <?php selected( $style, 'dropdown' ); ?>><?php _e( 'Dropdown', 'i18n-translate' ); ?></option>
                    <option value="list" <?php selected( $style, 'list' ); ?>><?php _e( 'List', 'i18n-translate' ); ?></option>
                    <option value="inline" <?php selected( $style, 'inline' ); ?>><?php _e( 'Inline', 'i18n-translate' ); ?></option>
                    <option value="flags-only" <?php selected( $style, 'flags-only' ); ?>><?php _e( 'Flags only', 'i18n-translate' ); ?></option>
                    <option value="names-only" <?php selected( $style, 'names-only' ); ?>><?php _e( 'Names only', 'i18n-translate' ); ?></option>
                </select>
            </label>
        </p>

        <p class="description description-thin">
            <label for="edit-menu-item-i18n-show-flags-<?php echo $item_id; ?>">
                <input type="checkbox" id="edit-menu-item-i18n-show-flags-<?php echo $item_id; ?>" name="menu-item-i18n-show-flags[<?php echo $item_id; ?>]" value="true" <?php checked( $show_flags, 'true' ); ?> />
                <?php _e( 'Show flags', 'i18n-translate' ); ?>
            </label>
        </p>

        <p class="description description-thin">
            <label for="edit-menu-item-i18n-show-names-<?php echo $item_id; ?>">
                <input type="checkbox" id="edit-menu-item-i18n-show-names-<?php echo $item_id; ?>" name="menu-item-i18n-show-names[<?php echo $item_id; ?>]" value="true" <?php checked( $show_names, 'true' ); ?> />
                <?php _e( 'Show names', 'i18n-translate' ); ?>
            </label>
        </p>
    </div>
    <?php
}


public function save_custom_fields( $menu_id, $menu_item_db_id ) {
    if ( isset( $_POST['menu-item-i18n-style'][ $menu_item_db_id ] ) ) {
        update_post_meta( $menu_item_db_id, '_menu_item_i18n_style', sanitize_text_field( $_POST['menu-item-i18n-style'][ $menu_item_db_id ] ) );
    }

    $show_flags = isset( $_POST['menu-item-i18n-show-flags'][ $menu_item_db_id ] ) ? 'true' : 'false';
    update_post_meta( $menu_item_db_id, '_menu_item_i18n_show_flags', $show_flags );

    $show_names = isset( $_POST['menu-item-i18n-show-names'][ $menu_item_db_id ] ) ? 'true' : 'false';
    update_post_meta( $menu_item_db_id, '_menu_item_i18n_show_names', $show_names );
}
}