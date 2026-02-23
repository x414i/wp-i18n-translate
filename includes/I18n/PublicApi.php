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
                    if ( is_admin() && ! wp_doing_ajax() ) {
                        return $block_content;
                    }

                    if ( ! is_array( $block ) || ( $block['blockName'] ?? '' ) !== 'core/navigation-link' ) {
                        return $block_content;
                    }

                    $url = isset( $block['attrs']['url'] ) && is_string( $block['attrs']['url'] ) ? $block['attrs']['url'] : '';
                    if ( $url === '' || strpos( $url, '#i18n-switcher' ) === false ) {
                        return $block_content;
                    }

                        $style = 'dropdown';
                        $parts = parse_url( $url );
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
                }, 10, 2 );

                // Menu Integration
                add_filter( 'walker_nav_menu_start_el', function( $item_output, $item, $depth, $args ) {
                    if ( is_admin() && ! wp_doing_ajax() ) {
                        return $item_output;
                    }

                    $url = '';
                    if ( is_object( $item ) && isset( $item->url ) && is_string( $item->url ) ) {
                        $url = $item->url;
                    }

                    if ( $url !== '' && strpos( $url, '#i18n-switcher' ) !== false ) {
                        // Extract style arg if present #i18n-switcher?style=list
                        $style = 'dropdown';
                        $parts = parse_url( $url );
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
                        'class'      => '',           
                    ], (array) $atts );

                    $style      = sanitize_text_field( (string) $atts['style'] );
                    $show_flags = filter_var( $atts['show_flags'], FILTER_VALIDATE_BOOLEAN );
                    $show_names = filter_var( $atts['show_names'], FILTER_VALIDATE_BOOLEAN );
                    $class      = sanitize_html_class( $atts['class'] );


                    return i18n_translate()->render()->language_switcher( $style, $show_flags, $show_names, $class );
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

                add_shortcode( 'i18n_field', function( $atts ) {
                    $atts = shortcode_atts( [
                        'object_type' => 'post',
                        'object_id'   => 0,
                        'field'       => '',
                        'default'     => '',
                        'lang'        => '',
                    ], (array) $atts );

                    $object_type = sanitize_key( (string) $atts['object_type'] );
                    $object_id   = (int) $atts['object_id'];
                    $field_key   = sanitize_text_field( (string) $atts['field'] );
                    $default     = (string) $atts['default'];
                    $lang        = sanitize_key( (string) $atts['lang'] );

                    if ( $object_id <= 0 && $object_type === 'post' ) {
                        $object_id = get_the_ID();
                    }

                    if ( $object_id <= 0 || $field_key === '' ) {
                        return '';
                    }

                    return wp_kses_post( json_i18n_translate_field( $object_type, $object_id, $field_key, $default, $lang ) );
                } );

                add_shortcode( 'i18n_post_title', function( $atts ) {
                    $atts = shortcode_atts( [
                        'id'      => 0,
                        'default' => '',
                        'lang'    => '',
                    ], (array) $atts );

                    $post_id = (int) $atts['id'];
                    if ( $post_id <= 0 ) {
                        $post_id = get_the_ID();
                    }

                    if ( $post_id <= 0 ) {
                        return '';
                    }

                    $default = $atts['default'] !== '' ? (string) $atts['default'] : get_the_title( $post_id );
                    return esc_html( json_i18n_translate_field( 'post', $post_id, 'title', $default, (string) $atts['lang'] ) );
                } );

                add_shortcode( 'i18n_post_excerpt', function( $atts ) {
                    $atts = shortcode_atts( [
                        'id'      => 0,
                        'default' => '',
                        'lang'    => '',
                    ], (array) $atts );

                    $post_id = (int) $atts['id'];
                    if ( $post_id <= 0 ) {
                        $post_id = get_the_ID();
                    }

                    if ( $post_id <= 0 ) {
                        return '';
                    }

                    $default = $atts['default'] !== '' ? (string) $atts['default'] : get_the_excerpt( $post_id );
                    return esc_html( json_i18n_translate_field( 'post', $post_id, 'excerpt', $default, (string) $atts['lang'] ) );
                } );

                add_shortcode( 'i18n_post_content', function( $atts ) {
                    $atts = shortcode_atts( [
                        'id'      => 0,
                        'default' => '',
                        'lang'    => '',
                    ], (array) $atts );

                    $post_id = (int) $atts['id'];
                    if ( $post_id <= 0 ) {
                        $post_id = get_the_ID();
                    }

                    if ( $post_id <= 0 ) {
                        return '';
                    }

                    $default = $atts['default'] !== '' ? (string) $atts['default'] : (string) get_post_field( 'post_content', $post_id );
                    return wp_kses_post( json_i18n_translate_field( 'post', $post_id, 'content', $default, (string) $atts['lang'] ) );
                } );
        }
}
