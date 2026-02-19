<?php

namespace I18nTranslate\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class ContentPage {
	public function render(): void {
		if ( ! current_user_can( 'i18n_translate_translate' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'i18n-translate' ) );
		}

		$languages = json_i18n_get_available_languages();
		$current_lang = '';
		if ( isset( $_GET['i18n_lang'] ) ) {
			$current_lang = sanitize_key( wp_unslash( $_GET['i18n_lang'] ) );
		} elseif ( isset( $_GET['lang'] ) ) {
			$current_lang = sanitize_key( wp_unslash( $_GET['lang'] ) );
		} else {
			$current_lang = json_i18n_get_current_language();
		}
		if ( ! isset( $languages[ $current_lang ] ) ) {
			$current_lang = json_i18n_get_current_language();
		}

		$mode = isset( $_GET['mode'] ) ? sanitize_key( wp_unslash( $_GET['mode'] ) ) : 'posts';
		if ( ! in_array( $mode, [ 'posts', 'terms' ], true ) ) {
			$mode = 'posts';
		}

		$post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'post';
		$available_post_types = $this->get_supported_post_types();
		if ( ! isset( $available_post_types[ $post_type ] ) ) {
			$post_type = 'post';
		}

		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_key( wp_unslash( $_GET['taxonomy'] ) ) : 'category';
		$available_taxonomies = $this->get_supported_taxonomies();
		if ( ! isset( $available_taxonomies[ $taxonomy ] ) ) {
			$taxonomy = 'category';
		}

		$paged = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
		$per_page = 10;

		$query = null;
		$terms = [];
		$total_pages = 1;
		if ( $mode === 'posts' ) {
			$query = new \WP_Query( [
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => $per_page,
				'paged'          => $paged,
				'no_found_rows'  => false,
			] );
			$total_pages = (int) $query->max_num_pages;
		} else {
			$term_query = new \WP_Term_Query( [
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'number'     => $per_page,
				'offset'     => ( $paged - 1 ) * $per_page,
			] );
			$terms = is_array( $term_query->terms ) ? $term_query->terms : [];
			$total_terms = (int) ( $term_query->found_terms ?? 0 );
			$total_pages = $total_terms > 0 ? (int) ceil( $total_terms / $per_page ) : 1;
		}
		?>
		<div class="wrap i18n-admin i18n-content-page">
			<h1><?php esc_html_e( 'Content Translations', 'i18n-translate' ); ?></h1>

			<div class="i18n-toolbar" style="margin: 20px 0;">
				<form method="get" action="" style="display:flex; gap: 12px; align-items:center;">
					<input type="hidden" name="page" value="i18n-translate-content" />
					<label>
						<span class="screen-reader-text"><?php esc_html_e( 'Mode', 'i18n-translate' ); ?></span>
						<select name="mode">
							<option value="posts" <?php selected( $mode, 'posts' ); ?>><?php esc_html_e( 'Posts', 'i18n-translate' ); ?></option>
							<option value="terms" <?php selected( $mode, 'terms' ); ?>><?php esc_html_e( 'Taxonomies', 'i18n-translate' ); ?></option>
						</select>
					</label>
					<label>
						<span class="screen-reader-text"><?php esc_html_e( 'Language', 'i18n-translate' ); ?></span>
						<select name="i18n_lang">
							<?php foreach ( $languages as $code => $lang ) : ?>
								<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $current_lang, $code ); ?>>
									<?php echo esc_html( ( $lang['flag'] ?? '🌐' ) . ' ' . ( $lang['name'] ?? $code ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>
					<?php if ( $mode === 'posts' ) : ?>
						<label>
							<span class="screen-reader-text"><?php esc_html_e( 'Post Type', 'i18n-translate' ); ?></span>
							<select name="post_type">
								<?php foreach ( $available_post_types as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $post_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php else : ?>
						<label>
							<span class="screen-reader-text"><?php esc_html_e( 'Taxonomy', 'i18n-translate' ); ?></span>
							<select name="taxonomy">
								<?php foreach ( $available_taxonomies as $key => $label ) : ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $taxonomy, $key ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					<?php endif; ?>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Filter', 'i18n-translate' ); ?></button>
				</form>
			</div>

			<?php if ( $mode === 'posts' ) : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th style="width: 20%;"><?php esc_html_e( 'Original Title', 'i18n-translate' ); ?></th>
							<th style="width: 20%;"><?php esc_html_e( 'Translated Title', 'i18n-translate' ); ?></th>
							<th style="width: 25%;"><?php esc_html_e( 'Translated Excerpt', 'i18n-translate' ); ?></th>
							<th style="width: 25%;"><?php esc_html_e( 'Translated Content', 'i18n-translate' ); ?></th>
							<th style="width: 10%;"><?php esc_html_e( 'Actions', 'i18n-translate' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if ( $query && $query->have_posts() ) : ?>
						<?php while ( $query->have_posts() ) : $query->the_post(); ?>
							<?php
								$post_id = get_the_ID();
								$translations = json_i18n_get_field_translations( 'post', $post_id, [ 'title', 'excerpt', 'content' ], $current_lang );
							?>
							<tr data-object-id="<?php echo esc_attr( $post_id ); ?>" data-object-type="post">
								<td>
									<strong><?php echo esc_html( get_the_title( $post_id ) ); ?></strong>
									<div class="i18n-text-muted" style="font-size: 12px; color: #6c7781;">#<?php echo esc_html( $post_id ); ?></div>
								</td>
								<td>
									<input type="text" class="widefat i18n-field" data-field="title" value="<?php echo esc_attr( $translations['title'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Translated title', 'i18n-translate' ); ?>" />
								</td>
								<td>
									<textarea class="widefat i18n-field" data-field="excerpt" rows="3" placeholder="<?php esc_attr_e( 'Translated excerpt', 'i18n-translate' ); ?>"><?php echo esc_textarea( $translations['excerpt'] ?? '' ); ?></textarea>
								</td>
								<td>
									<textarea class="widefat i18n-field" data-field="content" rows="4" placeholder="<?php esc_attr_e( 'Translated content', 'i18n-translate' ); ?>"><?php echo esc_textarea( $translations['content'] ?? '' ); ?></textarea>
								</td>
								<td>
									<button type="button" class="button button-secondary i18n-content-save" data-lang="<?php echo esc_attr( $current_lang ); ?>">
										<?php esc_html_e( 'Save', 'i18n-translate' ); ?>
									</button>
									<div class="i18n-save-status" style="font-size: 12px; margin-top: 6px;"></div>
								</td>
							</tr>
						<?php endwhile; ?>
						<?php wp_reset_postdata(); ?>
					<?php else : ?>
						<tr>
							<td colspan="5"><?php esc_html_e( 'No content found.', 'i18n-translate' ); ?></td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th style="width: 25%;"><?php esc_html_e( 'Original Name', 'i18n-translate' ); ?></th>
							<th style="width: 25%;"><?php esc_html_e( 'Translated Name', 'i18n-translate' ); ?></th>
							<th style="width: 30%;"><?php esc_html_e( 'Translated Description', 'i18n-translate' ); ?></th>
							<th style="width: 10%;"><?php esc_html_e( 'Actions', 'i18n-translate' ); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php if ( ! empty( $terms ) ) : ?>
						<?php foreach ( $terms as $term ) : ?>
							<?php
								$term_id = (int) $term->term_id;
								$translations = json_i18n_get_field_translations( 'term', $term_id, [ 'name', 'description' ], $current_lang );
							?>
							<tr data-object-id="<?php echo esc_attr( $term_id ); ?>" data-object-type="term">
								<td>
									<strong><?php echo esc_html( $term->name ); ?></strong>
									<div class="i18n-text-muted" style="font-size: 12px; color: #6c7781;">#<?php echo esc_html( $term_id ); ?></div>
								</td>
								<td>
									<input type="text" class="widefat i18n-field" data-field="name" value="<?php echo esc_attr( $translations['name'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Translated name', 'i18n-translate' ); ?>" />
								</td>
								<td>
									<textarea class="widefat i18n-field" data-field="description" rows="3" placeholder="<?php esc_attr_e( 'Translated description', 'i18n-translate' ); ?>"><?php echo esc_textarea( $translations['description'] ?? '' ); ?></textarea>
								</td>
								<td>
									<button type="button" class="button button-secondary i18n-content-save" data-lang="<?php echo esc_attr( $current_lang ); ?>">
										<?php esc_html_e( 'Save', 'i18n-translate' ); ?>
									</button>
									<div class="i18n-save-status" style="font-size: 12px; margin-top: 6px;"></div>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="4"><?php esc_html_e( 'No terms found.', 'i18n-translate' ); ?></td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<?php
			if ( $total_pages > 1 ) :
				$base_url = remove_query_arg( [ 'paged' ] );
				?>
				<div class="tablenav">
					<div class="tablenav-pages">
						<?php
						echo paginate_links( [
							'base'      => add_query_arg( 'paged', '%#%', $base_url ),
							'format'    => '',
							'current'   => $paged,
							'total'     => $total_pages,
							'prev_text' => '«',
							'next_text' => '»',
						] );
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<script>
			(function() {
				const restUrl = (window.i18nTranslate && i18nTranslate.restUrl) ? i18nTranslate.restUrl : '';
				const restNonce = (window.i18nTranslate && i18nTranslate.restNonce) ? i18nTranslate.restNonce : '';
				if (!restUrl) return;

				document.querySelectorAll('.i18n-content-save').forEach((btn) => {
					btn.addEventListener('click', async () => {
						const row = btn.closest('tr');
						if (!row) return;
						const lang = btn.getAttribute('data-lang');
						const statusEl = row.querySelector('.i18n-save-status');
						const fields = row.querySelectorAll('.i18n-field');
						const objectId = row.getAttribute('data-object-id');
						const objectType = row.getAttribute('data-object-type');

						btn.disabled = true;
						if (statusEl) statusEl.textContent = '<?php echo esc_js( __( 'Saving...', 'i18n-translate' ) ); ?>';

						try {
							for (const field of fields) {
								const fieldKey = field.getAttribute('data-field');
								const translation = field.value || '';
								await fetch(restUrl, {
									method: 'POST',
									credentials: 'same-origin',
									headers: {
										'Content-Type': 'application/json',
										'X-WP-Nonce': restNonce
									},
									body: JSON.stringify({
										object_type: objectType,
										object_id: parseInt(objectId, 10),
										field_key: fieldKey,
										lang: lang,
										translation: translation
									})
								});
							}

							if (statusEl) statusEl.textContent = '<?php echo esc_js( __( 'Saved', 'i18n-translate' ) ); ?>';
						} catch (e) {
							if (statusEl) statusEl.textContent = '<?php echo esc_js( __( 'Save failed', 'i18n-translate' ) ); ?>';
						}

						setTimeout(() => {
							if (statusEl) statusEl.textContent = '';
							btn.disabled = false;
						}, 1200);
					});
				});
			})();
		</script>
		<?php
	}

	private function get_supported_post_types(): array {
		$post_types = get_post_types( [ 'public' => true ], 'objects' );
		$out = [];
		foreach ( $post_types as $key => $obj ) {
			$out[ $key ] = $obj->labels->singular_name ?? $key;
		}

		if ( class_exists( 'WooCommerce' ) && isset( $out['product'] ) ) {
			$out['product'] = __( 'Product', 'i18n-translate' );
		}

		return $out;
	}

	private function get_supported_taxonomies(): array {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$out = [];
		foreach ( $taxonomies as $key => $obj ) {
			$out[ $key ] = $obj->labels->singular_name ?? $key;
		}
		return $out;
	}
}
