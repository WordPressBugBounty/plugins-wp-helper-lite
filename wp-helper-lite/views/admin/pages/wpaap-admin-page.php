<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
// 2. Giao diện trang nhập mô tả và xử lý hành động gửi dữ liệu
function wpaap_admin_page_layout() {
    $writer_ai_ok = false;
    if ( function_exists( 'wpaap_is_provider_connected' ) ) {
        foreach ( [ 'google', 'anthropic', 'openai' ] as $_p ) {
            if ( wpaap_is_provider_connected( $_p ) ) { $writer_ai_ok = true; break; }
        }
    } else {
        $writer_ai_ok = get_option( 'wpaap_core_connected', 'no' ) === 'yes';
    }
    $message = '';
    $status = 'info';

    // Lưu API Key ảnh (Pexels / Pixabay)
    if ( isset( $_POST['wpaap_save_image_api'] ) && check_admin_referer( 'wpaap_image_api_action', 'wpaap_image_api_nonce' ) ) {
        $pexels_key  = sanitize_text_field( $_POST['wpaap_pexels_api_key'] ?? '' );
        $pixabay_key = sanitize_text_field( $_POST['wpaap_pixabay_api_key'] ?? '' );
        update_option( 'wpaap_pexels_api_key', $pexels_key );
        update_option( 'wpaap_pixabay_api_key', $pixabay_key );
        $message = __( 'Đã lưu API Key ảnh thành công!', 'whp' );
        $status  = 'success';
    }

    // Xử lý Duyệt bài nhanh
    if ( isset( $_GET['action'] ) && 'wpaap_publish' === $_GET['action'] && isset( $_GET['post_id'] ) ) {
        $publish_post_id = intval( $_GET['post_id'] );
        if ( current_user_can( 'edit_post', $publish_post_id ) ) {
            wp_update_post( array(
                'ID' => $publish_post_id,
                'post_status' => 'publish'
            ) );
            $message = __( 'Đã duyệt và đăng bài viết thành công!', 'whp' );
            $status = 'success';
        }
    }

    // Xử lý Xóa bài viết
    if ( isset( $_GET['action'] ) && 'wpaap_delete' === $_GET['action'] && isset( $_GET['post_id'] ) ) {
        $delete_post_id = intval( $_GET['post_id'] );
        if ( current_user_can( 'delete_post', $delete_post_id ) ) {
            wp_trash_post( $delete_post_id );
            $message = __( 'Đã chuyển bài viết vào thùng rác thành công!', 'whp' );
            $status = 'success';
        }
    }

    $is_connected = get_option( 'wpaap_core_connected', 'no' ) === 'yes';

    if ( ! $is_connected ) {
        $message = '<strong>' . esc_html__( 'AI Core chưa kết nối!', 'whp' ) . '</strong> ' . sprintf( __( 'Vui lòng đi tới trang <a href="%s">Kết nối AI</a> để bật kết nối trước khi tạo bài viết.', 'whp' ), esc_url( admin_url( 'admin.php?page=mb-wphelper-ai&subtab=connection' ) ) );
        $status = 'error';
    }

    // Truy vấn danh sách 5 bài viết chờ duyệt gần đây nhất từ CSDL
    $recent_posts = get_posts( array(
        'numberposts' => 6,
        'post_status' => 'pending',
        'post_type'   => 'post',
    ) );
    $has_more_posts = count( $recent_posts ) > 5;
    if ( $has_more_posts ) {
        $recent_posts = array_slice( $recent_posts, 0, 5 );
    }

    ?>
    <div class="wrap wpaap-wrap" style="margin: 20px auto 40px; padding: 0 20px;">
        <div class="wpaap-header-modern">
            <div class="wpaap-header-left">
                <div class="wpaap-header-title-row">
                    <div class="wpaap-header-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6V17H8.3V15C6.3 13.7 5 11.5 5 9a7 7 0 0 1 7-7z" fill="#fff" fill-opacity=".9"/><path d="M9 17v1a3 3 0 0 0 6 0v-1" stroke="#a78bfa" stroke-width="1.5" fill="none"/><circle cx="9.5" cy="9.5" r="1" fill="#7c3aed"/><circle cx="14.5" cy="9.5" r="1" fill="#7c3aed"/></svg>
                    </div>
                    <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e( 'AI - Tự động soạn & Đăng bài', 'whp' ); ?></h1>
                </div>
                <p><?php esc_html_e( 'Giải pháp tối ưu hóa nội dung sử dụng trí tuệ nhân tạo độc quyền cho WordPress.', 'whp' ); ?></p>
            </div>
            <div class="wpaap-header-right">
                <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                    <defs>
                        <linearGradient id="hg" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%"   stop-color="#f5f3ff" stop-opacity="0"/>
                            <stop offset="22%"  stop-color="#ede9fe" stop-opacity="0.5"/>
                            <stop offset="100%" stop-color="#ddd6fe" stop-opacity="0.85"/>
                        </linearGradient>
                        <linearGradient id="acc" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%"   stop-color="#7c3aed"/>
                            <stop offset="100%" stop-color="#a78bfa"/>
                        </linearGradient>
                        <radialGradient id="chbg" cx="40%" cy="35%" r="65%">
                            <stop offset="0%"   stop-color="#f5f3ff"/>
                            <stop offset="100%" stop-color="#ddd6fe"/>
                        </radialGradient>
                        <radialGradient id="glw" cx="50%" cy="50%" r="50%">
                            <stop offset="0%"   stop-color="#7c3aed" stop-opacity="0.1"/>
                            <stop offset="100%" stop-color="#7c3aed" stop-opacity="0"/>
                        </radialGradient>
                        <filter id="fs1" x="-25%" y="-25%" width="150%" height="150%">
                            <feDropShadow dx="0" dy="6" stdDeviation="10" flood-color="#7c3aed" flood-opacity="0.16"/>
                        </filter>
                        <filter id="fs2" x="-25%" y="-25%" width="150%" height="150%">
                            <feDropShadow dx="0" dy="4" stdDeviation="7"  flood-color="#7c3aed" flood-opacity="0.13"/>
                        </filter>
                    </defs>

                    <!-- Background -->
                    <rect width="680" height="168" fill="url(#hg)"/>
                    <circle cx="615" cy="8"   r="145" fill="url(#glw)"/>
                    <circle cx="578" cy="178" r="100" fill="#4f46e5" fill-opacity="0.04"/>

                    <!-- ── Article Card ─────────────────────────── -->
                    <g filter="url(#fs1)">
                        <rect x="178" y="16"  width="158" height="136" rx="14" fill="#ffffff"/>
                        <!-- top accent strip -->
                        <rect x="178" y="16"  width="158" height="5"   rx="7"  fill="url(#acc)"/>
                        <!-- AI-generated badge -->
                        <rect x="188" y="31"  width="68"  height="18"  rx="9"  fill="#ede9fe"/>
                        <circle cx="200" cy="40" r="5"  fill="#7c3aed"/>
                        <circle cx="200" cy="40" r="2.5" fill="#a78bfa"/>
                        <rect x="209" y="37"  width="39"  height="5"   rx="2.5" fill="#c4b5fd"/>
                        <!-- headline bar -->
                        <rect x="188" y="60"  width="138" height="8"   rx="4"  fill="#0f172a" fill-opacity="0.11"/>
                        <!-- body text lines -->
                        <rect x="188" y="76"  width="138" height="5"   rx="2.5" fill="#e2e8f0"/>
                        <rect x="188" y="87"  width="114" height="5"   rx="2.5" fill="#e2e8f0"/>
                        <rect x="188" y="98"  width="124" height="5"   rx="2.5" fill="#e2e8f0"/>
                        <!-- typing line + cursor -->
                        <rect x="188" y="109" width="70"  height="5"   rx="2.5" fill="#ddd6fe"/>
                        <rect x="260" y="109" width="3"   height="5"   rx="1.5" fill="#7c3aed" class="wpaap-cursor"/>
                        <!-- separator -->
                        <line x1="188" y1="126" x2="322" y2="126" stroke="#f1f5f9" stroke-width="1"/>
                        <!-- tag pills -->
                        <rect x="188" y="133" width="38" height="12" rx="6" fill="#ede9fe"/>
                        <rect x="230" y="133" width="46" height="12" rx="6" fill="#dbeafe"/>
                        <rect x="280" y="133" width="36" height="12" rx="6" fill="#f0fdf4"/>
                    </g>

                    <!-- ── Flow arrow  AI chip → card ──────────── -->
                    <path d="M468 86 C442 64, 402 61, 350 85" stroke="#a78bfa" stroke-width="1.5" stroke-dasharray="5 4" fill="none" stroke-opacity="0.8"/>
                    <path d="M355 77 L344 86 L357 90" stroke="#a78bfa" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.8"/>

                    <!-- ── AI Chip Circle ───────────────────────── -->
                    <g filter="url(#fs2)">
                        <!-- outer glow halo -->
                        <circle cx="534" cy="86" r="68" fill="#f5f3ff" fill-opacity="0.55"/>
                        <!-- main body -->
                        <circle cx="534" cy="86" r="57" fill="url(#chbg)" stroke="#ddd6fe" stroke-width="1.5"/>
                        <!-- dashed orbit ring -->
                        <circle cx="534" cy="86" r="41" fill="none" stroke="#c4b5fd" stroke-width="1.5" stroke-dasharray="5 4"/>
                        <!-- inner fill -->
                        <circle cx="534" cy="86" r="25" fill="#ede9fe" fill-opacity="0.65"/>
                        <!-- 4-pointed sparkle star -->
                        <path d="M534 69 L537 82 L551 86 L537 90 L534 103 L531 90 L517 86 L531 82 Z" fill="#7c3aed" fill-opacity="0.82"/>
                        <!-- center white dot -->
                        <circle cx="534" cy="86" r="5" fill="#ffffff"/>
                        <!-- cardinal dots on orbit -->
                        <circle cx="534" cy="45"  r="3" fill="#7c3aed" fill-opacity="0.32"/>
                        <circle cx="575" cy="86"  r="3" fill="#7c3aed" fill-opacity="0.32"/>
                        <circle cx="534" cy="127" r="3" fill="#7c3aed" fill-opacity="0.32"/>
                        <circle cx="493" cy="86"  r="3" fill="#7c3aed" fill-opacity="0.32"/>
                    </g>

                    <!-- ── Decorative sparkles ─────────────────── -->
                    <path d="M442 34 L444 41 L451 43 L444 45 L442 52 L440 45 L433 43 L440 41 Z" fill="#a78bfa" fill-opacity="0.55"/>
                    <path d="M620 42 L622 48 L628 50 L622 52 L620 58 L618 52 L612 50 L618 48 Z" fill="#a78bfa" fill-opacity="0.42"/>
                    <path d="M632 126 L633.5 130 L638 131.5 L633.5 133 L632 137 L630.5 133 L626 131.5 L630.5 130 Z" fill="#7c3aed" fill-opacity="0.3"/>
                    <path d="M158 98 L159.5 102 L164 103.5 L159.5 105 L158 109 L156.5 105 L152 103.5 L156.5 102 Z" fill="#7c3aed" fill-opacity="0.18"/>

                    <!-- ── Floating dots ──────────────────────── -->
                    <circle cx="162"  cy="48"  r="4" fill="#7c3aed" fill-opacity="0.1"/>
                    <circle cx="418"  cy="148" r="4" fill="#a78bfa" fill-opacity="0.2"/>
                    <circle cx="617"  cy="108" r="5" fill="#4f46e5" fill-opacity="0.1"/>
                    <circle cx="648"  cy="52"  r="6" fill="#7c3aed" fill-opacity="0.07"/>
                </svg>
            </div>
        </div>

        <?php if ( ! empty( $message ) ) : ?>
            <div class="wpaap-notice wpaap-notice-<?php echo $status; ?>" style="margin-bottom: 25px;"><?php echo $message; ?></div>
        <?php endif; ?>
        <div id="wpaap_inline_notice" style="display:none; margin-bottom:25px;"></div>

        <div class="wpaap-container">
            <?php if ( ! $is_connected ) : ?>
                <div class="wpaap-card-modern" style="border-top:4px solid #d63638;padding:24px;text-align:center;grid-column:1/-1;">
                    <span class="dashicons dashicons-warning" style="font-size:48px;width:48px;height:48px;color:#d63638;margin-bottom:15px;display:block;margin-left:auto;margin-right:auto;"></span>
                    <h2 style="margin:0 0 10px 0;color:#d63638;"><?php esc_html_e( 'Chưa kết nối AI', 'whp' ); ?></h2>
                    <p style="font-size:14px;color:#646970;max-width:500px;margin:0 auto 20px auto;line-height:1.5;">
                        <?php esc_html_e( 'Bạn cần cấu hình mã khóa API trong phần Kết nối AI trước khi sử dụng tính năng này.', 'whp' ); ?>
                    </p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=connection')); ?>" class="wpaap-btn-primary-gradient" style="text-decoration:none;display:inline-flex;align-items:center;gap:8px;padding:10px 20px;border-radius:8px;">
                        <span class="dashicons dashicons-admin-plugins" style="font-size:16px;width:16px;height:16px;margin-top:1px;"></span> <?php esc_html_e( 'Sang trang Kết nối AI', 'whp' ); ?>
                    </a>
                </div>
            <?php else : ?>

            <!-- ── Row 1: Form soạn bài + Sidebar hướng dẫn ─── -->
            <div class="wpaap-writer-layout">

                <!-- Main: Biên soạn bài viết mới -->
                <div class="wpaap-writer-main">
                    <div class="wpaap-card">
                        <div class="wpaap-card-header">
                            <div class="wpaap-section-header-row">
                                <div class="wpaap-section-icon-box" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);box-shadow:0 4px 10px rgba(124,58,237,0.3);">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </div>
                                <h2 style="margin:0;font-size:18px;font-weight:700;color:#0f172a;letter-spacing:-0.3px;"><?php esc_html_e( 'Biên soạn bài viết mới', 'whp' ); ?></h2>
                            </div>
                        </div>

                        <form id="wpaap_form_generate" onsubmit="return false;">

                            <div class="wpaap-form-group">
                                <label for="wpaap_user_prompt" style="font-weight:600;font-size:15px;margin-bottom:10px;display:inline-flex;align-items:center;gap:7px;">
                                    <span class="wpaap-label-icon" style="background:#eff2fe;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </span>
                                    <?php esc_html_e( 'Mô tả chi tiết ý tưởng hoặc chủ đề bài viết:', 'whp' ); ?>
                                </label>
                                <textarea id="wpaap_user_prompt" name="wpaap_user_prompt" rows="8" placeholder="<?php esc_attr_e( 'Ví dụ: Viết một bài viết chuẩn SEO giới thiệu về những điểm mới của WordPress 7.0...', 'whp' ); ?>" class="wpaap-modern-textarea"></textarea>
                            </div>

                            <!-- Chọn độ dài bài viết -->
                            <div class="wpaap-form-group">
                                <label style="font-weight:600;font-size:14px;margin-bottom:10px;display:inline-flex;align-items:center;gap:7px;">
                                    <span class="wpaap-label-icon" style="background:#fef3c7;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>
                                    </span>
                                    <?php esc_html_e( 'Độ dài bài viết:', 'whp' ); ?>
                                </label>
                                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                                    <?php
                                    $length_opts = [
                                        'short'  => [ 'label' => __( 'Ngắn', 'whp' ),   'sub' => __( '~700-900 từ', 'whp' ),   'icon' => '▪', 'color' => '#0891b2', 'bg' => '#ecfeff', 'border' => '#a5f3fc' ],
                                        'medium' => [ 'label' => __( 'Chuẩn', 'whp' ),  'sub' => __( '~1200-1500 từ', 'whp' ), 'icon' => '▪▪', 'color' => '#16a34a', 'bg' => '#f0fdf4', 'border' => '#86efac' ],
                                        'long'   => [ 'label' => __( 'Dài', 'whp' ),    'sub' => __( '~1800-2200 từ', 'whp' ), 'icon' => '▪▪▪', 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'border' => '#c4b5fd' ],
                                    ];
                                    foreach ( $length_opts as $val => $opt ) :
                                        $checked = $val === 'medium' ? 'checked' : '';
                                    ?>
                                    <label style="flex:1;min-width:120px;cursor:pointer;display:flex;align-items:center;gap:10px;background:<?php echo esc_attr($opt['bg']); ?>;border:1.5px solid <?php echo esc_attr($opt['border']); ?>;border-radius:10px;padding:10px 14px;transition:all 0.15s;" class="wpaap-length-label" data-val="<?php echo esc_attr($val); ?>">
                                        <input type="radio" name="wpaap_article_length" value="<?php echo esc_attr($val); ?>" <?php echo $checked; ?> style="accent-color:#16a34a;width:16px;height:16px;flex-shrink:0;">
                                        <div>
                                            <div style="font-size:13.5px;font-weight:700;color:<?php echo esc_attr($opt['color']); ?>;"><?php echo esc_html($opt['label']); ?></div>
                                            <div style="font-size:11.5px;color:#64748b;margin-top:1px;"><?php echo esc_html($opt['sub']); ?></div>
                                        </div>
                                    </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="wpaap-form-group" style="display: flex; gap: 20px; flex-wrap: wrap;">
                                <div class="wpaap-modern-box" style="flex: 1; min-width: 250px;">
                                    <label style="font-weight:600;margin-bottom:10px;display:inline-flex;align-items:center;gap:7px;">
                                        <span class="wpaap-label-icon" style="background:#f5f3ff;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                        </span>
                                        <?php esc_html_e( 'Chọn Chuyên mục:', 'whp' ); ?>
                                    </label>
                                    <div class="wpaap-category-box" style="max-height: 120px; overflow-y: auto; padding-right: 5px;">
                                        <?php
                                        $categories = get_categories( array('hide_empty' => 0) );
                                        if ( ! empty($categories) ) {
                                            foreach ( $categories as $category ) {
                                                echo '<label style="display: block; margin-bottom: 6px; cursor: pointer;"><input type="checkbox" name="wpaap_post_category[]" value="' . esc_attr($category->term_id) . '"> ' . esc_html($category->name) . '</label>';
                                            }
                                        } else {
                                            echo '<span style="color: #8c8f94; font-size: 13px;">' . esc_html__( 'Chưa có chuyên mục nào.', 'whp' ) . '</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="wpaap-modern-box" style="flex: 1; min-width: 250px;">
                                    <label for="wpaap_post_tags" style="font-weight:600;margin-bottom:10px;display:inline-flex;align-items:center;gap:7px;">
                                        <span class="wpaap-label-icon" style="background:#ecfeff;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                        </span>
                                        <?php esc_html_e( 'Thẻ bài viết (Tags):', 'whp' ); ?>
                                    </label>
                                    <input type="text" id="wpaap_post_tags" name="wpaap_post_tags" placeholder="<?php esc_attr_e( 'Phân cách các thẻ bằng dấu phẩy (,)', 'whp' ); ?>" style="width: 100%; border: 1px solid #dcdcde; border-radius: 6px; padding: 10px 12px; font-size: 14px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02); transition: border-color 0.2s;" />
                                    <p class="description" style="margin-top: 8px; color: #646970; font-size: 13px; line-height: 1.4; display:flex; align-items:flex-start; gap:5px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6V17H8.3V15C6.3 13.7 5 11.5 5 9a7 7 0 0 1 7-7z"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>
                                        <span><?php esc_html_e( 'Thẻ (Tags) là các từ khóa chính giúp phân loại chi tiết, tăng cường hiệu quả tìm kiếm và SEO.', 'whp' ); ?><br><strong><?php esc_html_e( 'Ví dụ:', 'whp' ); ?></strong> <em><?php esc_html_e( 'du lịch, phú yên, cẩm nang', 'whp' ); ?></em></span>
                                    </p>
                                </div>
                            </div>

                            <div class="wpaap-form-group" style="display: flex; gap: 20px; flex-wrap: wrap;">
                                <div style="flex: 1; min-width: 250px;">
                                    <label style="font-weight:600;margin-bottom:10px;display:inline-flex;align-items:center;gap:7px;">
                                        <span class="wpaap-label-icon" style="background:#f0fdf4;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                        </span>
                                        <?php esc_html_e( 'Ảnh đại diện (Featured Image):', 'whp' ); ?>
                                    </label>
                                    <div class="wpaap-upload-zone" id="wpaap_upload_image_zone">
                                        <svg class="wpaap-upload-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                                        <span style="color: #646970; font-size: 14px; font-weight: 500;" id="wpaap_no_img_text"><?php esc_html_e( 'Bấm vào đây để chọn hoặc đổi ảnh', 'whp' ); ?></span>
                                    </div>
                                    <div style="text-align: right; margin-top: 5px; min-height: 20px;">
                                        <button type="button" id="wpaap_remove_image_btn" class="button button-link-delete" style="display: none; color: #d63638; text-decoration: none;"><?php esc_html_e( 'Xóa ảnh', 'whp' ); ?></button>
                                    </div>
                                    <input type="hidden" id="wpaap_featured_image_id" name="wpaap_featured_image_id" value="0" />
                                </div>
                                <div style="flex: 1; min-width: 250px;">
                                    <label style="font-weight:600;margin-bottom:10px;display:inline-flex;align-items:center;gap:7px;">
                                        <span class="wpaap-label-icon" style="background:#faf5ff;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="16" height="14" rx="2"/><path d="M22 3H10a2 2 0 0 0-2 2v2"/><circle cx="8" cy="13" r="1.5"/><polyline points="18 17 14 13 6 21"/></svg>
                                        </span>
                                        <?php esc_html_e( 'Ảnh mô tả nội dung bài viết:', 'whp' ); ?>
                                    </label>
                                    <div class="wpaap-upload-zone" id="wpaap_upload_content_images_zone" style="flex-direction: row; flex-wrap: wrap; justify-content: center; gap: 5px;">
                                        <div class="wpaap-upload-placeholder" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
                                            <svg class="wpaap-upload-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                                            <span style="color: #646970; font-size: 14px; font-weight: 500;" id="wpaap_no_content_img_text"><?php esc_html_e( 'Bấm vào đây để chọn nhiều ảnh', 'whp' ); ?></span>
                                        </div>
                                    </div>
                                    <div style="text-align: right; margin-top: 5px; min-height: 20px;">
                                        <button type="button" id="wpaap_remove_content_images_btn" class="button button-link-delete" style="display: none; color: #d63638; text-decoration: none;"><?php esc_html_e( 'Xóa tất cả', 'whp' ); ?></button>
                                    </div>
                                    <input type="hidden" id="wpaap_content_image_ids" name="wpaap_content_image_ids" value="" />
                                </div>
                            </div>

                            <div class="wpaap-notice-warning" style="margin-top: 20px; margin-bottom: 20px; display:flex; align-items:flex-start; gap:8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px;"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6V17H8.3V15C6.3 13.7 5 11.5 5 9a7 7 0 0 1 7-7z"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>
                                <span><strong><?php esc_html_e( 'Lưu ý:', 'whp' ); ?></strong> <?php esc_html_e( 'Nếu bạn KHÔNG upload ảnh đại diện hay mô tả nội dung, hệ thống sẽ tự động gọi AI vẽ hình minh họa để chèn vào bài (quá trình này mất khoảng vài phút).', 'whp' ); ?></span>
                            </div>

                            <button type="submit" id="wpaap_submit_btn" class="wpaap-btn-gradient" data-ai-ok="<?php echo $writer_ai_ok ? '1' : '0'; ?>" style="margin-top: 10px; width: 100%; justify-content: center;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                                <?php esc_html_e( 'AI soạn và Lưu chờ duyệt', 'whp' ); ?>
                            </button>
                            <div id="wpaap_writer_ai_notice" style="display:none;margin-top:10px;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;"></div>

                            <div id="wpaap_progress_container" style="display: none; margin-top: 15px;">
                                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px;">
                                    <span id="wpaap_progress_text" style="color: #7c3aed; font-weight: 500;"><?php esc_html_e( 'Đang khởi tạo AI...', 'whp' ); ?></span>
                                    <span id="wpaap_progress_percent" style="font-weight: 600;">0%</span>
                                </div>
                                <div class="wpaap-progress-bar-bg">
                                    <div class="wpaap-progress-bar-fill" id="wpaap_progress_fill"></div>
                                </div>
                                <div id="wpaap_job_logs_console" style="display: none; margin-top: 10px; background: #0f172a; color: #38bdf8; font-family: monospace; font-size: 11px; padding: 10px; border-radius: 6px; height: 120px; overflow-y: auto; line-height: 1.5; border: 1px solid #1e293b;"></div>
                            </div>

                            <div id="wpaap_ajax_message" style="display: none; margin-top: 15px;"></div>
                        </form>
                    </div>
                    <!-- Bài viết đang chờ duyệt (cùng cột với form) -->
                    <div class="wpaap-card" style="margin-bottom: 0;">
                        <div class="wpaap-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                            <div class="wpaap-section-header-row" style="margin-bottom:0;padding-bottom:0;border-bottom:none;flex:1;">
                                <div class="wpaap-section-icon-box" style="background:linear-gradient(135deg,#3858e9,#6b7ff0);box-shadow:0 4px 10px rgba(56,88,233,0.3);">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
                                </div>
                                <h2 style="margin:0;font-size:18px;font-weight:700;color:#0f172a;letter-spacing:-0.3px;"><?php esc_html_e( 'Hàng đợi bài viết AI', 'whp' ); ?></h2>
                            </div>
                            <a href="#" id="wpaap_refresh_posts_btn" title="<?php esc_attr_e( 'Làm mới danh sách', 'whp' ); ?>" style="color: #8c8f94; text-decoration: none; padding: 4px; border-radius: 4px; transition: background 0.2s; display: inline-flex; align-items: center; justify-content: center;">
                                <svg id="wpaap_refresh_icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                            </a>
                        </div>
                        <div id="wpaap_recent_posts_container">
                        <?php if ( ! empty( $recent_posts ) ) : ?>

                            <!-- Bulk action bar -->
                            <div id="wpaap_bulk_bar" class="wpaap-bulk-bar" style="display:none;">
                                <span id="wpaap_selected_count" class="wpaap-bulk-count"><?php esc_html_e( '0 bài đã chọn', 'whp' ); ?></span>
                                <button type="button" id="wpaap_bulk_approve_btn" class="wpaap-bulk-btn approve">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <?php esc_html_e( 'Duyệt đã chọn', 'whp' ); ?>
                                </button>
                                <button type="button" id="wpaap_bulk_delete_btn" class="wpaap-bulk-btn delete">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    <?php esc_html_e( 'Xóa đã chọn', 'whp' ); ?>
                                </button>
                            </div>

                            <!-- Table -->
                            <table class="wpaap-posts-table">
                                <thead>
                                    <tr>
                                        <th class="cb"><input type="checkbox" id="wpaap_select_all" title="<?php esc_attr_e( 'Chọn tất cả', 'whp' ); ?>"></th>
                                        <th><?php esc_html_e( 'Bài viết chờ duyệt', 'whp' ); ?></th>
                                        <th><?php esc_html_e( 'Ngày tạo', 'whp' ); ?></th>
                                        <th><?php esc_html_e( 'Loại', 'whp' ); ?></th>
                                        <th><?php esc_html_e( 'Trạng thái', 'whp' ); ?></th>
                                        <th><?php esc_html_e( 'Thao tác', 'whp' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="wpaap_posts_tbody">
                                <?php foreach ( $recent_posts as $post ) :
                                    $edit_link   = get_edit_post_link( $post->ID );
                                    $post_date   = get_the_date( 'd/m/Y', $post->ID );
                                    $publish_url = admin_url( 'admin.php?page=mb-wphelper-ai&subtab=writer&action=wpaap_publish&post_id=' . $post->ID );
                                    $delete_url  = admin_url( 'admin.php?page=mb-wphelper-ai&subtab=writer&action=wpaap_delete&post_id=' . $post->ID );
                                ?>
                                    <tr data-post-id="<?php echo esc_attr( $post->ID ); ?>">
                                        <td class="cb"><input type="checkbox" class="wpaap-post-cb" value="<?php echo esc_attr( $post->ID ); ?>"></td>
                                        <td><a href="<?php echo esc_url( $edit_link ); ?>" class="wpaap-post-title-link"><?php echo esc_html( $post->post_title ); ?></a></td>
                                        <td><?php echo esc_html( $post_date ); ?></td>
                                        <td><span class="wpaap-type-badge">AI Post</span></td>
                                        <td><span class="wpaap-status-badge wpaap-status-pending"><?php esc_html_e( 'Chờ duyệt', 'whp' ); ?></span></td>
                                        <td style="white-space:nowrap;">
                                            <a href="<?php echo esc_url( $edit_link ); ?>" style="color:#6366f1;text-decoration:none;margin-right:8px;font-weight:600;font-size:13px;"><?php esc_html_e( 'Sửa', 'whp' ); ?></a>
                                            <a href="<?php echo esc_url( $publish_url ); ?>" style="color:#16a34a;text-decoration:none;margin-right:8px;font-weight:600;font-size:13px;"><?php esc_html_e( 'Duyệt', 'whp' ); ?></a>
                                            <a href="<?php echo esc_url( $delete_url ); ?>" style="color:#ef4444;text-decoration:none;font-weight:600;font-size:13px;" onclick="return confirm('<?php echo esc_js( __( 'Xóa bài viết này?', 'whp' ) ); ?>');"><?php esc_html_e( 'Xóa', 'whp' ); ?></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if ( $has_more_posts ) : ?>
                                <div style="text-align: center; margin-top: 15px;">
                                    <button id="wpaap_load_more_posts_btn" class="button wpaap-btn-secondary" data-offset="5"><?php esc_html_e( 'Xem thêm bài viết', 'whp' ); ?></button>
                                </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <div class="wpaap-empty-posts">
                                <p><?php esc_html_e( 'Chưa có bài viết nào đang chờ duyệt.', 'whp' ); ?></p>
                            </div>
                        <?php endif; ?>
                        </div>
                    </div>
                </div><!-- /.wpaap-writer-main -->

                <!-- Sidebar: AI Assistant + Hướng dẫn + Mẹo -->
                <div class="wpaap-writer-sidebar">

                    <!-- AI Assistant (đầu sidebar) -->
                    <div class="wpaap-sidebar-card" style="padding:0;overflow:hidden;background:linear-gradient(140deg,#aa7fee 0%,#703dc6 55%,#3800b9 100%);border:none;box-shadow:0 6px 24px rgba(109,40,217,0.32);margin-bottom:16px;">
                        <div style="position:relative;padding:16px 18px;">
                            <!-- Decorative orbs -->
                            <div style="position:absolute;top:-18px;right:-18px;width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,0.08);pointer-events:none;"></div>
                            <div style="position:absolute;bottom:-12px;left:10px;width:48px;height:48px;border-radius:50%;background:rgba(255,255,255,0.06);pointer-events:none;"></div>

                            <!-- Title row -->
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                                <div style="width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                                </div>
                                <div>
                                    <div style="color:#fff;font-weight:700;font-size:13.5px;line-height:1.2;">AI Assistant</div>
                                    <div style="color:rgba(255,255,255,0.65);font-size:11px;margin-top:2px;"><?php esc_html_e( 'Trợ lý AI thông minh', 'whp' ); ?></div>
                                </div>
                            </div>

                            <!-- Description -->
                            <p style="margin:0 0 12px;font-size:12.5px;color:rgba(255,255,255,0.88);line-height:1.6;"><?php esc_html_e( 'Gợi ý ý tưởng, từ khóa và phác thảo dàn bài trước khi soạn.', 'whp' ); ?></p>

                            <!-- Location indicator -->
                            <div style="display:flex;align-items:center;gap:9px;background:rgba(255,255,255,0.15);border-radius:9px;padding:9px 11px;border:1px solid rgba(255,255,255,0.18);">
                                <div style="width:30px;height:30px;border-radius:50%;background:#fff;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,0.18);">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6V17H8.3V15C6.3 13.7 5 11.5 5 9a7 7 0 0 1 7-7z"/><path d="M9 17v1a3 3 0 0 0 6 0v-1"/></svg>
                                </div>
                                <span style="font-size:12px;color:#fff;line-height:1.45;"><?php esc_html_e( 'Nhấn nút', 'whp' ); ?> <strong>AI</strong> <?php esc_html_e( 'ở', 'whp' ); ?> <strong><?php esc_html_e( 'góc phải bên dưới', 'whp' ); ?></strong> <?php esc_html_e( 'màn hình', 'whp' ); ?> ↘</span>
                            </div>
                        </div>
                    </div>

                    <!-- Hướng dẫn nhanh -->
                    <div class="wpaap-sidebar-card" style="margin-bottom: 16px;">
                        <div class="wpaap-sidebar-card-header">
                            <div style="width:28px;height:28px;border-radius:7px;background:#eff2fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 8 12 12 14 14"/></svg>
                            </div>
                            <?php esc_html_e( 'Hướng dẫn nhanh', 'whp' ); ?>
                        </div>
                        <ol class="wpaap-guide-list">
                            <li><?php esc_html_e( 'Nhập mô tả chủ đề hoặc ý tưởng bài viết', 'whp' ); ?></li>
                            <li><?php esc_html_e( 'Chọn chuyên mục và thêm thẻ (tags)', 'whp' ); ?></li>
                            <li><?php esc_html_e( 'Upload ảnh đại diện', 'whp' ); ?> <em>(<?php esc_html_e( 'không bắt buộc', 'whp' ); ?>)</em></li>
                            <li><?php esc_html_e( 'Nhấn', 'whp' ); ?> <strong><?php esc_html_e( 'AI soạn và Lưu chờ duyệt', 'whp' ); ?></strong></li>
                            <li><?php esc_html_e( 'Xem lại bài ở mục bên dưới, duyệt hoặc chỉnh sửa', 'whp' ); ?></li>
                        </ol>
                    </div>

                    <!-- Mẹo sử dụng -->
                    <div class="wpaap-sidebar-card" style="margin-bottom: 16px;">
                        <div class="wpaap-sidebar-card-header">
                            <div style="width:28px;height:28px;border-radius:7px;background:#fef9c3;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="#f59e0b"><path d="M9 21h6M12 3a6 6 0 0 1 6 6c0 2.22-1.21 4.16-3 5.2V17H9v-2.8C7.21 13.16 6 11.22 6 9a6 6 0 0 1 6-6z"/></svg>
                            </div>
                            <?php esc_html_e( 'Mẹo sử dụng', 'whp' ); ?>
                        </div>
                        <ul class="wpaap-tips-list" style="margin-bottom: 0;">
                            <li><?php esc_html_e( 'Mô tả càng chi tiết → bài viết càng chuẩn SEO', 'whp' ); ?></li>
                            <li><?php esc_html_e( 'Upload ảnh nội dung để tiết kiệm vài phút chờ AI vẽ', 'whp' ); ?></li>
                            <li><?php esc_html_e( 'Thêm tags giúp phân loại và tăng hiệu quả SEO', 'whp' ); ?></li>
                        </ul>
                    </div>

                    <!-- API Key ảnh (Pexels / Pixabay) -->
                    <div class="wpaap-sidebar-card" style="margin-bottom: 0;">
                        <div class="wpaap-sidebar-card-header">
                            <div style="width:28px;height:28px;border-radius:7px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                            </div>
                            <?php esc_html_e( 'API Key ảnh (AI Writer)', 'whp' ); ?>
                        </div>
                        <p style="font-size:12px;color:#64748b;margin:0 0 10px;"><?php printf( esc_html__( 'Dùng để tìm ảnh minh họa khi tạo bài viết. Lấy key miễn phí tại %s và %s.', 'whp' ), '<a href="https://www.pexels.com/api/" target="_blank">pexels.com</a>', '<a href="https://pixabay.com/api/docs/" target="_blank">pixabay.com</a>' ); ?></p>
                        <form method="post">
                            <?php wp_nonce_field( 'wpaap_image_api_action', 'wpaap_image_api_nonce' ); ?>
                            <div style="margin-bottom:8px;">
                                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Pexels API Key</label>
                                <input type="password" name="wpaap_pexels_api_key" value="<?php echo esc_attr( get_option( 'wpaap_pexels_api_key', '' ) ); ?>" placeholder="<?php esc_attr_e( 'Dán API Key Pexels...', 'whp' ); ?>" style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:12px;">
                            </div>
                            <div style="margin-bottom:10px;">
                                <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:4px;">Pixabay API Key (backup)</label>
                                <input type="password" name="wpaap_pixabay_api_key" value="<?php echo esc_attr( get_option( 'wpaap_pixabay_api_key', '' ) ); ?>" placeholder="<?php esc_attr_e( 'Dán API Key Pixabay...', 'whp' ); ?>" style="width:100%;padding:6px 8px;border:1px solid #d1d5db;border-radius:6px;font-size:12px;">
                            </div>
                            <button type="submit" name="wpaap_save_image_api" style="width:100%;padding:7px 12px;background:linear-gradient(135deg,#7c3aed,#a78bfa);color:#fff;border:none;border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;"><?php esc_html_e( 'Lưu API Key ảnh', 'whp' ); ?></button>
                        </form>
                    </div>

                </div><!-- /.wpaap-writer-sidebar -->

            </div><!-- /.wpaap-writer-layout -->

        <?php endif; ?>
        </div>
    </div>
    
    <script>
    /* PHP-injected i18n strings for wpaap-admin-page */
    var wpaap_i18n_img_text           = '<?php echo esc_js( __( 'Bấm vào đây để chọn hoặc đổi ảnh', 'whp' ) ); ?>';
    var wpaap_i18n_multi_img_text     = '<?php echo esc_js( __( 'Bấm vào đây để chọn nhiều ảnh', 'whp' ) ); ?>';
    var wpaap_i18n_success            = '<?php echo esc_js( __( 'Bài viết đã được tạo thành công!', 'whp' ) ); ?>';
    var wpaap_i18n_pending            = '<?php echo esc_js( __( 'Chờ duyệt', 'whp' ) ); ?>';
    var wpaap_i18n_preview            = '<?php echo esc_js( __( 'Xem trước', 'whp' ) ); ?>';
    var wpaap_i18n_publish            = '<?php echo esc_js( __( 'Duyệt & Đăng ngay', 'whp' ) ); ?>';
    var wpaap_step_labels = {
        default:    '<?php echo esc_js( __( 'Đang xử lý...', 'whp' ) ); ?>',
        init:       '<?php echo esc_js( __( 'Đang khởi tạo AI...', 'whp' ) ); ?>',
        outline:    '<?php echo esc_js( __( 'Đang lập dàn bài...', 'whp' ) ); ?>',
        intro:      '<?php echo esc_js( __( 'Đang viết phần mở đầu...', 'whp' ) ); ?>',
        conclusion: '<?php echo esc_js( __( 'Đang soạn phần kết luận, FAQ...', 'whp' ) ); ?>',
        metadata:   '<?php echo esc_js( __( 'Đang tối ưu meta SEO...', 'whp' ) ); ?>',
        publish:    '<?php echo esc_js( __( 'Đang tạo bài viết nháp...', 'whp' ) ); ?>',
        images:     '<?php echo esc_js( __( 'Đang tải ảnh bài viết...', 'whp' ) ); ?>'
    };
    var wpaap_step_section_prefix = '<?php echo esc_js( __( 'Đang viết Phần nội dung chi tiết', 'whp' ) ); ?>';
    var wpaap_err_label           = '<?php echo esc_js( __( 'Lỗi:', 'whp' ) ); ?>';
    var wpaap_err_unknown         = '<?php echo esc_js( __( 'Lỗi không xác định từ AI', 'whp' ) ); ?>';
    var wpaap_no_ai_msg           = '<?php echo esc_js( __( '⚠️ Chưa kết nối AI. Vui lòng cấu hình API Key tại tab Kết nối AI trước.', 'whp' ) ); ?>';
    var wpaap_err_queue           = '<?php echo esc_js( __( 'Không thể xếp hàng công việc', 'whp' ) ); ?>';
    var wpaap_selected_suffix     = '<?php echo esc_js( __( 'bài đã chọn', 'whp' ) ); ?>';
    var wpaap_approved_prefix     = '<?php echo esc_js( __( 'Đã duyệt và đăng', 'whp' ) ); ?>';
    var wpaap_approved_suffix     = '<?php echo esc_js( __( 'bài viết thành công!', 'whp' ) ); ?>';
    var wpaap_confirm_approve     = '<?php echo esc_js( __( 'Duyệt', 'whp' ) ); ?>';
    var wpaap_confirm_delete      = '<?php echo esc_js( __( 'Xóa', 'whp' ) ); ?>';
    jQuery(document).ready(function($) {
        var file_frame;

        // Highlight active length option
        function wpaapHighlightLength() {
            $('.wpaap-length-label').each(function() {
                var $lbl = $(this);
                var isChecked = $lbl.find('input[type="radio"]').is(':checked');
                $lbl.css('box-shadow', isChecked ? '0 0 0 2px ' + ($lbl.find('input').css('accent-color') || '#16a34a') : 'none');
                $lbl.css('opacity', isChecked ? '1' : '0.72');
            });
        }
        wpaapHighlightLength();
        $('input[name="wpaap_article_length"]').on('change', wpaapHighlightLength);

        // Xử lý sự kiện bấm chọn/tải ảnh
        $('#wpaap_upload_image_zone').on('click', function(e) {
            e.preventDefault();
            if ( file_frame ) {
                file_frame.open();
                return;
            }
            file_frame = wp.media.frames.file_frame = wp.media({
                title: '<?php echo esc_js( __( 'Chọn ảnh đại diện', 'whp' ) ); ?>',
                button: { text: '<?php echo esc_js( __( 'Sử dụng ảnh này', 'whp' ) ); ?>' },
                multiple: false
            });
            file_frame.on('select', function() {
                var attachment = file_frame.state().get('selection').first().toJSON();
                $('#wpaap_featured_image_id').val(attachment.id);
                $('#wpaap_upload_image_zone').html('<img src="'+attachment.url+'" style="max-width:100%; max-height: 180px; border-radius:6px; object-fit: contain; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" />');
                $('#wpaap_remove_image_btn').show();
            });
            file_frame.open();
        });

        $('#wpaap_remove_image_btn').on('click', function(e) {
            e.preventDefault();
            $('#wpaap_featured_image_id').val('0');
            $('#wpaap_upload_image_zone').html('<span class="dashicons dashicons-cloud-upload wpaap-upload-icon"></span><span style="color: #646970; font-size: 14px; font-weight: 500;" id="wpaap_no_img_text">'+wpaap_i18n_img_text+'</span>');
            $(this).hide();
        });

        var content_frame;
        $('#wpaap_upload_content_images_zone').on('click', function(e) {
            e.preventDefault();
            if ( content_frame ) {
                content_frame.open();
                return;
            }
            content_frame = wp.media.frames.content_frame = wp.media({
                title: '<?php echo esc_js( __( 'Chọn ảnh mô tả nội dung bài viết', 'whp' ) ); ?>',
                button: { text: '<?php echo esc_js( __( 'Sử dụng các ảnh này', 'whp' ) ); ?>' },
                multiple: true
            });
            content_frame.on('select', function() {
                var selection = content_frame.state().get('selection');
                var ids = [];
                $('#wpaap_upload_content_images_zone').empty();
                selection.map(function(attachment) {
                    attachment = attachment.toJSON();
                    ids.push(attachment.id);
                    $('#wpaap_upload_content_images_zone').append('<img src="'+attachment.url+'" style="max-width: 80px; max-height: 80px; border-radius: 4px; object-fit: cover; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" />');
                });
                $('#wpaap_content_image_ids').val(ids.join(','));
                $('#wpaap_remove_content_images_btn').show();
            });
            content_frame.open();
        });

        // Xóa tất cả ảnh mô tả nội dung
        $('#wpaap_remove_content_images_btn').on('click', function(e) {
            e.preventDefault();
            $('#wpaap_content_image_ids').val('');
            $('#wpaap_upload_content_images_zone').html('<div class="wpaap-upload-placeholder" style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;"><span class="dashicons dashicons-cloud-upload wpaap-upload-icon"></span><span style="color: #646970; font-size: 14px; font-weight: 500;" id="wpaap_no_content_img_text">'+wpaap_i18n_multi_img_text+'</span></div>');
            $(this).hide();
        });

        var wpaap_is_generating = false; // flag chặn double-submit ở JS level
        var pollInterval = null;

        function wpaap_show_success(job) {
            var postTitle = job.title || '';
            var successHtml =
                '<div style="border-radius:10px;overflow:hidden;border:1px solid #bbf7d0;background:#fff;box-shadow:0 2px 16px rgba(22,163,74,0.12);">' +
                  '<div style="background:linear-gradient(135deg,#16a34a 0%,#22c55e 100%);padding:10px 16px;display:flex;align-items:center;gap:8px;">' +
                    '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                    '<span style="color:#fff;font-weight:700;font-size:13.5px;flex:1;">'+wpaap_i18n_success+'</span>' +
                    '<span style="background:rgba(255,255,255,0.22);color:#fff;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:600;border:1px solid rgba(255,255,255,0.35);">'+wpaap_i18n_pending+'</span>' +
                  '</div>' +
                  '<div style="padding:12px 16px;">' +
                    (postTitle ? '<div style="font-size:13px;color:#1e293b;font-weight:600;margin-bottom:12px;line-height:1.5;padding-left:10px;border-left:3px solid #22c55e;">' + postTitle + '</div>' : '') +
                    '<div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;">' +
                      '<a href="' + job.view_url + '" target="_blank" style="display:inline-flex;align-items:center;gap:5px;text-decoration:none;background:#f8fafc;color:#475569;border:1px solid #e2e8f0;padding:6px 13px;border-radius:6px;font-size:12.5px;font-weight:600;">' +
                        '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg> '+wpaap_i18n_preview+
                      '</a>' +
                      '<a href="' + job.edit_url + '" target="_blank" style="display:inline-flex;align-items:center;gap:5px;text-decoration:none;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;padding:6px 14px;border-radius:6px;font-size:12.5px;font-weight:700;box-shadow:0 2px 8px rgba(109,40,217,0.3);">' +
                        '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> '+wpaap_i18n_publish+
                      '</a>' +
                    '</div>' +
                  '</div>' +
                '</div>';
            $('#wpaap_ajax_message').removeClass('wpaap-ajax-error').addClass('wpaap-ajax-success')
                .css({'border-radius':'0','padding':'0','border':'none','background':'transparent','box-shadow':'none'})
                .html(successHtml).hide().slideDown(300);
            $('#wpaap_user_prompt').val('');
            $('#wpaap_post_tags').val('');
            $('input[name="wpaap_post_category[]"]').prop('checked', false);
            $('#wpaap_remove_image_btn').trigger('click');
            $('#wpaap_remove_content_images_btn').trigger('click');
            if (typeof wpaap_load_recent_posts_inline === 'function') {
                wpaap_load_recent_posts_inline(0, false);
            }
        }

        function wpaap_start_polling(jobId) {
            var $btn = $('#wpaap_submit_btn');
            var $progressContainer = $('#wpaap_progress_container');
            var $progressFill = $('#wpaap_progress_fill');
            var $progressText = $('#wpaap_progress_text');
            var $progressPercent = $('#wpaap_progress_percent');
            var $logsConsole = $('#wpaap_job_logs_console');
            var $msgBox = $('#wpaap_ajax_message');
            var _genNonce = '<?php echo wp_create_nonce("wpaap_generate_nonce"); ?>';

            wpaap_is_generating = true;
            $btn.prop('disabled', true).css('opacity', '0.6');
            $progressContainer.slideDown(200);
            $logsConsole.empty().show();
            $progressFill.addClass('wpaap-pulsing');
            $msgBox.hide();

            clearInterval(pollInterval);
            
            window._wpaap_last_step = null;
            window._wpaap_last_step_polls = 0;

            pollInterval = setInterval(function() {
                $.post(ajaxurl, { 
                    action: 'wpaap_check_post_job', 
                    nonce: _genNonce, 
                    job_id: jobId 
                }, function(pRes) {
                    if (!pRes || !pRes.success) return;
                    var job = pRes.data;

                    $progressFill.css('width', job.progress + '%');
                    $progressPercent.text(job.progress + '%');
                    
                    var stepLabel = wpaap_step_labels.default;
                    if (job.current_step === 'init') stepLabel = wpaap_step_labels.init;
                    else if (job.current_step === 'outline') stepLabel = wpaap_step_labels.outline;
                    else if (job.current_step === 'intro') stepLabel = wpaap_step_labels.intro;
                    else if (job.current_step === 'conclusion') stepLabel = wpaap_step_labels.conclusion;
                    else if (job.current_step === 'metadata') stepLabel = wpaap_step_labels.metadata;
                    else if (job.current_step === 'publish') stepLabel = wpaap_step_labels.publish;
                    else if (job.current_step === 'images') stepLabel = wpaap_step_labels.images;
                    else if (job.current_step.indexOf('section_') === 0) {
                        var secNum = parseInt(job.current_step.substring(8)) + 1;
                        stepLabel = wpaap_step_section_prefix + ' ' + secNum + '...';
                    }
                    $progressText.text(stepLabel);

                    if (job.logs && job.logs.length > 0) {
                        var logHtml = '';
                        job.logs.forEach(function(line) {
                            logHtml += '<div style="margin-bottom:3px;">' + line + '</div>';
                        });
                        $logsConsole.html(logHtml);
                        $logsConsole.scrollTop($logsConsole[0].scrollHeight);
                    }

                    if (job.status === 'queued' || job.status === 'running') {
                        if (!window._wpaap_last_step) {
                            window._wpaap_last_step = job.current_step;
                            window._wpaap_last_step_polls = 0;
                        } else if (window._wpaap_last_step === job.current_step) {
                            window._wpaap_last_step_polls++;
                            if (window._wpaap_last_step_polls >= 6) { // 15s without change
                                window._wpaap_last_step_polls = 0;
                                $.post(ajaxurl, {
                                    action: 'wpaap_resume_active_job',
                                    nonce: _genNonce
                                });
                            }
                        } else {
                            window._wpaap_last_step = job.current_step;
                            window._wpaap_last_step_polls = 0;
                        }
                        return;
                    }

                    clearInterval(pollInterval);
                    $progressFill.removeClass('wpaap-pulsing').css('width', '100%');
                    $progressPercent.text('100%');

                    if (job.status === 'completed') {
                        $progressText.text('<?php echo esc_js( __( 'Hoàn tất!', 'whp' ) ); ?>');
                        setTimeout(function() {
                            $progressContainer.slideUp();
                            wpaap_show_success(job);
                            wpaap_is_generating = false;
                            $btn.prop('disabled', false).css('opacity', '1');
                        }, 800);
                    } else {
                        $progressText.text('<?php echo esc_js( __( 'Có lỗi xảy ra', 'whp' ) ); ?>');
                        setTimeout(function() {
                            $progressContainer.slideUp();
                            $msgBox.addClass('wpaap-ajax-error').html('<strong>'+wpaap_err_label+'</strong> ' + (job.error || wpaap_err_unknown)).slideDown();
                            wpaap_is_generating = false;
                            $btn.prop('disabled', false).css('opacity', '1');
                        }, 800);
                    }
                }, 'json');
            }, 2500);
        }

        $('#wpaap_form_generate').on('submit', function(e) {
            e.preventDefault();

            if (wpaap_is_generating) return;

            var $btn = $('#wpaap_submit_btn');
            var $writerNotice = $('#wpaap_writer_ai_notice');

            if ($btn.data('ai-ok') !== 1 && $btn.data('ai-ok') !== '1') {
                $writerNotice.addClass('mb-error').css({'background':'#fef2f2','color':'#dc2626','border':'1px solid #fecaca'})
                    .html(wpaap_no_ai_msg)
                    .show();
                setTimeout(function() { $writerNotice.fadeOut(); }, 8000);
                return;
            }

            var promptText = $('#wpaap_user_prompt').val().trim();
            if (promptText === '') {
                alert('<?php echo esc_js( __( 'Vui lòng nhập mô tả chi tiết ý tưởng hoặc chủ đề bài viết!', 'whp' ) ); ?>');
                return;
            }
            var $progressContainer = $('#wpaap_progress_container');
            var $progressFill = $('#wpaap_progress_fill');
            var $progressText = $('#wpaap_progress_text');
            var $progressPercent = $('#wpaap_progress_percent');
            var $logsConsole = $('#wpaap_job_logs_console');
            var $msgBox = $('#wpaap_ajax_message');

            wpaap_is_generating = true;
            $btn.prop('disabled', true).css('opacity', '0.6');
            $msgBox.hide().removeClass('wpaap-ajax-success wpaap-ajax-error').html('');
            $('.wpaap-notice').hide();
            $('#wpaap_inline_notice').stop(true).hide();
            
            $progressContainer.slideDown(200);
            $logsConsole.empty().show();
            $progressFill.addClass('wpaap-pulsing');
            $progressFill.css('width', '5%');
            $progressPercent.text('5%');
            $progressText.text('<?php echo esc_js( __( 'Đang kết nối AI...', 'whp' ) ); ?>');

            var categories = [];
            $('input[name="wpaap_post_category[]"]:checked').each(function() {
                categories.push($(this).val());
            });

            var _genNonce = '<?php echo wp_create_nonce("wpaap_generate_nonce"); ?>';

            $.post(ajaxurl, {
                action: 'wpaap_queue_post_job',
                nonce: _genNonce,
                prompt: promptText,
                image_id: $('#wpaap_featured_image_id').val(),
                content_image_ids: $('#wpaap_content_image_ids').val(),
                categories: categories,
                tags: $('#wpaap_post_tags').val(),
                article_length: $('input[name="wpaap_article_length"]:checked').val() || 'medium'
            }, function(qRes) {
                if (!qRes || !qRes.success) {
                    wpaap_is_generating = false;
                    $btn.prop('disabled', false).css('opacity', '1');
                    $progressContainer.slideUp();
                    $msgBox.addClass('wpaap-ajax-error').html('<strong>'+wpaap_err_label+'</strong> ' + ((qRes && qRes.data && qRes.data.message) || wpaap_err_queue)).slideDown();
                    return;
                }
                wpaap_start_polling(qRes.data.job_id);
            }, 'json').fail(function(xhr) {
                wpaap_is_generating = false;
                $btn.prop('disabled', false).css('opacity', '1');
                $progressContainer.slideUp();
                $msgBox.addClass('wpaap-ajax-error').html('<strong>'+wpaap_err_label+'</strong> HTTP ' + xhr.status + ' — ' + (xhr.statusText || '')).slideDown();
            });
        });

        // ── Checkbox bulk select ──────────────────────────
        function wpaap_update_bulk_bar() {
            var ids = wpaap_get_selected_ids();
            var count = ids.length;
            if (count > 0) {
                $('#wpaap_selected_count').text(count + ' ' + wpaap_selected_suffix);
                $('#wpaap_bulk_bar').slideDown(150);
            } else {
                $('#wpaap_bulk_bar').slideUp(150);
            }
            $('#wpaap_posts_tbody tr').each(function(){
                var cb = $(this).find('.wpaap-post-cb');
                $(this).toggleClass('selected', cb.is(':checked'));
            });
        }
        function wpaap_get_selected_ids() {
            var ids = [];
            $('.wpaap-post-cb:checked').each(function(){ ids.push($(this).val()); });
            return ids;
        }
        $(document).on('change', '#wpaap_select_all', function(){
            var checked = $(this).is(':checked');
            $('.wpaap-post-cb').prop('checked', checked);
            wpaap_update_bulk_bar();
        });
        $(document).on('change', '.wpaap-post-cb', function(){
            var total = $('.wpaap-post-cb').length;
            var checked = $('.wpaap-post-cb:checked').length;
            $('#wpaap_select_all').prop('indeterminate', checked > 0 && checked < total);
            $('#wpaap_select_all').prop('checked', checked === total);
            wpaap_update_bulk_bar();
        });

        // ── Helper: hiển thị notice + scroll lên ─────────
        function wpaapShowNotice(msg, type) {
            type = type || 'success';
            var icons = {
                success: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
                error:   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
            };
            var colors = { success: '#16a34a', error: '#dc2626' };
            var bgs    = { success: '#f0fdf4', error: '#fef2f2' };
            var borders= { success: '#bbf7d0', error: '#fecaca' };
            var c = colors[type] || colors.success;
            var $notice = $('#wpaap_inline_notice');
            // Ẩn old static notice nếu có
            $('.wpaap-notice').hide();
            $notice.html(
                '<div style="display:flex;align-items:center;gap:10px;padding:14px 18px;background:' + bgs[type] + ';border:1px solid ' + borders[type] + ';border-left:4px solid ' + c + ';border-radius:10px;color:' + c + ';font-size:14px;font-weight:600;">' +
                icons[type] + msg + '</div>'
            ).hide().slideDown(250);
            $('html, body').animate({ scrollTop: $notice.offset().top - 80 }, 400);
            setTimeout(function(){ $notice.slideUp(300); }, 5000);
        }

        // ── Bulk approve ──────────────────────────────────
        $(document).on('click', '#wpaap_bulk_approve_btn', function(){
            var ids = wpaap_get_selected_ids();
            if (!ids.length) return;
            if (!confirm(wpaap_confirm_approve + ' ' + ids.length + ' <?php echo esc_js( __( 'bài viết đã chọn?', 'whp' ) ); ?>')) return;
            $.post(ajaxurl, {
                action: 'wpaap_bulk_publish',
                nonce: '<?php echo wp_create_nonce("wpaap_generate_nonce"); ?>',
                post_ids: ids
            }, function(res){
                if (res.success) {
                    ids.forEach(function(id){ $('[data-post-id="'+id+'"]').remove(); });
                    $('.wpaap-post-cb:checked').prop('checked', false);
                    $('#wpaap_select_all').prop('checked', false).prop('indeterminate', false);
                    wpaap_update_bulk_bar();
                    wpaapShowNotice(wpaap_approved_prefix + ' ' + ids.length + ' ' + wpaap_approved_suffix, 'success');
                    if ($('#wpaap_posts_tbody tr').length === 0) {
                        $('#wpaap_recent_posts_container').html('<div class="wpaap-empty-posts"><p><?php echo esc_js( __( 'Chưa có bài viết nào đang chờ duyệt.', 'whp' ) ); ?></p></div>');
                    }
                } else {
                    wpaapShowNotice('<?php echo esc_js( __( 'Có lỗi xảy ra khi duyệt bài. Vui lòng thử lại.', 'whp' ) ); ?>', 'error');
                }
            }, 'json');
        });

        // ── Bulk delete ───────────────────────────────────
        $(document).on('click', '#wpaap_bulk_delete_btn', function(){
            var ids = wpaap_get_selected_ids();
            if (!ids.length) return;
            if (!confirm(wpaap_confirm_delete + ' ' + ids.length + ' <?php echo esc_js( __( 'bài viết đã chọn?', 'whp' ) ); ?>')) return;
            var $btn = $(this).prop('disabled', true).text('<?php echo esc_js( __( 'Đang xóa...', 'whp' ) ); ?>');
            $.post(ajaxurl, {
                action: 'wpaap_bulk_delete',
                nonce: '<?php echo wp_create_nonce("wpaap_generate_nonce"); ?>',
                post_ids: ids
            }, function(res){
                $btn.prop('disabled', false).html('<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> <?php echo esc_js( __( 'Xóa đã chọn', 'whp' ) ); ?>');
                if (res.success) {
                    var deleted = res.data && res.data.count ? res.data.count : 0;
                    if (deleted === 0) {
                        wpaapShowNotice('<?php echo esc_js( __( 'Không có bài viết nào được xóa. Vui lòng thử lại.', 'whp' ) ); ?>', 'error');
                        return;
                    }
                    wpaapShowNotice('<?php echo esc_js( __( 'Đã xóa', 'whp' ) ); ?>' + ' ' + deleted + ' <?php echo esc_js( __( 'bài viết thành công!', 'whp' ) ); ?>', 'success');
                    // Reload list từ server để đảm bảo hiển thị đúng trạng thái thực tế
                    wpaap_load_recent_posts_inline(0, false);
                } else {
                    var msg = (res.data && res.data.message) ? res.data.message : '<?php echo esc_js( __( 'Có lỗi xảy ra khi xóa bài.', 'whp' ) ); ?>';
                    wpaapShowNotice(msg, 'error');
                }
            }, 'json').fail(function(){
                $btn.prop('disabled', false);
                wpaapShowNotice('<?php echo esc_js( __( 'Lỗi kết nối. Vui lòng tải lại trang và thử lại.', 'whp' ) ); ?>', 'error');
            });
        });

        // ── Load / reload posts ───────────────────────────
        function wpaap_load_recent_posts_inline(offset, append) {
            offset = offset || 0;
            append = append || false;
            var $btn = $('#wpaap_refresh_posts_btn');
            var $icon = $('#wpaap_refresh_icon');
            var $loadMoreBtn = $('#wpaap_load_more_posts_btn');

            if (!append) {
                $btn.css('opacity', '0.5');
                $icon.css('animation', 'wpaap-spin 0.8s linear infinite');
            } else {
                $loadMoreBtn.prop('disabled', true).text('<?php echo esc_js( __( 'Đang tải...', 'whp' ) ); ?>');
            }

            $.ajax({
                url: ajaxurl, type: 'POST', dataType: 'json',
                data: {
                    action: 'wpaap_reload_recent_posts',
                    nonce: '<?php echo wp_create_nonce("wpaap_generate_nonce"); ?>',
                    offset: offset,
                    page_slug: 'wp-ai-auto-poster-writer'
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        if (append) {
                            $('#wpaap_posts_tbody').append(response.data.html);
                            if (response.data.has_more) {
                                $loadMoreBtn.data('offset', offset + 5).prop('disabled', false).text('<?php echo esc_js( __( 'Xem thêm bài viết', 'whp' ) ); ?>');
                            } else {
                                $loadMoreBtn.remove();
                            }
                        } else {
                            $('#wpaap_recent_posts_container').html(response.data.html);
                        }
                    }
                    if (!append) {
                        $btn.css('opacity', '1');
                        $icon.css('animation', '');
                    }
                },
                error: function() {
                    if (!append) {
                        $btn.css('opacity', '1');
                        $icon.css('animation', '');
                    } else {
                        $loadMoreBtn.prop('disabled', false).text('<?php echo esc_js( __( 'Xem thêm bài viết', 'whp' ) ); ?>');
                    }
                }
            });
        }
        $('#wpaap_refresh_posts_btn').on('click', function(e) {
            e.preventDefault();
            wpaap_load_recent_posts_inline(0, false);
        });
        $(document).on('click', '#wpaap_load_more_posts_btn', function(e) {
            e.preventDefault();
            wpaap_load_recent_posts_inline($(this).data('offset'), true);
        });
    });
    </script>
    <?php
}
