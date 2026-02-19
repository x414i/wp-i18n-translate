<?php

namespace I18nTranslate\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * String Scanner Page
 */
final class ScannerPage {

	/**
	 * Patterns to detect translation function calls
	 */
	private array $patterns = [
		'/__t\s*\(\s*[\'"]([^\'"]+)[\'"]/m',
		'/\besc_html__t\s*\(\s*[\'"]([^\'"]+)[\'"]/m',
	];

	/**
	 * File extensions to scan
	 */
	private array $extensions = [ 'php', 'twig', 'html' ];

	/**
	 * Detected keys
	 */
	private array $detected_keys = [];

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_post_i18n_scan_strings', [ $this, 'handle_scan' ] );
		add_action( 'admin_post_i18n_import_scanned', [ $this, 'handle_import' ] );
	}

	/**
	 * Render scanner page
	 */
	public function render(): void {
		$cached_results = get_transient( 'i18n_scanner_results' );
		$existing_keys = $this->get_existing_keys();
		$imported = isset( $_GET['imported'] ) ? (int) $_GET['imported'] : null;
		?>
		<div class="wrap i18n-scanner-wrap">
			<h1><?php esc_html_e( 'String Scanner', 'i18n-translate' ); ?></h1>
			<p><?php esc_html_e( 'Scan your theme and plugin files to detect __t() translation keys.', 'i18n-translate' ); ?></p>

			<?php if ( $imported !== null ) : ?>
				<div class="notice notice-success"><p><?php printf( esc_html__( 'Imported %d new keys.', 'i18n-translate' ), $imported ); ?></p></div>
			<?php endif; ?>

			<div class="i18n-scan-options">
				<h2><?php esc_html_e( 'Scan Options', 'i18n-translate' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="i18n_scan_strings">
					<?php wp_nonce_field( 'i18n_scan_strings', 'i18n_scan_nonce' ); ?>
					
					<table class="form-table">
						<tr>
							<th scope="row">
								<label for="scan_path"><?php esc_html_e( 'Scan Path', 'i18n-translate' ); ?></label>
							</th>
							<td>
								<select name="scan_path" id="scan_path">
									<option value="theme"><?php esc_html_e( 'Current Theme', 'i18n-translate' ); ?></option>
									<option value="plugins"><?php esc_html_e( 'All Plugins', 'i18n-translate' ); ?></option>
									<option value="both"><?php esc_html_e( 'Theme & Plugins', 'i18n-translate' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
					
					<?php submit_button( __( 'Scan for Translation Keys', 'i18n-translate' ), 'primary', 'scan_submit' ); ?>
				</form>
			</div>

			<?php if ( $cached_results && ! empty( $cached_results['keys'] ) ) : ?>
				<?php 
				$new_count = 0;
				foreach ( $cached_results['keys'] as $key => $info ) {
					if ( ! in_array( $key, $existing_keys, true ) ) {
						$new_count++;
					}
				}
				?>
				<div class="i18n-scanner-results">
					<div class="i18n-stats">
						<div class="i18n-stat-box">
							<div class="i18n-stat-number"><?php echo count( $cached_results['keys'] ); ?></div>
							<div class="i18n-stat-label"><?php esc_html_e( 'Keys Found', 'i18n-translate' ); ?></div>
						</div>
						<div class="i18n-stat-box" style="border-color: #00a32a;">
							<div class="i18n-stat-number" style="color: #00a32a;"><?php echo $new_count; ?></div>
							<div class="i18n-stat-label"><?php esc_html_e( 'New Keys', 'i18n-translate' ); ?></div>
						</div>
						<div class="i18n-stat-box">
							<div class="i18n-stat-number"><?php echo count( $cached_results['files'] ); ?></div>
							<div class="i18n-stat-label"><?php esc_html_e( 'Files Scanned', 'i18n-translate' ); ?></div>
						</div>
					</div>

					<?php if ( $new_count > 0 ) : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="i18n_import_scanned">
							<?php wp_nonce_field( 'i18n_import_scanned', 'i18n_import_nonce' ); ?>
							
							<h2><?php esc_html_e( 'Detected Keys', 'i18n-translate' ); ?></h2>
							
							<table class="wp-list-table widefat fixed striped">
								<thead>
									<tr>
										<th style="width: 30px;"><input type="checkbox" id="select-all-keys"></th>
										<th><?php esc_html_e( 'Key', 'i18n-translate' ); ?></th>
										<th><?php esc_html_e( 'Found In', 'i18n-translate' ); ?></th>
										<th><?php esc_html_e( 'Status', 'i18n-translate' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $cached_results['keys'] as $key => $info ) : 
										$exists = in_array( $key, $existing_keys, true );
									?>
										<tr>
											<td>
												<?php if ( ! $exists ) : ?>
													<input type="checkbox" name="import_keys[]" value="<?php echo esc_attr( $key ); ?>" class="key-checkbox" checked>
												<?php endif; ?>
											</td>
											<td><code><?php echo esc_html( $key ); ?></code></td>
											<td>
												<?php
												$files = array_unique( $info['files'] );
												foreach ( array_slice( $files, 0, 2 ) as $file ) {
													echo '<small>' . esc_html( basename( $file ) ) . '</small><br>';
												}
												if ( count( $files ) > 2 ) {
													echo '<small>+' . ( count( $files ) - 2 ) . ' more</small>';
												}
												?>
											</td>
											<td>
												<?php if ( $exists ) : ?>
													<span style="color: #787c82;">✓ <?php esc_html_e( 'Exists', 'i18n-translate' ); ?></span>
												<?php else : ?>
													<span style="color: #00a32a;">⚡ <?php esc_html_e( 'New', 'i18n-translate' ); ?></span>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							
							<?php submit_button( __( 'Import Selected Keys', 'i18n-translate' ), 'primary', 'import_submit' ); ?>
						</form>
						
						<script>
						document.getElementById('select-all-keys').addEventListener('change', function() {
							var checkboxes = document.querySelectorAll('.key-checkbox');
							for (var i = 0; i < checkboxes.length; i++) {
								checkboxes[i].checked = this.checked;
							}
						});
						</script>
					<?php else : ?>
						<div class="notice notice-success"><p><?php esc_html_e( 'All detected keys already exist!', 'i18n-translate' ); ?></p></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<style>
		.i18n-scanner-wrap { max-width: 1000px; }
		.i18n-scan-options { background: #fff; padding: 20px; border: 1px solid #ddd; margin-bottom: 20px; }
		.i18n-stats { display: flex; gap: 20px; margin-bottom: 24px; }
		.i18n-stat-box { background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; text-align: center; min-width: 120px; }
		.i18n-stat-number { font-size: 32px; font-weight: 700; color: #2271b1; }
		.i18n-stat-label { font-size: 13px; color: #666; margin-top: 4px; }
		</style>
		<?php
	}

	/**
	 * Handle scan
	 */
	public function handle_scan(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'i18n_scan_strings', 'i18n_scan_nonce' );

		$scan_path = isset( $_POST['scan_path'] ) ? sanitize_text_field( $_POST['scan_path'] ) : 'theme';

		$directories = [];
		switch ( $scan_path ) {
			case 'theme':
				$directories[] = get_template_directory();
				break;
			case 'plugins':
				$directories[] = WP_PLUGIN_DIR;
				break;
			case 'both':
				$directories[] = get_template_directory();
				$directories[] = WP_PLUGIN_DIR;
				break;
		}

		$this->detected_keys = [];
		$scanned_files = [];

		foreach ( $directories as $dir ) {
			$result = $this->scan_directory( $dir );
			$scanned_files = array_merge( $scanned_files, $result['files'] );
		}

		set_transient( 'i18n_scanner_results', [
			'keys'  => $this->detected_keys,
			'files' => array_unique( $scanned_files ),
		], HOUR_IN_SECONDS );

		wp_redirect( admin_url( 'admin.php?page=i18n-translate-scanner&scanned=1' ) );
		exit;
	}

	/**
	 * Scan directory
	 */
	private function scan_directory( string $directory ): array {
		$files = [];

		if ( ! is_dir( $directory ) || ! is_readable( $directory ) ) {
			return [ 'files' => $files ];
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			$path = $file->getPathname();
			if ( strpos( $path, '/vendor/' ) !== false ||
				 strpos( $path, '/node_modules/' ) !== false ||
				 strpos( $path, '/.git/' ) !== false ) {
				continue;
			}

			if ( $file->isFile() ) {
				$ext = strtolower( $file->getExtension() );
				if ( in_array( $ext, $this->extensions, true ) ) {
					$this->scan_file( $path );
					$files[] = $path;
				}
			}
		}

		return [ 'files' => $files ];
	}

	/**
	 * Scan file
	 */
	private function scan_file( string $file_path ): void {
		$content = file_get_contents( $file_path );
		if ( false === $content ) {
			return;
		}

		foreach ( $this->patterns as $pattern ) {
			if ( preg_match_all( $pattern, $content, $matches ) ) {
				foreach ( $matches[1] as $key ) {
					if ( strpos( $key, '.' ) !== false || strpos( $key, '_' ) !== false ) {
						if ( ! isset( $this->detected_keys[ $key ] ) ) {
							$this->detected_keys[ $key ] = [ 'files' => [], 'count' => 0 ];
						}
						$this->detected_keys[ $key ]['files'][] = $file_path;
						$this->detected_keys[ $key ]['count']++;
					}
				}
			}
		}
	}

	/**
	 * Handle import
	 */
	public function handle_import(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_admin_referer( 'i18n_import_scanned', 'i18n_import_nonce' );

		$keys = isset( $_POST['import_keys'] ) ? array_map( 'sanitize_text_field', $_POST['import_keys'] ) : [];

		$imported = 0;
		foreach ( $keys as $key ) {
			if ( $this->add_key( $key ) ) {
				$imported++;
			}
		}

		delete_transient( 'i18n_scanner_results' );

		wp_redirect( admin_url( 'admin.php?page=i18n-translate-scanner&imported=' . $imported ) );
		exit;
	}

	/**
	 * Get existing keys
	 */
	private function get_existing_keys(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'i18n_strings';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			return [];
		}

		$keys = $wpdb->get_col( "SELECT string_key FROM $table" );
		return $keys ?: [];
	}

	/**
	 * Add key
	 */
	private function add_key( string $key ): bool {
		global $wpdb;
		$table = $wpdb->prefix . 'i18n_strings';
		$domain = 'scanned';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			return false;
		}

		$exists = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table WHERE string_key = %s",
			$key
		) );

		if ( $exists ) {
			return false;
		}

		$result = $wpdb->insert( $table, [
			'domain'       => $domain,
			'string_key'   => $key,
			'default_text' => '',
		] );

		return $result !== false;
	}
}
