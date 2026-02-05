<?php

namespace I18nTranslate\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Page with Language Progress Cards
 */
final class DashboardPage {

	/**
	 * Render dashboard
	 */
	public function render(): void {
		$languages = json_i18n_get_available_languages();
		$total_strings = $this->count_total_strings();
		?>
		<div class="wrap i18n-dashboard">
			<h1><?php esc_html_e( 'i18n Translate Dashboard', 'i18n-translate' ); ?></h1>
			
			<div class="i18n-dashboard-header">
				<p><?php esc_html_e( 'Manage your multilingual website translations.', 'i18n-translate' ); ?></p>
			</div>

			<div class="i18n-language-cards">
				<?php foreach ( $languages as $code => $data ) : 
					$translated = $this->count_translated( $code );
					$percent = $total_strings > 0 ? round( ( $translated / $total_strings ) * 100 ) : 0;
					$flag = $data['flag'] ?? '🌐';
					$name = $data['name'] ?? $code;
				?>
				<div class="i18n-language-card">
					<div class="i18n-card-flag"><?php echo esc_html( $flag ); ?></div>
					<div class="i18n-card-name"><?php echo esc_html( $name ); ?></div>
					<div class="i18n-card-count">(<?php echo esc_html( $translated ); ?>/<?php echo esc_html( $total_strings ); ?>)</div>
					<div class="i18n-card-progress">
						<div class="i18n-progress-bar" style="width: <?php echo esc_attr( $percent ); ?>%"></div>
					</div>
					<div class="i18n-card-percent"><?php echo esc_html( $percent ); ?>%</div>
				</div>
				<?php endforeach; ?>
			</div>

			<div class="i18n-quick-links">
				<h2><?php esc_html_e( 'Quick Actions', 'i18n-translate' ); ?></h2>
				<div class="i18n-links-grid">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=i18n-translate' ) ); ?>" class="i18n-link-card">
						<span class="dashicons dashicons-flag"></span>
						<span><?php esc_html_e( 'Languages', 'i18n-translate' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=i18n-translate-translations' ) ); ?>" class="i18n-link-card">
						<span class="dashicons dashicons-edit"></span>
						<span><?php esc_html_e( 'Translations', 'i18n-translate' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=i18n-translate-content' ) ); ?>" class="i18n-link-card">
						<span class="dashicons dashicons-media-document"></span>
						<span><?php esc_html_e( 'Content', 'i18n-translate' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=i18n-translate-scanner' ) ); ?>" class="i18n-link-card">
						<span class="dashicons dashicons-search"></span>
						<span><?php esc_html_e( 'Scan Strings', 'i18n-translate' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=i18n-translate-import-export' ) ); ?>" class="i18n-link-card">
						<span class="dashicons dashicons-download"></span>
						<span><?php esc_html_e( 'Import / Export', 'i18n-translate' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=i18n-translate-settings' ) ); ?>" class="i18n-link-card">
						<span class="dashicons dashicons-admin-settings"></span>
						<span><?php esc_html_e( 'Settings', 'i18n-translate' ); ?></span>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=i18n-translate-usage' ) ); ?>" class="i18n-link-card">
						<span class="dashicons dashicons-book"></span>
						<span><?php esc_html_e( 'Usage Guide', 'i18n-translate' ); ?></span>
					</a>
				</div>
			</div>

			<div class="i18n-quick-links" style="margin-top: 30px;">
				<h2><?php esc_html_e( 'Content Coverage', 'i18n-translate' ); ?></h2>
				<div class="i18n-content-coverage">
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Language', 'i18n-translate' ); ?></th>
								<th><?php esc_html_e( 'Posts (Title)', 'i18n-translate' ); ?></th>
								<th><?php esc_html_e( 'Pages (Title)', 'i18n-translate' ); ?></th>
								<?php if ( class_exists( 'WooCommerce' ) ) : ?>
								<th><?php esc_html_e( 'Products (Title)', 'i18n-translate' ); ?></th>
								<?php endif; ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $languages as $code => $data ) : ?>
								<?php
									$post_total = $this->count_content_total( 'post' );
									$page_total = $this->count_content_total( 'page' );
									$product_total = class_exists( 'WooCommerce' ) ? $this->count_content_total( 'product' ) : 0;
									$post_translated = $this->count_field_translated( 'post', 'title', $code );
									$page_translated = $this->count_field_translated( 'page', 'title', $code );
									$product_translated = class_exists( 'WooCommerce' ) ? $this->count_field_translated( 'product', 'title', $code ) : 0;
								?>
								<tr>
									<td><?php echo esc_html( ( $data['flag'] ?? '🌐' ) . ' ' . ( $data['name'] ?? $code ) ); ?></td>
									<td><?php echo esc_html( $post_translated . '/' . $post_total ); ?></td>
									<td><?php echo esc_html( $page_translated . '/' . $page_total ); ?></td>
									<?php if ( class_exists( 'WooCommerce' ) ) : ?>
									<td><?php echo esc_html( $product_translated . '/' . $product_total ); ?></td>
									<?php endif; ?>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<style>
		.i18n-dashboard {
			max-width: 1200px;
		}
		.i18n-dashboard-header {
			margin: 20px 0;
		}
		.i18n-language-cards {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
			gap: 20px;
			margin: 30px 0;
		}
		.i18n-language-card {
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 12px;
			padding: 24px 20px;
			text-align: center;
			transition: all 0.2s;
		}
		.i18n-language-card:hover {
			box-shadow: 0 4px 12px rgba(0,0,0,0.1);
			transform: translateY(-2px);
		}
		.i18n-card-flag {
			font-size: 48px;
			line-height: 1;
			margin-bottom: 12px;
		}
		.i18n-card-name {
			font-size: 16px;
			font-weight: 600;
			color: #1d2327;
			margin-bottom: 4px;
		}
		.i18n-card-count {
			font-size: 13px;
			color: #646970;
			margin-bottom: 12px;
		}
		.i18n-card-progress {
			background: #e5e5e5;
			border-radius: 10px;
			height: 8px;
			overflow: hidden;
			margin-bottom: 8px;
		}
		.i18n-progress-bar {
			background: linear-gradient(90deg, #2271b1, #135e96);
			height: 100%;
			border-radius: 10px;
			transition: width 0.3s;
		}
		.i18n-card-percent {
			font-size: 14px;
			font-weight: 600;
			color: #2271b1;
		}
		.i18n-quick-links {
			margin-top: 40px;
		}
		.i18n-links-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
			gap: 16px;
			margin-top: 20px;
		}
		.i18n-link-card {
			display: flex;
			align-items: center;
			gap: 12px;
			padding: 16px 20px;
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 8px;
			text-decoration: none;
			color: #1d2327;
			transition: all 0.2s;
		}
		.i18n-link-card:hover {
			background: #f6f7f7;
			border-color: #2271b1;
			color: #2271b1;
		}
		.i18n-link-card .dashicons {
			font-size: 24px;
			width: 24px;
			height: 24px;
		}
		</style>
		<?php
	}

	/**
	 * Count total translation strings
	 */
	private function count_total_strings(): int {
		global $wpdb;
		$table = $wpdb->prefix . 'i18n_strings';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		}
		
		return 0;
	}

	/**
	 * Count translated strings for a language
	 */
	private function count_translated( string $lang ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'i18n_translations';
		
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
			return (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE lang_code = %s AND translation_text != ''",
				$lang
			) );
		}
		
		return 0;
	}

	private function count_content_total( string $post_type ): int {
		$post_type = sanitize_key( $post_type );
		if ( $post_type === '' ) {
			return 0;
		}
		$count = wp_count_posts( $post_type );
		if ( ! $count ) {
			return 0;
		}
		return (int) ( $count->publish ?? 0 );
	}

	private function count_field_translated( string $object_type, string $field_key, string $lang ): int {
		global $wpdb;
		$table = $wpdb->prefix . 'i18n_field_translations';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) !== $table ) {
			return 0;
		}
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE object_type = %s AND field_key = %s AND lang_code = %s AND translation_text != ''",
			$object_type,
			$field_key,
			$lang
		) );
	}
}
