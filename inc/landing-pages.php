<?php
/**
 * Remotive Media — service landing pages for paid and social traffic.
 *
 * One page per service (SEO, Google Ads, paid social), each a short funnel:
 * promise and form above the fold, what you get, how it works, a second
 * form. Every form ends on the thank-you page, which fires the conversion
 * event (see inc/thank-you.php).
 *
 * These pages exist for ads and social posts, not for search. They are
 * noindex + nofollow, carry no site navigation, and are kept out of the
 * site's own search and the core sitemap. robots.txt must NOT block them:
 * a crawler has to fetch the page to see the noindex, and Google Ads needs
 * to fetch it to review the ad.
 *
 * Copy is English / Bahasa Melayu / Simplified Chinese, switched client-side
 * (assets/js/landing.js; ?lang=en|ms|zh lets an ad pick the language). The
 * server always renders English visible, so the page works without
 * JavaScript. Template: templates/page-landing.html, which holds only the
 * __REMOTIVE_LANDING__ token this file replaces.
 *
 * The Malay and Chinese copy is a first draft for native-speaker review.
 */

defined( 'ABSPATH' ) || exit;

const REMOTIVE_LANDING_TEMPLATE = 'page-landing';

/**
 * The services, keyed by the slug of the page that sells them.
 *
 * Each text is array( en, ms, zh ).
 *
 * @return array<string,array<string,mixed>>
 */
function remotive_landing_services() {
	return array(
		'lp-seo'        => array(
			'label'   => array( 'SEO', 'SEO', 'SEO' ),
			'eyebrow' => array( 'SEO for Asian markets', 'SEO untuk pasaran Asia', '面向亚洲市场的 SEO' ),
			'title'   => array( 'Be found when buyers search.', 'Dikenali apabila pembeli membuat carian.', '让买家在搜索时找到您。' ),
			'lead'    => array(
				'We fix what holds your site back, then build the pages and authority that bring in qualified enquiries from Google and AI search.',
				'Kami membaiki apa yang menghalang laman web anda, kemudian membina halaman dan kewibawaan yang membawa pertanyaan berkualiti daripada Google dan carian AI.',
				'我们先解决拖累网站表现的问题，再打造能从 Google 和 AI 搜索带来优质咨询的页面与权威度。',
			),
			'points'  => array(
				array( 'A technical and content audit that ranks fixes by impact', 'Audit teknikal dan kandungan yang menyusun pembaikan mengikut impak', '按影响力排序的技术与内容审计' ),
				array( 'Pages built around what your buyers actually search for', 'Halaman dibina berdasarkan carian sebenar pembeli anda', '围绕买家真实搜索需求打造的页面' ),
				array( 'Visibility in AI answers, not only the blue links', 'Keterlihatan dalam jawapan AI, bukan sekadar pautan biru', '不仅是搜索结果，也包括 AI 回答中的曝光' ),
			),
		),
		'lp-google-ads' => array(
			'label'   => array( 'Google Ads', 'Google Ads', 'Google Ads' ),
			'eyebrow' => array( 'Google Ads management', 'Pengurusan Google Ads', 'Google Ads 投放管理' ),
			'title'   => array( 'Google Ads that bring leads, not just clicks.', 'Google Ads yang membawa prospek, bukan sekadar klik.', '带来销售线索的 Google Ads，而不只是点击。' ),
			'lead'    => array(
				'Senior-run search campaigns measured against qualified leads and pipeline, so budget goes where it earns.',
				'Kempen carian yang diurus pakar kanan dan diukur berdasarkan prospek berkualiti serta saluran jualan, supaya bajet digunakan di tempat yang menjana hasil.',
				'由资深团队管理的搜索广告，以合格线索与销售管道衡量成效，让预算花在真正带来回报的地方。',
			),
			'points'  => array(
				array( 'Account and tracking review before any more spend', 'Semakan akaun dan penjejakan sebelum perbelanjaan tambahan', '追加预算之前，先审查账户与追踪设置' ),
				array( 'Campaigns structured around intent and conversion value', 'Kempen distruktur mengikut niat dan nilai penukaran', '围绕搜索意图与转化价值搭建的广告结构' ),
				array( 'Plain-language reporting tied to your enquiries', 'Laporan bahasa mudah yang dikaitkan dengan pertanyaan anda', '与您的咨询挂钩、通俗易懂的报告' ),
			),
		),
		'lp-social-ads' => array(
			'label'   => array( 'Paid social', 'Iklan sosial berbayar', '社交媒体广告' ),
			'eyebrow' => array( 'Paid social advertising', 'Pengiklanan sosial berbayar', '社交媒体付费广告' ),
			'title'   => array( 'Paid social that reaches the right buyers.', 'Iklan sosial berbayar yang mencapai pembeli yang tepat.', '精准触达目标买家的社交媒体广告。' ),
			'lead'    => array(
				'Meta, LinkedIn and TikTok campaigns built around your audience and your offer, tested fast and scaled on what converts.',
				'Kempen Meta, LinkedIn dan TikTok yang dibina berdasarkan audiens dan tawaran anda, diuji dengan pantas dan dikembangkan mengikut apa yang menukar.',
				'围绕您的受众与优惠打造 Meta、LinkedIn 和 TikTok 广告，快速测试，并按转化表现扩大投放。',
			),
			'points'  => array(
				array( 'Audience and offer worked out before creative is made', 'Audiens dan tawaran dikenal pasti sebelum kreatif dihasilkan', '在制作创意之前，先明确受众与优惠' ),
				array( 'Structured creative tests, not guesswork', 'Ujian kreatif berstruktur, bukan andaian', '有结构的创意测试，而非凭猜测' ),
				array( 'Lead quality tracked past the form, into your pipeline', 'Kualiti prospek dijejak melepasi borang, sehingga ke saluran jualan anda', '线索质量的追踪不止于表单，直达您的销售管道' ),
			),
		),
	);
}

/**
 * Whether the current request is one of the landing pages.
 *
 * @return bool
 */
function remotive_is_landing_page() {
	return is_page() && is_page_template( REMOTIVE_LANDING_TEMPLATE );
}

/**
 * Text in all three languages, one element per language.
 *
 * @param array  $t   array( en, ms, zh ).
 * @param string $tag Wrapping element.
 * @param string $cls Optional class.
 * @return string
 */
function remotive_lp_t( $t, $tag = 'span', $cls = '' ) {
	$langs = array( 'en' => 'en', 'ms' => 'ms', 'zh' => 'zh-Hans' );
	$out   = '';
	$i     = 0;

	foreach ( $langs as $key => $lang ) {
		$out .= sprintf(
			'<%1$s%2$s lang="%3$s" data-l="%4$s">%5$s</%1$s>',
			$tag,
			$cls ? ' class="' . esc_attr( $cls ) . '"' : '',
			esc_attr( $lang ),
			esc_attr( $key ),
			esc_html( $t[ $i ] )
		);
		$i++;
	}

	return $out;
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
	$id = 'rm-lp-' . $pos;

	$field = function ( $name, $type, $label, $required, $autocomplete ) use ( $id ) {
		return sprintf(
			'<p class="rm-lp__field"><label for="%1$s-%2$s">%3$s%4$s</label><input type="%5$s" id="%1$s-%2$s" name="%2$s" autocomplete="%6$s"%7$s></p>',
			esc_attr( $id ),
			esc_attr( $name ),
			remotive_lp_t( $label ),
			$required ? '' : ' <span class="rm-lp__opt">' . remotive_lp_t( array( '(optional)', '(pilihan)', '（选填）' ) ) . '</span>',
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
		'success' => array( 'Thanks. We will be in touch within three business days.', 'Terima kasih. Kami akan menghubungi anda dalam tiga hari bekerja.', '谢谢。我们将在三个工作日内与您联系。' ),
		'error'   => array( 'Something went wrong sending that. Please try again, or email us directly.', 'Sesuatu tidak kena semasa menghantar. Sila cuba lagi, atau e-mel kami terus.', '提交时出了问题。请重试，或直接给我们发送电子邮件。' ),
	) as $state => $t ) {
		$msgs .= remotive_lp_t( $t, 'span', 'rm-lp__msg rm-lp__msg--' . $state ) ;
	}
	$msgs = str_replace( '<span class=', '<span data-msg class=', $msgs );

	return '<form class="rm-lp__form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">'
		. '<input type="hidden" name="action" value="remotive_lp_submit">'
		. '<input type="hidden" name="remotive_lp_nonce" value="' . esc_attr( wp_create_nonce( 'remotive_lp_submit' ) ) . '">'
		. '<input type="hidden" name="service" value="' . esc_attr( $service ) . '">'
		. $tracked
		. '<div class="rm-lp__hp" aria-hidden="true"><label for="' . esc_attr( $id ) . '-hp">Leave this field empty</label>'
		. '<input type="text" id="' . esc_attr( $id ) . '-hp" name="remotive_lp_website" tabindex="-1" autocomplete="off"></div>'
		. $field( 'name', 'text', array( 'Your name', 'Nama anda', '您的姓名' ), true, 'name' )
		. $field( 'email', 'email', array( 'Work email', 'E-mel kerja', '工作邮箱' ), true, 'email' )
		. $field( 'site', 'text', array( 'Your website', 'Laman web anda', '您的网站' ), false, 'url' )
		. '<button type="submit" class="rm-lp__submit">' . remotive_lp_t( array( 'Get my free audit', 'Dapatkan audit percuma saya', '获取免费审计' ) ) . '</button>'
		. '<p class="rm-lp__fine">' . remotive_lp_t( array( '30 minutes, no commitment. We reply within three business days.', '30 minit, tanpa komitmen. Kami membalas dalam tiga hari bekerja.', '30 分钟，无需承诺。我们将在三个工作日内回复。' ) )
		. ' <a href="' . esc_url( home_url( '/privacy/' ) ) . '">' . remotive_lp_t( array( 'Privacy', 'Privasi', '隐私政策' ) ) . '</a></p>'
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
 * Markup for the page being rendered.
 *
 * @return string
 */
function remotive_lp_render() {
	$slug     = get_post_field( 'post_name', get_queried_object_id() );
	$services = remotive_landing_services();

	if ( ! isset( $services[ $slug ] ) ) {
		return '';
	}

	$s    = $services[ $slug ];
	$btns = '';
	foreach ( array( 'en' => 'English', 'ms' => 'Bahasa Melayu', 'zh' => '简体中文' ) as $key => $name ) {
		$btns .= sprintf(
			'<button type="button" class="rm-lp__lang" data-set-lang="%1$s" lang="%2$s" aria-pressed="%3$s">%4$s</button>',
			esc_attr( $key ),
			'zh' === $key ? 'zh-Hans' : esc_attr( $key ),
			'en' === $key ? 'true' : 'false',
			esc_html( $name )
		);
	}

	$points = '';
	foreach ( $s['points'] as $p ) {
		$points .= '<li>' . remotive_lp_t( $p ) . '</li>';
	}

	$steps = '';
	foreach ( array(
		array( array( 'Book a 30-minute call', 'Tempah panggilan 30 minit', '预约 30 分钟通话' ), array( 'Tell us what you sell, where, and what is not working.', 'Beritahu kami apa yang anda jual, di mana, dan apa yang tidak berjaya.', '告诉我们您的产品、市场，以及哪里不理想。' ) ),
		array( array( 'We send what we would fix first', 'Kami hantar apa yang akan kami baiki dahulu', '我们告知会优先解决什么' ), array( 'A specific view within three business days, not a brochure.', 'Pandangan khusus dalam tiga hari bekerja, bukan brosur.', '三个工作日内给出具体意见，而不是宣传册。' ) ),
		array( array( 'You decide', 'Anda yang memutuskan', '由您决定' ), array( 'Work with us or take the plan and run. No commitment either way.', 'Bekerja dengan kami atau gunakan pelan itu sendiri. Tiada komitmen.', '可与我们合作，也可自行执行方案，均无需承诺。' ) ),
	) as $i => $st ) {
		$steps .= '<li class="rm-lp__step"><span class="rm-lp__num">' . ( $i + 1 ) . '</span><h3>' . remotive_lp_t( $st[0] ) . '</h3><p>' . remotive_lp_t( $st[1] ) . '</p></li>';
	}

	$markets = remotive_lp_t(
		array(
			'Singapore · Malaysia · Thailand · Hong Kong · China',
			'Singapura · Malaysia · Thailand · Hong Kong · China',
			'新加坡 · 马来西亚 · 泰国 · 香港 · 中国',
		)
	);

	return '<div class="rm-lp" data-lang="en" data-service="' . esc_attr( $slug ) . '">'
		. '<header class="rm-lp__bar"><span class="rm-lp__brand">Re:Motive Media</span>'
		. '<div class="rm-lp__langs" role="group" aria-label="Language / Bahasa / 语言">' . $btns . '</div></header>'
		. '<main id="main" class="rm-lp__main">'
		. '<section class="rm-lp__hero"><div class="rm-lp__copy">'
		. '<p class="rm-lp__eyebrow">' . remotive_lp_t( $s['eyebrow'] ) . '</p>'
		. '<h1>' . remotive_lp_t( $s['title'] ) . '</h1>'
		. remotive_lp_t( $s['lead'], 'p', 'rm-lp__lead' )
		. '<ul class="rm-lp__points">' . $points . '</ul></div>'
		. '<div class="rm-lp__card" id="rm-lp-start"><h2>' . remotive_lp_t( array( 'Get a free audit', 'Dapatkan audit percuma', '获取免费审计' ) ) . '</h2>'
		. remotive_lp_form( $slug, 'top' ) . '</div></section>'
		. '<section class="rm-lp__section" aria-labelledby="rm-lp-how"><h2 id="rm-lp-how">' . remotive_lp_t( array( 'How it works', 'Cara ia berfungsi', '合作流程' ) ) . '</h2>'
		. '<ol class="rm-lp__steps">' . $steps . '</ol></section>'
		. '<section class="rm-lp__section rm-lp__trust"><p><strong>' . remotive_lp_t( array( 'Senior-led and independent.', 'Diketuai pakar kanan dan bebas.', '资深团队领导，独立运营。' ) ) . '</strong> ' . $markets . '</p></section>'
		. '<section class="rm-lp__section rm-lp__final" aria-labelledby="rm-lp-final"><h2 id="rm-lp-final">' . remotive_lp_t( array( 'Ready to see what we would fix first?', 'Bersedia melihat apa yang akan kami baiki dahulu?', '想知道我们会优先解决什么吗？' ) ) . '</h2>'
		. '<div class="rm-lp__card">' . remotive_lp_form( $slug, 'bottom' ) . '</div></section>'
		. '</main>'
		. '<a class="rm-lp__sticky" href="#rm-lp-start">' . remotive_lp_t( array( 'Get my free audit', 'Dapatkan audit percuma saya', '获取免费审计' ) ) . '</a>'
		. '<footer class="rm-lp__foot">&copy; ' . esc_html( gmdate( 'Y' ) ) . ' Re:Motive Media</footer>'
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

	$base = wp_get_referer();
	$base = $base ? remove_query_arg( 'remotive_lp', $base ) : home_url( '/' );

	remotive_handle_lead_form_submission( array(
		'form_key'       => 'lp',
		'nonce_action'   => 'remotive_lp_submit',
		'nonce_name'     => 'remotive_lp_nonce',
		'honeypot_field' => 'remotive_lp_website',
		'redirect_base'  => $base,
		/* translators: %s: the site name */
		'email_subject'  => sprintf( __( 'New landing page enquiry from %s', 'remotive' ), get_bloginfo( 'name' ) ),
		'extra_lines'    => $extra,
		'thanks_args'    => '' !== $service ? array( 'service' => $service ) : array(),
	) );
}
add_action( 'admin_post_remotive_lp_submit', 'remotive_handle_landing_submission' );
add_action( 'admin_post_nopriv_remotive_lp_submit', 'remotive_handle_landing_submission' );

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
