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

/** Slug of the confirmation page landing-page leads are sent to. */
const REMOTIVE_LP_THANKS_SLUG = 'audit-requested';

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
			'eyebrow' => array( 'SEO for B2B and B2C brands in Asia', 'SEO untuk jenama B2B dan B2C di Asia', '面向亚洲 B2B 与 B2C 品牌的 SEO', '面向亞洲 B2B 與 B2C 品牌的 SEO' ),
			'title'   => array( 'Be found by buyers already searching for you.', 'Dikenali oleh pembeli yang sedang mencari anda.', '让正在搜索您的买家找到您。', '讓正在搜尋您的買家找到您。' ),
			'lead'    => array(
				'Your site may be losing enquiries to problems you can fix: pages that miss what buyers search for, technical faults, tracking that cannot show what works. Our free audit finds them, ranks them by impact and tells you what to fix first, in Google and in AI search.',
				'Laman web anda mungkin kehilangan pertanyaan kerana masalah yang boleh dibaiki: halaman yang tidak menepati carian pembeli, kesilapan teknikal, dan penjejakan yang tidak dapat menunjukkan apa yang berkesan. Audit percuma kami mengenal pasti masalah tersebut, menyusunnya mengikut impak dan memberitahu apa yang perlu dibaiki dahulu, di Google dan dalam carian AI.',
				'您的网站可能正因这些可以解决的问题而流失咨询：页面没有对准买家的搜索需求、技术故障，以及无法显示成效的追踪。我们的免费审计会找出这些问题，按影响力排序，并告诉您应优先解决什么，无论在 Google 还是 AI 搜索中。',
				'您的網站可能正因這些可以解決的問題而流失諮詢：頁面沒有對準買家的搜尋需求、技術故障，以及無法顯示成效的追蹤。我們的免費審計會找出這些問題，按影響力排序，並告訴您應優先解決什麼，無論在 Google 還是 AI 搜尋中。' ),
			'points'  => array(
				array( 'A ranked fix list: what to do first, and why', 'Senarai pembaikan mengikut keutamaan: apa yang perlu dibuat dahulu, dan sebabnya', '按优先级排序的修复清单：先做什么，为什么', '按優先順序排列的修復清單：先做什麼，為什麼' ),
				array( 'Pages mapped to what your buyers actually search for', 'Halaman dipadankan dengan apa yang sebenarnya dicari pembeli anda', '页面对准买家的真实搜索需求', '頁面對準買家的真實搜尋需求' ),
				array( 'A view of how you appear in AI answers, not only the blue links', 'Gambaran bagaimana anda muncul dalam jawapan AI, bukan sekadar pautan biru', '了解您在 AI 回答中的呈现，而不只是搜索结果链接', '了解您在 AI 回答中的呈現，而不只是搜尋結果連結' ),
			),
			'form_title' => array( 'Get your free SEO audit', 'Dapatkan audit SEO percuma anda', '获取您的免费 SEO 审计', '獲取您的免費 SEO 審計' ),
			'cta'     => array( 'Get my free SEO audit', 'Dapatkan audit SEO percuma saya', '获取我的免费 SEO 审计', '獲取我的免費 SEO 審計' ),
		),
		'google-ads-management' => array(
			'photo_alt' => array( 'A laptop on a desk showing search results', 'Komputer riba di atas meja yang memaparkan hasil carian', '桌上显示搜索结果的笔记本电脑', '桌上顯示搜尋結果的筆記型電腦' ),
			'label'   => array( 'Google Ads', 'Google Ads', 'Google Ads', 'Google Ads' ),
			'eyebrow' => array( 'Google Ads management for lead generation', 'Pengurusan Google Ads untuk penjanaan prospek', '以获取线索为目标的 Google Ads 管理', '以獲取線索為目標的 Google Ads 管理' ),
			'title'   => array( 'Stop paying for clicks that never turn into leads.', 'Berhenti membayar untuk klik yang tidak menjadi prospek.', '别再为无法转化为线索的点击付费。', '別再為無法轉換為線索的點擊付費。' ),
			'lead'    => array(
				'We review your account and tracking first, then run search campaigns measured against qualified leads and pipeline, so your budget goes where it earns. The audit is free.',
				'Kami menyemak akaun dan penjejakan anda dahulu, kemudian mengendalikan kempen carian yang diukur berdasarkan prospek berkualiti dan saluran jualan, supaya bajet anda digunakan di tempat yang menjana hasil. Audit ini percuma.',
				'我们先审查您的账户与追踪设置，再运营以合格线索和销售管道衡量成效的搜索广告，让预算花在真正带来回报的地方。审计免费。',
				'我們先審查您的帳戶與追蹤設定，再營運以合格線索和銷售管道衡量成效的搜尋廣告，讓預算花在真正帶來回報的地方。審計免費。' ),
			'points'  => array(
				array( 'A free review of your account and tracking before any more spend', 'Semakan percuma akaun dan penjejakan anda sebelum sebarang perbelanjaan tambahan', '追加预算之前，先免费审查您的账户与追踪设置', '追加預算之前，先免費審查您的帳戶與追蹤設定' ),
				array( 'Campaigns built around buyer intent and what a lead is worth', 'Kempen dibina berdasarkan niat pembeli dan nilai sesuatu prospek', '围绕买家意图与线索价值搭建的广告', '圍繞買家意圖與線索價值搭建的廣告' ),
				array( 'Plain-language reports tied to the enquiries you receive', 'Laporan bahasa mudah yang dikaitkan dengan pertanyaan yang anda terima', '与您实际收到的咨询挂钩、通俗易懂的报告', '與您實際收到的諮詢掛鉤、通俗易懂的報告' ),
			),
			'form_title' => array( 'Get your free Google Ads audit', 'Dapatkan audit Google Ads percuma anda', '获取您的免费 Google Ads 审计', '獲取您的免費 Google Ads 審計' ),
			'cta'     => array( 'Get my free Google Ads audit', 'Dapatkan audit Google Ads percuma saya', '获取我的免费 Google Ads 审计', '獲取我的免費 Google Ads 審計' ),
		),
		'paid-social-advertising' => array(
			'photo_alt' => array( 'A phone showing a social media feed', 'Telefon yang memaparkan suapan media sosial', '显示社交媒体动态的手机', '顯示社群媒體動態的手機' ),
			'label'   => array( 'Paid social', 'Iklan sosial berbayar', '社交媒体广告', '社群媒體廣告' ),
			'eyebrow' => array( 'Paid social on Meta, LinkedIn and TikTok', 'Iklan sosial berbayar di Meta, LinkedIn dan TikTok', 'Meta、LinkedIn 与 TikTok 社交媒体广告', 'Meta、LinkedIn 與 TikTok 社群媒體廣告' ),
			'title'   => array( 'Paid social that reaches the right buyers, and shows what worked.', 'Iklan sosial berbayar yang mencapai pembeli yang tepat, dan menunjukkan apa yang berkesan.', '精准触达目标买家，并清楚显示什么有效的社交媒体广告。', '精準觸及目標買家，並清楚顯示什麼有效的社群媒體廣告。' ),
			'lead'    => array(
				'We build campaigns around your audience and your offer, test creative quickly and scale what converts. It starts with a free audit.',
				'Kami membina kempen berdasarkan audiens dan tawaran anda, menguji kreatif dengan pantas dan mengembangkan apa yang menukar. Ia bermula dengan audit percuma.',
				'我们围绕您的受众与优惠搭建广告，快速测试创意，并将有转化的部分扩大投放。一切从免费审计开始。',
				'我們圍繞您的受眾與優惠搭建廣告，快速測試創意，並將有轉換的部分擴大投放。一切從免費審計開始。' ),
			'points'  => array(
				array( 'Audience and offer settled before any creative is made', 'Audiens dan tawaran dimuktamadkan sebelum sebarang kreatif dihasilkan', '在制作任何创意之前，先确定受众与优惠', '在製作任何創意之前，先確定受眾與優惠' ),
				array( 'Structured creative tests, so you learn instead of guessing', 'Ujian kreatif berstruktur, supaya anda belajar dan bukan meneka', '有结构的创意测试，让您有依据而非靠猜', '有結構的創意測試，讓您有依據而非靠猜' ),
				array( 'Lead quality tracked past the form, into your pipeline', 'Kualiti prospek dijejak melepasi borang, sehingga ke saluran jualan anda', '线索质量的追踪不止于表单，直达您的销售管道', '線索品質的追蹤不止於表單，直達您的銷售管道' ),
			),
			'form_title' => array( 'Get your free paid social audit', 'Dapatkan audit iklan sosial berbayar percuma anda', '获取您的免费社交媒体广告审计', '獲取您的免費社群媒體廣告審計' ),
			'cta'     => array( 'Get my free paid social audit', 'Dapatkan audit iklan sosial percuma saya', '获取我的免费社交媒体广告审计', '獲取我的免費社群媒體廣告審計' ),
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
			array( 'Singapore, Malaysia, Thailand, Vietnam, Hong Kong and China.', 'Singapura, Malaysia, Thailand, Vietnam, Hong Kong dan China.', '新加坡、马来西亚、泰国、越南、香港和中国。', '新加坡、馬來西亞、泰國、越南、香港和中國。' ),
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
 * from inc/thank-you.php.
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
