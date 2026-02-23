<?php

namespace I18nTranslate\Runtime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Render {
public function language_switcher( string $style = 'dropdown', bool $show_flags = true, bool $show_names = true, string $extra_class = '' ): string {
    $style = $style !== '' ? sanitize_text_field( $style ) : 'dropdown';
    $args  = apply_filters( 'json_i18n_language_switcher_args', [
        'style'      => $style,
        'show_flags' => $show_flags,
        'show_names' => $show_names,
    ] );

    $style      = (string) ( $args['style'] ?? 'dropdown' );
    $show_flags = (bool) ( $args['show_flags'] ?? true );
    $show_names = (bool) ( $args['show_names'] ?? true );

    $languages  = json_i18n_get_available_languages();
    $current   = json_i18n_get_current_language();

    if ( empty( $languages ) ) {
        return '';
    }

    $extra_class = $extra_class ? ' ' . sanitize_html_class( $extra_class ) : '';

    if ( $style === 'dropdown' ) {
        $html = '<select class="language-switcher' . $extra_class . '" aria-label="' . esc_attr__( 'Language selector', 'i18n-translate' ) . '">';
        foreach ( $languages as $code => $lang ) {
            $label = '';
            if ( $show_flags && ! empty( $lang['flag'] ) ) {
                $label .= (string) $lang['flag'] . ' ';
            }
            if ( $show_names ) {
                $label .= (string) ( $lang['native_name'] ?? $lang['name'] ?? $code );
            } else {
                $label .= strtoupper( (string) $code );
            }

            $html .= sprintf(
                '<option value="%s" %s>%s</option>',
                esc_attr( $code ),
                selected( $current, $code, false ),
                esc_html( $label )
            );
        }
        $html .= '</select>';
        return $html;
    }

    $items = [];
    foreach ( $languages as $code => $lang ) {
        $url = add_query_arg( 'i18n_lang', $code );
        $text = '';
        if ( $show_flags && ! empty( $lang['flag'] ) ) {
            $text .= (string) $lang['flag'];
        }
        if ( $show_names ) {
            $name = (string) ( $lang['native_name'] ?? $lang['name'] ?? $code );
            $text .= ( $text !== '' ? ' ' : '' ) . $name;
        }
        $class = 'language-switcher-link' . ( $code === $current ? ' is-active' : '' );
        $items[] = '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
    }

    $wrapper_class = ( $style === 'flags' ? 'language-switcher-flags' : 'language-switcher-buttons' ) . $extra_class;
    return '<div class="' . esc_attr( $wrapper_class ) . '">' . implode( ' ', $items ) . '</div>';
}

private function render_dropdown_style( $items, $show_flags, $show_names, $extra_class ) {
    $current = current( array_filter( $items, function( $item ) { return $item['active']; } ) ) ?: $items[0];
    ob_start();
    ?>
    <div class="i18n-language-switcher dropdown <?php echo esc_attr( $extra_class ); ?>">
        <span class="current-language">
            <?php if ( $show_flags ) : ?>
                <span class="flag flag-<?php echo esc_attr( $current['code'] ); ?>"></span>
            <?php endif; ?>
            <?php if ( $show_names ) : ?>
                <?php echo esc_html( $current['name'] ); ?>
            <?php endif; ?>
        </span>
        <ul>
            <?php foreach ( $items as $item ) : ?>
                <li class="<?php echo $item['active'] ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url( $item['url'] ); ?>">
                        <?php if ( $show_flags ) : ?>
                            <span class="flag flag-<?php echo esc_attr( $item['code'] ); ?>"></span>
                        <?php endif; ?>
                        <?php if ( $show_names ) : ?>
                            <?php echo esc_html( $item['name'] ); ?>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

private function render_list_style( $items, $show_flags, $show_names, $extra_class ) {
    ob_start();
    ?>
    <ul class="i18n-language-switcher list <?php echo esc_attr( $extra_class ); ?>">
        <?php foreach ( $items as $item ) : ?>
            <li class="<?php echo $item['active'] ? 'active' : ''; ?>">
                <a href="<?php echo esc_url( $item['url'] ); ?>">
                    <?php if ( $show_flags ) : ?>
                        <span class="flag flag-<?php echo esc_attr( $item['code'] ); ?>"></span>
                    <?php endif; ?>
                    <?php if ( $show_names ) : ?>
                        <?php echo esc_html( $item['name'] ); ?>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}

private function render_inline_style( $items, $show_flags, $show_names, $extra_class ) {
    ob_start();
    ?>
    <div class="i18n-language-switcher inline <?php echo esc_attr( $extra_class ); ?>">
        <?php foreach ( $items as $item ) : ?>
            <a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo $item['active'] ? 'active' : ''; ?>">
                <?php if ( $show_flags ) : ?>
                    <span class="flag flag-<?php echo esc_attr( $item['code'] ); ?>"></span>
                <?php endif; ?>
                <?php if ( $show_names ) : ?>
                    <?php echo esc_html( $item['name'] ); ?>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

private function render_flags_only( $items, $extra_class ) {
    ob_start();
    ?>
    <div class="i18n-language-switcher flags-only <?php echo esc_attr( $extra_class ); ?>">
        <?php foreach ( $items as $item ) : ?>
            <a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo $item['active'] ? 'active' : ''; ?>" title="<?php echo esc_attr( $item['name'] ); ?>">
                <span class="flag flag-<?php echo esc_attr( $item['code'] ); ?>"></span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

private function render_names_only( $items, $extra_class ) {
    ob_start();
    ?>
    <div class="i18n-language-switcher names-only <?php echo esc_attr( $extra_class ); ?>">
        <?php foreach ( $items as $item ) : ?>
            <a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo $item['active'] ? 'active' : ''; ?>">
                <?php echo esc_html( $item['name'] ); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
private function get_current_url_without_lang() {
    global $wp;
    return home_url( add_query_arg( [], $wp->request ) );
}

private function get_language_name( $code ) {
    $languages = function_exists( 'json_i18n_get_languages' ) ? json_i18n_get_languages() : [];
    return $languages[ $code ] ?? $code;
}
}
