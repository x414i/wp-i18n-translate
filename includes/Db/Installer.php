<?php

namespace I18nTranslate\Db;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Installer {
	private const OPTION_DB_VERSION = 'i18n_translate_db_version';

	public function maybe_upgrade(): void {
		$installed = get_option( self::OPTION_DB_VERSION );
		if ( (string) $installed !== (string) I18N_TRANSLATE_DB_VERSION ) {
			$this->install();
		}
	}

	public function install(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$prefix          = $wpdb->prefix . 'i18n_';

		$languages_table = $prefix . 'languages';
		$strings_table   = $prefix . 'strings';
		$tr_table        = $prefix . 'translations';
		$media_table     = $prefix . 'media';
		$field_table     = $prefix . 'field_translations';

		$sql = [];

		$sql[] = "CREATE TABLE {$languages_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			code VARCHAR(20) NOT NULL,
			locale VARCHAR(20) NOT NULL,
			name VARCHAR(100) NOT NULL,
			native_name VARCHAR(100) NOT NULL,
			rtl TINYINT(1) NOT NULL DEFAULT 0,
			flag VARCHAR(16) NULL,
			enabled TINYINT(1) NOT NULL DEFAULT 1,
			sort_order INT(11) NOT NULL DEFAULT 0,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code),
			KEY enabled (enabled),
			KEY sort_order (sort_order)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$strings_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			domain VARCHAR(191) NOT NULL,
			string_key VARCHAR(191) NOT NULL,
			default_text LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY domain_key (domain, string_key),
			KEY domain (domain)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$tr_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			string_id BIGINT(20) UNSIGNED NOT NULL,
			lang_code VARCHAR(20) NOT NULL,
			translation_text LONGTEXT NULL,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY string_lang (string_id, lang_code),
			KEY lang_code (lang_code),
			KEY string_id (string_id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$media_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			attachment_id BIGINT(20) UNSIGNED NOT NULL,
			lang_code VARCHAR(20) NOT NULL,
			translated_attachment_id BIGINT(20) UNSIGNED NULL,
			alt_text TEXT NULL,
			caption TEXT NULL,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY attachment_lang (attachment_id, lang_code),
			KEY lang_code (lang_code)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$field_table} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			object_type VARCHAR(32) NOT NULL,
			object_id BIGINT(20) UNSIGNED NOT NULL,
			field_key VARCHAR(191) NOT NULL,
			lang_code VARCHAR(20) NOT NULL,
			translation_text LONGTEXT NULL,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY object_field_lang (object_type, object_id, field_key, lang_code),
			KEY object_lookup (object_type, object_id),
			KEY lang_code (lang_code)
		) {$charset_collate};";

		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}

		update_option( self::OPTION_DB_VERSION, (string) I18N_TRANSLATE_DB_VERSION );

		$this->seed_languages();
		$this->seed_sample_strings();
		$this->seed_caps();
		$this->seed_default_options();
	}

	private function seed_languages(): void {
		global $wpdb;
		$table = $wpdb->prefix . 'i18n_languages';

		$existing = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		if ( (int) $existing > 0 ) {
			return;
		}

		$languages = [
			[ 'code' => 'en', 'locale' => 'en_US', 'name' => 'English', 'native_name' => 'English', 'rtl' => 0, 'flag' => '🇺🇸', 'enabled' => 1, 'sort_order' => 10 ],
			[ 'code' => 'es', 'locale' => 'es_ES', 'name' => 'Spanish', 'native_name' => 'Español', 'rtl' => 0, 'flag' => '🇪🇸', 'enabled' => 1, 'sort_order' => 20 ],
			[ 'code' => 'fr', 'locale' => 'fr_FR', 'name' => 'French', 'native_name' => 'Français', 'rtl' => 0, 'flag' => '🇫🇷', 'enabled' => 1, 'sort_order' => 30 ],
			[ 'code' => 'de', 'locale' => 'de_DE', 'name' => 'German', 'native_name' => 'Deutsch', 'rtl' => 0, 'flag' => '🇩🇪', 'enabled' => 1, 'sort_order' => 40 ],
			[ 'code' => 'ar', 'locale' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'rtl' => 1, 'flag' => '🇸🇦', 'enabled' => 1, 'sort_order' => 50 ],
		];

		foreach ( $languages as $row ) {
			$wpdb->insert( $table, $row );
		}
	}

	private function seed_sample_strings(): void {
		global $wpdb;
		$strings_table = $wpdb->prefix . 'i18n_strings';
		$tr_table      = $wpdb->prefix . 'i18n_translations';

		// ~50 demo strings across the requested domains.
		$sample_strings = [
			'default' => [
				'site.title'      => 'My Website',
				'site.tagline'    => 'Welcome to our multilingual site',
				'nav.home'        => 'Home',
				'nav.about'       => 'About Us',
				'nav.services'    => 'Services',
				'nav.blog'        => 'Blog',
				'nav.contact'     => 'Contact',
				'btn.read_more'   => 'Read More',
				'btn.learn_more'  => 'Learn More',
				'btn.submit'      => 'Submit',
				'btn.send'        => 'Send Message',
				'btn.cancel'      => 'Cancel',
				'btn.close'       => 'Close',
				'btn.search'      => 'Search',
				'btn.get_started' => 'Get Started',
				'btn.login'       => 'Log In',
			],
			'forms' => [
				'form.name'          => 'Full Name',
				'form.email'         => 'Email Address',
				'form.phone'         => 'Phone Number',
				'form.message'       => 'Your Message',
				'form.subject'       => 'Subject',
				'form.company'       => 'Company Name',
				'form.required'      => 'This field is required',
				'form.invalid_email' => 'Please enter a valid email address',
				'form.success'       => 'Thank you! Your message has been sent successfully.',
				'form.error'         => 'Oops! Something went wrong. Please try again.',
			],
			'footer' => [
				'footer.copyright'              => '© 2025 Company Name. All rights reserved.',
				'footer.privacy'                => 'Privacy Policy',
				'footer.terms'                  => 'Terms of Service',
				'footer.cookies'                => 'Cookie Policy',
				'footer.follow_us'              => 'Follow Us',
				'footer.newsletter'             => 'Subscribe to our Newsletter',
				'footer.newsletter_placeholder' => 'Enter your email',
				'footer.address'                => 'Our Address',
				'footer.phone'                  => 'Call Us',
				'footer.email'                  => 'Email Us',
			],
			'hero' => [
				'hero.title'         => 'Welcome to Our Website',
				'hero.subtitle'      => 'We create amazing digital experiences',
				'hero.description'   => 'Discover our services and see how we can help your business grow.',
				'hero.cta'           => 'Get Started Today',
				'hero.secondary_cta' => 'Learn More About Us',
				'hero.scroll_down'   => 'Scroll Down',
			],
			'common' => [
				'common.loading'    => 'Loading...',
				'common.no_results' => 'No results found',
				'common.error'      => 'Something went wrong',
				'common.success'    => 'Success!',
				'common.share'      => 'Share',
				'common.back'       => 'Go Back',
				'common.next'       => 'Next',
				'common.previous'   => 'Previous',
			],
		];

		$translations = [
			'es' => [
				'default' => [
					'site.title'      => 'Mi Sitio Web',
					'site.tagline'    => 'Bienvenido a nuestro sitio multilingüe',
					'nav.home'        => 'Inicio',
					'nav.about'       => 'Nosotros',
					'nav.services'    => 'Servicios',
					'nav.blog'        => 'Blog',
					'nav.contact'     => 'Contacto',
					'btn.read_more'   => 'Leer más',
					'btn.learn_more'  => 'Más información',
					'btn.submit'      => 'Enviar',
					'btn.send'        => 'Enviar mensaje',
					'btn.cancel'      => 'Cancelar',
					'btn.close'       => 'Cerrar',
					'btn.search'      => 'Buscar',
					'btn.get_started' => 'Empezar',
					'btn.login'       => 'Iniciar sesión',
				],
				'forms' => [
					'form.name'          => 'Nombre completo',
					'form.email'         => 'Correo electrónico',
					'form.phone'         => 'Teléfono',
					'form.message'       => 'Tu mensaje',
					'form.subject'       => 'Asunto',
					'form.company'       => 'Nombre de la empresa',
					'form.required'      => 'Este campo es obligatorio',
					'form.invalid_email' => 'Por favor ingrese un correo válido',
					'form.success'       => '¡Gracias! Tu mensaje se ha enviado correctamente.',
					'form.error'         => 'Ups. Algo salió mal. Inténtalo de nuevo.',
				],
				'footer' => [
					'footer.copyright'              => '© 2025 Nombre de la empresa. Todos los derechos reservados.',
					'footer.privacy'                => 'Política de privacidad',
					'footer.terms'                  => 'Términos del servicio',
					'footer.cookies'                => 'Política de cookies',
					'footer.follow_us'              => 'Síguenos',
					'footer.newsletter'             => 'Suscríbete a nuestro boletín',
					'footer.newsletter_placeholder' => 'Introduce tu correo',
					'footer.address'                => 'Nuestra dirección',
					'footer.phone'                  => 'Llámanos',
					'footer.email'                  => 'Envíanos un correo',
				],
				'hero' => [
					'hero.title'         => 'Bienvenido a nuestro sitio web',
					'hero.subtitle'      => 'Creamos experiencias digitales increíbles',
					'hero.description'   => 'Descubre nuestros servicios y cómo podemos ayudarte a crecer.',
					'hero.cta'           => 'Empieza hoy',
					'hero.secondary_cta' => 'Saber más sobre nosotros',
					'hero.scroll_down'   => 'Desplázate hacia abajo',
				],
				'common' => [
					'common.loading'    => 'Cargando...',
					'common.no_results' => 'No se encontraron resultados',
					'common.error'      => 'Algo salió mal',
					'common.success'    => '¡Éxito!',
					'common.share'      => 'Compartir',
					'common.back'       => 'Volver',
					'common.next'       => 'Siguiente',
					'common.previous'   => 'Anterior',
				],
			],
			'fr' => [
				'default' => [
					'site.title'      => 'Mon site web',
					'site.tagline'    => 'Bienvenue sur notre site multilingue',
					'nav.home'        => 'Accueil',
					'nav.about'       => 'À propos',
					'nav.services'    => 'Services',
					'nav.blog'        => 'Blog',
					'nav.contact'     => 'Contact',
					'btn.read_more'   => 'Lire la suite',
					'btn.learn_more'  => 'En savoir plus',
					'btn.submit'      => 'Envoyer',
					'btn.send'        => 'Envoyer le message',
					'btn.cancel'      => 'Annuler',
					'btn.close'       => 'Fermer',
					'btn.search'      => 'Rechercher',
					'btn.get_started' => 'Commencer',
					'btn.login'       => 'Connexion',
				],
				'forms' => [
					'form.name'          => 'Nom complet',
					'form.email'         => 'Adresse e-mail',
					'form.phone'         => 'Numéro de téléphone',
					'form.message'       => 'Votre message',
					'form.subject'       => 'Objet',
					'form.company'       => 'Nom de l\'entreprise',
					'form.required'      => 'Ce champ est obligatoire',
					'form.invalid_email' => 'Veuillez entrer une adresse e-mail valide',
					'form.success'       => 'Merci ! Votre message a été envoyé avec succès.',
					'form.error'         => 'Oups. Une erreur s\'est produite. Veuillez réessayer.',
				],
				'footer' => [
					'footer.copyright'              => '© 2025 Nom de l\'entreprise. Tous droits réservés.',
					'footer.privacy'                => 'Politique de confidentialité',
					'footer.terms'                  => 'Conditions d\'utilisation',
					'footer.cookies'                => 'Politique des cookies',
					'footer.follow_us'              => 'Suivez-nous',
					'footer.newsletter'             => 'Abonnez-vous à notre newsletter',
					'footer.newsletter_placeholder' => 'Entrez votre e-mail',
					'footer.address'                => 'Notre adresse',
					'footer.phone'                  => 'Appelez-nous',
					'footer.email'                  => 'Écrivez-nous',
				],
				'hero' => [
					'hero.title'         => 'Bienvenue sur notre site',
					'hero.subtitle'      => 'Nous créons des expériences numériques incroyables',
					'hero.description'   => 'Découvrez nos services et comment nous pouvons vous aider à grandir.',
					'hero.cta'           => 'Commencer dès aujourd\'hui',
					'hero.secondary_cta' => 'En savoir plus sur nous',
					'hero.scroll_down'   => 'Faites défiler',
				],
				'common' => [
					'common.loading'    => 'Chargement...',
					'common.no_results' => 'Aucun résultat trouvé',
					'common.error'      => 'Une erreur s\'est produite',
					'common.success'    => 'Succès !',
					'common.share'      => 'Partager',
					'common.back'       => 'Retour',
					'common.next'       => 'Suivant',
					'common.previous'   => 'Précédent',
				],
			],
			'de' => [
				'default' => [
					'site.title'      => 'Meine Website',
					'site.tagline'    => 'Willkommen auf unserer mehrsprachigen Website',
					'nav.home'        => 'Startseite',
					'nav.about'       => 'Über uns',
					'nav.services'    => 'Leistungen',
					'nav.blog'        => 'Blog',
					'nav.contact'     => 'Kontakt',
					'btn.read_more'   => 'Weiterlesen',
					'btn.learn_more'  => 'Mehr erfahren',
					'btn.submit'      => 'Absenden',
					'btn.send'        => 'Nachricht senden',
					'btn.cancel'      => 'Abbrechen',
					'btn.close'       => 'Schließen',
					'btn.search'      => 'Suchen',
					'btn.get_started' => 'Jetzt starten',
					'btn.login'       => 'Anmelden',
				],
				'forms' => [
					'form.name'          => 'Vollständiger Name',
					'form.email'         => 'E-Mail-Adresse',
					'form.phone'         => 'Telefonnummer',
					'form.message'       => 'Ihre Nachricht',
					'form.subject'       => 'Betreff',
					'form.company'       => 'Firmenname',
					'form.required'      => 'Dieses Feld ist erforderlich',
					'form.invalid_email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein',
					'form.success'       => 'Danke! Ihre Nachricht wurde erfolgreich gesendet.',
					'form.error'         => 'Hoppla. Etwas ist schiefgelaufen. Bitte versuchen Sie es erneut.',
				],
				'footer' => [
					'footer.copyright'              => '© 2025 Firmenname. Alle Rechte vorbehalten.',
					'footer.privacy'                => 'Datenschutzrichtlinie',
					'footer.terms'                  => 'Nutzungsbedingungen',
					'footer.cookies'                => 'Cookie-Richtlinie',
					'footer.follow_us'              => 'Folgen Sie uns',
					'footer.newsletter'             => 'Abonnieren Sie unseren Newsletter',
					'footer.newsletter_placeholder' => 'E-Mail eingeben',
					'footer.address'                => 'Unsere Adresse',
					'footer.phone'                  => 'Rufen Sie uns an',
					'footer.email'                  => 'Schreiben Sie uns',
				],
				'hero' => [
					'hero.title'         => 'Willkommen auf unserer Website',
					'hero.subtitle'      => 'Wir schaffen großartige digitale Erlebnisse',
					'hero.description'   => 'Entdecken Sie unsere Leistungen und wie wir Ihr Wachstum unterstützen.',
					'hero.cta'           => 'Heute starten',
					'hero.secondary_cta' => 'Mehr über uns erfahren',
					'hero.scroll_down'   => 'Nach unten scrollen',
				],
				'common' => [
					'common.loading'    => 'Wird geladen...',
					'common.no_results' => 'Keine Ergebnisse gefunden',
					'common.error'      => 'Etwas ist schiefgelaufen',
					'common.success'    => 'Erfolg!',
					'common.share'      => 'Teilen',
					'common.back'       => 'Zurück',
					'common.next'       => 'Weiter',
					'common.previous'   => 'Zurück',
				],
			],
			'ar' => [
				'default' => [
					'site.title'      => 'موقعي الإلكتروني',
					'site.tagline'    => 'مرحباً بكم في موقعنا متعدد اللغات',
					'nav.home'        => 'الرئيسية',
					'nav.about'       => 'من نحن',
					'nav.services'    => 'الخدمات',
					'nav.blog'        => 'المدونة',
					'nav.contact'     => 'اتصل بنا',
					'btn.read_more'   => 'اقرأ المزيد',
					'btn.learn_more'  => 'معرفة المزيد',
					'btn.submit'      => 'إرسال',
					'btn.send'        => 'إرسال الرسالة',
					'btn.cancel'      => 'إلغاء',
					'btn.close'       => 'إغلاق',
					'btn.search'      => 'بحث',
					'btn.get_started' => 'ابدأ الآن',
					'btn.login'       => 'تسجيل الدخول',
				],
				'forms' => [
					'form.name'          => 'الاسم الكامل',
					'form.email'         => 'البريد الإلكتروني',
					'form.phone'         => 'رقم الهاتف',
					'form.message'       => 'رسالتك',
					'form.subject'       => 'الموضوع',
					'form.company'       => 'اسم الشركة',
					'form.required'      => 'هذا الحقل مطلوب',
					'form.invalid_email' => 'يرجى إدخال بريد إلكتروني صالح',
					'form.success'       => 'شكراً لك! تم إرسال رسالتك بنجاح.',
					'form.error'         => 'عذراً! حدث خطأ ما. يرجى المحاولة مرة أخرى.',
				],
				'footer' => [
					'footer.copyright'              => '© 2025 اسم الشركة. جميع الحقوق محفوظة.',
					'footer.privacy'                => 'سياسة الخصوصية',
					'footer.terms'                  => 'شروط الخدمة',
					'footer.cookies'                => 'سياسة ملفات تعريف الارتباط',
					'footer.follow_us'              => 'تابعنا',
					'footer.newsletter'             => 'اشترك في نشرتنا الإخبارية',
					'footer.newsletter_placeholder' => 'أدخل بريدك الإلكتروني',
					'footer.address'                => 'عنواننا',
					'footer.phone'                  => 'اتصل بنا',
					'footer.email'                  => 'راسلنا',
				],
				'hero' => [
					'hero.title'         => 'مرحباً بكم في موقعنا',
					'hero.subtitle'      => 'نحن نصنع تجارب رقمية مذهلة',
					'hero.description'   => 'اكتشف خدماتنا وكيف يمكننا مساعدتك على النمو.',
					'hero.cta'           => 'ابدأ اليوم',
					'hero.secondary_cta' => 'اعرف المزيد عنا',
					'hero.scroll_down'   => 'مرّر للأسفل',
				],
				'common' => [
					'common.loading'    => 'جاري التحميل...',
					'common.no_results' => 'لم يتم العثور على نتائج',
					'common.error'      => 'حدث خطأ ما',
					'common.success'    => 'نجاح!',
					'common.share'      => 'مشاركة',
					'common.back'       => 'رجوع',
					'common.next'       => 'التالي',
					'common.previous'   => 'السابق',
				],
			],
		];

		$lang_codes = [ 'en', 'es', 'fr', 'de', 'ar' ];

		// Insert strings (only if missing) and collect IDs.
		$string_ids = [];
		foreach ( $sample_strings as $domain => $strings ) {
			foreach ( $strings as $key => $default ) {
				$id = (int) $wpdb->get_var( $wpdb->prepare(
					"SELECT id FROM {$strings_table} WHERE domain = %s AND string_key = %s",
					$domain,
					$key
				) );

				if ( $id <= 0 ) {
					$wpdb->insert( $strings_table, [
						'domain'       => $domain,
						'string_key'   => $key,
						'default_text' => $default,
					] );
					$id = (int) $wpdb->insert_id;
				}

				$string_ids[ $domain ][ $key ] = $id;
			}
		}

		// Insert translations for all seeded languages (only if missing).
		foreach ( $lang_codes as $lang_code ) {
			foreach ( $sample_strings as $domain => $strings ) {
				foreach ( $strings as $key => $default ) {
					$string_id = (int) ( $string_ids[ $domain ][ $key ] ?? 0 );
					if ( $string_id <= 0 ) {
						continue;
					}

					$translation_text = $default;
					if ( $lang_code !== 'en' ) {
						$translation_text = (string) ( $translations[ $lang_code ][ $domain ][ $key ] ?? $default );
					}

					$exists = (int) $wpdb->get_var( $wpdb->prepare(
						"SELECT COUNT(*) FROM {$tr_table} WHERE string_id = %d AND lang_code = %s",
						$string_id,
						$lang_code
					) );

					if ( $exists > 0 ) {
						continue;
					}

					$wpdb->insert( $tr_table, [
						'string_id'        => $string_id,
						'lang_code'        => $lang_code,
						'translation_text' => $translation_text,
					] );
				}
			}
		}
	}


private function seed_caps(): void {
$role = get_role( 'administrator' );
		if ( $role ) {
			$role->add_cap( 'i18n_translate_manage' );
			$role->add_cap( 'i18n_translate_translate' );
		}
	}

private function seed_default_options(): void {
		if ( get_option( 'i18n_translate_default_language' ) === false ) {
			update_option( 'i18n_translate_default_language', 'en' );
		}
		if ( get_option( 'i18n_translate_auto_detect' ) === false ) {
			update_option( 'i18n_translate_auto_detect', false );
		}
}
}
