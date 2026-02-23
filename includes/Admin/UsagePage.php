<?php

namespace I18nTranslate\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class UsagePage {

	public function render(): void {
		if ( ! current_user_can( 'i18n_translate_translate' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'i18n-translate' ) );
		}
		?>
		<style>
			/* الأنماط كما هي دون تغيير */
			.i18n-usage-wrap { max-width: 1200px; }
			.i18n-usage-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 40px; border-radius: 12px; margin-bottom: 30px; }
			.i18n-usage-header h1 { margin: 0 0 10px; font-size: 32px; font-weight: 600; }
			.i18n-usage-header p { margin: 0; opacity: 0.9; font-size: 16px; }
			.i18n-tabs-nav { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; background: #f6f7f7; padding: 12px; border-radius: 8px; }
			.i18n-tabs-nav button { background: transparent; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; color: #50575e; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
			.i18n-tabs-nav button:hover { background: #fff; color: #2271b1; }
			.i18n-tabs-nav button.active { background: #2271b1; color: #fff; box-shadow: 0 2px 8px rgba(34,113,177,0.3); }
			.i18n-tabs-nav button .dashicons { font-size: 16px; width: 16px; height: 16px; }
			.i18n-section { background: #fff; border-radius: 12px; padding: 32px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
			.i18n-section h2 { margin: 0 0 16px; font-size: 24px; color: #1d2327; display: flex; align-items: center; gap: 12px; }
			.i18n-section h2 .emoji { font-size: 28px; }
			.i18n-section h3 { margin: 24px 0 12px; font-size: 18px; color: #2271b1; border-bottom: 2px solid #f0f0f1; padding-bottom: 8px; }
			.i18n-section h4 { margin: 16px 0 8px; font-size: 15px; color: #1d2327; }
			.i18n-section p, .i18n-section li { color: #50575e; line-height: 1.7; }
			.i18n-section ul, .i18n-section ol { margin: 12px 0; padding-left: 24px; }
			.i18n-section li { margin-bottom: 8px; }
			.i18n-code-box { background: #1d2327; color: #50fa7b; padding: 16px 20px; border-radius: 8px; font-family: 'SF Mono', Monaco, monospace; font-size: 13px; margin: 16px 0; position: relative; overflow-x: auto; line-height: 1.6; }
			.i18n-code-box .copy-btn { position: absolute; top: 8px; right: 8px; background: rgba(255,255,255,0.1); border: none; color: #fff; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 11px; }
			.i18n-code-box .copy-btn:hover { background: rgba(255,255,255,0.2); }
			.i18n-code-box .comment { color: #6272a4; }
			.i18n-code-box .string { color: #f1fa8c; }
			.i18n-code-box .keyword { color: #ff79c6; }
			.i18n-code-box .function { color: #8be9fd; }
			.i18n-info-box { background: #f0f6fc; border-left: 4px solid #2271b1; padding: 16px 20px; border-radius: 0 8px 8px 0; margin: 16px 0; }
			.i18n-info-box.success { background: #edfaef; border-color: #00a32a; }
			.i18n-info-box.warning { background: #fcf9e8; border-color: #dba617; }
			.i18n-info-box.danger { background: #fcf0f1; border-color: #d63638; }
			.i18n-info-box strong { display: block; margin-bottom: 4px; }
			.i18n-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin: 24px 0; }
			.i18n-step { background: #f9fafc; padding: 24px; border-radius: 10px; border: 1px solid #e8eaed; }
			.i18n-step-num { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border-radius: 50%; font-weight: 600; margin-bottom: 12px; }
			.i18n-step h4 { margin: 0 0 8px; font-size: 16px; color: #1d2327; }
			.i18n-step p { margin: 0; font-size: 14px; color: #646970; }
			.i18n-table { width: 100%; border-collapse: collapse; margin: 16px 0; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
			.i18n-table th, .i18n-table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #e0e0e0; }
			.i18n-table th { background: #f6f7f7; font-weight: 600; color: #1d2327; font-size: 13px; }
			.i18n-table td { font-size: 13px; }
			.i18n-table code { background: #f0f0f1; padding: 2px 8px; border-radius: 4px; font-size: 12px; color: #1d2327; }
			.i18n-table tr:last-child td { border-bottom: none; }
			.i18n-accordion { border: 1px solid #e0e0e0; border-radius: 8px; margin: 16px 0; overflow: hidden; }
			.i18n-accordion-item { border-bottom: 1px solid #e0e0e0; }
			.i18n-accordion-item:last-child { border-bottom: none; }
			.i18n-accordion-header { background: #f9fafc; padding: 16px 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 500; }
			.i18n-accordion-header:hover { background: #f0f6fc; }
			.i18n-accordion-content { padding: 20px; display: none; }
			.i18n-accordion-item.open .i18n-accordion-content { display: block; }
			.i18n-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
			@media (max-width: 782px) { .i18n-grid-2 { grid-template-columns: 1fr; } }
			/* Toast notification */
			.i18n-toast { position: fixed; bottom: 30px; right: 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 14px 24px; border-radius: 8px; box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4); font-size: 14px; font-weight: 500; z-index: 9999; animation: i18n-toast-slide 0.3s ease-out; display: flex; align-items: center; gap: 10px; }
			.i18n-toast .dashicons { font-size: 18px; width: 18px; height: 18px; }
			@keyframes i18n-toast-slide { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
			.i18n-toast.fade-out { animation: i18n-toast-fade 0.3s ease-out forwards; }
			@keyframes i18n-toast-fade { to { transform: translateY(20px); opacity: 0; } }
		</style>

		<div class="wrap i18n-usage-wrap" x-data="usageGuide()">
			<div class="i18n-usage-header">
				<h1>📚 <?php esc_html_e( 'Usage Guide', 'i18n-translate' ); ?></h1>
				<p><?php esc_html_e( 'Complete documentation for building multilingual WordPress sites with i18n Translate.', 'i18n-translate' ); ?></p>
			</div>

			<div class="i18n-tabs-nav">
				<button @click="tab = 'start'" :class="{ 'active': tab === 'start' }">
					<span class="dashicons dashicons-flag"></span> <?php esc_html_e( 'Getting Started', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'block'" :class="{ 'active': tab === 'block' }">
					<span class="dashicons dashicons-block-default"></span> <?php esc_html_e( 'Block Editor', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'classic'" :class="{ 'active': tab === 'classic' }">
					<span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Classic Editor', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'builders'" :class="{ 'active': tab === 'builders' }">
					<span class="dashicons dashicons-layout"></span> <?php esc_html_e( 'Page Builders', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'shortcodes'" :class="{ 'active': tab === 'shortcodes' }">
					<span class="dashicons dashicons-editor-code"></span> <?php esc_html_e( 'Shortcodes', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'images'" :class="{ 'active': tab === 'images' }">
					<span class="dashicons dashicons-format-image"></span> <?php esc_html_e( 'Images & Media', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'ecom'" :class="{ 'active': tab === 'ecom' }">
					<span class="dashicons dashicons-cart"></span> <?php esc_html_e( 'eCommerce', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'blogs'" :class="{ 'active': tab === 'blogs' }">
					<span class="dashicons dashicons-admin-post"></span> <?php esc_html_e( 'Blogs & Posts', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'menus'" :class="{ 'active': tab === 'menus' }">
					<span class="dashicons dashicons-menu"></span> <?php esc_html_e( 'Menus', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'php'" :class="{ 'active': tab === 'php' }">
					<span class="dashicons dashicons-editor-code"></span> <?php esc_html_e( 'PHP Reference', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'import'" :class="{ 'active': tab === 'import' }">
					<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Import/Export', 'i18n-translate' ); ?>
				</button>
				<button @click="tab = 'faq'" :class="{ 'active': tab === 'faq' }">
					<span class="dashicons dashicons-editor-help"></span> <?php esc_html_e( 'Best Practices', 'i18n-translate' ); ?>
				</button>
			</div>

			<?php $this->render_getting_started(); ?>
			<?php $this->render_block_editor(); ?>
			<?php $this->render_classic_editor(); ?>
			<?php $this->render_page_builders(); ?>
			<?php $this->render_shortcodes(); ?>
			<?php $this->render_images(); ?>
			<?php $this->render_ecommerce(); ?>
			<?php $this->render_blogs(); ?>
			<?php $this->render_menus(); ?>
			<?php $this->render_php(); ?>
			<?php $this->render_import_export(); ?>
			<?php $this->render_best_practices(); ?>
		</div>

		<script>
			function usageGuide() {
				return {
					tab: 'start',
					copy(text) {
						if (navigator.clipboard && navigator.clipboard.writeText) {
							navigator.clipboard.writeText(text).then(() => {
								this.showToast('Copied to clipboard!');
							});
						}
					},
					showToast(message) {
						const existing = document.querySelector('.i18n-toast');
						if (existing) existing.remove();
						const toast = document.createElement('div');
						toast.className = 'i18n-toast';
						toast.innerHTML = '<span class="dashicons dashicons-yes-alt"></span>' + message;
						document.body.appendChild(toast);
						setTimeout(() => {
							toast.classList.add('fade-out');
							setTimeout(() => toast.remove(), 300);
						}, 2000);
					},
					toggleAccordion(event) {
						const item = event.target.closest('.i18n-accordion-item');
						if (item) item.classList.toggle('open');
					}
				};
			}
		</script>
		<?php
	}

	private function render_getting_started(): void {
		?>
		<div x-show="tab === 'start'" x-transition class="i18n-section">
			<h2><span class="emoji">🚀</span> <?php esc_html_e( 'Getting Started', 'i18n-translate' ); ?></h2>
			
			<h3><?php esc_html_e( 'What is i18n Translate?', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'i18n Translate is a key-based translation system for WordPress. Instead of duplicating pages for each language, you create "translation keys" (like "home.title" or "button.submit") and provide translations for each language. The plugin automatically displays the right translation based on the visitor\'s selected language.', 'i18n-translate' ); ?></p>

			<h3><?php esc_html_e( 'Quick Setup (5 Steps)', 'i18n-translate' ); ?></h3>
			<div class="i18n-steps">
				<div class="i18n-step">
					<div class="i18n-step-num">1</div>
					<h4><?php esc_html_e( 'Enable Languages', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Go to i18n Translate → Languages and add the languages you want to support. Click on presets for quick setup.', 'i18n-translate' ); ?></p>
				</div>
				<div class="i18n-step">
					<div class="i18n-step-num">2</div>
					<h4><?php esc_html_e( 'Create Translation Keys', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Go to Translations and add keys like "home.title", "nav.about", or "button.submit". Use dots to organize by section.', 'i18n-translate' ); ?></p>
				</div>
				<div class="i18n-step">
					<div class="i18n-step-num">3</div>
					<h4><?php esc_html_e( 'Add Translations', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Click each key and enter the translation for each enabled language (English, French, Arabic, etc.).', 'i18n-translate' ); ?></p>
				</div>
				<div class="i18n-step">
					<div class="i18n-step-num">4</div>
					<h4><?php esc_html_e( 'Add Language Switcher', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Add a language switcher to your header/menu so visitors can change languages.', 'i18n-translate' ); ?></p>
				</div>
				<div class="i18n-step">
					<div class="i18n-step-num">5</div>
					<h4><?php esc_html_e( 'Use Translations', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Use blocks, shortcodes, or PHP functions to display translated content anywhere on your site.', 'i18n-translate' ); ?></p>
				</div>
			</div>

			<h3><?php esc_html_e( 'Choose Your Workflow', 'i18n-translate' ); ?></h3>
			<ul>
				<li><strong><?php esc_html_e( 'Editors:', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'Use i18n blocks or shortcodes in posts/pages.', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'Store Owners:', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'Use keys for WooCommerce UI text and marketing copy.', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'Developers:', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'Use PHP helpers in templates and theme overrides.', 'i18n-translate' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'How Language Detection Works', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'The current language is determined in this order:', 'i18n-translate' ); ?></p>
			<ol>
				<li><strong><?php esc_html_e( 'URL Parameter:', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'Add ?i18n_lang=fr to any URL to switch languages (legacy ?lang= still works)', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'Cookie:', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'The selected language is saved in a cookie for return visits', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'Default Language:', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'Falls back to your configured default language', 'i18n-translate' ); ?></li>
			</ol>

			<div class="i18n-info-box success">
				<strong>💡 <?php esc_html_e( 'Pro Tip:', 'i18n-translate' ); ?></strong>
				<?php esc_html_e( 'If a translation is missing, the plugin automatically shows the Default Language version. Configure this in Settings → Default Language.', 'i18n-translate' ); ?>
			</div>
		</div>
		<?php
	}

	private function render_block_editor(): void {
		?>
		<div x-show="tab === 'block'" x-transition class="i18n-section">
			<h2><span class="emoji">🧱</span> <?php esc_html_e( 'Block Editor (Gutenberg)', 'i18n-translate' ); ?></h2>
			<p><?php esc_html_e( 'The easiest way to add translations - no code required! Works in posts, pages, and the Full Site Editor.', 'i18n-translate' ); ?></p>

			<h3><?php esc_html_e( 'Finding i18n Blocks', 'i18n-translate' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Open the Block Editor (edit any post or page)', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Click the + button to add a block', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Search for "i18n" or "translate"', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'You\'ll see "i18n Text" and "Language Switcher" blocks', 'i18n-translate' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'i18n Text Block', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Displays translated text based on a translation key.', 'i18n-translate' ); ?></p>
			<table class="i18n-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Setting', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Description', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Example', 'i18n-translate' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'Translation Key', 'i18n-translate' ); ?></strong></td>
						<td><?php esc_html_e( 'The key from your Translations page', 'i18n-translate' ); ?></td>
						<td><code>home.title</code></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Fallback Text', 'i18n-translate' ); ?></strong></td>
						<td><?php esc_html_e( 'Text if translation is missing', 'i18n-translate' ); ?></td>
						<td><code>Welcome</code></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'HTML Tag', 'i18n-translate' ); ?></strong></td>
						<td><?php esc_html_e( 'Wrapper element', 'i18n-translate' ); ?></td>
						<td><code>h1, h2, p, span, div</code></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'CSS Class', 'i18n-translate' ); ?></strong></td>
						<td><?php esc_html_e( 'Custom CSS class', 'i18n-translate' ); ?></td>
						<td><code>hero-title</code></td>
					</tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Full Site Editor (FSE)', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'For block themes, you can use i18n blocks directly in your site templates:', 'i18n-translate' ); ?></p>
			<ul>
				<li><strong><?php esc_html_e( 'Header Template:', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'Add language switcher and translated site title', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'Footer Template:', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'Translate copyright text and links', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'Page Templates:', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'Create reusable translated patterns', 'i18n-translate' ); ?></li>
			</ul>

			<div class="i18n-info-box">
				<strong><?php esc_html_e( 'Reusable Blocks:', 'i18n-translate' ); ?></strong>
				<?php esc_html_e( 'Create a reusable block with your i18n Text block to use the same translation in multiple places. When you update the translation, it updates everywhere!', 'i18n-translate' ); ?>
			</div>
		</div>
		<?php
	}

	private function render_classic_editor(): void {
		?>
		<div x-show="tab === 'classic'" x-transition class="i18n-section">
			<h2><span class="emoji">✏️</span> <?php esc_html_e( 'Classic Editor', 'i18n-translate' ); ?></h2>
			<p><?php esc_html_e( 'Using the Classic Editor (TinyMCE)? Here\'s how to add translations.', 'i18n-translate' ); ?></p>

			<h3><?php esc_html_e( 'Using Shortcodes in Visual Mode', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Simply type or paste shortcodes directly in the visual editor:', 'i18n-translate' ); ?></p>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('[i18n &quot;home.title&quot;]')">Copy</button>
				[i18n "home.title"]
			</div>

			<h3><?php esc_html_e( 'Using Shortcodes in Text Mode', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Switch to "Text" tab and add shortcodes with HTML:', 'i18n-translate' ); ?></p>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('&lt;h1&gt;[i18n &quot;home.title&quot;]&lt;/h1&gt;')">Copy</button>
				&lt;h1&gt;[i18n "home.title"]&lt;/h1&gt;<br>
				&lt;p&gt;[i18n "home.description" default="Welcome to our website"]&lt;/p&gt;
			</div>

			<h3><?php esc_html_e( 'Content Strategy for Classic Editor', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Two approaches for multilingual content:', 'i18n-translate' ); ?></p>
			
			<div class="i18n-grid-2">
				<div class="i18n-step">
					<h4><?php esc_html_e( 'Approach 1: Single Page with Keys', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Create one page and use translation keys for all text. The same page serves all languages.', 'i18n-translate' ); ?></p>
				</div>
				<div class="i18n-step">
					<h4><?php esc_html_e( 'Approach 2: Duplicate Pages', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Create separate pages per language (e.g., /about/ and /fr/about/). Use translation keys for shared elements like headers.', 'i18n-translate' ); ?></p>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_page_builders(): void {
		?>
		<div x-show="tab === 'builders'" x-transition class="i18n-section">
			<h2><span class="emoji">🎨</span> <?php esc_html_e( 'Page Builders', 'i18n-translate' ); ?></h2>
			<p><?php esc_html_e( 'i18n Translate works with all major page builders through shortcodes.', 'i18n-translate' ); ?></p>

			<h3><?php esc_html_e( 'Elementor', 'i18n-translate' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Add a "Shortcode" widget or "Text Editor" widget', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Enter your translation shortcode:', 'i18n-translate' ); ?> <code>[i18n "your.key"]</code></li>
				<li><?php esc_html_e( 'For language switcher, use:', 'i18n-translate' ); ?> <code>[i18n_switcher]</code></li>
			</ol>
			<div class="i18n-info-box">
				<strong><?php esc_html_e( 'Elementor Pro Tip:', 'i18n-translate' ); ?></strong>
				<?php esc_html_e( 'Create a Global Widget with your language switcher so you can update it everywhere at once.', 'i18n-translate' ); ?>
			</div>

			<h3><?php esc_html_e( 'Beaver Builder', 'i18n-translate' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Add an "HTML" module', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Enter your shortcode in the HTML content area', 'i18n-translate' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'Divi Builder', 'i18n-translate' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Add a "Code" module', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Enter your shortcode', 'i18n-translate' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'WPBakery / Visual Composer', 'i18n-translate' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Add a "Raw HTML" or "Text Block" element', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Enter your shortcode', 'i18n-translate' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'Conditional Display by Language (CSS)', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Show/hide elements based on language using CSS. The plugin adds a language class to the body tag:', 'i18n-translate' ); ?></p>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('.lang-ar .english-only { display: none; }')">Copy</button>
				<span class="comment">/* Hide element for Arabic visitors */</span><br>
				.lang-ar .english-only { display: none; }<br><br>
				<span class="comment">/* Show element only for French visitors */</span><br>
				.french-only { display: none; }<br>
				.lang-fr .french-only { display: block; }
			</div>
		</div>
		<?php
	}

	private function render_shortcodes(): void {
		?>
		<div x-show="tab === 'shortcodes'" x-transition class="i18n-section">
			<h2><span class="emoji">📝</span> <?php esc_html_e( 'Shortcode Reference', 'i18n-translate' ); ?></h2>
			<p><?php esc_html_e( 'Complete reference for all available shortcodes.', 'i18n-translate' ); ?></p>

			<h3><?php esc_html_e( 'Text Translation', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('[i18n &quot;key&quot;]')">Copy</button>
				[i18n "home.title"]<br>
				[i18n "home.title" default="Welcome"]<br>
				[i18n "home.title" tag="h1" class="hero-title"]
			</div>
			<table class="i18n-table">
				<thead><tr><th><?php esc_html_e( 'Attribute', 'i18n-translate' ); ?></th><th><?php esc_html_e( 'Description', 'i18n-translate' ); ?></th></tr></thead>
				<tbody>
					<tr><td><code>key</code></td><td><?php esc_html_e( 'Translation key (required, first attribute)', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>default</code></td><td><?php esc_html_e( 'Fallback text if translation missing', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>tag</code></td><td><?php esc_html_e( 'HTML wrapper tag (span, p, h1-h6, div)', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>class</code></td><td><?php esc_html_e( 'CSS class for the wrapper', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>domain</code></td><td><?php esc_html_e( 'Translation domain (default: theme)', 'i18n-translate' ); ?></td></tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Image Translation', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('[i18n_image &quot;hero.banner&quot;]')">Copy</button>
				[i18n_image "hero.banner"]<br>
				[i18n_image "hero.banner" size="large" class="rounded"]
			</div>
			<table class="i18n-table">
				<thead><tr><th><?php esc_html_e( 'Attribute', 'i18n-translate' ); ?></th><th><?php esc_html_e( 'Description', 'i18n-translate' ); ?></th></tr></thead>
				<tbody>
					<tr><td><code>key</code></td><td><?php esc_html_e( 'Translation key containing image URL or attachment ID', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>size</code></td><td><?php esc_html_e( 'Image size (thumbnail, medium, large, full)', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>class</code></td><td><?php esc_html_e( 'CSS class for the image', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>alt</code></td><td><?php esc_html_e( 'Alt text (or use another translation key)', 'i18n-translate' ); ?></td></tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Language Switcher', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('[i18n_switcher style=&quot;dropdown&quot;]')">Copy</button>
				[i18n_switcher]<br>
				[i18n_switcher style="list" show_flags="false" show_names="true"]<br>
				[i18n_switcher style="flags-only" class="my-switcher"]<br>
				[i18n_switcher style="names-only" show_flags="false"]<br>
				[i18n_switcher style="inline"]
			</div>
			<table class="i18n-table">
				<thead><tr><th><?php esc_html_e( 'Attribute', 'i18n-translate' ); ?></th><th><?php esc_html_e( 'Description', 'i18n-translate' ); ?></th></tr></thead>
				<tbody>
					<tr><td><code>style</code></td><td><?php esc_html_e( 'Display style: dropdown (default), list, inline, flags-only, names-only', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>show_flags</code></td><td><?php esc_html_e( 'Show flag emojis (true/false)', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>show_names</code></td><td><?php esc_html_e( 'Show language names (true/false)', 'i18n-translate' ); ?></td></tr>
					<tr><td><code>class</code></td><td><?php esc_html_e( 'Additional CSS class for the wrapper', 'i18n-translate' ); ?></td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	private function render_images(): void {
		?>
		<div x-show="tab === 'images'" x-transition class="i18n-section">
			<h2><span class="emoji">🖼️</span> <?php esc_html_e( 'Images & Media', 'i18n-translate' ); ?></h2>
			<p><?php esc_html_e( 'Display different images, videos, or documents for different languages.', 'i18n-translate' ); ?></p>

			<h3><?php esc_html_e( 'Image Translation Workflow', 'i18n-translate' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Create a translation key (e.g., "hero.banner")', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'For each language, enter:', 'i18n-translate' ); ?>
					<ul>
						<li><strong><?php esc_html_e( 'Image URL:', 'i18n-translate' ); ?></strong> <code>https://example.com/banner-fr.jpg</code></li>
						<li><strong><?php esc_html_e( 'OR Attachment ID:', 'i18n-translate' ); ?></strong> <code>123</code> <?php esc_html_e( '(from Media Library)', 'i18n-translate' ); ?></li>
					</ul>
				</li>
				<li><?php esc_html_e( 'Use the shortcode or PHP function to display', 'i18n-translate' ); ?></li>
			</ol>

			<div class="i18n-info-box success">
				<strong><?php esc_html_e( 'Recommended: Use Attachment IDs', 'i18n-translate' ); ?></strong>
				<?php esc_html_e( 'Using attachment IDs provides responsive images with srcset for better performance on all devices.', 'i18n-translate' ); ?>
			</div>

			<h3><?php esc_html_e( 'Shortcode Examples', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('[i18n_image &quot;hero.banner&quot;]')">Copy</button>
				<span class="comment">// Basic usage</span><br>
				[i18n_image "hero.banner"]<br><br>
				<span class="comment">// With size and class</span><br>
				[i18n_image "hero.banner" size="large" class="rounded shadow"]<br><br>
				<span class="comment">// With translated alt text</span><br>
				[i18n_image "product.image" alt="product.image.alt"]
			</div>

			<h3><?php esc_html_e( 'PHP Examples', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('echo __img( \\'hero.banner\\', \\'full\\' );')">Copy</button>
				<span class="comment">// Output responsive image</span><br>
				&lt;?php echo __img( 'hero.banner', 'full' ); ?&gt;<br><br>
				<span class="comment">// With custom attributes</span><br>
				&lt;?php echo __img( 'hero.banner', 'large', ['class' => 'hero-image', 'loading' => 'eager'] ); ?&gt;
			</div>

			<h3><?php esc_html_e( 'Background Images (CSS)', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'For CSS background images, output the URL inline:', 'i18n-translate' ); ?></p>
			<div class="i18n-code-box">
				&lt;div style="background-image: url('&lt;?php echo __t('section.bg_image'); ?&gt;')"&gt;<br>
				&nbsp;&nbsp;Content here<br>
				&lt;/div&gt;
			</div>

			<h3><?php esc_html_e( 'Documents & Downloads', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Translate PDF and document links the same way:', 'i18n-translate' ); ?></p>
			<div class="i18n-code-box">
				&lt;a href="&lt;?php echo __t('downloads.brochure'); ?&gt;"&gt;<br>
				&nbsp;&nbsp;&lt;?php echo __t('downloads.brochure.label'); ?&gt;<br>
				&lt;/a&gt;
			</div>
		</div>
		<?php
	}

	private function render_ecommerce(): void {
		?>
		<div x-show="tab === 'ecom'" x-transition class="i18n-section">
			<h2><span class="emoji">🛒</span> <?php esc_html_e( 'eCommerce (WooCommerce)', 'i18n-translate' ); ?></h2>
			<p><?php esc_html_e( 'Translate store UI text (labels, headings, buttons) while WooCommerce continues to handle dynamic product data like price, stock, and SKU.', 'i18n-translate' ); ?></p>

			<div class="i18n-info-box">
				<strong><?php esc_html_e( 'Translate vs Keep in WooCommerce', 'i18n-translate' ); ?></strong>
				<ul>
					<li><?php esc_html_e( 'Translate: headings, button labels, badges, notices, trust copy.', 'i18n-translate' ); ?></li>
					<li><?php esc_html_e( 'Keep: product titles, prices, stock, attributes, variations, reviews, order data.', 'i18n-translate' ); ?></li>
				</ul>
			</div>

			<h3><?php esc_html_e( 'Recommended Key Groups', 'i18n-translate' ); ?></h3>
			<ul>
				<li><strong><?php esc_html_e( 'shop.*', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'filters, sorting, badges, empty states', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'product.*', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'headings, tabs, CTA labels, notices', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'cart.*', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'cart labels and messages', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'checkout.*', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'checkout headings and field labels', 'i18n-translate' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Template Examples (PHP)', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('echo __t( \'product.add_to_cart\', \'Add to cart\' );')">Copy</button>
<span class="comment">// Product page CTA</span><br>
&lt;?php echo __t( 'product.add_to_cart', 'Add to cart' ); ?&gt;<br><br>
<span class="comment">// Shop filters</span><br>
&lt;?php echo __t( 'shop.sort_by', 'Sort by' ); ?&gt;<br>
&lt;?php echo __t( 'shop.filter_by', 'Filter by' ); ?&gt;<br><br>
<span class="comment">// Cart / Checkout messaging</span><br>
&lt;?php echo __t( 'cart.empty', 'Your cart is empty' ); ?&gt;<br>
&lt;?php echo __t( 'checkout.secure', 'Secure checkout' ); ?&gt;
			</div>

			<h3><?php esc_html_e( 'Shortcode Examples (Content Builders)', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
<span class="comment">// Product short description</span><br>
[i18n "product.shipping_note" default="Free shipping over $50"]<br>
[i18n "product.return_policy" default="30-day returns"]<br><br>
<span class="comment">// Checkout reassurance</span><br>
[i18n "checkout.secure" default="Secure checkout"]
			</div>

			<h3><?php esc_html_e( 'Localized Product Images', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Use image keys to swap marketing banners or product graphics by language.', 'i18n-translate' ); ?></p>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('echo __img( \'product.hero_image\', \'large\' );')">Copy</button>
&lt;?php echo __img( 'product.hero_image', 'large' ); ?&gt;
			</div>

			<h3><?php esc_html_e( 'Page-by-Page Key Map', 'i18n-translate' ); ?></h3>
			<table class="i18n-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Page', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Suggested Keys', 'i18n-translate' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Shop (archive-product.php)', 'i18n-translate' ); ?></td>
						<td><code>shop.title</code>, <code>shop.sort_by</code>, <code>shop.filter_by</code></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Product (single-product.php)', 'i18n-translate' ); ?></td>
						<td><code>product.add_to_cart</code>, <code>product.tabs.*</code></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Cart (cart.php)', 'i18n-translate' ); ?></td>
						<td><code>cart.title</code>, <code>cart.empty</code>, <code>cart.continue_shopping</code></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Checkout (checkout.php)', 'i18n-translate' ); ?></td>
						<td><code>checkout.title</code>, <code>checkout.secure</code>, <code>checkout.notice.*</code></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Thank You (thankyou.php)', 'i18n-translate' ); ?></td>
						<td><code>checkout.thank_you</code>, <code>checkout.order_summary</code></td>
					</tr>
				</tbody>
			</table>

			<div class="i18n-info-box warning">
				<strong><?php esc_html_e( 'Note:', 'i18n-translate' ); ?></strong>
				<?php esc_html_e( 'Keep prices, inventory, and variations managed by WooCommerce. Use i18n Translate for labels, headings, and marketing copy.', 'i18n-translate' ); ?>
			</div>
		</div>
		<?php
	}

	private function render_blogs(): void {
		?>
		<div x-show="tab === 'blogs'" x-transition class="i18n-section">
			<h2><span class="emoji">📰</span> <?php esc_html_e( 'Blogs & Posts', 'i18n-translate' ); ?></h2>
			<p><?php esc_html_e( 'Use translation keys for recurring blog UI labels and layout text. Keep post content in the editor as normal.', 'i18n-translate' ); ?></p>

			<div class="i18n-info-box">
				<strong><?php esc_html_e( 'Translate vs Keep in Content', 'i18n-translate' ); ?></strong>
				<ul>
					<li><?php esc_html_e( 'Translate: template labels, buttons, CTAs, archive headings, empty states.', 'i18n-translate' ); ?></li>
					<li><?php esc_html_e( 'Keep: post body text, quotes, and one-off editorial copy.', 'i18n-translate' ); ?></li>
				</ul>
			</div>

			<h3><?php esc_html_e( 'Recommended Key Groups', 'i18n-translate' ); ?></h3>
			<ul>
				<li><strong><?php esc_html_e( 'blog.*', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'read more, categories, tags, author labels', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'post.*', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'share labels, table-of-contents headings', 'i18n-translate' ); ?></li>
				<li><strong><?php esc_html_e( 'archive.*', 'i18n-translate' ); ?></strong> <?php esc_html_e( 'archive titles, filters, empty states', 'i18n-translate' ); ?></li>
			</ul>

			<h3><?php esc_html_e( 'Template Examples (PHP)', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('echo __t( \'blog.read_more\', \'Read more\' );')">Copy</button>
<span class="comment">// Archive cards</span><br>
&lt;?php echo __t( 'blog.read_more', 'Read more' ); ?&gt;<br>
&lt;?php echo __t( 'blog.published_on', 'Published on' ); ?&gt;<br><br>
<span class="comment">// Empty state</span><br>
&lt;?php echo __t( 'archive.no_results', 'No posts found' ); ?&gt;
			</div>

			<h3><?php esc_html_e( 'Block/Editor Examples', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
<span class="comment">// In post content or patterns</span><br>
[i18n "blog.subscribe_cta" default="Subscribe for updates"]<br>
[i18n "post.share" default="Share this post"]
			</div>

			<h3><?php esc_html_e( 'Template Key Map', 'i18n-translate' ); ?></h3>
			<table class="i18n-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Template', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Suggested Keys', 'i18n-translate' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Archive (archive.php)', 'i18n-translate' ); ?></td>
						<td><code>archive.title</code>, <code>archive.no_results</code>, <code>blog.read_more</code></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Single (single.php)', 'i18n-translate' ); ?></td>
						<td><code>blog.published_on</code>, <code>post.share</code>, <code>post.author</code></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Pagination', 'i18n-translate' ); ?></td>
						<td><code>archive.prev</code>, <code>archive.next</code></td>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Author Box', 'i18n-translate' ); ?></td>
						<td><code>post.about_author</code>, <code>post.author_posts</code></td>
					</tr>
				</tbody>
			</table>

			<div class="i18n-info-box">
				<strong><?php esc_html_e( 'Tip:', 'i18n-translate' ); ?></strong>
				<?php esc_html_e( 'Use consistent keys in your theme templates so every post automatically inherits the translated labels.', 'i18n-translate' ); ?>
			</div>
		</div>
		<?php
	}

	private function render_menus(): void {
		?>
		<div x-show="tab === 'menus'" x-transition class="i18n-section">
			<h2><span class="emoji">🍔</span> <?php esc_html_e( 'Menus & Navigation', 'i18n-translate' ); ?></h2>
			
			<h3><?php esc_html_e( 'Method 1: Magic Link (Easiest)', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Works in both Classic and Block themes:', 'i18n-translate' ); ?></p>
			<ol>
				<li><?php esc_html_e( 'Go to Appearance → Menus', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Add a Custom Link', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'URL:', 'i18n-translate' ); ?> <code>#i18n-switcher</code> <?php esc_html_e( '(or with style: #i18n-switcher?style=list)', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Link Text: "Language" or your preferred label', 'i18n-translate' ); ?></li>
			</ol>
			<div class="i18n-info-box">
				<?php esc_html_e( 'Available styles: dropdown (default), list, inline, flags-only, names-only. Example:', 'i18n-translate' ); ?> <code>#i18n-switcher?style=list</code>
			</div>

			<h3><?php esc_html_e( 'Method 2: Widget', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Add the "Language Switcher (i18n)" widget to any widget area in Appearance → Widgets.', 'i18n-translate' ); ?></p>

			<h3><?php esc_html_e( 'Method 3: Shortcode in Menu', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Some themes support shortcodes in menu items. If your theme does:', 'i18n-translate' ); ?></p>
			<div class="i18n-code-box">
				[i18n_switcher style="list" show_flags="true" class="menu-item-lang"]
			</div>

			<h3><?php esc_html_e( 'Method 4: PHP (Theme Development)', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('__switcher(\\'dropdown\\');')">Copy</button>
				<span class="comment">// In header.php or navigation template</span><br>
				&lt;?php __switcher( 'dropdown' ); ?&gt;<br><br>
				<span class="comment">// List style with flags only</span><br>
				&lt;?php __switcher( 'list', ['show_names' => false] ); ?&gt;
			</div>

			<h3><?php esc_html_e( 'Translating Menu Items', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'To translate menu item labels:', 'i18n-translate' ); ?></p>
			<ol>
				<li><?php esc_html_e( 'Create translation keys like "nav.home", "nav.about", etc.', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'In your theme\'s nav walker or template, use __t() for menu labels', 'i18n-translate' ); ?></li>
			</ol>
		</div>
		<?php
	}

	private function render_php(): void {
		?>
		<div x-show="tab === 'php'" x-transition class="i18n-section">
			<h2><span class="emoji">⚙️</span> <?php esc_html_e( 'PHP Reference', 'i18n-translate' ); ?></h2>
			<p><?php esc_html_e( 'Complete reference for theme and plugin developers.', 'i18n-translate' ); ?></p>

			<h3><?php esc_html_e( 'Core Functions', 'i18n-translate' ); ?></h3>
			<table class="i18n-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Function', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Returns', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Description', 'i18n-translate' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>__t( $key, $default, $domain )</code></td>
						<td>string</td>
						<td><?php esc_html_e( 'Returns translated text for key', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>__img( $key, $size, $attrs )</code></td>
						<td>string</td>
						<td><?php esc_html_e( 'Returns translated image HTML', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>__lang()</code></td>
						<td>string</td>
						<td><?php esc_html_e( 'Returns current language code', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>__switcher( $style, $options )</code></td>
						<td>void</td>
						<td><?php esc_html_e( 'Outputs language switcher HTML', 'i18n-translate' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Advanced Functions', 'i18n-translate' ); ?></h3>
			<table class="i18n-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Function', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Description', 'i18n-translate' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>json_i18n_get_translation( $key, $lang, $domain )</code></td>
						<td><?php esc_html_e( 'Get specific translation', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>json_i18n_get_current_language()</code></td>
						<td><?php esc_html_e( 'Get current language with all details', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>json_i18n_get_available_languages()</code></td>
						<td><?php esc_html_e( 'Get all enabled languages', 'i18n-translate' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Example: Complete Header', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
&lt;header class="site-header"&gt;<br>
&nbsp;&nbsp;&lt;div class="logo"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;?php echo __img('site.logo', 'medium'); ?&gt;<br>
&nbsp;&nbsp;&lt;/div&gt;<br>
&nbsp;&nbsp;&lt;nav&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;a href="/"&gt;&lt;?php echo __t('nav.home'); ?&gt;&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;a href="/about"&gt;&lt;?php echo __t('nav.about'); ?&gt;&lt;/a&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;a href="/contact"&gt;&lt;?php echo __t('nav.contact'); ?&gt;&lt;/a&gt;<br>
&nbsp;&nbsp;&lt;/nav&gt;<br>
&nbsp;&nbsp;&lt;?php __switcher('dropdown'); ?&gt;<br>
&lt;/header&gt;
			</div>

			<h3><?php esc_html_e( 'Conditional by Language', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
&lt;?php if ( __lang() === 'ar' ) : ?&gt;<br>
&nbsp;&nbsp;&lt;!-- Arabic-specific content --&gt;<br>
&lt;?php else : ?&gt;<br>
&nbsp;&nbsp;&lt;!-- Other languages --&gt;<br>
&lt;?php endif; ?&gt;
			</div>

			<h3><?php esc_html_e( 'Hooks & Filters', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Extend and customize i18n Translate behavior using these hooks:', 'i18n-translate' ); ?></p>
			
			<h4><?php esc_html_e( 'Filters', 'i18n-translate' ); ?></h4>
			<table class="i18n-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Filter', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Parameters', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Description', 'i18n-translate' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>json_i18n_translation</code></td>
						<td><code>$translation, $key, $default, $domain</code></td>
						<td><?php esc_html_e( 'Modify translation output before display', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>json_i18n_available_languages</code></td>
						<td><code>$languages</code></td>
						<td><?php esc_html_e( 'Filter the list of available languages', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>json_i18n_language_switcher_args</code></td>
						<td><code>$args</code></td>
						<td><?php esc_html_e( 'Customize language switcher options', 'i18n-translate' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h4><?php esc_html_e( 'Actions', 'i18n-translate' ); ?></h4>
			<table class="i18n-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Action', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Parameters', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Description', 'i18n-translate' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>json_i18n_before_translate</code></td>
						<td><code>$key, $default, $domain</code></td>
						<td><?php esc_html_e( 'Fires before translation lookup', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>json_i18n_after_language_change</code></td>
						<td><code>$old_lang, $new_lang</code></td>
						<td><?php esc_html_e( 'Fires after language is switched', 'i18n-translate' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h4><?php esc_html_e( 'Filter Example: Modify Translations', 'i18n-translate' ); ?></h4>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('add_filter( \'json_i18n_translation\', function( $translation, $key, $default, $domain ) { ... }, 10, 4 );')">Copy</button>
<span class="comment">// Modify a specific translation</span><br>
<span class="keyword">add_filter</span>( <span class="string">'json_i18n_translation'</span>, <span class="keyword">function</span>( $translation, $key, $default, $domain ) {<br>
&nbsp;&nbsp;<span class="keyword">if</span> ( $key === <span class="string">'site.title'</span> &amp;&amp; is_front_page() ) {<br>
&nbsp;&nbsp;&nbsp;&nbsp;<span class="keyword">return</span> $translation . <span class="string">' - Official Site'</span>;<br>
&nbsp;&nbsp;}<br>
&nbsp;&nbsp;<span class="keyword">return</span> $translation;<br>
}, 10, 4 );
			</div>

			<h4><?php esc_html_e( 'Action Example: Track Language Changes', 'i18n-translate' ); ?></h4>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('add_action( \'json_i18n_after_language_change\', function( $old, $new ) { ... }, 10, 2 );')">Copy</button>
<span class="comment">// Log language switches</span><br>
<span class="keyword">add_action</span>( <span class="string">'json_i18n_after_language_change'</span>, <span class="keyword">function</span>( $old, $new ) {<br>
&nbsp;&nbsp;error_log( <span class="string">"Language changed: {$old} → {$new}"</span> );<br>
}, 10, 2 );
			</div>

			<h3><?php esc_html_e( 'Performance Tips', 'i18n-translate' ); ?></h3>
			<div class="i18n-grid-2">
				<div class="i18n-step">
					<h4>🚀 <?php esc_html_e( 'Built-in Caching', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Translations are cached automatically using WordPress transients. No configuration needed.', 'i18n-translate' ); ?></p>
				</div>
				<div class="i18n-step">
					<h4>📦 <?php esc_html_e( 'Page Caching', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Use page caching plugins (WP Super Cache, W3 Total Cache). Configure separate cache per language using the ?i18n_lang= parameter (legacy ?lang= also supported).', 'i18n-translate' ); ?></p>
				</div>
				<div class="i18n-step">
					<h4>🔄 <?php esc_html_e( 'Cache Invalidation', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Translation cache clears automatically when you update translations. Clear page cache manually after major changes.', 'i18n-translate' ); ?></p>
				</div>
				<div class="i18n-step">
					<h4>⚡ <?php esc_html_e( 'Lazy Loading', 'i18n-translate' ); ?></h4>
					<p><?php esc_html_e( 'Only requested translations are loaded. Keep domains separate (theme vs plugin) for optimal loading.', 'i18n-translate' ); ?></p>
				</div>
			</div>

			<div class="i18n-info-box success">
				<strong>💡 <?php esc_html_e( 'Object Caching:', 'i18n-translate' ); ?></strong>
				<?php esc_html_e( 'For best performance, use Redis or Memcached object caching. This dramatically speeds up translation lookups on high-traffic sites.', 'i18n-translate' ); ?>
			</div>
		</div>
		<?php
	}

	private function render_import_export(): void {
		?>
		<div x-show="tab === 'import'" x-transition class="i18n-section">
			<h2><span class="emoji">📦</span> <?php esc_html_e( 'Import & Export', 'i18n-translate' ); ?></h2>
			<p><?php esc_html_e( 'Bulk manage translations via CSV or JSON files.', 'i18n-translate' ); ?></p>

			<h3><?php esc_html_e( 'CSV Format', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'Export and import translations using CSV files:', 'i18n-translate' ); ?></p>
			<div class="i18n-code-box">
key,domain,en,fr,ar<br>
home.title,theme,Welcome,Bienvenue,مرحبا<br>
home.subtitle,theme,Our Services,Nos Services,خدماتنا<br>
nav.about,theme,About Us,À propos,من نحن
			</div>

			<h3><?php esc_html_e( 'JSON Format', 'i18n-translate' ); ?></h3>
			<div class="i18n-code-box">
{<br>
&nbsp;&nbsp;"home.title": {<br>
&nbsp;&nbsp;&nbsp;&nbsp;"domain": "theme",<br>
&nbsp;&nbsp;&nbsp;&nbsp;"translations": {<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"en": "Welcome",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"fr": "Bienvenue",<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"ar": "مرحبا"<br>
&nbsp;&nbsp;&nbsp;&nbsp;}<br>
&nbsp;&nbsp;}<br>
}
			</div>

			<h3><?php esc_html_e( 'How to Import', 'i18n-translate' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Go to i18n Translate → Import/Export', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Select your CSV or JSON file', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Choose how to handle duplicates (skip, overwrite, merge)', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Click Import', 'i18n-translate' ); ?></li>
			</ol>

			<h3><?php esc_html_e( 'How to Export', 'i18n-translate' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Go to i18n Translate → Import/Export', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Select format (CSV or JSON)', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Optionally filter by domain or language', 'i18n-translate' ); ?></li>
				<li><?php esc_html_e( 'Click Export', 'i18n-translate' ); ?></li>
			</ol>

			<div class="i18n-info-box warning">
				<strong><?php esc_html_e( 'Backup First!', 'i18n-translate' ); ?></strong>
				<?php esc_html_e( 'Always export your current translations before importing new ones.', 'i18n-translate' ); ?>
			</div>
		</div>
		<?php
	}

	private function render_best_practices(): void {
		?>
		<div x-show="tab === 'faq'" x-transition class="i18n-section">
			<h2><span class="emoji">✅</span> <?php esc_html_e( 'Best Practices & FAQ', 'i18n-translate' ); ?></h2>

			<h3><?php esc_html_e( 'Key Naming Conventions', 'i18n-translate' ); ?></h3>
			<table class="i18n-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Pattern', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Example', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Use For', 'i18n-translate' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>section.element</code></td>
						<td><code>nav.home</code>, <code>footer.copyright</code></td>
						<td><?php esc_html_e( 'UI elements and navigation', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>page.section.item</code></td>
						<td><code>home.hero.title</code></td>
						<td><?php esc_html_e( 'Page-specific content', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>form.field.label/placeholder</code></td>
						<td><code>contact.email.placeholder</code></td>
						<td><?php esc_html_e( 'Form elements', 'i18n-translate' ); ?></td>
					</tr>
					<tr>
						<td><code>error.code</code></td>
						<td><code>error.404</code>, <code>error.required</code></td>
						<td><?php esc_html_e( 'Error messages', 'i18n-translate' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'RTL Language Support', 'i18n-translate' ); ?></h3>
			<p><?php esc_html_e( 'For RTL languages (Arabic, Hebrew, Persian), the plugin automatically adds dir="rtl" to the HTML tag. Follow these CSS best practices:', 'i18n-translate' ); ?></p>
			
			<h4><?php esc_html_e( 'Use CSS Logical Properties', 'i18n-translate' ); ?></h4>
			<div class="i18n-code-box">
				<button class="copy-btn" @click="copy('.element { margin-inline-start: 20px; padding-inline-end: 10px; }')">Copy</button>
<span class="comment">/* ❌ Avoid physical properties */</span><br>
.element { margin-left: 20px; padding-right: 10px; }<br><br>
<span class="comment">/* ✅ Use logical properties (RTL-ready) */</span><br>
.element { margin-inline-start: 20px; padding-inline-end: 10px; }
			</div>

			<h4><?php esc_html_e( 'Common Logical Property Mappings', 'i18n-translate' ); ?></h4>
			<table class="i18n-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Physical (Old)', 'i18n-translate' ); ?></th>
						<th><?php esc_html_e( 'Logical (RTL-Ready)', 'i18n-translate' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr><td><code>margin-left</code></td><td><code>margin-inline-start</code></td></tr>
					<tr><td><code>margin-right</code></td><td><code>margin-inline-end</code></td></tr>
					<tr><td><code>padding-left</code></td><td><code>padding-inline-start</code></td></tr>
					<tr><td><code>text-align: left</code></td><td><code>text-align: start</code></td></tr>
					<tr><td><code>float: left</code></td><td><code>float: inline-start</code></td></tr>
				</tbody>
			</table>

			<h4><?php esc_html_e( 'RTL-Specific Overrides', 'i18n-translate' ); ?></h4>
			<div class="i18n-code-box">
<span class="comment">/* Target RTL languages specifically */</span><br>
[dir="rtl"] .icon-arrow { transform: scaleX(-1); }<br>
.lang-ar .custom-font { font-family: 'Noto Sans Arabic', sans-serif; }
			</div>

			<h3><?php esc_html_e( 'Migration Guides', 'i18n-translate' ); ?></h3>
			
			<div class="i18n-accordion">
				<div class="i18n-accordion-item">
					<div class="i18n-accordion-header" @click="toggleAccordion($event)">
						<?php esc_html_e( 'Migrating from WPML', 'i18n-translate' ); ?>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="i18n-accordion-content">
						<ol>
							<li><?php esc_html_e( 'Export WPML strings via WPML → String Translation → Export', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Convert exported XLIFF/CSV to i18n Translate CSV format', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Import via i18n Translate → Import/Export', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Update theme templates to use __t() instead of __() with WPML', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Replace language switcher shortcodes', 'i18n-translate' ); ?></li>
						</ol>
						<div class="i18n-info-box warning">
							<strong><?php esc_html_e( 'Note:', 'i18n-translate' ); ?></strong>
							<?php esc_html_e( 'WPML uses page duplication; i18n Translate uses key-based strings. You may need to restructure content.', 'i18n-translate' ); ?>
						</div>
					</div>
				</div>
				<div class="i18n-accordion-item">
					<div class="i18n-accordion-header" @click="toggleAccordion($event)">
						<?php esc_html_e( 'Migrating from Polylang', 'i18n-translate' ); ?>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="i18n-accordion-content">
						<ol>
							<li><?php esc_html_e( 'Export Polylang strings from Languages → String translations', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Format as CSV with columns: key, domain, lang1, lang2, ...', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Import into i18n Translate', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Replace pll__() function calls with __t()', 'i18n-translate' ); ?></li>
						</ol>
					</div>
				</div>
				<div class="i18n-accordion-item">
					<div class="i18n-accordion-header" @click="toggleAccordion($event)">
						<?php esc_html_e( 'Migrating from Loco Translate', 'i18n-translate' ); ?>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="i18n-accordion-content">
						<ol>
							<li><?php esc_html_e( 'Export .po files for each language', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Convert .po to CSV (tools available online)', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Create appropriate translation keys for each msgid', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Import CSV into i18n Translate', 'i18n-translate' ); ?></li>
						</ol>
						<div class="i18n-info-box">
							<strong><?php esc_html_e( 'Tip:', 'i18n-translate' ); ?></strong>
							<?php esc_html_e( 'Loco Translate uses gettext (.po/.mo files). i18n Translate uses a database approach which makes updates instant.', 'i18n-translate' ); ?>
						</div>
					</div>
				</div>
			</div>

			<h3><?php esc_html_e( 'Troubleshooting', 'i18n-translate' ); ?></h3>
			
			<div class="i18n-accordion">
				<div class="i18n-accordion-item">
					<div class="i18n-accordion-header" @click="toggleAccordion($event)">
						<?php esc_html_e( 'Translation shows the key instead of text', 'i18n-translate' ); ?>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="i18n-accordion-content">
						<p><strong><?php esc_html_e( 'Causes:', 'i18n-translate' ); ?></strong></p>
						<ul>
							<li><?php esc_html_e( 'Translation for current language is missing', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Key typo (check exact spelling including dots)', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Wrong domain specified', 'i18n-translate' ); ?></li>
						</ul>
						<p><strong><?php esc_html_e( 'Solutions:', 'i18n-translate' ); ?></strong></p>
						<ul>
							<li><?php esc_html_e( 'Add missing translation in Translations page', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Configure a Default Language fallback in Settings', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Use the default attribute: [i18n "key" default="Fallback text"]', 'i18n-translate' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="i18n-accordion-item">
					<div class="i18n-accordion-header" @click="toggleAccordion($event)">
						<?php esc_html_e( 'Language switcher not appearing', 'i18n-translate' ); ?>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="i18n-accordion-content">
						<ul>
							<li><?php esc_html_e( 'Ensure at least 2 languages are enabled in Languages page', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Check that AlpineJS is loading (required for some features)', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Verify shortcode syntax: [i18n_switcher]', 'i18n-translate' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="i18n-accordion-item">
					<div class="i18n-accordion-header" @click="toggleAccordion($event)">
						<?php esc_html_e( 'Translations not updating after save', 'i18n-translate' ); ?>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="i18n-accordion-content">
						<ul>
							<li><?php esc_html_e( 'Clear page cache (WP Super Cache, W3TC, etc.)', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Clear object cache if using Redis/Memcached', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Try hard refresh in browser (Ctrl+Shift+R)', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Check for CDN caching (Cloudflare, etc.)', 'i18n-translate' ); ?></li>
						</ul>
					</div>
				</div>
				<div class="i18n-accordion-item">
					<div class="i18n-accordion-header" @click="toggleAccordion($event)">
						<?php esc_html_e( 'Language not persisting between pages', 'i18n-translate' ); ?>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="i18n-accordion-content">
						<ul>
							<li><?php esc_html_e( 'Check that cookies are enabled in visitor browser', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Ensure site is on same domain (no subdomain mismatch)', 'i18n-translate' ); ?></li>
							<li><?php esc_html_e( 'Verify cookie consent plugin is not blocking i18n cookie', 'i18n-translate' ); ?></li>
						</ul>
					</div>
				</div>
			</div>

			<div class="i18n-info-box">
				<strong>📧 <?php esc_html_e( 'Need More Help?', 'i18n-translate' ); ?></strong>
				<?php esc_html_e( 'Check the plugin documentation or open an issue on GitHub for additional support.', 'i18n-translate' ); ?>
			</div>
		</div>
		<?php
	}
}