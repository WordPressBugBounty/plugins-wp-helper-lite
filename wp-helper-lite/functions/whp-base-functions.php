<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
function whp_get_list_tab()
{
    return [
        [
            'slug' => 'mb-wphelper-contact',
            'title' => __('Kênh liên hệ', 'whp'),
            'callback' => 'whp_contactChanel',
            'desc' => __('Tính năng này cho phép cài đặt các popup trên trang web của bạn để khách hàng có thể tương tác trực tiếp hỏi về sản phẩm.', 'whp'),
        ],
        [
            'slug' => 'mb-wphelper-code',
            'title' => __('Header & Footer', 'whp'),
            'callback' => 'whp_code',
            'desc' => __('Dễ dàng chèn các đoạn mã theo dõi, xác minh và tích hợp dịch vụ vào Header hoặc Footer website WordPress như Google Analytics, Google Search Console, Facebook Pixel, Google Tag Manager... mà không cần can thiệp vào mã nguồn.', 'whp'),
        ],
        [
            'slug' => 'mb-wphelper-pop-up',
            'title' => __('Pop-up', 'whp'),
            'callback' => 'whp_popup',
            'desc' => __('Tính năng này sẽ hiện thị quảng cáo khi người dùng vừa truy cập.', 'whp'),
        ],
        [
            'slug' => 'mb-wphelper-smtp',
            'title' => __('Email & Liên hệ', 'whp'),
            'callback' => 'whp_smtpSetting',
            'desc' => __('<p>Các website đều có phần gửi mail khi có người liên hệ hoặc khi có đơn hàng mới, nhưng <strong>không phải Hosting</strong> nào cũng <strong>cho phép người dùng gửi</strong> mail thông qua hàm <strong>PHP</strong> hay gửi <strong>SMTP mail theo tên miền.</strong></p>
            <p>Tính năng <strong>SMTP mail</strong> cho phép người dùng có thể <strong>cấu hình việc gửi mail</strong> dễ dàng, nhanh chóng và không tốn chi phí.</p>
            <a href="https://wiki.matbao.net/kb/thong-tin-smtp-gmail-cach-cau-hinh-smtp-gmail-free-vao-wordpress/" target="_blank">Xem hướng dẫn cài đặt</a>', 'whp')
        ],
        [
            'slug' => 'mb-wphelper-woocommerce-advance',
            'title' => __('Cửa hàng nâng cao', 'whp'),
            'callback' => 'whp_woocommerce_advance',
            'desc' => __('Chức năng nâng cao, giúp tối ưu cho cửa hàng của bạn', 'whp'),
        ],
        [
            'slug' => 'mb-wphelper-ai',
            'title' => __('AI Hub', 'whp'),
            'callback' => 'whp_ai_auto_poster',
            'desc' => __('Tính năng hỗ trợ soạn thảo nội dung, quét bảo mật, tối ưu SEO bằng trí tuệ nhân tạo.', 'whp'),
        ],
        [
            'slug' => 'mb-wphelper-extention',
            'title' => __('Bảo vệ & Tối ưu', 'whp'),
            'callback' => 'whp_security_extention',
            'desc' => __('Quản lý các công cụ bảo mật, tối ưu hiệu suất và tùy chỉnh quản trị cho website của bạn.', 'whp'),
        ],
    ];
}

function whp_get_woo_tab()
{
    return [
        [
            'slug' => 'mb-wphelper-woocommerce-advance',
            'title' => __('Cửa hàng nâng cao', 'whp'),
            'callback' => 'whp_woocommerce_advance',
            'desc' => __('Chức năng nâng cao, giúp tối ưu cho cửa hàng của bạn', 'whp'),
        ],
        [
            'slug' => 'mb-wphelper-woocommerce-wallet',
            'title' => __('Thanh toán bằng ví điện tử', 'whp'),
            'callback' => 'whp_woocommerce_wallet',
            'desc' => __('<p>Tính năng cho phép bạn <strong>cài đặt thêm</strong> phần <strong>hình thức thanh toán</strong> trên trang web bằng các ví điện tử (Momo, ZaloPay, VNPay, ShopeePay) một cách đơn giản và nhanh chóng.</p>', 'whp'),
        ],
        [
            'slug' => 'mb-wphelper-woocommerce-payment',
            'title' => __('Mẫu thông tin thanh toán', 'whp'),
            'callback' => 'whp_woocommerce_payment',
            'desc' => __('Điều chỉnh mẫu thông tin để việc quản lý CRM hiệu quả hơn.', 'whp'),
        ],
        [
            'slug' => 'mb-wphelper-woocommerce-ecommerce',
            'title' => __('Liên kết với sàn TMDT', 'whp'),
            'callback' => 'whp_woocommerce_ecommerce',
            'desc' => __('<p>Tính năng này cho phép bạn tạo đường dẫn cho sản phẩm đã được đăng ở các sàn thương mại điện tử: Shopee, Lazada, Tiki, Sendo.</p>', 'whp'),
        ],
        [
            'slug' => 'mb-wphelper-woocommerce-cta',
            'title' => __('Nút mua hàng (CTA)', 'whp'),
            'callback' => 'whp_woocommerce_cta',
            'desc' => __('<p>Bằng việc thay đổi nội dung và cài đặt khác của nút mua hàng sẽ thu hút khách hàng tốt hơn dẫn đến việc bán hàng của bạn sẽ hiệu quả hơn.</p>', 'whp'),
        ],
    ];
}
function whp_get_shared($name)
{
    $result = null;
    $result = MB_WHP_PATH_VIEW . "admin/shared/{$name}.php";
    if (file_exists($result)) {
        include($result);
    }
}
function whp_get_current_tab()
{
    $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : 'mb-wphelper-code';
    $tabs = whp_get_list_tab();
    $tabs = array_filter($tabs, function ($item) use ($page) {
        if ($item['slug'] == $page) {
            return $item;
        }
    });
    $result = $tabs ? array_shift($tabs) : [];

    return $result;
}
function whp_check_option()
{
    $optionOld = get_option('mbwp_helper', []);
    $optionNew = get_option('whp_setting', []);
    // Fresh install: cả 2 đều empty → dùng "new" (tránh load old path trả về integer 0)
    if ( ! empty( $optionNew ) ) return 'new';
    if ( ! empty( $optionOld ) ) return 'old';
    return 'new';
}

function whp_get_option($key = '')
{
    $option = get_option('whp_setting', []);

    $result = null;
    if ($key) {
        $result = isset($option[$key]) ? $option[$key] : null;
    }
    return $result;
}
function whp_get_option_old($key = '')
{
    $option = get_option('mbwp_helper', []);
    $result = null;
    if ($key) {
        $result = isset($option[$key]) ? $option[$key] : null;
    }
    return $result;
}
function whp_get_contact_option_old($type = "design", $key = "")
{
    $option  = get_option('mbwp_helper', []);

    $setting = $option['opt-accordion-contact'] ?? [];
    $design = $setting['mbwp-contact-design'] ?? [];
    $phone = $setting['mbwp-contact-phone'] ?? [];
    $contact = $setting['mbwp-general-contact'] ?? [];
    if ($type == 'design') {
        $result = $design[$key] ?? "";
    } elseif ($type == 'phone') {
        $result = $phone[$key] ?? "";
    } else {
        $result = $contact[$key] ?? "";
    }
    return $result;
}

function whp_get_contact_option($type = "", $key = "")
{
    $data = whp_get_option();
    $settingContactOld = $data['opt-accordion-contact'] ?? [];
    $settingContactNew = $data['contact'] ?? [];
    // ['design' => [ 'color','greeting', 'position-y','position-x' ], 'phone' => ['title','data'  ], 'other' => ['title','email','facebook','zalo','page']]
    $setting = $data['opt-accordion-contact'] ?? [];
    // design,phone,contact
    $design = $setting['mbwp-contact-design'] ?? [];
    $phone = $setting['mbwp-contact-phone'] ?? [];
    $contact = $setting['mbwp-general-contact'] ?? [];
    if ($type == 'design') {
        $result = $design[$key] ?? "";
    } elseif ($type == 'phone') {
        $result = $phone[$key] ?? "";
    } else {
        $result = $contact[$key] ?? "";
    }
    return $result;
}
function sanitize_data($data)
{
    $params = [];
    $keyCodes = ['whp_code_header', 'whp_code_body', 'whp_code_footer'];
    $key_code_editor = ['whp_popup_mail_template'];
    // Channel fields requiring specific format validation
    $url_fields   = ['whp_contact_other_facebook','whp_contact_other_facebook_page',
                     'whp_woo_thankyou_contact_fb_val','whp_woo_thankyou_contact_msg_val'];
    $email_fields = ['whp_contact_other_email','whp_woo_thankyou_contact_email_val'];
    $phone_fields = ['whp_contact_other_zalo','whp_woo_thankyou_contact_zalo_val'];
    foreach ($data as $key => $item) {
        if (is_array($item)) {
            $params[$key] = sanitize_array($item);
        } elseif (in_array($key, $keyCodes)) {
            $params[$key] = $item;
        } elseif (in_array($key, $key_code_editor)) {
            $params[$key] = $item;
        } else {
            $val = sanitize_text_field($item);
            if ($val !== '') {
                if (in_array($key, $url_fields) && !preg_match('#^https?://.{3,}#', $val)) {
                    $val = '';
                } elseif (in_array($key, $email_fields) && !is_email($val)) {
                    $val = '';
                } elseif (in_array($key, $phone_fields) && !preg_match('/^(\+84|0)\d{8,10}$/', $val)) {
                    $val = '';
                }
            }
            $params[$key] = $val;
        }
    }
    return $params;
}
function sanitize_array($array)
{
    $result = [];
    foreach ($array as $key => $item) {
        $item['avatar'] = isset($item['avatar']) ? sanitize_text_field($item['avatar']) : "";
        $item['title'] = isset($item['title']) ? sanitize_text_field($item['title']) : "";
        $item['phone'] = isset($item['phone']) ? sanitize_text_field($item['phone']) : "";
        $result[$key] = $item;
    }
    return $result;
}
function whp_show_html($data)
{
    $allowed_html = [
        'a' => [
            'id' => true,
            'href'  => true,
            'title' => true,
        ],
        'strong' => [],
        'br' => [],
        'p' => [],
        'span' => [
            'class' => true,
        ],
        'head'  => array(),
        'link'  => array(
            'as'             => true,
            'disabled'       => true,
            'href'           => true,
            'hreflang'       => true,
            'importance'     => true,
            'integrity'      => true,
            'media'          => true,
            'referrerpolicy' => true,
            'rel'            => true,
            'sizes'          => true,
            'title'          => true,
            'type'           => true,
        ),
        'style' => array(
            'type'  => true,
            'media' => true,
            'nonce' => true,
            'title' => true,
        ),
        'script'   => array(
            'async'          => true,
            'crossorigin'    => true,
            'defer'          => true,
            'integrity'      => true,
            'language'       => true,
            'nomodule'       => true,
            'referrerPolicy' => true,
            'src'            => true,
            'text'           => true,
            'type'           => true,
            'type.module'    => true,
        ),
        'meta'  => array(
            'content' => true,
            'name' => true,
        )
    ];
    $result = wp_kses($data, $allowed_html);
    return $result;
}
function whp_get_list_position()
{
    $result = [
        'left' => [
            'name' => 'Trái',
            'value' => 'mbwp-ct-left',
        ],
        'right' => [
            'name' => 'Phải',
            'value' => 'mbwp-ct-right',
        ],
    ];
    return $result;
}
function whp_get_list_smtp()
{
    $result = [
        'gmail' => [
            'name' => 'SMTP Gmail',
            'value' => '1',
        ],
        'yandex' => [
            'name' => 'SMTP Yandex',
            'value' => '2',
        ],
        'other' => [
            'name' => 'SMTP Khác',
            'value' => '3',
        ],
    ];
    return $result;
}
function whp_get_list_smtp_security()
{
    $result = [
        'none' => [
            'name' => 'None',
            'value' => 'none',
            'port' => '25',
        ],
        'ssl' => [
            'name' => 'SSL',
            'value' => 'ssl',
            'port' => '465',
        ],
        'tls' => [
            'name' => 'TLS',
            'value' => 'tls',
            'port' => '587',
        ],
    ];
    return $result;
}
function whp_get_list_layout_maintenance()
{
    $result = [
        'full-width' => [
            'name' => 'Center',
            'value' => 'center',

        ],
        'content-right' => [
            'name' => 'Content right',
            'value' => 'content-right',

        ],
        'content-left' => [
            'name' => 'Content left',
            'value' => 'content-left',

        ],
    ];
    return $result;
}
function whp_get_popup_type_field()
{
    $result = [
        'newsletter' => ['name' => 'Newsletter', 'value' => '0'],
        'banner' => ['name' => 'Banner', 'value' => '1'],
    ];
    return $result;
}
function whp_get_list_editor()
{
    $result = [
        'gutenberg' => [
            'name' => 'Gutenberg Editor',
            'value' => '0',
        ],
        'classic' => [
            'name' => 'Classic Editor',
            'value' => '1',
        ],
    ];
    return $result;
}
function whp_get_list_ecommerce()
{
    $result = [
        'tiki' => [
            'url' =>  MB_WHP_URL . "/assets/admin/images/Tiki.svg",
        ],
        'shopee' => [
            'url' =>  MB_WHP_URL . "/assets/admin/images/Shopee.svg",
        ],
        'lazada' => [
            'url' =>  MB_WHP_URL . "/assets/admin/images/Lazada.svg",
        ],
        'sendo' => [
            'url' =>  MB_WHP_URL . "/assets/admin/images/Sendo.svg",
        ],
    ];
    return $result;
}

function whp_get_list_wallet()
{
    $result = [
        'momo' => [
            'title' => 'MoMo',
            'url' =>  MB_WHP_URL . "/assets/admin/images/logo-momo.svg",
            'desc' => __('Thanh toán nhanh chóng bằng ví điện tử MoMo, giao dịch an toàn và bảo mật.', 'whp'),
        ],
        'zalopay' => [
            'title' => 'ZaloPay',
            'url' =>  MB_WHP_URL . "/assets/admin/images/zalopay.svg",
            'desc' => __('Quét mã QR hoặc thanh toán trực tiếp qua ứng dụng ZaloPay.', 'whp'),
        ],
        'vnpay' => [
            'title' => 'VNPay',
            'url' =>  MB_WHP_URL . "/assets/admin/images/vnpay.svg",
            'desc' => __('Thanh toán qua QR Code hoặc thẻ ATM, Internet Banking của các ngân hàng hỗ trợ.', 'whp'),
        ],
        'shopeepay' => [
            'title' => 'ShopeePay',
            'url' =>  MB_WHP_URL . "/assets/admin/images/shopeepay.svg",
            'desc' => __('Thanh toán tiện lợi bằng ví ShopeePay với nhiều ưu đãi hấp dẫn.', 'whp'),
        ],
    ];
    return $result;
}

function whp_get_contact_fields()
{
    $result = [
        'whp_contact_active',
        'whp_contact_design_color',
        'whp_contact_design_greeting',
        'whp_contact_design_position_y',
        'whp_contact_design_position_x',
        'whp_contact_phone_title',
        'whp_contact_phone_data',
        'whp_contact_other_title',
        'whp_contact_other_email',
        'whp_contact_other_facebook',
        'whp_contact_other_zalo',
        'whp_contact_other_facebook_page',
        'whp_contact_online_status_text',
        'whp_contact_phone_btn_text',
        'whp_contact_other_zalo_active',
        'whp_contact_other_facebook_active',
        'whp_contact_other_messenger_active',
        'whp_contact_other_email_active',
        'whp_contact_bottom_distance',
        'whp_contact_popup_effect',
        'whp_contact_display_desktop',
        'whp_contact_display_mobile',
        'whp_contact_phone_cta_note',
        'whp_contact_phone_cta_note_offline',
    ];
    return $result;
}
function whp_get_smtp_fields()
{
    $result = [
        'whp_smtp_active',
        'whp_smtp_setting',
        'whp_smtp_email',
        'whp_smtp_from_name',
        'whp_smtp_host',
        'whp_smtp_security',
        'whp_smtp_port',
        'whp_smtp_user',
        'whp_smtp_password',
        'whp_smtp_email_receive',
        'whp_smtp_auth'
    ];
    return $result;
}
function whp_get_security_fields()
{
    $result = [
        'whp_security_remove_xmlrpc',
        'whp_security_disable_copy',
        'whp_security_delete_wphead',
        'whp_security_hide_wp_version',
        'whp_security_hide_theme_plugin',
        'whp_security_change_login_url',
        'whp_new_login_url',
    ];
    return $result;
}
function whp_get_extention_fields()
{
    $result = [
        'whp_extention_editor_type',
        'whp_extention_duplicate_page_post',
        'whp_extention_notification',
        'whp_extention_duplicate_menu',
        'whp_extention_enable_404_redirect',
        'whp_extention_disable_emojis',
        'whp_extention_remove_query_string',
        'whp_extention_disbale_wp_embeds',
        'whp_extention_disbale_google_fonts',
        'whp_extention_disable_heartbeat_frontend',
        'whp_extention_heartbeat_limit_admin',
        'whp_extention_disbale_dashicons',
        'whp_extention_custom_login_theme',
        'whp_extention_custom_login_logo',
        'whp_extention_custom_link',
        'whp_extention_svg'
    ];
    return $result;
}
function whp_get_woo_cta_fields()
{
    $result = [
        'whp_woocommerce_cta_text',
        'whp_woocommerce_cta_convert_zero_to_contact',
        'whp_woocommerce_cta_show_buynow_button',
    ];
    return $result;
}
function whp_get_woo_ecommerce_fields()
{
    $result = [
        'whp_woocommerce_ecommerce_tiki',
        'whp_woocommerce_ecommerce_shopee',
        'whp_woocommerce_ecommerce_lazada',
        'whp_woocommerce_ecommerce_sendo',
    ];
    return $result;
}
function whp_get_woo_payment_fields()
{
    $result = [
        'whp_woocommerce_payment_fullname',
        'whp_woocommerce_payment_company',
        'whp_woocommerce_payment_zipcode',
        'whp_woocommerce_payment_province',
        'whp_woocommerce_payment_country',
        'whp_woocommerce_payment_address',
        'whp_woocommerce_payment_state'
    ];
    return $result;
}
function whp_get_woo_wallet_fields()
{
    $result = [
        'whp_woocommerce_wallet_momo',
        'whp_woocommerce_wallet_zalopay',
        'whp_woocommerce_wallet_vnpay',
        'whp_woocommerce_wallet_shopeepay',
    ];
    return $result;
}
function whp_get_woo_advance_fields()
{
    $result = [
        'whp_woocommerce_advance_enable_notice',
        'whp_woocommerce_advance_enable_vat',
        'whp_woocommerce_advance_enable_compact_desc',
        'whp_extention_filter_order_by_phone',
    ];
    return $result;
}
function whp_get_woo_thankyou_fields()
{
    return [
        'whp_woo_thankyou_enable',
        'whp_woo_thankyou_layout',
        'whp_woo_thankyou_color',
        'whp_woo_thankyou_color_custom',
        'whp_woo_thankyou_show_timeline',
        'whp_woo_thankyou_show_qr_large',
        'whp_woo_thankyou_copy_account',
        'whp_woo_thankyou_copy_content',
        'whp_woo_thankyou_show_support_btn',
        'whp_woo_thankyou_countdown_enable',
        'whp_woo_thankyou_countdown_minutes',
        'whp_woo_thankyou_btn_continue',
        'whp_woo_thankyou_btn_contact',
        'whp_woo_thankyou_btn_view_order',
        'whp_woo_thankyou_btn_invoice',
        'whp_woo_thankyou_trust_badges',
        'whp_woo_thankyou_transfer_btn',
        'whp_woo_thankyou_transfer_email',
        'whp_woo_thankyou_font',
        'whp_woo_thankyou_radius',
        'whp_woo_thankyou_shadow',
        'whp_woo_thankyou_spacing',
        'whp_woo_thankyou_color2',
        'whp_woo_thankyou_bg',
        'whp_woo_thankyou_contact_zalo_en',
        'whp_woo_thankyou_contact_zalo_val',
        'whp_woo_thankyou_contact_fb_en',
        'whp_woo_thankyou_contact_fb_val',
        'whp_woo_thankyou_contact_msg_en',
        'whp_woo_thankyou_contact_msg_val',
        'whp_woo_thankyou_contact_email_en',
        'whp_woo_thankyou_contact_email_val',
    ];
}
function whp_get_aipay_fields()
{
    return [
        // General config
        'whp_aipay_enable',
        'whp_aipay_ocr_enable',
        'whp_aipay_fraud_enable',
        'whp_aipay_copilot_enable',
        'whp_aipay_openai_key',
        'whp_aipay_gemini_key',
        // Email notifications
        'whp_aipay_email_address',
        'whp_aipay_email_new_order',
        'whp_aipay_email_transfer',
        'whp_aipay_email_risk',
        'whp_aipay_email_success',
        // Telegram
        'whp_aipay_telegram_token',
        'whp_aipay_telegram_chat_id',
        'whp_aipay_telegram_new_order',
        'whp_aipay_telegram_transfer',
        'whp_aipay_telegram_risk',
        'whp_aipay_telegram_success',
        // Discord
        'whp_aipay_discord_webhook',
        'whp_aipay_discord_new_order',
        'whp_aipay_discord_transfer',
        'whp_aipay_discord_risk',
        // Custom webhook
        'whp_aipay_webhook_url',
        'whp_aipay_webhook_method',
    ];
}

function whp_get_maintenance_fields()
{
    $result = [
        'whp_maintenance_banner',
        'whp_maintenance_title',
        'whp_maintenance_heading',
        'whp_maintenance_heading_sub',
        'whp_maintenance_active',
        'whp_maintenance_desc',
        'whp_maintenance_layout',
        'whp_maintenance_footer',
    ];
    return $result;
}

function whp_get_popup_fields()
{
    $result = [
        'whp_popup_active',
        'whp_popup_delay',
        'whp_popup_type',
        'whp_popup_form_source',
        'whp_popup_form_id',
        'whp_popup_title',
        'whp_popup_sub_title',
        'whp_popup_mail_template',
        'whp_popup_name',
        'whp_popup_email',
        'whp_popup_button',
        'whp_popup_facebook',
        'whp_popup_youtube',
        'whp_popup_instagram',
        'whp_popup_tiktok',
        'whp_popup_image_banner',
        'whp_popup_link_redirect'
    ];
    return $result;
}

function whp_get_filter_sidebar_fields()
{
    $result = [
        'whp_filter_sidebar_active',
        'whp_filter_sidebar_price',
        'whp_filter_sidebar_category_product',
        'whp_filter_sidebar_product_attribute'
    ];
    return $result;
}


function whp_get_responsive_fields()
{
    $result = [
        'whp_reponsive_mobile',
        'whp_reponsive_tablet',
        'whp_reponsive_desktop',
    ];

    return $result;
}

function whp_get_all_field()
{
    $params = [];
    $fields = [];
    $optionOld  = get_option('mbwp_helper', []);
    $MB_WHP_Data_Old = new MB_WHP_Data_Old();
    $fields = whp_get_contact_fields();
    foreach ($fields as $field) {
        $params[$field] = $MB_WHP_Data_Old->contact($field);
    }
    #_Header_footer
    $params['whp_code_header'] = "";
    $params['whp_code_body'] = "";
    $params['whp_code_footer'] = "";
    #_SMTP
    $fields = whp_get_smtp_fields();
    foreach ($fields as $field) {
        $params[$field] = $MB_WHP_Data_Old->smtp($field);
    }
    #_security
    $fields = whp_get_security_fields();
    foreach ($fields as $field) {
        $params[$field] = $MB_WHP_Data_Old->security($field);
    }
    #_extention
    $fields = whp_get_extention_fields();
    foreach ($fields as $field) {
        $params[$field] = $MB_WHP_Data_Old->extention($field);
    }
    #_woo_cta
    $fields = whp_get_woo_cta_fields();
    foreach ($fields as $field) {
        $params[$field] = $MB_WHP_Data_Old->woo_cta($field);
    }
    #_woo_eccommerce
    $fields = whp_get_woo_ecommerce_fields();
    foreach ($fields as $field) {
        $params[$field] = $MB_WHP_Data_Old->woo_ecommerce($field);
    }
    #_woo_payment
    $fields = whp_get_woo_payment_fields();
    foreach ($fields as $field) {
        $params[$field] = $MB_WHP_Data_Old->woo_payment($field);
    }
    #_woo_wallet
    $fields = whp_get_woo_wallet_fields();
    foreach ($fields as $field) {
        $params[$field] = $MB_WHP_Data_Old->woo_wallet($field);
    }
    #_woo_advance
    $fields = whp_get_woo_advance_fields();
    foreach ($fields as $field) {
        $params[$field] = $MB_WHP_Data_Old->woo_advance($field);
    }
    #_woo_thankyou
    foreach (whp_get_woo_thankyou_fields() as $field) {
        $params[$field] = '';
    }
    #_aipay
    foreach (whp_get_aipay_fields() as $field) {
        $params[$field] = '';
    }
    return $params;
}
function whp_get_icon($name)
{
    $result = null;
    $result = MB_WHP_PATH_VIEW . "frontend/icons/{$name}.php";
    if (file_exists($result)) {
        include($result);
    }
}
function whp_get_image_url($name)
{
    $result = null;
    $result = MB_WHP_URL . "assets/frontend/images/{$name}";
    return $result;
}
function whp_get_valid_login_logo($url = '')
{
    $default = MB_WHP_URL . 'assets/admin/images/icon.svg';
    $url = trim((string) $url);

    if ('' === $url || $url === $default) {
        return $default;
    }

    // Chỉ đối chiếu file thật với đường dẫn nội bộ (uploads/plugin assets),
    // bỏ qua URL ngoài vì không muốn gọi HTTP request để kiểm tra.
    $path = null;
    $upload_dir = wp_upload_dir();
    if (!empty($upload_dir['baseurl']) && 0 === strpos($url, $upload_dir['baseurl'])) {
        $path = $upload_dir['basedir'] . substr($url, strlen($upload_dir['baseurl']));
    } elseif (0 === strpos($url, content_url())) {
        $path = WP_CONTENT_DIR . substr($url, strlen(content_url()));
    }

    if (null !== $path && !file_exists($path)) {
        return $default;
    }

    return $url;
}
function whp_get_setting($key)
{
    $fields = whp_get_all_field();
    $result = null;
    $fieldOld = isset($fields[$key]) ? $fields[$key] : "";
    $fieldNew = whp_get_option($key);
    $result = $fieldNew != ""  ? $fieldNew : $fieldOld;
    return $result;
}
function whp_save_aipay_settings($posted_fields = [])
{
    if (empty($posted_fields)) {
        $posted_fields = whp_get_aipay_fields();
    }
    $option = get_option('whp_setting', []);
    foreach ($posted_fields as $field) {
        if (isset($_POST[$field])) {
            $option[$field] = sanitize_text_field($_POST[$field]);
        } else {
            // Unchecked checkboxes are absent from POST — set to empty
            $option[$field] = '';
        }
    }
    update_option('whp_setting', $option);
}

function whp_format_currency_vnd($number, $suffix = '')
{
    if (!empty($number)) {
        return number_format($number, 0, ',', '.') . "{$suffix}";
    }
}
