<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
function wpaap_debug_log( $msg ) {
    wpaap_log( $msg );
}

// Helper: trích keywords tiếng Anh từ prompt để tìm ảnh đúng chủ đề
function wpaap_extract_keywords( $prompt, $max = 4 ) {
    $stop = array(
        'a','an','the','of','in','on','at','to','for','with','and','or','but',
        'is','are','was','were','be','been','being','have','has','had',
        'this','that','these','those','it','its','very','quite','more','most',
        'from','into','over','under','about','after','before','between',
        'photorealistic','highly','detailed','image','photo','picture','showing',
        'depicting','illustrating','representing','featuring','displaying',
        'professional','modern','beautiful','stunning','amazing','high','quality',
        'resolution','concept','design','style','background','foreground',
    );

    $words    = preg_split( '/[\s,.\-_\/\(\)\[\]]+/', strtolower( trim( $prompt ) ) );
    $keywords = array();
    foreach ( $words as $w ) {
        $w = trim( $w );
        if ( strlen( $w ) > 3 && ! in_array( $w, $stop, true ) && ctype_alpha( $w ) ) {
            $keywords[] = $w;
            if ( count( $keywords ) >= $max ) break;
        }
    }

    return implode( ' ', $keywords );
}

// Tìm ảnh qua Pexels API (cần API key — cấu hình tại trang Kết Nối AI)
function wpaap_search_bing_image( $query, $timeout = 5 ) {
    $api_key = get_option( 'wpaap_pexels_api_key', '' );
    if ( empty( $api_key ) ) {
        wpaap_debug_log( 'WPAAP Pexels: chưa cấu hình API key, bỏ qua tìm ảnh.' );
        return null;
    }

    $url = 'https://api.pexels.com/v1/search?' . http_build_query( [
        'query'       => $query,
        'per_page'    => 5,
        'orientation' => 'landscape',
    ] );

    $response = wp_remote_get( $url, [
        'timeout' => $timeout,
        'headers' => [ 'Authorization' => $api_key ],
    ] );

    if ( is_wp_error( $response ) ) {
        wpaap_debug_log( 'WPAAP Pexels WP_Error: ' . $response->get_error_message() );
        return null;
    }
    if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
        wpaap_debug_log( 'WPAAP Pexels HTTP ' . wp_remote_retrieve_response_code( $response ) );
        return null;
    }

    $data   = json_decode( wp_remote_retrieve_body( $response ), true );
    $photos = $data['photos'] ?? [];
    foreach ( $photos as $photo ) {
        $img_url = $photo['src']['large'] ?? $photo['src']['original'] ?? '';
        if ( $img_url ) {
            wpaap_debug_log( 'WPAAP Pexels found: ' . $img_url );
            return $img_url;
        }
    }

    wpaap_debug_log( 'WPAAP Pexels: không tìm thấy ảnh cho "' . $query . '"' );
    return null;
}

// Tìm ảnh qua Pixabay API (backup — cần API key, cấu hình tại trang Kết Nối AI)
function wpaap_search_google_image( $query, $timeout = 5 ) {
    $api_key = get_option( 'wpaap_pixabay_api_key', '' );
    if ( empty( $api_key ) ) {
        wpaap_debug_log( 'WPAAP Pixabay: chưa cấu hình API key, bỏ qua.' );
        return null;
    }

    $url = 'https://pixabay.com/api/?' . http_build_query( [
        'key'         => $api_key,
        'q'           => $query,
        'image_type'  => 'photo',
        'orientation' => 'horizontal',
        'safesearch'  => 'true',
        'per_page'    => 5,
    ] );

    $response = wp_remote_get( $url, [
        'timeout' => $timeout,
    ] );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        return null;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    $hits = $data['hits'] ?? [];
    foreach ( $hits as $hit ) {
        $img_url = $hit['largeImageURL'] ?? $hit['webformatURL'] ?? '';
        if ( $img_url ) {
            wpaap_debug_log( 'WPAAP Pixabay found: ' . $img_url );
            return $img_url;
        }
    }

    wpaap_debug_log( 'WPAAP Pixabay: không tìm thấy ảnh cho "' . $query . '"' );
    return null;
}

// Download song song nhiều ảnh dùng curl_multi (nhanh hơn tuần tự)
// Trả về array indexed theo $ai_images: giá trị là đường dẫn tmp file hoặc null nếu thất bại
function wpaap_parallel_download_images( array $ai_images ) {
    $count   = count( $ai_images );
    $results = array_fill( 0, $count, null );

    if ( $count === 0 ) {
        return $results;
    }

    // Fallback tuần tự nếu curl_multi không khả dụng
    if ( ! function_exists( 'curl_multi_init' ) ) {
        wpaap_debug_log( 'PAR DL: curl_multi not available, falling back to sequential' );
        foreach ( $ai_images as $i => $image_data ) {
            $url = is_array( $image_data ) ? ( $image_data['ai_url'] ?? '' ) : (string) $image_data;
            if ( empty( $url ) ) continue;
            $tmp = wpaap_download_image_to_tmp( $url, 25 );
            if ( ! is_wp_error( $tmp ) ) {
                $results[ $i ] = $tmp;
            }
        }
        return $results;
    }

    $mh      = curl_multi_init();
    $handles = array();
    $files   = array();

    foreach ( $ai_images as $i => $image_data ) {
        $url = is_array( $image_data ) ? ( $image_data['ai_url'] ?? '' ) : (string) $image_data;
        if ( empty( $url ) ) continue;

        $tmp = wp_tempnam( 'wpaap_par_' );
        $fh  = @fopen( $tmp, 'wb' );
        if ( ! $fh ) {
            @unlink( $tmp );
            continue;
        }

        $ch = curl_init( $url );
        curl_setopt_array( $ch, array(
            CURLOPT_FILE           => $fh,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        ) );

        $handles[ $i ] = $ch;
        $files[ $i ]   = array( 'path' => $tmp, 'fh' => $fh );
        curl_multi_add_handle( $mh, $ch );
    }

    // Chạy song song
    if ( ! empty( $handles ) ) {
        $running = null;
        do {
            $status = curl_multi_exec( $mh, $running );
            if ( $running > 0 ) {
                curl_multi_select( $mh, 0.5 );
            }
        } while ( $running > 0 && $status === CURLM_OK );
    }

    // Thu thập kết quả
    foreach ( $handles as $i => $ch ) {
        $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
        curl_multi_remove_handle( $mh, $ch );
        curl_close( $ch );

        fclose( $files[ $i ]['fh'] );
        $tmp = $files[ $i ]['path'];

        if ( $http_code === 200 && file_exists( $tmp ) && filesize( $tmp ) > 2000 ) {
            $results[ $i ] = $tmp;
            wpaap_debug_log( 'PAR DL[' . $i . '] OK size=' . filesize( $tmp ) . 'B' );
        } else {
            @unlink( $tmp );
            wpaap_debug_log( 'PAR DL[' . $i . '] FAIL http=' . $http_code );
        }
    }

    curl_multi_close( $mh );
    return $results;
}

// Tạo danh sách ảnh: Pollinations AI → Picsum placeholder
function wpaap_generate_ai_images( $image_prompts, $image_keywords = array() ) {
    $images = array();

    if ( empty( $image_prompts ) || ! is_array( $image_prompts ) ) {
        return $images;
    }

    foreach ( $image_prompts as $idx => $prompt ) {
        if ( empty( $prompt ) ) continue;

        $seed = substr( md5( $prompt ), 0, 8 );
        $seed_int = hexdec( $seed ) % 999999;

        // Ưu tiên keyword do AI cung cấp → fallback tự extract
        $ai_keyword   = isset( $image_keywords[ $idx ] ) ? trim( $image_keywords[ $idx ] ) : '';
        $keywords     = ! empty( $ai_keyword ) ? $ai_keyword : wpaap_extract_keywords( $prompt, 4 );
        $search_query = $keywords ?: implode( ' ', array_slice( explode( ' ', $prompt ), 0, 5 ) );

        // Pollinations.ai: AI vẽ ảnh theo prompt (miễn phí, không cần API key)
        $ai_prompt    = $prompt ?: $keywords;
        $pollinations = 'https://image.pollinations.ai/prompt/' . rawurlencode( $ai_prompt )
                      . '?width=1200&height=675&nologo=true&seed=' . $seed_int . '&model=flux-schnell';

        wpaap_debug_log('WPAAP image[' . $idx . '] search_query: "' . $search_query . '"' );

        $images[] = array(
            'search_query'    => $search_query,
            'ai_url'          => $pollinations,
            'last_resort'     => 'https://picsum.photos/seed/' . $seed . '/1200/675',
            'prompt'          => $prompt,
            'keywords'        => $keywords,
        );
    }

    return $images;
}

// Helper: tải file từ URL về /tmp
function wpaap_download_image_to_tmp( $url, $timeout = 30 ) {
    $response = wp_remote_get( $url, array(
        'timeout'    => $timeout,
        'headers'    => array(
            'Accept'          => 'image/webp,image/png,image/jpeg,image/*,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9',
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $http_code = wp_remote_retrieve_response_code( $response );
    if ( $http_code !== 200 ) {
        return new WP_Error( 'http_error', 'HTTP ' . $http_code . ' from ' . esc_url( $url ) );
    }

    $body = wp_remote_retrieve_body( $response );
    if ( strlen( $body ) < 2000 ) {
        return new WP_Error( 'empty_response', 'Body too small (' . strlen( $body ) . ' bytes)' );
    }

    $tmp = wp_tempnam( 'wpaap_img_' );
    if ( ! $tmp ) {
        return new WP_Error( 'tmp_fail', 'Cannot create temp file' );
    }

    if ( file_put_contents( $tmp, $body ) === false ) {
        @unlink( $tmp );
        return new WP_Error( 'write_fail', 'Cannot write to temp file' );
    }

    return $tmp;
}

// Sideload ảnh vào WordPress Media Library
// Priority: Bing search → Google search → Pollinations AI → Picsum placeholder
function wpaap_sideload_image_to_media( $image, $post_id = 0, $description = '' ) {
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    // Đảm bảo thư mục uploads tồn tại và ghi được trước khi sideload
    $upload_dir = wp_upload_dir( null, true );
    if ( ! empty( $upload_dir['path'] ) && ! is_dir( $upload_dir['path'] ) ) {
        wp_mkdir_p( $upload_dir['path'] );
    }

    // Build URL list theo thứ tự ưu tiên
    $urls = array();

    if ( is_array( $image ) ) {
        // 1. Thử Bing Images search trước
        if ( ! empty( $image['search_query'] ) ) {
            $bing_url = wpaap_search_bing_image( $image['search_query'] );
            if ( $bing_url ) {
                $urls[] = array( 'url' => $bing_url, 'source' => 'Bing', 'timeout' => 15 );
            }
            // 2. Thử Google Images nếu Bing thất bại
            if ( ! $bing_url ) {
                $google_url = wpaap_search_google_image( $image['search_query'] );
                if ( $google_url ) {
                    $urls[] = array( 'url' => $google_url, 'source' => 'Google', 'timeout' => 15 );
                }
            }
        }

        // 3. Pollinations AI (vẽ ảnh theo prompt — miễn phí, timeout dài hơn)
        if ( ! empty( $image['ai_url'] ) ) {
            $urls[] = array( 'url' => $image['ai_url'], 'source' => 'Pollinations', 'timeout' => 60 );
        }

        // 4. Picsum placeholder (instant, không liên quan keyword nhưng luôn hoạt động)
        if ( ! empty( $image['last_resort'] ) ) {
            $urls[] = array( 'url' => $image['last_resort'], 'source' => 'Picsum', 'timeout' => 15 );
        }

        if ( ! empty( $image['keywords'] ) ) {
            wpaap_debug_log('WPAAP sideload keywords: ' . $image['keywords'] );
        }
    } else {
        $urls[] = array( 'url' => $image, 'source' => 'Direct', 'timeout' => 30 );
    }

    $last_error = null;

    foreach ( $urls as $entry ) {
        $try_url = $entry['url'];
        $source  = $entry['source'];
        $timeout = $entry['timeout'] ?? 30;

        wpaap_debug_log('WPAAP trying ' . $source . ': ' . $try_url );

        $tmp = wpaap_download_image_to_tmp( $try_url, $timeout );

        if ( is_wp_error( $tmp ) ) {
            $last_error = $tmp;
            wpaap_debug_log('WPAAP ' . $source . ' download FAIL: ' . $tmp->get_error_message() );
            continue;
        }

        $file_size = filesize( $tmp );
        if ( $file_size < 2000 ) {
            @unlink( $tmp );
            $last_error = new WP_Error( 'invalid_image', $source . ' file too small (' . $file_size . 'B)' );
            wpaap_debug_log('WPAAP ' . $source . ' invalid: ' . $file_size . ' bytes' );
            continue;
        }

        // Detect MIME
        $mime_type = '';
        if ( function_exists( 'finfo_open' ) ) {
            $fi        = finfo_open( FILEINFO_MIME_TYPE );
            $mime_type = finfo_file( $fi, $tmp );
            finfo_close( $fi );
        } elseif ( function_exists( 'mime_content_type' ) ) {
            $mime_type = mime_content_type( $tmp );
        }

        $ext_map = array(
            'image/jpeg' => '.jpg', 'image/jpg' => '.jpg',
            'image/png'  => '.png', 'image/webp' => '.webp',
            'image/gif'  => '.gif',
        );
        $ext  = isset( $ext_map[ $mime_type ] ) ? $ext_map[ $mime_type ] : '.jpg';
        $base = sanitize_file_name( sanitize_title( $description ?: ( 'ai-image-' . $post_id ) ) );
        if ( empty( $base ) ) {
            $base = 'ai-image-' . $post_id;
        }

        $file_array = array(
            'name'     => $base . $ext,
            'tmp_name' => $tmp,
        );

        $attachment_id = media_handle_sideload( $file_array, $post_id, $description );

        if ( is_wp_error( $attachment_id ) ) {
            @unlink( $tmp );
            $last_error = $attachment_id;
            wpaap_debug_log('WPAAP ' . $source . ' sideload FAIL: ' . $attachment_id->get_error_message() );
            continue;
        }

        if ( ! empty( $description ) ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', wp_strip_all_tags( $description ) );
        }

        wpaap_debug_log('WPAAP ' . $source . ' sideload OK: attachment_id=' . $attachment_id );
        return $attachment_id;
    }

    wpaap_debug_log('WPAAP ALL sources failed for post_id=' . $post_id );
    return $last_error ?: new WP_Error( 'sideload_failed', 'All image sources failed' );
}
