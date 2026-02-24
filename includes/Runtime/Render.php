<?php

namespace I18nTranslate\Runtime;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Render {

    public function language_switcher( string $style = 'dropdown', bool $show_flags = true, bool $show_names = true, string $extra_class = '' ): string {
        $args = apply_filters( 'json_i18n_language_switcher_args', [
            'style'      => $style,
            'show_flags' => $show_flags,
            'show_names' => $show_names,
        ] );

        $style      = (string) ( $args['style'] ?? 'dropdown' );
        $show_flags = (bool) ( $args['show_flags'] ?? true );
        $show_names = (bool) ( $args['show_names'] ?? true );
        $extra_class = $extra_class ? ' ' . sanitize_html_class( $extra_class ) : '';

        $languages = json_i18n_get_available_languages();
        $current   = json_i18n_get_current_language();

        if ( empty( $languages ) ) {
            return '';
        }

        $current_url = $this->get_current_url_without_lang();

        $items = [];
        foreach ( $languages as $code => $lang ) {
            $url = add_query_arg( 'i18n_lang', $code, $current_url );
            $name = $lang['native_name'] ?? $lang['name'] ?? $code;
            $active = ( $code === $current );
            $items[] = [
                'code'   => $code,
                'name'   => $name,
                'url'    => $url,
                'active' => $active,
                'flag'   => $lang['flag'] ?? '',
            ];
        }

        switch ( $style ) {
            case 'list':
                return $this->render_list_style( $items, $show_flags, $show_names, $extra_class );
            case 'inline':
                return $this->render_inline_style( $items, $show_flags, $show_names, $extra_class );
            case 'flags-only':
                return $this->render_flags_only( $items, $extra_class );
            case 'names-only':
                return $this->render_names_only( $items, $extra_class );
            case 'dropdown':
            default:
                return $this->render_dropdown_style( $items, $show_flags, $show_names, $extra_class );
        }
    }


    private function get_current_url_without_lang(): string {
        global $wp;
        return home_url( add_query_arg( [], $wp->request ) );
    }


    private function render_dropdown_style( array $items, bool $show_flags, bool $show_names, string $extra_class ): string {
        $current = current( array_filter( $items, function( $item ) {
            return $item['active'];
        } ) ) ?: $items[0];

        ob_start();
        ?>
        <div class="i18n-language-switcher dropdown<?php echo $extra_class; ?>">
            <span class="current-language">
                <?php if ( $show_flags && ! empty( $current['flag'] ) ) : ?>
                    <span class="flag flag-<?php echo esc_attr( $current['code'] ); ?>"><?php echo $current['flag']; ?></span>
                <?php endif; ?>
                <?php if ( $show_names ) : ?>
                    <?php echo esc_html( $current['name'] ); ?>
                <?php endif; ?>
            </span>
            <ul>
                <?php foreach ( $items as $item ) : ?>
                    <li class="<?php echo $item['active'] ? 'active' : ''; ?>">
                        <a href="<?php echo esc_url( $item['url'] ); ?>">
                            <?php if ( $show_flags && ! empty( $item['flag'] ) ) : ?>
                                <span class="flag flag-<?php echo esc_attr( $item['code'] ); ?>"><?php echo $item['flag']; ?></span>
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


    private function render_list_style( array $items, bool $show_flags, bool $show_names, string $extra_class ): string {
        ob_start();
        ?>
        <ul class="i18n-language-switcher list<?php echo $extra_class; ?>">
            <?php foreach ( $items as $item ) : ?>
                <li class="<?php echo $item['active'] ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url( $item['url'] ); ?>">
                        <?php if ( $show_flags && ! empty( $item['flag'] ) ) : ?>
                            <span class="flag flag-<?php echo esc_attr( $item['code'] ); ?>"><?php echo $item['flag']; ?></span>
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

    private function render_inline_style( array $items, bool $show_flags, bool $show_names, string $extra_class ): string {
        ob_start();
        ?>
        <div class="i18n-language-switcher inline<?php echo $extra_class; ?>">
            <?php foreach ( $items as $item ) : ?>
                <a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo $item['active'] ? 'active' : ''; ?>">
                    <?php if ( $show_flags && ! empty( $item['flag'] ) ) : ?>
                        <span class="flag flag-<?php echo esc_attr( $item['code'] ); ?>"><?php echo $item['flag']; ?></span>
                    <?php endif; ?>
                    <?php if ( $show_names ) : ?>
                        <?php echo esc_html( $item['name'] ); ?>
                    <?php endif; ?>
                </a>
                <?php if ( ! $show_flags && ! $show_names ) : ?>
                    <?php echo esc_html( strtoupper( $item['code'] ) ); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_flags_only( array $items, string $extra_class ): string {
        ob_start();
        ?>
        <div class="i18n-language-switcher flags-only<?php echo $extra_class; ?>">
            <?php foreach ( $items as $item ) : ?>
                <a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo $item['active'] ? 'active' : ''; ?>" title="<?php echo esc_attr( $item['name'] ); ?>">
                    <?php if ( ! empty( $item['flag'] ) ) : ?>
                        <span class="flag flag-<?php echo esc_attr( $item['code'] ); ?>"><?php echo $item['flag']; ?></span>
                    <?php else : ?>
                        <?php echo esc_html( strtoupper( $item['code'] ) ); ?>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }


    private function render_names_only( array $items, string $extra_class ): string {
        ob_start();
        ?>
        <div class="i18n-language-switcher names-only<?php echo $extra_class; ?>">
            <?php foreach ( $items as $item ) : ?>
                <a href="<?php echo esc_url( $item['url'] ); ?>" class="<?php echo $item['active'] ? 'active' : ''; ?>">
                    <?php echo esc_html( $item['name'] ); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}