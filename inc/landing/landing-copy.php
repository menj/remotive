<?php
/**
 * Remotive Media — copy for the ad landing pages.
 *
 * The words, kept apart from the rendering and routing in
 * inc/landing/landing-pages.php so they can be edited, translated and reviewed without
 * reading code. Every text is an array of four strings in this order:
 * English, Bahasa Melayu, Simplified Chinese (zh-Hans), Traditional Chinese
 * (zh-Hant). The Malay and Chinese copy is a first draft for native-speaker
 * review.
 *
 * Not here: the short shared labels inside the render functions (form labels,
 * step titles, the thank-you copy), which sit next to the markup they fill.
 */

defined( 'ABSPATH' ) || exit;

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
			'title'   => array( 'Be found by buyers already searching for you.', 'Dikenali oleh pembeli yang sedang mencari anda', '让正在搜索您的买家找到您。', '讓正在搜尋您的買家找到您。' ),
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
			'proof'   => array(
				array(
					array( '+198%', '+198%', '+198%', '+198%' ),
					array( 'Organic traffic tripled in month two, then held through four consecutive Google core updates.', 'Trafik organik meningkat tiga kali ganda pada bulan kedua, kemudian kekal melalui empat kemas kini teras Google berturut-turut.', '有机流量在第二个月增长两倍，并在连续四次 Google 核心算法更新中保持稳定。', '有機流量在第二個月成長兩倍，並在連續四次 Google 核心演算法更新中保持穩定。' ),
					array( 'B2B industrial supplier · 8-month engagement', 'Pembekal industri B2B · penglibatan 8 bulan', 'B2B 工业供应商 · 8 个月合作', 'B2B 工業供應商 · 8 個月合作' ),
				),
				array(
					array( '+94%', '+94%', '+94%', '+94%' ),
					array( 'Every tracked SEO metric up in a single month: traffic, sessions and engagement.', 'Setiap metrik SEO yang dijejaki meningkat dalam satu bulan: trafik, sesi, dan penglibatan.', '一个月内，所有被追踪的 SEO 指标全部上升：流量、会话与互动。', '一個月內，所有被追蹤的 SEO 指標全部上升：流量、工作階段與互動。' ),
					array( 'B2B services site', 'Tapak perkhidmatan B2B', 'B2B 服务网站', 'B2B 服務網站' ),
				),
				array(
					array( '+34%', '+34%', '+34%', '+34%' ),
					array( 'Search clicks up in month one, with 150% more sales-qualified leads within four weeks.', 'Klik carian naik pada bulan pertama, dengan prospek berkelayakan jualan 150% lebih banyak dalam empat minggu.', '第一个月搜索点击增长，四周内销售合格线索增加 150%。', '第一個月搜尋點擊成長，四週內銷售合格潛在客戶增加 150%。' ),
					array( 'Premium healthcare provider', 'Penyedia penjagaan kesihatan premium', '高端医疗服务机构', '高端醫療服務機構' ),
				),
			),
		),
		'google-ads-management' => array(
			'photo_alt' => array( 'A laptop on a desk showing search results', 'Komputer riba di atas meja yang memaparkan hasil carian', '桌上显示搜索结果的笔记本电脑', '桌上顯示搜尋結果的筆記型電腦' ),
			'label'   => array( 'Google Ads', 'Google Ads', 'Google Ads', 'Google Ads' ),
			'eyebrow' => array( 'Google Ads management for lead generation', 'Pengurusan Google Ads untuk penjanaan prospek', '以获取线索为目标的 Google Ads 管理', '以獲取線索為目標的 Google Ads 管理' ),
			'title'   => array( 'Stop paying for clicks that never turn into leads.', 'Berhenti membayar untuk klik yang tidak menjadi prospek', '别再为无法转化为线索的点击付费。', '別再為無法轉換為線索的點擊付費。' ),
			'lead'    => array(
				'We review your account and tracking first, then run search campaigns measured against qualified leads and pipeline, so your budget goes where it earns. The audit is free.',
				'Kami menyemak akaun dan penjejakan anda dahulu, kemudian mengendalikan kempen carian yang diukur berdasarkan prospek berkualiti dan saluran jualan supaya bajet anda digunakan di tempat yang menjana hasil. Audit ini percuma.',
				'我们先审查您的账户与追踪设置，再运营以合格线索和销售管道衡量成效的搜索广告，让预算花在真正带来回报的地方。审计免费。',
				'我們先審查您的帳戶與追蹤設定，再營運以合格線索和銷售管道衡量成效的搜尋廣告，讓預算花在真正帶來回報的地方。審計免費。' ),
			'points'  => array(
				array( 'A free review of your account and tracking before any more spend', 'Semakan percuma akaun dan penjejakan anda sebelum sebarang perbelanjaan tambahan', '追加预算之前，先免费审查您的账户与追踪设置', '追加預算之前，先免費審查您的帳戶與追蹤設定' ),
				array( 'Campaigns built around buyer intent and what a lead is worth', 'Kempen dibina berdasarkan niat pembeli dan nilai sesuatu prospek', '围绕买家意图与线索价值搭建的广告', '圍繞買家意圖與線索價值搭建的廣告' ),
				array( 'Plain-language reports tied to the enquiries you receive', 'Laporan bahasa mudah yang dikaitkan dengan pertanyaan yang anda terima', '与您实际收到的咨询挂钩、通俗易懂的报告', '與您實際收到的諮詢掛鉤、通俗易懂的報告' ),
			),
			'form_title' => array( 'Get your free Google Ads audit', 'Dapatkan audit Google Ads percuma anda', '获取您的免费 Google Ads 审计', '獲取您的免費 Google Ads 審計' ),
			'cta'     => array( 'Get my free Google Ads audit', 'Dapatkan audit Google Ads percuma saya', '获取我的免费 Google Ads 审计', '獲取我的免費 Google Ads 審計' ),
			'proof'   => array(
				array(
					array( '+150%', '+150%', '+150%', '+150%' ),
					array( 'Qualified leads up 150% on the same budget, after restructuring paid search.', 'Prospek berkelayakan naik 150% dengan belanjawan yang sama selepas carian berbayar distruktur semula.', '重构付费搜索后，同等预算下合格线索增长 150%。', '重構付費搜尋後，同等預算下合格潛在客戶成長 150%。' ),
					array( 'B2B corporate gifting brand', 'Jenama hadiah korporat B2B', 'B2B 企业礼品品牌', 'B2B 企業禮品品牌' ),
				),
				array(
					array( '+25–35%', '+25 – 35%', '+25–35%', '+25–35%' ),
					array( 'Conversion rate lifted while cost per conversion fell 20–30%, across four regulated markets.', 'Kadar penukaran meningkat manakala kos setiap penukaran turun 20 – 30%, merentasi empat pasaran terkawal.', '转化率提升，同时每次转化成本下降 20–30%，覆盖四个受监管市场。', '轉換率提升，同時每次轉換成本下降 20–30%，涵蓋四個受監管市場。' ),
					array( 'Global asset manager · 4 APAC markets', 'Pengurus aset global · 4 pasaran APAC', '全球资产管理公司 · 4 个亚太市场', '全球資產管理公司 · 4 個亞太市場' ),
				),
				array(
					array( '85%', '85%', '85%', '85%' ),
					array( 'CPM saving against market rate, with 26.1M completed views and 32,298 leads for distributors.', 'Penjimatan CPM berbanding kadar pasaran, dengan 26.1 juta tontonan lengkap dan 32,298 prospek untuk pengedar.', '相比市场价格节省 85% 的 CPM，并带来 2,610 万次完整播放与 32,298 条经销商线索。', '相較市場價格節省 85% 的 CPM，並帶來 2,610 萬次完整播放與 32,298 條經銷商潛在客戶。' ),
					array( 'Global automotive marque · 7 Asian markets', 'Jenama automotif global · 7 pasaran Asia', '全球汽车品牌 · 7 个亚洲市场', '全球汽車品牌 · 7 個亞洲市場' ),
				),
			),
		),
		'paid-social-advertising' => array(
			'photo_alt' => array( 'A phone showing a social media feed', 'Telefon yang memaparkan suapan media sosial', '显示社交媒体动态的手机', '顯示社群媒體動態的手機' ),
			'label'   => array( 'Paid social', 'Iklan sosial berbayar', '社交媒体广告', '社群媒體廣告' ),
			'eyebrow' => array( 'Paid social on Meta, LinkedIn and TikTok', 'Iklan sosial berbayar di Meta, LinkedIn dan TikTok', 'Meta、LinkedIn 与 TikTok 社交媒体广告', 'Meta、LinkedIn 與 TikTok 社群媒體廣告' ),
			'title'   => array( 'Paid social that reaches the right buyers, and shows what worked.', 'Iklan sosial berbayar yang mencapai pembeli yang tepat, dan menunjukkan apa yang berkesan', '精准触达目标买家，并清楚显示什么有效的社交媒体广告。', '精準觸及目標買家，並清楚顯示什麼有效的社群媒體廣告。' ),
			'lead'    => array(
				'We build campaigns around your audience and your offer, test creative quickly and scale what converts. It starts with a free audit.',
				'Kami membina kempen berdasarkan audiens dan tawaran anda, menguji kreatif dengan pantas dan mengembangkan apa yang menukar. Ia bermula dengan audit percuma.',
				'我们围绕您的受众与优惠搭建广告，快速测试创意，并将有转化的部分扩大投放。一切从免费审计开始。',
				'我們圍繞您的受眾與優惠搭建廣告，快速測試創意，並將有轉換的部分擴大投放。一切從免費審計開始。' ),
			'points'  => array(
				array( 'Audience and offer settled before any creative is made', 'Audiens dan tawaran dimuktamadkan sebelum sebarang kreatif dihasilkan', '在制作任何创意之前，先确定受众与优惠', '在製作任何創意之前，先確定受眾與優惠' ),
				array( 'Structured creative tests, so you learn instead of guessing', 'Ujian kreatif berstruktur supaya anda belajar dan bukan meneka', '有结构的创意测试，让您有依据而非靠猜', '有結構的創意測試，讓您有依據而非靠猜' ),
				array( 'Lead quality tracked past the form, into your pipeline', 'Kualiti prospek dijejak melepasi borang, sehingga ke saluran jualan anda', '线索质量的追踪不止于表单，直达您的销售管道', '線索品質的追蹤不止於表單，直達您的銷售管道' ),
			),
			'form_title' => array( 'Get your free paid social audit', 'Dapatkan audit iklan sosial berbayar percuma anda', '获取您的免费社交媒体广告审计', '獲取您的免費社群媒體廣告審計' ),
			'cta'     => array( 'Get my free paid social audit', 'Dapatkan audit iklan sosial percuma saya', '获取我的免费社交媒体广告审计', '獲取我的免費社群媒體廣告審計' ),
			'proof'   => array(
				array(
					array( '+168%', '+168%', '+168%', '+168%' ),
					array( 'GMV up 168% in three months across three Southeast Asian markets, after moving sales to marketplaces.', 'GMV naik 168% dalam tiga bulan merentasi tiga pasaran Asia Tenggara selepas jualan dialihkan ke marketplace.', '将销售转向电商平台后，三个月内东南亚三个市场的 GMV 增长 168%。', '將銷售轉向電商平台後，三個月內東南亞三個市場的 GMV 成長 168%。' ),
					array( 'Premium skincare launch', 'Pelancaran penjagaan kulit premium', '高端护肤品牌上市', '高端護膚品牌上市' ),
				),
				array(
					array( '745k', '745 ribu', '74.5万', '74.5萬' ),
					array( 'Addressable audience grown from 53k to 745k with cookieless personas; campaigns run 20–30% better on CPA.', 'Audiens yang boleh disasarkan berkembang daripada 53 ribu kepada 745 ribu dengan persona tanpa kuki; kempen berjalan 20 – 30% lebih baik pada CPA.', '利用无 Cookie 用户画像，将可触达受众从 5.3 万扩大到 74.5 万；广告活动的 CPA 表现提升 20–30%。', '利用無 Cookie 使用者輪廓，將可觸及受眾從 5.3 萬擴大到 74.5 萬；廣告活動的 CPA 表現提升 20–30%。' ),
					array( 'National sports precinct', 'Kompleks sukan kebangsaan', '国家体育综合体', '國家體育綜合體' ),
				),
				array(
					array( '26.1M', '26.1 juta', '2,610万', '2,610萬' ),
					array( 'Video beat direct YouTube and Meta buys: 26.1M completed views at $0.0048 each.', 'Video mengalahkan pembelian langsung YouTube dan Meta: 26.1 juta tontonan lengkap pada US$0.0048 setiap satu.', '视频投放胜过 YouTube 与 Meta 直接购买：2,610 万次完整播放，每次仅 $0.0048。', '影片投放勝過 YouTube 與 Meta 直接購買：2,610 萬次完整播放，每次僅 $0.0048。' ),
					array( 'Global automotive marque · 7 Asian markets', 'Jenama automotif global · 7 pasaran Asia', '全球汽车品牌 · 7 个亚洲市场', '全球汽車品牌 · 7 個亞洲市場' ),
				),
			),
		),
	);
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
