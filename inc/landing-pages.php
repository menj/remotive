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
 * Copy is English / Bahasa Melayu / Simplified Chinese / Traditional Chinese, one URL each
 * by URL: each language has its own path (see remotive_lp_languages()), the
 * server renders only that language, and no JavaScript is needed to read the page. Template: templates/page-landing.html, which holds only the
 * __REMOTIVE_LANDING__ token this file replaces.
 *
 * The Malay and Chinese copy is a first draft for native-speaker review.
 */

defined( 'ABSPATH' ) || exit;

const REMOTIVE_LANDING_TEMPLATE = 'page-landing';

/**
 * The services, keyed by the slug of the page that sells them.
 *
 * Each text is array( en, ms, zh-Hans, zh-Hant ).
 *
 * @return array<string,array<string,mixed>>
 */
function remotive_landing_services() {
	return array(
		'seo-audit'     => array(
			'photo_alt' => array( 'A laptop showing search and analytics charts', 'Komputer riba yang memaparkan carta carian dan analitik', '显示搜索与分析图表的笔记本电脑', '顯示搜尋與分析圖表的筆記型電腦' ),
			'label'   => array( 'SEO', 'SEO', 'SEO', 'SEO' ),
			'eyebrow' => array( 'SEO for Asian markets', 'SEO untuk pasaran Asia', '面向亚洲市场的 SEO', '面向亞洲市場的 SEO' ),
			'title'   => array( 'Be found when buyers search.', 'Dikenali apabila pembeli membuat carian.', '让买家在搜索时找到您。', '讓買家在搜尋時找到您。' ),
			'lead'    => array(
				'We fix what holds your site back, then build the pages and authority that bring in qualified enquiries from Google and AI search.',
				'Kami membaiki apa yang menghalang laman web anda, kemudian membina halaman dan kewibawaan yang membawa pertanyaan berkualiti daripada Google dan carian AI.',
				'我们先解决拖累网站表现的问题，再打造能从 Google 和 AI 搜索带来优质咨询的页面与权威度。', '我們先解決拖累網站表現的問題，再打造能從 Google 和 AI 搜尋帶來優質諮詢的頁面與權威度。',
			),
			'points'  => array(
				array( 'A technical and content audit that ranks fixes by impact', 'Audit teknikal dan kandungan yang menyusun pembaikan mengikut impak', '按影响力排序的技术与内容审计', '按影響力排序的技術與內容審計' ),
				array( 'Pages built around what your buyers actually search for', 'Halaman dibina berdasarkan carian sebenar pembeli anda', '围绕买家真实搜索需求打造的页面', '圍繞買家真實搜尋需求打造的頁面' ),
				array( 'Visibility in AI answers, not only the blue links', 'Keterlihatan dalam jawapan AI, bukan sekadar pautan biru', '不仅是搜索结果，也包括 AI 回答中的曝光', '不僅是搜尋結果，也包括 AI 回答中的曝光' ),
			),
		),
		'google-ads-management' => array(
			'photo_alt' => array( 'A laptop on a desk with marketing material', 'Komputer riba di atas meja bersama bahan pemasaran', '桌上的笔记本电脑与营销资料', '桌上的筆記型電腦與行銷資料' ),
			'label'   => array( 'Google Ads', 'Google Ads', 'Google Ads', 'Google Ads' ),
			'eyebrow' => array( 'Google Ads management', 'Pengurusan Google Ads', 'Google Ads 投放管理', 'Google Ads 投放管理' ),
			'title'   => array( 'Google Ads that bring leads, not just clicks.', 'Google Ads yang membawa prospek, bukan sekadar klik.', '带来销售线索的 Google Ads，而不只是点击。', '帶來銷售線索的 Google Ads，而不只是點擊。' ),
			'lead'    => array(
				'Senior-run search campaigns measured against qualified leads and pipeline, so budget goes where it earns.',
				'Kempen carian yang diurus pakar kanan dan diukur berdasarkan prospek berkualiti serta saluran jualan, supaya bajet digunakan di tempat yang menjana hasil.',
				'由资深团队管理的搜索广告，以合格线索与销售管道衡量成效，让预算花在真正带来回报的地方。', '由資深團隊管理的搜尋廣告，以合格線索與銷售管道衡量成效，讓預算花在真正帶來回報的地方。',
			),
			'points'  => array(
				array( 'Account and tracking review before any more spend', 'Semakan akaun dan penjejakan sebelum perbelanjaan tambahan', '追加预算之前，先审查账户与追踪设置', '追加預算之前，先審查帳戶與追蹤設定' ),
				array( 'Campaigns structured around intent and conversion value', 'Kempen distruktur mengikut niat dan nilai penukaran', '围绕搜索意图与转化价值搭建的广告结构', '圍繞搜尋意圖與轉換價值搭建的廣告結構' ),
				array( 'Plain-language reporting tied to your enquiries', 'Laporan bahasa mudah yang dikaitkan dengan pertanyaan anda', '与您的咨询挂钩、通俗易懂的报告', '與您的諮詢掛鉤、通俗易懂的報告' ),
			),
		),
		'paid-social-advertising' => array(
			'photo_alt' => array( 'A phone showing a social media feed', 'Telefon yang memaparkan suapan media sosial', '显示社交媒体动态的手机', '顯示社群媒體動態的手機' ),
			'label'   => array( 'Paid social', 'Iklan sosial berbayar', '社交媒体广告', '社群媒體廣告' ),
			'eyebrow' => array( 'Paid social advertising', 'Pengiklanan sosial berbayar', '社交媒体付费广告', '社群媒體付費廣告' ),
			'title'   => array( 'Paid social that reaches the right buyers.', 'Iklan sosial berbayar yang mencapai pembeli yang tepat.', '精准触达目标买家的社交媒体广告。', '精準觸及目標買家的社群媒體廣告。' ),
			'lead'    => array(
				'Meta, LinkedIn and TikTok campaigns built around your audience and your offer, tested fast and scaled on what converts.',
				'Kempen Meta, LinkedIn dan TikTok yang dibina berdasarkan audiens dan tawaran anda, diuji dengan pantas dan dikembangkan mengikut apa yang menukar.',
				'围绕您的受众与优惠打造 Meta、LinkedIn 和 TikTok 广告，快速测试，并按转化表现扩大投放。', '圍繞您的受眾與優惠打造 Meta、LinkedIn 和 TikTok 廣告，快速測試，並按轉換表現擴大投放。',
			),
			'points'  => array(
				array( 'Audience and offer worked out before creative is made', 'Audiens dan tawaran dikenal pasti sebelum kreatif dihasilkan', '在制作创意之前，先明确受众与优惠', '在製作創意之前，先明確受眾與優惠' ),
				array( 'Structured creative tests, not guesswork', 'Ujian kreatif berstruktur, bukan andaian', '有结构的创意测试，而非凭猜测', '有結構的創意測試，而非憑猜測' ),
				array( 'Lead quality tracked past the form, into your pipeline', 'Kualiti prospek dijejak melepasi borang, sehingga ke saluran jualan anda', '线索质量的追踪不止于表单，直达您的销售管道', '線索品質的追蹤不止於表單，直達您的銷售管道' ),
			),
		),
	);
}

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

	$slugs = array_map( 'preg_quote', array_keys( remotive_landing_services() ) );

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
	if ( '1' !== get_option( 'remotive_lp_rewrite_v' ) ) {
		flush_rewrite_rules( false );
		update_option( 'remotive_lp_rewrite_v', '1', true );
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

		if ( isset( remotive_landing_services()[ $slug ] ) ) {
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
		. $tracked
		. '<div class="rm-lp__hp" aria-hidden="true"><label for="' . esc_attr( $id ) . '-hp">Leave this field empty</label>'
		. '<input type="text" id="' . esc_attr( $id ) . '-hp" name="remotive_lp_website" tabindex="-1" autocomplete="off"></div>'
		. $field( 'name', 'text', array( 'Your name', 'Nama anda', '您的姓名', '您的姓名' ), true, 'name' )
		. $field( 'email', 'email', array( 'Work email', 'E-mel kerja', '工作邮箱', '工作信箱' ), true, 'email' )
		. $field( 'site', 'text', array( 'Your website', 'Laman web anda', '您的网站', '您的網站' ), false, 'url' )
		. '<button type="submit" class="rm-lp__submit">' . remotive_lp_t( array( 'Get my free audit', 'Dapatkan audit percuma saya', '获取免费审计', '獲取免費審計' ) ) . '</button>'
		. '<p class="rm-lp__fine">' . remotive_lp_t( array( '30 minutes, no commitment. We reply within three business days.', '30 minit, tanpa komitmen. Kami membalas dalam tiga hari bekerja.', '30 分钟，无需承诺。我们将在三个工作日内回复。', '30 分鐘，無需承諾。我們將在三個工作日內回覆。' ) )
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
 * The questions answered at the foot of every landing page, and in its
 * FAQPage structured data: three shared ones plus one per service. Each is
 * array( question, answer ), each of those array( en, ms, zh-Hans, zh-Hant ).
 *
 * @param string $slug Service slug.
 * @return array<int,array<int,array<int,string>>>
 */
function remotive_lp_faq_items( $slug ) {
	$shared = array(
		array(
			array( 'What happens after I send the form?', 'Apa yang berlaku selepas saya menghantar borang?', '提交表单后会怎样？', '提交表單後會怎樣？' ),
			array( 'We look at what you have and reply within three business days with what we would fix first. A 30-minute call can follow if you want one.', 'Kami meneliti apa yang anda ada dan membalas dalam tiga hari bekerja dengan apa yang akan kami baiki dahulu. Panggilan 30 minit boleh menyusul jika anda mahu.', '我们会查看您的情况，并在三个工作日内回复，告诉您我们会优先解决什么。如有需要，可再安排 30 分钟通话。', '我們會查看您的情況，並在三個工作日內回覆，告訴您我們會優先解決什麼。如有需要，可再安排 30 分鐘通話。' ),
		),
		array(
			array( 'Is there a commitment?', 'Adakah terdapat komitmen?', '需要承诺吗？', '需要承諾嗎？' ),
			array( 'No. The audit is free. You can work with us, or take the plan and run it yourself.', 'Tidak. Audit ini percuma. Anda boleh bekerja dengan kami, atau menggunakan pelan itu sendiri.', '不需要。审计是免费的。您可以与我们合作，也可以自行执行方案。', '不需要。審計是免費的。您可以與我們合作，也可以自行執行方案。' ),
		),
		array(
			array( 'Which markets do you work in?', 'Di pasaran mana anda beroperasi?', '你们服务哪些市场？', '你們服務哪些市場？' ),
			array( 'Singapore, Malaysia, Thailand, Hong Kong and China.', 'Singapura, Malaysia, Thailand, Hong Kong dan China.', '新加坡、马来西亚、泰国、香港和中国。', '新加坡、馬來西亞、泰國、香港和中國。' ),
		),
	);

	$specific = array(
		'seo-audit'               => array(
			array( 'Do you also optimise for AI search?', 'Adakah anda juga mengoptimumkan untuk carian AI?', '你们也做 AI 搜索优化吗？', '你們也做 AI 搜尋優化嗎？' ),
			array( 'Yes. We look at how your pages appear in AI answers as well as in Google, and shape them so they can be found and cited.', 'Ya. Kami meneliti bagaimana halaman anda muncul dalam jawapan AI serta di Google, dan membentuknya supaya boleh ditemui dan dirujuk.', '是的。我们会查看您的页面在 AI 回答和 Google 中的呈现方式，并优化页面，使其更容易被找到和引用。', '是的。我們會查看您的頁面在 AI 回答和 Google 中的呈現方式，並優化頁面，使其更容易被找到和引用。' ),
		),
		'google-ads-management'   => array(
			array( 'Do I need an existing Google Ads account?', 'Adakah saya perlukan akaun Google Ads sedia ada?', '我需要已有 Google Ads 账户吗？', '我需要已有 Google Ads 帳戶嗎？' ),
			array( 'No. If you have one we review it first. If you do not, we start with the account and tracking setup before any budget is spent.', 'Tidak. Jika anda ada, kami menyemaknya dahulu. Jika tiada, kami bermula dengan penyediaan akaun dan penjejakan sebelum sebarang bajet dibelanjakan.', '不需要。如果已有账户，我们会先审查；如果没有，我们会先完成账户与追踪设置，再投入预算。', '不需要。如果已有帳戶，我們會先審查；如果沒有，我們會先完成帳戶與追蹤設定，再投入預算。' ),
		),
		'paid-social-advertising' => array(
			array( 'Which platforms do you run?', 'Platform mana yang anda uruskan?', '你们投放哪些平台？', '你們投放哪些平台？' ),
			array( 'Meta (Facebook and Instagram), LinkedIn and TikTok, chosen to fit where your buyers are.', 'Meta (Facebook dan Instagram), LinkedIn dan TikTok, dipilih mengikut tempat pembeli anda berada.', 'Meta（Facebook 和 Instagram）、LinkedIn 和 TikTok，根据您的买家所在之处选择。', 'Meta（Facebook 和 Instagram）、LinkedIn 和 TikTok，根據您的買家所在之處選擇。' ),
		),
	);

	return isset( $specific[ $slug ] ) ? array_merge( array( $specific[ $slug ] ), $shared ) : $shared;
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
	// Real links to each language's own URL, labelled with the language
	// code; the full name is the accessible name. Campaign parameters are
	// added to these links by assets/js/landing.js, so switching keeps them.
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

	$points = '';
	foreach ( $s['points'] as $p ) {
		$points .= '<li>' . remotive_lp_t( $p ) . '</li>';
	}

	$steps = '';
	foreach ( array(
		array( array( 'Book a 30-minute call', 'Tempah panggilan 30 minit', '预约 30 分钟通话', '預約 30 分鐘通話' ), array( 'Tell us what you sell, where, and what is not working.', 'Beritahu kami apa yang anda jual, di mana, dan apa yang tidak berjaya.', '告诉我们您的产品、市场，以及哪里不理想。', '告訴我們您的產品、市場，以及哪裡不理想。' ) ),
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
		. '<div class="rm-lp__card" id="rm-lp-start"><h2>' . remotive_lp_t( array( 'Get a free audit', 'Dapatkan audit percuma', '获取免费审计', '獲取免費審計' ) ) . '</h2>'
		. remotive_lp_form( $slug, 'top' ) . '</div></section>'
		. '<section class="rm-lp__section" aria-labelledby="rm-lp-how"><h2 id="rm-lp-how">' . remotive_lp_t( array( 'How it works', 'Cara ia berfungsi', '合作流程', '合作流程' ) ) . '</h2>'
		. '<ol class="rm-lp__steps">' . $steps . '</ol></section>'
		. '<section class="rm-lp__section rm-lp__trust" aria-labelledby="rm-lp-where"><p id="rm-lp-where"><strong>' . remotive_lp_t( array( 'Senior-led and independent.', 'Diketuai pakar kanan dan bebas.', '资深团队领导，独立运营。', '資深團隊領導，獨立營運。' ) ) . '</strong> ' . remotive_lp_t( array( 'Working across five markets:', 'Beroperasi di lima pasaran:', '服务五大市场：', '服務五大市場：' ) ) . '</p>'
		. '<ul class="rm-lp__cities">' . $cities . '</ul></section>'
		. '<section class="rm-lp__section rm-lp__final" aria-labelledby="rm-lp-final"><div class="rm-lp__final-copy"><h2 id="rm-lp-final">' . remotive_lp_t( array( 'Ready to see what we would fix first?', 'Bersedia melihat apa yang akan kami baiki dahulu?', '想知道我们会优先解决什么吗？', '想知道我們會優先解決什麼嗎？' ) ) . '</h2>'
		. remotive_lp_t( array( 'A free audit, a specific view within three business days, and no commitment either way.', 'Audit percuma, pandangan khusus dalam tiga hari bekerja, dan tiada komitmen.', '免费审计，三个工作日内给出具体意见，无需任何承诺。', '免費審計，三個工作日內提出具體意見，無需任何承諾。' ), 'p', 'rm-lp__lead' ) . '</div>'
		. '<div class="rm-lp__card">' . remotive_lp_form( $slug, 'bottom' ) . '</div></section>'
		. '<section class="rm-lp__section rm-lp__faq" aria-labelledby="rm-lp-faq"><h2 id="rm-lp-faq">' . remotive_lp_t( array( 'Frequently asked questions', 'Soalan lazim', '常见问题', '常見問題' ) ) . '</h2><div class="rm-lp__faqs">' . $faqs . '</div></section>'
		. '</main>'
		. '<a class="rm-lp__sticky" href="#rm-lp-start">' . remotive_lp_t( array( 'Get my free audit', 'Dapatkan audit percuma saya', '获取免费审计', '獲取免費審計' ) ) . '</a>'
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

	if ( ! isset( remotive_landing_services()[ $slug ] ) ) {
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
