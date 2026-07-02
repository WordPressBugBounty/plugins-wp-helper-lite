<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpaap_limits_page_layout() {
    $message = '';
    $status = 'info';

    // Xử lý đặt lại (reset) số lượng tokens đã sử dụng
    if ( isset( $_POST['wpaap_reset_tokens'] ) && check_admin_referer( 'wpaap_limit_settings_action', 'wpaap_limit_settings_nonce' ) ) {
        $provider = sanitize_key( $_POST['wpaap_provider_id'] );
        update_option( "wpaap_tokens_used_{$provider}", 0 );
        
        // Xóa logs có mốc thời gian của provider này
        $logs = get_option( 'wpaap_token_usage_logs', [] );
        if ( is_array( $logs ) ) {
            $logs = array_filter( $logs, function( $entry ) use ( $provider ) {
                return ! isset( $entry['provider'] ) || $entry['provider'] !== $provider;
            } );
            update_option( 'wpaap_token_usage_logs', array_values( $logs ) );
        }

        $message = sprintf( __( 'Đã đặt lại toàn bộ thống kê tokens của %s về 0!', 'whp' ), esc_html( ucfirst( $provider ) ) );
        $status = 'success';
    }

    // Đọc bộ lọc từ GET
    $active_period = isset($_GET['period']) ? sanitize_key($_GET['period']) : 'today';
    $filter_from   = isset($_GET['from'])   ? sanitize_text_field($_GET['from'])   : date('Y-m-d', strtotime('-1 month'));
    $filter_to     = isset($_GET['to'])     ? sanitize_text_field($_GET['to'])     : date('Y-m-d');
    $period_map = ['today' => 'today', '7days' => '7_days', '15days' => '15_days'];
    $stat_period = isset($period_map[$active_period]) ? $period_map[$active_period] : 'today';

    $is_connected = get_option( 'wpaap_core_connected', 'no' ) === 'yes';

    $google_key = trim( (string) (get_option( 'connectors_gemini_api_key' ) ? get_option( 'connectors_gemini_api_key' ) : get_option( 'connectors_google_api_key' )) );
    $google_connected = $is_connected && !empty( $google_key ) && ( get_option( 'wpaap_provider_connected_google', 'no' ) === 'yes' );

    $anthropic_key = trim( (string) get_option( 'connectors_anthropic_api_key' ) );
    $anthropic_connected = $is_connected && !empty( $anthropic_key ) && ( get_option( 'wpaap_provider_connected_anthropic', 'no' ) === 'yes' );

    $openai_key = trim( (string) get_option( 'connectors_openai_api_key' ) );
    $openai_connected = $is_connected && !empty( $openai_key ) && ( get_option( 'wpaap_provider_connected_openai', 'no' ) === 'yes' );

    $providers = [
        'google' => [
            'name' => 'Google Gemini',
            'desc' => __( 'Tạo văn bản và hình ảnh thông minh với các mô hình Gemini và Imagen.', 'whp' ),
            'icon' => '<svg viewBox="0 0 28 28" width="28" height="28" style="vertical-align: middle;"><path d="M14 28C14 26.0633 13.6267 24.2433 12.88 22.54C12.1567 20.8367 11.165 19.355 9.905 18.095C8.645 16.835 7.16333 15.8433 5.46 15.12C3.75667 14.3733 1.93667 14 0 14C1.93667 14 3.75667 13.6383 5.46 12.915C7.16333 12.1683 8.645 11.165 9.905 9.905C11.165 8.645 12.1567 7.16333 12.88 5.46C13.6267 3.75667 14 1.93667 14 0C14 1.93667 14.3617 3.75667 15.085 5.46C15.8317 7.16333 16.835 8.645 18.095 9.905C19.355 11.165 20.8367 12.1683 22.54 12.915C24.2433 13.6383 26.0633 14 28 14C26.0633 14 24.2433 14.3733 22.54 15.12C20.8367 15.8433 19.355 16.835 18.095 18.095C16.835 19.355 15.8317 20.8367 15.085 22.54C14.3617 24.2433 14 26.0633 14 28Z" fill="url(#geminiGradientRadialLimits)"/><defs><radialGradient id="geminiGradientRadialLimits" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(2.77876 11.3795) rotate(18.6832) scale(29.8025 238.737)"><stop offset="0.0671246" stop-color="#9168C0"/><stop offset="0.342551" stop-color="#5684D1"/><stop offset="0.672076" stop-color="#1BA1E3"/></radialGradient></defs></svg>'
        ],
        'anthropic' => [
            'name' => 'Anthropic Claude',
            'desc' => __( 'Trí tuệ nhân tạo chuyên sâu với các mô hình Claude 3/3.5.', 'whp' ),
            'icon' => '<svg viewBox="0 0 24 24" width="28" height="28" style="vertical-align: middle; fill: #D97757;"><path d="m4.7144 15.9555 4.7174-2.6471.079-.2307-.079-.1275h-.2307l-.7893-.0486-2.6956-.0729-2.3375-.0971-2.2646-.1214-.5707-.1215-.5343-.7042.0546-.3522.4797-.3218.686.0608 1.5179.1032 2.2767.1578 1.6514.0972 2.4468.255h.3886l.0546-.1579-.1336-.0971-.1032-.0972L6.973 9.8356l-2.55-1.6879-1.3356-.9714-.7225-.4918-.3643-.4614-.1578-1.0078.6557-.7225.8803.0607.2246.0607.8925.686 1.9064 1.4754 2.4893 1.8336.3643.3035.1457-.1032.0182-.0728-.164-.2733-1.3539-2.4467-1.445-2.4893-.6435-1.032-.17-.6194c-.0607-.255-.1032-.4674-.1032-.7285L6.287.1335 6.6997 0l.9957.1336.419.3642.6192 1.4147 1.0018 2.2282 1.5543 3.0296.4553.8985.2429.8318.091.255h.1579v-.1457l.1275-1.706.2368-2.0947.2307-2.6957.0789-.7589.3764-.9107.7468-.4918.5828.2793.4797.686-.0668.4433-.2853 1.8517-.5586 2.9021-.3643 1.9429h.2125l.2429-.2429.9835-1.3053 1.6514-2.0643.7286-.8196.85-.9046.5464-.4311h1.0321l.759 1.1293-.34 1.1657-1.0625 1.3478-.8804 1.1414-1.2628 1.7-.7893 1.36.0729.1093.1882-.0183 2.8535-.607 1.5421-.2794 1.8396-.3157.8318.3886.091.3946-.3278.8075-1.967.4857-2.3072.4614-3.4364.8136-.0425.0304.0486.0607 1.5482.1457.6618.0364h1.621l3.0175.2247.7892.522.4736.6376-.079.4857-1.2142.6193-1.6393-.3886-3.825-.9107-1.3113-.3279h-.1822v.1093l1.0929 1.0686 2.0035 1.8092 2.5075 2.3314.1275.5768-.3218.4554-.34-.0486-2.2039-1.6575-.85-.7468-1.9246-1.621h-.1275v.17l.4432.6496 2.3436 3.5214.1214 1.0807-.17.3521-.6071.2125-.6679-.1214-1.3721-1.9246L14.38 17.959l-1.1414-1.9428-.1397.079-.674 7.2552-.3156.3703-.7286.2793-.6071-.4614-.3218-.7468.3218-1.4753.3886-1.9246.3157-1.53.2853-1.9004.17-.6314-.0121-.0425-.1397.0182-1.4328 1.9672-2.1796 2.9446-1.7243 1.8456-.4128.164-.7164-.3704.0667-.6618.4008-.5889 2.386-3.0357 1.4389-1.882.929-1.0868-.0062-.1579h-.0546l-6.3385 4.1164-1.1293.1457-.4857-.4554.0608-.7467.2307-.2429 1.9064-1.3114Z"/></svg>'
        ],
        'openai' => [
            'name' => 'OpenAI GPT',
            'desc' => __( 'Dịch vụ AI phổ biến nhất thế giới với mô hình GPT-4o.', 'whp' ),
            'icon' => '<svg viewBox="0 0 24 24" width="28" height="28" style="vertical-align: middle; fill: #000000;"><path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z"/></svg>'
        ]
    ];

    // Helper: nhóm dữ liệu từ wpaap_token_usage_logs thành các khoảng dữ liệu cụ thể
    $wpaap_get_sparkline_bins = function( $provider, $period ) {
        $logs = get_option( 'wpaap_token_usage_logs', [] );
        if ( ! is_array( $logs ) ) {
            $logs = [];
        }

        $now = current_time( 'timestamp' );
        $today_start = strtotime( 'today', $now );
        $yesterday_start = $today_start - DAY_IN_SECONDS;
        
        $bins = [];
        
        if ( $period === 'today' ) {
            for ( $i = 0; $i < 6; $i++ ) {
                $bins[$i] = 0;
            }
            foreach ( $logs as $entry ) {
                if ( ! isset( $entry['provider'] ) || $entry['provider'] !== $provider ) {
                    continue;
                }
                $ts = intval( $entry['timestamp'] );
                if ( $ts >= $today_start && $ts < $today_start + DAY_IN_SECONDS ) {
                    $hour = intval( date( 'G', $ts ) );
                    $bin_index = min( 5, floor( $hour / 4 ) );
                    $bins[$bin_index] += intval( $entry['tokens'] );
                }
            }
        } elseif ( $period === 'yesterday' ) {
            for ( $i = 0; $i < 6; $i++ ) {
                $bins[$i] = 0;
            }
            foreach ( $logs as $entry ) {
                if ( ! isset( $entry['provider'] ) || $entry['provider'] !== $provider ) {
                    continue;
                }
                $ts = intval( $entry['timestamp'] );
                if ( $ts >= $yesterday_start && $ts < $today_start ) {
                    $hour = intval( date( 'G', $ts ) );
                    $bin_index = min( 5, floor( $hour / 4 ) );
                    $bins[$bin_index] += intval( $entry['tokens'] );
                }
            }
        } elseif ( $period === '7_days' ) {
            for ( $i = 6; $i >= 0; $i-- ) {
                $day_ts = $today_start - $i * DAY_IN_SECONDS;
                $bins[date('Y-m-d', $day_ts)] = 0;
            }
            foreach ( $logs as $entry ) {
                if ( ! isset( $entry['provider'] ) || $entry['provider'] !== $provider ) {
                    continue;
                }
                $ts = intval( $entry['timestamp'] );
                $d = date('Y-m-d', $ts);
                if ( isset( $bins[$d] ) ) {
                    $bins[$d] += intval( $entry['tokens'] );
                }
            }
            $bins = array_values($bins);
        } elseif ( $period === '15_days' ) {
            for ( $i = 0; $i < 6; $i++ ) {
                $bins[$i] = 0;
            }
            $fifteen_days_ago = $now - 15 * DAY_IN_SECONDS;
            foreach ( $logs as $entry ) {
                if ( ! isset( $entry['provider'] ) || $entry['provider'] !== $provider ) {
                    continue;
                }
                $ts = intval( $entry['timestamp'] );
                if ( $ts >= $fifteen_days_ago ) {
                    $days_ago = floor( ($now - $ts) / DAY_IN_SECONDS );
                    $bin_index = min( 5, floor( $days_ago / 5 ) );
                    $bins[5 - $bin_index] += intval( $entry['tokens'] );
                }
            }
        }
        
        return $bins;
    };

    // Helper: Vẽ đường lượn sóng sparkline SVG
    $wpaap_render_sparkline = function( $bins, $active ) {
        $width = 110;
        $height = 25;
        $padding = 2;
        
        $max_val = max( $bins );
        if ( $max_val <= 0 ) {
            $stroke_color = $active ? '#93c5fd' : '#cbd5e1';
            return '<svg viewBox="0 0 ' . $width . ' ' . $height . '" width="' . $width . '" height="' . $height . '" style="overflow:visible;display:block;margin:6px auto 0;">' .
                   '<path d="M 0 ' . ($height - 4) . ' C 25 ' . ($height - 8) . ', 75 ' . ($height - 2) . ', 110 ' . ($height - 5) . '" fill="none" stroke="' . $stroke_color . '" stroke-width="1.5" />' .
                   '</svg>';
        }
        
        $points = [];
        $count = count( $bins );
        for ( $i = 0; $i < $count; $i++ ) {
            $x = ($i / ($count - 1)) * $width;
            $y = ($height - $padding * 2) - (($bins[$i] / $max_val) * ($height - $padding * 3)) + $padding;
            $points[] = [$x, $y];
        }
        
        $path = 'M ' . $points[0][0] . ' ' . $points[0][1];
        for ( $i = 0; $i < $count - 1; $i++ ) {
            $x1 = $points[$i][0];
            $y1 = $points[$i][1];
            $x2 = $points[$i+1][0];
            $y2 = $points[$i+1][1];
            $cx1 = $x1 + ($x2 - $x1) / 3;
            $cy1 = $y1;
            $cx2 = $x1 + 2 * ($x2 - $x1) / 3;
            $cy2 = $y2;
            $path .= " C $cx1 $cy1, $cx2 $cy2, $x2 $y2";
        }
        
        $fill_path = $path . " L {$width} {$height} L 0 {$height} Z";
        $stroke_color = $active ? '#3b82f6' : '#94a3b8';
        $grad_id = 'sparkline_grad_' . uniqid();
        
        $svg = '<svg viewBox="0 0 ' . $width . ' ' . $height . '" width="' . $width . '" height="' . $height . '" style="overflow:visible;display:block;margin:6px auto 0;">';
        $svg .= '<defs>';
        $svg .= '<linearGradient id="' . $grad_id . '" x1="0" y1="0" x2="0" y2="1">';
        if ( $active ) {
            $svg .= '<stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>';
            $svg .= '<stop offset="100%" stop-color="#3b82f6" stop-opacity="0.0"/>';
        } else {
            $svg .= '<stop offset="0%" stop-color="#94a3b8" stop-opacity="0.15"/>';
            $svg .= '<stop offset="100%" stop-color="#94a3b8" stop-opacity="0.0"/>';
        }
        $svg .= '</linearGradient>';
        $svg .= '</defs>';
        $svg .= '<path d="' . $fill_path . '" fill="url(#' . $grad_id . ')" />';
        $svg .= '<path d="' . $path . '" fill="none" stroke="' . $stroke_color . '" stroke-width="2" stroke-linecap="round" />';
        $last_point = end( $points );
        $svg .= '<circle cx="' . $last_point[0] . '" cy="' . $last_point[1] . '" r="2.5" fill="' . $stroke_color . '" />';
        $svg .= '</svg>';
        
        return $svg;
    };

    // Helper: Vẽ biểu đồ cột bar chart SVG
    $wpaap_render_bar_chart = function( $bins, $active ) {
        $width = 110;
        $height = 25;
        $bar_width = 8;
        $bar_gap = 5;
        $count = count( $bins );
        
        $max_val = max( $bins );
        $grad_id = 'bar_grad_' . uniqid();
        
        $svg = '<svg viewBox="0 0 ' . $width . ' ' . $height . '" width="' . $width . '" height="' . $height . '" style="overflow:visible;display:block;margin:6px auto 0;">';
        $svg .= '<defs>';
        $svg .= '<linearGradient id="' . $grad_id . '" x1="0" y1="0" x2="0" y2="1">';
        if ( $active ) {
            $svg .= '<stop offset="0%" stop-color="#60a5fa" stop-opacity="1"/>';
            $svg .= '<stop offset="100%" stop-color="#3b82f6" stop-opacity="0.5"/>';
        } else {
            $svg .= '<stop offset="0%" stop-color="#cbd5e1" stop-opacity="1"/>';
            $svg .= '<stop offset="100%" stop-color="#94a3b8" stop-opacity="0.5"/>';
        }
        $svg .= '</linearGradient>';
        $svg .= '</defs>';
        
        $total_bar_w = ($count * $bar_width) + (($count - 1) * $bar_gap);
        $start_x = ($width - $total_bar_w) / 2;
        
        for ( $i = 0; $i < $count; $i++ ) {
            $x = $start_x + $i * ($bar_width + $bar_gap);
            if ( $max_val <= 0 ) {
                $h = 3;
            } else {
                $h = min( $height, max( 3, ($bins[$i] / $max_val) * $height ) );
            }
            $y = $height - $h;
            $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $bar_width . '" height="' . $h . '" rx="2" fill="url(#' . $grad_id . ')" />';
        }
        
        $svg .= '</svg>';
        return $svg;
    };
    ?>
    
    <style>
    .wpaap-limits-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 0;
        font-family: inherit;
    }
    
    /* Header card styling */
    .wpaap-limits-header {
        position: relative;
        background: linear-gradient(100deg, #ffffff 0%, #ecfeff 45%, #cffafe 100%);
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(8,145,178,0.1), 0 0 0 1px #a5f3fc;
        margin-bottom: 25px;
        overflow: hidden;
        min-height: 168px;
        display: flex;
        align-items: stretch;
    }
    .wpaap-limits-header-left {
        position: relative; z-index: 2;
        padding: 32px 36px;
        display: flex; flex-direction: column; justify-content: center; gap: 14px;
        max-width: 500px; flex-shrink: 0;
    }
    .wpaap-limits-header-title-row { display: flex; align-items: center; gap: 14px; }
    .wpaap-limits-header-icon-box {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #0891b2, #22d3ee);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; box-shadow: 0 4px 12px rgba(8,145,178,0.3);
    }
    .wpaap-limits-header-right {
        position: absolute; inset: 0 0 0 38%;
        overflow: hidden; pointer-events: none;
    }
    
    /* Filter bar */
    .wpaap-limits-filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }
    .wpaap-limits-filter-btns {
        display: flex;
        gap: 4px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .wpaap-limits-filter-btn {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
    }
    .wpaap-limits-filter-btn:hover {
        color: #475569;
    }
    .wpaap-limits-filter-btn.active {
        background: linear-gradient(135deg, #0891b2, #22d3ee);
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(8,145,178,0.25);
    }
    .wpaap-limits-date-range {
        display: none;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 6px 14px;
        font-size: 13px;
        color: #475569;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .wpaap-limits-date-range.show {
        display: flex;
    }
    .wpaap-limits-date-range input[type="date"] {
        border: none;
        outline: none;
        font-size: 13px;
        color: #0f172a;
        background: transparent;
        cursor: pointer;
        font-family: inherit;
        font-weight: 500;
    }
    .wpaap-limits-filter-btn-apply {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 20px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 700;
        background: linear-gradient(135deg, #0891b2, #22d3ee);
        color: #ffffff;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(8,145,178,0.2);
        transition: all 0.15s ease;
    }
    .wpaap-limits-filter-btn-apply:hover {
        background: linear-gradient(135deg, #0770a0, #14b8d5);
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(8,145,178,0.35);
    }

    /* Active filter info badge */
    .wpaap-limits-active-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #ecfeff;
        border: 1px solid #a5f3fc;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 13.5px;
        color: #0e7490;
    }
    .wpaap-limits-active-filter strong {
        color: #0891b2;
        font-weight: 700;
    }
    .wpaap-limits-active-filter span.label {
        color: #64748b;
    }

    /* 2-column layout */
    .wpaap-limits-body {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 960px) {
        .wpaap-limits-body {
            grid-template-columns: 1fr;
        }
        .wpaap-limits-sidebar {
            display: none;
        }
    }
    .wpaap-limits-main {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Provider Cards */
    .wpaap-limits-provider-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        border-left: 5px solid transparent;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .wpaap-limits-provider-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    }
    .wpaap-limits-provider-card.accent-google    { border-left-color: #5684D1; }
    .wpaap-limits-provider-card.accent-anthropic { border-left-color: #D97757; }
    .wpaap-limits-provider-card.accent-openai    { border-left-color: #374151; }
    .wpaap-limits-provider-card.not-connected    { opacity: 0.5; pointer-events: none; }
    .wpaap-limits-provider-card.not-connected:hover { transform: none; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
    .wpaap-limits-provider-card.not-connected .wpaap-limits-btn-connect { pointer-events: auto; }

    .wpaap-limits-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        gap: 16px;
    }
    @media (max-width: 768px) {
        .wpaap-limits-card-header {
            flex-wrap: wrap;
        }
    }
    .wpaap-limits-card-left {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1 1 auto;
        min-width: 0;
    }
    .wpaap-limits-provider-logo {
        width: 52px;
        height: 52px;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }
    .wpaap-limits-provider-logo:hover {
        transform: scale(1.05);
    }
    .wpaap-limits-provider-logo svg {
        display: block;
    }
    .wpaap-limits-provider-name {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 6px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .wpaap-limits-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        white-space: nowrap;
    }
    .wpaap-limits-status-badge.connected {
        background: #dcfce7;
        color: #15803d;
    }
    .wpaap-limits-status-badge.disconnected {
        background: #f1f5f9;
        color: #475569;
    }
    .wpaap-limits-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }
    .wpaap-limits-provider-desc {
        font-size: 13.5px;
        color: #64748b;
        margin: 0;
        line-height: 1.45;
    }
    .wpaap-limits-card-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .wpaap-limits-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none !important;
        border: 1.5px solid transparent;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    
    /* Button styles */
    .wpaap-limits-btn-details {
        background: #64748b;
        color: #ffffff !important;
    }
    .wpaap-limits-btn-details:hover, .wpaap-limits-btn-details.active {
        background: #475569;
    }
    .wpaap-limits-btn-disconnect-toggle {
        background: #ffffff;
        border-color: #e2e8f0;
        color: #475569 !important;
    }
    .wpaap-limits-btn-disconnect-toggle:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a !important;
    }
    .wpaap-limits-btn-connect {
        background: #10b981;
        color: #ffffff !important;
    }
    .wpaap-limits-btn-connect:hover {
        background: #059669;
    }
    .wpaap-limits-btn-outline {
        background: #fee2e2;
        border-color: #fecaca;
        color: #ef4444 !important;
    }
    .wpaap-limits-btn-outline:hover {
        background: #fca5a5;
        color: #b91c1c !important;
    }

    /* Stats Grid */
    .wpaap-limits-stats-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        background: #fcfcfd;
    }
    .wpaap-limits-stat.highlighted .wpaap-limits-stat-date {
        color: #0891b2;
    }
    
    .wpaap-limits-stat {
        padding: 20px 24px;
        text-align: center;
        border-right: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease;
    }
    .wpaap-limits-stat:last-child {
        border-right: none;
    }
    .wpaap-limits-stat-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }
    .wpaap-limits-stat-value {
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }
    .wpaap-limits-stat-unit {
        font-size: 11.5px;
        color: #94a3b8;
        margin-top: 2px;
        margin-bottom: 8px;
    }
    .wpaap-limits-trend-up {
        color: #22c55e;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        margin-top: 4px;
    }
    .wpaap-limits-trend-down {
        color: #ef4444;
        font-size: 11.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        margin-top: 4px;
    }
    
    /* Highlight states */
    .wpaap-limits-stat.highlighted {
        background: #ecfeff;
    }
    .wpaap-limits-stat.highlighted .wpaap-limits-stat-value {
        color: #0891b2;
    }
    .wpaap-limits-stat.highlighted .wpaap-limits-stat-label {
        color: #0891b2;
    }
    .has-filter .wpaap-limits-stat:not(.highlighted) {
        opacity: 0.6;
    }

    /* Detail Panel */
    .wpaap-limits-detail-panel {
        display: none;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }
    .wpaap-limits-detail-panel.open {
        display: block;
    }
    .wpaap-limits-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
        background: #fafafa;
    }
    .wpaap-limits-detail-header h4 {
        margin: 0;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .wpaap-limits-detail-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }
    .wpaap-limits-detail-table th {
        padding: 10px 24px;
        text-align: left;
        font-size: 11.5px;
        font-weight: 700;
        color: #64748b;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbfd;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .wpaap-limits-detail-table td {
        padding: 12px 24px;
        border-bottom: 1px solid #f8fafc;
        color: #334155;
    }
    .wpaap-limits-detail-table tr:last-child td {
        border-bottom: none;
    }
    .wpaap-limits-detail-table tr:hover td {
        background: #f8fafc;
    }
    .wpaap-limits-detail-empty {
        padding: 30px 24px;
        text-align: center;
        color: #94a3b8;
        font-size: 13.5px;
    }

    /* Summary Card bottom */
    .wpaap-limits-summary-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,.03);
        display: grid;
        grid-template-columns: 280px 220px 1fr;
        overflow: hidden;
        margin-top: 24px;
        align-items: stretch;
    }
    @media (max-width: 900px) {
        .wpaap-limits-summary-card {
            grid-template-columns: 1fr;
        }
        .wpaap-limits-summary-left {
            border-right: none !important;
            border-bottom: 1px solid #e2e8f0;
        }
        .wpaap-limits-summary-center {
            border-right: none !important;
            border-bottom: 1px solid #e2e8f0;
            padding: 30px !important;
        }
    }
    .wpaap-limits-summary-left {
        padding: 24px;
        background: #f8fafc;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 8px;
    }
    .wpaap-limits-summary-title {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .wpaap-limits-summary-desc {
        font-size: 12.5px;
        color: #64748b;
        margin: 0;
        line-height: 1.45;
    }
    .wpaap-limits-summary-center {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
        border-right: 1px solid #e2e8f0;
        background: #ffffff;
    }
    .wpaap-donut-center {
        position: absolute;
        width: 90px;
        height: 90px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 2px 12px rgba(15,23,42,0.08);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        pointer-events: none;
        transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.2s ease;
    }
    .wpaap-limits-summary-center:hover .wpaap-donut-center {
        transform: scale(1.05);
        box-shadow: 0 6px 18px rgba(236,28,36,0.15);
    }
    .wpaap-donut-center-value {
        font-size: 17px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }
    .wpaap-donut-center-label {
        font-size: 9px;
        color: #0891b2;
        font-weight: 700;
        text-transform: uppercase;
        margin-top: 3px;
        letter-spacing: 1px;
    }
    .wpaap-donut-segment {
        transition: stroke-width 0.2s ease, filter 0.2s ease;
        animation: wpaapDonutAppear 1.2s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
    @keyframes wpaapDonutAppear {
        from {
            stroke-dashoffset: 238.76;
        }
    }
    .wpaap-limits-summary-right {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 24px 32px;
        background: #ffffff;
    }

    /* Sidebar cards */
    .wpaap-limits-sidebar {
        position: sticky;
        top: 32px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .wpaap-limits-sidebar-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        padding: 20px;
    }
    .wpaap-limits-sidebar-card h4 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14.5px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 16px 0;
    }
    .wpaap-limits-sidebar-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .wpaap-limits-steps {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }
    .wpaap-limits-step {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .wpaap-limits-step-num {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #ecfeff;
        color: #0891b2;
        font-size: 12px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .wpaap-limits-step-text strong {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 2px;
    }
    .wpaap-limits-step-text span {
        font-size: 12.5px;
        color: #64748b;
        line-height: 1.45;
    }
    .wpaap-limits-tips-list {
        margin: 0;
        padding-left: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        list-style-type: disc;
    }
    .wpaap-limits-tips-list li {
        font-size: 13px;
        color: #475569;
        line-height: 1.45;
    }
    
    /* Notices */
    .wpaap-notice {
        padding: 12px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 13.5px;
        font-weight: 600;
    }
    .wpaap-notice-success {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .wpaap-notice-info {
        background: #ecfeff;
        color: #0e7490;
        border: 1px solid #a5f3fc;
    }
    </style>

    <div class="wrap wpaap-wrap" style="margin: 20px auto 40px; padding: 0 20px;">
        <!-- Header -->
        <div class="wpaap-limits-wrap">
        <div class="wpaap-limits-header">
            <div class="wpaap-limits-header-left">
                <div class="wpaap-limits-header-title-row">
                    <div class="wpaap-limits-header-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e( 'Thống Kê Tokens AI', 'whp' ); ?></h1>
                </div>
                <p style="margin:0;font-size:13.5px;color:#64748b;line-height:1.6;padding-left:58px;max-width:400px;"><?php esc_html_e( 'Xem thống kê chi tiết số lượng tokens đã tiêu thụ của từng nhà cung cấp dịch vụ AI.', 'whp' ); ?></p>
            </div>
            <div class="wpaap-limits-header-right">
                <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                    <defs>
                        <linearGradient id="limits_grad1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#0891b2" stop-opacity="0.18"/><stop offset="100%" stop-color="#22d3ee" stop-opacity="0.08"/></linearGradient>
                        <linearGradient id="limits_grad2" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#0891b2" stop-opacity="0.4"/><stop offset="100%" stop-color="#22d3ee" stop-opacity="0.15"/></linearGradient>
                        <linearGradient id="limits_bar1" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#0891b2" stop-opacity="0.7"/><stop offset="100%" stop-color="#0891b2" stop-opacity="0.3"/></linearGradient>
                        <linearGradient id="limits_bar2" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#22d3ee" stop-opacity="0.7"/><stop offset="100%" stop-color="#22d3ee" stop-opacity="0.3"/></linearGradient>
                    </defs>
                    <!-- Bar chart -->
                    <rect x="310" y="30" width="160" height="108" rx="10" fill="url(#limits_grad1)" stroke="#a5f3fc" stroke-width="1.5"/>
                    <!-- Bars -->
                    <rect x="325" y="90" width="18" height="40" rx="3" fill="url(#limits_bar1)"/>
                    <rect x="349" y="72" width="18" height="58" rx="3" fill="url(#limits_bar2)"/>
                    <rect x="373" y="60" width="18" height="70" rx="3" fill="url(#limits_bar1)"/>
                    <rect x="397" y="78" width="18" height="52" rx="3" fill="url(#limits_bar2)"/>
                    <rect x="421" y="50" width="18" height="80" rx="3" fill="url(#limits_bar1)"/>
                    <!-- Chart baseline -->
                    <line x1="320" y1="132" x2="445" y2="132" stroke="#a5f3fc" stroke-width="1.5"/>
                    <!-- Provider icons: A G O circles -->
                    <circle cx="510" cy="52" r="22" fill="url(#limits_grad2)" stroke="#a5f3fc" stroke-width="1.5"/>
                    <text x="510" y="59" text-anchor="middle" font-size="17" font-weight="700" fill="#D97757" fill-opacity="0.8">A</text>
                    <circle cx="510" cy="104" r="22" fill="url(#limits_grad2)" stroke="#a5f3fc" stroke-width="1.5"/>
                    <text x="510" y="111" text-anchor="middle" font-size="17" font-weight="700" fill="#5684D1" fill-opacity="0.8">G</text>
                    <!-- Token counter display -->
                    <rect x="555" y="38" width="88" height="36" rx="8" fill="url(#limits_grad2)" stroke="#a5f3fc" stroke-width="1.5"/>
                    <text x="565" y="50" font-size="8" fill="#0891b2" fill-opacity="0.7" font-weight="600">TOKENS</text>
                    <text x="565" y="64" font-size="11" fill="#0f172a" fill-opacity="0.7" font-weight="700">12,450</text>
                    <!-- Coin/token symbol -->
                    <circle cx="575" cy="104" r="18" fill="url(#limits_grad1)" stroke="#a5f3fc" stroke-width="1.5"/>
                    <text x="575" y="110" text-anchor="middle" font-size="14" font-weight="700" fill="#0891b2" fill-opacity="0.6">T</text>
                    <!-- Decorative dots -->
                    <circle cx="620" cy="50" r="5" fill="#22d3ee" fill-opacity="0.3"/>
                    <circle cx="640" cy="130" r="7" fill="#0891b2" fill-opacity="0.2"/>
                    <circle cx="480" cy="148" r="4" fill="#22d3ee" fill-opacity="0.25"/>
                </svg>
            </div>
        </div>
        </div>

        <?php if ( ! empty( $message ) ) : ?>
            <div class="wpaap-limits-wrap">
                <div class="wpaap-notice wpaap-notice-<?php echo esc_attr($status); ?>">
                    <?php echo wp_kses_post($message); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="wpaap-limits-wrap">
            <!-- Filter bar -->
            <form method="get" action="" id="wpaap-filter-form">
                <input type="hidden" name="page" value="mb-wphelper-ai">
                <input type="hidden" name="subtab" value="limits">
                <input type="hidden" name="period" id="wpaap-period-input" value="<?php echo esc_attr($active_period); ?>">
                <div class="wpaap-limits-filter-bar">
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <span style="font-size:13.5px;font-weight:700;color:#334155;display:flex;align-items:center;gap:6px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php esc_html_e( 'Bộ lọc thời gian', 'whp' ); ?>
                        </span>
                        <div class="wpaap-limits-filter-btns">
                            <button type="button" class="wpaap-limits-filter-btn <?php echo $active_period==='today'  ?'active':''; ?>" data-period="today"><?php esc_html_e( 'Hôm nay', 'whp' ); ?></button>
                            <button type="button" class="wpaap-limits-filter-btn <?php echo $active_period==='7days'  ?'active':''; ?>" data-period="7days"><?php esc_html_e( '7 ngày qua', 'whp' ); ?></button>
                            <button type="button" class="wpaap-limits-filter-btn <?php echo $active_period==='15days' ?'active':''; ?>" data-period="15days"><?php esc_html_e( '15 ngày qua', 'whp' ); ?></button>
                            <button type="button" class="wpaap-limits-filter-btn <?php echo $active_period==='custom' ?'active':''; ?>" data-period="custom"><?php esc_html_e( 'Tùy chọn', 'whp' ); ?></button>
                        </div>
                        <div class="wpaap-limits-date-range <?php echo $active_period==='custom'?'show':''; ?>" id="wpaap-date-range">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <input type="date" name="from" id="wpaap-date-from" value="<?php echo esc_attr($filter_from); ?>">
                            <span style="color:#94a3b8;">→</span>
                            <input type="date" name="to" id="wpaap-date-to" value="<?php echo esc_attr($filter_to); ?>">
                        </div>
                    </div>
                    <button type="submit" class="wpaap-limits-filter-btn-apply">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="22" y1="3" x2="2" y2="3"/><line x1="18" y1="9" x2="6" y2="9"/><line x1="14" y1="15" x2="10" y2="15"/></svg>
                        <?php esc_html_e( 'Lọc dữ liệu', 'whp' ); ?>
                    </button>
                </div>
            </form>

            <div class="wpaap-limits-body">
                <div class="wpaap-limits-main">

                <?php
                $period_labels = [
                    'today'   => __( 'Hôm nay', 'whp' ),
                    '7days'   => __( '7 ngày qua', 'whp' ),
                    '15days'  => __( '15 ngày qua', 'whp' ),
                    'custom'  => __( 'Tùy chọn', 'whp' ) . ' (' . date('d/m/Y', strtotime($filter_from)) . ' → ' . date('d/m/Y', strtotime($filter_to)) . ')',
                ];
                $period_label = $period_labels[$active_period] ?? __( 'Hôm nay', 'whp' );
                ?>
                <div class="wpaap-limits-active-filter">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="22" y1="3" x2="2" y2="3"/><line x1="18" y1="9" x2="6" y2="9"/><line x1="14" y1="15" x2="10" y2="15"/></svg>
                    <span style="margin-left: 5px;"><?php esc_html_e( 'Đang xem dữ liệu:', 'whp' ); ?></span>
                    <strong style="margin-left: 4px;"><?php echo esc_html($period_label); ?></strong>
                    <span class="label" style="margin-left: 8px;"><?php esc_html_e( '— Cột được tô sáng là khoảng thời gian tương ứng với bộ lọc đã chọn', 'whp' ); ?></span>
                </div>

                <?php
                $total_today    = 0;
                $total_7_days   = 0;
                $total_15_days  = 0;
                $grand_total    = 0;
                $provider_total_tokens = [];

                foreach ( $providers as $pid => $pinfo ) :
                    $p_connected = false;
                    if ( $pid === 'google' ) {
                        $p_connected = $google_connected;
                    } elseif ( $pid === 'anthropic' ) {
                        $p_connected = $anthropic_connected;
                    } elseif ( $pid === 'openai' ) {
                        $p_connected = $openai_connected;
                    }

                    // Lấy thống kê tokens
                    $used_today     = $p_connected && function_exists( 'wpaap_get_token_usage_stats' ) ? wpaap_get_token_usage_stats( $pid, 'today' )     : 0;
                    $used_yesterday = $p_connected && function_exists( 'wpaap_get_token_usage_stats' ) ? wpaap_get_token_usage_stats( $pid, 'yesterday' ) : 0;
                    $used_7_days    = $p_connected && function_exists( 'wpaap_get_token_usage_stats' ) ? wpaap_get_token_usage_stats( $pid, '7_days' )    : 0;
                    $used_15_days   = $p_connected && function_exists( 'wpaap_get_token_usage_stats' ) ? wpaap_get_token_usage_stats( $pid, '15_days' )   : 0;
                    $used_total     = (int) get_option( "wpaap_tokens_used_{$pid}", 0 );

                    // Tính trend hôm nay so hôm qua
                    $trend_today = $used_yesterday > 0 ? round( ( $used_today - $used_yesterday ) / $used_yesterday * 100, 1 ) : null;

                    // Tính tokens cho khoảng tùy chọn từ logs
                    $used_custom = 0;
                    if ( $p_connected ) {
                        $all_logs = get_option('wpaap_token_usage_logs', []);
                        $from_ts = strtotime($filter_from);
                        $to_ts   = strtotime($filter_to . ' 23:59:59');
                        if (is_array($all_logs)) {
                            foreach ($all_logs as $entry) {
                                if (!isset($entry['provider']) || $entry['provider'] !== $pid) continue;
                                $ts = isset($entry['timestamp']) ? (int)$entry['timestamp'] : (isset($entry['date']) ? strtotime($entry['date']) : 0);
                                if ($ts >= $from_ts && $ts <= $to_ts) {
                                    $used_custom += (int)($entry['tokens'] ?? 0);
                                }
                            }
                        }
                    }

                    // Cộng vào tổng
                    $total_today   += $used_today;
                    $total_7_days  += $used_7_days;
                    $total_15_days += $used_15_days;
                    $grand_total   += $used_total;
                    $provider_total_tokens[$pid] = $used_total;
                ?>
                <!-- Provider Card: <?php echo esc_html( $pinfo['name'] ); ?> -->
                <div class="wpaap-limits-provider-card accent-<?php echo esc_attr($pid); ?><?php echo $p_connected ? '' : ' not-connected'; ?>">
                    <div class="wpaap-limits-card-header">
                        <div class="wpaap-limits-card-left">
                            <div class="wpaap-limits-provider-logo">
                                <?php echo $pinfo['icon']; // trusted static SVG ?>
                            </div>
                            <div style="min-width:0;">
                                <p class="wpaap-limits-provider-name">
                                    <?php echo esc_html( $pinfo['name'] ); ?>
                                    <?php if ( $p_connected ) : ?>
                                        <span class="wpaap-limits-status-badge connected">
                                            <span class="wpaap-limits-status-dot" style="background:#16a34a;"></span>
                                            <?php esc_html_e( 'Đang hoạt động', 'whp' ); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="wpaap-limits-status-badge disconnected">
                                            <span class="wpaap-limits-status-dot" style="background:#94a3b8;"></span>
                                            <?php esc_html_e( 'Chưa kết nối', 'whp' ); ?>
                                        </span>
                                    <?php endif; ?>
                                </p>
                                <p class="wpaap-limits-provider-desc"><?php echo esc_html( $pinfo['desc'] ); ?></p>
                            </div>
                        </div>
                        <div class="wpaap-limits-card-actions">
                            <?php if ( $p_connected ) : ?>
                                <button type="button" class="wpaap-limits-btn wpaap-limits-btn-details wpaap-detail-toggle" data-provider="<?php echo esc_attr($pid); ?>">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <?php esc_html_e( 'Chi tiết', 'whp' ); ?>
                                </button>
                                <a href="<?php echo esc_url( admin_url('admin.php?page=mb-wphelper-ai&subtab=connection') ); ?>" class="wpaap-limits-btn wpaap-limits-btn-disconnect-toggle">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                    <?php esc_html_e( 'Ngắt kết nối', 'whp' ); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php echo esc_url( admin_url('admin.php?page=mb-wphelper-ai&subtab=connection') ); ?>" class="wpaap-limits-btn wpaap-limits-btn-connect">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    <?php esc_html_e( 'Kết nối', 'whp' ); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    $hl = ['today'=>'','yesterday'=>'','7days'=>'','15days'=>'','custom'=>''];
                    if ($active_period==='today')  $hl['today']    = ' highlighted';
                    if ($active_period==='7days')  $hl['7days']    = ' highlighted';
                    if ($active_period==='15days') $hl['15days']   = ' highlighted';
                    if ($active_period==='custom') $hl['custom']   = ' highlighted';
                    ?>
                    <!-- Stats grid -->
                    <div class="wpaap-limits-stats-grid has-filter">
                        <div class="wpaap-limits-stat<?php echo $hl['today']; ?>">
                            <span class="wpaap-limits-stat-label"><?php esc_html_e( 'Hôm nay', 'whp' ); ?></span>
                            <span class="wpaap-limits-stat-value"><?php echo number_format($used_today); ?></span>
                            <span class="wpaap-limits-stat-unit">tokens</span>
                            <?php 
                            $today_bins = $wpaap_get_sparkline_bins($pid, 'today');
                            echo $wpaap_render_sparkline($today_bins, $p_connected); // trusted SVG
                            ?>
                            <?php if ($trend_today !== null) : ?>
                                <span class="<?php echo $trend_today>=0?'wpaap-limits-trend-up':'wpaap-limits-trend-down'; ?>">
                                    <?php echo $trend_today>=0?'↑':'↓'; ?><?php echo abs($trend_today); ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="wpaap-limits-stat<?php echo $hl['yesterday']; ?>">
                            <span class="wpaap-limits-stat-label"><?php esc_html_e( 'Hôm qua', 'whp' ); ?></span>
                            <span class="wpaap-limits-stat-value"><?php echo number_format($used_yesterday); ?></span>
                            <span class="wpaap-limits-stat-unit">tokens</span>
                            <?php 
                            $yesterday_bins = $wpaap_get_sparkline_bins($pid, 'yesterday');
                            echo $wpaap_render_sparkline($yesterday_bins, $p_connected); // trusted SVG
                            ?>
                        </div>
                        <div class="wpaap-limits-stat<?php echo $hl['7days']; ?>">
                            <span class="wpaap-limits-stat-label"><?php esc_html_e( '7 ngày qua', 'whp' ); ?></span>
                            <span class="wpaap-limits-stat-value"><?php echo number_format($used_7_days); ?></span>
                            <span class="wpaap-limits-stat-unit">tokens</span>
                            <?php 
                            $seven_days_bins = $wpaap_get_sparkline_bins($pid, '7_days');
                            echo $wpaap_render_bar_chart($seven_days_bins, $p_connected); // trusted SVG
                            ?>
                        </div>
                        <div class="wpaap-limits-stat<?php echo $hl['15days']; ?>">
                            <span class="wpaap-limits-stat-label"><?php esc_html_e( '15 ngày qua', 'whp' ); ?></span>
                            <span class="wpaap-limits-stat-value"><?php echo number_format($used_15_days); ?></span>
                            <span class="wpaap-limits-stat-unit">tokens</span>
                            <?php 
                            $fifteen_days_bins = $wpaap_get_sparkline_bins($pid, '15_days');
                            echo $wpaap_render_bar_chart($fifteen_days_bins, $p_connected); // trusted SVG
                            ?>
                        </div>
                        <div class="wpaap-limits-stat<?php echo $hl['custom']; ?>">
                            <span class="wpaap-limits-stat-label"><?php esc_html_e( 'Khoảng lọc', 'whp' ); ?></span>
                            <span class="wpaap-limits-stat-value"><?php echo number_format($used_custom); ?></span>
                            <span class="wpaap-limits-stat-unit">tokens</span>
                            <span class="wpaap-limits-stat-date" style="font-size: 11px; font-weight: 600; color: #64748b; margin-top: 4px;">
                                <?php echo esc_html(date('d/m', strtotime($filter_from))); ?> → <?php echo esc_html(date('d/m/Y', strtotime($filter_to))); ?>
                            </span>
                        </div>
                    </div>
                    <?php if ( $p_connected ) :
                        // Lấy logs 15 ngày gần nhất của provider này
                        $all_logs = get_option('wpaap_token_usage_logs', []);
                        $provider_logs = [];
                        if (is_array($all_logs)) {
                            foreach ($all_logs as $entry) {
                                if (isset($entry['provider']) && $entry['provider'] === $pid) {
                                    $provider_logs[] = $entry;
                                }
                            }
                        }
                        // Group by date
                        $daily = [];
                        foreach ($provider_logs as $entry) {
                            $d = isset($entry['date']) ? $entry['date'] : (isset($entry['timestamp']) ? date('Y-m-d', (int)$entry['timestamp']) : '');
                            if ($d) $daily[$d] = ($daily[$d] ?? 0) + (int)($entry['tokens'] ?? 0);
                        }
                        krsort($daily);
                        $daily = array_slice($daily, 0, 14, true); // 14 ngày gần nhất
                    ?>
                    <!-- Detail panel -->
                    <div class="wpaap-limits-detail-panel" id="detail-<?php echo esc_attr($pid); ?>">
                        <div class="wpaap-limits-detail-header">
                            <h4>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" style="vertical-align:middle;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                                <?php esc_html_e( 'Chi tiết sử dụng', 'whp' ); ?> — <?php echo esc_html($pinfo['name']); ?>
                            </h4>
                            <span style="font-size:12px;color:#94a3b8;"><?php esc_html_e( '14 ngày gần nhất', 'whp' ); ?></span>
                        </div>
                        <?php if (!empty($daily)) : ?>
                        <table class="wpaap-limits-detail-table">
                            <thead><tr><th><?php esc_html_e( 'Ngày', 'whp' ); ?></th><th><?php esc_html_e( 'Tokens sử dụng', 'whp' ); ?></th><th><?php esc_html_e( 'So với ngày trước', 'whp' ); ?></th></tr></thead>
                            <tbody>
                            <?php $prev = null; foreach ($daily as $d => $t) :
                                $diff = $prev !== null ? $t - $prev : null;
                            ?>
                            <tr>
                                <td><?php echo esc_html(date('d/m/Y', strtotime($d))); ?></td>
                                <td><strong><?php echo number_format($t); ?></strong> tokens</td>
                                <td>
                                    <?php if ($diff !== null && $prev > 0) :
                                        $pct = round($diff / $prev * 100, 1);
                                    ?>
                                        <span style="color:<?php echo $diff>=0?'#22c55e':'#ef4444'; ?>;font-weight:600;">
                                            <?php echo $diff>=0?'↑':'↓'; ?><?php echo abs($pct); ?>%
                                        </span>
                                    <?php elseif ($diff !== null) : ?>
                                        <span style="color:#94a3b8;">—</span>
                                    <?php else : ?>
                                        <span style="color:#94a3b8;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php $prev = $t; endforeach; ?>
                            </tbody>
                        </table>
                        <?php else : ?>
                        <div class="wpaap-limits-detail-empty">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" style="display:block;margin:0 auto 8px;"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            <?php esc_html_e( 'Chưa có dữ liệu sử dụng chi tiết theo ngày.', 'whp' ); ?><br>
                            <small><?php esc_html_e( 'Logs sẽ xuất hiện sau khi bạn sử dụng các tính năng AI.', 'whp' ); ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <!-- Summary Card -->
                <?php
                $total_yesterday = 0;
                foreach ( $providers as $pid => $pinfo ) {
                    $pc = false;
                    if ( $pid === 'google' ) $pc = $google_connected;
                    elseif ( $pid === 'anthropic' ) $pc = $anthropic_connected;
                    elseif ( $pid === 'openai' ) $pc = $openai_connected;
                    $total_yesterday += ( $pc && function_exists('wpaap_get_token_usage_stats') ) ? wpaap_get_token_usage_stats($pid, 'yesterday') : 0;
                }
                $summary_trend_today   = $total_yesterday > 0 ? round( ($total_today - $total_yesterday) / $total_yesterday * 100, 1 ) : null;
                
                // Chuẩn bị phân đoạn Donut chart
                $segments = [
                    'google' => [
                        'value' => $provider_total_tokens['google'] ?? 0,
                        'color' => '#5684D1',
                        'name'  => 'Google Gemini'
                    ],
                    'anthropic' => [
                        'value' => $provider_total_tokens['anthropic'] ?? 0,
                        'color' => '#16a34a',
                        'name'  => 'Anthropic Claude'
                    ],
                    'openai' => [
                        'value' => $provider_total_tokens['openai'] ?? 0,
                        'color' => '#a855f7',
                        'name'  => 'OpenAI GPT'
                    ]
                ];
                
                $has_data = false;
                foreach ($segments as $seg) {
                    if ($seg['value'] > 0) {
                        $has_data = true;
                        break;
                    }
                }
                if (!$has_data) {
                    $segments_display = [
                        'empty' => [
                            'value' => 1,
                            'color' => '#e2e8f0',
                            'name'  => __( 'Chưa dùng', 'whp' )
                        ]
                    ];
                    $donut_total = 1;
                } else {
                    $segments_display = $segments;
                    $donut_total = array_sum(array_column($segments, 'value'));
                }
                
                $r = 38;
                $circ = 2 * M_PI * $r; // ~238.76
                $accumulated_pct = 0;
                ?>
                <div class="wpaap-limits-summary-card">
                    <div class="wpaap-limits-summary-left">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px;">
                            <div style="width:36px;height:36px;border-radius:9px;background:linear-gradient(135deg,#0891b2,#22d3ee);display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                            </div>
                            <p class="wpaap-limits-summary-title"><?php esc_html_e( 'Tổng quan sử dụng tokens', 'whp' ); ?></p>
                        </div>
                        <p class="wpaap-limits-summary-desc"><?php esc_html_e( 'Tổng hợp từ tất cả nhà cung cấp AI đã kết nối.', 'whp' ); ?></p>
                    </div>
                    <div class="wpaap-limits-summary-center">
                        <div style="position: relative; width: 150px; height: 150px; display: flex; align-items: center; justify-content: center;">
                            <svg viewBox="0 0 100 100" width="150" height="150" style="transform: rotate(-90deg); overflow: visible;">
                                <circle cx="50" cy="50" r="<?php echo $r; ?>" fill="none" stroke="#f3f4f6" stroke-width="10" />
                                <?php foreach ($segments_display as $key => $seg) :
                                    $pct = ($seg['value'] / $donut_total) * 100;
                                    if ($pct <= 0) continue;
                                    $stroke_length = ($pct / 100) * $circ;
                                    $stroke_offset = $circ - $stroke_length;
                                    $rotation = ($accumulated_pct / 100) * 360;
                                ?>
                                    <circle class="wpaap-donut-segment" cx="50" cy="50" r="<?php echo $r; ?>" fill="none" stroke="<?php echo esc_attr($seg['color']); ?>" stroke-width="10" stroke-dasharray="<?php echo esc_attr($circ); ?>" stroke-dashoffset="<?php echo esc_attr($stroke_offset); ?>" transform="rotate(<?php echo esc_attr($rotation); ?> 50 50)" stroke-linecap="round" />
                                <?php
                                    $accumulated_pct += $pct;
                                endforeach; ?>
                            </svg>
                            <div class="wpaap-donut-center">
                                <span class="wpaap-donut-center-value"><?php echo esc_html($has_data ? number_format($grand_total) : '0'); ?></span>
                                <span class="wpaap-donut-center-label">Tokens</span>
                            </div>
                        </div>
                    </div>
                    <div class="wpaap-limits-summary-right">
                        <ul style="margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 12px; width: 100%;">
                            <?php foreach ($segments as $key => $seg) : 
                                $pct = $grand_total > 0 ? round(($seg['value'] / $grand_total) * 100) : 0;
                            ?>
                                <li style="display: flex; align-items: center; justify-content: space-between; font-size: 13.5px; width: 100%;">
                                    <span style="display: flex; align-items: center; gap: 8px; color: #475569; font-weight: 500;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: <?php echo esc_attr($seg['color']); ?>; display: inline-block; flex-shrink: 0;"></span>
                                        <?php echo esc_html($seg['name']); ?>
                                    </span>
                                    <span style="color: #64748b; font-size: 12.5px; margin-left: auto; margin-right: 12px;"><?php echo esc_html($pct); ?>%</span>
                                    <strong style="color: #0f172a; font-weight: 700;"><?php echo number_format($seg['value']); ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                </div><!-- /wpaap-limits-main -->

                <!-- Sidebar -->
                <div class="wpaap-limits-sidebar">

                    <!-- Hướng dẫn nhanh -->
                    <div class="wpaap-limits-sidebar-card">
                        <h4>
                            <div class="wpaap-limits-sidebar-icon" style="background:#fef9c3;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="#f59e0b"><path d="M9 21h6M12 3a6 6 0 0 1 6 6c0 2.22-1.21 4.16-3 5.2V17H9v-2.8C7.21 13.16 6 11.22 6 9a6 6 0 0 1 6-6z"/></svg>
                            </div>
                            <?php esc_html_e( 'Hướng dẫn nhanh', 'whp' ); ?>
                        </h4>
                        <div class="wpaap-limits-steps">
                            <div class="wpaap-limits-step">
                                <div class="wpaap-limits-step-num">1</div>
                                <div class="wpaap-limits-step-text">
                                    <strong><?php esc_html_e( 'Chọn bộ lọc thời gian', 'whp' ); ?></strong>
                                    <span><?php esc_html_e( 'Nhấn Hôm nay / 7 ngày / 15 ngày hoặc Tùy chọn để xem theo ngày cụ thể.', 'whp' ); ?></span>
                                </div>
                            </div>
                            <div class="wpaap-limits-step">
                                <div class="wpaap-limits-step-num">2</div>
                                <div class="wpaap-limits-step-text">
                                    <strong><?php esc_html_e( 'Nhấn Lọc dữ liệu', 'whp' ); ?></strong>
                                    <span><?php esc_html_e( 'Cột tương ứng sẽ được tô sáng — các cột còn lại mờ đi để dễ theo dõi.', 'whp' ); ?></span>
                                </div>
                            </div>
                            <div class="wpaap-limits-step">
                                <div class="wpaap-limits-step-num">3</div>
                                <div class="wpaap-limits-step-text">
                                    <strong><?php esc_html_e( 'Chi tiết sử dụng', 'whp' ); ?></strong>
                                    <span><?php esc_html_e( 'Nhấn nút "Chi tiết" để xem chi tiết lượng tokens tiêu thụ theo từng ngày.', 'whp' ); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mẹo tiết kiệm tokens -->
                    <div class="wpaap-limits-sidebar-card">
                        <h4>
                            <div class="wpaap-limits-sidebar-icon" style="background:#eff2fe;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                            </div>
                            <?php esc_html_e( 'Mẹo tiết kiệm tokens', 'whp' ); ?>
                        </h4>
                        <ul class="wpaap-limits-tips-list">
                            <li><?php esc_html_e( 'Viết prompt ngắn gọn, rõ ràng để giảm tokens đầu vào.', 'whp' ); ?></li>
                            <li><?php esc_html_e( 'Dùng Claude Haiku hoặc Gemini Flash cho các tác vụ đơn giản.', 'whp' ); ?></li>
                            <li><?php esc_html_e( 'Đặt lại bộ đếm định kỳ để theo dõi theo tháng.', 'whp' ); ?></li>
                            <li><?php esc_html_e( 'Kiểm tra bảng "Chi tiết" để tìm ngày tiêu thụ nhiều bất thường.', 'whp' ); ?></li>
                        </ul>
                    </div>

                    <!-- Quick link sang kết nối -->
                    <div class="wpaap-limits-sidebar-card" style="background:linear-gradient(135deg,#ecfeff,#cffafe);border:1px solid #a5f3fc;">
                        <h4 style="color:#0e7490;">
                            <div class="wpaap-limits-sidebar-icon" style="background:#fff;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            </div>
                            <?php esc_html_e( 'Kết nối thêm AI', 'whp' ); ?>
                        </h4>
                        <p style="font-size:12.5px;color:#0e7490;line-height:1.5;margin:0 0 12px;"><?php esc_html_e( 'Kết nối thêm nhà cung cấp AI để so sánh chi phí và chọn mô hình phù hợp nhất.', 'whp' ); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=connection')); ?>" style="display:inline-flex;align-items:center;gap:5px;font-size:12.5px;font-weight:700;color:#2563eb;text-decoration:none;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            <?php esc_html_e( 'Cấu hình kết nối →', 'whp' ); ?>
                        </a>
                    </div>

                </div><!-- /wpaap-limits-sidebar -->
            </div><!-- /wpaap-limits-body -->
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Chi tiết sử dụng toggle
        $('.wpaap-detail-toggle').on('click', function() {
            var provider = $(this).data('provider');
            var $panel = $('#detail-' + provider);
            var $btn = $(this);
            if ($panel.hasClass('open')) {
                $panel.removeClass('open');
                $btn.removeClass('active');
            } else {
                $panel.addClass('open');
                $btn.addClass('active');
                $('html, body').animate({ scrollTop: $panel.offset().top - 80 }, 300);
            }
        });
        // Filter bar: toggle active + update hidden input + show/hide date range
        $('.wpaap-limits-filter-btn[data-period]').on('click', function() {
            $('.wpaap-limits-filter-btn[data-period]').removeClass('active');
            $(this).addClass('active');
            var period = $(this).data('period');
            $('#wpaap-period-input').val(period);
            if (period === 'custom') {
                $('#wpaap-date-range').addClass('show');
            } else {
                $('#wpaap-date-range').removeClass('show');
                var today = new Date();
                var from = new Date();
                if (period === 'today') from = today;
                else if (period === '7days') from.setDate(today.getDate() - 7);
                else if (period === '15days') from.setDate(today.getDate() - 15);
                $('#wpaap-date-from').val(from.toISOString().split('T')[0]);
                $('#wpaap-date-to').val(today.toISOString().split('T')[0]);
            }
        });
    });
    </script>
    <?php
}
