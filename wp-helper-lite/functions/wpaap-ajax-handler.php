<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
// ─── DEBUG LOGGER ─────────────────────────────────────────────────────────────
function wpaap_log( $msg ) {
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
        return;
    }
    $line = '[' . date( 'Y-m-d H:i:s' ) . '] ' . $msg . "\n";
    file_put_contents( WP_CONTENT_DIR . '/ai-writer-debug.log', $line, FILE_APPEND | LOCK_EX );
    error_log( 'WPAAP | ' . $msg );
}

// ─── LOG VIEWER (admin only) ───────────────────────────────────────────────────
add_action( 'wp_ajax_wpaap_read_log', 'wpaap_ajax_read_log' );
function wpaap_ajax_read_log() {
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'forbidden' );
    $log_file = WP_CONTENT_DIR . '/ai-writer-debug.log';
    if ( ! file_exists( $log_file ) ) { wp_send_json_success( [ 'log' => '(log trống)' ] ); return; }
    $lines = file( $log_file );
    $last  = array_slice( $lines, -100 ); // 100 dòng cuối
    wp_send_json_success( [ 'log' => implode( '', $last ) ] );
}

// ─── Hàm xử lý AJAX tạo bài viết ─────────────────────────────────────────────
add_action( 'wp_ajax_wpaap_generate_post', 'wpaap_ajax_generate_post_handler' );
function wpaap_ajax_generate_post_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );

    if ( function_exists( 'set_time_limit' ) ) {
        @set_time_limit( 300 );
    }
    @ini_set( 'memory_limit', '256M' );

    $user_prompt = isset( $_POST['prompt'] ) ? sanitize_textarea_field( $_POST['prompt'] ) : '';
    if ( empty( $user_prompt ) ) {
        wp_send_json_error( array( 'message' => 'Vui lòng nhập mô tả rõ ràng cho bài viết!' ) );
        return;
    }

    // ── PHP-LEVEL LOCK — chặn duplicate dù JS có nhiều handler ─────────────
    $lock_key = 'wpaap_gen_' . get_current_user_id();
    if ( get_transient( $lock_key ) ) {
        wp_send_json_error( array( 'message' => 'Đang tạo bài viết, vui lòng đợi cho đến khi hoàn tất.' ) );
        return;
    }
    set_transient( $lock_key, 1, 180 ); // lock 3 phút

    $ai_model          = get_option( 'wpaap_default_ai_model', 'gemini-2.5-flash-lite' );
    $featured_image_id = isset( $_POST['image_id'] ) ? intval( $_POST['image_id'] ) : 0;
    $content_image_ids = isset( $_POST['content_image_ids'] ) ? sanitize_text_field( $_POST['content_image_ids'] ) : '';
    $categories        = isset( $_POST['categories'] ) ? array_map( 'intval', (array) $_POST['categories'] ) : array();
    $tags              = isset( $_POST['tags'] ) ? sanitize_text_field( $_POST['tags'] ) : '';

    $result = wpaap_generate_content_with_ai( $user_prompt, $featured_image_id, $content_image_ids, $categories, $tags, $ai_model );

    if ( is_wp_error( $result ) ) {
        delete_transient( $lock_key );
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        return;
    }

    delete_transient( $lock_key );

    // Xử lý ảnh ngay trong cùng request (không loopback)
    $pending_images = get_post_meta( $result['post_id'], '_wpaap_pending_images', true );
    if ( ! empty( $pending_images ) ) {
        wpaap_do_process_pending_images( $result['post_id'] );
    }

    $edit_link = get_edit_post_link( $result['post_id'], 'raw' );
    $view_link = get_permalink( $result['post_id'] );
    $img_note  = '';

    wp_send_json_success( array(
        'message'    => '<span class="dashicons dashicons-yes" style="color:#00a32a; font-size:18px; width:18px; height:18px; vertical-align:middle; margin-right:4px;"></span> Đã tạo bài viết "<strong>' . esc_html( $result['title'] ) . '</strong>" thành công. Trạng thái: Chờ duyệt.' . esc_html( $img_note ),
        'edit_url'   => $edit_link,
        'view_url'   => $view_link,
        'async_imgs' => false,
    ) );
}

// Core image processing logic — tải song song + sideload vào WP Media Library
function wpaap_do_process_pending_images( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post ) {
        wpaap_log( "IMG PROCESS post not found post_id=$post_id" );
        return false;
    }

    $ai_images = get_post_meta( $post_id, '_wpaap_pending_images', true );

    if ( empty( $ai_images ) || ! is_array( $ai_images ) ) {
        wpaap_log( "IMG PROCESS no pending images post_id=$post_id" );
        return false;
    }

    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $t_start  = microtime( true );
    $deadline = $t_start + 50; // hard cap: tối đa 50s cho toàn bộ image processing
    wpaap_log( 'IMG PROCESS START post_id=' . $post_id . ' count=' . count( $ai_images ) );

    // Đảm bảo thư mục uploads tồn tại
    $upload_dir = wp_upload_dir( null, true );
    if ( ! empty( $upload_dir['path'] ) && ! is_dir( $upload_dir['path'] ) ) {
        wp_mkdir_p( $upload_dir['path'] );
    }

    // Bước 1: Download tất cả ảnh song song từ Pollinations (timeout 25s)
    $tmp_files = wpaap_parallel_download_images( $ai_images );
    wpaap_log( 'IMG PAR DL DONE elapsed=' . round( microtime( true ) - $t_start, 1 ) . 's' );

    $post_content  = $post->post_content;
    $first_att_id  = 0;

    $ext_map = array(
        'image/jpeg' => '.jpg', 'image/jpg' => '.jpg',
        'image/png'  => '.png', 'image/webp' => '.webp',
        'image/gif'  => '.gif',
    );

    for ( $i = 0; $i < count( $ai_images ); $i++ ) {
        $placeholder = 'AI_IMAGE_PLACEHOLDER_' . ( $i + 1 );
        if ( strpos( $post_content, $placeholder ) === false ) continue;

        // Hard cap: nếu vượt 50s tổng, xóa placeholder còn lại và thoát
        if ( microtime( true ) >= $deadline ) {
            wpaap_log( 'IMG PROCESS deadline reached at image ' . $i . ', removing remaining placeholders' );
            for ( $j = $i; $j < count( $ai_images ); $j++ ) {
                $ph = 'AI_IMAGE_PLACEHOLDER_' . ( $j + 1 );
                $post_content = preg_replace( '/<figure[^>]*>.*?' . preg_quote( $ph, '/' ) . '.*?<\/figure>/si', '', $post_content );
                $post_content = str_replace( $ph, '', $post_content );
            }
            break;
        }

        $image_data  = $ai_images[ $i ];
        $description = is_array( $image_data ) ? ( $image_data['keywords'] ?? '' ) : '';
        $picsum_url  = is_array( $image_data ) ? ( $image_data['last_resort'] ?? '' ) : '';
        $tmp         = $tmp_files[ $i ] ?? null;
        $att_id      = 0;

        // Bước 2a: Sideload từ tmp file Pollinations đã download song song
        if ( $tmp && file_exists( $tmp ) && filesize( $tmp ) > 2000 ) {
            $mime_type = '';
            if ( function_exists( 'finfo_open' ) ) {
                $fi        = finfo_open( FILEINFO_MIME_TYPE );
                $mime_type = finfo_file( $fi, $tmp );
                finfo_close( $fi );
            } elseif ( function_exists( 'mime_content_type' ) ) {
                $mime_type = mime_content_type( $tmp );
            }

            $ext  = $ext_map[ $mime_type ] ?? '.jpg';
            $base = sanitize_file_name( sanitize_title( $description ?: ( 'ai-img-' . $post_id . '-' . ( $i + 1 ) ) ) );
            if ( empty( $base ) ) $base = 'ai-img-' . $post_id . '-' . ( $i + 1 );

            $file_array = array( 'name' => $base . $ext, 'tmp_name' => $tmp );
            $att_id     = media_handle_sideload( $file_array, $post_id, $description );

            if ( is_wp_error( $att_id ) ) {
                wpaap_log( 'IMG SIDELOAD[' . $i . '] FAIL: ' . $att_id->get_error_message() );
                @unlink( $tmp );
                $att_id = 0;
            } else {
                if ( ! empty( $description ) ) {
                    update_post_meta( $att_id, '_wp_attachment_image_alt', wp_strip_all_tags( $description ) );
                }
                if ( ! $first_att_id ) $first_att_id = $att_id;
                wpaap_log( 'IMG SIDELOAD[' . $i . '] OK att_id=' . $att_id );
            }
        } else {
            if ( $tmp ) @unlink( $tmp );
        }

        // Bước 2b: Pollinations thất bại → thử Picsum nhanh (tránh retry Pollinations 60s)
        if ( ! $att_id && ! empty( $picsum_url ) ) {
            wpaap_log( 'IMG[' . $i . '] Pollinations fail, trying Picsum fallback' );
            $tmp_picsum = wpaap_download_image_to_tmp( $picsum_url, 10 );
            if ( ! is_wp_error( $tmp_picsum ) && file_exists( $tmp_picsum ) && filesize( $tmp_picsum ) > 2000 ) {
                $base_fb       = 'ai-img-' . $post_id . '-' . ( $i + 1 );
                $file_array_fb = array( 'name' => $base_fb . '.jpg', 'tmp_name' => $tmp_picsum );
                $fb_att        = media_handle_sideload( $file_array_fb, $post_id, $description );
                if ( ! is_wp_error( $fb_att ) ) {
                    $att_id = $fb_att;
                    if ( ! empty( $description ) ) {
                        update_post_meta( $att_id, '_wp_attachment_image_alt', wp_strip_all_tags( $description ) );
                    }
                    if ( ! $first_att_id ) $first_att_id = $att_id;
                    wpaap_log( 'IMG PICSUM[' . $i . '] OK att_id=' . $att_id );
                } else {
                    @unlink( $tmp_picsum );
                    wpaap_log( 'IMG PICSUM[' . $i . '] sideload FAIL: ' . $fb_att->get_error_message() );
                }
            } else {
                wpaap_log( 'IMG PICSUM[' . $i . '] download FAIL' );
            }
        }

        // Bước 3: Thay placeholder bằng URL local hoặc xóa figure nếu mọi nguồn đều thất bại
        if ( $att_id ) {
            $img_url      = wp_get_attachment_url( $att_id );
            $post_content = str_replace( $placeholder, esc_url( $img_url ), $post_content );
        } else {
            $post_content = preg_replace(
                '/<figure[^>]*>.*?' . preg_quote( $placeholder, '/' ) . '.*?<\/figure>/si',
                '',
                $post_content
            );
            $post_content = str_replace( $placeholder, '', $post_content );
            wpaap_log( 'IMG[' . $i . '] figure removed (no source)' );
        }
    }

    // Dọn placeholder còn sót
    $post_content = preg_replace( '/<figure[^>]*>.*?(?:AI|USER)_IMAGE_PLACEHOLDER_\d+.*?<\/figure>/si', '', $post_content );
    $post_content = preg_replace( '/(?:AI|USER)_IMAGE_PLACEHOLDER_\d+/', '', $post_content );
    $post_content = preg_replace( '/<figure[^>]*>(?!\s*<img)\s*(?:<figcaption[^>]*>.*?<\/figcaption>)?\s*<\/figure>/si', '', $post_content );

    wp_update_post( array( 'ID' => $post_id, 'post_content' => $post_content ) );

    // Gán ảnh đại diện từ ảnh đầu tiên sideload thành công (nếu chưa có)
    if ( $first_att_id && ! get_post_thumbnail_id( $post_id ) ) {
        set_post_thumbnail( $post_id, $first_att_id );
        wpaap_log( 'IMG SET THUMBNAIL post_id=' . $post_id . ' att_id=' . $first_att_id );
    }

    delete_post_meta( $post_id, '_wpaap_pending_images' );
    delete_post_meta( $post_id, '_wpaap_pending_title' );

    $elapsed = round( microtime( true ) - $t_start, 1 );
    wpaap_log( 'IMG PROCESS DONE post_id=' . $post_id . ' elapsed=' . $elapsed . 's' );
    return true;
}

// Background handler: giữ lại để tương thích nếu còn loopback cũ trong hàng đợi
add_action( 'wp_ajax_nopriv_wpaap_process_images_bg', 'wpaap_bg_process_images_handler' );
add_action( 'wp_ajax_wpaap_process_images_bg',        'wpaap_bg_process_images_handler' );
function wpaap_bg_process_images_handler() {
    // Chỉ cho phép gọi từ loopback nội bộ
    $remote_ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if ( ! in_array( $remote_ip, [ '127.0.0.1', '::1' ], true ) ) {
        wp_die( 'forbidden', '', [ 'response' => 403 ] );
    }

    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    $secret  = isset( $_POST['secret'] )  ? sanitize_text_field( $_POST['secret'] ) : '';

    wpaap_log( "BG IMAGES called post_id=$post_id" );

    if ( ! $post_id || wp_hash( 'wpaap_bg_imgs_' . $post_id ) !== $secret ) {
        wpaap_log( "BG IMAGES forbidden - secret mismatch post_id=$post_id" );
        wp_die( 'forbidden', '', array( 'response' => 403 ) );
    }

    @ignore_user_abort( true );
    if ( function_exists( 'set_time_limit' ) ) {
        @set_time_limit( 600 );
    }
    @ini_set( 'memory_limit', '256M' );

    wpaap_do_process_pending_images( $post_id );
    wp_die( 'ok' );
}

// Hàm xử lý AJAX load lại danh sách bài viết
add_action( 'wp_ajax_wpaap_reload_recent_posts', 'wpaap_ajax_reload_recent_posts_handler' );
function wpaap_ajax_reload_recent_posts_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );
    
    $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
    $page_slug = isset( $_POST['page_slug'] ) ? sanitize_key( $_POST['page_slug'] ) : 'wp-ai-auto-poster';
    
    $recent_posts = get_posts( array(
        'numberposts' => 6,
        'offset'      => $offset,
        'post_status' => 'pending',
        'post_type'   => 'post',
    ) );

    $has_more = count( $recent_posts ) > 5;
    if ( $has_more ) {
        $recent_posts = array_slice( $recent_posts, 0, 5 );
    }

    ob_start();
    if ( ! empty( $recent_posts ) ) {
        if ( $offset === 0 ) {
            echo '<div id="wpaap_bulk_bar" class="wpaap-bulk-bar" style="display:none;">';
            echo '<span id="wpaap_selected_count" class="wpaap-bulk-count">0 bài đã chọn</span>';
            echo '<button type="button" id="wpaap_bulk_approve_btn" class="wpaap-bulk-btn approve"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Duyệt đã chọn</button>';
            echo '<button type="button" id="wpaap_bulk_delete_btn" class="wpaap-bulk-btn delete"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg> Xóa đã chọn</button>';
            echo '</div>';
            echo '<table class="wpaap-posts-table"><thead><tr>';
            echo '<th class="cb"><input type="checkbox" id="wpaap_select_all" title="Chọn tất cả"></th>';
            echo '<th>Bài viết chờ duyệt</th><th>Ngày tạo</th><th>Loại</th><th>Trạng thái</th><th>Thao tác</th>';
            echo '</tr></thead><tbody id="wpaap_posts_tbody">';
        }

        foreach ( $recent_posts as $post ) {
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
                <td><span class="wpaap-status-badge wpaap-status-pending">Chờ duyệt</span></td>
                <td style="white-space:nowrap;">
                    <a href="<?php echo esc_url( $edit_link ); ?>" style="color:#6366f1;text-decoration:none;margin-right:8px;font-weight:600;font-size:13px;">Sửa</a>
                    <a href="<?php echo esc_url( $publish_url ); ?>" style="color:#16a34a;text-decoration:none;margin-right:8px;font-weight:600;font-size:13px;">Duyệt</a>
                    <a href="<?php echo esc_url( $delete_url ); ?>" style="color:#ef4444;text-decoration:none;font-weight:600;font-size:13px;" onclick="return confirm('Xóa bài viết này?');">Xóa</a>
                </td>
            </tr>
            <?php
        }

        if ( $offset === 0 ) {
            echo '</tbody></table>';
            if ( $has_more ) {
                echo '<div style="text-align:center;margin-top:15px;"><button id="wpaap_load_more_posts_btn" class="button wpaap-btn-secondary" data-offset="5">Xem thêm bài viết</button></div>';
            }
        }
    } else {
        if ( $offset === 0 ) {
            echo '<div class="wpaap-empty-posts"><p>Chưa có bài viết nào đang chờ duyệt.</p></div>';
        }
    }
    $html = ob_get_clean();
    wp_send_json_success( array( 'html' => $html, 'has_more' => $has_more ) );
}

// Handler AJAX chat AI
add_action( 'wp_ajax_wpaap_chat_ai', 'wpaap_ajax_chat_ai_handler' );
function wpaap_ajax_chat_ai_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );

    // Kiểm tra kết nối AI
    $ai_connected = false;
    if ( function_exists( 'wpaap_is_provider_connected' ) ) {
        foreach ( [ 'google', 'anthropic', 'openai' ] as $_prov ) {
            if ( wpaap_is_provider_connected( $_prov ) ) { $ai_connected = true; break; }
        }
    } else {
        $ai_connected = get_option( 'wpaap_core_connected', 'no' ) === 'yes';
    }
    if ( ! $ai_connected ) {
        wp_send_json_error( array( 'message' => '⚠️ Chưa kết nối AI. Vui lòng cấu hình API Key tại tab Kết nối AI.' ) );
    }

    $prompt = isset( $_POST['prompt'] ) ? sanitize_text_field( $_POST['prompt'] ) : '';

    if ( empty( $prompt ) ) {
        wp_send_json_error( array( 'message' => 'Vui lòng nhập tin nhắn!' ) );
    }

    $system_prompt = "Bạn là Cố vấn AI chuyên nghiệp của WordPress. Xin lưu ý thời điểm hiện tại đang là năm " . date('Y') . " (nếu người dùng hỏi về thời gian, hãy cập nhật theo mốc thời gian này). Trả lời câu hỏi sau của người dùng bằng tiếng Việt, thật ngắn gọn, chính xác và hữu ích:\n\n" . $prompt;

    $response = wpaap_call_ai_chat( $system_prompt );
 
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ) );
    }
 
    wp_send_json_success( array( 'response' => $response ) );
}

// Handler AJAX lưu cài đặt trang bảo trì
add_action( 'wp_ajax_whp_maintenance_toggle_enable', 'whp_ajax_maintenance_toggle_enable' );
function whp_ajax_maintenance_toggle_enable() {
    if ( ! check_ajax_referer( 'whp_maintenance_toggle', 'nonce', false ) ) {
        wp_send_json_error( 'invalid_nonce', 403 );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'forbidden', 403 );
    }
    $active  = isset( $_POST['active'] ) && $_POST['active'] === '1' ? '1' : '';
    $setting = get_option( 'whp_setting', [] );
    $setting['whp_maintenance_active'] = $active;
    update_option( 'whp_setting', $setting );
    update_option( 'whp_maintenance_active', $active );
    wp_send_json_success( [ 'active' => $active ] );
}

add_action( 'wp_ajax_wpaap_save_maintenance', 'wpaap_ajax_save_maintenance_handler' );
function wpaap_ajax_save_maintenance_handler() {
    check_ajax_referer( 'wpaap_maintenance_nonce', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Bạn không có quyền thực hiện thao tác này.' ) );
    }

    $option = get_option( 'whp_setting', [] );
    $option['whp_maintenance_active']      = isset( $_POST['whp_maintenance_active'] ) && $_POST['whp_maintenance_active'] === '1' ? '1' : '';
    $option['whp_maintenance_title']       = sanitize_text_field( $_POST['whp_maintenance_title'] ?? '' );
    $option['whp_maintenance_heading']     = sanitize_text_field( $_POST['whp_maintenance_heading'] ?? '' );
    $option['whp_maintenance_heading_sub'] = sanitize_text_field( $_POST['whp_maintenance_heading_sub'] ?? '' );
    $option['whp_maintenance_desc']        = sanitize_textarea_field( $_POST['whp_maintenance_desc'] ?? '' );
    $option['whp_maintenance_logo']        = esc_url_raw( $_POST['whp_maintenance_logo'] ?? '' );
    $option['whp_maintenance_countdown']   = sanitize_text_field( $_POST['whp_maintenance_countdown'] ?? '' );
    $tpl = sanitize_key( $_POST['whp_maintenance_template'] ?? '' );
    if ( in_array( $tpl, ['dark', 'light', 'gradient', 'construction', 'cyberpunk', 'corporate'] ) ) {
        $option['whp_maintenance_template'] = $tpl;
    }
    $option['whp_maintenance_phone']    = sanitize_text_field( $_POST['whp_maintenance_phone']    ?? '' );
    $option['whp_maintenance_email']    = sanitize_email( $_POST['whp_maintenance_email']          ?? '' );
    $option['whp_maintenance_facebook'] = esc_url_raw( $_POST['whp_maintenance_facebook']         ?? '' );
    $option['whp_maintenance_youtube']  = esc_url_raw( $_POST['whp_maintenance_youtube']          ?? '' );
    $option['whp_maintenance_zalo']     = sanitize_text_field( $_POST['whp_maintenance_zalo']     ?? '' );
    $option['whp_maintenance_tiktok']   = esc_url_raw( $_POST['whp_maintenance_tiktok']           ?? '' );
    update_option( 'whp_setting', $option );
    // Đồng bộ xóa individual option cũ để tránh fallback sai
    update_option( 'whp_maintenance_active', $option['whp_maintenance_active'] );

    $is_active = $option['whp_maintenance_active'] === '1';
    wp_send_json_success( array(
        'message'            => 'Đã lưu thành công.',
        'maintenance_active' => $is_active,
    ) );
}

// AJAX lấy thống kê truy cập trang bảo trì
add_action( 'wp_ajax_wpaap_get_maint_stats', 'wpaap_ajax_get_maint_stats_handler' );
function wpaap_ajax_get_maint_stats_handler() {
    check_ajax_referer( 'wpaap_maintenance_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Không có quyền.' ] );
    }

    $period = sanitize_key( $_POST['period'] ?? 'today' );

    $total_hits   = 0;
    $total_unique = 0;
    $prev_hits    = 0;
    $prev_unique  = 0;

    if ( $period === 'today' ) {
        $s = get_option( 'wpaap_maint_stats_' . gmdate( 'Y_m_d' ), [ 'hits' => 0, 'unique_ips' => [] ] );
        $total_hits   = intval( $s['hits'] );
        $total_unique = count( $s['unique_ips'] );
        $ps = get_option( 'wpaap_maint_stats_' . gmdate( 'Y_m_d', strtotime( '-1 day' ) ), [ 'hits' => 0, 'unique_ips' => [] ] );
        $prev_hits    = intval( $ps['hits'] );
        $prev_unique  = count( $ps['unique_ips'] );
    } elseif ( $period === 'yesterday' ) {
        $s = get_option( 'wpaap_maint_stats_' . gmdate( 'Y_m_d', strtotime( '-1 day' ) ), [ 'hits' => 0, 'unique_ips' => [] ] );
        $total_hits   = intval( $s['hits'] );
        $total_unique = count( $s['unique_ips'] );
        $ps = get_option( 'wpaap_maint_stats_' . gmdate( 'Y_m_d', strtotime( '-2 days' ) ), [ 'hits' => 0, 'unique_ips' => [] ] );
        $prev_hits    = intval( $ps['hits'] );
        $prev_unique  = count( $ps['unique_ips'] );
    } else {
        $days = in_array( intval( $period ), [ 7, 30 ] ) ? intval( $period ) : 7;
        for ( $i = 0; $i < $days; $i++ ) {
            $date_key = gmdate( 'Y_m_d', strtotime( "-{$i} days" ) );
            $s        = get_option( 'wpaap_maint_stats_' . $date_key, [ 'hits' => 0, 'unique_ips' => [] ] );
            $total_hits   += intval( $s['hits'] );
            $total_unique += count( $s['unique_ips'] );
        }
        for ( $i = $days; $i < $days * 2; $i++ ) {
            $date_key = gmdate( 'Y_m_d', strtotime( "-{$i} days" ) );
            $s        = get_option( 'wpaap_maint_stats_' . $date_key, [ 'hits' => 0, 'unique_ips' => [] ] );
            $prev_hits   += intval( $s['hits'] );
            $prev_unique += count( $s['unique_ips'] );
        }
    }

    $bounce      = $total_hits > 0 ? round( 100 * $total_unique / $total_hits, 1 ) : 0.0;
    $prev_bounce = $prev_hits  > 0 ? round( 100 * $prev_unique  / $prev_hits,  1 ) : 0.0;

    $pct = function( $curr, $prev ) {
        if ( $prev == 0 ) return $curr > 0 ? 100.0 : 0.0;
        return round( ( $curr - $prev ) / $prev * 100, 1 );
    };

    wp_send_json_success( [
        'hits'        => $total_hits,
        'pageviews'   => $total_hits,
        'unique'      => $total_unique,
        'bounce'      => $bounce,
        'hits_pct'    => $pct( $total_hits,   $prev_hits ),
        'pv_pct'      => $pct( $total_hits,   $prev_hits ),
        'unique_pct'  => $pct( $total_unique, $prev_unique ),
        'bounce_pct'  => $pct( $bounce,       $prev_bounce ),
    ] );
}

// AJAX chẩn đoán và kiểm tra kết nối AI
add_action( 'wp_ajax_wpaap_test_connection', 'wpaap_ajax_test_connection_handler' );
function wpaap_ajax_test_connection_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );

    $provider = isset( $_POST['provider'] ) ? sanitize_key( $_POST['provider'] ) : '';

    if ( ! in_array( $provider, ['google', 'anthropic', 'openai'] ) ) {
        wp_send_json_error( array( 'message' => 'Nhà cung cấp AI không hợp lệ.' ) );
    }

    $api_key = '';
    if ( $provider === 'google' ) {
        $api_key = get_option( 'connectors_gemini_api_key' ) ? get_option( 'connectors_gemini_api_key' ) : get_option( 'connectors_google_api_key' );
    } elseif ( $provider === 'anthropic' ) {
        $api_key = get_option( 'connectors_anthropic_api_key' );
    } elseif ( $provider === 'openai' ) {
        $api_key = get_option( 'connectors_openai_api_key' );
    }
    
    $api_key = trim( (string) $api_key );
    if ( empty( $api_key ) ) {
        wp_send_json_error( array( 'message' => 'Bạn chưa nhập API Key cho dịch vụ này.' ) );
    }

    // Chọn danh sách model chạy thử
    $test_models = [];
    if ( $provider === 'google' ) {
        $test_models = ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.5-flash-lite', 'gemini-2.0-flash', 'gemini-1.5-flash'];
    } elseif ( $provider === 'anthropic' ) {
        $test_models = ['claude-3-5-haiku-20241022', 'claude-3-haiku', 'claude-3-5-sonnet', 'claude-3-opus'];
    } elseif ( $provider === 'openai' ) {
        $test_models = ['gpt-4o-mini', 'gpt-4o', 'gpt-4-turbo', 'gpt-3.5-turbo'];
    }

    $prompt = "Say 'OK' in exactly 1 word.";

    $response = '';
    $last_error_msg = '';
    foreach ( $test_models as $model ) {
        $res = wpaap_call_ai_api_direct( $model, $prompt );
        if ( ! is_wp_error( $res ) ) {
            $response = $res;
            break;
        } else {
            $last_error_msg = $res->get_error_message();
        }
    }

    if ( empty( $response ) ) {
        $err_msg = $last_error_msg;
        
        // Gợi ý thêm danh sách model có sẵn nếu lỗi xảy ra với Google
        if ( $provider === 'google' && function_exists( 'wpaap_diagnose_gemini_models' ) ) {
            $diagnose = wpaap_diagnose_gemini_models( $api_key );
            if ( ! is_wp_error( $diagnose ) ) {
                $err_msg .= "\n\n[Gợi ý chẩn đoán] Các mô hình khả dụng thực tế của API Key này:\n" . implode("\n", $diagnose);
            }
        }
        
        wp_send_json_error( array( 'message' => $err_msg ) );
    }

    wp_send_json_success( array( 'response' => $response ) );
}

// Handler AJAX duyệt hàng loạt từ dashboard queue
add_action( 'wp_ajax_wpaap_bulk_publish', 'wpaap_ajax_bulk_publish_handler' );
function wpaap_ajax_bulk_publish_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );
    if ( ! current_user_can( 'publish_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Không có quyền duyệt bài viết.' ) );
    }
    $post_ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', (array) $_POST['post_ids'] ) : array();
    if ( empty( $post_ids ) ) {
        wp_send_json_error( array( 'message' => 'Không có bài viết nào được chọn.' ) );
    }
    $count = 0;
    foreach ( $post_ids as $post_id ) {
        if ( $post_id > 0 && current_user_can( 'publish_post', $post_id ) ) {
            wp_update_post( array( 'ID' => $post_id, 'post_status' => 'publish' ) );
            $count++;
        }
    }
    wp_send_json_success( array( 'message' => "Đã duyệt {$count} bài viết.", 'count' => $count ) );
}

// Handler AJAX xóa hàng loạt từ dashboard queue
add_action( 'wp_ajax_wpaap_bulk_delete', 'wpaap_ajax_bulk_delete_handler' );
function wpaap_ajax_bulk_delete_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );
    if ( ! current_user_can( 'delete_posts' ) ) {
        wp_send_json_error( array( 'message' => 'Không có quyền xóa bài viết.' ) );
    }
    $post_ids = isset( $_POST['post_ids'] ) ? array_map( 'intval', (array) $_POST['post_ids'] ) : array();
    $post_ids = array_filter( $post_ids ); // loại bỏ ID = 0
    if ( empty( $post_ids ) ) {
        wp_send_json_error( array( 'message' => 'Không có bài viết nào được chọn.' ) );
    }
    $count  = 0;
    $failed = array();
    foreach ( $post_ids as $post_id ) {
        if ( ! current_user_can( 'delete_post', $post_id ) ) {
            $failed[] = $post_id;
            continue;
        }
        // Dùng wp_delete_post(force=true) để xóa hẳn, tránh bài vẫn hiện lại qua trash
        $result = wp_delete_post( $post_id, true );
        if ( $result && ! is_wp_error( $result ) ) {
            $count++;
        } else {
            // Fallback: thử trash nếu force-delete thất bại
            $trashed = wp_trash_post( $post_id );
            if ( $trashed ) {
                $count++;
            } else {
                $failed[] = $post_id;
            }
        }
    }
    if ( $count === 0 ) {
        wp_send_json_error( array(
            'message' => 'Không thể xóa bài viết. Vui lòng kiểm tra quyền hoặc tải lại trang.',
            'count'   => 0,
        ) );
    }
    wp_send_json_success( array(
        'message' => "Đã xóa {$count} bài viết.",
        'count'   => $count,
        'failed'  => $failed,
    ) );
}

// ──────────────────────────────────────────────────────────────────────────────
// AI Payment — Xác minh biên lai chuyển khoản bằng AI Vision
// ──────────────────────────────────────────────────────────────────────────────

add_action( 'wp_ajax_wpaap_aipay_verify',               'wpaap_ajax_aipay_verify_handler' );
add_action( 'wp_ajax_wpaap_frontend_ai_verify',        'wpaap_ajax_frontend_ai_verify_handler' );
add_action( 'wp_ajax_nopriv_wpaap_frontend_ai_verify', 'wpaap_ajax_frontend_ai_verify_handler' );
add_action( 'wp_ajax_wpaap_aipay_order_action',        'wpaap_ajax_aipay_order_action_handler' );

function wpaap_aipay_build_email( $accent, $icon_svg, $title, $body_text, $cta_label = '', $cta_url = '' ) {
    $shop_name = get_bloginfo( 'name' );
    $cta_block = '';
    if ( $cta_label && $cta_url ) {
        $cta_block = '<p style="text-align:center;margin:24px 0 8px">
            <a href="' . esc_url( $cta_url ) . '" style="display:inline-block;background:' . esc_attr( $accent ) . ';color:#fff;text-decoration:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:14px">' . esc_html( $cta_label ) . '</a>
        </p>';
    }
    return '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="center" style="padding:32px 16px">
<table width="100%" style="max-width:520px;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08)">
  <tr><td style="background:linear-gradient(135deg,' . esc_attr( $accent ) . ' 0%,' . esc_attr( $accent ) . 'cc 100%);padding:28px 24px;text-align:center">
    <div style="width:56px;height:56px;background:rgba(255,255,255,.2);border-radius:50%;display:inline-block;text-align:center;line-height:56px;margin-bottom:12px"><span style="font-size:28px;color:#fff;line-height:1;vertical-align:middle">' . $icon_svg . '</span></div>
    <h1 style="color:#fff;font-size:18px;font-weight:700;margin:0">' . esc_html( $title ) . '</h1>
  </td></tr>
  <tr><td style="padding:24px">
    <p style="color:#374151;font-size:14px;line-height:1.7;margin:0 0 16px">' . wp_kses_post( $body_text ) . '</p>
    ' . $cta_block . '
  </td></tr>
  <tr><td style="background:#f9fafb;padding:14px 24px;text-align:center;border-top:1px solid #e5e7eb">
    <p style="color:#9ca3af;font-size:12px;margin:0">' . esc_html( $shop_name ) . '</p>
  </td></tr>
</table>
</td></tr></table></body></html>';
}

function wpaap_ajax_aipay_order_action_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Không có quyền thực hiện thao tác này.' ] );
    }

    $order_id = absint( $_POST['order_id'] ?? 0 );
    $type     = sanitize_key( $_POST['type'] ?? '' );
    $note     = sanitize_textarea_field( $_POST['note'] ?? '' );

    if ( ! $order_id || ! $type ) {
        wp_send_json_error( [ 'message' => 'Thiếu tham số.' ] );
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        wp_send_json_error( [ 'message' => 'Không tìm thấy đơn hàng #' . $order_id . '.' ] );
    }

    switch ( $type ) {
        case 'confirm':
            $order->update_status( 'processing', 'Admin xác nhận thanh toán qua AI Payment.' );
            $order->update_meta_data( '_whp_payment_confirmed_by', get_current_user_id() );
            $order->update_meta_data( '_whp_payment_confirmed_at', current_time( 'mysql' ) );
            $order->save();
            $customer_email = $order->get_billing_email();
            if ( $customer_email ) {
                $icon_svg = '&#10004;';
                $body     = 'Xin chào <strong>' . esc_html( $order->get_billing_first_name() ) . '</strong>,<br><br>Thanh toán cho đơn hàng <strong>#' . $order_id . '</strong> đã được xác nhận thành công. Chúng tôi đang xử lý đơn hàng của bạn và sẽ giao hàng trong thời gian sớm nhất.<br><br>Cảm ơn bạn đã tin tưởng mua hàng!';
                $html     = wpaap_aipay_build_email( '#16a34a', $icon_svg, 'Thanh toán đã xác nhận!', $body, 'Xem đơn hàng', $order->get_view_order_url() );
                wp_mail( $customer_email, sprintf( 'Xác nhận thanh toán — Đơn hàng #%d', $order_id ), $html, [ 'Content-Type: text/html; charset=UTF-8' ] );
            }
            wp_send_json_success( [ 'message' => 'Đã xác nhận thanh toán.' ] );
            break;

        case 'suspect':
            $order->add_order_note( 'AI Payment: Đánh dấu nghi ngờ bởi admin.' );
            $order->update_meta_data( '_whp_payment_suspect', 1 );
            $order->save();
            $customer_email = $order->get_billing_email();
            if ( $customer_email ) {
                $icon_svg = '&#9888;';
                $body     = 'Xin chào <strong>' . esc_html( $order->get_billing_first_name() ) . '</strong>,<br><br>Chúng tôi đang xem xét thông tin thanh toán cho đơn hàng <strong>#' . $order_id . '</strong>.<br><br>Chúng tôi có thể sẽ liên hệ với bạn để xác minh thêm. Vui lòng giữ liên lạc và chuẩn bị biên lai chuyển khoản để cung cấp khi cần.';
                $html     = wpaap_aipay_build_email( '#d97706', $icon_svg, 'Đơn hàng đang xem xét thanh toán', $body, 'Xem đơn hàng', $order->get_view_order_url() );
                wp_mail( $customer_email, sprintf( 'Đơn hàng #%d đang được xem xét', $order_id ), $html, [ 'Content-Type: text/html; charset=UTF-8' ] );
            }
            wp_send_json_success( [ 'message' => 'Đã đánh dấu nghi ngờ.' ] );
            break;

        case 'reject':
            $order->update_status( 'cancelled', 'Admin từ chối thanh toán qua AI Payment.' );
            $order->update_meta_data( '_whp_payment_rejected_by', get_current_user_id() );
            $order->save();
            $customer_email = $order->get_billing_email();
            if ( $customer_email ) {
                $icon_svg = '&#10006;';
                $body     = 'Xin chào <strong>' . esc_html( $order->get_billing_first_name() ) . '</strong>,<br><br>Rất tiếc, chúng tôi không thể xác minh thanh toán cho đơn hàng <strong>#' . $order_id . '</strong>. Đơn hàng đã bị hủy.<br><br>Nếu bạn đã thực hiện thanh toán, vui lòng liên hệ với chúng tôi ngay để được hỗ trợ.';
                $html     = wpaap_aipay_build_email( '#dc2626', $icon_svg, 'Thanh toán bị từ chối', $body );
                wp_mail( $customer_email, sprintf( 'Thông báo từ chối thanh toán — Đơn hàng #%d', $order_id ), $html, [ 'Content-Type: text/html; charset=UTF-8' ] );
            }
            wp_send_json_success( [ 'message' => 'Đã từ chối thanh toán.' ] );
            break;

        case 'request_receipt':
            $customer_email = $order->get_billing_email();
            if ( $customer_email ) {
                $icon_svg = '&#9993;';
                $body     = 'Xin chào <strong>' . esc_html( $order->get_billing_first_name() ) . '</strong>,<br><br>Chúng tôi chưa nhận được hoặc không xác minh được biên lai thanh toán cho đơn hàng <strong>#' . $order_id . '</strong>.<br><br>Vui lòng gửi lại ảnh biên lai chuyển khoản để chúng tôi có thể xử lý đơn hàng của bạn nhanh nhất. Bạn có thể trả lời email này hoặc truy cập trang đơn hàng để cập nhật.';
                $html     = wpaap_aipay_build_email( '#2563eb', $icon_svg, 'Vui lòng gửi lại biên lai', $body, 'Xem đơn hàng', $order->get_view_order_url() );
                wp_mail( $customer_email, sprintf( 'Vui lòng gửi lại biên lai — Đơn hàng #%d', $order_id ), $html, [ 'Content-Type: text/html; charset=UTF-8' ] );
            }
            $order->add_order_note( 'AI Payment: Đã gửi yêu cầu biên lai tới khách hàng.' );
            $order->save();
            wp_send_json_success( [ 'message' => 'Đã gửi yêu cầu biên lai.' ] );
            break;

        case 'note':
            if ( ! $note ) {
                wp_send_json_error( [ 'message' => 'Nội dung ghi chú không được để trống.' ] );
            }
            $order->add_order_note( '[Nội bộ] ' . $note );
            $order->save();
            wp_send_json_success( [ 'message' => 'Đã lưu ghi chú.' ] );
            break;

        default:
            wp_send_json_error( [ 'message' => 'Hành động không hợp lệ.' ] );
    }
}
function wpaap_ajax_aipay_verify_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Không có quyền thực hiện thao tác này.' ] );
    }

    $order_id = absint( $_POST['order_id'] ?? 0 );
    if ( ! $order_id ) {
        wp_send_json_error( [ 'message' => 'Thiếu order_id.' ] );
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        wp_send_json_error( [ 'message' => 'Không tìm thấy đơn hàng #' . $order_id ] );
    }

    $receipt_url = $order->get_meta( '_whp_transfer_receipt' );
    if ( empty( $receipt_url ) ) {
        wp_send_json_error( [ 'message' => 'Đơn hàng #' . $order_id . ' chưa có ảnh biên lai. Yêu cầu khách tải lên trước.' ] );
    }

    // Tải ảnh và chuyển sang base64
    $image_data = wpaap_aipay_fetch_image_base64( $receipt_url );
    if ( is_wp_error( $image_data ) ) {
        wp_send_json_error( [ 'message' => $image_data->get_error_message() ] );
    }

    // Lấy số tài khoản nhận tiền và tên phương thức thanh toán của shop
    $method_id     = $order->get_payment_method();
    $store_account = '';
    $wallet_acct_map = [
        'MB_WHP_Wallet_MoMo'      => 'account_number',
        'MB_WHP_Wallet_ZaloPay'   => 'number_zalopay',
        'MB_WHP_Wallet_VNPAY'     => 'number_vnpay',
        'MB_WHP_Wallet_ShopeePay' => 'number_shopee',
    ];
    $wallet_label_map = [
        'MB_WHP_Wallet_MoMo'      => 'MoMo',
        'MB_WHP_Wallet_ZaloPay'   => 'ZaloPay',
        'MB_WHP_Wallet_VNPAY'     => 'VNPAY',
        'MB_WHP_Wallet_ShopeePay' => 'ShopeePay',
    ];
    if ( isset( $wallet_acct_map[ $method_id ] ) && function_exists('WC') ) {
        $gw = WC()->payment_gateways()->payment_gateways()[ $method_id ] ?? null;
        if ( $gw ) $store_account = (string) $gw->get_option( $wallet_acct_map[ $method_id ] );
    } elseif ( $method_id === 'bacs' ) {
        $bacs_accounts = get_option( 'woocommerce_bacs_accounts', [] );
        $store_account = $bacs_accounts[0]['account_number'] ?? '';
    }
    $payment_label = $wallet_label_map[ $method_id ] ?? ( $method_id === 'bacs' ? 'Chuyển khoản ngân hàng' : $order->get_payment_method_title() );

    // Gọi AI Vision để phân tích
    $order_total = floatval( $order->get_total() );
    $result = wpaap_aipay_verify_with_vision( $image_data['data'], $image_data['mime'], $order_total, $order_id, $store_account, $payment_label );
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    // Lưu kết quả vào order meta
    $order->update_meta_data( '_whp_ai_verify_result', $result );
    $order->update_meta_data( '_whp_ai_verify_at', current_time( 'mysql' ) );
    $order->save();

    wp_send_json_success( $result );
}

/**
 * Tải ảnh từ URL và trả về dạng base64 + mime type.
 */
function wpaap_aipay_fetch_image_base64( $url ) {
    $ext_map = [ 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif' ];

    // Ưu tiên đọc từ filesystem để tránh loopback HTTP 403.
    // Strip protocol khi so sánh — tránh mismatch http:// vs https:// (Traefik/proxy).
    $upload_dir  = wp_upload_dir();
    $upload_url  = rtrim( $upload_dir['baseurl'], '/' );
    $upload_path = rtrim( $upload_dir['basedir'], '/' );

    $url_no_proto    = preg_replace( '#^https?://#i', '//', $url );
    $upload_no_proto = preg_replace( '#^https?://#i', '//', $upload_url );

    if ( strpos( $url_no_proto, $upload_no_proto ) === 0 ) {
        $rel_path   = substr( $url_no_proto, strlen( $upload_no_proto ) );
        $rel_path   = urldecode( $rel_path ); // xử lý %20 và ký tự đặc biệt
        $local_path = $upload_path . $rel_path;
        // Ngăn path traversal
        $local_path = realpath( $local_path ) ?: $local_path;

        if ( file_exists( $local_path ) && strpos( $local_path, $upload_path ) === 0 ) {
            $body = file_get_contents( $local_path );
            if ( empty( $body ) ) {
                return new WP_Error( 'empty_image', 'File ảnh rỗng.' );
            }
            $mime = 'image/jpeg';
            if ( function_exists( 'finfo_open' ) ) {
                $fi   = finfo_open( FILEINFO_MIME_TYPE );
                $mime = finfo_file( $fi, $local_path );
                finfo_close( $fi );
            } else {
                $ext  = strtolower( pathinfo( $local_path, PATHINFO_EXTENSION ) );
                $mime = $ext_map[ $ext ] ?? 'image/jpeg';
            }
            return [ 'data' => base64_encode( $body ), 'mime' => $mime ];
        }
    }

    // Fallback: HTTP download (cho URL ngoài wp-uploads)
    $response = wp_remote_get( $url, [ 'timeout' => 30, 'sslverify' => true ] );
    if ( is_wp_error( $response ) ) {
        return new WP_Error( 'download_failed', 'Không tải được ảnh: ' . $response->get_error_message() );
    }
    $code = wp_remote_retrieve_response_code( $response );
    if ( $code !== 200 ) {
        return new WP_Error( 'download_failed', "Không tải được ảnh (HTTP {$code})" );
    }
    $body = wp_remote_retrieve_body( $response );
    if ( empty( $body ) ) {
        return new WP_Error( 'empty_image', 'Ảnh trống hoặc không hợp lệ.' );
    }

    $mime = strtolower( trim( explode( ';', wp_remote_retrieve_header( $response, 'content-type' ) )[0] ) );
    if ( ! in_array( $mime, array_values( $ext_map ), true ) ) {
        $ext  = strtolower( pathinfo( $url, PATHINFO_EXTENSION ) );
        $mime = $ext_map[ $ext ] ?? 'image/jpeg';
    }

    return [ 'data' => base64_encode( $body ), 'mime' => $mime ];
}

/**
 * Gọi AI Vision API để phân tích biên lai và trả về structured result.
 */
function wpaap_aipay_verify_with_vision( $image_b64, $mime_type, $order_total, $order_id, $store_account = '', $payment_label = '' ) {
    // Tìm provider AI đã kết nối
    $provider = '';
    foreach ( [ 'google', 'openai', 'anthropic' ] as $p ) {
        if ( function_exists( 'wpaap_is_provider_connected' ) && wpaap_is_provider_connected( $p ) ) {
            $provider = $p;
            break;
        }
    }
    if ( empty( $provider ) ) {
        return new WP_Error( 'no_ai', 'Chưa kết nối AI. Vui lòng cấu hình API Key tại trang AI Kết nối.' );
    }

    $total_formatted  = number_format( $order_total, 0, ',', '.' ) . ' VND';
    $today_str        = current_time( 'd/m/Y' );
    $account_hint     = $store_account ? "Số tài khoản/số điện thoại nhận tiền của shop: {$store_account}" : '';
    $payment_hint     = $payment_label ? "Phương thức thanh toán khách chọn: {$payment_label}" : '';
    $is_en = strpos( get_locale(), 'en' ) === 0;
    $lang  = $is_en ? 'English' : 'Vietnamese';

    if ( $is_en ) {
        $account_match_rule = $store_account
            ? "\"account_match\": <true if the receiving account/phone in the receipt matches \"{$store_account}\", false if different or not found>,"
            : "\"account_match\": <true if a valid receiving account number can be read from the receipt, false if unclear>,";
        $bank_match_rule = $payment_label
            ? "\"bank_match\": <true if the receiving bank/wallet in the receipt is \"{$payment_label}\", false if different or unclear>,"
            : "\"bank_match\": <true if the receiving bank/wallet name can be clearly read, false if unclear>,";
        $flag_amount      = 'Insufficient amount';
        $flag_note        = 'Transfer note does not match order #' . $order_id;
        $flag_bank        = 'Receiving bank/wallet does not match the selected payment method';
        $flag_edited      = 'Suspected image tampering';
        $prompt_intro     = "Analyze the bank transfer receipt image. Return ONLY a JSON object, NO markdown, NO other text:";
        $prompt_fields    = <<<EOT
  "amount": <amount on receipt, integer without comma separators>,
  "bank": "<name of RECEIVING bank or wallet on receipt, e.g.: MoMo, Vietcombank, VP Bank — null if unclear>",
  "account_to": "<RECEIVING account number/phone on receipt or null>",
  "date": "<transaction date dd/mm/yyyy or null>",
  "time": "<time HH:mm or null>",
  "sender": "<sender name or null>",
  "note": "<transfer note content or null>",
  {$account_match_rule}
  {$bank_match_rule}
  "amount_match": <true if amount matches {$order_total} VND, false if not or unclear>,
  "note_match": <true if the transfer note mentions order #{$order_id} or is clearly related, false if unclear>,
  "time_match": <true if transaction date is reasonable: not after {$today_str} and not more than 7 days before {$today_str}; false if future date, too old, or unreadable>,
  "edited": <true if signs of image editing/Photoshop detected (unusual pixels, uneven fonts, abnormal color regions), false if natural>,
  "confidence": <overall confidence 0-100, 100=certainly valid transaction for this order>,
  "risk_score": <0.0 safe to 1.0 very risky>,
  "risk_flags": [<list of warning strings in English, empty if none>],
  "verdict": "<valid | suspicious | invalid>",
  "verdict_reason": "<short reason in English>"
EOT;
        $prompt_notes     = <<<EOT
Today: {$today_str}
Order #{$order_id} — Total to pay: {$total_formatted}
{$payment_hint}
{$account_hint}
Important: "bank" must be the RECEIVING bank/wallet (beneficiary), not the sending bank.
If the image is not a receipt or cannot be read: verdict=invalid, risk_score=1.0, confidence=0, edited=false.
If amount is less than the order total: amount_match=false, risk_flags include "{$flag_amount}".
If the transfer note does not mention order #{$order_id}: note_match=false, risk_flags include "{$flag_note}".
If bank_match=false and payment_label has a value: risk_flags include "{$flag_bank}".
If edited=true: risk_flags include "{$flag_edited}", verdict must be at least suspicious.
EOT;
    } else {
        $account_match_rule = $store_account
            ? "\"account_match\": <true nếu số TK/SĐT nhận tiền trong biên lai khớp \"{$store_account}\", false nếu khác hoặc không tìm thấy>,"
            : "\"account_match\": <true nếu đọc được số TK nhận tiền hợp lệ trong biên lai, false nếu không rõ>,";
        $bank_match_rule = $payment_label
            ? "\"bank_match\": <true nếu ngân hàng/ví nhận tiền trên biên lai là \"{$payment_label}\", false nếu khác hoặc không rõ>,"
            : "\"bank_match\": <true nếu đọc được tên ngân hàng/ví nhận tiền rõ ràng, false nếu không rõ>,";
        $flag_amount      = 'Số tiền không đủ';
        $flag_note        = 'Nội dung CK không khớp mã đơn';
        $flag_bank        = 'Ngân hàng/ví nhận không khớp phương thức thanh toán';
        $flag_edited      = 'Nghi ngờ ảnh bị chỉnh sửa';
        $prompt_intro     = "Phân tích ảnh biên lai chuyển khoản ngân hàng. Trả về DUY NHẤT JSON object, KHÔNG markdown, KHÔNG text khác:";
        $prompt_fields    = <<<EOT
  "amount": <số tiền trên biên lai, số nguyên không dấu phẩy>,
  "bank": "<tên ngân hàng hoặc ví NHẬN tiền trên biên lai, ví dụ: MoMo, Vietcombank, VP Bank — null nếu không rõ>",
  "account_to": "<số tài khoản/số điện thoại NHẬN tiền trên biên lai hoặc null>",
  "date": "<ngày giao dịch dd/mm/yyyy hoặc null>",
  "time": "<giờ HH:mm hoặc null>",
  "sender": "<tên người gửi hoặc null>",
  "note": "<nội dung ghi chú CK hoặc null>",
  {$account_match_rule}
  {$bank_match_rule}
  "amount_match": <true nếu số tiền khớp {$order_total} VND, false nếu không khớp hoặc không rõ>,
  "note_match": <true nếu nội dung CK đề cập mã đơn #{$order_id} hoặc liên quan rõ ràng, false nếu không rõ>,
  "time_match": <true nếu ngày giao dịch hợp lý: không sau {$today_str} và không quá 7 ngày trước {$today_str}; false nếu ngày trong tương lai, quá cũ hoặc không đọc được>,
  "edited": <true nếu phát hiện dấu hiệu chỉnh sửa ảnh/Photoshop (pixel lạ, font không đều, vùng màu bất thường), false nếu ảnh tự nhiên>,
  "confidence": <độ tin cậy tổng thể 0-100, 100=chắc chắn GD hợp lệ cho đơn này>,
  "risk_score": <0.0 an toàn đến 1.0 rất rủi ro>,
  "risk_flags": [<danh sách chuỗi cảnh báo tiếng Việt, rỗng nếu không có>],
  "verdict": "<valid | suspicious | invalid>",
  "verdict_reason": "<lý do ngắn bằng tiếng Việt>"
EOT;
        $prompt_notes     = <<<EOT
Ngày hôm nay: {$today_str}
Đơn hàng #{$order_id} — Tổng cần thanh toán: {$total_formatted}
{$payment_hint}
{$account_hint}
Lưu ý quan trọng: "bank" phải là ngân hàng/ví NHẬN tiền (bên thụ hưởng), không phải ngân hàng GỬI.
Nếu ảnh không phải biên lai hoặc không đọc được: verdict=invalid, risk_score=1.0, confidence=0, edited=false.
Nếu số tiền nhỏ hơn tổng đơn hàng: amount_match=false, risk_flags bao gồm "{$flag_amount}".
Nếu nội dung CK không đề cập mã đơn hàng #{$order_id}: note_match=false, risk_flags bao gồm "{$flag_note}".
Nếu bank_match=false và payment_label có giá trị: risk_flags bao gồm "{$flag_bank}".
Nếu edited=true: risk_flags bao gồm "{$flag_edited}", verdict tối thiểu là suspicious.
EOT;
    }

    $prompt = "{$prompt_intro}
{
{$prompt_fields}
}

{$prompt_notes}";

    $text = '';

    if ( $provider === 'google' ) {
        $api_key = trim( (string) ( get_option( 'connectors_gemini_api_key' ) ?: get_option( 'connectors_google_api_key' ) ) );
        $body    = [ 'contents' => [ [ 'parts' => [
            [ 'inline_data' => [ 'mime_type' => $mime_type, 'data' => $image_b64 ] ],
            [ 'text' => $prompt ],
        ] ] ] ];
        // Thử gemini-2.5-flash trước (stable), fallback gemini-2.0-flash; mỗi model thử v1 rồi v1beta
        foreach ( [ 'gemini-2.5-flash', 'gemini-2.0-flash' ] as $model ) {
            foreach ( [
                "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent?key={$api_key}",
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}",
            ] as $url ) {
                $r = wp_remote_post( $url, [
                    'headers' => [ 'Content-Type' => 'application/json' ],
                    'body'    => wp_json_encode( $body ),
                    'timeout' => 60,
                ] );
                if ( ! is_wp_error( $r ) && wp_remote_retrieve_response_code( $r ) === 200 ) {
                    $d    = json_decode( wp_remote_retrieve_body( $r ), true );
                    $text = $d['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if ( ! empty( $text ) ) break 2;
                }
            }
        }
        if ( empty( $text ) ) return new WP_Error( 'api_fail', 'Gemini Vision không phản hồi. Thử lại sau.' );

    } elseif ( $provider === 'openai' ) {
        $api_key = trim( (string) get_option( 'connectors_openai_api_key' ) );
        $body    = [
            'model'      => 'gpt-4o',
            'max_tokens' => 600,
            'messages'   => [ [ 'role' => 'user', 'content' => [
                [ 'type' => 'image_url', 'image_url' => [ 'url' => "data:{$mime_type};base64,{$image_b64}" ] ],
                [ 'type' => 'text', 'text' => $prompt ],
            ] ] ],
        ];
        $r = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
            'headers' => [ 'Content-Type' => 'application/json', 'Authorization' => "Bearer {$api_key}" ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 60,
        ] );
        if ( is_wp_error( $r ) || wp_remote_retrieve_response_code( $r ) !== 200 ) {
            return new WP_Error( 'api_fail', 'OpenAI Vision không phản hồi. Thử lại sau.' );
        }
        $d    = json_decode( wp_remote_retrieve_body( $r ), true );
        $text = $d['choices'][0]['message']['content'] ?? '';

    } elseif ( $provider === 'anthropic' ) {
        $api_key = trim( (string) get_option( 'connectors_anthropic_api_key' ) );
        $body    = [
            'model'      => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 600,
            'messages'   => [ [ 'role' => 'user', 'content' => [
                [ 'type' => 'image', 'source' => [ 'type' => 'base64', 'media_type' => $mime_type, 'data' => $image_b64 ] ],
                [ 'type' => 'text', 'text' => $prompt ],
            ] ] ],
        ];
        $r = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
            'headers' => [ 'Content-Type' => 'application/json', 'x-api-key' => $api_key, 'anthropic-version' => '2023-06-01' ],
            'body'    => wp_json_encode( $body ),
            'timeout' => 60,
        ] );
        if ( is_wp_error( $r ) || wp_remote_retrieve_response_code( $r ) !== 200 ) {
            return new WP_Error( 'api_fail', 'Claude Vision không phản hồi. Thử lại sau.' );
        }
        $d    = json_decode( wp_remote_retrieve_body( $r ), true );
        $text = $d['content'][0]['text'] ?? '';
    }

    // Strip markdown code fences nếu AI trả về ```json ... ```
    $text = preg_replace( '/^```(?:json)?\s*/im', '', $text );
    $text = preg_replace( '/```\s*$/m', '', $text );
    $text = trim( $text );

    $result = json_decode( $text, true );
    if ( ! is_array( $result ) ) {
        return new WP_Error( 'parse_fail', 'AI không trả về JSON hợp lệ. Thử lại.' );
    }

    $result['provider']    = $provider;
    $result['verified_at'] = current_time( 'mysql', true );
    return $result;
}

// ──────────────────────────────────────────────────────────────────────────────
// Frontend AI Verify — khách hàng tự kích hoạt sau khi xác nhận chuyển khoản
// ──────────────────────────────────────────────────────────────────────────────
function wpaap_ajax_frontend_ai_verify_handler() {
    $order_id = absint( $_POST['order_id'] ?? 0 );
    if ( ! $order_id ) {
        wp_send_json_error( [ 'message' => 'Thiếu order_id.' ] );
    }

    if ( ! check_ajax_referer( 'whp_confirm_transfer_' . $order_id, 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => 'Xác thực thất bại.' ] );
    }

    $order = function_exists( 'wc_get_order' ) ? wc_get_order( $order_id ) : null;
    if ( ! $order ) {
        wp_send_json_error( [ 'message' => 'Không tìm thấy đơn hàng.' ] );
    }

    // Kiểm tra quyền sở hữu đơn hàng với user đã đăng nhập
    if ( is_user_logged_in() && (int) $order->get_customer_id() !== get_current_user_id() ) {
        wp_send_json_error( [ 'message' => 'Không có quyền xem đơn hàng này.' ] );
    }

    $receipt_url = $order->get_meta( '_whp_transfer_receipt' );
    if ( empty( $receipt_url ) ) {
        wp_send_json_error( [ 'message' => 'Chưa có ảnh biên lai.' ] );
    }

    $image_data = wpaap_aipay_fetch_image_base64( $receipt_url );
    if ( is_wp_error( $image_data ) ) {
        wp_send_json_error( [ 'message' => $image_data->get_error_message() ] );
    }

    // Lấy số tài khoản nhận tiền và tên phương thức thanh toán của shop
    $method_id       = $order->get_payment_method();
    $store_account   = '';
    $wallet_acct_map = [
        'MB_WHP_Wallet_MoMo'      => 'account_number',
        'MB_WHP_Wallet_ZaloPay'   => 'number_zalopay',
        'MB_WHP_Wallet_VNPAY'     => 'number_vnpay',
        'MB_WHP_Wallet_ShopeePay' => 'number_shopee',
    ];
    $wallet_label_map = [
        'MB_WHP_Wallet_MoMo'      => 'MoMo',
        'MB_WHP_Wallet_ZaloPay'   => 'ZaloPay',
        'MB_WHP_Wallet_VNPAY'     => 'VNPAY',
        'MB_WHP_Wallet_ShopeePay' => 'ShopeePay',
    ];
    if ( isset( $wallet_acct_map[ $method_id ] ) && function_exists( 'WC' ) ) {
        $gw = WC()->payment_gateways()->payment_gateways()[ $method_id ] ?? null;
        if ( $gw ) $store_account = (string) $gw->get_option( $wallet_acct_map[ $method_id ] );
    } elseif ( $method_id === 'bacs' ) {
        $bacs_accounts = get_option( 'woocommerce_bacs_accounts', [] );
        $store_account = $bacs_accounts[0]['account_number'] ?? '';
    }
    $payment_label = $wallet_label_map[ $method_id ] ?? ( $method_id === 'bacs' ? 'Chuyển khoản ngân hàng' : $order->get_payment_method_title() );

    $order_total = floatval( $order->get_total() );
    $result = wpaap_aipay_verify_with_vision( $image_data['data'], $image_data['mime'], $order_total, $order_id, $store_account, $payment_label );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( [ 'message' => $result->get_error_message() ] );
    }

    // Lưu kết quả vào order meta
    $order->update_meta_data( '_whp_ai_verify_result', $result );
    $order->save();

    wp_send_json_success( $result );
}

// ── Order detail for risk popup ────────────────────────────────────────────
add_action( 'wp_ajax_wpaap_aipay_get_order_detail', 'wpaap_ajax_aipay_get_order_detail_handler' );
function wpaap_ajax_aipay_get_order_detail_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Không có quyền.' ] );
    }

    $order_id = absint( $_POST['order_id'] ?? 0 );
    if ( ! $order_id ) wp_send_json_error( [ 'message' => 'Thiếu order_id.' ] );

    $order = wc_get_order( $order_id );
    if ( ! $order ) wp_send_json_error( [ 'message' => 'Không tìm thấy đơn hàng.' ] );

    $ai_res    = $order->get_meta( '_whp_ai_verify_result' );
    $conf_at   = $order->get_meta( '_whp_transfer_confirmed_at' );
    $method_id = $order->get_payment_method();
    $wmap      = ['MB_WHP_Wallet_MoMo'=>'MoMo','MB_WHP_Wallet_ZaloPay'=>'ZaloPay','MB_WHP_Wallet_VNPAY'=>'VNPAY','MB_WHP_Wallet_ShopeePay'=>'ShopeePay'];
    $pay_label = $wmap[$method_id] ?? ( $method_id === 'bacs' ? 'Chuyển khoản NH' : $order->get_payment_method_title() );
    $bank      = $order->get_meta( '_whp_transfer_bank' ) ?: $pay_label;
    $sender    = $order->get_meta( '_whp_transfer_sender' );
    $last4     = $order->get_meta( '_whp_transfer_last4' );
    $order_url = get_edit_post_link( $order_id ) ?: admin_url( 'post.php?post=' . $order_id . '&action=edit' );

    wp_send_json_success( [
        'order_id'  => $order_id,
        'name'      => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ?: 'Khách',
        'email'     => $order->get_billing_email(),
        'phone'     => $order->get_billing_phone(),
        'total'     => number_format( floatval( $order->get_total() ), 0, ',', '.' ) . ' VND',
        'total_raw' => floatval( $order->get_total() ),
        'confirmed' => $conf_at ? date_i18n( 'd/m/Y H:i', strtotime( $conf_at ) ) : '—',
        'payment'   => $bank,
        'sender'    => $sender ? ( $sender . ( $last4 ? ' — ···' . $last4 : '' ) ) : '—',
        'receipt'   => $order->get_meta( '_whp_transfer_receipt' ) ?: '',
        'status'    => $order->get_status(),
        'status_lbl'=> wc_get_order_status_name( $order->get_status() ),
        'order_url' => esc_url( $order_url ),
        'ai_result' => is_array( $ai_res ) ? $ai_res : null,
    ] );
}

// ─── VERIFIED LIST (paginated + filtered) ────────────────────────────────────
add_action( 'wp_ajax_wpaap_vf_list', 'wpaap_vf_list_handler' );
function wpaap_vf_list_handler() {
    check_ajax_referer( 'wpaap_vf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $q    = sanitize_text_field( $_POST['q']    ?? '' );
    $from = sanitize_text_field( $_POST['from'] ?? '' );
    $to   = sanitize_text_field( $_POST['to']   ?? '' );
    $page = max( 1, absint( $_POST['page'] ?? 1 ) );
    $per  = min( 100, max( 10, absint( $_POST['per'] ?? 20 ) ) );

    $args = [
        'meta_query' => [ [ 'key' => '_whp_ai_verify_result', 'value' => '"verdict";s:5:"valid"', 'compare' => 'LIKE' ] ],
        'orderby'    => 'date',
        'order'      => 'DESC',
        'limit'      => $per,
        'offset'     => ( $page - 1 ) * $per,
        'paginate'   => true,
    ];

    if ( $from && $to )  $args['date_created'] = $from . '...' . $to;
    elseif ( $from )     $args['date_created'] = '>=' . $from;
    elseif ( $to )       $args['date_created'] = '<=' . $to;

    // Numeric = search by order ID
    if ( $q && preg_match( '/^#?\d+$/', $q ) ) {
        $args['post__in'] = [ absint( ltrim( $q, '#' ) ) ];
    }

    $result = wc_get_orders( $args );
    $rows   = [];

    foreach ( $result->orders as $order ) {
        $ai = $order->get_meta( '_whp_ai_verify_result' );
        if ( ! is_array( $ai ) || ( $ai['verdict'] ?? '' ) !== 'valid' ) continue;

        $name  = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ?: 'Khách';
        $email = $order->get_billing_email();

        // Text search on name / email (post-query, non-numeric)
        if ( $q && ! preg_match( '/^#?\d+$/', $q ) ) {
            if ( stripos( $name, $q ) === false && stripos( $email, $q ) === false ) continue;
        }

        $bank  = $order->get_meta( '_whp_transfer_bank' ) ?: ( $ai['bank'] ?? '—' );
        $ver   = $order->get_meta( '_whp_ai_verify_at' )  ?: ( $ai['verified_at'] ?? '' );
        $conf  = isset( $ai['confidence'] )  ? intval( $ai['confidence'] )
               : ( isset( $ai['risk_score'] ) ? intval( ( 1 - floatval( $ai['risk_score'] ) ) * 100 ) : 0 );
        $url   = get_edit_post_link( $order->get_id() ) ?: admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' );

        $rows[] = [
            'id'         => $order->get_id(),
            'name'       => $name,
            'email'      => $email,
            'amount'     => number_format( floatval( $order->get_total() ), 0, ',', '.' ),
            'bank'       => $bank,
            'ver_at'     => $ver ? wp_date( 'd/m/Y H:i', strtotime( $ver . ' UTC' ) ) : '—',
            'confidence' => $conf,
            'edit_url'   => esc_url( $url ),
        ];
    }

    wp_send_json_success( [
        'rows'  => $rows,
        'total' => $result->total,
        'pages' => $result->max_num_pages,
        'page'  => $page,
    ] );
}

// ─── VERIFIED STATS ───────────────────────────────────────────────────────────
add_action( 'wp_ajax_wpaap_vf_stats', 'wpaap_vf_stats_handler' );
function wpaap_vf_stats_handler() {
    check_ajax_referer( 'wpaap_vf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $range = sanitize_text_field( $_POST['range'] ?? 'today' );
    $now   = current_time( 'timestamp' );

    switch ( $range ) {
        case '7d':
            $from = date( 'Y-m-d', strtotime( '-6 days', $now ) );
            break;
        case '30d':
            $from = date( 'Y-m-d', strtotime( '-29 days', $now ) );
            break;
        default:
            $from = date( 'Y-m-d', $now );
    }
    $to = date( 'Y-m-d', $now );

    $orders = wc_get_orders( [
        'meta_query'   => [ [ 'key' => '_whp_ai_verify_result', 'value' => '"verdict";s:5:"valid"', 'compare' => 'LIKE' ] ],
        'date_created' => $from . '...' . $to,
        'limit'        => -1,
        'return'       => 'objects',
    ] );

    $count = count( $orders );
    $amount = 0; $banks = []; $providers = [];
    $hourly = array_fill( 0, 24, 0 );
    $times  = [];

    foreach ( $orders as $order ) {
        $amount += floatval( $order->get_total() );
        $ai = $order->get_meta( '_whp_ai_verify_result' );
        if ( ! is_array( $ai ) ) continue;

        $bank = $order->get_meta( '_whp_transfer_bank' ) ?: ( $ai['bank'] ?? 'Khác' );
        $banks[ $bank ] = ( $banks[ $bank ] ?? 0 ) + 1;

        $prov = $ai['provider'] ?? 'ocr';
        $providers[ $prov ] = ( $providers[ $prov ] ?? 0 ) + 1;

        $ver = $order->get_meta( '_whp_ai_verify_at' ) ?: ( $ai['verified_at'] ?? '' );
        if ( $ver ) {
            $h = intval( date( 'G', strtotime( $ver ) ) );
            if ( $h >= 0 && $h < 24 ) $hourly[ $h ]++;
            $created = $order->get_date_created();
            if ( $created ) {
                $diff = strtotime( $ver ) - $created->getTimestamp();
                if ( $diff > 0 && $diff < 86400 ) $times[] = $diff;
            }
        }
    }

    $avg = count( $times ) ? round( array_sum( $times ) / count( $times ) ) : 0;

    arsort( $banks );
    $banks_out = [];
    $top5 = array_slice( $banks, 0, 5, true );
    foreach ( $top5 as $bn => $bc ) {
        $banks_out[] = [ 'name' => $bn, 'count' => $bc, 'pct' => $count ? round( $bc / $count * 100 ) : 0 ];
    }
    if ( count( $banks ) > 5 ) {
        $rest = array_sum( array_slice( array_values( $banks ), 5 ) );
        $banks_out[] = [ 'name' => 'Khác', 'count' => $rest, 'pct' => $count ? round( $rest / $count * 100 ) : 0 ];
    }

    $pmap = [ 'google' => 'Google Gemini', 'openai' => 'OpenAI', 'anthropic' => 'Claude', 'ocr' => 'AI OCR' ];
    $provs = [];
    foreach ( $providers as $pk => $pc ) {
        $provs[] = [ 'name' => $pmap[ $pk ] ?? ucfirst( $pk ), 'count' => $pc, 'pct' => $count ? round( $pc / $count * 100 ) : 0 ];
    }

    wp_send_json_success( [
        'count'     => $count,
        'amount'    => $amount,
        'avg_time'  => $avg,
        'banks'     => $banks_out,
        'providers' => $provs,
        'hourly'    => array_values( $hourly ),
    ] );
}

// ─── HELPER: đóng kết nối HTTP trả JSON cho browser, PHP tiếp tục chạy ────────
// Dùng thay thế wp_remote_post(blocking=false) để tránh Traefik IP-whitelist block.
// Apache mod_php: Connection:close + Content-Length → browser nhận xong, TCP đóng,
// PHP vẫn chạy đến exit(). PHP-FPM: fastcgi_finish_request() xử lý tương tự.
function wpaap_close_and_continue( array $data ) {
    $body = wp_json_encode( $data );

    while ( ob_get_level() > 0 ) ob_end_clean();

    @ignore_user_abort( true );
    header( 'Content-Type: application/json; charset=utf-8' );
    header( 'Connection: close' );
    header( 'Cache-Control: no-store' );
    header( 'Content-Length: ' . strlen( $body ) );

    ob_start();
    echo $body;
    ob_end_flush();
    flush();

    if ( function_exists( 'fastcgi_finish_request' ) ) {
        fastcgi_finish_request();
    }
}

// ─── QUEUE POST JOB: trả job_id ngay, AI chạy qua loopback handler riêng ────
// Lý do dùng loopback thay vì inline sau close_and_continue:
// Traefik cắt backend connection sau 60-120s dù đã gửi response cho browser,
// khiến PHP process bị kill trước khi AI generation hoàn thành.
// Loopback 127.0.0.1 → Apache trực tiếp: không qua Traefik, không bị timeout.
// ─── QUEUE POST JOB: trả job_id ngay, AI chạy qua loopback handler riêng ────
add_action( 'wp_ajax_wpaap_queue_post_job', 'wpaap_ajax_queue_post_job' );
function wpaap_ajax_queue_post_job() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );

    $user_prompt = sanitize_textarea_field( $_POST['prompt'] ?? '' );
    if ( ! $user_prompt ) {
        wp_send_json_error( [ 'message' => 'Vui lòng nhập mô tả rõ ràng cho bài viết!' ] );
    }

    global $wpdb;
    $jobs_table = $wpdb->prefix . 'ai_jobs';
    $user_id  = get_current_user_id();

    // cancel any previous queued/running job for this user to release worker
    $old_job_uuid = get_transient( 'wpaap_active_job_' . $user_id );
    if ( $old_job_uuid ) {
        $wpdb->update( $jobs_table, [ 'status' => 'cancelled' ], [ 'uuid' => $old_job_uuid, 'status' => 'queued' ] );
        $wpdb->update( $jobs_table, [ 'status' => 'cancelled' ], [ 'uuid' => $old_job_uuid, 'status' => 'running' ] );
    }

    $job_uuid       = wp_generate_password( 32, false );
    $categories     = array_map( 'intval', (array) ( $_POST['categories'] ?? [] ) );
    $categories_str = implode( ',', $categories );
    $raw_length     = sanitize_text_field( $_POST['article_length'] ?? 'medium' );
    $article_length = in_array( $raw_length, [ 'short', 'medium', 'long' ], true ) ? $raw_length : 'medium';

    $wpdb->insert( $jobs_table, [
        'uuid'              => $job_uuid,
        'user_id'           => $user_id,
        'prompt'            => $user_prompt,
        'image_id'          => intval( $_POST['image_id'] ?? 0 ),
        'content_image_ids' => sanitize_text_field( $_POST['content_image_ids'] ?? '' ),
        'categories'        => $categories_str,
        'tags'              => sanitize_text_field( $_POST['tags'] ?? '' ),
        'ai_model'          => get_option( 'wpaap_default_ai_model', 'gemini-2.5-flash-lite' ),
        'status'            => 'queued',
        'current_step'      => 'init',
        'progress'          => 0,
        'payload'           => wp_json_encode( [ 'article_length' => $article_length ] ),
    ] );

    set_transient( 'wpaap_active_job_' . $user_id, $job_uuid, 1800 ); // track active job
    wpaap_log( "QUEUE job=$job_uuid user=$user_id model=" . get_option( 'wpaap_default_ai_model' ) );

    // Dùng WP-Cron thay loopback — hoạt động trên cả Shared Hosting lẫn VPS/Docker
    wpaap_schedule_ai_step( $job_uuid );
    wpaap_log( "QUEUE scheduled WP-Cron step for job=$job_uuid" );
    wp_send_json_success( [ 'job_id' => $job_uuid ] );
}

// ─── HELPER: Schedule WP-Cron bước tiếp theo + spawn cron ngay ───────────────
function wpaap_schedule_ai_step( $job_id ) {
    // Luôn schedule WP-Cron (fallback cho hosting không block cron)
    $existing = wp_next_scheduled( 'wpaap_ai_process_step', [ $job_id ] );
    if ( $existing ) {
        wp_unschedule_event( $existing, 'wpaap_ai_process_step', [ $job_id ] );
    }
    wp_schedule_single_event( time(), 'wpaap_ai_process_step', [ $job_id ] );

    $secret   = wp_hash( 'wpaap_ai_bg_' . $job_id );
    $host     = parse_url( site_url(), PHP_URL_HOST );
    $ajax_path = parse_url( admin_url( 'admin-ajax.php' ), PHP_URL_PATH );
    $ua        = 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url( '/' );

    $post_args = [
        'blocking'   => false,
        'sslverify'  => false,
        'timeout'    => 0.01,
        'user-agent' => $ua,
        'body'       => [
            'action' => 'wpaap_run_ai_bg',
            'job_id' => $job_id,
            'secret' => $secret,
        ],
    ];

    // 1. Loopback 127.0.0.1 → admin-ajax.php (bypasses Traefik VÀ DISABLE_WP_CRON)
    $r = wp_remote_post( 'http://127.0.0.1' . $ajax_path, array_merge( $post_args, [
        'headers' => [ 'Host' => $host ],
    ] ) );
    if ( ! is_wp_error( $r ) ) {
        wpaap_log( "SCHEDULE loopback-127 OK job=$job_id" );
        return;
    }
    wpaap_log( "SCHEDULE loopback-127 FAIL: " . $r->get_error_message() . " job=$job_id" );

    // 2. Thử external admin_url (shared hosting, không có Traefik)
    $r2 = wp_remote_post( admin_url( 'admin-ajax.php' ), $post_args );
    if ( ! is_wp_error( $r2 ) ) {
        wpaap_log( "SCHEDULE external-ajax OK job=$job_id" );
        return;
    }
    wpaap_log( "SCHEDULE external-ajax FAIL: " . $r2->get_error_message() . " job=$job_id" );

    // 3. Fallback: kích hoạt wp-cron.php nếu không bị tắt
    if ( ! defined( 'DISABLE_WP_CRON' ) || ! DISABLE_WP_CRON ) {
        wp_remote_get( site_url( 'wp-cron.php' ), [
            'blocking'   => false,
            'sslverify'  => false,
            'timeout'    => 0.01,
            'user-agent' => $ua,
        ] );
        wpaap_log( "SCHEDULE wp-cron fallback triggered job=$job_id" );
    } else {
        wpaap_log( "SCHEDULE all triggers failed, DISABLE_WP_CRON=true job=$job_id" );
    }
}

// ─── CORE: Xử lý 1 bước AI job (dùng chung cho WP-Cron và HTTP handler) ──────
function wpaap_execute_ai_job_step( $job_id ) {
    if ( ! $job_id ) return;

    global $wpdb;
    $jobs_table = $wpdb->prefix . 'ai_jobs';
    $job = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $jobs_table WHERE uuid = %s", $job_id ) );
    if ( ! $job ) {
        wpaap_log( "STEP EXEC job NOT_FOUND job=$job_id" );
        return;
    }
    if ( in_array( $job->status, [ 'completed', 'failed', 'cancelled' ], true ) ) {
        wpaap_log( "STEP EXEC job already finished status={$job->status} job=$job_id" );
        return;
    }

    $lock_key = 'wpaap_lock_' . $job_id;
    if ( get_transient( $lock_key ) ) {
        wpaap_log( "STEP EXEC locked, skipping job=$job_id" );
        return;
    }
    set_transient( $lock_key, '1', 30 );

    @ignore_user_abort( true );
    if ( function_exists( 'set_time_limit' ) ) @set_time_limit( 90 );
    @ini_set( 'memory_limit', '256M' );

    if ( ! empty( $job->user_id ) ) {
        wp_set_current_user( $job->user_id );
    }

    $payload = ! empty( $job->payload ) ? json_decode( $job->payload, true ) : [];
    if ( ! is_array( $payload ) ) {
        $payload = [];
    }

    $current_step = $job->current_step;
    $next_step    = $current_step;
    $progress     = $job->progress;
    $error        = null;

    wpaap_log( "STEP EXEC job=$job_id step=$current_step progress=$progress%" );

    if ( $job->status === 'queued' ) {
        $wpdb->update( $jobs_table, [
            'status'     => 'running',
            'started_at' => current_time( 'mysql' ),
        ], [ 'uuid' => $job_id ] );
    }

    try {
        switch ( $current_step ) {
            case 'init':
                wpaap_log_job_step( $job_id, 'Khởi tạo công việc tạo bài viết bằng AI...' );
                $next_step = 'outline';
                $progress  = 10;
                break;

            case 'outline':
                wpaap_log_job_step( $job_id, 'Đang phân tích chủ đề và sinh dàn ý bài viết...' );
                $res = wpaap_step_generate_outline( $job, $payload );
                if ( is_wp_error( $res ) ) {
                    $error = $res->get_error_message();
                } else {
                    wpaap_log_job_step( $job_id, 'Sinh dàn ý thành công với ' . count( $payload['outline'] ) . ' phần chính.' );
                    $next_step = 'intro';
                    $progress  = 20;
                }
                break;

            case 'intro':
                wpaap_log_job_step( $job_id, 'Đang viết đoạn mở đầu và tóm tắt bài viết...' );
                $res = wpaap_step_generate_intro( $job, $payload );
                if ( is_wp_error( $res ) ) {
                    $error = $res->get_error_message();
                } else {
                    wpaap_log_job_step( $job_id, 'Đoạn mở đầu viết thành công.' );
                    $next_step = 'section_0';
                    $progress  = 30;
                }
                break;

            case 'conclusion':
                wpaap_log_job_step( $job_id, 'Đang soạn phần kết luận, FAQ và checklist...' );
                $res = wpaap_step_generate_conclusion( $job, $payload );
                if ( is_wp_error( $res ) ) {
                    $error = $res->get_error_message();
                } else {
                    wpaap_log_job_step( $job_id, 'Phần kết luận viết thành công.' );
                    $next_step = 'metadata';
                    $progress  = 85;
                }
                break;

            case 'metadata':
                wpaap_log_job_step( $job_id, 'Đang phân tích từ khóa và tạo thẻ meta SEO...' );
                $res = wpaap_step_generate_metadata( $job, $payload );
                if ( is_wp_error( $res ) ) {
                    $error = $res->get_error_message();
                } else {
                    wpaap_log_job_step( $job_id, 'Đã tạo thẻ SEO, meta description và slug thành công.' );
                    $next_step = 'publish';
                    $progress  = 90;
                }
                break;

            case 'publish':
                wpaap_log_job_step( $job_id, 'Đang tổng hợp nội dung và đăng bản nháp chờ duyệt...' );
                $res = wpaap_step_publish_post( $job, $payload );
                if ( is_wp_error( $res ) ) {
                    $error = $res->get_error_message();
                } else {
                    wpaap_log_job_step( $job_id, 'Đăng bài viết nháp thành công ID: ' . $payload['post_id'] );
                    $next_step = 'images';
                    $progress  = 95;
                }
                break;

            case 'images':
                $post_id = $payload['post_id'] ?? 0;
                if ( $post_id ) {
                    wpaap_log_job_step( $job_id, 'Đang tiến hành xử lý tải ảnh minh họa...' );
                    $res = wpaap_step_process_images( $job, $payload );
                    if ( is_wp_error( $res ) ) {
                        wpaap_log_job_step( $job_id, 'Lỗi tải ảnh: ' . $res->get_error_message() . '. Tiếp tục hoàn tất bài viết.' );
                    } else {
                        wpaap_log_job_step( $job_id, 'Xử lý hình ảnh thành công.' );
                    }
                }
                $next_step = 'done';
                $progress  = 100;
                break;

            default:
                if ( strpos( $current_step, 'section_' ) === 0 ) {
                    $h2_index = intval( substr( $current_step, 8 ) );
                    $outline  = $payload['outline'] ?? [];
                    $total_h2 = count( $outline );

                    if ( $h2_index >= 0 && $h2_index < $total_h2 ) {
                        $h2_title = $outline[ $h2_index ]['h2'] ?? '';
                        wpaap_log_job_step( $job_id, sprintf( 'Đang biên soạn nội dung Phần %d: "%s"...', $h2_index + 1, $h2_title ) );
                        $res = wpaap_step_generate_section( $job, $payload, $h2_index );
                        if ( is_wp_error( $res ) ) {
                            $error = $res->get_error_message();
                        } else {
                            wpaap_log_job_step( $job_id, sprintf( 'Hoàn thành Phần %d.', $h2_index + 1 ) );
                            $next_h2 = $h2_index + 1;
                            if ( $next_h2 < $total_h2 ) {
                                $next_step = 'section_' . $next_h2;
                                $progress  = min( 80, 30 + round( ( $next_h2 / $total_h2 ) * 50 ) );
                            } else {
                                $next_step = 'conclusion';
                                $progress  = 80;
                            }
                        }
                    } else {
                        $next_step = 'conclusion';
                        $progress  = 80;
                    }
                } else {
                    $next_step = 'done';
                    $progress  = 100;
                }
                break;
        }
    } catch ( \Throwable $e ) {
        $error = 'Ngoại lệ runtime: ' . $e->getMessage() . ' tại ' . $e->getFile() . ':' . $e->getLine();
    }

    delete_transient( $lock_key );

    if ( $error ) {
        wpaap_log_job_step( $job_id, '❌ Thất bại: ' . $error );
        $wpdb->update( $jobs_table, [
            'status'        => 'failed',
            'error_message' => $error,
        ], [ 'uuid' => $job_id ] );
        return;
    }

    $update_fields = [
        'current_step' => $next_step,
        'progress'     => $progress,
        'payload'      => wp_json_encode( $payload ),
    ];
    if ( $next_step === 'done' ) {
        $update_fields['status']       = 'completed';
        $update_fields['completed_at'] = current_time( 'mysql' );
        $update_fields['post_id']      = $payload['post_id'] ?? 0;
        wpaap_log_job_step( $job_id, '🎉 Công việc tạo bài viết hoàn tất thành công!' );
    }

    $wpdb->update( $jobs_table, $update_fields, [ 'uuid' => $job_id ] );

    // Schedule bước tiếp theo qua WP-Cron (không loopback)
    if ( $next_step !== 'done' ) {
        wpaap_schedule_ai_step( $job_id );
    }
}

// ─── WP-CRON ENTRY: hook chạy bước AI ────────────────────────────────────────
add_action( 'wpaap_ai_process_step', 'wpaap_execute_ai_job_step' );

// ─── HTTP ENTRY: giữ lại để tương thích ngược nếu còn request cũ ─────────────
add_action( 'wp_ajax_nopriv_wpaap_run_ai_bg', 'wpaap_bg_run_ai_handler' );
add_action( 'wp_ajax_wpaap_run_ai_bg',        'wpaap_bg_run_ai_handler' );
function wpaap_bg_run_ai_handler() {
    $job_id = sanitize_text_field( $_POST['job_id'] ?? '' );
    $secret = sanitize_text_field( $_POST['secret'] ?? '' );
    wpaap_log( "BG HANDLER called job=$job_id ip=" . ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
    if ( ! $job_id || wp_hash( 'wpaap_ai_bg_' . $job_id ) !== $secret ) {
        wp_die( 'forbidden', '', [ 'response' => 403 ] );
    }
    wpaap_execute_ai_job_step( $job_id );
    wp_die( 'ok' );
}

// ─── POLL JOB STATUS ──────────────────────────────────────────────────────────
add_action( 'wp_ajax_wpaap_check_post_job', 'wpaap_ajax_check_post_job' );
function wpaap_ajax_check_post_job() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );
    $job_id = sanitize_text_field( $_POST['job_id'] ?? '' );
    
    global $wpdb;
    $jobs_table = $wpdb->prefix . 'ai_jobs';
    $logs_table = $wpdb->prefix . 'ai_job_logs';

    $job = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $jobs_table WHERE uuid = %s", $job_id ) );
    if ( ! $job ) {
        wp_send_json_error( [ 'message' => 'Job không tìm thấy', 'status' => 'not_found' ] );
    }

    // Get logs
    $logs = $wpdb->get_col( $wpdb->prepare( "SELECT message FROM $logs_table WHERE job_uuid = %s ORDER BY id ASC", $job_id ) );

    $response_data = [
        'job_id'            => $job->uuid,
        'status'            => $job->status,
        'current_step'      => $job->current_step,
        'progress'          => intval( $job->progress ),
        'error'             => $job->error_message,
        'post_id'           => intval( $job->post_id ),
        'logs'              => $logs ?: [],
        'edit_url'          => $job->post_id ? get_edit_post_link( $job->post_id, 'raw' ) : '',
        'view_url'          => $job->post_id ? get_permalink( $job->post_id ) : '',
    ];

    if ( in_array( $job->status, [ 'completed', 'failed', 'cancelled' ], true ) ) {
        $user_id = get_current_user_id();
        delete_transient( 'wpaap_active_job_' . $user_id );
    }

    wp_send_json_success( $response_data );
}

// ─── RESUME ACTIVE RUNNING JOB ───────────────────────────────────────────────
add_action( 'wp_ajax_wpaap_resume_active_job', 'wpaap_ajax_resume_active_job' );
function wpaap_ajax_resume_active_job() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );
    $user_id = get_current_user_id();
    $job_id = get_transient( 'wpaap_active_job_' . $user_id );

    if ( ! $job_id ) {
        wp_send_json_error( [ 'message' => 'Không có Job nào đang chạy.' ] );
    }

    global $wpdb;
    $jobs_table = $wpdb->prefix . 'ai_jobs';
    $job = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $jobs_table WHERE uuid = %s", $job_id ) );

    if ( ! $job || ! in_array( $job->status, [ 'queued', 'running' ], true ) ) {
        delete_transient( 'wpaap_active_job_' . $user_id );
        wp_send_json_error( [ 'message' => 'Job đã kết thúc hoặc không tồn tại.' ] );
    }

    // Thực thi bước hiện tại inline (fallback cho shared hosting mà background trigger thất bại).
    // Lock transient bên trong wpaap_execute_ai_job_step ngăn double-execution.
    // Sau khi bước xong, nó tự schedule bước tiếp theo qua wpaap_schedule_ai_step.
    wpaap_log( "RESUME inline-exec start step={$job->current_step} job={$job->uuid}" );
    wpaap_execute_ai_job_step( $job->uuid );
    wpaap_log( "RESUME inline-exec done job={$job->uuid}" );

    wp_send_json_success( [ 'job_id' => $job->uuid ] );
}

// ─── AUTO-SAVE AIPAY ENABLE TOGGLE ───────────────────────────────────────────
add_action( 'wp_ajax_wpaap_aipay_toggle_enable', 'wpaap_ajax_aipay_toggle_enable_handler' );
function wpaap_ajax_aipay_toggle_enable_handler() {
    check_ajax_referer( 'wpaap_generate_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => 'Không có quyền thực hiện.' ] );
        return;
    }
    $value  = ( isset( $_POST['value'] ) && $_POST['value'] === '1' ) ? '1' : '0';
    // Block enabling if no AI provider is connected
    if ( $value === '1' && function_exists( 'wpaap_is_provider_connected' ) ) {
        $any_connected = wpaap_is_provider_connected( 'google' ) || wpaap_is_provider_connected( 'anthropic' ) || wpaap_is_provider_connected( 'openai' );
        if ( ! $any_connected ) {
            wp_send_json_error( [ 'message' => __( 'Cần kết nối AI trước khi bật AI Thanh Toán.', 'whp' ) ] );
            return;
        }
    }
    $option = get_option( 'whp_setting', [] );
    $option['whp_aipay_enable'] = $value;
    update_option( 'whp_setting', $option );
    $msg = $value === '1' ? __( 'Đã bật AI Thanh Toán', 'whp' ) : __( 'Đã tắt AI Thanh Toán', 'whp' );
    wp_send_json_success( [ 'message' => $msg, 'value' => $value ] );
}

// ─── DATABASE INITIALIZATION FOR AI WRITER QUEUE ───────────────────────────
function wpaap_create_queue_tables() {
    global $wpdb;
    $jobs_table = $wpdb->prefix . 'ai_jobs';
    $logs_table = $wpdb->prefix . 'ai_job_logs';

    if ( $wpdb->get_var( "SHOW TABLES LIKE '$jobs_table'" ) === $jobs_table ) {
        return;
    }

    $charset_collate = $wpdb->get_charset_collate();

    $sql_jobs = "CREATE TABLE $jobs_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        uuid varchar(64) NOT NULL,
        user_id bigint(20) unsigned NOT NULL,
        prompt text NOT NULL,
        image_id int(11) DEFAULT 0,
        content_image_ids text DEFAULT '',
        categories text DEFAULT '',
        tags text DEFAULT '',
        ai_model varchar(64) DEFAULT '',
        status varchar(20) DEFAULT 'queued',
        current_step varchar(64) DEFAULT 'init',
        progress int(3) DEFAULT 0,
        retry_count int(3) DEFAULT 0,
        error_message text DEFAULT '',
        post_id bigint(20) unsigned DEFAULT 0,
        payload longtext DEFAULT '',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        started_at datetime DEFAULT NULL,
        completed_at datetime DEFAULT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY uuid (uuid)
    ) $charset_collate;";

    $sql_logs = "CREATE TABLE $logs_table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        job_uuid varchar(64) NOT NULL,
        message text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY job_uuid (job_uuid)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql_jobs );
    dbDelta( $sql_logs );
}
add_action( 'admin_init', 'wpaap_create_queue_tables' );

function wpaap_log_job_step( $job_uuid, $message ) {
    global $wpdb;
    $logs_table = $wpdb->prefix . 'ai_job_logs';
    $wpdb->insert( $logs_table, [
        'job_uuid' => $job_uuid,
        'message'  => $message,
    ], [ '%s', '%s' ] );
}
