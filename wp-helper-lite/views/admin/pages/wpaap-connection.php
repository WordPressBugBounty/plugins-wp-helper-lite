<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpaap_connection_page_layout() {
    $message = '';
    $status = 'info';

    // Xử lý lưu kết nối hệ thống toàn cục WordPress 7.0
    if ( isset( $_POST['wpaap_toggle_global_connection'] ) && check_admin_referer( 'wpaap_connection_settings_action', 'wpaap_connection_settings_nonce' ) ) {
        $conn_action = sanitize_key( $_POST['wpaap_conn_action'] ); // 'connect' or 'disconnect'

        if ( $conn_action === 'connect' ) {
            update_option( 'wpaap_core_connected', 'yes' );
            $message = __( 'Đã bật kết nối thành công với API AI!', 'whp' );
            $status = 'success';
        } else {
            update_option( 'wpaap_core_connected', 'no' );
            update_option( 'wpaap_provider_connected_google', 'no' );
            update_option( 'wpaap_provider_connected_anthropic', 'no' );
            update_option( 'wpaap_provider_connected_openai', 'no' );
            $message = __( 'Đã ngắt kết nối hệ thống AI Auto Poster.', 'whp' );
            $status = 'warning';
        }
    }

    // Xử lý lưu kết nối cho từng AI provider
    if ( isset( $_POST['wpaap_toggle_provider_connection'] ) && check_admin_referer( 'wpaap_connection_settings_action', 'wpaap_connection_settings_nonce' ) ) {
        $provider = sanitize_key( $_POST['wpaap_provider'] );
        $conn_action = sanitize_key( $_POST['wpaap_conn_action'] ); // 'connect' or 'disconnect'

        if ( $conn_action === 'connect' ) {
            // Lưu API Key trực tiếp nếu người dùng gửi lên
            if ( isset( $_POST['wpaap_api_key'] ) ) {
                $api_key = trim( sanitize_text_field( $_POST['wpaap_api_key'] ) );
                if ( ! empty( $api_key ) ) {
                    if ( $provider === 'google' ) {
                        update_option( 'connectors_gemini_api_key', $api_key );
                        update_option( 'connectors_google_api_key', $api_key );
                    } elseif ( $provider === 'anthropic' ) {
                        update_option( 'connectors_anthropic_api_key', $api_key );
                    } elseif ( $provider === 'openai' ) {
                        update_option( 'connectors_openai_api_key', $api_key );
                    }
                }
            }

            // Kiểm tra xem đã cấu hình API Key trong WordPress chưa
            $key_configured = false;
            if ( $provider === 'google' ) {
                $google_key = trim( (string) (get_option( 'connectors_gemini_api_key' ) ? get_option( 'connectors_gemini_api_key' ) : get_option( 'connectors_google_api_key' )) );
                $key_configured = ! empty( $google_key );
            } elseif ( $provider === 'anthropic' ) {
                $anthropic_key = trim( (string) get_option( 'connectors_anthropic_api_key' ) );
                $key_configured = ! empty( $anthropic_key );
            } elseif ( $provider === 'openai' ) {
                $openai_key = trim( (string) get_option( 'connectors_openai_api_key' ) );
                $key_configured = ! empty( $openai_key );
            }

            if ( ! $key_configured ) {
                $provider_name = $provider === 'google' ? 'Google Gemini' : ($provider === 'anthropic' ? 'Anthropic Claude' : 'OpenAI GPT');
                $message = sprintf( __( 'Lỗi: Bạn chưa cài đặt API Key cho %s!', 'whp' ), $provider_name );
                $status = 'error';
            } else {
                // Enforce single-provider rule
                $blocking_provider = '';
                if ( $provider !== 'google'    && get_option( 'wpaap_provider_connected_google',    'no' ) === 'yes' ) $blocking_provider = 'Google Gemini';
                if ( $provider !== 'anthropic' && get_option( 'wpaap_provider_connected_anthropic', 'no' ) === 'yes' ) $blocking_provider = 'Anthropic Claude';
                if ( $provider !== 'openai'    && get_option( 'wpaap_provider_connected_openai',    'no' ) === 'yes' ) $blocking_provider = 'OpenAI GPT';

                if ( $blocking_provider ) {
                    $message = sprintf( __( 'Vui lòng ngắt kết nối %s trước khi sử dụng nhà cung cấp khác.', 'whp' ), $blocking_provider );
                    $status  = 'error';
                } else {
                    update_option( "wpaap_provider_connected_{$provider}", 'yes' );
                    update_option( 'wpaap_core_connected', 'yes' );
                    $provider_name = $provider === 'google' ? 'Google Gemini' : ($provider === 'anthropic' ? 'Anthropic Claude' : 'OpenAI GPT');
                    $message = sprintf( __( 'Đã kết nối thành công với %s!', 'whp' ), $provider_name );
                    $status  = 'success';
                }
            }
        } else {
            update_option( "wpaap_provider_connected_{$provider}", 'no' );
            $provider_name = $provider === 'google' ? 'Google Gemini' : ($provider === 'anthropic' ? 'Anthropic Claude' : 'OpenAI GPT');
            $message = sprintf( __( 'Đã ngắt kết nối với %s.', 'whp' ), $provider_name );
            $status = 'warning';
        }
    }

    // Lưu model được chọn cho provider
    if ( isset( $_POST['wpaap_save_provider_model'] ) && check_admin_referer( 'wpaap_connection_settings_action', 'wpaap_connection_settings_nonce' ) ) {
        $provider = sanitize_key( $_POST['wpaap_provider'] ?? '' );
        $model    = sanitize_text_field( $_POST['wpaap_model'] ?? '' );
        if ( $provider && $model ) {
            update_option( "wpaap_{$provider}_model", $model );
            update_option( 'wpaap_default_ai_model', $model );
            $message = __( 'Đã lưu model thành công!', 'whp' );
            $status  = 'success';
        }
    }

    $is_connected = get_option( 'wpaap_core_connected', 'no' ) === 'yes';

    $google_key = trim( (string) (get_option( 'connectors_gemini_api_key' ) ? get_option( 'connectors_gemini_api_key' ) : get_option( 'connectors_google_api_key' )) );
    $google_connected = $is_connected && !empty( $google_key ) && ( get_option( 'wpaap_provider_connected_google', 'no' ) === 'yes' );

    $anthropic_key = trim( (string) get_option( 'connectors_anthropic_api_key' ) );
    $anthropic_connected = $is_connected && !empty( $anthropic_key ) && ( get_option( 'wpaap_provider_connected_anthropic', 'no' ) === 'yes' );

    $openai_key = trim( (string) get_option( 'connectors_openai_api_key' ) );
    $openai_connected = $is_connected && !empty( $openai_key ) && ( get_option( 'wpaap_provider_connected_openai', 'no' ) === 'yes' );

    $any_connected          = $google_connected || $anthropic_connected || $openai_connected;
    $connected_provider_name = $google_connected ? 'Google Gemini' : ( $anthropic_connected ? 'Anthropic Claude' : ( $openai_connected ? 'OpenAI GPT' : '' ) );

    $stored_anthropic = trim( (string) get_option( 'connectors_anthropic_api_key' ) );
    $stored_google = trim( (string) (get_option( 'connectors_gemini_api_key' ) ? get_option( 'connectors_gemini_api_key' ) : get_option( 'connectors_google_api_key' )) );
    $stored_openai = trim( (string) get_option( 'connectors_openai_api_key' ) );
    ?>

<style>
/* ==============================
   AI Connection Page - Modern Layout
   ============================== */
.mb-wph-page {
    font-family: inherit;
    max-width: 1200px;
    margin: 20px auto 40px;
    padding: 0 15px 40px;
    box-sizing: border-box;
}
.mb-conn-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 900px) { .mb-conn-layout { grid-template-columns: 1fr; } .mb-conn-sidebar { display: none; } }
.mb-conn-sidebar { position: sticky; top: 32px; }
.mb-conn-sidebar-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 18px 20px; margin-bottom: 16px;
}
.mb-conn-sidebar-card h4 {
    display: flex; align-items: center; gap: 8px;
    font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 14px;
}
.mb-conn-sidebar-icon {
    width: 28px; height: 28px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
/* Steps */
.mb-conn-steps { display: flex; flex-direction: column; gap: 12px; }
.mb-conn-step { display: flex; align-items: flex-start; gap: 10px; }
.mb-conn-step-num {
    width: 22px; height: 22px; border-radius: 50%;
    background: #eff2fe; color: #3858e9;
    font-size: 12px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mb-conn-step-text strong { display: block; font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
.mb-conn-step-text span  { font-size: 12px; color: #64748b; line-height: 1.5; }
/* Provider quick links */
.mb-conn-provider-links { display: flex; flex-direction: column; gap: 8px; }
.mb-conn-provider-link {
    display: flex; align-items: center; justify-content: space-between;
    padding: 9px 12px; border-radius: 8px; border: 1px solid #f1f5f9;
    background: #fafbfd; cursor: pointer; transition: all 0.15s; text-decoration: none;
}
.mb-conn-provider-link:hover { border-color: #c7d2fe; background: #eff2fe; }
.mb-conn-provider-link-left { display: flex; align-items: center; gap: 10px; }
.mb-conn-provider-link-name { font-size: 13.5px; font-weight: 600; color: #0f172a; }
.mb-conn-provider-link-info { font-size: 11.5px; color: #94a3b8; }
.mb-conn-sidebar-link {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12.5px; color: #3858e9; font-weight: 600; text-decoration: none;
    margin-top: 12px;
}
.mb-conn-sidebar-link:hover { text-decoration: underline; }
.mb-wph-header-card {
    position: relative;
    background: linear-gradient(100deg, #ffffff 0%, #eef2ff 45%, #e0e7ff 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(79,70,229,0.1), 0 0 0 1px #c7d2fe;
    margin-bottom: 25px;
    overflow: hidden;
    min-height: 168px;
    display: flex;
    align-items: stretch;
}
.mb-wph-header-left {
    position: relative; z-index: 2;
    padding: 32px 36px;
    display: flex; flex-direction: column; justify-content: center; gap: 14px;
    max-width: 500px; flex-shrink: 0;
}
.mb-wph-header-title-row { display: flex; align-items: center; gap: 14px; }
.mb-wph-header-icon-box {
    width: 44px; height: 44px; border-radius: 12px;
    background: linear-gradient(135deg, #4f46e5, #818cf8);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 12px rgba(79,70,229,0.3);
}
.mb-wph-header-right {
    position: absolute; inset: 0 0 0 38%;
    overflow: hidden; pointer-events: none;
}

/* Cards */
.mb-wph-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 20px;
    overflow: hidden;
}
.mb-wph-card-inner {
    padding: 24px;
}
.mb-wph-section-card {
    border-left: 4px solid transparent;
    transition: box-shadow 0.2s;
}
.mb-wph-section-card.accent-blue { border-left-color: #3858e9; }

/* Section header */
.mb-wph-section-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f1f5f9;
}
.mb-wph-section-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 17px;
    background: #eff2fe;
}
.mb-wph-section-header-text h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}
.mb-wph-section-header-text p {
    margin: 0;
    font-size: 13.5px;
    color: #475569;
    line-height: 1.6;
}

/* Provider rows */
.mb-conn-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 0;
    border-bottom: 1px solid #f1f5f9;
    gap: 20px;
}
.mb-conn-row:last-child { border-bottom: none; padding-bottom: 0; }
.mb-conn-row:first-child { padding-top: 0; }
.mb-conn-row--locked {
    opacity: 0.38;
    pointer-events: none;
    filter: grayscale(0.25);
    position: relative;
    user-select: none;
}
.mb-conn-row--locked::after {
    content: 'Ngắt kết nối ' attr(data-locked-by) ' trước khi chọn nhà cung cấp khác';
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12.5px;
    color: #475569;
    background: rgba(255,255,255,0.55);
    border-radius: 12px;
    font-weight: 500;
    pointer-events: none;
}
.mb-conn-left {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    flex: 0 0 340px;
    min-width: 0;
}
.mb-conn-logo {
    width: 48px; height: 48px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.mb-conn-info h4 { margin: 0 0 3px 0; font-size: 15px; font-weight: 700; color: #0f172a; }
.mb-conn-info p  { margin: 0; font-size: 13px; color: #64748b; line-height: 1.4; }
.mb-conn-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    justify-content: flex-end;
}

/* Status badge */
.mb-conn-status {
    background: #dcfce7;
    color: #16a34a;
    font-size: 12.5px;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}
.mb-conn-status svg {
    flex-shrink: 0;
}

/* Action buttons */
.mb-conn-btn {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.2s; white-space: nowrap;
    text-decoration: none !important; border: 1.5px solid; flex-shrink: 0;
}
.mb-conn-btn-disconnect { background:#fff; border-color:#dc2626; color:#dc2626 !important; }
.mb-conn-btn-disconnect:hover { background:#fef2f2; }
.mb-conn-btn-test { background:#fff; border-color:#10b981; color:#10b981 !important; }
.mb-conn-btn-test:hover { background:#f0fdf4; }
.mb-conn-btn-connect {
    background: linear-gradient(135deg, #3858e9, #2563eb);
    border-color: #3858e9; color: #fff !important;
    box-shadow: 0 2px 8px rgba(56,88,233,0.3);
}
.mb-conn-btn-connect:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    box-shadow: 0 4px 14px rgba(56,88,233,0.4); transform: translateY(-1px);
}
/* Connected meta info */
.mb-conn-meta { font-size: 12px; color: #94a3b8; margin-top: 6px; display: flex; align-items: center; gap: 8px 14px; flex-wrap: wrap; }
.mb-conn-meta span { display: inline-flex; align-items: center; gap: 4px; }
.mb-conn-meta strong { color: #4f46e5; font-weight: 600; }
/* Eye toggle */
.mb-conn-input-wrap { position: relative; flex: 1; min-width: 0; }
.mb-conn-eye { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0; display: flex; align-items: center; }
.mb-conn-eye:hover { color: #64748b; }
.mb-conn-input-wrap .mb-conn-input { padding-right: 36px; width: 100%; }
/* Security card */
.mb-conn-security-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 20px 24px; margin-top: 16px;
    border-left: 4px solid #10b981;
}
.mb-conn-security-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9; }
.mb-conn-security-icon { width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg,#10b981,#34d399); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 3px 8px rgba(16,185,129,0.25); }
.mb-conn-security-title { font-size: 14.5px; font-weight: 700; color: #0f172a; margin: 0 0 3px; }
.mb-conn-security-desc { font-size: 12.5px; color: #64748b; line-height: 1.4; margin: 0; }
.mb-conn-security-features { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.mb-conn-security-feat { display: flex; align-items: flex-start; gap: 10px; border-radius: 10px; padding: 12px 14px; }
.mb-conn-security-feat-icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mb-conn-security-feat-text strong { display: block; font-size: 13px; font-weight: 700; margin-bottom: 2px; }
.mb-conn-security-feat-text span { font-size: 12px; line-height: 1.4; display: block; }
/* Status card */
.mb-conn-status-card {
    position: relative;
    background: linear-gradient(100deg, #ffffff 0%, #f5f3ff 60%, #ede9fe 100%);
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #ddd6fe;
    padding: 20px 24px; margin-top: 16px;
    overflow: hidden;
    display: flex; gap: 0;
}
.mb-conn-status-inner { flex: 1; position: relative; z-index: 2; min-width: 0; }
.mb-conn-status-illus { position: absolute; inset: 0 0 0 55%; pointer-events: none; overflow: hidden; }
.mb-conn-status-header { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
.mb-conn-status-header h4 { margin: 0; font-size: 14.5px; font-weight: 700; color: #0f172a; }
.mb-conn-status-rows { display: flex; flex-direction: column; gap: 0; }
.mb-conn-status-row { display: flex; align-items: center; justify-content: space-between; padding: 11px 0; border-bottom: 1px solid #f8fafc; }
.mb-conn-status-row:last-child { border-bottom: none; padding-bottom: 0; }
.mb-conn-status-row:first-child { padding-top: 0; }
.mb-conn-status-left { display: flex; align-items: center; gap: 10px; }
.mb-conn-status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.mb-conn-status-dot.connected { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.2); }
.mb-conn-status-dot.disconnected { background: #cbd5e1; }
.mb-conn-status-name { font-size: 13.5px; font-weight: 600; color: #0f172a; }
.mb-conn-status-badge { font-size: 12px; font-weight: 600; padding: 3px 10px; border-radius: 20px; }
.mb-conn-status-badge.on { background: #dcfce7; color: #16a34a; }
.mb-conn-status-badge.off { background: #f1f5f9; color: #94a3b8; }
.mb-conn-status-time { font-size: 11.5px; color: #94a3b8; }
/* CTA banner */
.mb-conn-cta-banner {
    margin-top: 16px; padding: 20px 24px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    box-shadow: 0 4px 14px rgba(79,70,229,0.3);
}
.mb-conn-cta-banner-left { display: flex; align-items: center; gap: 14px; }
.mb-conn-cta-icon { width: 44px; height: 44px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mb-conn-cta-title { font-size: 15px; font-weight: 700; color: #fff; margin: 0 0 3px; }
.mb-conn-cta-desc { font-size: 12.5px; color: rgba(255,255,255,0.75); margin: 0; }
.mb-conn-cta-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: #fff; border-radius: 8px; font-size: 13px; font-weight: 700; color: #4f46e5; text-decoration: none; white-space: nowrap; flex-shrink: 0; transition: all 0.2s; }
.mb-conn-cta-btn:hover { background: #f0f4ff; transform: translateY(-1px); }
/* Tips card */
.mb-conn-tips-card {
    background: #eff2fe; border-radius: 12px; border: 1px solid #c7d2fe;
    padding: 16px 20px; margin-top: 16px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
}
.mb-conn-tips-left { display: flex; align-items: flex-start; gap: 10px; flex: 1; min-width: 200px; }
.mb-conn-tips-title { font-size: 13.5px; font-weight: 700; color: #3730a3; margin: 0 0 6px; }
.mb-conn-tips-list { margin: 0; padding-left: 16px; }
.mb-conn-tips-list li { font-size: 12.5px; color: #4338ca; line-height: 1.6; }
.mb-conn-tips-actions { display: flex; gap: 10px; flex-shrink: 0; }
.mb-conn-tips-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer; text-decoration: none; border: 1.5px solid #818cf8; background: #fff; color: #4f46e5; transition: all 0.2s; }
.mb-conn-tips-btn:hover { background: #eef2ff; }

/* API Key input */
.mb-conn-input {
    flex: 1;
    min-width: 0;
    width: 100%;
    padding: 8px 14px !important;
    border: 1.5px solid #d1d5db !important;
    border-radius: 8px !important;
    font-size: 13.5px !important;
    color: #0f172a !important;
    background: #f8fafc !important;
    box-sizing: border-box;
    font-family: inherit;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    outline: none !important;
    line-height: 1.5;
    box-shadow: none !important;
    min-height: unset !important;
}
.mb-conn-input::placeholder { color: #94a3b8; }
.mb-conn-input:focus {
    border-color: #3858e9 !important;
    background: #fff !important;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.1) !important;
}

/* Connect form layout */
.mb-conn-form {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}
.mb-conn-form-connected {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>
<script>
function mbToggleApiKey(btn) {
    var i = btn.previousElementSibling;
    var show = i.type === 'password';
    i.type = show ? 'text' : 'password';
    var svgOpen  = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    var svgSlash = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    btn.querySelector('svg').innerHTML = show ? svgSlash : svgOpen;
}
</script>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-header-left">
            <div class="mb-wph-header-title-row">
                <div class="mb-wph-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e( 'Cấu hình Kết nối AI', 'whp' ); ?></h1>
            </div>
            <p style="margin:0;font-size:13.5px;color:#64748b;line-height:1.6;padding-left:58px;max-width:400px;"><?php esc_html_e( 'Cấu hình mã khóa API (Direct API Keys) để kết nối trực tiếp với các mô hình trí tuệ nhân tạo độc lập.', 'whp' ); ?></p>
        </div>
        <div class="mb-wph-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="conn_grad1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#4f46e5" stop-opacity="0.18"/><stop offset="100%" stop-color="#818cf8" stop-opacity="0.08"/></linearGradient>
                    <linearGradient id="conn_grad2" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#4f46e5" stop-opacity="0.25"/><stop offset="100%" stop-color="#818cf8" stop-opacity="0.12"/></linearGradient>
                </defs>
                <!-- API key card -->
                <rect x="310" y="38" width="130" height="52" rx="10" fill="url(#conn_grad2)" stroke="#c7d2fe" stroke-width="1.5"/>
                <circle cx="330" cy="64" r="6" fill="#4f46e5" fill-opacity="0.5"/>
                <rect x="344" y="58" width="60" height="5" rx="2.5" fill="#4f46e5" fill-opacity="0.3"/>
                <rect x="344" y="67" width="40" height="4" rx="2" fill="#818cf8" fill-opacity="0.4"/>
                <rect x="410" y="60" width="18" height="8" rx="3" fill="#4f46e5" fill-opacity="0.4"/>
                <!-- Connection lines -->
                <path d="M460 64 Q500 64 510 84" stroke="#818cf8" stroke-width="1.5" stroke-opacity="0.5" fill="none" stroke-dasharray="4 3"/>
                <path d="M460 64 Q500 64 510 44" stroke="#818cf8" stroke-width="1.5" stroke-opacity="0.5" fill="none" stroke-dasharray="4 3"/>
                <path d="M460 64 L510 64" stroke="#4f46e5" stroke-width="1.5" stroke-opacity="0.4" fill="none" stroke-dasharray="4 3"/>
                <!-- Provider circle: Anthropic A -->
                <circle cx="530" cy="44" r="20" fill="url(#conn_grad2)" stroke="#c7d2fe" stroke-width="1.5"/>
                <text x="530" y="50" text-anchor="middle" font-size="16" font-weight="700" fill="#D97757" fill-opacity="0.8">A</text>
                <!-- Provider circle: Google G -->
                <circle cx="530" cy="84" r="20" fill="url(#conn_grad2)" stroke="#c7d2fe" stroke-width="1.5"/>
                <text x="530" y="90" text-anchor="middle" font-size="16" font-weight="700" fill="#5684D1" fill-opacity="0.8">G</text>
                <!-- Provider circle: OpenAI O -->
                <circle cx="530" cy="124" r="20" fill="url(#conn_grad2)" stroke="#c7d2fe" stroke-width="1.5"/>
                <text x="530" y="130" text-anchor="middle" font-size="16" font-weight="700" fill="#475569" fill-opacity="0.7">O</text>
                <!-- Connecting line to left provider -->
                <path d="M460 64 Q500 64 510 124" stroke="#818cf8" stroke-width="1.5" stroke-opacity="0.4" fill="none" stroke-dasharray="4 3"/>
                <!-- Plug/socket icon -->
                <rect x="580" y="55" width="40" height="58" rx="8" fill="url(#conn_grad1)" stroke="#c7d2fe" stroke-width="1.5"/>
                <rect x="590" y="48" width="8" height="12" rx="2" fill="#4f46e5" fill-opacity="0.45"/>
                <rect x="606" y="48" width="8" height="12" rx="2" fill="#4f46e5" fill-opacity="0.45"/>
                <circle cx="600" cy="80" r="8" fill="#4f46e5" fill-opacity="0.2" stroke="#818cf8" stroke-width="1.5"/>
                <rect x="597" y="78" width="6" height="10" rx="2" fill="#4f46e5" fill-opacity="0.4"/>
                <!-- Decorative nodes -->
                <circle cx="480" cy="130" r="5" fill="#818cf8" fill-opacity="0.3"/>
                <circle cx="560" cy="20" r="4" fill="#4f46e5" fill-opacity="0.2"/>
                <circle cx="640" cy="140" r="6" fill="#818cf8" fill-opacity="0.2"/>
            </svg>
        </div>
    </div>

    <?php if ( ! empty( $message ) ) : ?>
        <div class="notice notice-<?php echo esc_attr( $status ); ?> is-dismissible" style="margin: 0 0 20px 0; border-radius: 8px;">
            <p><strong><?php echo esc_html( $message ); ?></strong></p>
        </div>
    <?php endif; ?>

    <div class="mb-conn-layout">
    <div class="mb-conn-main">

    <!-- Providers Card -->
    <div class="mb-wph-card mb-wph-section-card accent-blue">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                </div>
                <div class="mb-wph-section-header-text">
                    <h3><?php esc_html_e( 'Nhà cung cấp AI', 'whp' ); ?></h3>
                    <p><?php esc_html_e( 'Nhập API Key và kết nối một nhà cung cấp. Chỉ một nhà cung cấp hoạt động tại một thời điểm — muốn đổi, ngắt kết nối hiện tại trước.', 'whp' ); ?></p>
                </div>
            </div>

            <?php
            $model_lists = [
                'anthropic' => [
                    'claude-opus-4-8'            => __( 'Claude Opus 4.8 (Mạnh nhất)', 'whp' ),
                    'claude-sonnet-4-6'          => __( 'Claude Sonnet 4.6 (Khuyên dùng)', 'whp' ),
                    'claude-haiku-4-5-20251001'  => __( 'Claude Haiku 4.5 (Nhanh)', 'whp' ),
                    'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
                    'claude-3-5-haiku-20241022'  => 'Claude 3.5 Haiku',
                ],
                'google' => [
                    'gemini-3.5-flash' => __( 'Gemini 3.5 Flash — Nhanh & thông minh nhất (Mới nhất)', 'whp' ),
                    'gemini-3.1-pro'   => __( 'Gemini 3.1 Pro — Chất lượng cao nhất (Mới nhất)', 'whp' ),
                    'gemini-2.5-flash-lite' => __( 'Gemini 2.5 Flash-Lite — Nhanh (Ổn định)', 'whp' ),
                    'gemini-2.5-flash'      => __( 'Gemini 2.5 Flash — Toàn diện (Ổn định)', 'whp' ),
                    'gemini-2.5-pro'        => __( 'Gemini 2.5 Pro — Nâng cao (Ổn định)', 'whp' ),
                    'gemini-2.0-flash'      => 'Gemini 2.0 Flash',
                ],
                'openai' => [
                    'gpt-4o'       => __( 'GPT-4o (Khuyên dùng)', 'whp' ),
                    'gpt-4o-mini'  => __( 'GPT-4o Mini (Nhanh)', 'whp' ),
                    'gpt-4-turbo'  => 'GPT-4 Turbo',
                    'gpt-3.5-turbo'=> 'GPT-3.5 Turbo',
                ],
            ];
            $default_models = [
                'anthropic' => 'claude-sonnet-4-6',
                'google'    => 'gemini-3.5-flash',
                'openai'    => 'gpt-4o',
            ];

            $providers = [
                'anthropic' => [
                    'label'    => __( 'Anthropic (Claude)', 'whp' ),
                    'desc'     => __( 'Tạo văn bản với Claude', 'whp' ),
                    'connected'=> $anthropic_connected,
                    'stored'   => $stored_anthropic,
                    'api_url'  => 'https://console.anthropic.com/settings/keys',
                    'logo'     => '<svg viewBox="0 0 24 24" width="28" height="28" style="fill:#D97757"><path d="m4.7144 15.9555 4.7174-2.6471.079-.2307-.079-.1275h-.2307l-.7893-.0486-2.6956-.0729-2.3375-.0971-2.2646-.1214-.5707-.1215-.5343-.7042.0546-.3522.4797-.3218.686.0608 1.5179.1032 2.2767.1578 1.6514.0972 2.4468.255h.3886l.0546-.1579-.1336-.0971-.1032-.0972L6.973 9.8356l-2.55-1.6879-1.3356-.9714-.7225-.4918-.3643-.4614-.1578-1.0078.6557-.7225.8803.0607.2246.0607.8925.686 1.9064 1.4754 2.4893 1.8336.3643.3035.1457-.1032.0182-.0728-.164-.2733-1.3539-2.4467-1.445-2.4893-.6435-1.032-.17-.6194c-.0607-.255-.1032-.4674-.1032-.7285L6.287.1335 6.6997 0l.9957.1336.419.3642.6192 1.4147 1.0018 2.2282 1.5543 3.0296.4553.8985.2429.8318.091.255h.1579v-.1457l.1275-1.706.2368-2.0947.2307-2.6957.0789-.7589.3764-.9107.7468-.4918.5828.2793.4797.686-.0668.4433-.2853 1.8517-.5586 2.9021-.3643 1.9429h.2125l.2429-.2429.9835-1.3053 1.6514-2.0643.7286-.8196.85-.9046.5464-.4311h1.0321l.759 1.1293-.34 1.1657-1.0625 1.3478-.8804 1.1414-1.2628 1.7-.7893 1.36.0729.1093.1882-.0183 2.8535-.607 1.5421-.2794 1.8396-.3157.8318.3886.091.3946-.3278.8075-1.967.4857-2.3072.4614-3.4364.8136-.0425.0304.0486.0607 1.5482.1457.6618.0364h1.621l3.0175.2247.7892.522.4736.6376-.079.4857-1.2142.6193-1.6393-.3886-3.825-.9107-1.3113-.3279h-.1822v.1093l1.0929 1.0686 2.0035 1.8092 2.5075 2.3314.1275.5768-.3218.4554-.34-.0486-2.2039-1.6575-.85-.7468-1.9246-1.621h-.1275v.17l.4432.6496 2.3436 3.5214.1214 1.0807-.17.3521-.6071.2125-.6679-.1214-1.3721-1.9246L14.38 17.959l-1.1414-1.9428-.1397.079-.674 7.2552-.3156.3703-.7286.2793-.6071-.4614-.3218-.7468.3218-1.4753.3886-1.9246.3157-1.53.2853-1.9004.17-.6314-.0121-.0425-.1397.0182-1.4328 1.9672-2.1796 2.9446-1.7243 1.8456-.4128.164-.7164-.3704.0667-.6618.4008-.5889 2.386-3.0357 1.4389-1.882.929-1.0868-.0062-.1579h-.0546l-6.3385 4.1164-1.1293.1457-.4857-.4554.0608-.7467.2307-.2429 1.9064-1.3114Z"/></svg>',
                ],
                'google' => [
                    'label'    => __( 'Google (Gemini)', 'whp' ),
                    'desc'     => __( 'Tạo nội dung và hình ảnh với Gemini và Imagen', 'whp' ),
                    'connected'=> $google_connected,
                    'stored'   => $stored_google,
                    'api_url'  => 'https://aistudio.google.com/app/apikey',
                    'logo'     => '<svg viewBox="0 0 28 28" width="28" height="28"><path d="M14 28C14 26.0633 13.6267 24.2433 12.88 22.54C12.1567 20.8367 11.165 19.355 9.905 18.095C8.645 16.835 7.16333 15.8433 5.46 15.12C3.75667 14.3733 1.93667 14 0 14C1.93667 14 3.75667 13.6383 5.46 12.915C7.16333 12.1683 8.645 11.165 9.905 9.905C11.165 8.645 12.1567 7.16333 12.88 5.46C13.6267 3.75667 14 1.93667 14 0C14 1.93667 14.3617 3.75667 15.085 5.46C15.8317 7.16333 16.835 8.645 18.095 9.905C19.355 11.165 20.8367 12.1683 22.54 12.915C24.2433 13.6383 26.0633 14 28 14C26.0633 14 24.2433 14.3733 22.54 15.12C20.8367 15.8433 19.355 16.835 18.095 18.095C16.835 19.355 15.8317 20.8367 15.085 22.54C14.3617 24.2433 14 26.0633 14 28Z" fill="url(#geminiGradConn)"/><defs><radialGradient id="geminiGradConn" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(2.77876 11.3795) rotate(18.6832) scale(29.8025 238.737)"><stop offset="0.0671246" stop-color="#9168C0"/><stop offset="0.342551" stop-color="#5684D1"/><stop offset="0.672076" stop-color="#1BA1E3"/></radialGradient></defs></svg>',
                ],
                'openai' => [
                    'label'    => __( 'OpenAI (GPT)', 'whp' ),
                    'desc'     => __( 'Tạo văn bản và hình ảnh với GPT và DALL-E', 'whp' ),
                    'connected'=> $openai_connected,
                    'stored'   => $stored_openai,
                    'api_url'  => 'https://platform.openai.com/api-keys',
                    'logo'     => '<svg viewBox="0 0 24 24" width="28" height="28" style="fill:#000"><path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z"/></svg>',
                ],
            ];
            foreach ( $providers as $provider_key => $prov ) :
                $current_model       = get_option( "wpaap_{$provider_key}_model", $default_models[ $provider_key ] );
                $current_model_label = $model_lists[ $provider_key ][ $current_model ] ?? $current_model;
                $is_locked           = $any_connected && ! $prov['connected'];
            ?>
            <div class="mb-conn-row<?php echo $is_locked ? ' mb-conn-row--locked' : ''; ?>"
                 <?php echo $is_locked ? 'data-locked-by="' . esc_attr( $connected_provider_name ) . '"' : ''; ?>
                 style="flex-wrap:wrap;">
                <div class="mb-conn-left">
                    <div class="mb-conn-logo"><?php echo $prov['logo']; ?></div>
                    <div class="mb-conn-info">
                        <h4><?php echo esc_html( $prov['label'] ); ?></h4>
                        <p><?php echo esc_html( $prov['desc'] ); ?></p>
                        <?php if ( $prov['connected'] ) : ?>
                        <div class="mb-conn-meta">
                            <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> <?php esc_html_e( 'Đã kết nối lúc:', 'whp' ); ?> <strong><?php echo date('d/m/Y H:i', (int) get_option("wpaap_provider_connected_time_{$provider_key}", time())); ?></strong></span>
                            <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg> <?php esc_html_e( 'Model:', 'whp' ); ?> <strong><?php echo esc_html( str_replace( __( ' (Khuyên dùng)', 'whp' ), '', $current_model_label ) ); ?></strong></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mb-conn-right">
                    <?php if ( $prov['connected'] ) : ?>
                        <span class="mb-conn-status">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <?php esc_html_e( 'Đã kết nối', 'whp' ); ?>
                        </span>
                        <form method="post" action="" class="mb-conn-form-connected">
                            <?php wp_nonce_field( 'wpaap_connection_settings_action', 'wpaap_connection_settings_nonce' ); ?>
                            <input type="hidden" name="wpaap_provider" value="<?php echo esc_attr( $provider_key ); ?>" />
                            <input type="hidden" name="wpaap_conn_action" value="disconnect" />
                            <button type="submit" name="wpaap_toggle_provider_connection" class="mb-conn-btn mb-conn-btn-disconnect"><?php esc_html_e( 'Ngắt kết nối', 'whp' ); ?></button>
                        </form>
                        <button type="button" class="mb-conn-btn mb-conn-btn-test wpaap-test-conn-btn" data-provider="<?php echo esc_attr( $provider_key ); ?>"><?php esc_html_e( 'Kiểm tra', 'whp' ); ?></button>
                    <?php else : ?>
                        <form method="post" action="" class="mb-conn-form">
                            <?php wp_nonce_field( 'wpaap_connection_settings_action', 'wpaap_connection_settings_nonce' ); ?>
                            <input type="hidden" name="wpaap_provider" value="<?php echo esc_attr( $provider_key ); ?>" />
                            <input type="hidden" name="wpaap_conn_action" value="connect" />
                            <div style="display:flex;flex-direction:column;gap:5px;flex:1;min-width:0;">
                                <div class="mb-conn-input-wrap">
                                    <input type="password" name="wpaap_api_key" value="<?php echo esc_attr( $prov['stored'] ); ?>" placeholder="<?php esc_attr_e( 'Nhập API Key...', 'whp' ); ?>" class="mb-conn-input" required />
                                    <button type="button" class="mb-conn-eye" onclick="mbToggleApiKey(this)" title="<?php esc_attr_e( 'Hiện/Ẩn API Key', 'whp' ); ?>">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                </div>
                                <?php if ( ! empty( $prov['api_url'] ) ) : ?>
                                <a href="<?php echo esc_url( $prov['api_url'] ); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:4px;font-size:11.5px;color:#3b82f6;text-decoration:none;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    <?php esc_html_e( 'Lấy API Key tại', 'whp' ); ?> <?php echo esc_html( $prov['api_url'] ); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                            <button type="submit" name="wpaap_toggle_provider_connection" class="mb-conn-btn mb-conn-btn-connect" style="align-self:flex-start;"><?php esc_html_e( 'Kết nối', 'whp' ); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php if ( $prov['connected'] ) : ?>
                <div style="flex-basis:100%;padding-top:12px;border-top:1px solid #f1f5f9;margin-top:4px;padding-left:66px;">
                    <form method="post" action="" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <?php wp_nonce_field( 'wpaap_connection_settings_action', 'wpaap_connection_settings_nonce' ); ?>
                        <input type="hidden" name="wpaap_provider" value="<?php echo esc_attr( $provider_key ); ?>" />
                        <label style="font-size:12px;font-weight:600;color:#475569;white-space:nowrap;flex-shrink:0;"><?php esc_html_e( 'Model AI:', 'whp' ); ?></label>
                        <select name="wpaap_model" style="flex:1;min-width:200px;max-width:480px;padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:12.5px;color:#1e293b;background:#fff;cursor:pointer;outline:none;transition:border-color .15s;"
                                onfocus="this.style.borderColor='#3858e9'" onblur="this.style.borderColor='#e2e8f0'">
                            <?php foreach ( $model_lists[ $provider_key ] as $mv => $ml ) :
                                $parts   = explode( ' — ', $ml, 2 );
                                $mname   = $parts[0];
                                $mdesc   = isset( $parts[1] ) ? ' — ' . $parts[1] : '';
                                $is_new  = strpos( $ml, __( 'Mới nhất', 'whp' ) ) !== false;
                                $label   = $mname . $mdesc . ( $is_new ? ' ★' : '' );
                            ?>
                            <option value="<?php echo esc_attr( $mv ); ?>" <?php selected( $current_model, $mv ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="wpaap_save_provider_model"
                                style="flex-shrink:0;padding:6px 16px;border:1.5px solid #3858e9;border-radius:7px;font-size:12px;font-weight:600;color:#3858e9;background:#fff;cursor:pointer;transition:background .15s,color .15s;white-space:nowrap;"
                                onmouseover="this.style.background='#3858e9';this.style.color='#fff'"
                                onmouseout="this.style.background='#fff';this.style.color='#3858e9'"><?php esc_html_e( 'Lưu model', 'whp' ); ?></button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>


        </div>
    </div>

    <!-- Security info card -->
    <div class="mb-conn-security-card">
        <div class="mb-conn-security-header">
            <div class="mb-conn-security-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
                <p class="mb-conn-security-title"><?php esc_html_e( 'Bảo mật API Key', 'whp' ); ?></p>
                <p class="mb-conn-security-desc"><?php esc_html_e( 'API Key được mã hóa và lưu trữ an toàn. Chúng tôi không lưu trữ hoặc chia sẻ khóa API của bạn với bên thứ ba.', 'whp' ); ?></p>
            </div>
        </div>
        <div class="mb-conn-security-features">
            <div class="mb-conn-security-feat" style="background:#eff6ff;border:1px solid #bfdbfe;">
                <div class="mb-conn-security-feat-icon" style="background:#dbeafe;border:none;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></div>
                <div class="mb-conn-security-feat-text"><strong style="color:#1e40af;"><?php esc_html_e( 'Mã hóa AES-256', 'whp' ); ?></strong><span style="color:#3b82f6;"><?php esc_html_e( 'Bảo mật cấp ngân hàng', 'whp' ); ?></span></div>
            </div>
            <div class="mb-conn-security-feat" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                <div class="mb-conn-security-feat-icon" style="background:#dcfce7;border:none;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                <div class="mb-conn-security-feat-text"><strong style="color:#14532d;"><?php esc_html_e( 'Không lưu trữ', 'whp' ); ?></strong><span style="color:#16a34a;"><?php esc_html_e( 'Chỉ lưu tạm thời', 'whp' ); ?></span></div>
            </div>
            <div class="mb-conn-security-feat" style="background:#fff7ed;border:1px solid #fed7aa;">
                <div class="mb-conn-security-feat-icon" style="background:#ffedd5;border:none;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg></div>
                <div class="mb-conn-security-feat-text"><strong style="color:#7c2d12;"><?php esc_html_e( 'Kiểm soát hoàn toàn', 'whp' ); ?></strong><span style="color:#ea580c;"><?php esc_html_e( 'Bạn toàn quyền quản lý', 'whp' ); ?></span></div>
            </div>
        </div>
    </div>

    <!-- Status card -->
    <div class="mb-conn-status-card">
        <div class="mb-conn-status-inner">
            <div class="mb-conn-status-header">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                <h4><?php esc_html_e( 'Trạng thái kết nối', 'whp' ); ?></h4>
            </div>
            <div class="mb-conn-status-rows">
                <?php
                $status_providers = [
                    'anthropic' => ['name' => 'Anthropic (Claude)', 'connected' => $anthropic_connected],
                    'google'    => ['name' => 'Google (Gemini)',    'connected' => $google_connected],
                    'openai'    => ['name' => 'OpenAI (GPT)',       'connected' => $openai_connected],
                ];
                foreach ($status_providers as $key => $sp) :
                    $time = get_option("wpaap_provider_connected_time_{$key}");
                ?>
                <div class="mb-conn-status-row">
                    <div class="mb-conn-status-left">
                        <div class="mb-conn-status-dot <?php echo $sp['connected'] ? 'connected' : 'disconnected'; ?>"></div>
                        <span class="mb-conn-status-name"><?php echo esc_html($sp['name']); ?></span>
                        <?php if ($sp['connected'] && $time) : ?>
                            <span class="mb-conn-status-time">— <?php esc_html_e( 'từ', 'whp' ); ?> <?php echo date('d/m/Y H:i', (int)$time); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($sp['connected']) : ?>
                    <span class="mb-conn-status-badge on">✓ <?php esc_html_e( 'Đã kết nối', 'whp' ); ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- Illustration right -->
        <div class="mb-conn-status-illus">
            <svg viewBox="0 0 400 140" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="st_fade" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f5f3ff" stop-opacity="0"/>
                        <stop offset="40%" stop-color="#ede9fe" stop-opacity="0.8"/>
                        <stop offset="100%" stop-color="#ddd6fe" stop-opacity="1"/>
                    </linearGradient>
                </defs>
                <rect width="400" height="140" fill="url(#st_fade)"/>
                <!-- Central hub -->
                <circle cx="180" cy="70" r="22" fill="#fff" stroke="#c4b5fd" stroke-width="1.5" opacity=".9"/>
                <circle cx="180" cy="70" r="14" fill="#ede9fe" stroke="#a78bfa" stroke-width="1.5"/>
                <circle cx="180" cy="70" r="6" fill="#7c3aed" opacity=".7"/>
                <!-- Node A (Anthropic) -->
                <circle cx="80" cy="35" r="16" fill="#fff" stroke="#c4b5fd" stroke-width="1.2" opacity=".85"/>
                <text x="80" y="40" text-anchor="middle" font-size="11" font-weight="700" fill="#D97757" opacity=".8">A</text>
                <!-- Node G (Google) -->
                <circle cx="70" cy="85" r="16" fill="#fff" stroke="#bfdbfe" stroke-width="1.2" opacity=".85"/>
                <text x="70" y="90" text-anchor="middle" font-size="11" font-weight="700" fill="#5684D1" opacity=".8">G</text>
                <!-- Node O (OpenAI) -->
                <circle cx="100" cy="118" r="16" fill="#fff" stroke="#c4b5fd" stroke-width="1.2" opacity=".85"/>
                <text x="100" y="123" text-anchor="middle" font-size="11" font-weight="700" fill="#475569" opacity=".7">O</text>
                <!-- Connection lines -->
                <line x1="96" y1="43" x2="160" y2="62" stroke="#a78bfa" stroke-width="1.2" stroke-dasharray="4 3" opacity=".6"/>
                <line x1="86" y1="85" x2="158" y2="72" stroke="#818cf8" stroke-width="1.2" stroke-dasharray="4 3" opacity=".5"/>
                <line x1="113" y1="111" x2="162" y2="80" stroke="#a78bfa" stroke-width="1.2" stroke-dasharray="4 3" opacity=".5"/>
                <!-- Right side floating elements -->
                <circle cx="290" cy="40" r="18" fill="#fff" stroke="#ddd6fe" stroke-width="1.2" opacity=".7"/>
                <path d="M282 40l4 4 8-8" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" opacity=".8"/>
                <circle cx="340" cy="90" r="14" fill="#fff" stroke="#ddd6fe" stroke-width="1.2" opacity=".6"/>
                <path d="M334 90l2.5 2.5 6-6" stroke="#3858e9" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" opacity=".7"/>
                <line x1="202" y1="66" x2="272" y2="44" stroke="#c4b5fd" stroke-width="1.2" stroke-dasharray="4 3" opacity=".5"/>
                <line x1="200" y1="74" x2="326" y2="87" stroke="#c4b5fd" stroke-width="1.2" stroke-dasharray="4 3" opacity=".4"/>
                <!-- Sparkle dots -->
                <circle cx="240" cy="110" r="3.5" fill="#a78bfa" opacity=".3"/>
                <circle cx="370" cy="45" r="4" fill="#818cf8" opacity=".25"/>
                <circle cx="310" cy="125" r="3" fill="#7c3aed" opacity=".2"/>
            </svg>
        </div>
    </div>

    <!-- CTA banner -->
    <?php $any_connected = $anthropic_connected || $google_connected || $openai_connected; ?>
    <div class="mb-conn-cta-banner">
        <div class="mb-conn-cta-banner-left">
            <div class="mb-conn-cta-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <div>
                <p class="mb-conn-cta-title"><?php echo $any_connected ? esc_html__( 'Kết nối thành công! Bắt đầu tạo bài viết ngay', 'whp' ) : esc_html__( 'Kết nối AI để mở khóa toàn bộ tính năng', 'whp' ); ?></p>
                <p class="mb-conn-cta-desc"><?php echo $any_connected ? esc_html__( 'AI đã sẵn sàng — thử tính năng AI Viết Bài để tự động soạn nội dung SEO.', 'whp' ) : esc_html__( 'Nhập API Key ở trên để sử dụng AI Viết Bài, AI Bảo Mật và AI Cố Vấn SEO.', 'whp' ); ?></p>
            </div>
        </div>
        <?php if ($any_connected) : ?>
        <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=writer'); ?>" class="mb-conn-cta-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <?php esc_html_e( 'AI Viết Bài →', 'whp' ); ?>
        </a>
        <?php else : ?>
        <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=dashboard'); ?>" class="mb-conn-cta-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            <?php esc_html_e( 'Xem tổng quan →', 'whp' ); ?>
        </a>
        <?php endif; ?>
    </div>

    </div><!-- /mb-conn-main -->

    <!-- Sidebar cột 2 -->
    <div class="mb-conn-sidebar">

        <!-- Hướng dẫn nhanh -->
        <div class="mb-conn-sidebar-card">
            <h4>
                <div class="mb-conn-sidebar-icon" style="background:#fef9c3;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="#f59e0b"><path d="M9 21h6M12 3a6 6 0 0 1 6 6c0 2.22-1.21 4.16-3 5.2V17H9v-2.8C7.21 13.16 6 11.22 6 9a6 6 0 0 1 6-6z"/></svg>
                </div>
                <?php esc_html_e( 'Hướng dẫn nhanh', 'whp' ); ?>
            </h4>
            <div class="mb-conn-steps">
                <div class="mb-conn-step">
                    <div class="mb-conn-step-num">1</div>
                    <div class="mb-conn-step-text">
                        <strong><?php esc_html_e( 'Lấy API Key', 'whp' ); ?></strong>
                        <span><?php esc_html_e( 'Đăng nhập tài khoản nhà cung cấp và tạo API Key mới.', 'whp' ); ?></span>
                    </div>
                </div>
                <div class="mb-conn-step">
                    <div class="mb-conn-step-num">2</div>
                    <div class="mb-conn-step-text">
                        <strong><?php esc_html_e( 'Nhập thông tin', 'whp' ); ?></strong>
                        <span><?php esc_html_e( 'Dán API Key vào ô tương ứng và nhấn "Kết nối".', 'whp' ); ?></span>
                    </div>
                </div>
                <div class="mb-conn-step">
                    <div class="mb-conn-step-num">3</div>
                    <div class="mb-conn-step-text">
                        <strong><?php esc_html_e( 'Kiểm tra và lưu', 'whp' ); ?></strong>
                        <span><?php esc_html_e( 'Nhấn "Kiểm tra" để xác nhận kết nối hoạt động tốt.', 'whp' ); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mẹo kết nối -->
        <div class="mb-conn-sidebar-card">
            <h4>
                <div class="mb-conn-sidebar-icon" style="background:#eff2fe;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                </div>
                <?php esc_html_e( 'Mẹo kết nối', 'whp' ); ?>
            </h4>
            <ul class="mb-conn-tips-list" style="padding-left:14px;margin:0;display:flex;flex-direction:column;gap:8px;">
                <li style="font-size:12.5px;color:#475569;line-height:1.5;"><?php esc_html_e( 'Đảm bảo đã đăng ký tài khoản và lấy API Key từ nhà cung cấp.', 'whp' ); ?></li>
                <li style="font-size:12.5px;color:#475569;line-height:1.5;"><?php esc_html_e( 'Một số nhà cung cấp yêu cầu xác minh thanh toán.', 'whp' ); ?></li>
                <li style="font-size:12.5px;color:#475569;line-height:1.5;"><?php esc_html_e( 'Kiểm tra kết nối sau khi nhập API Key để đảm bảo hoạt động.', 'whp' ); ?></li>
            </ul>
        </div>

        <!-- Nhà cung cấp phổ biến -->
        <div class="mb-conn-sidebar-card">
            <h4>
                <div class="mb-conn-sidebar-icon" style="background:#f0fdf4;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <?php esc_html_e( 'Nhà cung cấp phổ biến', 'whp' ); ?>
            </h4>
            <div class="mb-conn-provider-links">
                <a href="https://console.anthropic.com/keys" target="_blank" class="mb-conn-provider-link">
                    <div class="mb-conn-provider-link-left">
                        <svg viewBox="0 0 24 24" width="20" height="20" style="fill:#D97757;flex-shrink:0;"><path d="m4.7144 15.9555 4.7174-2.6471.079-.2307-.079-.1275h-.2307l-.7893-.0486-2.6956-.0729-2.3375-.0971-2.2646-.1214-.5707-.1215-.5343-.7042.0546-.3522.4797-.3218.686.0608 1.5179.1032 2.2767.1578 1.6514.0972 2.4468.255h.3886l.0546-.1579-.1336-.0971-.1032-.0972L6.973 9.8356l-2.55-1.6879-1.3356-.9714-.7225-.4918-.3643-.4614-.1578-1.0078.6557-.7225.8803.0607.2246.0607.8925.686 1.9064 1.4754 2.4893 1.8336.3643.3035.1457-.1032.0182-.0728-.164-.2733-1.3539-2.4467-1.445-2.4893-.6435-1.032-.17-.6194c-.0607-.255-.1032-.4674-.1032-.7285L6.287.1335 6.6997 0l.9957.1336.419.3642.6192 1.4147 1.0018 2.2282 1.5543 3.0296.4553.8985.2429.8318.091.255h.1579v-.1457l.1275-1.706.2368-2.0947.2307-2.6957.0789-.7589.3764-.9107.7468-.4918.5828.2793.4797.686-.0668.4433-.2853 1.8517-.5586 2.9021-.3643 1.9429h.2125l.2429-.2429.9835-1.3053 1.6514-2.0643.7286-.8196.85-.9046.5464-.4311h1.0321l.759 1.1293-.34 1.1657-1.0625 1.3478-.8804 1.1414-1.2628 1.7-.7893 1.36.0729.1093.1882-.0183 2.8535-.607 1.5421-.2794 1.8396-.3157.8318.3886.091.3946-.3278.8075-1.967.4857-2.3072.4614-3.4364.8136-.0425.0304.0486.0607 1.5482.1457.6618.0364h1.621l3.0175.2247.7892.522.4736.6376-.079.4857-1.2142.6193-1.6393-.3886-3.825-.9107-1.3113-.3279h-.1822v.1093l1.0929 1.0686 2.0035 1.8092 2.5075 2.3314.1275.5768-.3218.4554-.34-.0486-2.2039-1.6575-.85-.7468-1.9246-1.621h-.1275v.17l.4432.6496 2.3436 3.5214.1214 1.0807-.17.3521-.6071.2125-.6679-.1214-1.3721-1.9246L14.38 17.959l-1.1414-1.9428-.1397.079-.674 7.2552-.3156.3703-.7286.2793-.6071-.4614-.3218-.7468.3218-1.4753.3886-1.9246.3157-1.53.2853-1.9004.17-.6314-.0121-.0425-.1397.0182-1.4328 1.9672-2.1796 2.9446-1.7243 1.8456-.4128.164-.7164-.3704.0667-.6618.4008-.5889 2.386-3.0357 1.4389-1.882.929-1.0868-.0062-.1579h-.0546l-6.3385 4.1164-1.1293.1457-.4857-.4554.0608-.7467.2307-.2429 1.9064-1.3114Z"/></svg>
                        <span class="mb-conn-provider-link-name">Anthropic</span>
                    </div>
                    <span class="mb-conn-provider-link-info">console.anthropic.com</span>
                </a>
                <a href="https://aistudio.google.com/app/apikey" target="_blank" class="mb-conn-provider-link">
                    <div class="mb-conn-provider-link-left">
                        <svg viewBox="0 0 28 28" width="20" height="20"><path d="M14 28C14 26.0633 13.6267 24.2433 12.88 22.54C12.1567 20.8367 11.165 19.355 9.905 18.095C8.645 16.835 7.16333 15.8433 5.46 15.12C3.75667 14.3733 1.93667 14 0 14C1.93667 14 3.75667 13.6383 5.46 12.915C7.16333 12.1683 8.645 11.165 9.905 9.905C11.165 8.645 12.1567 7.16333 12.88 5.46C13.6267 3.75667 14 1.93667 14 0C14 1.93667 14.3617 3.75667 15.085 5.46C15.8317 7.16333 16.835 8.645 18.095 9.905C19.355 11.165 20.8367 12.1683 22.54 12.915C24.2433 13.6383 26.0633 14 28 14C26.0633 14 24.2433 14.3733 22.54 15.12C20.8367 15.8433 19.355 16.835 18.095 18.095C16.835 19.355 15.8317 20.8367 15.085 22.54C14.3617 24.2433 14 26.0633 14 28Z" fill="url(#geminiGradSB)"/><defs><radialGradient id="geminiGradSB" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(2.77876 11.3795) rotate(18.6832) scale(29.8025 238.737)"><stop offset="0.0671246" stop-color="#9168C0"/><stop offset="0.342551" stop-color="#5684D1"/><stop offset="0.672076" stop-color="#1BA1E3"/></radialGradient></defs></svg>
                        <span class="mb-conn-provider-link-name">Google Gemini</span>
                    </div>
                    <span class="mb-conn-provider-link-info">aistudio.google.com</span>
                </a>
                <a href="https://platform.openai.com/api-keys" target="_blank" class="mb-conn-provider-link">
                    <div class="mb-conn-provider-link-left">
                        <svg viewBox="0 0 24 24" width="20" height="20" style="fill:#000;"><path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z"/></svg>
                        <span class="mb-conn-provider-link-name">OpenAI</span>
                    </div>
                    <span class="mb-conn-provider-link-info">platform.openai.com</span>
                </a>
            </div>
        </div>

    </div><!-- /mb-conn-sidebar -->
    </div><!-- /mb-conn-layout -->

</div><!-- /.mb-wph-page -->

    <?php
}
