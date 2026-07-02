<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// 2. Logic Kiểm tra các vấn đề SEO
function wpaap_get_seo_issues()
{
    $issues = array();
    $score = 100;

    // 1. Kiểm tra SEO Plugin
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $is_yoast_active = is_plugin_active('wordpress-seo/wp-seo.php');
    $is_rankmath_active = is_plugin_active('seo-by-rank-math/rank-math.php');

    if ($is_yoast_active || $is_rankmath_active) {
        $seo_plugin_name = $is_yoast_active ? 'Yoast SEO' : 'Rank Math SEO';
        if ($is_yoast_active && $is_rankmath_active) {
            $seo_plugin_name = 'Yoast SEO và Rank Math SEO (Lỗi: Dùng chung 2 plugin SEO)';
            $score -= 20;
            $issues[] = array(
                'level' => 'danger',
                'title' => __('Xung đột Plugin SEO', 'whp') . ' (' . $seo_plugin_name . ')',
                'desc'  => __('Việc sử dụng cả 2 plugin SEO lớn cùng lúc sẽ gây xung đột thẻ meta, làm hỏng cấu trúc website và bị Google phạt. Hãy gỡ cài đặt một trong hai ngay lập tức.', 'whp')
            );
        } else {
            $issues[] = array(
                'level' => 'success',
                'title' => __('Đã cài đặt Plugin hỗ trợ SEO', 'whp') . ' (' . $seo_plugin_name . ')',
                'desc'  => __('Website đang sử dụng plugin SEO tiêu chuẩn. AI sẽ phân tích và đưa ra các gợi ý cấu hình SEO nâng cao phù hợp với plugin này.', 'whp')
            );
        }
    } else {
        $issues[] = array(
            'level' => 'warning',
            'title' => __('Chưa cài đặt Plugin hỗ trợ SEO (Rank Math / Yoast SEO)', 'whp'),
            'desc'  => __('Cấu trúc On-page SEO, Sitemap và Schema có thể chưa đạt chuẩn. Hãy cài đặt Rank Math SEO hoặc Yoast SEO để tối ưu hóa hiển thị trên Google.', 'whp')
        );
        $score -= 20;
    }

    // 2. Kiểm tra Search Engine Visibility
    if (get_option('blog_public') == '0') {
        $issues[] = array(
            'level' => 'danger',
            'title' => __('Đang chặn công cụ tìm kiếm (Search Engine Visibility)', 'whp'),
            'desc'  => __('Tính năng "Ngăn chặn các công cụ tìm kiếm đánh chỉ mục trang web này" đang BẬT ở Cài đặt > Đọc. Google sẽ không thể lập chỉ mục (index) website của bạn. Vui lòng tắt ngay lập tức!', 'whp')
        );
        $score -= 40;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => __('Cho phép công cụ tìm kiếm thu thập dữ liệu', 'whp'),
            'desc'  => __('Website của bạn đang cho phép Googlebot và các công cụ tìm kiếm khác lập chỉ mục.', 'whp')
        );
    }

    // 3. Kiểm tra Permalink Structure
    $permalink_structure = get_option('permalink_structure');
    if (empty($permalink_structure)) {
        $issues[] = array(
            'level' => 'danger',
            'title' => __('Cấu trúc đường dẫn tĩnh (Permalink) chưa chuẩn SEO', 'whp'),
            'desc'  => __('Bạn đang dùng cấu trúc mặc định (VD: ?p=123), điều này không tốt cho SEO. Hãy vào Cài đặt > Đường dẫn tĩnh và chọn cấu trúc "Tên bài viết" (/%postname%/).', 'whp')
        );
        $score -= 20;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => __('Cấu trúc đường dẫn tĩnh chuẩn SEO', 'whp'),
            'desc'  => __('Đường dẫn tĩnh đang sử dụng cấu trúc thân thiện với công cụ tìm kiếm', 'whp') . ' (' . esc_html($permalink_structure) . ').'
        );
    }

    // 4. Kiểm tra Tagline mặc định
    $blog_description = get_option('blogdescription');
    if (strtolower($blog_description) === 'just another wordpress site' || strtolower($blog_description) === 'một trang web mới sử dụng wordpress') {
        $issues[] = array(
            'level' => 'warning',
            'title' => __('Khẩu hiệu (Tagline) đang để mặc định', 'whp'),
            'desc'  => __('Khẩu hiệu đang để là "Just another WordPress site". Điều này làm giảm sự chuyên nghiệp và hiển thị không tốt trên kết quả tìm kiếm. Vào Cài đặt > Tổng quan để thay đổi.', 'whp')
        );
        $score -= 10;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => __('Khẩu hiệu (Tagline) đã được tùy chỉnh', 'whp'),
            'desc'  => __('Bạn đã thay đổi khẩu hiệu mặc định của website, giúp tối ưu thẻ meta description cho trang chủ.', 'whp')
        );
    }

    return array(
        'score' => max(0, $score),
        'items' => $issues
    );
}

// 3. Quét trạng thái SEO của nội dung
function wpaap_get_content_seo_stats($scan_type = 'all', $term_id = '') {
    $stats = array(
        'total' => 0,
        'missing_meta' => 0,
        'missing_keyword' => 0,
        'thin_content' => 0,
        'good' => 0,
        'type_label' => __('tất cả nội dung', 'whp')
    );

    $args = array(
        'post_status' => 'publish',
        'posts_per_page' => 100,
        'orderby' => 'date',
        'order' => 'DESC'
    );

    if ($scan_type === 'pages') {
        $args['post_type'] = 'page';
        $stats['type_label'] = __('trang tĩnh (Pages)', 'whp');
    } elseif ($scan_type === 'post_category' && !empty($term_id)) {
        $args['post_type'] = 'post';
        $args['cat'] = intval($term_id);
        $term = get_term($term_id);
        if ($term && !is_wp_error($term)) {
            $stats['type_label'] = __('chuyên mục', 'whp') . ' "' . $term->name . '"';
        }
    } elseif ($scan_type === 'product_category' && !empty($term_id) && class_exists('WooCommerce')) {
        $args['post_type'] = 'product';
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => intval($term_id),
            ),
        );
        $term = get_term($term_id, 'product_cat');
        if ($term && !is_wp_error($term)) {
            $stats['type_label'] = __('danh mục sản phẩm', 'whp') . ' "' . $term->name . '"';
        }
    } else {
        $args['post_type'] = array('post', 'page');
        if (class_exists('WooCommerce')) {
            $args['post_type'][] = 'product';
        }
    }

    $query = new WP_Query($args);
    $stats['total'] = $query->found_posts > 100 ? 100 : $query->found_posts;

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();
            $content = get_the_content();
            $word_count = str_word_count(wp_strip_all_tags($content));
            
            $has_meta = false;
            $has_keyword = false;

            // Check Yoast
            $yoast_meta = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
            $yoast_kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
            
            // Check RankMath
            $rm_meta = get_post_meta($post_id, 'rank_math_description', true);
            $rm_kw = get_post_meta($post_id, 'rank_math_focus_keyword', true);

            $wpaap_meta = get_post_meta($post_id, '_wpaap_seo_metadesc', true);
            $wpaap_kw   = get_post_meta($post_id, '_wpaap_seo_focus_keyword', true);

            if (!empty($yoast_meta) || !empty($rm_meta) || !empty($wpaap_meta)) {
                $has_meta = true;
            }
            if (!empty($yoast_kw) || !empty($rm_kw) || !empty($wpaap_kw)) {
                $has_keyword = true;
            }

            $is_good = true;

            if (!$has_meta) {
                $stats['missing_meta']++;
                $is_good = false;
            }
            if (!$has_keyword) {
                $stats['missing_keyword']++;
                $is_good = false;
            }
            if ($word_count < 300) {
                $stats['thin_content']++;
                $is_good = false;
            }

            if ($is_good) {
                $stats['good']++;
            }
        }
        wp_reset_postdata();
    }

    return $stats;
}

// Lấy bài viết cần tối ưu SEO gấp (hỗ trợ phân trang)
function wpaap_get_urgent_seo_posts($post_type = 'post', $limit = 5, $offset = 0) {
    $args = array(
        'post_type'      => $post_type,
        'post_status'    => 'publish',
        'posts_per_page' => 100,
        'orderby'        => 'modified',
        'order'          => 'DESC',
    );

    if (class_exists('WooCommerce')) {
        $exclude_ids = array();
        if (function_exists('wc_get_page_id')) {
            $exclude_ids[] = wc_get_page_id('cart');
            $exclude_ids[] = wc_get_page_id('checkout');
            $exclude_ids[] = wc_get_page_id('myaccount');
            $exclude_ids[] = wc_get_page_id('shop');
        }
        $args['post__not_in'] = array_filter($exclude_ids);
    }

    $query = new WP_Query($args);
    $urgent_posts = array();
    $skipped = 0;

    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $has_meta    = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true)  || get_post_meta($post->ID, 'rank_math_description', true)     || get_post_meta($post->ID, '_wpaap_seo_metadesc', true);
            $has_keyword = get_post_meta($post->ID, '_yoast_wpseo_focuskw', true)   || get_post_meta($post->ID, 'rank_math_focus_keyword', true)   || get_post_meta($post->ID, '_wpaap_seo_focus_keyword', true);
            $word_count  = str_word_count(strip_tags($post->post_content));
            $is_thin     = $word_count < 300;

            $issues = array();
            if (!$has_meta)                                          $issues[] = __('Thiếu Meta', 'whp');
            if (!$has_keyword && $post->post_type !== 'page')        $issues[] = __('Thiếu Keyword', 'whp');
            if ($is_thin)                                            $issues[] = __('Quá ngắn', 'whp');

            if (!$has_meta || (!$has_keyword && $post->post_type !== 'page') || $is_thin) {
                if ($skipped < $offset) { $skipped++; continue; }

                $category_name = __('Chưa phân loại', 'whp');
                if ($post->post_type === 'post') {
                    $cats = get_the_category($post->ID);
                    if (!empty($cats)) $category_name = $cats[0]->name;
                } elseif ($post->post_type === 'product') {
                    $terms = get_the_terms($post->ID, 'product_cat');
                    if (!empty($terms) && !is_wp_error($terms)) $category_name = $terms[0]->name;
                } elseif ($post->post_type === 'page') {
                    $category_name = __('Trang (Page)', 'whp');
                }

                $urgent_posts[] = array(
                    'id'        => $post->ID,
                    'title'     => get_the_title($post->ID),
                    'edit_link' => get_edit_post_link($post->ID),
                    'view_link' => get_permalink($post->ID),
                    'category'  => $category_name,
                    'issues'    => $issues,
                );

                if (count($urgent_posts) >= $limit) break;
            }
        }
    }

    return $urgent_posts;
}

// Render HTML một row bài viết cần tối ưu (dùng cả template lẫn AJAX load-more)
function wpaap_render_urgent_post_row($upost, $seo_ai_ok = false) {
    ob_start(); ?>
    <div class="wpaap-urgent-row" style="padding:10px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px;transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
        <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px;"><?php echo esc_html($upost['title']); ?></div>
            <div style="display:flex;gap:4px;flex-wrap:wrap;align-items:center;">
                <?php foreach ($upost['issues'] as $is): ?>
                    <span style="font-size:10.5px;background:#fee2e2;color:#dc2626;padding:1px 7px;border-radius:10px;font-weight:600;"><?php echo esc_html($is); ?></span>
                <?php endforeach; ?>
                <span style="font-size:10.5px;background:#f1f5f9;color:#64748b;padding:1px 7px;border-radius:10px;font-weight:500;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo esc_attr($upost['category']); ?>"><?php echo esc_html($upost['category']); ?></span>
            </div>
        </div>
        <!-- Grouped action buttons -->
        <div style="display:inline-flex;align-items:stretch;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;flex-shrink:0;background:#fff;">
            <a href="<?php echo esc_url($upost['view_link']); ?>" target="_blank" title="<?php esc_attr_e('Xem bài viết', 'whp'); ?>" style="display:inline-flex;align-items:center;gap:4px;padding:6px 11px;font-size:12px;font-weight:600;color:#475569;text-decoration:none;border-right:1px solid #e2e8f0;background:#fff;transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span><?php esc_html_e('Xem', 'whp'); ?></span>
            </a>
            <a href="<?php echo esc_url($upost['edit_link']); ?>" target="_blank" title="<?php esc_attr_e('Chỉnh sửa bài viết', 'whp'); ?>" style="display:inline-flex;align-items:center;gap:4px;padding:6px 11px;font-size:12px;font-weight:600;color:#2563eb;text-decoration:none;<?php echo $seo_ai_ok ? 'border-right:1px solid #e2e8f0;' : ''; ?>background:#fff;transition:background 0.15s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background='#fff'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>
                <span><?php esc_html_e('Sửa', 'whp'); ?></span>
            </a>
            <?php if ($seo_ai_ok): ?>
            <button class="wpaap-ai-scan-post-btn"
                data-post-id="<?php echo esc_attr($upost['id']); ?>"
                data-post-title="<?php echo esc_attr($upost['title']); ?>"
                title="<?php esc_attr_e('AI phân tích SEO bài viết này', 'whp'); ?>"
                style="display:inline-flex;align-items:center;gap:4px;padding:6px 11px;font-size:12px;font-weight:700;color:#16a34a;background:#f0fdf4;border:none;cursor:pointer;transition:background 0.15s;white-space:nowrap;" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><polyline points="8 11 11 8 14 11"/><line x1="11" y1="8" x2="11" y2="14"/></svg>
                <span><?php esc_html_e('AI Quét', 'whp'); ?></span>
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// 4. Giao diện trang SEO (SEO Page Layout)
function wpaap_seo_page_layout()
{
    $seo_ai_ok = false;
    if ( function_exists( 'wpaap_is_provider_connected' ) ) {
        foreach ( [ 'google', 'anthropic', 'openai' ] as $_p ) {
            if ( wpaap_is_provider_connected( $_p ) ) { $seo_ai_ok = true; break; }
        }
    } else {
        $seo_ai_ok = get_option( 'wpaap_core_connected', 'no' ) === 'yes';
    }
    $is_connected = $seo_ai_ok;
    $has_core_api = function_exists( 'wp_ai_client_prompt' ) || function_exists( 'wp_ai_generate_text' );

    $seo_data = wpaap_get_seo_issues();
    $content_stats = wpaap_get_content_seo_stats();
    $score = $seo_data['score'];
    $issues = $seo_data['items'];



    // Tính toán màu sắc dựa trên điểm số
    $score_color = '#10b981'; $score_bg_light = '#f0fdf4'; $score_border_light = '#bbf7d0';
    if ($score < 60) {
        $score_color = '#ef4444'; $score_bg_light = '#fff5f5'; $score_border_light = '#fecaca';
    } elseif ($score < 80) {
        $score_color = '#f59e0b'; $score_bg_light = '#fffbeb'; $score_border_light = '#fde68a';
    }
?>
    <style>
    .wpaap-seo-header {
        position: relative;
        background: linear-gradient(100deg, #ffffff 0%, #f0fdf4 45%, #dcfce7 100%);
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(22,163,74,0.1), 0 0 0 1px #bbf7d0;
        margin-bottom: 25px;
        overflow: hidden;
        min-height: 168px;
        display: flex;
        align-items: stretch;
    }
    .wpaap-seo-header-left {
        position: relative; z-index: 2;
        padding: 32px 36px;
        display: flex; flex-direction: column; justify-content: center; gap: 14px;
        max-width: 500px; flex-shrink: 0;
    }
    .wpaap-seo-header-title-row { display: flex; align-items: center; gap: 14px; }
    .wpaap-seo-header-icon-box {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #16a34a, #4ade80);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; box-shadow: 0 4px 12px rgba(22,163,74,0.3);
    }
    .wpaap-seo-header-right {
        position: absolute; inset: 0 0 0 38%;
        overflow: hidden; pointer-events: none;
    }
    </style>
    <div class="wrap wpaap-wrap" style="margin: 20px auto 40px; padding: 0 20px;">

        <div class="wpaap-seo-header">
            <div class="wpaap-seo-header-left">
                <div class="wpaap-seo-header-title-row">
                    <div class="wpaap-seo-header-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><polyline points="8 11 11 8 14 11"/><line x1="11" y1="8" x2="11" y2="14"/></svg>
                    </div>
                    <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Gợi ý SEO Website (SEO Advisor)', 'whp'); ?></h1>
                </div>
                <p style="margin:0;font-size:13.5px;color:#64748b;line-height:1.6;padding-left:58px;max-width:400px;"><?php esc_html_e('Phân tích cấu trúc On-page SEO, sitemap, thẻ meta và tối ưu nội dung website bằng trí tuệ nhân tạo.', 'whp'); ?></p>
                <div style="padding-left:58px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <button id="wpaap_ai_scan_seo_btn" class="wpaap-btn-success-gradient" data-ai-ok="<?php echo $seo_ai_ok ? '1' : '0'; ?>">
                        <span class="dashicons dashicons-superhero" style="font-size:15px;width:15px;height:15px;line-height:15px;vertical-align:middle;margin-right:5px;"></span> <?php esc_html_e('AI Phân Tích SEO', 'whp'); ?>
                    </button>
                </div>
                <div id="wpaap_seo_ai_notice" style="display:none;margin:10px 0 0 0;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;"></div>
            </div>
            <div class="wpaap-seo-header-right">
                <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                    <defs>
                        <linearGradient id="seo_grad1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#16a34a" stop-opacity="0.15"/><stop offset="100%" stop-color="#4ade80" stop-opacity="0.07"/></linearGradient>
                        <linearGradient id="seo_grad2" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#16a34a" stop-opacity="0.25"/><stop offset="100%" stop-color="#4ade80" stop-opacity="0.12"/></linearGradient>
                        <linearGradient id="seo_trend" x1="0" y1="1" x2="1" y2="0"><stop offset="0%" stop-color="#16a34a" stop-opacity="0.3"/><stop offset="100%" stop-color="#4ade80" stop-opacity="0.7"/></linearGradient>
                    </defs>
                    <!-- Search result card 1 -->
                    <rect x="310" y="20" width="130" height="38" rx="7" fill="url(#seo_grad2)" stroke="#bbf7d0" stroke-width="1.5"/>
                    <rect x="322" y="30" width="70" height="5" rx="2.5" fill="#16a34a" fill-opacity="0.4"/>
                    <rect x="322" y="40" width="50" height="4" rx="2" fill="#4ade80" fill-opacity="0.4"/>
                    <rect x="322" y="48" width="88" height="3" rx="1.5" fill="#16a34a" fill-opacity="0.2"/>
                    <!-- Search result card 2 -->
                    <rect x="310" y="66" width="130" height="38" rx="7" fill="url(#seo_grad2)" stroke="#bbf7d0" stroke-width="1.5"/>
                    <rect x="322" y="76" width="60" height="5" rx="2.5" fill="#16a34a" fill-opacity="0.4"/>
                    <rect x="322" y="86" width="45" height="4" rx="2" fill="#4ade80" fill-opacity="0.4"/>
                    <rect x="322" y="94" width="80" height="3" rx="1.5" fill="#16a34a" fill-opacity="0.2"/>
                    <!-- Search result card 3 -->
                    <rect x="310" y="112" width="130" height="38" rx="7" fill="url(#seo_grad2)" stroke="#bbf7d0" stroke-width="1.5"/>
                    <rect x="322" y="122" width="55" height="5" rx="2.5" fill="#16a34a" fill-opacity="0.4"/>
                    <rect x="322" y="132" width="40" height="4" rx="2" fill="#4ade80" fill-opacity="0.4"/>
                    <rect x="322" y="140" width="75" height="3" rx="1.5" fill="#16a34a" fill-opacity="0.2"/>
                    <!-- Trend chart going up -->
                    <polyline points="470,130 490,110 510,100 530,78 550,55" stroke="url(#seo_trend)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    <circle cx="550" cy="55" r="5" fill="#16a34a" fill-opacity="0.6"/>
                    <polyline points="540,50 550,55 545,64" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" stroke-opacity="0.6"/>
                    <!-- Magnifier -->
                    <circle cx="590" cy="90" r="24" fill="url(#seo_grad1)" stroke="#bbf7d0" stroke-width="1.5"/>
                    <circle cx="588" cy="88" r="12" fill="none" stroke="#16a34a" stroke-width="2" stroke-opacity="0.5"/>
                    <line x1="596" y1="98" x2="608" y2="110" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-opacity="0.5"/>
                    <!-- Keyword tags -->
                    <rect x="460" y="140" width="40" height="16" rx="5" fill="url(#seo_grad2)" stroke="#bbf7d0" stroke-width="1"/>
                    <rect x="506" y="140" width="30" height="16" rx="5" fill="url(#seo_grad2)" stroke="#bbf7d0" stroke-width="1"/>
                    <!-- Sitemap dots -->
                    <circle cx="640" cy="40" r="5" fill="#16a34a" fill-opacity="0.25"/>
                    <circle cx="655" cy="60" r="4" fill="#4ade80" fill-opacity="0.3"/>
                    <line x1="640" y1="40" x2="655" y2="60" stroke="#bbf7d0" stroke-width="1" stroke-opacity="0.5"/>
                    <circle cx="645" cy="130" r="6" fill="#16a34a" fill-opacity="0.15"/>
                </svg>
            </div>
        </div>
        <hr class="wp-header-end">
        <?php if ( ! $is_connected ) : ?>
            <div class="wpaap-card-modern" style="border-top: 4px solid #d63638; padding: 24px; text-align: center;">
                <span class="dashicons dashicons-warning" style="font-size: 48px; width: 48px; height: 48px; color: #d63638; margin-bottom: 15px;"></span>
                <h2 style="margin: 0 0 10px 0; color: #d63638;"><?php esc_html_e('Chưa kết nối AI', 'whp'); ?></h2>
                <p style="font-size: 14px; color: #646970; max-width: 500px; margin: 0 auto 20px auto; line-height: 1.5;">
                    <?php esc_html_e('Bạn cần cấu hình mã khóa API trong phần Kết nối AI trước khi sử dụng tính năng này.', 'whp'); ?>
                </p>
                <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=connection'); ?>" class="wpaap-btn-primary-gradient" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px;">
                    <span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e('Sang trang Kết nối AI', 'whp'); ?>
                </a>
            </div>
        <?php else : ?>
        <!-- Khung Loading AI Quét -->
        <div id="wpaap_ai_scan_loading" class="wpaap-loading-card" style="display: none; margin-bottom: 25px;">
            <div class="wpaap-loading-title">
                <span class="dashicons dashicons-admin-generic wpaap-spinning" style="font-size: 16px; width: 16px; height: 16px; margin-right: 4px; vertical-align: middle;"></span> <?php esc_html_e('AI đang tiến hành phân tích sâu hệ thống...', 'whp'); ?> <span id="wpaap_ai_loading_percent">0%</span>
            </div>
            <div class="wpaap-loading-track">
                <div class="wpaap-loading-fill"></div>
            </div>
        </div>

        <div id="wpaap_ai_advice_container" style="display: none; margin-top: 0; margin-bottom: 25px; background: #fff; border: 1px solid #e2e4e7; border-top: 4px solid #16a34a; padding: 0; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="background: #f0fdf4; padding: 16px 22px; border-bottom: 1px solid #e2e4e7; display: flex; align-items: center; gap: 10px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><polyline points="8 11 11 8 14 11"/><line x1="11" y1="8" x2="11" y2="14"/></svg>
                <h3 style="margin: 0; color: #1d2327; font-size: 18px; font-weight: 700; flex: 1;"><?php esc_html_e('Báo Cáo Phân Tích & Giải Pháp Từ AI', 'whp'); ?></h3>
                <button id="wpaap_seo_report_collapse" style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;padding:4px 12px;font-size:12px;font-weight:600;color:#16a34a;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
                    <?php esc_html_e('Thu gọn tất cả', 'whp'); ?>
                </button>
            </div>

            <div id="wpaap_ai_advice_content" style="padding: 30px 25px; font-size: 15px; color: #3c434a; line-height: 1.6;">
                <!-- Nội dung AI sẽ được đổ vào đây -->
            </div>
        </div>


        <style>
            /* CSS Tùy chỉnh cho nội dung AI trả về để nhìn hiện đại như SaaS */
            #wpaap_ai_advice_content h4 {
                font-size: 15px;
                color: #1d2327;
                margin: 25px 0 10px 0;
                padding: 10px 14px;
                border-bottom: none;
                border-radius: 6px 6px 0 0;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 8px;
                background: #fef2f2;
                border: 1px solid #fecaca;
                cursor: default;
            }
            #wpaap_ai_advice_content .ai-issue-box.warning h4 {
                background: #fffbeb; border-color: #fde68a;
            }
            #wpaap_ai_advice_content .ai-issue-box.success h4 {
                background: #f0fdf4; border-color: #bbf7d0;
            }
            #wpaap_ai_advice_content .ai-issue-box.info h4 {
                background: #eff6ff; border-color: #bfdbfe;
            }
            #wpaap_ai_advice_content .wpaap-issue-body {
                padding-top: 14px;
                overflow: hidden;
                transition: max-height 0.3s ease, opacity 0.3s ease;
            }
            #wpaap_ai_advice_content .wpaap-issue-body.collapsed { max-height: 0 !important; opacity: 0; padding-top: 0; }
            .wpaap-issue-collapse-btn {
                flex-shrink: 0; margin-left: auto;
                background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px;
                padding: 3px 10px; font-size: 11.5px; font-weight: 600; color: #6b7280;
                cursor: pointer; display: inline-flex; align-items: center; gap: 4px;
                transition: all 0.15s ease;
            }
            .wpaap-issue-collapse-btn:hover { background: #e5e7eb; color: #374151; }

            #wpaap_ai_advice_content h4:first-child {
                margin-top: 0;
            }

            #wpaap_ai_advice_content p {
                margin: 0 0 15px 0;
            }

            #wpaap_ai_advice_content .ai-issue-box {
                background: #fff;
                border: 1px solid #e2e4e7;
                border-left: 4px solid #d63638;
                border-radius: 6px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            }

            #wpaap_ai_advice_content .ai-issue-box.warning {
                border-left-color: #f0b849;
            }

            #wpaap_ai_advice_content .ai-issue-box.success {
                border-left-color: #00a32a;
            }

            #wpaap_ai_advice_content .ai-issue-box.info {
                border-left-color: #2271b1;
            }

            #wpaap_ai_advice_content .ai-fix-steps {
                background: #f0f6fc;
                border-radius: 8px;
                border: 1px dashed #b5d1ed;
                padding: 18px 20px;
                margin-top: 15px;
            }

            #wpaap_ai_advice_content .ai-fix-steps strong {
                color: #0a4b78;
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 12px;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 700;
            }
            
            #wpaap_ai_advice_content .ai-fix-steps strong::before {
                content: "\f463";
                font-family: "dashicons";
                font-size: 18px;
                font-weight: normal;
                color: #2271b1;
            }

            #wpaap_ai_advice_content ul {
                margin: 0;
                padding-left: 20px;
            }

            #wpaap_ai_advice_content li {
                margin-bottom: 8px;
            }

            #wpaap_ai_advice_content .ai-fix-steps ul {
                padding-left: 0;
                list-style: none;
            }

            #wpaap_ai_advice_content .ai-fix-steps li {
                position: relative;
                line-height: 1.6;
                color: #2c3338;
                background: #fff;
                padding: 12px 15px 12px 38px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 2px rgba(0,0,0,0.02);
                margin-bottom: 10px;
            }
            
            #wpaap_ai_advice_content .ai-fix-steps li::before {
                content: "\f345";
                font-family: "dashicons";
                position: absolute;
                left: 10px;
                top: 13px;
                color: #2271b1;
                font-size: 18px;
            }
            
            #wpaap_ai_advice_content .ai-fix-steps p {
                background: #fff;
                padding: 12px 15px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 2px rgba(0,0,0,0.02);
                margin-bottom: 10px;
            }
        </style>

        <style>
            .wpaap-modern-box {
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.04);
                border: 1px solid #eaeaea;
                overflow: hidden;
            }
            .wpaap-issue-card {
                padding: 20px;
                margin: 0 20px 15px 20px;
                border-radius: 10px;
                display: flex;
                gap: 15px;
                transition: all 0.2s ease;
                border: 1px solid #eaeaea;
            }
            .wpaap-issue-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 15px rgba(0,0,0,0.05);
            }
            .wpaap-icon-wrap {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
        </style>

        <?php
        /* =====================================================================
         * COMPUTED VARIABLES — dùng cho layout mới
         * ===================================================================== */

        // Score label
        if ($score >= 85)     { $score_label = __('Xuất sắc', 'whp'); $score_label_color = '#16a34a'; }
        elseif ($score >= 60) { $score_label = __('Khá', 'whp');      $score_label_color = '#d97706'; }
        else                  { $score_label = __('Yếu', 'whp');      $score_label_color = '#ef4444'; }

        // Passed / warning counts
        $passed_c  = 0;
        $warning_c = 0;
        foreach ($issues as $issue) {
            if ($issue['level'] === 'success') $passed_c++;
            else $warning_c++;
        }

        // Gauge SVG values (270° arc)
        $gauge_r     = 68;
        $gauge_cx    = 100;
        $gauge_cy    = 95;
        $gauge_circ  = 2 * M_PI * $gauge_r;
        $gauge_track = $gauge_circ * 0.75;
        $gauge_gap   = $gauge_circ * 0.25;
        $gauge_fill  = $gauge_track * ($score / 100);

        // Sub-score bars — plugin detection
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $_seo_yoast    = is_plugin_active('wordpress-seo/wp-seo.php');
        $_seo_rankmath = is_plugin_active('seo-by-rank-math/rank-math.php');

        $sub_onpage    = 100;
        $sub_technical = 100;
        foreach ($issues as $idx => $iss) {
            if ($iss['level'] !== 'success') {
                if ($idx == 0) $sub_onpage    = max(0, $sub_onpage - 25);
                if ($idx == 1) $sub_technical = max(0, $sub_technical - 45);
                if ($idx == 2) $sub_technical = max(0, $sub_technical - 20);
                if ($idx == 3) $sub_onpage    = max(0, $sub_onpage - 10);
            }
        }
        $sub_content  = 75;
        $sub_internal = 60;
        $sub_schema   = 70;
        if ($content_stats['total'] > 0) {
            $meta_rate    = 1 - ($content_stats['missing_meta']    / $content_stats['total']);
            $kw_rate      = 1 - ($content_stats['missing_keyword'] / $content_stats['total']);
            $thin_rate    = 1 - ($content_stats['thin_content']    / $content_stats['total']);
            $sub_content  = max(30, (int)round($meta_rate * 60 + $kw_rate * 40));
            $sub_internal = max(20, (int)round($thin_rate * 80));
        }
        $sub_schema = ($_seo_yoast || $_seo_rankmath) ? 90 : 45;

        $sub_bars = [
            ['label' => 'On-page SEO',          'score' => $sub_onpage,    'color' => '#16a34a'],
            ['label' => 'Technical SEO',         'score' => $sub_technical, 'color' => '#f59e0b'],
            ['label' => 'Content Quality',       'score' => $sub_content,   'color' => '#0ea5e9'],
            ['label' => 'Internal Link',         'score' => $sub_internal,  'color' => '#ef4444'],
            ['label' => 'Schema & Rich Snippet', 'score' => $sub_schema,    'color' => '#8b5cf6'],
        ];

        // Website status items
        $site_google   = get_option('blog_public') != '0';
        $site_sitemap  = $_seo_yoast || $_seo_rankmath || file_exists(ABSPATH.'sitemap.xml') || file_exists(ABSPATH.'sitemap_index.xml');
        $site_robots   = file_exists(ABSPATH.'robots.txt') || $_seo_yoast || $_seo_rankmath;
        $site_schema   = $_seo_yoast || $_seo_rankmath;
        $site_internal = $content_stats['total'] > 0 && ($content_stats['total'] - $content_stats['thin_content']) >= $content_stats['total'] * 0.5;

        $site_items = [
            ['label'=>'Google Index',  'ok'=>$site_google,   'ok_text'=>__('Đã index', 'whp'),    'fail_text'=>__('Đang bị chặn', 'whp')],
            ['label'=>'Sitemap.xml',   'ok'=>$site_sitemap,  'ok_text'=>__('Hợp lệ', 'whp'),      'fail_text'=>__('Chưa có', 'whp')],
            ['label'=>'Robots.txt',    'ok'=>$site_robots,   'ok_text'=>__('Hợp lệ', 'whp'),      'fail_text'=>__('Chưa có', 'whp')],
            ['label'=>'Schema Markup', 'ok'=>$site_schema,   'ok_text'=>__('Đã cài đặt', 'whp'),  'fail_text'=>__('Thiếu', 'whp')],
            ['label'=>'Internal Link', 'ok'=>$site_internal, 'ok_text'=>__('Ổn', 'whp'),          'fail_text'=>__('Cần cải thiện', 'whp')],
        ];

        // Opportunities
        $opps = [];
        if ($content_stats['missing_meta'] > 0)    $opps[] = ['label'=>__('Tối ưu Meta Description', 'whp'), 'pts'=>5, 'color'=>'#16a34a'];
        if ($content_stats['missing_keyword'] > 0) $opps[] = ['label'=>__('Tối ưu Focus Keyword', 'whp'),    'pts'=>4, 'color'=>'#2563eb'];
        if (!$site_schema)                          $opps[] = ['label'=>__('Bổ sung Schema Markup', 'whp'),   'pts'=>6, 'color'=>'#7c3aed'];
        if ($content_stats['thin_content'] > 0)    $opps[] = ['label'=>__('Tối ưu Internal Link', 'whp'),    'pts'=>3, 'color'=>'#d97706'];
        $opp_total = array_sum(array_column($opps, 'pts'));

        // Last scan info
        $last_scan_date   = get_option('wpaap_seo_last_scan_date', '--');
        $last_scan_posts  = get_option('wpaap_seo_last_scan_posts', $content_stats['total']);
        $last_scan_errors = get_option('wpaap_seo_last_scan_errors', $content_stats['missing_meta'] + $content_stats['missing_keyword']);
        ?>

        <style>
        .wpaap-seo-section-title { font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.8px;display:flex;align-items:center;gap:7px;margin:0 0 14px 0;padding-bottom:10px;border-bottom:1px solid #f1f5f9; }
        .wpaap-seo-progress-row { display:flex;align-items:center;gap:10px;margin-bottom:10px;font-size:12.5px; }
        .wpaap-seo-status-row { display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f8fafc;font-size:13px; }
        .wpaap-seo-status-row:last-child { border-bottom:none; }
        .wpaap-seo-opp-row { display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f8fafc;font-size:12.5px; }
        .wpaap-seo-opp-row:last-child { border-bottom:none; }
        .wpaap-seo-issue-row { display:flex;align-items:flex-start;gap:12px;padding:14px 20px;border-bottom:1px solid #f8fafc;transition:background 0.15s; }
        .wpaap-seo-issue-row:hover { background:#fafafa; }
        .wpaap-seo-issue-row:last-child { border-bottom:none; }
        .wpaap-feature-card { background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;display:flex;flex-direction:column;gap:10px;transition:box-shadow 0.2s,transform 0.2s; }
        .wpaap-feature-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.07);transform:translateY(-2px); }
        .wpaap-tab-btn { padding:10px 16px;border:none;background:transparent;font-size:13px;font-weight:600;color:#94a3b8;cursor:pointer;border-bottom:3px solid transparent;transition:all 0.2s; }
        .wpaap-tab-btn.active { color:#16a34a;border-bottom-color:#16a34a; }
        .wpaap-tab-btn:hover:not(.active) { color:#475569; }
        .wpaap-tab-content { display:none; }
        .wpaap-tab-content.active { display:block; }
        .wp-core-ui select.wpaap-header-select,
        .wpaap-header-select {
            padding: 7px 32px 7px 12px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            color: #1e293b !important;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.5' stroke-linecap='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            outline: none !important;
            cursor: pointer !important;
            transition: border-color 0.2s, box-shadow 0.2s, background-color 0.2s !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04) !important;
            min-height: unset !important;
            max-width: unset !important;
            line-height: normal !important;
            vertical-align: unset !important;
        }
        .wp-core-ui select.wpaap-header-select:focus,
        .wpaap-header-select:focus {
            border-color: #16a34a !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 3px rgba(22,163,74,0.12) !important;
        }
        .wp-core-ui select.wpaap-header-select:hover,
        .wpaap-header-select:hover {
            border-color: #9ca3af !important;
            background-color: #fff !important;
            color: #1e293b !important;
        }
        </style>

        <!-- ================================================================
             MAIN 2-COLUMN LAYOUT
             ================================================================ -->
        <div style="display:flex;gap:20px;margin-top:20px;align-items:flex-start;">

            <!-- ============================================================
                 LEFT COLUMN — 310px fixed
                 ============================================================ -->
            <div style="width:310px;flex-shrink:0;display:flex;flex-direction:column;gap:16px;">

                <!-- Card 1: Điểm SEO Tổng Quan -->
                <div style="background:linear-gradient(160deg,#ffffff 0%,<?php echo esc_attr($score_bg_light); ?> 100%);border-radius:12px;border:1px solid <?php echo esc_attr($score_border_light); ?>;border-top:4px solid <?php echo esc_attr($score_color); ?>;padding:22px 20px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <div class="wpaap-seo-section-title">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                        <?php esc_html_e('ĐIỂM SEO TỔNG QUAN', 'whp'); ?>
                    </div>

                    <!-- Gauge SVG -->
                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <svg viewBox="0 0 200 165" width="200" height="165" style="overflow:visible;">
                            <!-- Track -->
                            <circle
                                cx="<?php echo $gauge_cx; ?>"
                                cy="<?php echo $gauge_cy; ?>"
                                r="<?php echo $gauge_r; ?>"
                                fill="none"
                                stroke="#e2e8f0"
                                stroke-width="11"
                                stroke-linecap="round"
                                stroke-dasharray="<?php echo round($gauge_track, 2); ?> <?php echo round($gauge_gap, 2); ?>"
                                transform="rotate(135 <?php echo $gauge_cx; ?> <?php echo $gauge_cy; ?>)"
                            />
                            <!-- Fill -->
                            <circle
                                cx="<?php echo $gauge_cx; ?>"
                                cy="<?php echo $gauge_cy; ?>"
                                r="<?php echo $gauge_r; ?>"
                                fill="none"
                                stroke="<?php echo esc_attr($score_color); ?>"
                                stroke-width="11"
                                stroke-linecap="round"
                                stroke-dasharray="<?php echo round($gauge_fill, 2); ?> 10000"
                                transform="rotate(135 <?php echo $gauge_cx; ?> <?php echo $gauge_cy; ?>)"
                            />
                            <!-- Score number -->
                            <text x="<?php echo $gauge_cx; ?>" y="<?php echo $gauge_cy + 16; ?>" text-anchor="middle" font-size="46" font-weight="800" fill="<?php echo esc_attr($score_color); ?>" font-family="-apple-system,sans-serif"><?php echo esc_html($score); ?></text>
                            <!-- /100 -->
                            <text x="<?php echo $gauge_cx; ?>" y="<?php echo $gauge_cy + 34; ?>" text-anchor="middle" font-size="14" fill="#94a3b8" font-family="-apple-system,sans-serif">/100</text>
                        </svg>

                        <!-- Level label -->
                        <div style="margin-top:-20px;font-size:18px;font-weight:800;color:<?php echo esc_attr($score_label_color); ?>;"><?php echo esc_html($score_label); ?></div>

                        <!-- Trend badge -->
                        <div style="margin-top:10px;background:#dcfce7;color:#16a34a;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;display:flex;align-items:center;gap:4px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                            +<?php echo $passed_c; ?> <?php esc_html_e('điểm so với tuần trước', 'whp'); ?>
                        </div>

                        <!-- Stats row -->
                        <div style="display:flex;align-items:center;gap:0;margin-top:16px;width:100%;border-top:1px solid #f1f5f9;padding-top:14px;">
                            <div style="flex:1;text-align:center;">
                                <div style="font-size:22px;font-weight:800;color:#16a34a;"><?php echo $passed_c; ?></div>
                                <div style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-top:2px;"><?php esc_html_e('AN TOÀN', 'whp'); ?></div>
                            </div>
                            <div style="width:1px;height:36px;background:#e2e8f0;"></div>
                            <div style="flex:1;text-align:center;">
                                <div style="font-size:22px;font-weight:800;color:#f59e0b;"><?php echo $warning_c; ?></div>
                                <div style="font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-top:2px;"><?php esc_html_e('CẢNH BÁO', 'whp'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Phân Bố Điểm SEO -->
                <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <div class="wpaap-seo-section-title">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        <?php esc_html_e('PHÂN BỐ ĐIỂM SEO', 'whp'); ?>
                    </div>
                    <?php foreach ($sub_bars as $bar) : ?>
                    <div class="wpaap-seo-progress-row">
                        <span style="flex:1;color:#475569;font-weight:500;"><?php echo esc_html($bar['label']); ?></span>
                        <div style="flex:2;background:#f1f5f9;border-radius:6px;height:6px;overflow:hidden;">
                            <div style="width:<?php echo $bar['score']; ?>%;height:100%;background:<?php echo esc_attr($bar['color']); ?>;border-radius:6px;transition:width 0.4s;"></div>
                        </div>
                        <span style="width:35px;text-align:right;font-weight:700;color:<?php echo esc_attr($bar['color']); ?>;font-size:12px;"><?php echo $bar['score']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Card 3: Tình Trạng Website -->
                <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <div class="wpaap-seo-section-title">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <?php esc_html_e('TÌNH TRẠNG WEBSITE', 'whp'); ?>
                    </div>
                    <?php foreach ($site_items as $si) : ?>
                    <div class="wpaap-seo-status-row">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <?php if ($si['ok']) : ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#dcfce7"/><polyline points="9 12 11 14 15 10" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <?php else : ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" fill="#fef3c7" stroke="#f59e0b" stroke-width="1.5"/><line x1="12" y1="9" x2="12" y2="13" stroke="#f59e0b" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="17" r="1" fill="#f59e0b"/></svg>
                            <?php endif; ?>
                            <span style="color:#374151;font-weight:500;"><?php echo esc_html($si['label']); ?></span>
                        </div>
                        <span style="font-size:12px;font-weight:600;color:<?php echo $si['ok'] ? '#16a34a' : '#d97706'; ?>;"><?php echo esc_html($si['ok'] ? $si['ok_text'] : $si['fail_text']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Card 4: Cơ Hội Tăng Điểm -->
                <?php if (!empty($opps)) : ?>
                <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <div class="wpaap-seo-section-title">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        <?php esc_html_e('CƠ HỘI TĂNG ĐIỂM SEO', 'whp'); ?>
                    </div>
                    <div style="font-size:12.5px;color:#475569;margin-bottom:12px;">
                        <?php esc_html_e('Bạn có thể tăng tối đa', 'whp'); ?> <strong style="color:<?php echo esc_attr($score_color); ?>;">+<?php echo $opp_total; ?> <?php esc_html_e('điểm', 'whp'); ?></strong>
                    </div>
                    <?php foreach ($opps as $opp) : ?>
                    <div class="wpaap-seo-opp-row">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="<?php echo esc_attr($opp['color']); ?>" fill-opacity="0.12"/><polyline points="9 12 11 14 15 10" stroke="<?php echo esc_attr($opp['color']); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span style="flex:1;color:#374151;font-weight:500;"><?php echo esc_html($opp['label']); ?></span>
                        <span style="background:<?php echo esc_attr($opp['color']); ?>1a;color:<?php echo esc_attr($opp['color']); ?>;padding:2px 9px;border-radius:12px;font-size:11.5px;font-weight:700;">+<?php echo $opp['pts']; ?> <?php esc_html_e('điểm','whp'); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Card 5: Quét SEO Gần Nhất -->
                <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                    <div class="wpaap-seo-section-title">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?php esc_html_e('QUÉT SEO GẦN NHẤT', 'whp'); ?>
                    </div>
                    <div class="wpaap-seo-status-row">
                        <span style="color:#64748b;"><?php esc_html_e('Lần quét', 'whp'); ?></span>
                        <span style="font-weight:600;color:#1e293b;font-size:12.5px;"><?php echo esc_html($last_scan_date); ?></span>
                    </div>
                    <div class="wpaap-seo-status-row">
                        <span style="color:#64748b;"><?php esc_html_e('Bài viết đã quét', 'whp'); ?></span>
                        <span style="font-weight:600;color:#1e293b;"><?php echo esc_html($last_scan_posts); ?></span>
                    </div>
                    <div class="wpaap-seo-status-row">
                        <span style="color:#64748b;"><?php esc_html_e('Lỗi phát hiện', 'whp'); ?></span>
                        <span style="font-weight:600;color:#ef4444;"><?php echo esc_html($last_scan_errors); ?></span>
                    </div>
                    <div class="wpaap-seo-status-row">
                        <span style="color:#64748b;"><?php esc_html_e('Trạng thái', 'whp'); ?></span>
                        <span style="display:flex;align-items:center;gap:5px;font-size:12.5px;font-weight:600;color:#16a34a;">
                            <span style="width:7px;height:7px;border-radius:50%;background:#16a34a;display:inline-block;"></span><?php esc_html_e('Hoàn tất', 'whp'); ?>
                        </span>
                    </div>
                    <div style="margin-top:14px;display:flex;flex-direction:column;gap:8px;">
                        <button id="wpaap_ai_scan_seo_btn_2" onclick="jQuery('html,body').animate({scrollTop:0},300);setTimeout(function(){jQuery('#wpaap_ai_scan_seo_btn').trigger('click');},320);return false;" style="width:100%;padding:9px 0;background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:background 0.2s;">
                            <?php esc_html_e('Quét lại ngay', 'whp'); ?>
                        </button>
                    </div>
                </div>

            </div><!-- /LEFT COLUMN -->

            <!-- ============================================================
                 RIGHT COLUMN — flex 1
                 ============================================================ -->
            <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:16px;position:sticky;top:32px;align-self:flex-start;">

                <!-- Card: Danh Sách Kiểm Tra SEO Chi Tiết -->
                <div style="background:#fff;border-radius:12px;border:1px solid #bbf7d0;overflow:hidden;box-shadow:0 2px 8px rgba(22,163,74,0.07);">
                    <div style="padding:14px 20px;border-bottom:1px solid #dcfce7;display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:28px;height:28px;border-radius:7px;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                            </div>
                            <span style="font-size:12px;font-weight:800;color:#166534;text-transform:uppercase;letter-spacing:0.7px;"><?php esc_html_e('Danh Sách Kiểm Tra SEO Chi Tiết', 'whp'); ?></span>
                        </div>
                        <span style="background:#fff;color:#16a34a;border:1px solid #bbf7d0;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;white-space:nowrap;"><?php printf( esc_html__('%d mục', 'whp'), count($issues) ); ?></span>
                    </div>
                    <?php foreach ($issues as $issue) :
                        $is_success = $issue['level'] === 'success';
                        $is_danger  = $issue['level'] === 'danger';
                        if ($is_success) {
                            $icon_class = 'dashicons-yes-alt'; $icon_color = '#16a34a'; $icon_bg = '#f0fdf4';
                            $badge_style = 'background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;'; $badge_text = __('An Toàn', 'whp');
                        } elseif ($is_danger) {
                            $icon_class = 'dashicons-warning'; $icon_color = '#dc2626'; $icon_bg = '#fef2f2';
                            $badge_style = 'background:#fef2f2;color:#dc2626;border:1px solid #fecaca;'; $badge_text = __('Nguy hiểm', 'whp');
                        } else {
                            $icon_class = 'dashicons-flag'; $icon_color = '#d97706'; $icon_bg = '#fffbeb';
                            $badge_style = 'background:#fef3c7;color:#d97706;border:1px solid #fde68a;'; $badge_text = __('Cảnh báo', 'whp');
                        }
                    ?>
                    <div class="wpaap-seo-issue-row">
                        <div class="wpaap-icon-wrap" style="background:<?php echo esc_attr($icon_bg); ?>;flex-shrink:0;width:34px;height:34px;">
                            <span class="dashicons <?php echo esc_attr($icon_class); ?>" style="color:<?php echo esc_attr($icon_color); ?>;font-size:18px;width:18px;height:18px;"></span>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13.5px;font-weight:600;color:#1e293b;margin-bottom:3px;"><?php echo esc_html($issue['title']); ?></div>
                            <div style="font-size:12.5px;color:#64748b;line-height:1.5;"><?php echo wp_kses_post($issue['desc']); ?></div>
                        </div>
                        <span style="flex-shrink:0;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;<?php echo $badge_style; ?>">
                            <?php echo esc_html($badge_text); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <div style="padding:12px 20px;border-top:1px solid #f1f5f9;">
                        <a href="#" onclick="jQuery('html,body').animate({scrollTop:0},300);setTimeout(function(){jQuery('#wpaap_ai_scan_seo_btn').trigger('click');},320);return false;" style="font-size:13px;color:#16a34a;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            <?php esc_html_e('Xem hướng dẫn khắc phục', 'whp'); ?> &rarr;
                        </a>
                    </div>
                </div>

                <!-- Card: Top Nội Dung Cần Tối Ưu Gấp -->
                <div style="background:#fff;border-radius:12px;border:1px solid #fecaca;overflow:hidden;box-shadow:0 2px 8px rgba(239,68,68,0.07);">
                    <div style="padding:14px 20px;border-bottom:1px solid #fee2e2;display:flex;align-items:center;gap:8px;background:linear-gradient(135deg,#fff5f5 0%,#fee2e2 100%);">
                        <div style="width:28px;height:28px;border-radius:7px;background:#ef4444;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </div>
                        <span style="font-size:12px;font-weight:800;color:#991b1b;text-transform:uppercase;letter-spacing:0.7px;"><?php esc_html_e('Top Nội Dung Cần Tối Ưu Gấp', 'whp'); ?></span>
                    </div>

                    <!-- Tabs -->
                    <div style="display:flex;border-bottom:1px solid #f1f5f9;background:#fafafa;">
                        <button class="wpaap-tab-btn active" onclick="wpaapSwitchTab(event,'tab-seo-posts')"><?php esc_html_e('Bài viết', 'whp'); ?></button>
                        <?php if (class_exists('WooCommerce')) : ?>
                        <button class="wpaap-tab-btn" onclick="wpaapSwitchTab(event,'tab-seo-products')"><?php esc_html_e('Sản phẩm', 'whp'); ?></button>
                        <?php endif; ?>
                        <button class="wpaap-tab-btn" onclick="wpaapSwitchTab(event,'tab-seo-pages')"><?php esc_html_e('Trang', 'whp'); ?></button>
                    </div>

                    <?php
                    $tab_types = ['tab-seo-posts' => 'post'];
                    if (class_exists('WooCommerce')) $tab_types['tab-seo-products'] = 'product';
                    $tab_types['tab-seo-pages'] = 'page';
                    $first_tab = true;
                    foreach ($tab_types as $tab_id => $pt) :
                        $urgent_posts = wpaap_get_urgent_seo_posts($pt);
                    ?>
                    <div class="wpaap-tab-content <?php echo $first_tab ? 'active' : ''; ?>"
                         id="<?php echo esc_attr($tab_id); ?>"
                         data-post-type="<?php echo esc_attr($pt); ?>"
                         data-offset="5">
                        <?php if (empty($urgent_posts)) : ?>
                            <div style="padding:20px;text-align:center;color:#16a34a;font-size:13px;font-weight:500;"><?php esc_html_e('Không tìm thấy nội dung nào thiếu sót nghiêm trọng.', 'whp'); ?></div>
                        <?php else : ?>
                            <?php foreach ($urgent_posts as $upost) : ?>
                                <?php echo wpaap_render_urgent_post_row($upost, $seo_ai_ok); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php $first_tab = false; endforeach; ?>

                    <div style="padding:10px 20px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;min-height:40px;">
                        <button id="wpaap-load-more-btn" style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#16a34a;font-weight:600;background:none;border:none;cursor:pointer;padding:4px 0;line-height:1;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            <?php esc_html_e('Tải thêm', 'whp'); ?>
                        </button>
                        <span id="wpaap-load-more-note" style="font-size:11.5px;color:#94a3b8;"></span>
                    </div>
                </div>

            </div><!-- /RIGHT COLUMN -->

        </div><!-- /2-column layout -->

        <!-- ================================================================
             BOTTOM — 4 feature cards
             ================================================================ -->
        <div style="margin-top:20px;display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
            <?php
            $seo_features = [
                [
                    'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
                    'color' => '#16a34a', 'bg' => '#f0fdf4',
                    'title' => __('Điểm SEO & Checklist', 'whp'),
                    'desc'  => __('Xem điểm SEO tổng quan, danh sách kiểm tra và các vấn đề cần khắc phục ngay trên trang này.', 'whp'),
                    'cta'   => __('Xem điểm & checklist', 'whp'),
                    'href'  => '#',
                    'js'    => "jQuery('html,body').animate({scrollTop:0},300);return false;",
                ],
                [
                    'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>',
                    'color' => '#2563eb', 'bg' => '#eff6ff',
                    'title' => __('Tối ưu Website', 'whp'),
                    'desc'  => __('Các công cụ giúp tăng tốc và cải thiện trải nghiệm sử dụng website.', 'whp'),
                    'cta'   => __('Vào Tối ưu Website', 'whp'),
                    'href'  => admin_url('admin.php?page=mb-wphelper-extention') . '#mb-seu-s2',
                    'js'    => '',
                ],
                [
                    'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                    'color' => '#7c3aed', 'bg' => '#f5f3ff',
                    'title' => __('Báo cáo AI', 'whp'),
                    'desc'  => __('AI phân tích chuyên sâu từng lỗi SEO và đưa ra hướng dẫn khắc phục cụ thể, dễ thực hiện.', 'whp'),
                    'cta'   => __('Chạy AI Phân tích', 'whp'),
                    'href'  => '#',
                    'js'    => "jQuery('html,body').animate({scrollTop:0},300);setTimeout(function(){jQuery('#wpaap_ai_scan_seo_btn').trigger('click');},320);return false;",
                ],
                [
                    'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
                    'color' => '#d97706', 'bg' => '#fffbeb',
                    'title' => __('Kết nối AI', 'whp'),
                    'desc'  => __('Kết nối Gemini / Claude / GPT để AI tự động viết bài, tối ưu meta và phân tích nội dung.', 'whp'),
                    'cta'   => __('Cấu hình kết nối AI', 'whp'),
                    'href'  => admin_url('admin.php?page=mb-wphelper-ai&subtab=connection'),
                    'js'    => '',
                ],
            ];
            foreach ($seo_features as $feat): ?>
            <div style="background:#fff;border-radius:12px;border:1px solid #e5e7eb;padding:20px 18px;display:flex;flex-direction:column;gap:10px;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                <div style="width:40px;height:40px;background:<?php echo esc_attr($feat['bg']); ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                    <?php echo $feat['svg']; ?>
                </div>
                <div style="flex:1;">
                    <div style="font-size:14px;font-weight:700;color:#1d2327;margin-bottom:4px;"><?php echo esc_html($feat['title']); ?></div>
                    <div style="font-size:12.5px;color:#6b7280;line-height:1.5;"><?php echo esc_html($feat['desc']); ?></div>
                </div>
                <a href="<?php echo esc_url($feat['href']); ?>"
                   <?php if ($feat['js']): ?>onclick="<?php echo esc_attr($feat['js']); ?>"<?php endif; ?>
                   style="font-size:12px;color:<?php echo esc_attr($feat['color']); ?>;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                    <?php echo esc_html($feat['cta']); ?> →
                </a>
            </div>
            <?php endforeach; ?>
        </div><!-- /bottom feature cards -->

        <div style="margin-top:16px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px 18px;display:flex;align-items:center;gap:10px;font-size:13px;color:#166534;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span><strong><?php esc_html_e('Mẹo:', 'whp'); ?></strong> <?php esc_html_e('Hãy quét SEO định kỳ hàng tuần để phát hiện sớm các vấn đề và duy trì thứ hạng tìm kiếm ổn định.', 'whp'); ?></span>
        </div>

        <script>
        function wpaapSwitchTab(evt, tabId) {
            document.querySelectorAll('.wpaap-tab-content').forEach(function(el){el.style.display='none';el.classList.remove('active');});
            document.querySelectorAll('.wpaap-tab-btn').forEach(function(el){el.classList.remove('active');});
            var t = document.getElementById(tabId);
            if(t){t.style.display='block';t.classList.add('active');}
            evt.currentTarget.classList.add('active');
            wpaapUpdateLoadMore();
        }
        function wpaapUpdateLoadMore() {
            var $active = jQuery('.wpaap-tab-content.active');
            if (!$active.length) return;
            var offset = parseInt($active.data('offset') || 5);
            var $btn = jQuery('#wpaap-load-more-btn');
            if (offset === -1) {
                $btn.hide();
                jQuery('#wpaap-load-more-note').text('Đã hiển thị hết');
            } else {
                $btn.show();
                jQuery('#wpaap-load-more-note').text('');
            }
        }
        </script>

        <!-- Modal 1: Confirm AI Quét SEO bài viết -->
        <div id="wpaap-post-seo-confirm-modal" style="display:none;position:fixed;inset:0;z-index:100001;background:rgba(15,23,42,0.6);align-items:center;justify-content:center;backdrop-filter:blur(2px);">
            <div style="background:#fff;border-radius:18px;width:500px;max-width:94vw;box-shadow:0 24px 64px rgba(0,0,0,0.22);overflow:hidden;">

                <!-- Header gradient -->
                <div style="background:linear-gradient(135deg,#16a34a 0%,#15803d 60%,#14532d 100%);padding:22px 24px 20px;position:relative;overflow:hidden;">
                    <!-- Background circles decoration -->
                    <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.06);"></div>
                    <div style="position:absolute;bottom:-30px;right:40px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,0.04);"></div>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:42px;height:42px;border-radius:11px;background:rgba(255,255,255,0.18);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid rgba(255,255,255,0.25);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><polyline points="8 11 11 8 14 11"/><line x1="11" y1="8" x2="11" y2="14"/></svg>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:15px;font-weight:700;color:#fff;letter-spacing:-0.2px;"><?php esc_html_e('AI Quét SEO bài viết', 'whp'); ?></div>
                            <div id="wpaap-confirm-post-title" style="font-size:11.5px;color:rgba(255,255,255,0.75);margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:350px;"></div>
                        </div>
                        <button id="wpaap-confirm-close-x" style="width:28px;height:28px;border-radius:7px;background:rgba(255,255,255,0.12);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;" title="Đóng">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div style="padding:20px 24px 0;">
                    <div style="font-size:12.5px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:10px;"><?php esc_html_e('AI sẽ kiểm tra', 'whp'); ?></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-bottom:18px;">
                        <?php
                        $checklist = [
                            ['icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>', 'text' => __('Điểm SEO tổng thể', 'whp')],
                            ['icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>', 'text' => __('Từ khóa trọng tâm', 'whp')],
                            ['icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>', 'text' => __('Keyword phụ thiếu', 'whp')],
                            ['icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>', 'text' => __('Cấu trúc H2/H3', 'whp')],
                            ['icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>', 'text' => __('Meta Description', 'whp')],
                            ['icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>', 'text' => __('Internal Link', 'whp')],
                            ['icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>', 'text' => __('Độ dài & mật độ', 'whp')],
                            ['icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round"><polyline points="22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>', 'text' => __('Điểm SEO dự kiến', 'whp')],
                        ];
                        foreach ($checklist as $item): ?>
                        <div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #dcfce7;border-radius:8px;padding:8px 10px;font-size:12px;color:#166534;font-weight:500;">
                            <?php echo $item['icon']; ?>
                            <?php echo esc_html($item['text']); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Note -->
                    <div style="display:flex;align-items:flex-start;gap:9px;background:#fffbeb;border:1px solid #fde68a;border-radius:9px;padding:11px 14px;margin-bottom:20px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span style="font-size:12px;color:#92400e;line-height:1.6;"><?php echo wp_kses(__('Quá trình mất khoảng <strong>15–30 giây</strong>. Bài viết <strong>không thay đổi</strong> cho đến khi bạn xác nhận <strong>Áp dụng</strong>.', 'whp'), ['strong' => []]); ?></span>
                    </div>
                </div>

                <!-- Footer -->
                <div style="padding:0 24px 20px;display:flex;gap:10px;justify-content:flex-end;">
                    <button id="wpaap-confirm-cancel-btn" style="padding:9px 20px;border-radius:9px;font-size:13px;font-weight:600;border:1px solid #e2e8f0;background:#f8fafc;color:#475569;cursor:pointer;transition:background 0.15s;"><?php esc_html_e('Hủy', 'whp'); ?></button>
                    <button id="wpaap-confirm-scan-btn" style="padding:9px 22px;border-radius:9px;font-size:13px;font-weight:600;border:none;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;cursor:pointer;display:inline-flex;align-items:center;gap:7px;box-shadow:0 2px 8px rgba(22,163,74,0.3);">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <?php esc_html_e('Xác nhận quét', 'whp'); ?>
                    </button>
                </div>

            </div>
        </div>

        <!-- Modal 2: Kết quả AI Quét SEO -->
        <div id="wpaap-post-seo-result-modal" style="display:none;position:fixed;inset:0;z-index:100001;background:rgba(15,23,42,0.55);align-items:center;justify-content:center;">
            <div style="background:#f8fafc;border-radius:16px;width:700px;max-width:96vw;max-height:90vh;display:flex;flex-direction:column;box-shadow:0 25px 80px rgba(0,0,0,0.22);overflow:hidden;">
                <!-- Gradient header -->
                <div style="background:linear-gradient(135deg,#16a34a,#15803d,#14532d);padding:20px 24px 18px;flex-shrink:0;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,0.07);pointer-events:none;"></div>
                    <div style="position:absolute;bottom:-30px;right:60px;width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,0.05);pointer-events:none;"></div>
                    <button id="wpaap-result-close-x" style="position:absolute;top:14px;right:16px;background:rgba(255,255,255,0.15);border:none;border-radius:7px;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;font-size:14px;line-height:1;" onmouseover="this.style.background='rgba(255,255,255,0.28)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:40px;height:40px;border-radius:10px;background:rgba(255,255,255,0.18);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid rgba(255,255,255,0.25);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><polyline points="8 11 11 8 14 11"/><line x1="11" y1="8" x2="11" y2="14"/></svg>
                        </div>
                        <div>
                            <div style="font-size:16px;font-weight:700;color:#fff;letter-spacing:0.01em;"><?php esc_html_e('Kết quả phân tích SEO', 'whp'); ?></div>
                            <div id="wpaap-result-post-title" style="font-size:12px;color:rgba(255,255,255,0.72);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:520px;"></div>
                        </div>
                    </div>
                </div>
                <!-- Scrollable body -->
                <div id="wpaap-result-body" style="overflow-y:auto;flex:1;padding:18px 20px;background:#f8fafc;">
                    <!-- Loading state -->
                    <div id="wpaap-result-loading" style="padding:48px 24px 52px;text-align:center;">
                        <!-- SVG progress ring + percent -->
                        <div style="position:relative;display:inline-flex;align-items:center;justify-content:center;width:80px;height:80px;margin-bottom:20px;">
                            <svg width="80" height="80" viewBox="0 0 80 80" style="position:absolute;inset:0;transform:rotate(-90deg);">
                                <circle cx="40" cy="40" r="34" fill="none" stroke="#dcfce7" stroke-width="7"/>
                                <circle id="wpaap-result-circle-fill" cx="40" cy="40" r="34" fill="none" stroke="#16a34a" stroke-width="7" stroke-linecap="round" stroke-dasharray="213.63 213.63" stroke-dashoffset="213.63" style="transition:stroke-dashoffset 0.45s ease;"/>
                            </svg>
                            <div id="wpaap-result-percent" style="font-size:14px;font-weight:800;color:#15803d;line-height:1;position:relative;z-index:1;">0%</div>
                        </div>
                        <!-- Progress bar -->
                        <div style="background:#e2e8f0;border-radius:99px;height:6px;overflow:hidden;margin:0 auto 14px;max-width:320px;">
                            <div id="wpaap-result-progress-bar" style="height:100%;width:0%;background:linear-gradient(90deg,#16a34a,#4ade80);border-radius:99px;transition:width 0.4s ease;"></div>
                        </div>
                        <!-- Status message -->
                        <div id="wpaap-result-progress-msg" style="font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;"><?php esc_html_e('Đang kết nối AI...', 'whp'); ?></div>
                        <div style="font-size:11.5px;color:#94a3b8;"><?php esc_html_e('Quá trình phân tích thường mất 15–40 giây', 'whp'); ?></div>
                    </div>
                    <!-- Result content (hidden while loading) -->
                    <div id="wpaap-result-content" style="display:none;">
                        <!-- SEO Score Card -->
                        <div style="background:#fff;border-radius:12px;padding:18px 20px;margin-bottom:12px;box-shadow:0 1px 4px rgba(0,0,0,0.07);border:1px solid #e2e8f0;">
                            <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:14px;"><?php esc_html_e('Điểm SEO tổng quan', 'whp'); ?></div>
                            <div style="display:flex;align-items:center;">
                                <div style="flex:1;text-align:center;">
                                    <div style="font-size:11px;color:#94a3b8;margin-bottom:5px;"><?php esc_html_e('Hiện tại', 'whp'); ?></div>
                                    <div id="wpaap-score-before" style="font-size:44px;font-weight:800;color:#ef4444;line-height:1;">–</div>
                                    <div style="font-size:10px;color:#cbd5e1;margin-top:3px;">/100</div>
                                </div>
                                <div style="display:flex;flex-direction:column;align-items:center;gap:5px;padding:0 18px;">
                                    <svg width="30" height="14" viewBox="0 0 30 14" fill="none"><path d="M2 7 H26 M20 2 L28 7 L20 12" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <div id="wpaap-score-delta" style="font-size:11px;font-weight:700;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:20px;padding:2px 9px;white-space:nowrap;"></div>
                                </div>
                                <div style="flex:1;text-align:center;">
                                    <div style="font-size:11px;color:#94a3b8;margin-bottom:5px;"><?php esc_html_e('Sau khi áp dụng', 'whp'); ?></div>
                                    <div id="wpaap-score-after" style="font-size:44px;font-weight:800;color:#16a34a;line-height:1;">–</div>
                                    <div style="font-size:10px;color:#cbd5e1;margin-top:3px;">/100</div>
                                </div>
                                <div style="width:1px;height:56px;background:#e2e8f0;margin:0 18px;flex-shrink:0;"></div>
                                <div style="flex:1.3;">
                                    <div style="font-size:11px;color:#94a3b8;margin-bottom:6px;"><?php esc_html_e('Từ khóa trọng tâm', 'whp'); ?></div>
                                    <div id="wpaap-focus-keyword" style="font-size:13.5px;color:#15803d;font-weight:700;word-break:break-word;line-height:1.4;"></div>
                                </div>
                            </div>
                        </div>
                        <!-- Issues -->
                        <div id="wpaap-issues-section" style="margin-bottom:12px;display:none;">
                            <div style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,0.07);border:1px solid #fecdd3;">
                                <div style="background:linear-gradient(90deg,#fef2f2,#fff5f5);padding:11px 16px;display:flex;align-items:center;gap:8px;border-bottom:1px solid #fecdd3;">
                                    <div style="width:22px;height:22px;border-radius:6px;background:#ef4444;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                    </div>
                                    <span style="font-size:12.5px;font-weight:700;color:#b91c1c;"><?php esc_html_e('Vấn đề cần khắc phục', 'whp'); ?></span>
                                </div>
                                <div id="wpaap-issues-list" style="padding:8px 14px 4px;"></div>
                            </div>
                        </div>
                        <!-- Meta description -->
                        <div style="background:#fff;border-radius:12px;padding:16px 18px;margin-bottom:12px;box-shadow:0 1px 4px rgba(0,0,0,0.07);border:1px solid #e2e8f0;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div style="width:22px;height:22px;border-radius:6px;background:#2563eb;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>
                                    </div>
                                    <span style="font-size:12.5px;font-weight:700;color:#1e293b;">Meta Description</span>
                                </div>
                                <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:#374151;cursor:pointer;font-weight:500;">
                                    <input type="checkbox" id="wpaap-apply-meta-cb" style="accent-color:#16a34a;width:14px;height:14px;" checked>
                                    <?php esc_html_e('Áp dụng đề xuất', 'whp'); ?>
                                </label>
                            </div>
                            <div style="background:#f8fafc;border-radius:8px;padding:9px 12px;font-size:12px;color:#64748b;margin-bottom:10px;border:1px solid #f1f5f9;">
                                <span style="font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.04em;">Hiện tại:</span>
                                <span id="wpaap-current-meta" style="display:block;margin-top:3px;font-style:italic;color:#475569;line-height:1.5;word-break:break-word;"></span>
                            </div>
                            <textarea id="wpaap-meta-desc-textarea" rows="3" style="width:100%;border:1.5px solid #bfdbfe;border-radius:8px;padding:9px 12px;font-size:13px;color:#1e40af;resize:vertical;box-sizing:border-box;background:#eff6ff;font-weight:500;line-height:1.5;" placeholder="Meta description đề xuất từ AI..."></textarea>
                        </div>
                        <!-- Suggested keywords -->
                        <div id="wpaap-keywords-section" style="background:#fff;border-radius:12px;padding:16px 18px;margin-bottom:12px;box-shadow:0 1px 4px rgba(0,0,0,0.07);border:1px solid #e2e8f0;display:none;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="width:22px;height:22px;border-radius:6px;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </div>
                                <span style="font-size:12.5px;font-weight:700;color:#1e293b;"><?php esc_html_e('Từ khóa liên quan nên bổ sung', 'whp'); ?></span>
                            </div>
                            <div id="wpaap-keywords-list" style="display:flex;flex-wrap:wrap;gap:8px;"></div>
                        </div>
                        <!-- Suggested headings -->
                        <div id="wpaap-headings-section" style="background:#fff;border-radius:12px;padding:16px 18px;margin-bottom:12px;box-shadow:0 1px 4px rgba(0,0,0,0.07);border:1px solid #e2e8f0;display:none;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="width:22px;height:22px;border-radius:6px;background:#7c3aed;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                                </div>
                                <span style="font-size:12.5px;font-weight:700;color:#1e293b;"><?php esc_html_e('Tiêu đề H2 nên bổ sung', 'whp'); ?></span>
                            </div>
                            <div id="wpaap-headings-list"></div>
                        </div>
                        <!-- Suggested internal links -->
                        <div id="wpaap-links-section" style="background:#fff;border-radius:12px;padding:16px 18px;margin-bottom:4px;box-shadow:0 1px 4px rgba(0,0,0,0.07);border:1px solid #e2e8f0;display:none;">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                                <div style="width:22px;height:22px;border-radius:6px;background:#0891b2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                                </div>
                                <span style="font-size:12.5px;font-weight:700;color:#1e293b;"><?php esc_html_e('Liên kết nội bộ nên bổ sung', 'whp'); ?></span>
                            </div>
                            <div id="wpaap-links-list"></div>
                        </div>
                    </div>
                </div>
                <!-- Sticky footer -->
                <div style="padding:14px 20px;border-top:1px solid #e2e8f0;flex-shrink:0;display:flex;justify-content:space-between;align-items:center;background:#fff;">
                    <button id="wpaap-result-cancel-btn" style="padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;border:1px solid #e2e8f0;background:#f8fafc;color:#64748b;cursor:pointer;"><?php esc_html_e('Hủy', 'whp'); ?></button>
                    <div style="display:flex;gap:8px;">
                        <button id="wpaap-preview-changes-btn" style="display:none;padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid #bfdbfe;background:#eff6ff;color:#2563eb;cursor:pointer;"><?php esc_html_e('Xem trước thay đổi', 'whp'); ?></button>
                        <button id="wpaap-apply-changes-btn" style="display:none;padding:9px 22px;border-radius:8px;font-size:13px;font-weight:700;border:none;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;cursor:pointer;box-shadow:0 2px 8px rgba(22,163,74,0.35);"><?php esc_html_e('Áp dụng thay đổi', 'whp'); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 3: Xem trước thay đổi -->
        <div id="wpaap-post-seo-preview-modal" style="display:none;position:fixed;inset:0;z-index:100002;background:rgba(15,23,42,0.65);align-items:center;justify-content:center;">
            <div style="background:#f8fafc;border-radius:16px;width:640px;max-width:96vw;max-height:88vh;display:flex;flex-direction:column;box-shadow:0 25px 80px rgba(0,0,0,0.22);overflow:hidden;">
                <!-- Gradient header -->
                <div style="background:linear-gradient(135deg,#4f46e5,#3730a3,#1e1b4b);padding:18px 22px 16px;flex-shrink:0;position:relative;overflow:hidden;">
                    <div style="position:absolute;top:-18px;right:-18px;width:90px;height:90px;border-radius:50%;background:rgba(255,255,255,0.07);pointer-events:none;"></div>
                    <div style="position:absolute;bottom:-25px;right:55px;width:60px;height:60px;border-radius:50%;background:rgba(255,255,255,0.05);pointer-events:none;"></div>
                    <button id="wpaap-preview-close-btn" style="position:absolute;top:13px;right:14px;background:rgba(255,255,255,0.15);border:none;border-radius:7px;width:27px;height:27px;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;font-size:14px;line-height:1;" onmouseover="this.style.background='rgba(255,255,255,0.28)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
                    <div style="display:flex;align-items:center;gap:11px;">
                        <div style="width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,0.18);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;flex-shrink:0;border:1px solid rgba(255,255,255,0.25);">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </div>
                        <div>
                            <div style="font-size:15px;font-weight:700;color:#fff;"><?php esc_html_e('Xem trước thay đổi', 'whp'); ?></div>
                            <div style="font-size:11.5px;color:rgba(255,255,255,0.65);margin-top:2px;"><?php esc_html_e('Kiểm tra kỹ trước khi áp dụng vào bài viết', 'whp'); ?></div>
                        </div>
                    </div>
                </div>
                <!-- Body -->
                <div id="wpaap-preview-body" style="overflow-y:auto;flex:1;padding:16px 18px;background:#f8fafc;"></div>
                <!-- Footer -->
                <div style="padding:12px 20px;border-top:1px solid #e2e8f0;flex-shrink:0;display:flex;justify-content:space-between;align-items:center;background:#fff;">
                    <span style="font-size:11.5px;color:#94a3b8;"><?php esc_html_e('Xem xong? Đóng để quay lại chỉnh sửa hoặc áp dụng.', 'whp'); ?></span>
                    <button id="wpaap-preview-close-btn2" style="padding:8px 20px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid #c7d2fe;background:#eef2ff;color:#4338ca;cursor:pointer;"><?php esc_html_e('Đóng', 'whp'); ?></button>
                </div>
            </div>
        </div>

        <!-- Modal 4: Thông báo kết quả Áp dụng SEO -->
        <div id="wpaap-apply-result-modal" style="display:none;position:fixed;inset:0;z-index:100003;background:rgba(15,23,42,0.6);align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:18px;width:420px;max-width:94vw;overflow:hidden;box-shadow:0 30px 90px rgba(0,0,0,0.25);animation:wpaapFadeIn 0.22s ease;">
                <!-- Icon area -->
                <div id="wpaap-apply-result-icon-wrap" style="padding:32px 24px 20px;text-align:center;background:linear-gradient(160deg,#f0fdf4,#fff);">
                    <div id="wpaap-apply-result-icon" style="width:62px;height:62px;border-radius:50%;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#16a34a,#15803d);box-shadow:0 6px 20px rgba(22,163,74,0.32);">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div id="wpaap-apply-result-title" style="font-size:17px;font-weight:800;color:#15803d;margin-bottom:8px;"><?php esc_html_e('Áp dụng thành công!', 'whp'); ?></div>
                    <div id="wpaap-apply-result-msg" style="font-size:13px;color:#475569;line-height:1.6;max-width:320px;margin:0 auto;"></div>
                </div>
                <!-- Revision note -->
                <div id="wpaap-apply-result-note" style="display:none;background:#f0fdf4;border-top:1px solid #dcfce7;padding:10px 20px;text-align:center;">
                    <div style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:#16a34a;font-weight:600;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <?php esc_html_e('Một bản sao lưu (revision) đã được tạo tự động', 'whp'); ?>
                    </div>
                </div>
                <!-- Buttons -->
                <div style="padding:16px 20px;display:flex;gap:10px;justify-content:center;">
                    <button id="wpaap-apply-result-close" style="padding:9px 22px;border-radius:9px;font-size:13px;font-weight:600;border:1.5px solid #e2e8f0;background:#f8fafc;color:#64748b;cursor:pointer;"><?php esc_html_e('Đóng', 'whp'); ?></button>
                    <a id="wpaap-apply-result-edit-link" href="#" target="_blank" style="display:none;padding:9px 22px;border-radius:9px;font-size:13px;font-weight:700;border:none;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;cursor:pointer;text-decoration:none;box-shadow:0 2px 8px rgba(22,163,74,0.3);">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="vertical-align:middle;margin-right:4px;"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><polyline points="16 2 22 2 22 8"/><line x1="11" y1="13" x2="22" y2="2"/></svg>
                        <?php esc_html_e('Mở trang chỉnh sửa', 'whp'); ?>
                    </a>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <style>
        @keyframes wpaapFadeIn { from { opacity:0; transform:scale(0.92) translateY(8px); } to { opacity:1; transform:scale(1) translateY(0); } }
    </style>

    <script>
    var whpSeoAiI18n = {
        connecting:   '<?php echo esc_js(__('Đang kết nối AI...', 'whp')); ?>',
        reanalyze:    '<?php echo esc_js(__('AI Phân Tích SEO', 'whp')); ?>',
        collapseAll:  '<?php echo esc_js(__('Thu gọn tất cả', 'whp')); ?>',
        expandAll:    '<?php echo esc_js(__('Mở rộng tất cả', 'whp')); ?>',
        collapse:     '<?php echo esc_js(__('Thu gọn', 'whp')); ?>',
        expand:       '<?php echo esc_js(__('Mở rộng', 'whp')); ?>',
        applyChanges:    '<?php echo esc_js(__('Áp dụng thay đổi', 'whp')); ?>',
        selectCategory:  '<?php echo esc_js(__('Vui lòng chọn một danh mục sản phẩm!', 'whp')); ?>',
        scanMsg1:        '<?php echo esc_js(__('Đọc nội dung bài viết…', 'whp')); ?>',
        scanMsg2:        '<?php echo esc_js(__('Phân tích từ khóa và cấu trúc…', 'whp')); ?>',
        scanMsg3:        '<?php echo esc_js(__('Kiểm tra Meta Description…', 'whp')); ?>',
        scanMsg4:        '<?php echo esc_js(__('Đánh giá điểm SEO tổng thể…', 'whp')); ?>',
        scanMsg5:        '<?php echo esc_js(__('Tổng hợp đề xuất cải thiện…', 'whp')); ?>',
        scanDone:        '<?php echo esc_js(__('Phân tích hoàn tất!', 'whp')); ?>'
    };
        jQuery(document).ready(function($) {

            // ── Load-more bài viết cần tối ưu ──────────────────────────────
            var _lmNonce = '<?php echo wp_create_nonce('wpaap_seo_nonce'); ?>';
            var _lmAiOk  = <?php echo $seo_ai_ok ? 'true' : 'false'; ?>;

            wpaapUpdateLoadMore();

            $('#wpaap-load-more-btn').on('click', function() {
                var $btn     = $(this);
                var $active  = $('.wpaap-tab-content.active');
                if (!$active.length) return;

                var postType = $active.data('post-type') || 'post';
                var offset   = parseInt($active.data('offset') || 5);

                $btn.prop('disabled', true).html(
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="animation:wpaapSpin 1s linear infinite"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg> Đang tải...'
                );

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action:    'wpaap_load_more_seo_posts',
                        nonce:     _lmNonce,
                        post_type: postType,
                        offset:    offset,
                        ai_ok:     _lmAiOk ? '1' : '0',
                    },
                    success: function(res) {
                        $btn.prop('disabled', false).html(
                            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg> Tải thêm'
                        );
                        if (res.success) {
                            if (res.data.html) {
                                $active.append(res.data.html);
                                $active.data('offset', offset + res.data.count);
                            }
                            if (!res.data.has_more) {
                                $btn.hide();
                                $('#wpaap-load-more-note').text('Đã hiển thị hết');
                            }
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html(
                            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg> Tải thêm'
                        );
                    }
                });
            });
            // ───────────────────────────────────────────────────────────────

            // Inject badge + nút thu gọn vào từng ai-issue-box
            function wpaapSeoInjectCollapseButtons() {
                $('#wpaap_ai_advice_content .ai-issue-box').each(function() {
                    var $box = $(this);
                    if ($box.find('.wpaap-issue-collapse-btn').length) return;
                    var badgeColor = '#dc2626', badgeBg = '#fef2f2', badgeBorder = '#fecaca', badgeText = 'Nguy hiểm';
                    if ($box.hasClass('warning')) {
                        badgeColor = '#d97706'; badgeBg = '#fffbeb'; badgeBorder = '#fde68a'; badgeText = 'Cảnh báo';
                    } else if ($box.hasClass('info')) {
                        badgeColor = '#2563eb'; badgeBg = '#eff6ff'; badgeBorder = '#bfdbfe'; badgeText = 'Khuyến nghị';
                    } else if ($box.hasClass('success')) {
                        badgeColor = '#16a34a'; badgeBg = '#f0fdf4'; badgeBorder = '#bbf7d0'; badgeText = 'Ổn định';
                    }
                    var $h4 = $box.find('h4').first();
                    $box.children().not('h4').wrapAll('<div class="wpaap-issue-body" style="max-height:2000px;opacity:1;"></div>');
                    var badgeHtml = '<span style="flex-shrink:0;background:' + badgeBg + ';color:' + badgeColor + ';border:1px solid ' + badgeBorder + ';padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;">' + badgeText + '</span>';
                    var btnHtml = '<button class="wpaap-issue-collapse-btn" data-state="open"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> Thu gọn</button>';
                    $h4.css({'display':'flex','align-items':'center','gap':'8px'}).append(badgeHtml + btnHtml);
                });
                $('#wpaap_ai_advice_content').off('click', '.wpaap-issue-collapse-btn').on('click', '.wpaap-issue-collapse-btn', function() {
                    var $btn = $(this);
                    var $body = $btn.closest('.ai-issue-box').find('.wpaap-issue-body');
                    var isOpen = $btn.data('state') === 'open';
                    if (isOpen) {
                        $body.addClass('collapsed');
                        $btn.data('state','closed').html('<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg> Mở rộng');
                    } else {
                        $body.removeClass('collapsed');
                        $btn.data('state','open').html('<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> Thu gọn');
                    }
                });
            }

            // Thu gọn tất cả / Mở rộng tất cả
            var _allSeoCollapsed = false;
            $('#wpaap_seo_report_collapse').on('click', function() {
                var $collapseBtn = $(this);
                _allSeoCollapsed = !_allSeoCollapsed;
                $('#wpaap_ai_advice_content .wpaap-issue-body').toggleClass('collapsed', _allSeoCollapsed);
                $('#wpaap_ai_advice_content .wpaap-issue-collapse-btn').each(function() {
                    $(this).data('state', _allSeoCollapsed ? 'closed' : 'open');
                    $(this).html(_allSeoCollapsed
                        ? '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg> Mở rộng'
                        : '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> Thu gọn');
                });
                $collapseBtn.html(_allSeoCollapsed
                    ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg> Mở rộng tất cả'
                    : '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> Thu gọn tất cả');
            });

            // Xử lý ẩn hiện Dropdown
            $('#wpaap_scan_type').on('change', function() {
                var val = $(this).val();
                $('#wpaap_post_cat_container').hide();
                $('#wpaap_product_cat_container').hide();
                if (val === 'post_category') {
                    $('#wpaap_post_cat_container').show();
                } else if (val === 'product_category') {
                    $('#wpaap_product_cat_container').show();
                }
            });

            $('#wpaap_ai_scan_seo_btn').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $notice = $('#wpaap_seo_ai_notice');

                if ($btn.data('ai-ok') !== 1 && $btn.data('ai-ok') !== '1') {
                    $notice.addClass('mb-error').css({'background':'#fef2f2','color':'#dc2626','border':'1px solid #fecaca'})
                        .html('<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="vertical-align:middle;margin-right:5px;"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Chưa kết nối AI. Vui lòng cấu hình API Key tại <a href="?page=mb-wphelper-ai&subtab=connection" style="color:#dc2626;text-decoration:underline;">tab Kết nối AI</a> trước.')
                        .show();
                    setTimeout(function() { $notice.fadeOut(); }, 8000);
                    return;
                }

                var scanType = $('#wpaap_scan_type').val();
                var termId = '';
                if (scanType === 'post_category') {
                    termId = $('#wpaap_post_cat_select').val();
                    if (!termId) {
                        alert('Vui lòng chọn một chuyên mục!');
                        return;
                    }
                } else if (scanType === 'product_category') {
                    termId = $('#wpaap_product_cat_select').val();
                    if (!termId) {
                        alert(whpSeoAiI18n.selectCategory);
                        return;
                    }
                }

                // UI thay đổi
                $btn.prop('disabled', true).css('opacity', '0.7').html('<span class="dashicons dashicons-update-alt" style="animation: wpaapSpin 2s infinite linear;"></span> ' + whpSeoAiI18n.connecting);
                $('#wpaap_ai_scan_loading').slideDown();
                $('#wpaap_ai_advice_container').slideUp();

                // Thêm keyframes quay icon
                if ($('#wpaap-spin-css').length === 0) {
                    $('head').append('<style id="wpaap-spin-css">@keyframes wpaapSpin { 100% { transform: rotate(360deg); } }</style>');
                }

                // Logic chạy % ảo trong lúc đợi
                var percent = 0;
                $('#wpaap_ai_loading_percent').text('0%');
                var interval = setInterval(function() {
                    if (percent < 99) {
                        percent += Math.floor(Math.random() * 10) + 1;
                        if (percent > 99) percent = 99;
                        $('#wpaap_ai_loading_percent').text(percent + '%');
                    }
                }, 600);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpaap_ai_seo_scan',
                        nonce: '<?php echo wp_create_nonce("wpaap_seo_nonce"); ?>',
                        scan_type: scanType,
                        term_id: termId
                    },
                    success: function(response) {
                        clearInterval(interval);
                        $('#wpaap_ai_loading_percent').text('100%');

                        $btn.prop('disabled', false).css('opacity', '1').html('<span class="dashicons dashicons-superhero" style="font-size:15px;width:15px;height:15px;line-height:15px;vertical-align:middle;margin-right:5px;"></span> ' + whpSeoAiI18n.reanalyze);
                        $('#wpaap_ai_scan_loading').slideUp();

                        if (response.success && response.data.ai_advice) {
                            $('#wpaap_ai_advice_content').html(response.data.ai_advice);
                            wpaapSeoInjectCollapseButtons();
                            $('#wpaap_ai_advice_container').slideDown();
                            
                            // Cập nhật lại giao diện thống kê nội dung
                            if (response.data.content_stats) {
                                $('#stats_label').text('Đã quét ' + response.data.content_stats.total + ' ' + response.data.content_stats.type_label);
                                $('#stats_missing_meta').text(response.data.content_stats.missing_meta);
                                $('#stats_missing_keyword').text(response.data.content_stats.missing_keyword);
                                $('#stats_thin_content').text(response.data.content_stats.thin_content);
                                $('#stats_good').text(response.data.content_stats.good);

                                var total = response.data.content_stats.total > 0 ? response.data.content_stats.total : 1;
                                $('#stats_bar_meta').css('width', (response.data.content_stats.missing_meta / total * 100) + '%');
                                $('#stats_bar_keyword').css('width', (response.data.content_stats.missing_keyword / total * 100) + '%');
                                $('#stats_bar_thin').css('width', (response.data.content_stats.thin_content / total * 100) + '%');
                                $('#stats_bar_good').css('width', (response.data.content_stats.good / total * 100) + '%');
                            }
                        } else {
                            var errMsg = (response.data && response.data.message) ? response.data.message : (response.data || '<?php echo esc_js( __('Không nhận được dữ liệu hợp lệ.', 'whp') ); ?>');
                            alert('Có lỗi xảy ra: ' + errMsg);
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        clearInterval(interval);

                        $btn.prop('disabled', false).css('opacity', '1').html('<span class="dashicons dashicons-superhero" style="font-size:15px;width:15px;height:15px;line-height:15px;vertical-align:middle;margin-right:5px;"></span> ' + whpSeoAiI18n.reanalyze);
                        $('#wpaap_ai_scan_loading').slideUp();
                        
                        var errorMsg = 'Lỗi kết nối máy chủ! Quá trình phản hồi AI có thể đã vượt quá thời gian cho phép.';
                        if (xhr.status) {
                            errorMsg += '\nMã lỗi HTTP: ' + xhr.status + ' ' + errorThrown;
                        }
                        if (xhr.responseText) {
                            errorMsg += '\nChi tiết: ' + xhr.responseText.substring(0, 500);
                        }
                        alert(errorMsg);
                    }
                });
            });

            // ================================================================
            // AI Quét SEO từng bài viết
            // ================================================================
            var _wpaap_current_post_id = 0;
            var _wpaap_current_scan_data = null;

            var _wpaap_scan_nonce = '<?php echo wp_create_nonce('wpaap_scan_post_nonce'); ?>';
            var _wpaap_apply_nonce = '<?php echo wp_create_nonce('wpaap_apply_seo_nonce'); ?>';

            function escHtml(str) {
                var d = document.createElement('div');
                d.appendChild(document.createTextNode(str || ''));
                return d.innerHTML;
            }
            function escAttr(str) {
                return (str || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            // Open confirm modal on button click
            $(document).on('click', '.wpaap-ai-scan-post-btn', function() {
                var $btn = $(this);
                _wpaap_current_post_id = $btn.data('post-id');
                var postTitle = $btn.data('post-title') || '';
                $('#wpaap-confirm-post-title').text(postTitle);
                _wpaap_current_scan_data = null;
                var $modal = $('#wpaap-post-seo-confirm-modal');
                $modal.css('display', 'flex');
            });

            // Backdrop click intentionally disabled — only Hủy / ✕ close this modal

            // Cancel / close confirm modal
            $('#wpaap-confirm-cancel-btn, #wpaap-confirm-close-x').on('click', function() {
                $('#wpaap-post-seo-confirm-modal').css('display', 'none');
            });

            // Confirm → run scan
            var _scanProgressInterval = null;
            var _scanProgressMsgs = [
                whpSeoAiI18n.scanMsg1,
                whpSeoAiI18n.scanMsg2,
                whpSeoAiI18n.scanMsg3,
                whpSeoAiI18n.scanMsg4,
                whpSeoAiI18n.scanMsg5
            ];

            var _wpaapCircumference = 213.63;

            function wpaapSetCircle(pct) {
                var offset = _wpaapCircumference - (pct / 100) * _wpaapCircumference;
                $('#wpaap-result-circle-fill').css('stroke-dashoffset', offset);
            }

            function wpaapStartScanProgress() {
                var pct = 0;
                $('#wpaap-result-percent').text('0%');
                $('#wpaap-result-progress-bar').css('width', '0%');
                $('#wpaap-result-circle-fill').css('stroke-dashoffset', _wpaapCircumference);
                $('#wpaap-result-progress-msg').text(_scanProgressMsgs[0]);
                clearInterval(_scanProgressInterval);
                _scanProgressInterval = setInterval(function() {
                    if (pct < 93) {
                        pct += Math.floor(Math.random() * 7) + 2;
                        if (pct > 93) pct = 93;
                        $('#wpaap-result-percent').text(pct + '%');
                        $('#wpaap-result-progress-bar').css('width', pct + '%');
                        wpaapSetCircle(pct);
                        var msgIdx = Math.min(Math.floor(pct / 20), _scanProgressMsgs.length - 1);
                        $('#wpaap-result-progress-msg').text(_scanProgressMsgs[msgIdx]);
                    }
                }, 650);
            }

            function wpaapStopScanProgress(success) {
                clearInterval(_scanProgressInterval);
                if (success) {
                    $('#wpaap-result-percent').text('100%');
                    $('#wpaap-result-progress-bar').css('width', '100%');
                    wpaapSetCircle(100);
                    $('#wpaap-result-progress-msg').text(whpSeoAiI18n.scanDone);
                }
            }

            $('#wpaap-confirm-scan-btn').on('click', function() {
                $('#wpaap-post-seo-confirm-modal').css('display', 'none');

                // Show result modal in loading state
                var $resultModal = $('#wpaap-post-seo-result-modal');
                $('#wpaap-result-loading').show();
                $('#wpaap-result-content').hide();
                $('#wpaap-preview-changes-btn, #wpaap-apply-changes-btn').hide();
                var postTitle = $('[data-post-id="' + _wpaap_current_post_id + '"].wpaap-ai-scan-post-btn').data('post-title') || '';
                $('#wpaap-result-post-title').text(postTitle);
                $resultModal.css('display', 'flex');
                wpaapStartScanProgress();

                // AJAX call
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    timeout: 120000,
                    data: {
                        action: 'wpaap_ai_scan_post_seo',
                        nonce: _wpaap_scan_nonce,
                        post_id: _wpaap_current_post_id
                    },
                    success: function(resp) {
                        wpaapStopScanProgress(resp && resp.success);
                        setTimeout(function() {
                            $('#wpaap-result-loading').hide();
                            if (resp && resp.success && resp.data) {
                                _wpaap_current_scan_data = resp.data;
                                wpaapPopulateResultModal(resp.data);
                                $('#wpaap-result-content').show();
                                $('#wpaap-preview-changes-btn, #wpaap-apply-changes-btn').show();
                            } else {
                                var msg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Lỗi không xác định từ máy chủ.';
                                $('#wpaap-result-content').html('<div style="color:#ef4444;font-size:13px;padding:20px 0;">' + escHtml(msg) + '</div>').show();
                            }
                        }, 400);
                    },
                    error: function(xhr) {
                        wpaapStopScanProgress(false);
                        $('#wpaap-result-loading').hide();
                        var msg = 'Lỗi kết nối! Quá trình phản hồi AI có thể đã vượt quá thời gian cho phép.';
                        if (xhr.status) msg += ' (HTTP ' + xhr.status + ')';
                        $('#wpaap-result-content').html('<div style="color:#ef4444;font-size:13px;padding:20px 0;">' + escHtml(msg) + '</div>').show();
                    }
                });
            });

            function wpaapPopulateResultModal(data) {
                // Score
                var score = parseInt(data.seo_score) || 0;
                var scoreAfter = parseInt(data.estimated_score_after) || score;
                var scoreColor = score >= 80 ? '#16a34a' : (score >= 60 ? '#d97706' : '#ef4444');
                var scoreAfterColor = scoreAfter >= 80 ? '#16a34a' : (scoreAfter >= 60 ? '#d97706' : '#ef4444');
                $('#wpaap-score-before').text(score).css('color', scoreColor);
                $('#wpaap-score-after').text(scoreAfter).css('color', scoreAfterColor);
                var delta = scoreAfter - score;
                var deltaText = delta > 0 ? '+' + delta + ' điểm' : (delta < 0 ? delta + ' điểm' : '±0');
                var deltaColor = delta > 0 ? '#16a34a' : (delta < 0 ? '#ef4444' : '#64748b');
                var deltaBg = delta > 0 ? '#f0fdf4' : (delta < 0 ? '#fef2f2' : '#f8fafc');
                var deltaBorder = delta > 0 ? '#bbf7d0' : (delta < 0 ? '#fecdd3' : '#e2e8f0');
                $('#wpaap-score-delta').text(deltaText).css({'color': deltaColor, 'background': deltaBg, 'border-color': deltaBorder});
                $('#wpaap-focus-keyword').text(data.focus_keyword || '(chưa xác định)');

                // Current meta
                $('#wpaap-current-meta').text(data.current_meta || '(trống)');
                $('#wpaap-meta-desc-textarea').val(data.meta_description || '');
                $('#wpaap-apply-meta-cb').prop('checked', true);

                // Issues
                var $issuesList = $('#wpaap-issues-list');
                $issuesList.empty();
                if (data.issues && data.issues.length > 0) {
                    $.each(data.issues, function(i, iss) {
                        $issuesList.append(
                            '<div style="display:flex;align-items:flex-start;gap:8px;padding:8px 4px;border-bottom:1px solid #fee2e2;font-size:12.5px;color:#7f1d1d;line-height:1.45;">' +
                            '<svg style="flex-shrink:0;margin-top:1px;" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' +
                            '<span>' + escHtml(iss) + '</span></div>'
                        );
                    });
                    $('#wpaap-issues-section').show();
                } else {
                    $('#wpaap-issues-section').hide();
                }

                // Keywords
                var $kwList = $('#wpaap-keywords-list');
                $kwList.empty();
                if (data.suggested_keywords && data.suggested_keywords.length > 0) {
                    $.each(data.suggested_keywords, function(i, kw) {
                        var cbId = 'wpaap-kw-' + i;
                        $kwList.append(
                            '<label style="display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;border:1.5px solid #bbf7d0;color:#166534;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;">' +
                            '<input type="checkbox" class="wpaap-kw-cb" value="' + escAttr(kw) + '" id="' + cbId + '" style="accent-color:#16a34a;" checked>' +
                            escHtml(kw) + '</label>'
                        );
                    });
                    $('#wpaap-keywords-section').show();
                } else {
                    $('#wpaap-keywords-section').hide();
                }

                // Headings — strip "H2: " prefix if AI already included it
                var $hdList = $('#wpaap-headings-list');
                $hdList.empty();
                if (data.suggested_headings && data.suggested_headings.length > 0) {
                    $.each(data.suggested_headings, function(i, hd) {
                        var cbId = 'wpaap-hd-' + i;
                        var hdText = hd.replace(/^H[1-6]:\s*/i, '');
                        $hdList.append(
                            '<label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#374151;padding:8px 10px;background:#faf5ff;border:1px solid #ede9fe;border-radius:7px;margin-bottom:6px;cursor:pointer;">' +
                            '<input type="checkbox" class="wpaap-hd-cb" value="' + escAttr(hd) + '" id="' + cbId + '" style="accent-color:#7c3aed;flex-shrink:0;" checked>' +
                            '<span style="background:#7c3aed;color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:4px;flex-shrink:0;letter-spacing:0.03em;">H2</span>' +
                            '<span style="flex:1;">' + escHtml(hdText) + '</span></label>'
                        );
                    });
                    $('#wpaap-headings-section').show();
                } else {
                    $('#wpaap-headings-section').hide();
                }

                // Internal links
                var $lnList = $('#wpaap-links-list');
                $lnList.empty();
                if (data.suggested_internal_links && data.suggested_internal_links.length > 0) {
                    $.each(data.suggested_internal_links, function(i, lnk) {
                        var cbId = 'wpaap-lnk-' + i;
                        var lnkVal = (typeof lnk === 'object') ? JSON.stringify(lnk) : lnk;
                        var lnkDisplay = (typeof lnk === 'object') ? (lnk.anchor || lnk.url || lnkVal) : lnk;
                        $lnList.append(
                            '<label style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#164e63;padding:8px 10px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:7px;margin-bottom:6px;cursor:pointer;word-break:break-all;">' +
                            '<input type="checkbox" class="wpaap-lnk-cb" value="' + escAttr(lnkVal) + '" id="' + cbId + '" style="accent-color:#0891b2;flex-shrink:0;" checked>' +
                            '<span style="flex:1;">' + escHtml(lnkDisplay) + '</span></label>'
                        );
                    });
                    $('#wpaap-links-section').show();
                } else {
                    $('#wpaap-links-section').hide();
                }
            }

            // Backdrop click closes result modal
            $('#wpaap-post-seo-result-modal').on('click', function(e) {
                if ($(e.target).is('#wpaap-post-seo-result-modal')) {
                    $(this).css('display', 'none');
                }
            });
            $('#wpaap-result-cancel-btn, #wpaap-result-close-x').on('click', function() {
                $('#wpaap-post-seo-result-modal').css('display', 'none');
            });

            // Preview changes
            $('#wpaap-preview-changes-btn').on('click', function() {
                if (!_wpaap_current_scan_data) return;
                var d = _wpaap_current_scan_data;
                var html = '';

                var svgMeta   = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>';
                var svgKw     = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
                var svgH2     = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>';
                var svgLink   = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>';

                function sectionCard(iconSvg, iconBg, title, body) {
                    return '<div style="background:#fff;border-radius:12px;padding:14px 16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,0.07);border:1px solid #e2e8f0;">' +
                        '<div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">' +
                        '<div style="width:22px;height:22px;border-radius:6px;background:' + iconBg + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">' + iconSvg + '</div>' +
                        '<span style="font-size:12.5px;font-weight:700;color:#1e293b;">' + title + '</span>' +
                        '</div>' + body + '</div>';
                }

                // Meta description before/after
                var applyMeta = $('#wpaap-apply-meta-cb').is(':checked');
                var metaBody = '';
                metaBody += '<div style="margin-bottom:8px;">';
                metaBody += '<div style="font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Hiện tại</div>';
                metaBody += '<div style="background:#f8fafc;border:1px solid #f1f5f9;border-radius:7px;padding:9px 12px;font-size:12.5px;color:#64748b;line-height:1.5;font-style:italic;">' + escHtml(d.current_meta || '(trống)') + '</div>';
                metaBody += '</div>';
                if (applyMeta) {
                    metaBody += '<div style="font-size:10.5px;font-weight:700;color:#16a34a;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Sẽ cập nhật thành</div>';
                    metaBody += '<div style="background:#f0fdf4;border:1.5px solid #86efac;border-radius:7px;padding:9px 12px;font-size:12.5px;color:#15803d;line-height:1.5;font-weight:500;">' + escHtml($('#wpaap-meta-desc-textarea').val()) + '</div>';
                } else {
                    metaBody += '<div style="font-size:12px;color:#94a3b8;font-style:italic;padding:4px 0;">Không áp dụng (đã bỏ chọn).</div>';
                }
                html += sectionCard(svgMeta, '#2563eb', 'Meta Description', metaBody);

                // Keywords to add
                var selectedKws = [];
                $('.wpaap-kw-cb:checked').each(function() { selectedKws.push($(this).val()); });
                if (selectedKws.length > 0) {
                    var kwBody = '<div style="display:flex;flex-wrap:wrap;gap:7px;">';
                    $.each(selectedKws, function(i, kw) {
                        kwBody += '<span style="background:#f0fdf4;border:1.5px solid #bbf7d0;color:#166534;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">' + escHtml(kw) + '</span>';
                    });
                    kwBody += '</div>';
                    html += sectionCard(svgKw, '#16a34a', 'Từ khóa sẽ bổ sung vào cuối bài (' + selectedKws.length + ')', kwBody);
                }

                // Headings to add — strip "H2: " prefix if AI already included it
                var selectedHds = [];
                $('.wpaap-hd-cb:checked').each(function() { selectedHds.push($(this).val()); });
                if (selectedHds.length > 0) {
                    var hdBody = '';
                    $.each(selectedHds, function(i, hd) {
                        var hdText = hd.replace(/^H[1-6]:\s*/i, '');
                        hdBody += '<div style="display:flex;align-items:center;gap:8px;background:#faf5ff;border:1px solid #ede9fe;border-radius:7px;padding:8px 12px;margin-bottom:6px;">' +
                            '<span style="background:#7c3aed;color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:4px;flex-shrink:0;letter-spacing:0.03em;">H2</span>' +
                            '<span style="font-size:12.5px;color:#374151;line-height:1.4;">' + escHtml(hdText) + '</span></div>';
                    });
                    html += sectionCard(svgH2, '#7c3aed', 'Tiêu đề H2 sẽ bổ sung vào cuối bài (' + selectedHds.length + ')', hdBody);
                }

                // Links to add
                var selectedLnks = [];
                $('.wpaap-lnk-cb:checked').each(function() { selectedLnks.push($(this).val()); });
                if (selectedLnks.length > 0) {
                    var lnkBody = '';
                    $.each(selectedLnks, function(i, lnk) {
                        lnkBody += '<div style="display:flex;align-items:center;gap:8px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:7px;padding:8px 12px;margin-bottom:6px;">' +
                            '<svg style="flex-shrink:0;color:#0891b2;" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>' +
                            '<span style="font-size:12.5px;color:#164e63;word-break:break-all;">' + escHtml(lnk) + '</span></div>';
                    });
                    html += sectionCard(svgLink, '#0891b2', 'Liên kết nội bộ sẽ bổ sung vào cuối bài (' + selectedLnks.length + ')', lnkBody);
                }

                if (!applyMeta && selectedKws.length === 0 && selectedHds.length === 0 && selectedLnks.length === 0) {
                    html = '<div style="background:#fff;border-radius:12px;padding:28px 20px;text-align:center;border:1px solid #fef3c7;">' +
                        '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:10px;"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>' +
                        '<div style="font-size:13.5px;font-weight:600;color:#92400e;margin-bottom:4px;">Không có thay đổi nào được chọn</div>' +
                        '<div style="font-size:12px;color:#b45309;">Hãy chọn ít nhất một mục trước khi xem trước hoặc áp dụng.</div></div>';
                }

                $('#wpaap-preview-body').html(html);
                $('#wpaap-post-seo-preview-modal').css('display', 'flex');
            });

            // Close preview modal
            $('#wpaap-preview-close-btn, #wpaap-preview-close-btn2').on('click', function() {
                $('#wpaap-post-seo-preview-modal').css('display', 'none');
            });
            $('#wpaap-post-seo-preview-modal').on('click', function(e) {
                if ($(e.target).is('#wpaap-post-seo-preview-modal')) {
                    $(this).css('display', 'none');
                }
            });

            // Apply changes
            $('#wpaap-apply-changes-btn').on('click', function() {
                if (!_wpaap_current_scan_data || !_wpaap_current_post_id) return;
                var $btn = $(this);
                var applyMeta = $('#wpaap-apply-meta-cb').is(':checked') ? 1 : 0;
                var metaDesc = $('#wpaap-meta-desc-textarea').val();
                var focusKw = _wpaap_current_scan_data.focus_keyword || '';

                var selectedKws = [];
                $('.wpaap-kw-cb:checked').each(function() { selectedKws.push($(this).val()); });

                var selectedHds = [];
                $('.wpaap-hd-cb:checked').each(function() { selectedHds.push($(this).val()); });

                var selectedLnks = [];
                $('.wpaap-lnk-cb:checked').each(function() { selectedLnks.push($(this).val()); });

                $btn.prop('disabled', true).text('Đang áp dụng…');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    timeout: 60000,
                    data: {
                        action: 'wpaap_ai_apply_post_seo',
                        nonce: _wpaap_apply_nonce,
                        post_id: _wpaap_current_post_id,
                        apply_meta: applyMeta,
                        meta_description: metaDesc,
                        focus_keyword: focusKw,
                        keywords: JSON.stringify(selectedKws),
                        headings: JSON.stringify(selectedHds),
                        links: JSON.stringify(selectedLnks)
                    },
                    success: function(resp) {
                        $btn.prop('disabled', false).text(whpSeoAiI18n.applyChanges);
                        $('#wpaap-post-seo-result-modal').css('display', 'none');
                        if (resp && resp.success && resp.data) {
                            var editUrl = resp.data.edit_url || '';
                            var msg = resp.data.message || 'Đã áp dụng thành công!';
                            wpaapShowApplyResult('success', msg, editUrl);
                        } else {
                            var errMsg = (resp && resp.data && resp.data.message) ? resp.data.message : 'Đã xảy ra lỗi khi áp dụng.';
                            wpaapShowApplyResult('error', errMsg, '');
                        }
                    },
                    error: function(xhr) {
                        $btn.prop('disabled', false).text(whpSeoAiI18n.applyChanges);
                        wpaapShowApplyResult('error', 'Lỗi kết nối khi áp dụng! (HTTP ' + xhr.status + ')', '');
                    }
                });
            });

            // ── Apply-result custom modal ──────────────────────────────────
            function wpaapShowApplyResult(type, msg, editUrl) {
                var isOk = (type === 'success');
                var iconBg  = isOk ? 'linear-gradient(135deg,#16a34a,#15803d)' : 'linear-gradient(135deg,#ef4444,#dc2626)';
                var iconSvg = isOk
                    ? '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>'
                    : '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
                var iconWrapBg = isOk ? 'linear-gradient(160deg,#f0fdf4,#fff)' : 'linear-gradient(160deg,#fef2f2,#fff)';
                var iconShadow = isOk ? '0 6px 20px rgba(22,163,74,0.32)' : '0 6px 20px rgba(239,68,68,0.28)';
                var titleColor = isOk ? '#15803d' : '#dc2626';
                var title      = isOk ? 'Áp dụng thành công!' : 'Có lỗi xảy ra';

                $('#wpaap-apply-result-icon-wrap').css('background', iconWrapBg);
                $('#wpaap-apply-result-icon').css({'background': iconBg, 'box-shadow': iconShadow}).html(iconSvg);
                $('#wpaap-apply-result-title').css('color', titleColor).text(title);
                $('#wpaap-apply-result-msg').text(msg);
                if (isOk) {
                    $('#wpaap-apply-result-note').show();
                } else {
                    $('#wpaap-apply-result-note').hide();
                }
                if (editUrl) {
                    $('#wpaap-apply-result-edit-link').attr('href', editUrl).show();
                } else {
                    $('#wpaap-apply-result-edit-link').hide();
                }
                $('#wpaap-apply-result-modal').css('display', 'flex');
            }

            $('#wpaap-apply-result-close').on('click', function() {
                $('#wpaap-apply-result-modal').css('display', 'none');
            });
            $('#wpaap-apply-result-modal').on('click', function(e) {
                if ($(e.target).is('#wpaap-apply-result-modal')) $(this).css('display', 'none');
            });

        });
    </script>
<?php
}

// 4. Hàm xử lý AJAX gửi dữ liệu cho AI
add_action('wp_ajax_wpaap_ai_seo_scan', 'wpaap_ajax_ai_seo_scan_handler');
function wpaap_ajax_ai_seo_scan_handler()
{
    check_ajax_referer('wpaap_seo_nonce', 'nonce');

    // Kiểm tra kết nối AI trước mọi thứ — không cho bypass qua try/catch
    $ai_any_connected = false;
    if ( function_exists( 'wpaap_is_provider_connected' ) ) {
        foreach ( ['google', 'anthropic', 'openai'] as $_prov ) {
            if ( wpaap_is_provider_connected( $_prov ) ) {
                $ai_any_connected = true;
                break;
            }
        }
    }
    if ( ! $ai_any_connected ) {
        wp_send_json_error( array( 'message' => __('Chưa có nhà cung cấp AI nào được kết nối. Vui lòng cấu hình API Key tại tab AI Kết nối.', 'whp') ) );
    }

    $ai_model = get_option('wpaap_default_ai_model', 'gemini-2.5-flash');
    $scan_type = isset($_POST['scan_type']) ? sanitize_text_field($_POST['scan_type']) : 'all';
    $term_id = isset($_POST['term_id']) ? sanitize_text_field($_POST['term_id']) : '';

    // Tăng thời gian phản hồi PHP để chờ AI
    if (function_exists('set_time_limit')) {
        @set_time_limit(300);
    }
    @ini_set('memory_limit', '256M');

    // Ghi đè timeout của wp_remote_post
    $timeout_filter = function ($args) {
        $args['timeout'] = 300;
        return $args;
    };
    add_filter('http_request_args', $timeout_filter, 999);

    $curl_filter = function( $handle ) {
        curl_setopt( $handle, CURLOPT_TIMEOUT, 300 );
        curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 300 );
    };
    add_action( 'http_api_curl', $curl_filter, 999 );

    try {
        // 1. Chạy lại quét để lấy dữ liệu mới nhất
        $seo_data = wpaap_get_seo_issues();
        $content_stats = wpaap_get_content_seo_stats($scan_type, $term_id);

        // 2. Chuyển đổi dữ liệu thành prompt gửi cho AI
        $issues_text = "Website SEO Report (Score: " . $seo_data['score'] . "/100):\n";
        foreach ($seo_data['items'] as $item) {
            $issues_text .= "- [" . strtoupper($item['level']) . "] " . $item['title'] . ": " . strip_tags($item['desc']) . "\n";
        }
        
        $issues_text .= "\n\nContent SEO Statistics (Analyzed " . $content_stats['total'] . " latest posts/pages/products):\n";
        $issues_text .= "- Posts missing Meta Description: " . $content_stats['missing_meta'] . "\n";
        $issues_text .= "- Posts missing Focus Keyword: " . $content_stats['missing_keyword'] . "\n";
        $issues_text .= "- Posts with Thin Content (< 300 words): " . $content_stats['thin_content'] . "\n";
        $issues_text .= "- SEO Optimized Posts: " . $content_stats['good'] . "\n";

        $is_en = strpos(get_locale(), 'en') === 0;
        $lang  = $is_en ? 'English' : 'Vietnamese';

        if ($is_en) {
            $seo_template_p1 = "
    PART 1: For EACH issue listed in the report (including SEO plugins), output this exact HTML:
    <div class='ai-issue-box'>
        <h4><span class='dashicons dashicons-warning' style='color: #d63638; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Issue Name or Risk / SEO Plugin Name]</h4>
        <p>[Detailed explanation of why this issue is dangerous OR advanced configuration tips if it's an SEO plugin]</p>
        <div class='ai-fix-steps'>
            <strong>How to Fix / Configure:</strong>
            <ul>
                <li>[Step 1]</li>
                <li>[Step 2]</li>
            </ul>
        </div>
    </div>";
            $seo_template_p2 = "
    PART 2: Provide specific advice regarding the 'Content SEO Statistics'. If there are many posts missing meta descriptions, keywords, or having thin content, output an analysis box using this exact HTML structure with the 'warning' class. If the stats are good, use the 'success' class to praise them.
    <div class='ai-issue-box [warning/success]'>
        <h4><span class='dashicons dashicons-chart-bar' style='color: #3858e9; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Content Status Analysis / Content Quality Assessment]</h4>
        <p>[Detailed evaluation based on post statistics]</p>
        <div class='ai-fix-steps'>
            <strong>Fix / Maintenance Strategy:</strong>
            <ul>
                <li>[Step 1]</li>
                <li>[Step 2]</li>
            </ul>
        </div>
    </div>";
            $seo_template_p3 = "
    PART 3: After addressing the report's issues and content stats, provide 1 or 2 ADDITIONAL overall WordPress recommendations (e.g., advanced SEO strategies, internal linking tips) that were NOT mentioned in the report. Output them using this exact HTML structure with the 'info' class:
    <div class='ai-issue-box info'>
        <h4><span class='dashicons dashicons-lightbulb' style='color: #f0b849; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Advanced SEO Recommendation Name]</h4>
        <p>[Explanation of why the website needs this feature]</p>
        <div class='ai-fix-steps'>
            <strong>Implementation Guide:</strong>
            <ul>
                <li>[Step 1]</li>
                <li>[Step 2]</li>
            </ul>
        </div>
    </div>";
        } else {
            $seo_template_p1 = "
    PART 1: For EACH issue listed in the report (including SEO plugins), output this exact HTML:
    <div class='ai-issue-box'>
        <h4><span class='dashicons dashicons-warning' style='color: #d63638; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Tên lỗi hoặc Rủi ro / Tên Plugin SEO]</h4>
        <p>[Giải thích chi tiết vì sao lỗi này nguy hiểm HOẶC đưa ra đánh giá/gợi ý cấu hình nâng cao nếu là plugin SEO]</p>
        <div class='ai-fix-steps'>
            <strong>Hướng dẫn khắc phục/cấu hình:</strong>
            <ul>
                <li>[Bước 1]</li>
                <li>[Bước 2]</li>
            </ul>
        </div>
    </div>";
            $seo_template_p2 = "
    PART 2: Provide specific advice regarding the 'Content SEO Statistics'. If there are many posts missing meta descriptions, keywords, or having thin content, output an analysis box using this exact HTML structure with the 'warning' class. If the stats are good, use the 'success' class to praise them.
    <div class='ai-issue-box [warning/success]'>
        <h4><span class='dashicons dashicons-chart-bar' style='color: #3858e9; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Phân Tích Tình Trạng Nội Dung / Đánh Giá Chất Lượng Nội Dung]</h4>
        <p>[Đánh giá chi tiết dựa trên số liệu thống kê bài viết]</p>
        <div class='ai-fix-steps'>
            <strong>Chiến lược khắc phục/duy trì:</strong>
            <ul>
                <li>[Bước 1]</li>
                <li>[Bước 2]</li>
            </ul>
        </div>
    </div>";
            $seo_template_p3 = "
    PART 3: After addressing the report's issues and content stats, provide 1 or 2 ADDITIONAL overall WordPress recommendations (e.g., advanced SEO strategies, internal linking tips) that were NOT mentioned in the report. Output them using this exact HTML structure with the 'info' class:
    <div class='ai-issue-box info'>
        <h4><span class='dashicons dashicons-lightbulb' style='color: #f0b849; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Tên đề xuất SEO nâng cao]</h4>
        <p>[Giải thích vì sao website cần tính năng này]</p>
        <div class='ai-fix-steps'>
            <strong>Hướng dẫn triển khai:</strong>
            <ul>
                <li>[Bước 1]</li>
                <li>[Bước 2]</li>
            </ul>
        </div>
    </div>";
        }

        $system_prompt = "You are a Cyber Security and SEO Advisor. Read the SEO report (Score: " . $seo_data['score'] . "/100) and provide ALL advice strictly in {$lang}. Do NOT mix languages.
    CRITICAL: You MUST strictly output ONLY clean HTML using the exact structure below. Do NOT use markdown. Do NOT use standard plain text paragraphs.
{$seo_template_p1}
{$seo_template_p2}
{$seo_template_p3}

    Report to analyze:
    " . $issues_text;

        $provider_models = [
            'google' => ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash-lite-001', 'gemini-2.5-flash-lite'],
            'anthropic' => ['claude-3-5-sonnet', 'claude-3-opus', 'claude-3-haiku'],
            'openai' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo']
        ];
        
        $selected_provider = '';
        foreach ( $provider_models as $prov => $mods ) {
            if ( in_array( $ai_model, $mods ) ) {
                $selected_provider = $prov;
                break;
            }
        }
        
        $models_to_try = [];
        // Chỉ thêm mô hình từ các provider đã được kết nối
        foreach ( $provider_models as $prov => $mods ) {
            if ( function_exists( 'wpaap_is_provider_connected' ) && wpaap_is_provider_connected( $prov ) ) {
                if ( $prov === $selected_provider ) {
                    array_unshift( $models_to_try, $ai_model );
                    foreach ( $mods as $m ) {
                        if ( $m !== $ai_model ) {
                            $models_to_try[] = $m;
                        }
                    }
                } else {
                    $models_to_try = array_merge( $models_to_try, $mods );
                }
            }
        }
        
        if ( empty( $models_to_try ) ) {
            wp_send_json_error( array( 'message' => __('Chưa có nhà cung cấp AI nào được kết nối. Vui lòng cấu hình API Key tại trang AI Kết nối.', 'whp') ) );
        }
        
        $models_to_try = array_values( array_unique( $models_to_try ) );

        $ai_response = '';
        $last_error_msg = '';

        foreach ($models_to_try as $model) {
            $current_response = '';
            
            // Gọi trực tiếp API
            if ( function_exists( 'wpaap_call_ai_api_direct' ) ) {
                $direct_result = wpaap_call_ai_api_direct( $model, $system_prompt );
                if ( ! is_wp_error( $direct_result ) ) {
                    $current_response = $direct_result;
                } else {
                    $last_error_msg = $direct_result->get_error_message();
                }
            } else {
                wp_send_json_error(__('Hệ thống kết nối AI trực tiếp chưa được thiết lập.', 'whp'));
            }
            
            if (!empty($current_response)) {
                $ai_response = $current_response;
                break;
            }
        }

        // Nếu tất cả model đều thất bại
        if (empty($ai_response)) {
            $user_friendly_err = $last_error_msg;
            if (strpos(strtolower($last_error_msg), '429') !== false || strpos(strtolower($last_error_msg), 'too many requests') !== false || strpos(strtolower($last_error_msg), 'quota exceeded') !== false) {
                $user_friendly_err = __('Toàn bộ tài khoản API của bạn đã hết sạch lượt truy vấn miễn phí (Quota Limit) trong chu kỳ này. Vui lòng nghỉ ngơi khoảng 1-2 phút để Google hồi phục lại số lượt dùng, hoặc chủ động chọn một Mô hình AI khác ở ô Lựa chọn Model bên trên!', 'whp');
            }
            $ai_response = "<div style='background: #fcf0f1; border-left: 4px solid #d63638; padding: 15px 20px; color: #d63638; border-radius: 4px; margin-top: 10px;'>
                                <strong style='font-size: 15px;'>" . esc_html__('Không thể hoàn thành phân tích do quá tải AI!', 'whp') . "</strong><br>
                                <span style='font-size: 14px; margin-top: 8px; display: block;'>" . esc_html__('Hệ thống đã tự động thử tất cả các Mô hình AI dự phòng (Fallback) nhưng đều bị từ chối.', 'whp') . "<br>
                                <br><strong>Gợi ý:</strong> " . esc_html($user_friendly_err) . "</span>
                            </div>";
        }

        // Đảm bảo AI Response là một chuỗi an toàn
        if (! is_string($ai_response)) {
            if (is_wp_error($ai_response)) {
                $err_msg = $ai_response->get_error_message();
                if (strpos($err_msg, '503') !== false || strpos($err_msg, 'high demand') !== false) {
                    $ai_response = "<p style='color: #d63638;'><strong>" . esc_html__('Hệ thống AI đang quá tải (Lỗi 503):', 'whp') . "</strong> " . esc_html__('Hiện tại máy chủ AI đang xử lý quá nhiều yêu cầu. Bạn vui lòng đợi khoảng 1-2 phút và bấm nút Quét lại nhé!', 'whp') . "</p>";
                } else {
                    $ai_response = "<p style='color: #d63638;'><strong>Lỗi API:</strong> " . esc_html($err_msg) . "</p>";
                }
            } else {
                $ai_response = wp_json_encode($ai_response);
            }
        }

        // Dọn dẹp markdown code blocks nếu AI trả về lỗi định dạng
        $ai_response = trim($ai_response);
        $ai_response = preg_replace('/^```(?:html)?\s*/i', '', $ai_response);
        $ai_response = preg_replace('/\s*```$/', '', $ai_response);

        if (empty($ai_response) || $ai_response === '""' || $ai_response === '[]') {
            $ai_response = "<p style='color: #d63638;'>" . esc_html__('Không thể kết nối với AI lúc này hoặc dữ liệu trả về rỗng. Vui lòng kiểm tra lại thiết lập mã khóa API trong trang Kết nối AI.', 'whp') . "</p>";
        }

        // Lưu thông tin lần quét cuối
        update_option('wpaap_seo_last_scan_date',   current_time('d/m/Y H:i'));
        update_option('wpaap_seo_last_scan_posts',  $content_stats['total']);
        update_option('wpaap_seo_last_scan_errors', $content_stats['missing_meta'] + $content_stats['missing_keyword']);

        wp_send_json_success([
            'ai_advice' => wp_kses_post($ai_response),
            'content_stats' => isset($content_stats) ? $content_stats : null
        ]);
    } catch (\Throwable $e) {
        // Bắt lỗi hệ thống để không crash AJAX
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) { error_log( 'WPAAP AI Security Scan Error: ' . $e->getMessage() ); }
        wp_send_json_error('Lỗi hệ thống: ' . $e->getMessage());
    } finally {
        // Đảm bảo gỡ filter trong mọi trường hợp
        if ( isset( $timeout_filter ) ) {
            remove_filter( 'http_request_args', $timeout_filter, 999 );
        }
        if ( isset( $curl_filter ) ) {
            remove_action( 'http_api_curl', $curl_filter, 999 );
        }
    }
}

// ============================================================
// AJAX Handler: AI Quét SEO từng bài viết
// ============================================================
add_action('wp_ajax_wpaap_ai_scan_post_seo', 'wpaap_ajax_ai_scan_post_seo_handler');
function wpaap_ajax_ai_scan_post_seo_handler()
{
    check_ajax_referer('wpaap_scan_post_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Bạn không có quyền thực hiện hành động này.', 'whp')]);
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => __('Post ID không hợp lệ hoặc bạn không có quyền chỉnh sửa bài viết này.', 'whp')]);
    }

    $post = get_post($post_id);
    if (!$post) {
        wp_send_json_error(['message' => __('Không tìm thấy bài viết.', 'whp')]);
    }

    // Kiểm tra kết nối AI
    $ai_any_connected = false;
    if (function_exists('wpaap_is_provider_connected')) {
        foreach (['google', 'anthropic', 'openai'] as $_prov) {
            if (wpaap_is_provider_connected($_prov)) {
                $ai_any_connected = true;
                break;
            }
        }
    }
    if (!$ai_any_connected) {
        wp_send_json_error(['message' => __('Chưa có nhà cung cấp AI nào được kết nối.', 'whp')]);
    }

    if (function_exists('set_time_limit')) {
        @set_time_limit(120);
    }

    // Thu thập dữ liệu bài viết
    $title       = get_the_title($post_id);
    $raw_content = wp_strip_all_tags($post->post_content);
    $content_excerpt = mb_substr($raw_content, 0, 3000);
    $word_count  = str_word_count(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $raw_content));

    // Meta description hiện tại (Yoast / RankMath / generic)
    $current_meta = '';
    $yoast_meta   = get_post_meta($post_id, '_yoast_wpseo_metadesc', true);
    $rm_meta      = get_post_meta($post_id, 'rank_math_description', true);
    if (!empty($yoast_meta)) {
        $current_meta = $yoast_meta;
    } elseif (!empty($rm_meta)) {
        $current_meta = $rm_meta;
    } else {
        $current_meta = get_post_meta($post_id, '_wpaap_seo_metadesc', true);
    }

    // Focus keyword hiện tại
    $current_keyword = '';
    $yoast_kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
    $rm_kw    = get_post_meta($post_id, 'rank_math_focus_keyword', true);
    if (!empty($yoast_kw)) {
        $current_keyword = $yoast_kw;
    } elseif (!empty($rm_kw)) {
        $current_keyword = $rm_kw;
    }

    // Categories & tags
    $categories   = implode(', ', wp_get_post_terms($post_id, 'category', ['fields' => 'names']));
    $tags         = implode(', ', wp_get_post_terms($post_id, 'post_tag', ['fields' => 'names']));

    // Internal link count
    preg_match_all('/<a\s[^>]*href=["\']' . preg_quote(home_url(), '/') . '/i', $post->post_content, $lm);
    $internal_link_count = count($lm[0]);

    // Heading structure
    preg_match_all('/<h([2-3])[^>]*>(.*?)<\/h\1>/is', $post->post_content, $hm);
    $headings_list = [];
    if (!empty($hm[2])) {
        foreach ($hm[2] as $i => $htext) {
            $headings_list[] = 'H' . $hm[1][$i] . ': ' . wp_strip_all_tags($htext);
        }
    }
    $headings_str = !empty($headings_list) ? implode("\n", $headings_list) : '(chưa có heading H2/H3)';

    // Build prompt
    $prompt = 'Bạn là chuyên gia SEO. Phân tích bài viết WordPress sau và trả về JSON (không có markdown, chỉ JSON thuần):

Tiêu đề: ' . $title . '
Số từ: ' . $word_count . '
Danh mục: ' . ($categories ?: 'N/A') . '
Thẻ: ' . ($tags ?: 'N/A') . '
Meta Description hiện tại: ' . ($current_meta ?: '(trống)') . '
Từ khóa trọng tâm hiện tại: ' . ($current_keyword ?: '(trống)') . '
Số liên kết nội bộ: ' . $internal_link_count . '
Cấu trúc heading:
' . $headings_str . '

Nội dung (tối đa 3000 ký tự đầu):
' . $content_excerpt . '

Hãy trả về JSON theo đúng cấu trúc sau (không thêm text nào ngoài JSON):
{
  "seo_score": <số nguyên 0-100 đánh giá SEO hiện tại>,
  "focus_keyword": "<từ khóa trọng tâm bạn phát hiện trong nội dung>",
  "suggested_keywords": ["<kw1>", "<kw2>", "<kw3>"],
  "meta_description": "<meta description đề xuất, tối đa 160 ký tự, tiếng Việt>",
  "suggested_headings": ["<h2 heading 1>", "<h2 heading 2>"],
  "suggested_internal_links": ["<url hoặc slug bài viết liên quan>"],
  "issues": ["<vấn đề SEO cần khắc phục 1>", "<vấn đề 2>"],
  "estimated_score_after": <số nguyên 0-100 điểm SEO dự kiến sau khi áp dụng>
}';

    $ai_model = get_option('wpaap_default_ai_model', 'gemini-2.5-flash');
    $provider_models = [
        'google'    => ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.5-flash-lite'],
        'anthropic' => ['claude-3-5-sonnet', 'claude-3-opus', 'claude-3-haiku'],
        'openai'    => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo'],
    ];

    $selected_provider = '';
    foreach ($provider_models as $prov => $mods) {
        if (in_array($ai_model, $mods)) {
            $selected_provider = $prov;
            break;
        }
    }

    $models_to_try = [];
    foreach ($provider_models as $prov => $mods) {
        if (function_exists('wpaap_is_provider_connected') && wpaap_is_provider_connected($prov)) {
            if ($prov === $selected_provider) {
                array_unshift($models_to_try, $ai_model);
                foreach ($mods as $m) {
                    if ($m !== $ai_model) $models_to_try[] = $m;
                }
            } else {
                $models_to_try = array_merge($models_to_try, $mods);
            }
        }
    }
    $models_to_try = array_values(array_unique($models_to_try));

    if (empty($models_to_try)) {
        wp_send_json_error(['message' => __('Chưa có nhà cung cấp AI nào được kết nối.', 'whp')]);
    }

    $ai_response = '';
    $last_error  = '';
    foreach ($models_to_try as $model) {
        if (!function_exists('wpaap_call_ai_api_direct')) continue;
        $result = wpaap_call_ai_api_direct($model, $prompt, ['response_json' => true]);
        if (!is_wp_error($result) && !empty($result)) {
            $ai_response = $result;
            break;
        } elseif (is_wp_error($result)) {
            $last_error = $result->get_error_message();
        }
    }

    if (empty($ai_response)) {
        wp_send_json_error(['message' => __('AI không phản hồi.', 'whp') . ' ' . $last_error]);
    }

    // Parse JSON
    if (is_string($ai_response)) {
        $ai_response = trim($ai_response);
        $ai_response = preg_replace('/^```(?:json)?\s*/i', '', $ai_response);
        $ai_response = preg_replace('/\s*```$/', '', $ai_response);
        // Extract JSON object if wrapped in text
        if (preg_match('/\{[\s\S]*\}/u', $ai_response, $jm)) {
            $ai_response = $jm[0];
        }
        $parsed = json_decode($ai_response, true);
    } elseif (is_array($ai_response)) {
        $parsed = $ai_response;
    } else {
        $parsed = null;
    }

    if (!is_array($parsed)) {
        wp_send_json_error(['message' => __('AI trả về dữ liệu không hợp lệ. Vui lòng thử lại.', 'whp')]);
    }

    // Add extra context fields
    $parsed['post_title']        = $title;
    $parsed['current_meta']      = $current_meta;
    $parsed['current_keyword']   = $current_keyword;

    wp_send_json_success($parsed);
}

// ============================================================
// AJAX Handler: Áp dụng thay đổi SEO vào bài viết
// ============================================================
add_action('wp_ajax_wpaap_ai_apply_post_seo', 'wpaap_ajax_ai_apply_post_seo_handler');
function wpaap_ajax_ai_apply_post_seo_handler()
{
    check_ajax_referer('wpaap_apply_seo_nonce', 'nonce');

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error(['message' => __('Post ID không hợp lệ hoặc bạn không có quyền.', 'whp')]);
    }

    $post = get_post($post_id);
    if (!$post) {
        wp_send_json_error(['message' => __('Không tìm thấy bài viết.', 'whp')]);
    }

    // Sanitize inputs
    $apply_meta      = !empty($_POST['apply_meta']) ? 1 : 0;
    $meta_desc       = isset($_POST['meta_description']) ? sanitize_textarea_field(wp_unslash($_POST['meta_description'])) : '';
    $focus_keyword   = isset($_POST['focus_keyword'])    ? sanitize_text_field(wp_unslash($_POST['focus_keyword']))   : '';
    $keywords_raw    = isset($_POST['keywords'])  ? wp_unslash($_POST['keywords'])  : '[]';
    $headings_raw    = isset($_POST['headings'])  ? wp_unslash($_POST['headings'])  : '[]';
    $links_raw       = isset($_POST['links'])     ? wp_unslash($_POST['links'])     : '[]';

    $keywords = json_decode($keywords_raw, true);
    $headings = json_decode($headings_raw, true);
    $links    = json_decode($links_raw, true);

    if (!is_array($keywords)) $keywords = [];
    if (!is_array($headings)) $headings = [];
    if (!is_array($links))    $links    = [];

    $keywords = array_map('sanitize_text_field', $keywords);
    $headings = array_map('sanitize_text_field', $headings);

    // Backup: create WP revision first
    wp_save_post_revision($post_id);

    // Detect active SEO plugins
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $is_yoast  = is_plugin_active('wordpress-seo/wp-seo.php');
    $is_rm     = is_plugin_active('seo-by-rank-math/rank-math.php');

    // Update meta description
    if ($apply_meta && !empty($meta_desc)) {
        if ($is_yoast) update_post_meta($post_id, '_yoast_wpseo_metadesc', $meta_desc);
        if ($is_rm)    update_post_meta($post_id, 'rank_math_description', $meta_desc);
        update_post_meta($post_id, '_wpaap_seo_metadesc', $meta_desc);
    }

    // Update focus keyword
    if (!empty($focus_keyword)) {
        if ($is_yoast) update_post_meta($post_id, '_yoast_wpseo_focuskw', $focus_keyword);
        if ($is_rm)    update_post_meta($post_id, 'rank_math_focus_keyword', $focus_keyword);
        update_post_meta($post_id, '_wpaap_seo_focus_keyword', $focus_keyword);
    }

    // Build content to append
    $content_append = '';

    if (!empty($keywords)) {
        $kw_str = implode(', ', $keywords);
        $content_append .= "\n\n<p><strong>Từ khóa liên quan:</strong> " . esc_html($kw_str) . '</p>';
    }

    foreach ($headings as $hd) {
        if (empty($hd)) continue;
        $content_append .= "\n\n<h2>" . esc_html($hd) . "</h2>\n<p>[Nội dung cần bổ sung cho phần này]</p>";
    }

    if (!empty($links)) {
        $link_parts = [];
        foreach ($links as $lnk) {
            if (is_array($lnk)) {
                $url    = isset($lnk['url'])    ? esc_url($lnk['url'])          : '';
                $anchor = isset($lnk['anchor']) ? esc_html($lnk['anchor'])      : $url;
            } else {
                $lnk_str = sanitize_text_field($lnk);
                $url    = esc_url($lnk_str);
                $anchor = $url;
            }
            if (!empty($url)) {
                $link_parts[] = '<a href="' . $url . '">' . $anchor . '</a>';
            }
        }
        if (!empty($link_parts)) {
            $content_append .= "\n\n<p><strong>Xem thêm:</strong> " . implode(' | ', $link_parts) . '</p>';
        }
    }

    // Update post content if there is anything to append
    if (!empty($content_append)) {
        $updated = wp_update_post([
            'ID'           => $post_id,
            'post_content' => $post->post_content . $content_append,
        ], true);
        if (is_wp_error($updated)) {
            wp_send_json_error(['message' => __('Lỗi khi cập nhật nội dung: ', 'whp') . $updated->get_error_message()]);
        }
    }

    wp_send_json_success([
        'message'  => __('Đã áp dụng thay đổi SEO thành công! Một bản sao lưu (revision) đã được tạo.', 'whp'),
        'edit_url' => get_edit_post_link($post_id, 'raw'),
    ]);
}

// Load-more: tải thêm bài viết cần tối ưu SEO
add_action('wp_ajax_wpaap_load_more_seo_posts', 'wpaap_ajax_load_more_seo_posts');
function wpaap_ajax_load_more_seo_posts() {
    check_ajax_referer('wpaap_seo_nonce', 'nonce');
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(['message' => __('Không có quyền.', 'whp')]);
    }

    $post_type = sanitize_text_field($_POST['post_type'] ?? 'post');
    $offset    = max(0, intval($_POST['offset'] ?? 5));
    $ai_ok     = (sanitize_text_field($_POST['ai_ok'] ?? '0') === '1');

    $allowed_types = ['post', 'page', 'product'];
    if (!in_array($post_type, $allowed_types, true)) {
        $post_type = 'post';
    }

    $posts    = wpaap_get_urgent_seo_posts($post_type, 5, $offset);
    $has_more = count($posts) >= 5;

    $html = '';
    foreach ($posts as $upost) {
        $html .= wpaap_render_urgent_post_row($upost, $ai_ok);
    }

    wp_send_json_success([
        'html'     => $html,
        'count'    => count($posts),
        'has_more' => $has_more,
    ]);
}
