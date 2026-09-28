<?php
/**
 * Privacy Policy Disclaimer Without WordPress Plugin
 *
 * Cookie consent for WordPress in a single file, loaded from the theme's
 * functions.php (no plugin). Covers opt-in consent (LGPD, GDPR), opt-out
 * notices (CCPA/CPRA and other US state laws), Google Consent Mode v2,
 * Global Privacy Control, blocking of scripts and third-party embeds until
 * consent, and a consent log integrated with the WordPress privacy tools.
 *
 * Usage, in the (child) theme's functions.php:
 *
 *     require_once get_stylesheet_directory() . '/privacy-policy-disclaimer.php';
 *
 * Then open Settings > Cookies.
 *
 * @package   PrivacyPolicyDisclaimer
 * @version   2.0.0
 * @author    Lucas Ferraz <https://lucasferraz.com>
 * @license   MIT
 * @link      https://github.com/LucasFerrazSEO/Privacy-Policy-Disclaimer-Without-WordPress-Plugin
 *
 * Requires WordPress 5.9+ and PHP 7.4+.
 *
 * This is a technical tool, not legal advice. Whether a site complies with a
 * given law depends on how it is configured and on what the site does.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'PPD_VERSION' ) ) {
	return; // Already loaded (included twice).
}

define( 'PPD_VERSION', '2.0.0' );
define( 'PPD_DB_VERSION', '1' );
define( 'PPD_OPTION', 'ppd_settings' );
define( 'PPD_COOKIE', 'ppd_consent' );

// -----------------------------------------------------------------------------
// 1. Strings and settings
// -----------------------------------------------------------------------------

/**
 * Whether the current request should use Portuguese strings.
 *
 * The file has no .mo files; every string ships in English and Brazilian
 * Portuguese and is picked by locale. Filter `ppd_is_portuguese` to force.
 *
 * @return bool
 */
function ppd_is_pt() {
	$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
	return (bool) apply_filters( 'ppd_is_portuguese', 0 === strpos( (string) $locale, 'pt' ) );
}

/**
 * Picks the English or Portuguese version of a string.
 *
 * @param string $en English.
 * @param string $pt Brazilian Portuguese.
 * @return string
 */
function ppd_t( $en, $pt ) {
	return ppd_is_pt() ? $pt : $en;
}

/**
 * Consent categories (necessary is always on and is not stored).
 *
 * @return string[]
 */
function ppd_categories() {
	return array( 'preferences', 'analytics', 'marketing' );
}

/**
 * Default texts, by locale. An empty text setting falls back to these.
 *
 * @return array<string,string>
 */
function ppd_default_texts() {
	return array(
		'title'             => ppd_t( 'Your privacy', 'Sua privacidade' ),
		'message'           => ppd_t(
			'We use cookies that the site needs to work and, with your permission, cookies for preferences, statistics and marketing. You can accept, reject or choose by category, and change your mind at any time.',
			'Usamos cookies necessários para o site funcionar e, com a sua permissão, cookies de preferências, estatísticas e marketing. Você pode aceitar, recusar ou escolher por categoria, e mudar de ideia quando quiser.'
		),
		'message_optout'    => ppd_t(
			'We use cookies to measure how the site is used and for advertising. You can opt out of the sale or sharing of your personal information at any time.',
			'Usamos cookies para medir o uso do site e para publicidade. Você pode se opor à venda ou ao compartilhamento das suas informações pessoais a qualquer momento.'
		),
		'accept'            => ppd_t( 'Accept all', 'Aceitar todos' ),
		'reject'            => ppd_t( 'Reject all', 'Recusar todos' ),
		'customize'         => ppd_t( 'Customize', 'Personalizar' ),
		'save'              => ppd_t( 'Save choices', 'Salvar escolhas' ),
		'acknowledge'       => ppd_t( 'Got it', 'Entendi' ),
		'close'             => ppd_t( 'Close', 'Fechar' ),
		'policy_label'      => ppd_t( 'Privacy policy', 'Política de privacidade' ),
		'prefs_title'       => ppd_t( 'Cookie preferences', 'Preferências de cookies' ),
		'prefs_intro'       => ppd_t(
			'Choose which categories you allow. Necessary cookies are always on because the site does not work without them.',
			'Escolha quais categorias você permite. Os cookies necessários ficam sempre ligados porque o site não funciona sem eles.'
		),
		'settings_button'   => ppd_t( 'Cookie settings', 'Configurações de cookies' ),
		'dns_label'         => ppd_t( 'Do Not Sell or Share My Personal Information', 'Não vender nem compartilhar minhas informações pessoais' ),
		'dns_done'          => ppd_t(
			'Your request was saved. We will not sell or share your personal information through cookies on this browser.',
			'Seu pedido foi registrado. Não vamos vender nem compartilhar suas informações pessoais por meio de cookies neste navegador.'
		),
		'gpc_notice'        => ppd_t(
			'Your browser sent a Global Privacy Control signal, so marketing cookies stay off.',
			'Seu navegador enviou o sinal Global Privacy Control, então os cookies de marketing ficam desligados.'
		),
		'embed_text'        => ppd_t(
			'This content is hosted by an external service (%s) that may set marketing cookies.',
			'Este conteúdo é de um serviço externo (%s) que pode gravar cookies de marketing.'
		),
		'embed_load'        => ppd_t( 'Load content', 'Carregar conteúdo' ),
		'embed_always'      => ppd_t( 'Always allow', 'Permitir sempre' ),
		'necessary_label'   => ppd_t( 'Necessary', 'Necessários' ),
		'necessary_desc'    => ppd_t(
			'Required for the site to work, such as security and remembering your cookie choice. They cannot be turned off.',
			'Essenciais para o site funcionar, como segurança e a lembrança da sua escolha de cookies. Não podem ser desligados.'
		),
		'preferences_label' => ppd_t( 'Preferences', 'Preferências' ),
		'preferences_desc'  => ppd_t( 'Remember choices such as language and region.', 'Lembram escolhas como idioma e região.' ),
		'analytics_label'   => ppd_t( 'Statistics', 'Estatísticas' ),
		'analytics_desc'    => ppd_t( 'Help us understand how the site is used, in aggregate.', 'Ajudam a entender como o site é usado, de forma agregada.' ),
		'marketing_label'   => ppd_t( 'Marketing', 'Marketing' ),
		'marketing_desc'    => ppd_t(
			'Used for ads, remarketing and third-party embedded content such as videos and maps.',
			'Usados para anúncios, remarketing e conteúdo incorporado de terceiros, como vídeos e mapas.'
		),
		'always_on'         => ppd_t( 'Always on', 'Sempre ligado' ),
	);
}

/**
 * Countries that get opt-in consent in "auto" mode: EU, EEA, UK,
 * Switzerland and Brazil. Filter `ppd_optin_countries` to change.
 *
 * @return string[]
 */
function ppd_default_optin_countries() {
	return array(
		'AT',
		'BE',
		'BG',
		'HR',
		'CY',
		'CZ',
		'DK',
		'EE',
		'FI',
		'FR',
		'DE',
		'GR',
		'HU',
		'IE',
		'IT',
		'LV',
		'LT',
		'LU',
		'MT',
		'NL',
		'PL',
		'PT',
		'RO',
		'SK',
		'SI',
		'ES',
		'SE',
		'IS',
		'LI',
		'NO',
		'GB',
		'CH',
		'BR',
	);
}

/**
 * Default settings.
 *
 * @return array<string,mixed>
 */
function ppd_default_settings() {
	$defaults = array(
		'enabled'           => 1,
		// optin = LGPD/GDPR, optout = CCPA/CPRA, auto = by visitor country.
		'regime'            => 'optin',
		'optin_countries'   => implode( ',', ppd_default_optin_countries() ),
		'auto_unknown'      => 'optin',
		'policy_url'        => '',
		'policy_version'    => '1',
		'expiry_days'       => 180,
		'position'          => 'bar', // bar, box, modal.
		'color_bg'          => '#111827',
		'color_text'        => '#f9fafb',
		'color_accent'      => '#1d4ed8',
		'color_accent_text' => '#ffffff',
		'floating_button'   => 1,
		'respect_gpc'       => 1,
		'reload_on_revoke'  => 1,
		// Google Consent Mode v2.
		'gcm_enabled'       => 1,
		'gcm_mode'          => 'advanced', // advanced = tags load and wait; basic = tags load only after consent.
		'gtm_id'            => '',
		'ga4_id'            => '',
		'ads_redaction'     => 1,
		'url_passthrough'   => 0,
		// Blocking.
		'embeds_block'      => 1,
		'embeds_category'   => 'marketing',
		'script_handles'    => '',
		// Consent log.
		'log_enabled'       => 1,
		'log_retention'     => 730,
	);
	foreach ( ppd_categories() as $cat ) {
		$defaults[ 'cat_' . $cat ]     = 1;
		$defaults[ 'scripts_' . $cat ] = '';
		$defaults[ 'cookies_' . $cat ] = '';
	}
	$defaults['cookies_analytics'] = '_ga,_gid,_gat,_clck,_clsk,_hjSession,_hjSessionUser';
	$defaults['cookies_marketing'] = '_fbp,_fbc,_gcl_au,_gcl_aw,_uetsid,_uetvid,_ttp';
	foreach ( array_keys( ppd_default_texts() ) as $key ) {
		$defaults[ 'text_' . $key ] = '';
	}
	return $defaults;
}

/**
 * Current settings merged with defaults.
 *
 * @param bool $fresh Skip the per-request cache.
 * @return array<string,mixed>
 */
function ppd_settings( $fresh = false ) {
	static $cache = null;
	if ( null === $cache || $fresh ) {
		$saved = get_option( PPD_OPTION, array() );
		$cache = apply_filters( 'ppd_settings', wp_parse_args( is_array( $saved ) ? $saved : array(), ppd_default_settings() ) );
	}
	return $cache;
}

/**
 * A banner text: the saved override or the default for the locale.
 *
 * @param string $key Text key.
 * @return string
 */
function ppd_text( $key ) {
	$s = ppd_settings();
	if ( isset( $s[ 'text_' . $key ] ) && '' !== trim( (string) $s[ 'text_' . $key ] ) ) {
		return (string) $s[ 'text_' . $key ];
	}
	$d = ppd_default_texts();
	return isset( $d[ $key ] ) ? $d[ $key ] : '';
}

/**
 * Enabled categories (necessary excluded).
 *
 * @return string[]
 */
function ppd_enabled_categories() {
	$s = ppd_settings();
	return array_values(
		array_filter(
			ppd_categories(),
			static function ( $c ) use ( $s ) {
				return ! empty( $s[ 'cat_' . $c ] );
			}
		)
	);
}

/**
 * Privacy policy URL: the setting, else the WordPress privacy page.
 *
 * @return string
 */
function ppd_policy_url() {
	$s = ppd_settings();
	if ( ! empty( $s['policy_url'] ) ) {
		return $s['policy_url'];
	}
	return function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
}

/**
 * Countries that get opt-in in auto mode.
 *
 * @return string[]
 */
function ppd_optin_countries() {
	$s    = ppd_settings();
	$list = array_filter( array_map( 'trim', explode( ',', strtoupper( (string) $s['optin_countries'] ) ) ) );
	return (array) apply_filters( 'ppd_optin_countries', array_values( $list ) );
}

// -----------------------------------------------------------------------------
// 2. Server-side helpers for theme code
// -----------------------------------------------------------------------------

/**
 * The visitor's stored choice, read from the consent cookie.
 *
 * Only reliable on pages that are not served from a full-page cache.
 *
 * @return array|null { v, c: {preferences, analytics, marketing}, r, t, id, g } or null.
 */
function ppd_get_consent() {
	if ( empty( $_COOKIE[ PPD_COOKIE ] ) ) {
		return null;
	}
	$raw  = wp_unslash( $_COOKIE[ PPD_COOKIE ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- decoded and validated below.
	$data = json_decode( (string) $raw, true );
	if ( ! is_array( $data ) || ! isset( $data['v'], $data['c'] ) || ! is_array( $data['c'] ) ) {
		return null;
	}
	if ( (string) ppd_settings()['policy_version'] !== (string) $data['v'] ) {
		return null;
	}
	return $data;
}

/**
 * Whether the visitor allowed a category ("necessary" is always true).
 *
 * @param string $category necessary, preferences, analytics or marketing.
 * @return bool
 */
function ppd_has_consent( $category ) {
	if ( 'necessary' === $category ) {
		return true;
	}
	$consent = ppd_get_consent();
	return $consent && ! empty( $consent['c'][ $category ] );
}

// -----------------------------------------------------------------------------
// 3. Visitor country (auto mode)
// -----------------------------------------------------------------------------

/**
 * Two-letter country code from CDN or server headers, or '' if unknown.
 *
 * Cloudflare (CF-IPCountry), CloudFront, Fastly-style X-Country-Code and
 * the GeoIP module are read. Filter `ppd_visitor_country` for anything else.
 *
 * @return string
 */
function ppd_visitor_country() {
	$country = '';
	foreach ( array( 'HTTP_CF_IPCOUNTRY', 'HTTP_CLOUDFRONT_VIEWER_COUNTRY', 'HTTP_X_COUNTRY_CODE', 'GEOIP_COUNTRY_CODE', 'HTTP_X_GEO_COUNTRY' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$country = strtoupper( sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
			break;
		}
	}
	if ( ! preg_match( '/^[A-Z]{2}$/', $country ) || in_array( $country, array( 'XX', 'T1' ), true ) ) {
		$country = '';
	}
	return (string) apply_filters( 'ppd_visitor_country', $country );
}

/**
 * Regime for a country in auto mode.
 *
 * @param string $country Two-letter code or ''.
 * @return string optin|optout
 */
function ppd_regime_for_country( $country ) {
	$s = ppd_settings();
	if ( '' === $country ) {
		$regime = 'optout' === $s['auto_unknown'] ? 'optout' : 'optin';
	} elseif ( in_array( $country, ppd_optin_countries(), true ) ) {
		$regime = 'optin';
	} elseif ( 'US' === $country ) {
		$regime = 'optout';
	} else {
		$regime = 'optout' === $s['auto_unknown'] ? 'optout' : 'optin';
	}
	return (string) apply_filters( 'ppd_regime_for_country', $regime, $country );
}

// -----------------------------------------------------------------------------
// 4. Consent log (database)
// -----------------------------------------------------------------------------

/**
 * Log table name.
 *
 * @return string
 */
function ppd_log_table() {
	global $wpdb;
	return $wpdb->prefix . 'ppd_consent_log';
}

/**
 * Creates or updates the log table when the schema version changes.
 */
function ppd_maybe_install() {
	if ( get_option( 'ppd_db_version' ) === PPD_DB_VERSION ) {
		return;
	}
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = ppd_log_table();
	$charset = $wpdb->get_charset_collate();
	dbDelta(
		"CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		consent_id char(36) NOT NULL,
		created_at datetime NOT NULL,
		user_id bigint(20) unsigned NOT NULL DEFAULT 0,
		regime varchar(10) NOT NULL,
		action varchar(20) NOT NULL,
		categories varchar(100) NOT NULL,
		policy_version varchar(20) NOT NULL,
		gpc tinyint(1) NOT NULL DEFAULT 0,
		ip_hash char(64) NOT NULL,
		url varchar(255) NOT NULL DEFAULT '',
		PRIMARY KEY  (id),
		KEY consent_id (consent_id),
		KEY created_at (created_at),
		KEY user_id (user_id)
		) {$charset};"
	);
	update_option( 'ppd_db_version', PPD_DB_VERSION, false );
}
add_action( 'admin_init', 'ppd_maybe_install' );

/**
 * Visitor IP, anonymized (IPv4 /24, IPv6 /48), then hashed with a site salt.
 * The raw IP is never stored.
 *
 * @return string 64-char hex.
 */
function ppd_ip_hash() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( function_exists( 'wp_privacy_anonymize_ip' ) ) {
		$ip = wp_privacy_anonymize_ip( $ip );
	}
	return hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
}

/**
 * Deletes log rows older than the retention period (daily cron).
 */
function ppd_cleanup_log() {
	$s = ppd_settings();
	if ( get_option( 'ppd_db_version' ) !== PPD_DB_VERSION ) {
		return;
	}
	global $wpdb;
	$days  = max( 30, (int) $s['log_retention'] );
	$limit = gmdate( 'Y-m-d H:i:s', time() - $days * DAY_IN_SECONDS );
	$table = ppd_log_table();
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE created_at < %s", $limit ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery
}
add_action( 'ppd_cleanup', 'ppd_cleanup_log' );

add_action(
	'init',
	static function () {
		if ( ! wp_next_scheduled( 'ppd_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'ppd_cleanup' );
		}
	}
);

// -----------------------------------------------------------------------------
// 5. REST endpoints: geo and consent log
// -----------------------------------------------------------------------------

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'ppd/v1',
			'/geo',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => 'ppd_rest_geo',
			)
		);
		register_rest_route(
			'ppd/v1',
			'/consent',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => 'ppd_rest_consent',
				'args'                => array(
					'id'         => array(
						'required'          => true,
						'type'              => 'string',
						'validate_callback' => static function ( $v ) {
							return is_string( $v ) && (bool) preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $v );
						},
					),
					'action'     => array(
						'required' => true,
						'type'     => 'string',
						'enum'     => array( 'accept_all', 'reject_all', 'custom', 'acknowledge', 'optout', 'gpc', 'embed' ),
					),
					'categories' => array(
						'required' => true,
						'type'     => 'object',
					),
					'regime'     => array(
						'required' => true,
						'type'     => 'string',
						'enum'     => array( 'optin', 'optout' ),
					),
					'gpc'        => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'url'        => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
	}
);

/**
 * GET /ppd/v1/geo: regime for the visitor, never cached.
 *
 * @return WP_REST_Response
 */
function ppd_rest_geo() {
	$country  = ppd_visitor_country();
	$response = new WP_REST_Response(
		array(
			'country' => $country,
			'regime'  => ppd_regime_for_country( $country ),
		)
	);
	$response->header( 'Cache-Control', 'no-store, max-age=0' );
	return $response;
}

/**
 * POST /ppd/v1/consent: stores one consent record.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function ppd_rest_consent( WP_REST_Request $request ) {
	$s = ppd_settings();
	if ( empty( $s['log_enabled'] ) ) {
		return new WP_REST_Response( array( 'logged' => false ), 200 );
	}
	ppd_maybe_install();

	$ip_hash = ppd_ip_hash();
	$rl_key  = 'ppd_rl_' . substr( $ip_hash, 0, 32 );
	$hits    = (int) get_transient( $rl_key );
	if ( $hits >= 30 ) {
		return new WP_Error( 'ppd_rate_limited', 'Too many requests.', array( 'status' => 429 ) );
	}
	set_transient( $rl_key, $hits + 1, 10 * MINUTE_IN_SECONDS );

	$cats = array();
	$in   = (array) $request->get_param( 'categories' );
	foreach ( ppd_categories() as $cat ) {
		if ( ! empty( $in[ $cat ] ) ) {
			$cats[] = $cat;
		}
	}

	// Only same-site URLs, without query string (it may hold personal data).
	$url  = (string) $request->get_param( 'url' );
	$home = wp_parse_url( home_url() );
	$part = wp_parse_url( $url );
	$path = '';
	if ( $part && isset( $part['host'], $home['host'] ) && strtolower( $part['host'] ) === strtolower( $home['host'] ) ) {
		$path = isset( $part['path'] ) ? substr( $part['path'], 0, 255 ) : '/';
	}

	global $wpdb;
	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		ppd_log_table(),
		array(
			'consent_id'     => strtolower( (string) $request->get_param( 'id' ) ),
			'created_at'     => gmdate( 'Y-m-d H:i:s' ),
			'user_id'        => get_current_user_id(),
			'regime'         => (string) $request->get_param( 'regime' ),
			'action'         => (string) $request->get_param( 'action' ),
			'categories'     => implode( ',', $cats ),
			'policy_version' => substr( (string) $s['policy_version'], 0, 20 ),
			'gpc'            => $request->get_param( 'gpc' ) ? 1 : 0,
			'ip_hash'        => $ip_hash,
			'url'            => esc_url_raw( $path ),
		),
		array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
	);

	$response = new WP_REST_Response( array( 'logged' => (bool) $wpdb->insert_id ), 201 );
	$response->header( 'Cache-Control', 'no-store, max-age=0' );
	return $response;
}

// -----------------------------------------------------------------------------
// 6. Front end: when to run
// -----------------------------------------------------------------------------

/**
 * Whether the banner and blocking run on this request.
 *
 * @return bool
 */
function ppd_is_active() {
	$s = ppd_settings();
	if ( empty( $s['enabled'] ) || is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}
	if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_feed() || is_embed() ) {
		return false;
	}
	if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
		return false;
	}
	return (bool) apply_filters( 'ppd_is_active', true );
}

/**
 * Fixed regime ('optin' or 'optout') or 'auto'.
 *
 * @return string
 */
function ppd_regime_setting() {
	$r = ppd_settings()['regime'];
	return in_array( $r, array( 'optin', 'optout', 'auto' ), true ) ? $r : 'optin';
}

/**
 * Prints an inline script, through the core helper when available (it
 * honors the `wp_inline_script_attributes` filter, useful for CSP nonces).
 *
 * @param string $js         Code.
 * @param array  $attributes Extra attributes.
 */
function ppd_print_inline_script( $js, $attributes = array() ) {
	if ( function_exists( 'wp_print_inline_script_tag' ) ) {
		wp_print_inline_script_tag( $js, $attributes );
		return;
	}
	echo '<script>' . $js . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped JSON.
}

/**
 * Google Consent Mode v2 defaults, as early as possible in <head>.
 *
 * Opt-in: everything denied until a choice. Opt-out: granted unless the
 * visitor opted out or sent Global Privacy Control. Auto: denied until the
 * regime is known, then updated. A stored choice is applied right away.
 */
function ppd_head_consent_defaults() {
	if ( ! ppd_is_active() ) {
		return;
	}
	$s = ppd_settings();
	if ( empty( $s['gcm_enabled'] ) ) {
		return;
	}
	$regime  = ppd_regime_setting();
	$granted = 'optout' === $regime ? 'granted' : 'denied';
	$config  = array(
		'cookie'  => PPD_COOKIE,
		'version' => (string) $s['policy_version'],
		'regime'  => $regime,
		'gpc'     => ! empty( $s['respect_gpc'] ),
		'cats'    => ppd_enabled_categories(),
	);
	$js      = 'window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}';
	$js     .= '(function(c){var g=' . wp_json_encode( $granted ) . ';';
	$js     .= 'var gpc=c.gpc&&navigator.globalPrivacyControl===true;';
	$js     .= "gtag('consent','default',{ad_storage:gpc?'denied':g,ad_user_data:gpc?'denied':g,ad_personalization:gpc?'denied':g,analytics_storage:g,functionality_storage:g,personalization_storage:g,security_storage:'granted',wait_for_update:500});";
	if ( ! empty( $s['ads_redaction'] ) ) {
		$js .= "gtag('set','ads_data_redaction',true);";
	}
	if ( ! empty( $s['url_passthrough'] ) ) {
		$js .= "gtag('set','url_passthrough',true);";
	}
	// Stored choice: apply before any tag fires.
	$js .= 'try{var m=document.cookie.match(new RegExp("(?:^|; )"+c.cookie+"=([^;]*)"));if(m){var d=JSON.parse(decodeURIComponent(m[1]));';
	$js .= 'if(d&&String(d.v)===c.version&&d.c){var on=function(k){return c.cats.indexOf(k)>-1&&d.c[k]?"granted":"denied";};';
	$js .= "gtag('consent','update',{analytics_storage:on('analytics'),ad_storage:on('marketing'),ad_user_data:on('marketing'),ad_personalization:on('marketing'),functionality_storage:on('preferences'),personalization_storage:on('preferences')});}}}catch(e){}";
	$js .= '})(' . wp_json_encode( $config ) . ');';
	ppd_print_inline_script( $js, array( 'id' => 'ppd-consent-defaults' ) );
}
add_action( 'wp_head', 'ppd_head_consent_defaults', 1 );

/**
 * Google tag loaders (GTM and/or GA4), after the consent defaults.
 * In "basic" mode they are held back until the category is allowed.
 */
function ppd_head_google_tags() {
	if ( ! ppd_is_active() ) {
		return;
	}
	$s     = ppd_settings();
	$gtm   = preg_match( '/^GTM-[A-Z0-9]+$/', (string) $s['gtm_id'] ) ? $s['gtm_id'] : '';
	$ga4   = preg_match( '/^G-[A-Z0-9]+$/', (string) $s['ga4_id'] ) ? $s['ga4_id'] : '';
	$basic = empty( $s['gcm_enabled'] ) || 'basic' === $s['gcm_mode'];
	$out   = '';
	if ( $gtm ) {
		$out .= "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer'," . wp_json_encode( $gtm ) . ');';
	}
	if ( $ga4 ) {
		$out .= "(function(){var j=document.createElement('script');j.async=true;j.src='https://www.googletagmanager.com/gtag/js?id='+" . wp_json_encode( $ga4 ) . ";document.head.appendChild(j);window.dataLayer=window.dataLayer||[];if(typeof window.gtag!=='function'){window.gtag=function(){dataLayer.push(arguments);};}gtag('js',new Date());gtag('config'," . wp_json_encode( $ga4 ) . ');})();';
	}
	if ( '' === $out ) {
		return;
	}
	if ( $basic ) {
		// Held until the visitor allows statistics.
		printf(
			"<script type=\"text/plain\" data-ppd-category=\"analytics\" id=\"ppd-google-tags\">%s</script>\n",
			$out // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from validated IDs.
		);
		return;
	}
	ppd_print_inline_script( $out, array( 'id' => 'ppd-google-tags' ) );
}
add_action( 'wp_head', 'ppd_head_google_tags', 2 );

/**
 * Code pasted in the settings (per category), held in <template> until
 * the visitor allows that category. The JS re-creates every <script> so it
 * runs, in document order.
 */
function ppd_head_custom_scripts() {
	if ( ! ppd_is_active() ) {
		return;
	}
	$s = ppd_settings();
	foreach ( ppd_enabled_categories() as $cat ) {
		$code = trim( (string) $s[ 'scripts_' . $cat ] );
		if ( '' === $code ) {
			continue;
		}
		printf(
			"<template data-ppd-category=\"%s\">%s</template>\n",
			esc_attr( $cat ),
			$code // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- saved only by users with unfiltered_html.
		);
	}
}
add_action( 'wp_head', 'ppd_head_custom_scripts', 20 );

/**
 * Handle → category map from the settings ("handle:category" per line).
 *
 * @return array<string,string>
 */
function ppd_script_handle_map() {
	$map = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) ppd_settings()['script_handles'] ) as $line ) {
		$parts = array_map( 'trim', explode( ':', $line, 2 ) );
		if ( 2 === count( $parts ) && '' !== $parts[0] && in_array( $parts[1], ppd_categories(), true ) ) {
			$map[ $parts[0] ] = $parts[1];
		}
	}
	return (array) apply_filters( 'ppd_script_handles', $map );
}

/**
 * Blocks enqueued scripts mapped to a category: they get type="text/plain",
 * so the browser neither downloads nor runs them until consent.
 *
 * @param string $tag    <script> tag.
 * @param string $handle Handle.
 * @return string
 */
function ppd_filter_script_tag( $tag, $handle ) {
	if ( ! ppd_is_active() ) {
		return $tag;
	}
	$map    = ppd_script_handle_map();
	$handle = preg_replace( '/-js(-(before|after|extra))?$/', '', $handle );
	if ( ! isset( $map[ $handle ] ) || ! in_array( $map[ $handle ], ppd_enabled_categories(), true ) ) {
		return $tag;
	}
	$cat = esc_attr( $map[ $handle ] );
	return preg_replace_callback(
		'/<script\b([^>]*)>/i',
		static function ( $m ) use ( $cat ) {
			$attrs = $m[1];
			$type  = '';
			if ( preg_match( '/\stype=(["\'])(.*?)\1/i', $attrs, $t ) ) {
				$type  = $t[2];
				$attrs = str_replace( $t[0], '', $attrs );
			}
			$keep = ( '' !== $type && 'text/javascript' !== strtolower( $type ) ) ? ' data-ppd-type="' . esc_attr( $type ) . '"' : '';
			return '<script type="text/plain" data-ppd-category="' . $cat . '"' . $keep . $attrs . '>';
		},
		$tag
	);
}
add_filter( 'script_loader_tag', 'ppd_filter_script_tag', 20, 2 );

// -----------------------------------------------------------------------------
// 7. Third-party embeds (YouTube, Vimeo, Maps, social, audio)
// -----------------------------------------------------------------------------

/**
 * Embed providers: host fragment → display name.
 *
 * @return array<string,string>
 */
function ppd_embed_providers() {
	return (array) apply_filters(
		'ppd_embed_providers',
		array(
			'youtube.com'          => 'YouTube',
			'youtube-nocookie.com' => 'YouTube',
			'youtu.be'             => 'YouTube',
			'vimeo.com'            => 'Vimeo',
			'google.com/maps'      => 'Google Maps',
			'maps.google.'         => 'Google Maps',
			'spotify.com'          => 'Spotify',
			'soundcloud.com'       => 'SoundCloud',
			'twitter.com'          => 'X (Twitter)',
			'platform.x.com'       => 'X (Twitter)',
			'instagram.com'        => 'Instagram',
			'facebook.com'         => 'Facebook',
			'tiktok.com'           => 'TikTok',
			'dailymotion.com'      => 'Dailymotion',
			'linkedin.com/embed'   => 'LinkedIn',
			'calendly.com'         => 'Calendly',
		)
	);
}

/**
 * Provider name for a piece of HTML, or '' when none matches.
 *
 * @param string $html HTML.
 * @return string
 */
function ppd_match_provider( $html ) {
	foreach ( ppd_embed_providers() as $needle => $name ) {
		if ( false !== stripos( $html, $needle ) ) {
			return $name;
		}
	}
	return '';
}

/**
 * Placeholder that holds the original embed in a <template>.
 *
 * @param string $html     Original HTML.
 * @param string $provider Provider name.
 * @return string
 */
function ppd_embed_placeholder( $html, $provider ) {
	$s   = ppd_settings();
	$cat = in_array( $s['embeds_category'], ppd_categories(), true ) ? $s['embeds_category'] : 'marketing';
	// Mark iframes so the content filter does not wrap them a second time.
	$html = preg_replace( '/<iframe\b/i', '<iframe data-ppd-done="1"', $html );
	$text = sprintf( ppd_text( 'embed_text' ), $provider );
	return sprintf(
		'<div class="ppd-embed" data-ppd-category="%1$s" data-ppd-service="%2$s"><div class="ppd-embed__ph"><p>%3$s</p><p class="ppd-embed__actions"><button type="button" class="ppd-btn ppd-btn--primary" data-ppd-embed="load">%4$s</button> <button type="button" class="ppd-btn ppd-btn--link" data-ppd-embed="always">%5$s</button></p></div><template>%6$s</template></div>',
		esc_attr( $cat ),
		esc_attr( $provider ),
		esc_html( $text ),
		esc_html( ppd_text( 'embed_load' ) ),
		esc_html( ppd_text( 'embed_always' ) . ' (' . ppd_text( $cat . '_label' ) . ')' ),
		$html
	);
}

/**
 * Wraps oEmbed output from known providers.
 *
 * @param string $html oEmbed HTML.
 * @return string
 */
function ppd_filter_oembed( $html ) {
	if ( ! ppd_is_active() || empty( ppd_settings()['embeds_block'] ) || false !== strpos( (string) $html, 'ppd-embed' ) ) {
		return $html;
	}
	$provider = ppd_match_provider( (string) $html );
	return '' === $provider ? $html : ppd_embed_placeholder( $html, $provider );
}
add_filter( 'embed_oembed_html', 'ppd_filter_oembed', 99 );

/**
 * Wraps loose <iframe> tags from known providers in post content and widgets
 * (maps pasted as HTML, custom HTML blocks).
 *
 * @param string $content HTML.
 * @return string
 */
function ppd_filter_content_iframes( $content ) {
	if ( ! ppd_is_active() || empty( ppd_settings()['embeds_block'] ) || false === stripos( (string) $content, '<iframe' ) ) {
		return $content;
	}
	return preg_replace_callback(
		'#<iframe\b(?![^>]*data-ppd-done)[^>]*>.*?</iframe>#is',
		static function ( $m ) {
			if ( ! preg_match( '/\ssrc=(["\'])(.*?)\1/i', $m[0], $src ) ) {
				return $m[0];
			}
			$provider = ppd_match_provider( $src[2] );
			return '' === $provider ? $m[0] : ppd_embed_placeholder( $m[0], $provider );
		},
		$content
	);
}
add_filter( 'the_content', 'ppd_filter_content_iframes', 999 );
add_filter( 'widget_text_content', 'ppd_filter_content_iframes', 999 );
add_filter( 'widget_block_content', 'ppd_filter_content_iframes', 999 );

// -----------------------------------------------------------------------------
// 8. Banner, preferences dialog and floating button
// -----------------------------------------------------------------------------

/**
 * Category rows for the preferences dialog.
 */
function ppd_render_category_rows() {
	printf(
		'<div class="ppd-cat"><div class="ppd-cat__head"><span class="ppd-cat__name" id="ppd-cat-necessary">%1$s</span><span class="ppd-cat__always">%2$s</span></div><p class="ppd-cat__desc">%3$s</p></div>',
		esc_html( ppd_text( 'necessary_label' ) ),
		esc_html( ppd_text( 'always_on' ) ),
		esc_html( ppd_text( 'necessary_desc' ) )
	);
	foreach ( ppd_enabled_categories() as $cat ) {
		printf(
			'<div class="ppd-cat"><div class="ppd-cat__head"><label class="ppd-cat__name" for="ppd-cb-%1$s">%2$s</label><input class="ppd-switch" type="checkbox" role="switch" id="ppd-cb-%1$s" name="%1$s" value="1" aria-describedby="ppd-desc-%1$s"></div><p class="ppd-cat__desc" id="ppd-desc-%1$s">%3$s</p></div>',
			esc_attr( $cat ),
			esc_html( ppd_text( $cat . '_label' ) ),
			esc_html( ppd_text( $cat . '_desc' ) )
		);
	}
}

/**
 * Markup, printed in the footer and kept hidden until the JS decides.
 * Nothing here is a heading, so the page outline is not affected.
 */
function ppd_render_markup() {
	if ( ! ppd_is_active() ) {
		return;
	}
	$s        = ppd_settings();
	$policy   = ppd_policy_url();
	$modal    = 'modal' === $s['position'];
	$tag      = $modal ? 'dialog' : 'section';
	$position = in_array( $s['position'], array( 'bar', 'box', 'modal' ), true ) ? $s['position'] : 'bar';
	$policy_a = $policy ? sprintf( ' <a class="ppd-link" href="%1$s">%2$s</a>', esc_url( $policy ), esc_html( ppd_text( 'policy_label' ) ) ) : '';
	?>
<div id="ppd-root" class="ppd-root">
	<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed value. ?> id="ppd-banner" class="ppd-banner ppd-banner--<?php echo esc_attr( $position ); ?>" <?php echo $modal ? 'aria-modal="true"' : 'role="region"'; ?> aria-labelledby="ppd-banner-title" aria-describedby="ppd-banner-msg" hidden>
		<div class="ppd-banner__inner">
			<div class="ppd-banner__text">
				<p class="ppd-banner__title" id="ppd-banner-title"><?php echo esc_html( ppd_text( 'title' ) ); ?></p>
				<p class="ppd-banner__msg" id="ppd-banner-msg">
					<span data-ppd-show="optin"><?php echo esc_html( ppd_text( 'message' ) ); ?></span>
					<span data-ppd-show="optout" hidden><?php echo esc_html( ppd_text( 'message_optout' ) ); ?></span>
					<span class="ppd-gpc" data-ppd-show="gpc" hidden><?php echo esc_html( ppd_text( 'gpc_notice' ) ); ?></span>
					<?php echo $policy_a; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
				</p>
			</div>
			<div class="ppd-banner__actions" data-ppd-show="optin">
				<button type="button" class="ppd-btn ppd-btn--primary" data-ppd-action="reject"><?php echo esc_html( ppd_text( 'reject' ) ); ?></button>
				<button type="button" class="ppd-btn ppd-btn--ghost" data-ppd-action="customize"><?php echo esc_html( ppd_text( 'customize' ) ); ?></button>
				<button type="button" class="ppd-btn ppd-btn--primary" data-ppd-action="accept"><?php echo esc_html( ppd_text( 'accept' ) ); ?></button>
			</div>
			<div class="ppd-banner__actions" data-ppd-show="optout" hidden>
				<button type="button" class="ppd-btn ppd-btn--ghost" data-ppd-action="optout"><?php echo esc_html( ppd_text( 'dns_label' ) ); ?></button>
				<button type="button" class="ppd-btn ppd-btn--primary" data-ppd-action="acknowledge"><?php echo esc_html( ppd_text( 'acknowledge' ) ); ?></button>
			</div>
		</div>
	</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed value. ?>>

	<dialog id="ppd-prefs" class="ppd-prefs" aria-labelledby="ppd-prefs-title">
		<form method="dialog" class="ppd-prefs__form">
			<div class="ppd-prefs__head">
				<p class="ppd-prefs__title" id="ppd-prefs-title"><?php echo esc_html( ppd_text( 'prefs_title' ) ); ?></p>
				<button type="button" class="ppd-prefs__close" data-ppd-action="close" aria-label="<?php echo esc_attr( ppd_text( 'close' ) ); ?>">&times;</button>
			</div>
			<p class="ppd-prefs__intro"><?php echo esc_html( ppd_text( 'prefs_intro' ) ); ?><?php echo $policy_a; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></p>
			<div class="ppd-prefs__cats"><?php ppd_render_category_rows(); ?></div>
			<p class="ppd-dns-done" data-ppd-show="dnsdone" role="status" hidden><?php echo esc_html( ppd_text( 'dns_done' ) ); ?></p>
			<div class="ppd-prefs__actions">
				<button type="button" class="ppd-btn ppd-btn--primary" data-ppd-action="reject"><?php echo esc_html( ppd_text( 'reject' ) ); ?></button>
				<button type="button" class="ppd-btn ppd-btn--ghost" data-ppd-action="save"><?php echo esc_html( ppd_text( 'save' ) ); ?></button>
				<button type="button" class="ppd-btn ppd-btn--primary" data-ppd-action="accept"><?php echo esc_html( ppd_text( 'accept' ) ); ?></button>
			</div>
		</form>
	</dialog>

	<?php if ( ! empty( $s['floating_button'] ) ) : ?>
	<button type="button" id="ppd-fab" class="ppd-fab" data-ppd-open aria-label="<?php echo esc_attr( ppd_text( 'settings_button' ) ); ?>" title="<?php echo esc_attr( ppd_text( 'settings_button' ) ); ?>" hidden>
		<svg aria-hidden="true" focusable="false" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5Zm-4.5 9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Zm1.5 5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Zm6 1a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/></svg>
	</button>
	<?php endif; ?>
</div>
	<?php
}
add_action( 'wp_footer', 'ppd_render_markup', 5 );

// -----------------------------------------------------------------------------
// 9. Shortcodes
// -----------------------------------------------------------------------------

/**
 * [ppd_cookie_settings text="..."]: button that reopens the preferences.
 *
 * @param array $atts Attributes.
 * @return string
 */
function ppd_shortcode_settings( $atts ) {
	$atts = shortcode_atts( array( 'text' => ppd_text( 'settings_button' ) ), $atts, 'ppd_cookie_settings' );
	return sprintf( '<button type="button" class="ppd-inline-btn" data-ppd-open>%s</button>', esc_html( $atts['text'] ) );
}
add_shortcode( 'ppd_cookie_settings', 'ppd_shortcode_settings' );

/**
 * [ppd_do_not_sell text="..."]: CCPA/CPRA opt-out link, for the footer.
 *
 * @param array $atts Attributes.
 * @return string
 */
function ppd_shortcode_dns( $atts ) {
	$atts = shortcode_atts( array( 'text' => ppd_text( 'dns_label' ) ), $atts, 'ppd_do_not_sell' );
	return sprintf( '<button type="button" class="ppd-inline-btn" data-ppd-action="optout">%s</button>', esc_html( $atts['text'] ) );
}
add_shortcode( 'ppd_do_not_sell', 'ppd_shortcode_dns' );

/**
 * [ppd_cookie_table]: categories and descriptions, for the privacy policy.
 *
 * @return string
 */
function ppd_shortcode_table() {
	$rows = array( array( ppd_text( 'necessary_label' ), ppd_text( 'necessary_desc' ) ) );
	foreach ( ppd_enabled_categories() as $cat ) {
		$rows[] = array( ppd_text( $cat . '_label' ), ppd_text( $cat . '_desc' ) );
	}
	$html = '<table class="ppd-table"><thead><tr><th scope="col">' . esc_html( ppd_t( 'Category', 'Categoria' ) ) . '</th><th scope="col">' . esc_html( ppd_t( 'Purpose', 'Finalidade' ) ) . '</th></tr></thead><tbody>';
	foreach ( $rows as $r ) {
		$html .= '<tr><td>' . esc_html( $r[0] ) . '</td><td>' . esc_html( $r[1] ) . '</td></tr>';
	}
	return $html . '</tbody></table>';
}
add_shortcode( 'ppd_cookie_table', 'ppd_shortcode_table' );

// -----------------------------------------------------------------------------
// 10. CSS and JS
// -----------------------------------------------------------------------------

/**
 * Validated hex color or the fallback.
 *
 * @param string $value    Color.
 * @param string $fallback Fallback.
 * @return string
 */
function ppd_color( $value, $fallback ) {
	$c = sanitize_hex_color( (string) $value );
	return $c ? $c : $fallback;
}

/**
 * Banner CSS (scoped, colors from the settings).
 *
 * @return string
 */
function ppd_css() {
	$s  = ppd_settings();
	$d  = ppd_default_settings();
	$bg = ppd_color( $s['color_bg'], $d['color_bg'] );
	$tx = ppd_color( $s['color_text'], $d['color_text'] );
	$ac = ppd_color( $s['color_accent'], $d['color_accent'] );
	$at = ppd_color( $s['color_accent_text'], $d['color_accent_text'] );
	return <<<CSS
.ppd-root{--ppd-bg:{$bg};--ppd-tx:{$tx};--ppd-ac:{$ac};--ppd-at:{$at};font:inherit;font-size:15px;line-height:1.5}
.ppd-root [hidden],.ppd-embed [hidden]{display:none!important}
.ppd-banner{position:fixed;z-index:2147483000;box-sizing:border-box;margin:0;background:var(--ppd-bg);color:var(--ppd-tx);box-shadow:0 -2px 16px rgba(0,0,0,.25);border:0;padding:0}
.ppd-banner--bar{left:0;right:0;bottom:0;width:100%}
.ppd-banner--box{left:16px;bottom:16px;max-width:420px;border-radius:12px}
.ppd-banner--modal{max-width:560px;width:calc(100% - 32px);border-radius:12px;position:fixed;inset:0;margin:auto;height:fit-content}
.ppd-banner--modal::backdrop{background:rgba(0,0,0,.55)}
.ppd-banner__inner{display:flex;flex-wrap:wrap;gap:12px 24px;align-items:center;justify-content:space-between;padding:16px 20px;max-width:1200px;margin:0 auto}
.ppd-banner--box .ppd-banner__inner,.ppd-banner--modal .ppd-banner__inner{flex-direction:column;align-items:stretch}
.ppd-banner--box .ppd-banner__text,.ppd-banner--modal .ppd-banner__text{flex:0 0 auto}
.ppd-banner__text{flex:1 1 420px;min-width:0}
.ppd-banner__title{margin:0 0 4px;font-weight:700;font-size:1.05em}
.ppd-banner__msg{margin:0}
.ppd-gpc{display:block;margin-top:6px;font-style:italic}
.ppd-banner__actions,.ppd-prefs__actions{display:flex;flex-wrap:wrap;gap:8px}
.ppd-link{color:inherit;text-decoration:underline;text-underline-offset:2px}
.ppd-btn{font:inherit;cursor:pointer;border-radius:8px;padding:10px 16px;min-height:44px;border:2px solid var(--ppd-ac);line-height:1.2}
.ppd-btn--primary{background:var(--ppd-ac);color:var(--ppd-at)}
.ppd-btn--ghost{background:transparent;color:inherit;border-color:currentColor}
.ppd-btn--link{background:none;border:0;padding:10px 4px;color:inherit;text-decoration:underline}
.ppd-btn:focus-visible,.ppd-fab:focus-visible,.ppd-switch:focus-visible,.ppd-prefs__close:focus-visible,.ppd-inline-btn:focus-visible{outline:3px solid var(--ppd-ac);outline-offset:2px;box-shadow:0 0 0 5px #fff}
.ppd-prefs{max-width:560px;width:calc(100% - 32px);border:0;border-radius:12px;padding:0;background:var(--ppd-bg);color:var(--ppd-tx)}
.ppd-prefs::backdrop{background:rgba(0,0,0,.55)}
.ppd-prefs__form{padding:20px;margin:0}
.ppd-prefs__head{display:flex;justify-content:space-between;align-items:center;gap:12px}
.ppd-prefs__title{margin:0;font-weight:700;font-size:1.15em}
.ppd-prefs__close{background:none;border:0;color:inherit;font-size:28px;line-height:1;cursor:pointer;min-width:44px;min-height:44px}
.ppd-prefs__intro{margin:8px 0 12px}
.ppd-cat{border-top:1px solid rgba(127,127,127,.35);padding:12px 0}
.ppd-cat__head{display:flex;justify-content:space-between;align-items:center;gap:12px}
.ppd-cat__name{font-weight:700}
.ppd-cat__always{font-size:.9em;opacity:.85}
.ppd-cat__desc{margin:4px 0 0;font-size:.93em;opacity:.9}
.ppd-switch{appearance:none;-webkit-appearance:none;width:46px;height:26px;border-radius:13px;background:#6b7280;position:relative;cursor:pointer;flex:0 0 auto;margin:0;border:2px solid transparent}
.ppd-switch::after{content:"";position:absolute;top:2px;left:2px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .15s}
.ppd-switch:checked{background:var(--ppd-ac)}
.ppd-switch:checked::after{transform:translateX(20px)}
.ppd-dns-done{margin:8px 0;font-weight:700}
.ppd-prefs__actions{margin-top:12px;justify-content:flex-end}
.ppd-fab{position:fixed;left:16px;bottom:16px;z-index:2147482999;width:48px;height:48px;border-radius:50%;border:0;background:var(--ppd-bg);color:var(--ppd-tx);box-shadow:0 2px 10px rgba(0,0,0,.3);cursor:pointer;display:flex;align-items:center;justify-content:center}
.ppd-inline-btn{font:inherit;background:none;border:0;padding:0;color:inherit;text-decoration:underline;cursor:pointer}
.ppd-embed{position:relative;background:#f3f4f6;color:#111827;border:1px solid #d1d5db;border-radius:8px;padding:24px;margin:1em 0;text-align:center;min-height:160px;display:flex;align-items:center;justify-content:center}
.ppd-embed p{margin:0 0 12px}
.ppd-embed .ppd-btn--primary{background:#1d4ed8;color:#fff;border-color:#1d4ed8}
.ppd-embed .ppd-btn--link{color:#1d4ed8}
.ppd-table{width:100%;border-collapse:collapse}.ppd-table th,.ppd-table td{border:1px solid #d1d5db;padding:8px;text-align:left;vertical-align:top}
@media (max-width:600px){.ppd-banner__actions .ppd-btn,.ppd-prefs__actions .ppd-btn{flex:1 1 100%}.ppd-banner--box{left:8px;right:8px;bottom:8px;max-width:none}}
@media (prefers-reduced-motion:reduce){.ppd-switch::after{transition:none}}
CSS;
}

/**
 * Visitor-side script (vanilla JS, no dependencies).
 *
 * @return string
 */
function ppd_js() {
	return <<<'JS'
(function () {
  'use strict';
  var C = window.ppdConfig;
  if (!C) { return; }
  var root = document.getElementById('ppd-root');
  if (!root) { return; }
  var banner = document.getElementById('ppd-banner');
  var prefs = document.getElementById('ppd-prefs');
  var fab = document.getElementById('ppd-fab');
  var gpc = C.respectGpc && navigator.globalPrivacyControl === true;
  var regime = C.regime === 'auto' ? null : C.regime;
  var state = read();

  function read() {
    try {
      var m = document.cookie.match(new RegExp('(?:^|; )' + C.cookie + '=([^;]*)'));
      if (!m) { return null; }
      var d = JSON.parse(decodeURIComponent(m[1]));
      if (!d || String(d.v) !== String(C.version) || !d.c) { return null; }
      return d;
    } catch (e) { return null; }
  }

  function write(d) {
    document.cookie = C.cookie + '=' + encodeURIComponent(JSON.stringify(d)) +
      '; Max-Age=' + (C.expiry * 86400) + '; Path=/; SameSite=Lax' +
      (location.protocol === 'https:' ? '; Secure' : '');
  }

  function uuid() {
    if (window.crypto && crypto.randomUUID) { return crypto.randomUUID(); }
    var b = new Uint8Array(16);
    (window.crypto || window.msCrypto).getRandomValues(b);
    b[6] = (b[6] & 15) | 64; b[8] = (b[8] & 63) | 128;
    var h = Array.prototype.map.call(b, function (x) { return (x + 256).toString(16).slice(1); }).join('');
    return h.slice(0, 8) + '-' + h.slice(8, 12) + '-' + h.slice(12, 16) + '-' + h.slice(16, 20) + '-' + h.slice(20);
  }

  function all(v) {
    var c = {};
    C.cats.forEach(function (k) { c[k] = v ? 1 : 0; });
    return c;
  }

  function gcm(c) {
    if (!C.gcm || typeof window.gtag !== 'function') { return; }
    var on = function (k) { return C.cats.indexOf(k) > -1 && c[k] ? 'granted' : 'denied'; };
    window.gtag('consent', 'update', {
      analytics_storage: on('analytics'),
      ad_storage: on('marketing'),
      ad_user_data: on('marketing'),
      ad_personalization: on('marketing'),
      functionality_storage: on('preferences'),
      personalization_storage: on('preferences')
    });
  }

  function revive(old) {
    var s = document.createElement('script');
    for (var i = 0; i < old.attributes.length; i++) {
      var a = old.attributes[i];
      if (a.name === 'type' || a.name.indexOf('data-ppd-') === 0) { continue; }
      s.setAttribute(a.name, a.value);
    }
    if (old.getAttribute('data-ppd-type')) { s.type = old.getAttribute('data-ppd-type'); }
    if (old.getAttribute('data-ppd-src')) { s.src = old.getAttribute('data-ppd-src'); }
    if (s.src) { s.async = old.hasAttribute('async'); } else { s.text = old.text || old.textContent; }
    return s;
  }

  function unpack(tpl) {
    var frag = tpl.content ? tpl.content.cloneNode(true) : document.createRange().createContextualFragment(tpl.innerHTML);
    Array.prototype.forEach.call(frag.querySelectorAll('script'), function (o) { o.parentNode.replaceChild(revive(o), o); });
    return frag;
  }

  function activateEl(el) {
    if (el.getAttribute('data-ppd-active')) { return; }
    el.setAttribute('data-ppd-active', '1');
    var tag = el.tagName;
    if (tag === 'SCRIPT') {
      el.parentNode.replaceChild(revive(el), el);
    } else if (tag === 'TEMPLATE') {
      el.parentNode.replaceChild(unpack(el), el);
    } else if (el.classList.contains('ppd-embed')) {
      var t = el.querySelector('template');
      if (t) { el.parentNode.replaceChild(unpack(t), el); }
    } else if (el.getAttribute('data-ppd-src')) {
      el.setAttribute('src', el.getAttribute('data-ppd-src'));
    }
  }

  var allowed = null;

  function activate(c) {
    allowed = c;
    Array.prototype.forEach.call(document.querySelectorAll('[data-ppd-category]'), function (el) {
      var k = el.getAttribute('data-ppd-category');
      if (k === 'necessary' || c[k]) { activateEl(el); }
    });
  }

  // Elements added later (lazy content, AJAX) follow the current choice.
  if (window.MutationObserver) {
    new MutationObserver(function (list) {
      if (!allowed) { return; }
      list.forEach(function (m) {
        Array.prototype.forEach.call(m.addedNodes, function (n) {
          if (n.nodeType !== 1) { return; }
          var els = n.matches && n.matches('[data-ppd-category]') ? [n] : [];
          if (n.querySelectorAll) { els = els.concat(Array.prototype.slice.call(n.querySelectorAll('[data-ppd-category]'))); }
          els.forEach(function (el) {
            var k = el.getAttribute('data-ppd-category');
            if (!el.getAttribute('data-ppd-active') && (k === 'necessary' || allowed[k])) { activateEl(el); }
          });
        });
      });
    }).observe(document.documentElement, { childList: true, subtree: true });
  }

  function clearCookies(list) {
    var names = document.cookie.split(';').map(function (p) { return p.split('=')[0].trim(); });
    var host = location.hostname.split('.');
    var domains = [''];
    for (var i = 0; i < host.length - 1; i++) { domains.push('; domain=.' + host.slice(i).join('.')); }
    names.forEach(function (n) {
      var hit = list.some(function (p) { return p && n.indexOf(p) === 0; });
      if (!hit) { return; }
      domains.forEach(function (d) { document.cookie = n + '=; Max-Age=0; Path=/' + d; });
    });
  }

  function apply(c) {
    gcm(c);
    activate(c);
    if (window.dataLayer) { window.dataLayer.push({ event: 'ppd_consent', ppd_categories: c }); }
    try { document.dispatchEvent(new CustomEvent('ppd:consent', { detail: c })); } catch (e) {}
  }

  function log(action) {
    if (!C.log || !state) { return; }
    try {
      var h = { 'Content-Type': 'application/json' };
      if (C.nonce) { h['X-WP-Nonce'] = C.nonce; }
      fetch(C.rest + 'consent', {
        method: 'POST', credentials: 'same-origin', keepalive: true, headers: h,
        body: JSON.stringify({ id: state.id, action: action, categories: state.c, regime: state.r, gpc: !!gpc, url: location.href })
      }).catch(function () {});
    } catch (e) {}
  }

  function show(el, on) { if (el) { el.hidden = !on; } }

  function toggle(scope, key, on) {
    Array.prototype.forEach.call(scope.querySelectorAll('[data-ppd-show="' + key + '"]'), function (e) { e.hidden = !on; });
  }

  function openBanner() {
    toggle(banner, 'optin', regime === 'optin');
    toggle(banner, 'optout', regime === 'optout');
    toggle(banner, 'gpc', regime === 'optout' && gpc);
    banner.hidden = false;
    if (banner.tagName === 'DIALOG' && banner.showModal && !banner.open) {
      banner.showModal();
    }
    show(fab, false);
  }

  function closeBanner() {
    if (banner.tagName === 'DIALOG' && banner.open) { banner.close(); }
    banner.hidden = true;
    show(fab, true);
  }

  function current() {
    if (state) { return state.c; }
    if (regime === 'optout') { var c = all(true); if (gpc) { c.marketing = 0; } return c; }
    return all(false);
  }

  function openPrefs() {
    var c = current();
    C.cats.forEach(function (k) {
      var cb = prefs.querySelector('input[name="' + k + '"]');
      if (cb) { cb.checked = !!c[k]; }
    });
    toggle(prefs, 'dnsdone', false);
    if (prefs.showModal) { prefs.showModal(); } else { prefs.setAttribute('open', ''); }
  }

  function closePrefs() {
    if (prefs.open) { if (prefs.close) { prefs.close(); } else { prefs.removeAttribute('open'); } }
  }

  function save(c, action) {
    // What was running before this choice (opt-out runs tags before any choice).
    var before = state ? state.c : (regime === 'optout' ? current() : null);
    if (gpc && regime === 'optout' && action !== 'embed') { c.marketing = 0; }
    state = { v: C.version, c: c, r: regime || 'optin', t: Math.floor(Date.now() / 1000), id: (state && state.id) || uuid(), g: gpc ? 1 : 0 };
    write(state);
    apply(c);
    log(action);
    closeBanner();
    // Scripts that already ran cannot be unloaded: withdrawing needs a reload.
    var revoked = before && C.cats.some(function (k) { return before[k] && !c[k]; });
    if (revoked) {
      C.cats.forEach(function (k) { if (!c[k]) { clearCookies(C.cookieNames[k] || []); } });
      if (C.reload) { setTimeout(function () { location.reload(); }, 150); }
    }
  }

  function fromPrefs() {
    var c = {};
    C.cats.forEach(function (k) {
      var cb = prefs.querySelector('input[name="' + k + '"]');
      c[k] = cb && cb.checked ? 1 : 0;
    });
    return c;
  }

  document.addEventListener('click', function (ev) {
    var t = ev.target.closest ? ev.target.closest('[data-ppd-action],[data-ppd-open],[data-ppd-embed]') : null;
    if (!t) { return; }
    if (t.hasAttribute('data-ppd-open')) { ev.preventDefault(); openPrefs(); return; }
    var e = t.getAttribute('data-ppd-embed');
    if (e) {
      var box = t.closest('.ppd-embed');
      if (!box) { return; }
      if (e === 'load') { activateEl(box); return; }
      var cat = box.getAttribute('data-ppd-category');
      var c = Object.assign({}, current()); c[cat] = 1;
      save(c, 'embed');
      return;
    }
    var a = t.getAttribute('data-ppd-action');
    var inPrefs = prefs.contains(t);
    if (a === 'accept') { save(all(true), 'accept_all'); closePrefs(); }
    else if (a === 'reject') { save(all(false), 'reject_all'); closePrefs(); }
    else if (a === 'customize') { openPrefs(); }
    else if (a === 'save') { save(fromPrefs(), 'custom'); closePrefs(); }
    else if (a === 'close') { closePrefs(); }
    else if (a === 'acknowledge') { save(current(), 'acknowledge'); }
    else if (a === 'optout') {
      if (!regime) { regime = 'optout'; }
      var o = Object.assign({}, current()); o.marketing = 0;
      save(o, 'optout');
      if (!inPrefs) { openPrefs(); }
      toggle(prefs, 'dnsdone', true);
    }
  });

  // Escape does not dismiss the modal banner: a choice is required.
  banner.addEventListener('cancel', function (ev) { ev.preventDefault(); });

  function start() {
    if (state) {
      if (!regime) { regime = state.r; }
      apply(state.c);
      if (gpc && regime === 'optout' && state.c.marketing) {
        var c = Object.assign({}, state.c); c.marketing = 0;
        save(c, 'gpc');
      }
      show(fab, true);
      return;
    }
    if (regime === 'optout') {
      var d = current();
      apply(d);
      openBanner();
      return;
    }
    openBanner();
  }

  function boot() {
    if (regime) {
      start();
    } else {
      var cached = null;
      try { cached = sessionStorage.getItem('ppd_regime'); } catch (e) {}
      if (state && state.r) { regime = state.r; start(); }
      else if (cached === 'optin' || cached === 'optout') { regime = cached; start(); }
      else {
        fetch(C.rest + 'geo', { credentials: 'omit', cache: 'no-store' })
          .then(function (r) { return r.json(); })
          .then(function (j) {
            regime = j && j.regime === 'optout' ? 'optout' : 'optin';
            try { sessionStorage.setItem('ppd_regime', regime); } catch (e) {}
            start();
          })
          .catch(function () { regime = 'optin'; start(); });
      }
    }
  }

  // Wait for the whole document, so scripts printed after this one are found.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
JS;
}

/**
 * Registers the CSS and JS (no files: inline, attached to empty handles).
 */
function ppd_enqueue() {
	if ( ! ppd_is_active() ) {
		return;
	}
	$s          = ppd_settings();
	$cookie_map = array();
	foreach ( ppd_categories() as $cat ) {
		$cookie_map[ $cat ] = array_values( array_filter( array_map( 'trim', explode( ',', (string) $s[ 'cookies_' . $cat ] ) ) ) );
	}
	$config = array(
		'cookie'      => PPD_COOKIE,
		'version'     => (string) $s['policy_version'],
		'expiry'      => max( 1, min( 395, (int) $s['expiry_days'] ) ),
		'regime'      => ppd_regime_setting(),
		'cats'        => ppd_enabled_categories(),
		'gcm'         => ! empty( $s['gcm_enabled'] ),
		'respectGpc'  => ! empty( $s['respect_gpc'] ),
		'reload'      => ! empty( $s['reload_on_revoke'] ),
		'log'         => ! empty( $s['log_enabled'] ),
		'rest'        => esc_url_raw( rest_url( 'ppd/v1/' ) ),
		'nonce'       => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
		'cookieNames' => $cookie_map,
	);
	wp_register_style( 'ppd', false, array(), PPD_VERSION );
	wp_enqueue_style( 'ppd' );
	wp_add_inline_style( 'ppd', ppd_css() );
	wp_register_script( 'ppd', false, array(), PPD_VERSION, true );
	wp_enqueue_script( 'ppd' );
	wp_add_inline_script( 'ppd', 'window.ppdConfig=' . wp_json_encode( $config ) . ';', 'before' );
	wp_add_inline_script( 'ppd', ppd_js() );
}
add_action( 'wp_enqueue_scripts', 'ppd_enqueue' );

// -----------------------------------------------------------------------------
// 11. Admin: Settings > Cookies
// -----------------------------------------------------------------------------

/**
 * Settings tabs: key → [label, checkbox keys on that tab].
 *
 * @return array<string,array>
 */
function ppd_admin_tabs() {
	$cat_boxes = array();
	foreach ( ppd_categories() as $cat ) {
		$cat_boxes[] = 'cat_' . $cat;
	}
	return array(
		'general'    => array( ppd_t( 'General', 'Geral' ), array( 'enabled', 'respect_gpc', 'reload_on_revoke' ) ),
		'appearance' => array( ppd_t( 'Appearance', 'Aparência' ), array( 'floating_button' ) ),
		'texts'      => array( ppd_t( 'Texts', 'Textos' ), array() ),
		'categories' => array( ppd_t( 'Categories and scripts', 'Categorias e scripts' ), $cat_boxes ),
		'google'     => array( ppd_t( 'Google Consent Mode', 'Google Consent Mode' ), array( 'gcm_enabled', 'ads_redaction', 'url_passthrough' ) ),
		'blocking'   => array( ppd_t( 'Embeds', 'Embeds' ), array( 'embeds_block' ) ),
		'log'        => array( ppd_t( 'Consent log', 'Registro de consentimento' ), array( 'log_enabled' ) ),
	);
}

add_action(
	'admin_menu',
	static function () {
		add_options_page(
			ppd_t( 'Cookies and privacy', 'Cookies e privacidade' ),
			ppd_t( 'Cookies', 'Cookies' ),
			'manage_options',
			'ppd-settings',
			'ppd_render_settings_page'
		);
	}
);

add_action(
	'admin_init',
	static function () {
		register_setting(
			'ppd',
			PPD_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => 'ppd_sanitize_settings',
				'default'           => array(),
			)
		);
	}
);

/**
 * Sanitizes the settings. Only the submitted tab changes; the rest is kept.
 *
 * @param mixed $input Raw input.
 * @return array
 */
function ppd_sanitize_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = ppd_default_settings();
	$saved    = get_option( PPD_OPTION, array() );
	$out      = wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
	$tab      = isset( $input['_tab'] ) ? sanitize_key( $input['_tab'] ) : '';
	$tabs     = ppd_admin_tabs();

	// Unchecked boxes are not sent: reset the ones that belong to this tab.
	if ( isset( $tabs[ $tab ] ) ) {
		foreach ( $tabs[ $tab ][1] as $box ) {
			$out[ $box ] = empty( $input[ $box ] ) ? 0 : 1;
		}
	}

	$choices = array(
		'regime'          => array( 'optin', 'optout', 'auto' ),
		'auto_unknown'    => array( 'optin', 'optout' ),
		'position'        => array( 'bar', 'box', 'modal' ),
		'gcm_mode'        => array( 'advanced', 'basic' ),
		'embeds_category' => ppd_categories(),
	);
	foreach ( $input as $key => $value ) {
		if ( '_tab' === $key || ! array_key_exists( $key, $defaults ) ) {
			continue;
		}
		$value = is_string( $value ) ? wp_unslash( $value ) : $value;
		if ( isset( $choices[ $key ] ) ) {
			if ( in_array( $value, $choices[ $key ], true ) ) {
				$out[ $key ] = $value;
			}
		} elseif ( 0 === strpos( $key, 'color_' ) ) {
			$c           = sanitize_hex_color( $value );
			$out[ $key ] = $c ? $c : $defaults[ $key ];
		} elseif ( in_array( $key, array( 'expiry_days', 'log_retention' ), true ) ) {
			$out[ $key ] = max( 1, absint( $value ) );
		} elseif ( 'policy_url' === $key ) {
			$out[ $key ] = esc_url_raw( trim( (string) $value ) );
		} elseif ( 'policy_version' === $key ) {
			$v           = preg_replace( '/[^A-Za-z0-9._-]/', '', (string) $value );
			$out[ $key ] = '' === $v ? '1' : substr( $v, 0, 20 );
		} elseif ( 'optin_countries' === $key ) {
			$codes       = array_filter( array_map( 'trim', explode( ',', strtoupper( (string) $value ) ) ) );
			$codes       = array_filter(
				$codes,
				static function ( $c ) {
					return (bool) preg_match( '/^[A-Z]{2}$/', $c );
				}
			);
			$out[ $key ] = implode( ',', array_unique( $codes ) );
		} elseif ( in_array( $key, array( 'gtm_id', 'ga4_id' ), true ) ) {
			$out[ $key ] = strtoupper( preg_replace( '/[^A-Za-z0-9-]/', '', (string) $value ) );
		} elseif ( 0 === strpos( $key, 'scripts_' ) ) {
			// Raw HTML/JS: only for users allowed to post unfiltered HTML.
			if ( current_user_can( 'unfiltered_html' ) ) {
				$out[ $key ] = trim( (string) $value );
			}
		} elseif ( 'script_handles' === $key ) {
			$lines = array();
			foreach ( preg_split( '/\r\n|\r|\n/', (string) $value ) as $line ) {
				$line = trim( sanitize_text_field( $line ) );
				if ( '' !== $line ) {
					$lines[] = $line;
				}
			}
			$out[ $key ] = implode( "\n", $lines );
		} elseif ( 0 === strpos( $key, 'cookies_' ) ) {
			$out[ $key ] = implode( ',', array_filter( array_map( 'trim', explode( ',', sanitize_text_field( (string) $value ) ) ) ) );
		} elseif ( 0 === strpos( $key, 'text_' ) ) {
			$out[ $key ] = sanitize_textarea_field( (string) $value );
		} elseif ( is_int( $defaults[ $key ] ) ) {
			$out[ $key ] = empty( $value ) ? 0 : 1;
		}
	}

	// "Ask everyone again": bump the policy version.
	if ( ! empty( $input['_renew'] ) ) {
		$v                     = (string) $out['policy_version'];
		$out['policy_version'] = ctype_digit( $v ) ? (string) ( (int) $v + 1 ) : $v . '.1';
	}
	return $out;
}

/**
 * Field helpers for the settings page.
 *
 * @param string $key   Setting key.
 * @param string $label Label.
 * @param string $help  Help text.
 */
function ppd_field_checkbox( $key, $label, $help = '' ) {
	$s = ppd_settings( true );
	printf(
		'<tr><th scope="row">%1$s</th><td><label><input type="checkbox" name="%2$s[%3$s]" value="1" %4$s> %5$s</label>%6$s</td></tr>',
		esc_html( $label ),
		esc_attr( PPD_OPTION ),
		esc_attr( $key ),
		checked( ! empty( $s[ $key ] ), true, false ),
		esc_html( ppd_t( 'Enabled', 'Ativado' ) ),
		$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
	);
}

/**
 * Text, number, url or color field.
 *
 * @param string $key   Setting key.
 * @param string $label Label.
 * @param string $type  Input type.
 * @param string $help  Help text.
 * @param string $ph    Placeholder.
 */
function ppd_field_input( $key, $label, $type = 'text', $help = '', $ph = '' ) {
	$s = ppd_settings( true );
	printf(
		'<tr><th scope="row"><label for="ppd-%2$s">%1$s</label></th><td><input type="%3$s" id="ppd-%2$s" name="%4$s[%2$s]" value="%5$s" class="%6$s" placeholder="%7$s">%8$s</td></tr>',
		esc_html( $label ),
		esc_attr( $key ),
		esc_attr( $type ),
		esc_attr( PPD_OPTION ),
		esc_attr( (string) $s[ $key ] ),
		'color' === $type || 'number' === $type ? 'small-text' : 'regular-text',
		esc_attr( $ph ),
		$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
	);
}

/**
 * Select field.
 *
 * @param string $key     Setting key.
 * @param string $label   Label.
 * @param array  $options value => label.
 * @param string $help    Help text.
 */
function ppd_field_select( $key, $label, $options, $help = '' ) {
	$s    = ppd_settings( true );
	$opts = '';
	foreach ( $options as $v => $l ) {
		$opts .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $v ), selected( $s[ $key ], $v, false ), esc_html( $l ) );
	}
	printf(
		'<tr><th scope="row"><label for="ppd-%2$s">%1$s</label></th><td><select id="ppd-%2$s" name="%3$s[%2$s]">%4$s</select>%5$s</td></tr>',
		esc_html( $label ),
		esc_attr( $key ),
		esc_attr( PPD_OPTION ),
		$opts, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
	);
}

/**
 * Textarea field.
 *
 * @param string $key      Setting key.
 * @param string $label    Label.
 * @param string $help     Help text.
 * @param string $ph       Placeholder.
 * @param bool   $read_only Read-only.
 * @param bool   $code     Monospace.
 */
function ppd_field_textarea( $key, $label, $help = '', $ph = '', $read_only = false, $code = false ) {
	$s = ppd_settings( true );
	printf(
		'<tr><th scope="row"><label for="ppd-%2$s">%1$s</label></th><td><textarea id="ppd-%2$s" name="%3$s[%2$s]" rows="%4$d" class="large-text%5$s" placeholder="%6$s"%7$s>%8$s</textarea>%9$s</td></tr>',
		esc_html( $label ),
		esc_attr( $key ),
		esc_attr( PPD_OPTION ),
		$code ? 6 : 3,
		$code ? ' code' : '',
		esc_attr( $ph ),
		$read_only ? ' readonly' : '',
		esc_textarea( (string) $s[ $key ] ),
		$help ? '<p class="description">' . esc_html( $help ) . '</p>' : ''
	);
}

/**
 * Settings page.
 */
function ppd_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$tabs = ppd_admin_tabs();
	$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- navigation only.
	if ( ! isset( $tabs[ $tab ] ) ) {
		$tab = 'general';
	}
	$s   = ppd_settings( true );
	$url = admin_url( 'options-general.php?page=ppd-settings' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( ppd_t( 'Cookies and privacy', 'Cookies e privacidade' ) ); ?></h1>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $key => $t ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'tab', $key, $url ) ); ?>" class="nav-tab <?php echo $key === $tab ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $t[0] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<form method="post" action="options.php">
			<?php settings_fields( 'ppd' ); ?>
			<input type="hidden" name="<?php echo esc_attr( PPD_OPTION ); ?>[_tab]" value="<?php echo esc_attr( $tab ); ?>">
			<table class="form-table" role="presentation">
			<?php
			switch ( $tab ) {
				case 'general':
					ppd_field_checkbox( 'enabled', ppd_t( 'Banner and blocking', 'Banner e bloqueio' ) );
					ppd_field_select(
						'regime',
						ppd_t( 'Consent model', 'Modelo de consentimento' ),
						array(
							'optin'  => ppd_t( 'Opt-in: nothing optional runs before consent (LGPD, GDPR)', 'Opt-in: nada opcional roda antes do aceite (LGPD, GDPR)' ),
							'optout' => ppd_t( 'Opt-out: notice with "Do Not Sell or Share" (CCPA/CPRA)', 'Opt-out: aviso com "Não vender nem compartilhar" (CCPA/CPRA)' ),
							'auto'   => ppd_t( 'Automatic, by visitor country', 'Automático, pelo país do visitante' ),
						),
						ppd_t(
							'Automatic mode reads the country from Cloudflare (CF-IPCountry), CloudFront or GeoIP headers, through an uncached REST call, so it works with page cache.',
							'O modo automático lê o país do cabeçalho do Cloudflare (CF-IPCountry), do CloudFront ou do GeoIP, por uma chamada REST sem cache, então funciona com cache de página.'
						)
					);
					ppd_field_input( 'optin_countries', ppd_t( 'Opt-in countries (automatic mode)', 'Países com opt-in (modo automático)' ), 'text', ppd_t( 'Two-letter codes separated by commas. Default: EU, EEA, UK, Switzerland and Brazil. The US gets opt-out.', 'Códigos de duas letras separados por vírgula. Padrão: UE, EEE, Reino Unido, Suíça e Brasil. Os EUA recebem opt-out.' ) );
					ppd_field_select(
						'auto_unknown',
						ppd_t( 'Other or unknown countries', 'Outros países ou país desconhecido' ),
						array(
							'optin'  => ppd_t( 'Opt-in (safer)', 'Opt-in (mais seguro)' ),
							'optout' => 'Opt-out',
						)
					);
					ppd_field_input( 'policy_url', ppd_t( 'Privacy policy URL', 'URL da política de privacidade' ), 'url', ppd_t( 'Empty uses the page set in Settings > Privacy.', 'Vazio usa a página definida em Configurações > Privacidade.' ), ppd_policy_url() );
					ppd_field_input( 'policy_version', ppd_t( 'Policy version', 'Versão da política' ), 'text', ppd_t( 'Stored with every choice. When it changes, everyone is asked again.', 'Gravada com cada escolha. Quando muda, todos são perguntados de novo.' ) );
					printf(
						'<tr><th scope="row">%1$s</th><td><label><input type="checkbox" name="%2$s[_renew]" value="1"> %3$s</label></td></tr>',
						esc_html( ppd_t( 'Ask again', 'Pedir de novo' ) ),
						esc_attr( PPD_OPTION ),
						esc_html( ppd_t( 'Increase the version on save and show the banner to everyone', 'Aumentar a versão ao salvar e mostrar o banner para todos' ) )
					);
					ppd_field_input( 'expiry_days', ppd_t( 'Choice lasts (days)', 'Validade da escolha (dias)' ), 'number', ppd_t( 'Maximum 395 (13 months). Default 180.', 'Máximo de 395 (13 meses). Padrão 180.' ) );
					ppd_field_checkbox( 'respect_gpc', ppd_t( 'Global Privacy Control', 'Global Privacy Control' ), ppd_t( 'Treat the browser GPC signal as an opt-out of sale and sharing (marketing).', 'Tratar o sinal GPC do navegador como oposição à venda e ao compartilhamento (marketing).' ) );
					ppd_field_checkbox( 'reload_on_revoke', ppd_t( 'Reload on withdrawal', 'Recarregar ao retirar consentimento' ), ppd_t( 'Scripts that already ran cannot be stopped; a reload makes the withdrawal effective.', 'Scripts que já rodaram não podem ser parados; recarregar a página efetiva a retirada.' ) );
					break;

				case 'appearance':
					ppd_field_select(
						'position',
						ppd_t( 'Position', 'Posição' ),
						array(
							'bar'   => ppd_t( 'Bar at the bottom', 'Barra no rodapé' ),
							'box'   => ppd_t( 'Box in the corner', 'Caixa no canto' ),
							'modal' => ppd_t( 'Centered window (requires a choice)', 'Janela central (exige escolha)' ),
						)
					);
					ppd_field_input( 'color_bg', ppd_t( 'Background', 'Fundo' ), 'color' );
					ppd_field_input( 'color_text', ppd_t( 'Text', 'Texto' ), 'color' );
					ppd_field_input( 'color_accent', ppd_t( 'Buttons', 'Botões' ), 'color', ppd_t( 'Accept and reject use the same style, so neither is highlighted over the other.', 'Aceitar e recusar usam o mesmo estilo, para nenhum ser destacado sobre o outro.' ) );
					ppd_field_input( 'color_accent_text', ppd_t( 'Button text', 'Texto dos botões' ), 'color', ppd_t( 'Keep a contrast of at least 4.5:1 with the button color.', 'Mantenha contraste de pelo menos 4,5:1 com a cor do botão.' ) );
					ppd_field_checkbox( 'floating_button', ppd_t( 'Floating button', 'Botão flutuante' ), ppd_t( 'Lets visitors reopen their choices. Without it, add [ppd_cookie_settings] to the footer.', 'Permite reabrir as escolhas. Sem ele, coloque [ppd_cookie_settings] no rodapé.' ) );
					break;

				case 'texts':
					echo '<tr><td colspan="2"><p class="description">' . esc_html( ppd_t( 'Empty fields use the default text (shown as placeholder) in the site language.', 'Campos vazios usam o texto padrão (mostrado em cinza) no idioma do site.' ) ) . '</p></td></tr>';
					$defaults = ppd_default_texts();
					foreach ( $defaults as $key => $default ) {
						if ( strlen( $default ) > 90 ) {
							ppd_field_textarea( 'text_' . $key, $key, '', $default );
						} else {
							ppd_field_input( 'text_' . $key, $key, 'text', '', $default );
						}
					}
					break;

				case 'categories':
					$can = current_user_can( 'unfiltered_html' );
					foreach ( ppd_categories() as $cat ) {
						echo '<tr><td colspan="2"><h2>' . esc_html( ppd_text( $cat . '_label' ) ) . '</h2></td></tr>';
						ppd_field_checkbox( 'cat_' . $cat, ppd_t( 'Show this category', 'Mostrar esta categoria' ) );
						ppd_field_textarea(
							'scripts_' . $cat,
							ppd_t( 'Code to load after consent', 'Código carregado após o aceite' ),
							$can ? ppd_t( 'Paste scripts or pixels here (e.g. Meta Pixel, Clarity, Hotjar). They only run after the visitor allows this category.', 'Cole aqui scripts ou pixels (ex.: Meta Pixel, Clarity, Hotjar). Só rodam depois que o visitante permitir esta categoria.' ) : ppd_t( 'Your user cannot save unfiltered HTML.', 'Seu usuário não pode salvar HTML sem filtro.' ),
							'<script>...</script>',
							! $can,
							true
						);
						ppd_field_input( 'cookies_' . $cat, ppd_t( 'Cookies removed on withdrawal', 'Cookies apagados na retirada' ), 'text', ppd_t( 'Name prefixes, separated by commas.', 'Prefixos de nome, separados por vírgula.' ) );
					}
					break;

				case 'google':
					ppd_field_checkbox( 'gcm_enabled', 'Google Consent Mode v2', ppd_t( 'Sends consent defaults and updates to Google tags (GA4, Ads, GTM).', 'Envia o estado padrão e as atualizações de consentimento para as tags do Google (GA4, Ads, GTM).' ) );
					ppd_field_select(
						'gcm_mode',
						ppd_t( 'Mode', 'Modo' ),
						array(
							'advanced' => ppd_t( 'Advanced: tags load and wait for consent (cookieless pings)', 'Avançado: tags carregam e esperam o consentimento (pings sem cookie)' ),
							'basic'    => ppd_t( 'Basic: tags load only after statistics consent', 'Básico: tags só carregam após aceite de estatísticas' ),
						)
					);
					ppd_field_input( 'gtm_id', ppd_t( 'Google Tag Manager ID', 'ID do Google Tag Manager' ), 'text', ppd_t( 'Optional. Leave empty if GTM is already added by the theme or another tool.', 'Opcional. Deixe vazio se o GTM já é inserido pelo tema ou outra ferramenta.' ), 'GTM-XXXXXXX' );
					ppd_field_input( 'ga4_id', ppd_t( 'GA4 measurement ID', 'ID de medição do GA4' ), 'text', ppd_t( 'Optional, same note as GTM.', 'Opcional, mesma observação do GTM.' ), 'G-XXXXXXXXXX' );
					ppd_field_checkbox( 'ads_redaction', 'ads_data_redaction', ppd_t( 'Redact ad click identifiers while ad_storage is denied.', 'Oculta identificadores de clique de anúncio enquanto ad_storage está negado.' ) );
					ppd_field_checkbox( 'url_passthrough', 'url_passthrough', ppd_t( 'Pass click information in URLs while cookies are denied.', 'Repassa informação de clique pela URL enquanto os cookies estão negados.' ) );
					break;

				case 'blocking':
					ppd_field_checkbox( 'embeds_block', ppd_t( 'Block third-party embeds', 'Bloquear embeds de terceiros' ), ppd_t( 'YouTube, Vimeo, Google Maps, Spotify, SoundCloud, X, Instagram, Facebook, TikTok and others show a placeholder until allowed.', 'YouTube, Vimeo, Google Maps, Spotify, SoundCloud, X, Instagram, Facebook, TikTok e outros mostram um aviso até serem permitidos.' ) );
					ppd_field_select(
						'embeds_category',
						ppd_t( 'Embeds belong to', 'Embeds pertencem a' ),
						array_combine(
							ppd_categories(),
							array_map(
								static function ( $c ) {
									return ppd_text( $c . '_label' );
								},
								ppd_categories()
							)
						)
					);
					ppd_field_textarea( 'script_handles', ppd_t( 'Enqueued scripts to block', 'Scripts enfileirados para bloquear' ), ppd_t( 'One per line, "handle:category" (analytics, marketing or preferences). For scripts added by the theme or plugins with wp_enqueue_script.', 'Um por linha, "handle:categoria" (analytics, marketing ou preferences). Para scripts adicionados pelo tema ou plugins com wp_enqueue_script.' ), "google-analytics:analytics\nfacebook-pixel:marketing", false, true );
					break;

				case 'log':
					ppd_field_checkbox( 'log_enabled', ppd_t( 'Store every choice', 'Registrar cada escolha' ), ppd_t( 'Anonymous record (random ID, date, choice, policy version, anonymized and hashed IP) to prove consent.', 'Registro anônimo (ID aleatório, data, escolha, versão da política, IP anonimizado e com hash) para comprovar o consentimento.' ) );
					ppd_field_input( 'log_retention', ppd_t( 'Keep records for (days)', 'Manter registros por (dias)' ), 'number' );
					break;
			}
			?>
			</table>
			<?php submit_button(); ?>
		</form>
		<?php
		if ( 'log' === $tab ) {
			ppd_render_log_table();
		}
		if ( 'general' === $tab ) {
			echo '<h2>' . esc_html( ppd_t( 'Shortcodes and helpers', 'Shortcodes e funções' ) ) . '</h2><ul class="ul-disc">';
			echo '<li><code>[ppd_cookie_settings]</code> ' . esc_html( ppd_t( 'button that reopens the preferences', 'botão que reabre as preferências' ) ) . '</li>';
			echo '<li><code>[ppd_do_not_sell]</code> ' . esc_html( ppd_t( 'CCPA/CPRA opt-out link', 'link de oposição do CCPA/CPRA' ) ) . '</li>';
			echo '<li><code>[ppd_cookie_table]</code> ' . esc_html( ppd_t( 'category table for the privacy policy', 'tabela de categorias para a política de privacidade' ) ) . '</li>';
			echo '<li><code>ppd_has_consent( \'analytics\' )</code> ' . esc_html( ppd_t( 'in theme code (pages without full-page cache)', 'no código do tema (páginas sem cache de página inteira)' ) ) . '</li>';
			echo '<li><code>&lt;script type="text/plain" data-ppd-category="marketing"&gt;</code> ' . esc_html( ppd_t( 'holds any script until consent', 'segura qualquer script até o aceite' ) ) . '</li>';
			echo '</ul>';
		}
		?>
	</div>
	<?php
}

/**
 * Log summary and the latest records, with CSV export.
 */
function ppd_render_log_table() {
	global $wpdb;
	if ( get_option( 'ppd_db_version' ) !== PPD_DB_VERSION ) {
		return;
	}
	$table = ppd_log_table();
	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery
	$total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	$counts = $wpdb->get_results( "SELECT action, COUNT(*) AS n FROM {$table} GROUP BY action ORDER BY n DESC", ARRAY_A );
	$rows   = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 50", ARRAY_A );
	// phpcs:enable
	$export = wp_nonce_url( admin_url( 'admin-post.php?action=ppd_export' ), 'ppd_export' );
	echo '<h2>' . esc_html( ppd_t( 'Records', 'Registros' ) ) . ' (' . esc_html( number_format_i18n( $total ) ) . ')</h2>';
	if ( $counts ) {
		echo '<p>';
		foreach ( $counts as $c ) {
			echo '<code>' . esc_html( $c['action'] ) . '</code>: ' . esc_html( number_format_i18n( (int) $c['n'] ) ) . ' &nbsp; ';
		}
		echo '</p>';
	}
	echo '<p><a class="button" href="' . esc_url( $export ) . '">' . esc_html( ppd_t( 'Export CSV', 'Exportar CSV' ) ) . '</a></p>';
	echo '<table class="widefat striped"><thead><tr>';
	foreach ( array( ppd_t( 'Date (UTC)', 'Data (UTC)' ), 'ID', ppd_t( 'Model', 'Modelo' ), ppd_t( 'Action', 'Ação' ), ppd_t( 'Categories', 'Categorias' ), ppd_t( 'Version', 'Versão' ), 'GPC', ppd_t( 'User', 'Usuário' ), 'URL' ) as $h ) {
		echo '<th scope="col">' . esc_html( $h ) . '</th>';
	}
	echo '</tr></thead><tbody>';
	if ( ! $rows ) {
		echo '<tr><td colspan="9">' . esc_html( ppd_t( 'No records yet.', 'Nenhum registro ainda.' ) ) . '</td></tr>';
	}
	foreach ( (array) $rows as $r ) {
		printf(
			'<tr><td>%s</td><td><code>%s</code></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
			esc_html( $r['created_at'] ),
			esc_html( substr( $r['consent_id'], 0, 8 ) ),
			esc_html( $r['regime'] ),
			esc_html( $r['action'] ),
			esc_html( '' === $r['categories'] ? '-' : $r['categories'] ),
			esc_html( $r['policy_version'] ),
			$r['gpc'] ? '&#10003;' : '',
			$r['user_id'] ? esc_html( (string) $r['user_id'] ) : '',
			esc_html( $r['url'] )
		);
	}
	echo '</tbody></table>';
}

/**
 * CSV export of the whole log.
 */
function ppd_export_csv() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to access this page.' ) );
	}
	check_admin_referer( 'ppd_export' );
	global $wpdb;
	$table = ppd_log_table();
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=consent-log-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	fputcsv( $out, array( 'created_at_utc', 'consent_id', 'regime', 'action', 'categories', 'policy_version', 'gpc', 'user_id', 'ip_hash', 'url' ), ',', '"', '' );
	$offset = 0;
	do {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT created_at, consent_id, regime, action, categories, policy_version, gpc, user_id, ip_hash, url FROM {$table} ORDER BY id ASC LIMIT 1000 OFFSET %d", $offset ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery
		foreach ( (array) $rows as $r ) {
			fputcsv( $out, $r, ',', '"', '' );
		}
		$offset += 1000;
		$batch   = is_array( $rows ) ? count( $rows ) : 0;
	} while ( 1000 === $batch );
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_ppd_export', 'ppd_export_csv' );

// -----------------------------------------------------------------------------
// 12. WordPress privacy tools (Tools > Export / Erase Personal Data)
// -----------------------------------------------------------------------------

add_filter(
	'wp_privacy_personal_data_exporters',
	static function ( $exporters ) {
		$exporters['ppd'] = array(
			'exporter_friendly_name' => ppd_t( 'Cookie consent records', 'Registros de consentimento de cookies' ),
			'callback'               => 'ppd_privacy_exporter',
		);
		return $exporters;
	}
);

add_filter(
	'wp_privacy_personal_data_erasers',
	static function ( $erasers ) {
		$erasers['ppd'] = array(
			'eraser_friendly_name' => ppd_t( 'Cookie consent records', 'Registros de consentimento de cookies' ),
			'callback'             => 'ppd_privacy_eraser',
		);
		return $erasers;
	}
);

/**
 * Exports the records tied to a registered user (anonymous visitors have no
 * link between an e-mail and a record).
 *
 * @param string $email Email.
 * @param int    $page  Page.
 * @return array
 */
function ppd_privacy_exporter( $email, $page = 1 ) {
	$user = get_user_by( 'email', $email );
	if ( ! $user || get_option( 'ppd_db_version' ) !== PPD_DB_VERSION ) {
		return array(
			'data' => array(),
			'done' => true,
		);
	}
	global $wpdb;
	$table  = ppd_log_table();
	$offset = ( max( 1, (int) $page ) - 1 ) * 200;
	$rows   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY id ASC LIMIT 200 OFFSET %d", $user->ID, $offset ), ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery
	$data   = array();
	foreach ( (array) $rows as $r ) {
		$data[] = array(
			'group_id'    => 'ppd-consent',
			'group_label' => ppd_t( 'Cookie consent', 'Consentimento de cookies' ),
			'item_id'     => 'ppd-' . $r['id'],
			'data'        => array(
				array(
					'name'  => ppd_t( 'Date (UTC)', 'Data (UTC)' ),
					'value' => $r['created_at'],
				),
				array(
					'name'  => ppd_t( 'Action', 'Ação' ),
					'value' => $r['action'],
				),
				array(
					'name'  => ppd_t( 'Categories', 'Categorias' ),
					'value' => $r['categories'],
				),
				array(
					'name'  => ppd_t( 'Policy version', 'Versão da política' ),
					'value' => $r['policy_version'],
				),
			),
		);
	}
	return array(
		'data' => $data,
		'done' => count( (array) $rows ) < 200,
	);
}

/**
 * Erases the records tied to a registered user.
 *
 * @param string $email Email.
 * @return array
 */
function ppd_privacy_eraser( $email ) {
	$user    = get_user_by( 'email', $email );
	$removed = 0;
	if ( $user && get_option( 'ppd_db_version' ) === PPD_DB_VERSION ) {
		global $wpdb;
		$removed = (int) $wpdb->delete( ppd_log_table(), array( 'user_id' => $user->ID ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	}
	return array(
		'items_removed'  => $removed,
		'items_retained' => false,
		'messages'       => array(),
		'done'           => true,
	);
}

/**
 * Suggested text for the privacy policy (Settings > Privacy > Policy Guide).
 */
add_action(
	'admin_init',
	static function () {
		if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
			return;
		}
		$text = ppd_t(
			'<p>We use cookies grouped into categories: necessary, preferences, statistics and marketing. Only necessary cookies are set without your permission. You can change your choice at any time through the cookie settings button.</p><p>When you make a choice we store a record with a random identifier, the date, the categories you allowed, the version of this policy and an anonymized, hashed version of your IP address, to prove that consent was given. [ppd_cookie_table]</p>',
			'<p>Usamos cookies divididos em categorias: necessários, preferências, estatísticas e marketing. Só os necessários são gravados sem a sua permissão. Você pode mudar a sua escolha a qualquer momento pelo botão de configurações de cookies.</p><p>Quando você faz uma escolha, guardamos um registro com um identificador aleatório, a data, as categorias permitidas, a versão desta política e uma versão anonimizada e com hash do seu endereço IP, para comprovar o consentimento. [ppd_cookie_table]</p>'
		);
		wp_add_privacy_policy_content( ppd_t( 'Cookie consent', 'Consentimento de cookies' ), wp_kses_post( $text ) );
	},
	20
);
