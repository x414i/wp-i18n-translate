<?php

namespace I18nTranslate\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import/Export Page
 */
final class ImportExportPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'handle_export' ] );
		add_action( 'admin_init', [ $this, 'handle_import' ] );
	}

	/**
	 * Render page
	 */
	public function render(): void {
		$stats = $this->get_stats();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import / Export', 'i18n-translate' ); ?></h1>
			
			<?php if ( isset( $_GET['exported'] ) ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Export completed successfully.', 'i18n-translate' ); ?></p></div>
			<?php endif; ?>
			
			<?php if ( isset( $_GET['imported'] ) ) : ?>
				<div class="notice notice-success"><p><?php printf( esc_html__( 'Imported %d items.', 'i18n-translate' ), intval( $_GET['imported'] ) ); ?></p></div>
			<?php endif; ?>
			
			<?php if ( isset( $_GET['error'] ) ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( urldecode( $_GET['error'] ) ); ?></p></div>
			<?php endif; ?>

			<div class="i18n-import-export">
				<!-- Export Panel -->
				<div class="i18n-panel">
					<h2><?php esc_html_e( 'Export Translations', 'i18n-translate' ); ?></h2>
					<p><?php esc_html_e( 'Download all translation data as a JSON file.', 'i18n-translate' ); ?></p>
					
					<div class="i18n-stats">
						<div class="i18n-stat-row">
							<span><?php esc_html_e( 'Languages:', 'i18n-translate' ); ?></span>
							<strong><?php echo esc_html( $stats['languages'] ); ?></strong>
						</div>
						<div class="i18n-stat-row">
							<span><?php esc_html_e( 'Translation Keys:', 'i18n-translate' ); ?></span>
							<strong><?php echo esc_html( $stats['strings'] ); ?></strong>
						</div>
						<div class="i18n-stat-row">
							<span><?php esc_html_e( 'Total Translations:', 'i18n-translate' ); ?></span>
							<strong><?php echo esc_html( $stats['translations'] ); ?></strong>
						</div>
						<div class="i18n-stat-row">
							<span><?php esc_html_e( 'Content Translations:', 'i18n-translate' ); ?></span>
							<strong><?php echo esc_html( $stats['field_translations'] ); ?></strong>
						</div>
					</div>
					
					<form method="post">
						<?php wp_nonce_field( 'i18n_export', 'i18n_export_nonce' ); ?>
						
						<p>
							<label>
								<input type="checkbox" name="include_strings" value="1" checked>
								<?php esc_html_e( 'Include string keys', 'i18n-translate' ); ?>
							</label>
						</p>
						<p>
							<label>
								<input type="checkbox" name="include_translations" value="1" checked>
								<?php esc_html_e( 'Include translations', 'i18n-translate' ); ?>
							</label>
						</p>
						<p>
							<label>
								<input type="checkbox" name="include_languages" value="1" checked>
								<?php esc_html_e( 'Include language settings', 'i18n-translate' ); ?>
							</label>
						</p>
						<p>
							<label>
								<input type="checkbox" name="include_field_translations" value="1" checked>
								<?php esc_html_e( 'Include content translations', 'i18n-translate' ); ?>
							</label>
						</p>
						
						<?php submit_button( __( 'Download Export', 'i18n-translate' ), 'primary', 'i18n_export' ); ?>
					</form>
				</div>

				<!-- Import Panel -->
				<div class="i18n-panel">
					<h2><?php esc_html_e( 'Import Translations', 'i18n-translate' ); ?></h2>
					<p><?php esc_html_e( 'Upload a JSON file to import translations.', 'i18n-translate' ); ?></p>
					
					<form method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'i18n_import', 'i18n_import_nonce' ); ?>
						
						<p>
							<label for="import_file"><strong><?php esc_html_e( 'Select file:', 'i18n-translate' ); ?></strong></label><br>
							<input type="file" name="import_file" id="import_file" accept=".json" required>
						</p>
						
						<p>
							<label>
								<input type="checkbox" name="overwrite" value="1">
								<?php esc_html_e( 'Overwrite existing translations', 'i18n-translate' ); ?>
							</label>
						</p>
						
						<?php submit_button( __( 'Upload and Import', 'i18n-translate' ), 'secondary', 'i18n_import' ); ?>
					</form>
				</div>
			</div>
		</div>

		<style>
		.i18n-import-export { display: flex; gap: 30px; flex-wrap: wrap; }
		.i18n-panel { background: #fff; border: 1px solid #ddd; padding: 20px; flex: 1; min-width: 300px; }
		.i18n-panel h2 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; }
		.i18n-stats { background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 4px; }
		.i18n-stat-row { display: flex; justify-content: space-between; padding: 5px 0; }
		</style>
		<?php
	}

	/**
	 * Handle export
	 */
	public function handle_export(): void {
		if ( ! isset( $_POST['i18n_export'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['i18n_export_nonce'], 'i18n_export' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $wpdb;
		$export_data = [
			'version'  => I18N_TRANSLATE_VERSION,
			'site'     => get_site_url(),
			'exported' => current_time( 'mysql' ),
		];

		// Export strings
		if ( ! empty( $_POST['include_strings'] ) ) {
			$table = $wpdb->prefix . 'i18n_strings';
			$export_data['strings'] = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A ) ?: [];
		}

		// Export translations
		if ( ! empty( $_POST['include_translations'] ) ) {
			$table = $wpdb->prefix . 'i18n_translations';
			$export_data['translations'] = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A ) ?: [];
		}

		// Export languages
		if ( ! empty( $_POST['include_languages'] ) ) {
			$table = $wpdb->prefix . 'i18n_languages';
			$export_data['languages'] = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A ) ?: [];
		}

		// Export content/field translations
		if ( ! empty( $_POST['include_field_translations'] ) ) {
			$table = $wpdb->prefix . 'i18n_field_translations';
			$export_data['field_translations'] = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A ) ?: [];
		}

		$filename = 'i18n-translations-' . date( 'Y-m-d' ) . '.json';
		
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		
		echo wp_json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		exit;
	}

	/**
	 * Handle import
	 */
	public function handle_import(): void {
		if ( ! isset( $_POST['i18n_import'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['i18n_import_nonce'], 'i18n_import' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $_FILES['import_file']['tmp_name'] ) ) {
			wp_redirect( add_query_arg( 'error', urlencode( 'No file uploaded' ), admin_url( 'admin.php?page=i18n-translate-import-export' ) ) );
			exit;
		}

		$content = file_get_contents( $_FILES['import_file']['tmp_name'] );
		$data = json_decode( $content, true );

		if ( ! $data || ! isset( $data['version'] ) ) {
			wp_redirect( add_query_arg( 'error', urlencode( 'Invalid file format' ), admin_url( 'admin.php?page=i18n-translate-import-export' ) ) );
			exit;
		}

		global $wpdb;
		$overwrite = ! empty( $_POST['overwrite'] );
		$count = 0;

		// Import strings
		if ( ! empty( $data['strings'] ) ) {
			$table = $wpdb->prefix . 'i18n_strings';
			foreach ( $data['strings'] as $row ) {
				$domain = isset( $row['domain'] ) ? $row['domain'] : 'default';
				$exists = $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM $table WHERE domain = %s AND string_key = %s",
					$domain,
					$row['string_key']
				) );

				if ( ! $exists ) {
					$wpdb->insert( $table, [
						'domain'       => $domain,
						'string_key'   => $row['string_key'],
						'default_text' => $row['default_text'] ?? '',
					] );
					$count++;
				} elseif ( $overwrite ) {
					$wpdb->update( $table, [
						'default_text' => $row['default_text'] ?? '',
					], [
						'domain'     => $domain,
						'string_key' => $row['string_key'],
					] );
					$count++;
				}
			}
		}

		// Import translations
		if ( ! empty( $data['translations'] ) ) {
			$table = $wpdb->prefix . 'i18n_translations';
			foreach ( $data['translations'] as $row ) {
				$exists = $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM $table WHERE string_id = %d AND lang_code = %s",
					$row['string_id'],
					$row['lang_code']
				) );

				if ( ! $exists ) {
					$wpdb->insert( $table, [
						'string_id'        => $row['string_id'],
						'lang_code'        => $row['lang_code'],
						'translation_text' => $row['translation_text'] ?? '',
					] );
					$count++;
				} elseif ( $overwrite ) {
					$wpdb->update( $table, 
						[ 'translation_text' => $row['translation_text'] ?? '' ],
						[ 'string_id' => $row['string_id'], 'lang_code' => $row['lang_code'] ]
					);
					$count++;
				}
			}
		}

		// Import content translations
		if ( ! empty( $data['field_translations'] ) ) {
			$table = $wpdb->prefix . 'i18n_field_translations';
			foreach ( $data['field_translations'] as $row ) {
				$exists = $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM $table WHERE object_type = %s AND object_id = %d AND field_key = %s AND lang_code = %s",
					$row['object_type'],
					(int) $row['object_id'],
					$row['field_key'],
					$row['lang_code']
				) );

				if ( ! $exists ) {
					$wpdb->insert( $table, [
						'object_type'      => $row['object_type'],
						'object_id'        => (int) $row['object_id'],
						'field_key'        => $row['field_key'],
						'lang_code'        => $row['lang_code'],
						'translation_text' => $row['translation_text'] ?? '',
						'updated_at'       => current_time( 'mysql' ),
					] );
					$count++;
				} elseif ( $overwrite ) {
					$wpdb->update( $table,
						[ 'translation_text' => $row['translation_text'] ?? '', 'updated_at' => current_time( 'mysql' ) ],
						[ 'object_type' => $row['object_type'], 'object_id' => (int) $row['object_id'], 'field_key' => $row['field_key'], 'lang_code' => $row['lang_code'] ]
					);
					$count++;
				}
			}
		}

		wp_redirect( add_query_arg( 'imported', $count, admin_url( 'admin.php?page=i18n-translate-import-export' ) ) );
		exit;
	}

	/**
	 * Get stats
	 */
	private function get_stats(): array {
		global $wpdb;
		
		$languages = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}i18n_languages WHERE enabled = 1" ) ?: 0;
		$strings = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}i18n_strings" ) ?: 0;
		$translations = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}i18n_translations" ) ?: 0;
		$field_translations = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}i18n_field_translations" ) ?: 0;

		return [
			'languages'    => (int) $languages,
			'strings'      => (int) $strings,
			'translations' => (int) $translations,
			'field_translations' => (int) $field_translations,
		];
	}
}
