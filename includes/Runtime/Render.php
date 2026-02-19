<?php

namespace I18nTranslate\Runtime;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Render {
	public function language_switcher( string $style = 'dropdown', bool $show_flags = true, bool $show_names = true ): string {
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

		if ( $style === 'dropdown' ) {
			$html = '<select class="language-switcher" aria-label="' . esc_attr__( 'Language selector', 'i18n-translate' ) . '">';
			foreach ( $languages as $code => $lang ) {
				$label = '';
				if ( $show_flags && ! empty( $lang['flag'] ) ) {
					$label .= $lang['flag'] . ' ';
				}
				if ( $show_names ) {
					$label .= $lang['native_name'] ?? $lang['name'] ?? $code;
				} else {
					$label .= strtoupper( $code );
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

		// flags/buttons: render links that keep existing query args.
		$items = [];
		foreach ( $languages as $code => $lang ) {
			$url = add_query_arg( 'i18n_lang', $code );
			$text = $show_flags && ! empty( $lang['flag'] ) ? $lang['flag'] : '';
			if ( $show_names ) {
				$text .= ( $text !== '' ? ' ' : '' ) . ( $lang['native_name'] ?? $lang['name'] ?? $code );
			}
			$class = 'language-switcher-link' . ( $code === $current ? ' is-active' : '' );
			$items[] = '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
		}

		$wrapper_class = $style === 'flags' ? 'language-switcher-flags' : 'language-switcher-buttons';
		return '<div class="' . esc_attr( $wrapper_class ) . '">' . implode( ' ', $items ) . '</div>';
	}
}
