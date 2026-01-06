<?php

namespace I18nTranslate\I18n;

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

final class PublicApi {
        public function register(): void {
                $this->register_shortcodes();
                
                // Allow shortcodes in text widgets
                add_filter( 'widget_text', 'do_shortcode' );
                // FSE Navigation Block Support
                add_filter( 'render_block', function( $block_content, $block ) {
                    if ( $block['blockName'] === 'core/navigation-link' && isset( $block['attrs']['url'] ) && strpos( $block['attrs']['url'], '#i18n-switcher' ) !== false ) {
                        $style = 'dropdown';
                        $parts = parse_url( $block['attrs']['url'] );
                        if ( isset( $parts['query'] ) ) {
                            parse_str( $parts['query'], $query );
                            if ( isset( $query['style'] ) ) {
                                $style = sanitize_text_field( $query['style'] );
                            }
                        }
                        // Render full switcher markup (replacing the link)
                        // Note: Navigation block expects LI elements often, but render_block returns the LI content or wrapper?
                        // core/navigation-link renders <li ...><a ...>...</a></li>.
                        // Wait, render_block output is string.
                        // Switcher renders a div/ul/dropdown.
                        // If we return just the switcher, the LI wrapper from the Block Editor might still be there? 
                        // Actually render_block returns the FULL html of the block.
                        // So we should return the switcher wrapped in LI if needed, OR just the switcher if the block IS the LI.
                        // core/navigation-link output includes the <li> wrapper.
                        // So we should return: <li class="wp-block-navigation-item i18n-menu-item">SWITCHER</li>
                        
                        $switcher = i18n_translate()->render()->language_switcher( $style, true, true );
                        return '<li class="wp-block-navigation-item wp-block-navigation-link i18n-menu-item">' . $switcher . '</li>';
                    }
                    return $block_content;
                }, 10, 2 );

                // Menu Integration
                add_filter( 'walker_nav_menu_start_el', function( $item_output, $item, $depth, $args ) {
                    if ( strpos( $item->url, '#i18n-switcher' ) !== false ) {
                        // Extract style arg if present #i18n-switcher?style=list
                        $style = 'dropdown';
                        $parts = parse_url( $item->url );
                        if ( isset( $parts['query'] ) ) {
                            parse_str( $parts['query'], $query );
                            if ( isset( $query['style'] ) ) {
                                $style = sanitize_text_field( $query['style'] );
                            }
                        }
                        
                        // Render switcher
                        return i18n_translate()->render()->language_switcher( $style, true, true );
                    }
                    return $item_output;
                }, 10, 4 );
        }

        private function register_shortcodes(): void {
                add_shortcode( 'json_i18n_text', function( $atts ) {
                        $atts = shortcode_atts( [
                                'key'     => '',
                                'default' => '',
                                'domain'  => 'default',
                        ], (array) $atts );

                        $key = sanitize_text_field( (string) $atts['key'] );
                        if ( $key === '' ) {
                                return '';
                        }

                        $default = (string) $atts['default'];
                        $domain  = sanitize_text_field( (string) $atts['domain'] );

                        return esc_html( json_i18n_translate( $key, $default, $domain ) );
                } );

                add_shortcode( 'json-i18n-text', function( $atts ) {
                        $atts = (array) $atts;
                        return do_shortcode( sprintf( '[json_i18n_text key="%s" default="%s" domain="%s"]',
                                esc_attr( $atts['key'] ?? '' ),
                                esc_attr( $atts['default'] ?? '' ),
                                esc_attr( $atts['domain'] ?? 'default' )
                        ) );
                } );

                add_shortcode( 'json_i18n_language_switcher', function( $atts ) {
                        $atts = shortcode_atts( [
                                'style'      => 'dropdown',
                                'show_flags' => 'true',
                                'show_names' => 'true',
                        ], (array) $atts );

                        $style      = sanitize_text_field( (string) $atts['style'] );
                        $show_flags = filter_var( $atts['show_flags'], FILTER_VALIDATE_BOOLEAN );
                        $show_names = filter_var( $atts['show_names'], FILTER_VALIDATE_BOOLEAN );

                        return i18n_translate()->render()->language_switcher( $style, $show_flags, $show_names );
                } );

                add_shortcode( 'json-i18n-language-switcher', function( $atts ) {
                        return do_shortcode( '[json_i18n_language_switcher]' );
                } );

                // Simplified Aliases

                add_shortcode( 'i18n', function( $atts ) {
                    // Support [i18n "key" default="..."]
                    if ( isset( $atts[0] ) && ! isset( $atts['key'] ) ) {
                        $atts['key'] = $atts[0];
                    }

                    $atts = shortcode_atts( [
                        'key'     => '',
                        'default' => '',
                        'domain'  => 'default',
                    ], (array) $atts );

                    $key = sanitize_text_field( (string) $atts['key'] );
                    if ( $key === '' ) {
                        return '';
                    }
                    
                    $default = (string) $atts['default'];
                    $domain  = sanitize_text_field( (string) $atts['domain'] );

                    return esc_html( json_i18n_translate( $key, $default, $domain ) );
                } );

                add_shortcode( 'i18n_switcher', function( $atts ) {
                    $atts = shortcode_atts( [
                         'style'      => 'dropdown',
                         'show_flags' => 'true',
                         'show_names' => 'true',
                    ], (array) $atts );

                    $style      = sanitize_text_field( (string) $atts['style'] );
                    $show_flags = filter_var( $atts['show_flags'], FILTER_VALIDATE_BOOLEAN );
                    $show_names = filter_var( $atts['show_names'], FILTER_VALIDATE_BOOLEAN );

                    return i18n_translate()->render()->language_switcher( $style, $show_flags, $show_names );
                } );

                // Media Support
                add_shortcode( 'i18n_image', function( $atts ) {
                    if ( isset( $atts[0] ) && ! isset( $atts['key'] ) ) {
                        $atts['key'] = $atts[0];
                    }

                    $atts = shortcode_atts( [
                        'key'     => '',
                        'size'    => 'full',
                        'class'   => '',
                        'default' => '',
                    ], (array) $atts );

                    $key = sanitize_text_field( (string) $atts['key'] );
                    if ( $key === '' ) {
                        return '';
                    }

                    $val = json_i18n_translate( $key, $atts['default'] );
                    if ( ! $val ) {
                        return '';
                    }

                    // Check if numeric ID
                    if ( is_numeric( $val ) && (int) $val > 0 ) {
                        return wp_get_attachment_image( (int) $val, $atts['size'], false, [ 'class' => esc_attr( $atts['class'] ) ] );
                    }

                    // Treat as URL
                    return sprintf( '<img src="%s" class="%s" alt="">', esc_url( $val ), esc_attr( $atts['class'] ) );
                } );
        }
}
