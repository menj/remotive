<?php
/**
 * Remotive Media — service landing pages for paid and social traffic.
 *
 * One page per service (SEO, Google Ads, paid social), each a short funnel:
 * promise and form above the fold, what you get, how it works, a second
 * form. Every form ends on the thank-you page, which fires the conversion
 * event (see inc/forms/thank-you.php).
 *
 * These pages exist for ads and social posts, not for search. They are
 * noindex + nofollow, carry no site navigation, and are kept out of the
 * site's own search and the core sitemap. robots.txt must NOT block them:
 * a crawler has to fetch the page to see the noindex, and Google Ads needs
 * to fetch it to review the ad.
 *
 * Copy is English / Bahasa Melayu / Simplified Chinese / Traditional Chinese, one URL each
 * by URL: each language has its own path (see remotive_lp_languages()), the
 * server renders only that language, and no JavaScript is needed to read the page. Template: templates/page-landing.html, which holds only the
 * __REMOTIVE_LANDING__ token this file replaces.
 *
 * The Malay and Chinese copy is a first draft for native-speaker review.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/landing-copy.php';

const REMOTIVE_LANDING_TEMPLATE = 'page-landing';

/** Slug of the confirmation page landing-page leads are sent to. */
const REMOTIVE_LP_THANKS_SLUG = 'audit-requested';

/**
 * The languages: key => array( URL path prefix, BCP 47 tag, button code,
 * accessible name ). English lives at the page's own URL; the others sit under
 * a language directory:
 *
 *   /seo-audit/            English
 *   /ms/seo-audit/         Bahasa Melayu
 *   /zh-cn/seo-audit/      Simplified Chinese
 *   /zh-tw/seo-audit/      Traditional Chinese
 *
 * The key is also the index of that language's text in each copy array.
 *
 * @return array<string,array<int,string>>
 */
function remotive_lp_languages() {
	return array(
		'en'  => array( '', 'en', 'EN', 'English' ),
		'ms'  => array( 'ms', 'ms', 'MS', 'Bahasa Melayu' ),
		'zh'  => array( 'zh-cn', 'zh-Hans', 'ZH-CN', '简体中文' ),
		'zht' => array( 'zh-tw', 'zh-Hant', 'ZH-TW', '繁體中文' ),
	);
}

/**
 * The language of the current request, from the URL's language directory.
 *
 * @return string One of the keys of remotive_lp_languages().
 */
function remotive_lp_requested_lang() {
	$prefix = (string) get_query_var( 'rm_lang' );

	foreach ( remotive_lp_languages() as $key => $lang ) {
		if ( '' !== $prefix && $lang[0] === $prefix ) {
			return $key;
		}
	}

	return 'en';
}

/**
 * Index of the current language in a copy array.
 *
 * @return int
 */
function remotive_lp_lang_index() {
	return (int) array_search( remotive_lp_requested_lang(), array_keys( remotive_lp_languages() ), true );
}

/**
 * URL of one service page in one language.
 *
 * @param string $slug Service page slug.
 * @param string $lang Language key.
 * @return string
 */
function remotive_lp_url( $slug, $lang ) {
	$prefix = remotive_lp_languages()[ $lang ][0];

	if ( '' === $prefix ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );

		return $page ? get_permalink( $page->ID ) : user_trailingslashit( home_url( '/' . $slug ) );
	}

	return user_trailingslashit( home_url( '/' . $prefix . '/' . $slug ) );
}

/* ---- Language URLs: /ms/<slug>/, /zh-cn/<slug>/, /zh-tw/<slug>/ ---- */

function remotive_lp_query_vars( $vars ) {
	$vars[] = 'rm_lang';

	return $vars;
}
add_filter( 'query_vars', 'remotive_lp_query_vars' );

function remotive_lp_rewrites() {
	$prefixes = array();

	foreach ( remotive_lp_languages() as $lang ) {
		if ( '' !== $lang[0] ) {
			$prefixes[] = preg_quote( $lang[0], '#' );
		}
	}

	$slugs = array_map( 'preg_quote', array_merge( array_keys( remotive_landing_services() ), array( REMOTIVE_LP_THANKS_SLUG ) ) );

	add_rewrite_rule(
		'^(' . implode( '|', $prefixes ) . ')/(' . implode( '|', $slugs ) . ')/?$',
		'index.php?pagename=$matches[2]&rm_lang=$matches[1]',
		'top'
	);
}
add_action( 'init', 'remotive_lp_rewrites' );

/**
 * Rewrite rules are stored, so a new rule needs one flush. Tracked by a
 * version, so it runs once after an upgrade (and again if the rule set
 * changes) and never on an ordinary request.
 */
function remotive_lp_maybe_flush_rewrites() {
	if ( '2' !== get_option( 'remotive_lp_rewrite_v' ) ) {
		flush_rewrite_rules( false );
		update_option( 'remotive_lp_rewrite_v', '2', true );
	}
}
add_action( 'wp_loaded', 'remotive_lp_maybe_flush_rewrites' );

/**
 * Without this, WordPress "corrects" /ms/<slug>/ back to /<slug>/, because the
 * page's permalink has no language directory.
 */
function remotive_lp_keep_language_url( $redirect_url ) {
	return '' !== (string) get_query_var( 'rm_lang' ) ? false : $redirect_url;
}
add_filter( 'redirect_canonical', 'remotive_lp_keep_language_url' );

/** Each language URL is its own canonical. */
function remotive_lp_canonical( $url, $post ) {
	if ( $post && remotive_is_landing_page() ) {
		$slug = get_post_field( 'post_name', $post );

		if ( isset( remotive_landing_services()[ $slug ] ) || REMOTIVE_LP_THANKS_SLUG === $slug ) {
			return remotive_lp_url( $slug, remotive_lp_requested_lang() );
		}
	}

	return $url;
}
add_filter( 'get_canonical_url', 'remotive_lp_canonical', 10, 2 );

/**
 * Whether the current request is one of the landing pages.
 *
 * @return bool
 */
function remotive_is_landing_page() {
	return is_page() && is_page_template( REMOTIVE_LANDING_TEMPLATE );
}

/**
 * Text in the language of the current URL. Only that language is output.
 *
 * @param array  $t   array( en, ms, zh-Hans, zh-Hant ).
 * @param string $tag Optional wrapping element; plain text when empty.
 * @param string $cls Optional class on that element.
 * @return string
 */
function remotive_lp_t( $t, $tag = '', $cls = '' ) {
	$text = esc_html( $t[ remotive_lp_lang_index() ] );

	if ( '' === $tag ) {
		return $text;
	}

	return sprintf( '<%1$s%2$s>%3$s</%1$s>', $tag, $cls ? ' class="' . esc_attr( $cls ) . '"' : '', $text );
}

/**
 * The lead form. Rendered twice per page (top and bottom), so ids carry a
 * suffix and the nonce is a plain hidden input (wp_nonce_field() would
 * repeat its id).
 *
 * @param string $service Service slug.
 * @param string $pos     'top' or 'bottom'.
 * @return string
 */
function remotive_lp_form( $service, $pos ) {
	$cta = remotive_landing_services()[ $service ]['cta'];
	$id = 'rm-lp-' . $pos;

	$field = function ( $name, $type, $label, $required, $autocomplete ) use ( $id ) {
		return sprintf(
			'<p class="rm-lp__field"><label for="%1$s-%2$s">%3$s%4$s</label><input type="%5$s" id="%1$s-%2$s" name="%2$s" autocomplete="%6$s"%7$s></p>',
			esc_attr( $id ),
			esc_attr( $name ),
			remotive_lp_t( $label ),
			$required ? '' : ' <span class="rm-lp__opt">' . remotive_lp_t( array( '(optional)', '(pilihan)', '（选填）', '（選填）' ) ) . '</span>',
			esc_attr( $type ),
			esc_attr( $autocomplete ),
			$required ? ' required' : ''
		);
	};

	$tracked = '';
	foreach ( remotive_lp_tracking_keys() as $key ) {
		$tracked .= sprintf( '<input type="hidden" name="%s" value="" data-track="%s">', esc_attr( $key ), esc_attr( $key ) );
	}

	$msgs = '';
	foreach ( array(
		'success' => array( 'Thanks. We will be in touch within three business days.', 'Terima kasih. Kami akan menghubungi anda dalam tiga hari bekerja.', '谢谢。我们将在三个工作日内与您联系。', '謝謝。我們將在三個工作日內與您聯絡。' ),
		'error'   => array( 'Something went wrong sending that. Please try again, or email us directly.', 'Sesuatu tidak kena semasa menghantar. Sila cuba lagi, atau e-mel kami terus.', '提交时出了问题。请重试，或直接给我们发送电子邮件。', '提交時發生問題。請重試，或直接寄電子郵件給我們。' ),
	) as $state => $t ) {
		$msgs .= '<span data-msg class="rm-lp__msg rm-lp__msg--' . esc_attr( $state ) . '">' . remotive_lp_t( $t ) . '</span>';
	}

	return '<form class="rm-lp__form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
		. '<input type="hidden" name="action" value="remotive_lp_submit">'
		. '<input type="hidden" name="remotive_lp_nonce" value="' . esc_attr( wp_create_nonce( 'remotive_lp_submit' ) ) . '">'
		. '<input type="hidden" name="service" value="' . esc_attr( $service ) . '">'
		. '<input type="hidden" name="lp_lang" value="' . esc_attr( remotive_lp_requested_lang() ) . '">'
		. $tracked
		. '<div class="rm-lp__hp" aria-hidden="true"><label for="' . esc_attr( $id ) . '-hp">Leave this field empty</label>'
		. '<input type="text" id="' . esc_attr( $id ) . '-hp" name="remotive_lp_website" tabindex="-1" autocomplete="off"></div>'
		. $field( 'name', 'text', array( 'Your name', 'Nama anda', '您的姓名', '您的姓名' ), true, 'name' )
		. $field( 'email', 'email', array( 'Work email', 'E-mel kerja', '工作邮箱', '工作信箱' ), true, 'email' )
		. $field( 'site', 'text', array( 'Website to review', 'Laman web untuk disemak', '待审查的网站', '待審查的網站' ), false, 'url' )
		. '<button type="submit" class="rm-lp__submit">' . remotive_lp_t( $cta ) . '</button>'
		. '<p class="rm-lp__fine">' . remotive_lp_t( array( 'Free. No commitment. No mailing list. We reply within three business days.', 'Percuma. Tiada komitmen. Tiada senarai mel. Kami membalas dalam tiga hari bekerja.', '免费，无需承诺，不加入邮件列表。我们会在三个工作日内回复。', '免費，無需承諾，不加入郵寄名單。我們會在三個工作日內回覆。' ) )
		. ' <a href="' . esc_url( home_url( '/privacy/' ) ) . '">' . remotive_lp_t( array( 'Privacy', 'Privasi', '隐私政策', '隱私權政策' ) ) . '</a></p>'
		. '<div class="rm-lp__status" data-lead-status="remotive_lp" role="status" aria-live="polite" hidden>' . $msgs . '</div>'
		. '</form>';
}

/**
 * Campaign fields captured from the landing URL and sent with the lead.
 *
 * @return string[]
 */
function remotive_lp_tracking_keys() {
	return array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid', 'ttclid' );
}

/**
 * A responsive photo from assets/images/landing/ (AVIF with JPEG fallback),
 * self-hosted. Sizes are pre-generated as <stem>-<width>.avif|jpg.
 *
 * @param string          $stem  File stem, e.g. 'city-singapore'.
 * @param array<int,int>  $sizes Width => height of each generated size.
 * @param string          $sizes_attr The `sizes` attribute.
 * @param string          $alt   Alt text ('' for decorative).
 * @param string          $cls   Optional class on the <img>.
 * @return string
 */
function remotive_lp_picture( $stem, $sizes, $sizes_attr, $alt = '', $cls = '' ) {
	$base = get_stylesheet_directory_uri() . '/assets/images/landing/';
	$dir  = get_stylesheet_directory() . '/assets/images/landing/';

	$avif = array();
	$jpg  = array();

	foreach ( $sizes as $w => $h ) {
		if ( is_readable( $dir . $stem . '-' . $w . '.avif' ) ) {
			$avif[] = esc_url( $base . $stem . '-' . $w . '.avif' ) . ' ' . (int) $w . 'w';
		}
		$jpg[] = esc_url( $base . $stem . '-' . $w . '.jpg' ) . ' ' . (int) $w . 'w';
	}

	$widths = array_keys( $sizes );
	$last_w = end( $widths );
	$last_h = end( $sizes );

	return '<picture>'
		. ( $avif ? '<source type="image/avif" srcset="' . implode( ', ', $avif ) . '" sizes="' . esc_attr( $sizes_attr ) . '">' : '' )
		. '<img src="' . esc_url( $base . $stem . '-' . $widths[0] . '.jpg' ) . '" srcset="' . implode( ', ', $jpg ) . '" sizes="' . esc_attr( $sizes_attr ) . '"'
		. ' width="' . (int) $last_w . '" height="' . (int) $last_h . '" alt="' . esc_attr( $alt ) . '"'
		. ( $cls ? ' class="' . esc_attr( $cls ) . '"' : '' )
		. ' loading="lazy" decoding="async"></picture>';
}

/**
 * FAQPage structured data, built from the same items as the visible FAQ so
 * the two cannot drift apart. In the language of the current URL.
 */
function remotive_lp_faq_schema() {
	if ( ! remotive_is_landing_page() ) {
		return;
	}

	$slug = get_post_field( 'post_name', get_queried_object_id() );

	if ( ! isset( remotive_landing_services()[ $slug ] ) ) {
		return;
	}

	$i        = remotive_lp_lang_index();
	$entities = array();

	foreach ( remotive_lp_faq_items( $slug ) as $qa ) {
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => $qa[0][ $i ],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $qa[1][ $i ],
			),
		);
	}

	$data = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'inLanguage' => remotive_lp_languages()[ remotive_lp_requested_lang() ][1],
		'mainEntity' => $entities,
	);

	echo '<script type="application/ld+json" id="remotive-lp-faq">' . wp_json_encode( $data, JSON_HEX_TAG | JSON_HEX_AMP ) . '</script>' . "\n";
}
add_action( 'wp_head', 'remotive_lp_faq_schema', 7 );

/**
 * The logo, from the theme's own files (AVIF with PNG fallback). Not linked:
 * these pages have no way out except the form.
 *
 * @return string
 */
function remotive_lp_logo() {
	$base = get_stylesheet_directory_uri() . '/assets/images/remotive-logo-';
	$dir  = get_stylesheet_directory() . '/assets/images/remotive-logo-';

	$avif = is_readable( $dir . '112.avif' ) && is_readable( $dir . '168.avif' )
		? '<source type="image/avif" srcset="' . esc_url( $base . '112.avif' ) . ' 112w, ' . esc_url( $base . '168.avif' ) . ' 168w" sizes="73px">'
		: '';

	return '<span class="rm-lp__logo"><picture>' . $avif
		. '<img src="' . esc_url( $base . '112.png' ) . '" srcset="' . esc_url( $base . '112.png' ) . ' 112w, ' . esc_url( $base . '168.png' ) . ' 168w" sizes="73px"'
		. ' width="112" height="86" alt="Re:Motive Media" loading="eager" decoding="sync">'
		. '</picture></span>';
}

/**
 * The language links: real links to each language's own URL, labelled with
 * the language code (the full name is the accessible name). Campaign
 * parameters are added to these links by assets/js/landing.js, so switching
 * keeps them.
 *
 * @param string $slug Page slug.
 * @return string
 */
function remotive_lp_lang_nav( $slug ) {
	$btns    = '';
	$current = remotive_lp_requested_lang();

	foreach ( remotive_lp_languages() as $key => $lang ) {
		$btns .= sprintf(
			'<a class="rm-lp__lang" href="%1$s" hreflang="%2$s" lang="%2$s" aria-label="%3$s" title="%3$s"%4$s>%5$s</a>',
			esc_url( remotive_lp_url( $slug, $key ) ),
			esc_attr( $lang[1] ),
			esc_attr( $lang[3] ),
			$current === $key ? ' aria-current="true"' : '',
			esc_html( $lang[2] )
		);
	}

	return $btns;
}

/**
 * The confirmation page landing-page leads arrive on: the same chrome as the
 * landing pages (logo and language links only), in the language of the URL.
 * It is the conversion URL for landing-page forms; the event itself is pushed
 * from inc/forms/thank-you.php.
 *
 * @return string
 */
function remotive_lp_render_thanks() {
	$services = remotive_landing_services();
	$asked    = isset( $_GET['service'] ) ? sanitize_key( wp_unslash( $_GET['service'] ) ) : '';
	$i        = remotive_lp_lang_index();
	$label    = isset( $services[ $asked ] ) ? $services[ $asked ]['label'][ $i ] : '';

	if ( '' !== $label ) {
		// A Latin label inside Chinese text needs spaces; a Chinese label does not.
		$spaced = ( $i >= 2 && preg_match( '/[A-Za-z]/', $label ) ) ? ' ' . $label . ' ' : $label;
		$titles = array(
			sprintf( 'Thanks. Your free %s audit request is in.', $label ),
			sprintf( 'Terima kasih. Permintaan audit %s percuma anda telah diterima.', $label ),
			sprintf( '谢谢。您的免费%s审计申请已收到。', $spaced ),
			sprintf( '謝謝。您的免費%s審計申請已收到。', $spaced ),
		);
	} else {
		$titles = array( 'Thanks. Your request is in.', 'Terima kasih. Permintaan anda telah diterima.', '谢谢。您的申请已收到。', '謝謝。您的申請已收到。' );
	}

	$steps = '';
	foreach ( array(
		array( array( 'We read it', 'Kami membacanya', '我们会阅读', '我們會閱讀' ), array( 'Your details go to a named specialist, not a shared inbox.', 'Butiran anda dihantar kepada pakar yang dinamakan, bukan peti mel kongsi.', '您的资料会交给指定的专员，而不是共用收件箱。', '您的資料會交給指定的專員，而不是共用收件匣。' ) ),
		array( array( 'We reply within three business days', 'Kami membalas dalam tiga hari bekerja', '三个工作日内回复', '三個工作日內回覆' ), array( 'Singapore hours. A specific view of what we would fix first, not a brochure.', 'Waktu Singapura. Pandangan khusus tentang apa yang akan kami baiki dahulu, bukan brosur.', '新加坡时间。给出我们会优先解决什么的具体意见，而不是宣传册。', '新加坡時間。給出我們會優先解決什麼的具體意見，而不是宣傳冊。' ) ),
		array( array( 'You decide', 'Anda yang memutuskan', '由您决定', '由您決定' ), array( 'If an audit fits, we book 30 minutes. No commitment either way.', 'Jika audit sesuai, kami tempah 30 minit. Tiada komitmen.', '如果适合做审计，我们会预约 30 分钟。无需任何承诺。', '如果適合做審計，我們會預約 30 分鐘。無需任何承諾。' ) ),
	) as $n => $st ) {
		$steps .= '<li class="rm-lp__step"><span class="rm-lp__num">' . ( $n + 1 ) . '</span><h3>' . remotive_lp_t( $st[0] ) . '</h3><p>' . remotive_lp_t( $st[1] ) . '</p></li>';
	}

	return '<div class="rm-lp" data-lang="' . esc_attr( remotive_lp_requested_lang() ) . '" data-page="thanks">'
		. '<header class="rm-lp__bar">' . remotive_lp_logo()
		. '<nav class="rm-lp__langs" aria-label="Language / Bahasa / 语言 / 語言">' . remotive_lp_lang_nav( REMOTIVE_LP_THANKS_SLUG ) . '</nav></header>'
		. '<main id="main" class="rm-lp__main">'
		. '<section class="rm-lp__hero rm-lp__thanks"><div class="rm-lp__copy">'
		. '<p class="rm-lp__eyebrow">' . remotive_lp_t( array( 'Request received', 'Permintaan diterima', '申请已收到', '申請已收到' ) ) . '</p>'
		. '<h1>' . esc_html( $titles[ $i ] ) . '</h1>'
		. remotive_lp_t( array( 'A person reads what you sent and replies within three business days, Singapore hours, with a specific view of what we would fix first.', 'Seorang pakar membaca apa yang anda hantar dan membalas dalam tiga hari bekerja, waktu Singapura, dengan pandangan khusus tentang apa yang akan kami baiki dahulu.', '会有专人阅读您提交的内容，并在三个工作日内（新加坡时间）回复，给出我们会优先解决什么的具体意见。', '會有專人閱讀您提交的內容，並在三個工作日內（新加坡時間）回覆，給出我們會優先解決什麼的具體意見。' ), 'p', 'rm-lp__lead' )
		. '</div></section>'
		. '<section class="rm-lp__section" aria-labelledby="rm-lp-next"><h2 id="rm-lp-next">' . remotive_lp_t( array( 'What happens next', 'Apa yang berlaku seterusnya', '接下来会怎样', '接下來會怎樣' ) ) . '</h2>'
		. '<ol class="rm-lp__steps">' . $steps . '</ol>'
		. '<p class="rm-lp__fine">' . remotive_lp_t( array( 'Nothing was added to a mailing list. Your details are used to reply to you and nothing else.', 'Tiada apa-apa ditambah ke senarai mel. Butiran anda hanya digunakan untuk membalas anda.', '我们没有将您加入任何邮件列表。您的资料仅用于回复您。', '我們沒有將您加入任何郵寄名單。您的資料僅用於回覆您。' ) )
		. ' <a href="' . esc_url( home_url( '/privacy/' ) ) . '">' . remotive_lp_t( array( 'Privacy', 'Privasi', '隐私政策', '隱私權政策' ) ) . '</a></p></section>'
		. '</main>'
		. '<footer class="rm-lp__foot">&copy; ' . esc_html( gmdate( 'Y' ) ) . ' Re:Motive Media</footer>'
		. '</div>';
}

/**
 * Markup for the page being rendered.
 *
 * @return string
 */
function remotive_lp_render() {
	$slug     = get_post_field( 'post_name', get_queried_object_id() );
	$services = remotive_landing_services();

	if ( REMOTIVE_LP_THANKS_SLUG === $slug ) {
		return remotive_lp_render_thanks();
	}

	if ( ! isset( $services[ $slug ] ) ) {
		return '';
	}

	$s    = $services[ $slug ];
	$btns = remotive_lp_lang_nav( $slug );

	$points = '';
	foreach ( $s['points'] as $p ) {
		$points .= '<li>' . remotive_lp_t( $p ) . '</li>';
	}

	$steps = '';
	foreach ( array(
		array( array( 'Tell us where to look', 'Beritahu kami di mana untuk meneliti', '告诉我们从哪里看起', '告訴我們從哪裡看起' ), array( 'Your name, email and website. It takes under a minute.', 'Nama, e-mel dan laman web anda. Ia mengambil masa kurang daripada seminit.', '您的姓名、邮箱和网站，用时不到一分钟。', '您的姓名、信箱和網站，用時不到一分鐘。' ) ),
		array( array( 'We send what we would fix first', 'Kami hantar apa yang akan kami baiki dahulu', '我们告知会优先解决什么', '我們會告知將優先解決什麼' ), array( 'A specific view within three business days, not a brochure.', 'Pandangan khusus dalam tiga hari bekerja, bukan brosur.', '三个工作日内给出具体意见，而不是宣传册。', '三個工作日內提出具體意見，而不是宣傳冊。' ) ),
		array( array( 'You decide', 'Anda yang memutuskan', '由您决定', '由您決定' ), array( 'Work with us or take the plan and run. No commitment either way.', 'Bekerja dengan kami atau gunakan pelan itu sendiri. Tiada komitmen.', '可与我们合作，也可自行执行方案，均无需承诺。', '可與我們合作，也可自行執行方案，皆無需承諾。' ) ),
	) as $i => $st ) {
		$steps .= '<li class="rm-lp__step"><span class="rm-lp__num">' . ( $i + 1 ) . '</span><h3>' . remotive_lp_t( $st[0] ) . '</h3><p>' . remotive_lp_t( $st[1] ) . '</p></li>';
	}

	$cities = '';
	foreach ( array(
		'singapore' => array( 'Singapore', 'Singapura', '新加坡', '新加坡' ),
		'malaysia'  => array( 'Malaysia', 'Malaysia', '马来西亚', '馬來西亞' ),
		'thailand'  => array( 'Thailand', 'Thailand', '泰国', '泰國' ),
		'vietnam'   => array( 'Vietnam', 'Vietnam', '越南', '越南' ),
		'hong-kong' => array( 'Hong Kong', 'Hong Kong', '香港', '香港' ),
		'china'     => array( 'China', 'China', '中国', '中國' ),
	) as $stem => $label ) {
		$cities .= '<li class="rm-lp__city">'
			. remotive_lp_picture( 'city-' . $stem, array( 480 => 360, 720 => 540 ), '(min-width: 56rem) 14rem, 45vw' )
			. '<span>' . remotive_lp_t( $label ) . '</span></li>';
	}

	$faqs = '';
	$qi   = remotive_lp_lang_index();
	foreach ( remotive_lp_faq_items( $slug ) as $qa ) {
		$faqs .= '<details class="rm-lp__q"><summary>' . esc_html( $qa[0][ $qi ] ) . '</summary><p>' . esc_html( $qa[1][ $qi ] ) . '</p></details>';
	}

	return '<div class="rm-lp" data-lang="' . esc_attr( remotive_lp_requested_lang() ) . '" data-service="' . esc_attr( $slug ) . '">'
		. '<header class="rm-lp__bar">' . remotive_lp_logo()
		. '<nav class="rm-lp__langs" aria-label="Language / Bahasa / 语言 / 語言">' . $btns . '</nav></header>'
		. '<main id="main" class="rm-lp__main">'
		. '<section class="rm-lp__hero"><div class="rm-lp__copy">'
		. '<p class="rm-lp__eyebrow">' . remotive_lp_t( $s['eyebrow'] ) . '</p>'
		. '<h1>' . remotive_lp_t( $s['title'] ) . '</h1>'
		. remotive_lp_t( $s['lead'], 'p', 'rm-lp__lead' )
		. '<ul class="rm-lp__points">' . $points . '</ul>'
		. '<div class="rm-lp__photo">' . remotive_lp_picture( 'svc-' . $slug, array( 640 => 400, 1000 => 625 ), '(min-width: 56rem) 34rem, 0px', remotive_lp_t( $s['photo_alt'] ) ) . '</div></div>'
		. '<div class="rm-lp__card" id="rm-lp-start"><h2>' . remotive_lp_t( $s['form_title'] ) . '</h2><p class="rm-lp__intro">' . remotive_lp_t( array( 'Tell us where to look. We reply within three business days.', 'Beritahu kami di mana untuk meneliti. Kami membalas dalam tiga hari bekerja.', '告诉我们从哪里开始看。我们会在三个工作日内回复。', '告訴我們從哪裡開始看。我們會在三個工作日內回覆。' ) ) . '</p>'
		. remotive_lp_form( $slug, 'top' ) . '</div></section>'
		. '<section class="rm-lp__section" aria-labelledby="rm-lp-how"><h2 id="rm-lp-how">' . remotive_lp_t( array( 'How it works', 'Cara ia berfungsi', '合作流程', '合作流程' ) ) . '</h2>'
		. '<ol class="rm-lp__steps">' . $steps . '</ol></section>'
		. '<section class="rm-lp__section rm-lp__trust" aria-labelledby="rm-lp-where"><p id="rm-lp-where"><strong>' . remotive_lp_t( array( 'Senior-led and independent.', 'Diketuai pakar kanan dan bebas.', '资深团队领导，独立运营。', '資深團隊領導，獨立營運。' ) ) . '</strong> ' . remotive_lp_t( array( 'Working across six markets:', 'Beroperasi di enam pasaran:', '服务六大市场：', '服務六大市場：' ) ) . '</p>'
		. '<ul class="rm-lp__cities">' . $cities . '</ul></section>'
		. '<section class="rm-lp__section rm-lp__final" aria-labelledby="rm-lp-final"><div class="rm-lp__final-copy"><h2 id="rm-lp-final">' . remotive_lp_t( array( 'See what we would fix first. It\'s free.', 'Lihat apa yang akan kami baiki dahulu. Percuma.', '看看我们会优先解决什么。免费。', '看看我們會優先解決什麼。免費。' ) ) . '</h2>'
		. remotive_lp_t( array( 'A free audit, a specific view within three business days, and no commitment either way.', 'Audit percuma, pandangan khusus dalam tiga hari bekerja, dan tiada komitmen.', '免费审计，三个工作日内给出具体意见，无需任何承诺。', '免費審計，三個工作日內提出具體意見，無需任何承諾。' ), 'p', 'rm-lp__lead' ) . '</div>'
		. '<div class="rm-lp__card">' . remotive_lp_form( $slug, 'bottom' ) . '</div></section>'
		. '<section class="rm-lp__section rm-lp__faq" aria-labelledby="rm-lp-faq"><h2 id="rm-lp-faq">' . remotive_lp_t( array( 'Frequently asked questions', 'Soalan lazim', '常见问题', '常見問題' ) ) . '</h2><div class="rm-lp__faqs">' . $faqs . '</div></section>'
		. '</main>'
		. '<a class="rm-lp__sticky" href="#rm-lp-start">' . remotive_lp_t( $s['cta'] ) . '</a>'
		. '<footer class="rm-lp__foot">&copy; ' . esc_html( gmdate( 'Y' ) ) . ' Re:Motive Media. ' . remotive_lp_t( array( 'Photos via Pexels.', 'Foto melalui Pexels.', '图片来自 Pexels。', '圖片來自 Pexels。' ) ) . '</footer>'
		. '</div>';
}

function remotive_lp_token( $tokens ) {
	$tokens['__REMOTIVE_LANDING__'] = remotive_is_landing_page() ? remotive_lp_render() : '';
	return $tokens;
}
add_filter( 'remotive_theme_option_tokens', 'remotive_lp_token' );

/**
 * Form handler. Service and campaign fields are whitelisted and sanitised,
 * then ride along with the lead so each enquiry says which page and which ad
 * produced it.
 */
function remotive_handle_landing_submission() {
	$services = remotive_landing_services();
	$service  = isset( $_POST['service'] ) ? sanitize_key( wp_unslash( $_POST['service'] ) ) : '';
	$service  = isset( $services[ $service ] ) ? $service : '';

	$extra = array();

	if ( '' !== $service ) {
		$extra[] = 'Service: ' . $services[ $service ]['label'][0];
	}

	$site = isset( $_POST['site'] ) ? esc_url_raw( wp_unslash( $_POST['site'] ), array( 'http', 'https' ) ) : '';
	if ( '' === $site && ! empty( $_POST['site'] ) ) {
		$typed = sanitize_text_field( wp_unslash( $_POST['site'] ) );
		$site  = esc_url_raw( 'https://' . $typed, array( 'https' ) );
	}
	if ( '' !== $site ) {
		$extra[] = 'Website: ' . substr( $site, 0, 200 );
	}

	foreach ( remotive_lp_tracking_keys() as $key ) {
		$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		if ( '' !== $value ) {
			$extra[] = $key . ': ' . substr( $value, 0, 120 );
		}
	}

	// The confirmation page is in the language the form was filled in.
	$lang = isset( $_POST['lp_lang'] ) ? sanitize_key( wp_unslash( $_POST['lp_lang'] ) ) : 'en';
	$lang = isset( remotive_lp_languages()[ $lang ] ) ? $lang : 'en';

	$base = wp_get_referer();
	$base = $base ? remove_query_arg( 'remotive_lp', $base ) : home_url( '/' );

	remotive_handle_lead_form_submission( array(
		'form_key'       => 'lp',
		'rate_limit'     => 10,
		'nonce_action'   => 'remotive_lp_submit',
		'nonce_name'     => 'remotive_lp_nonce',
		'honeypot_field' => 'remotive_lp_website',
		'redirect_base'  => $base,
		/* translators: %s: the site name */
		'email_subject'  => sprintf( __( 'New landing page enquiry from %s', 'remotive' ), get_bloginfo( 'name' ) ),
		'extra_lines'    => $extra,
		'thanks_url'     => remotive_lp_url( REMOTIVE_LP_THANKS_SLUG, $lang ),
		'thanks_args'    => '' !== $service ? array( 'service' => $service ) : array(),
	) );
}
add_action( 'admin_post_remotive_lp_submit', 'remotive_handle_landing_submission' );
add_action( 'admin_post_nopriv_remotive_lp_submit', 'remotive_handle_landing_submission' );

/* ---- Language alternates ---- */

/**
 * hreflang links: one per language plus x-default (the English page), each
 * pointing at that language's own URL. Every page lists itself and all its
 * alternates, as hreflang requires.
 *
 * These pages are noindex, so search engines ignore the annotations; they
 * are still correct, and identify the language versions to anything else that
 * reads them (ad review, link checkers, a future indexable page).
 */
function remotive_lp_hreflang() {
	if ( ! remotive_is_landing_page() ) {
		return;
	}

	$slug = get_post_field( 'post_name', get_queried_object_id() );

	if ( ! isset( remotive_landing_services()[ $slug ] ) && REMOTIVE_LP_THANKS_SLUG !== $slug ) {
		return;
	}

	foreach ( remotive_lp_languages() as $key => $lang ) {
		printf(
			'<link rel="alternate" hreflang="%1$s" href="%2$s">' . "\n",
			esc_attr( $lang[1] ),
			esc_url( remotive_lp_url( $slug, $key ) )
		);
	}

	printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( remotive_lp_url( $slug, 'en' ) ) );
}
add_action( 'wp_head', 'remotive_lp_hreflang', 2 );

/**
 * <html lang> follows the language of the URL, so the declared language
 * matches the content.
 */
function remotive_lp_html_lang( $output ) {
	if ( ! is_admin() && remotive_is_landing_page() ) {
		$tag    = remotive_lp_languages()[ remotive_lp_requested_lang() ][1];
		$output = preg_replace( '/lang="[^"]*"/', 'lang="' . esc_attr( $tag ) . '"', $output, 1, $n );
		if ( ! $n ) {
			$output .= ' lang="' . esc_attr( $tag ) . '"';
		}
	}

	return $output;
}
add_filter( 'language_attributes', 'remotive_lp_html_lang' );

/* ---- Keep these pages away from search engines and site search ---- */

function remotive_lp_robots( $robots ) {
	if ( remotive_is_landing_page() ) {
		$robots = array(
			'noindex'  => true,
			'nofollow' => true,
		);
	}

	return $robots;
}
add_filter( 'wp_robots', 'remotive_lp_robots', 99 );

// Rank Math, if installed, prints its own robots tag; give it the same answer.
add_filter(
	'rank_math/frontend/robots',
	function ( $robots ) {
		if ( remotive_is_landing_page() ) {
			$robots = array( 'index' => 'noindex', 'follow' => 'nofollow' );
		}
		return $robots;
	}
);

function remotive_lp_robots_header() {
	if ( ! is_admin() && remotive_is_landing_page() ) {
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}
}
add_action( 'template_redirect', 'remotive_lp_robots_header' );

/**
 * IDs of pages using the landing template.
 *
 * @return int[]
 */
function remotive_lp_page_ids() {
	return get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'any',
		'posts_per_page' => 50,
		'fields'         => 'ids',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => REMOTIVE_LANDING_TEMPLATE,
		'no_found_rows'  => true,
	) );
}

function remotive_lp_hide_from_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$ids = remotive_lp_page_ids();

	if ( $ids ) {
		$query->set( 'post__not_in', array_merge( (array) $query->get( 'post__not_in' ), $ids ) );
	}
}
add_action( 'pre_get_posts', 'remotive_lp_hide_from_search' );

function remotive_lp_hide_from_sitemap( $args, $post_type ) {
	if ( 'page' === $post_type ) {
		$ids = remotive_lp_page_ids();
		if ( $ids ) {
			$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), $ids );
		}
	}

	return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'remotive_lp_hide_from_sitemap', 10, 2 );

/* ---- Assets ---- */

function remotive_lp_assets() {
	if ( ! remotive_is_landing_page() ) {
		return;
	}

	$css = get_stylesheet_directory() . '/assets/css/landing.css';
	$js  = get_stylesheet_directory() . '/assets/js/landing.js';

	wp_enqueue_style(
		'remotive-landing',
		get_stylesheet_directory_uri() . '/assets/css/landing.css',
		array( 'remotive-style' ),
		file_exists( $css ) ? filemtime( $css ) : '1.0.0'
	);

	wp_enqueue_script(
		'remotive-landing',
		get_stylesheet_directory_uri() . '/assets/js/landing.js',
		array(),
		file_exists( $js ) ? filemtime( $js ) : '1.0.0',
		true
	);
	wp_script_add_data( 'remotive-landing', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'remotive_lp_assets', 22 );
