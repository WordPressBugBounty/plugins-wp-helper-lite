<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

if (!class_exists('MB_WHP_Frontend_Setup_Function')) {
    class MB_WHP_Frontend_Setup_Function
    {
        private $pathView = MB_WHP_PATH_VIEW . 'frontend/pages/';
        private $pathViewAdmin = MB_WHP_PATH_VIEW . 'admin/pages/';
        private $pathViewElement = MB_WHP_PATH_VIEW . 'frontend/elements/';
        private $pathViewPages = MB_WHP_PATH_VIEW . 'frontend/pages/';
        private $pathViewLayout = MB_WHP_PATH_VIEW . 'layout/';
        private $pathAsset = MB_WHP_URL . 'assets/frontend/';
        private $pathSidebar = MB_WHP_PATH_SIDEBAR;
        function __construct()
        {

            add_action('template_redirect', [$this, 'whp_popup_preview'], 1);
            add_action('wp_enqueue_scripts', [$this, 'include_style']);
            add_action('wp_enqueue_scripts', [$this, 'include_script']);
            $this->include_header();
            $this->include_body();
            $this->include_footer();
            $this->whp_smtp();
            $this->whp_security();
            $this->whp_extention();
            if ( class_exists( 'WooCommerce' ) ) {
                $this->whp_woo_cta();
                $this->whp_ecommerce();
                $this->whp_advance();
                $this->whp_woo_admin_ecommerce();
                $this->whp_checkout();
                $this->whp_gateway_wallet();
                $this->whp_woo_thankyou();
            }
            // $this->whp_maintenance();
            $this->whp_popup();
            $this->whp_reponsive();

            add_action('wp_ajax_whp_smtp_send_mail_test', [$this, 'whp_smtp_send_mail_test']);
            //  add_filter('template_include', [$this, 'whp_wallet_thanhyou_page'], 10, 2);
            //  add_action('get_header', [$this, 'whp_maintenance']);
            add_action('init', [$this, 'whp_maintenance'], 999);
        }
        public function whp_wallet_thanhyou_page($template)
        {
            global $wp;
            if (!empty($wp->query_vars['order-received'])) {

                $new_template =    $this->pathViewPages . 'thank_you.php';
                if (file_exists($new_template)) {

                    return $new_template;
                }
            }
            return $template;
        }
        public function whp_smtp_send_mail_test()
        {
            // Kiểm tra quyền hạn
            if (!current_user_can('manage_options')) {
                echo json_encode(['status' => 403, 'message' => 'Unauthorized']);
                exit();
            }

            // Kiểm tra xác thực nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'whp_smtp_send_mail_test_nonce')) {
                echo json_encode(['status' => 400, 'message' => 'Invalid nonce']);
                exit();
            }

            $to      = sanitize_email($_POST['email']);
            $message = wp_kses_post($_POST['content']);
            $subject = 'WP Helper - Cấu hình SMTP thành công';
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            $mail    = wp_mail($to, $subject, $message, $headers);

            if ($mail) {
                echo json_encode(['status' => 200, 'message' => 'Email sent successfully']);
            } else {
                echo json_encode(['status' => 500, 'message' => 'Failed to send email']);
            }
            exit();
        }
        public function include_style()
        {
            wp_enqueue_style('whp-frontend-app', $this->pathAsset . 'css/app.css', array(), time(), 'all');
            wp_enqueue_style('whp-popup', $this->pathAsset . 'css/popup.css', array(), time(), 'all');
            $whp_contact_design_position_y = whp_get_setting('whp_contact_design_position_y');
            // Giá trị lưu là % (0-100). Nếu chưa set dùng mặc định 5% (≈ cạnh dưới)
            $pos_y = (is_numeric($whp_contact_design_position_y) && $whp_contact_design_position_y !== '')
                ? intval($whp_contact_design_position_y)
                : 5;
            $whp_contact_design_color      = whp_get_setting('whp_contact_design_color') ?: '#00c217';
            $whp_contact_design_position_x = whp_get_setting('whp_contact_design_position_x') ?: 'left';
            $is_right = in_array($whp_contact_design_position_x, ['right', 'mbwp-ct-right']);
            $pos_css  = $is_right
                ? 'right:20px;left:auto;'
                : 'left:20px;right:auto;';
            
            $bottom_dist = whp_get_setting('whp_contact_bottom_distance');
            $bottom_css  = ($bottom_dist !== '' && is_numeric($bottom_dist)) ? intval($bottom_dist) . 'px' : $pos_y . '%';
            
            $custom_css = '#mb-whp-contact{bottom:' . $bottom_css . ';' . $pos_css . '}'
                        . '.whp-contact-icon,.whp-contact-icon:before,.whp-contact-icon:after,.whp-contact-content-head,.whp-v2-header,.whp-v2-call-btn{background:' . esc_attr($whp_contact_design_color) . '}'
                        . '.whp-v2-header-icon,.whp-v2-call-icon{color:' . esc_attr($whp_contact_design_color) . '}'
                        . '.whp-v2-call-btn{padding:15px 14px !important; margin-top: 15px !important; margin-bottom: 15px !important;}';
            
            $display_desktop = whp_get_setting('whp_contact_display_desktop');
            if ($display_desktop === '0') {
                $custom_css .= '@media (min-width: 768px) { #mb-whp-contact { display: none !important; } }';
            }
            $display_mobile = whp_get_setting('whp_contact_display_mobile');
            if ($display_mobile === '0') {
                $custom_css .= '@media (max-width: 767px) { #mb-whp-contact { display: none !important; } }';
            }
            
            $popup_effect = whp_get_setting('whp_contact_popup_effect') ?: 'zoom-in';
            if ($popup_effect === 'fade-in') {
                $custom_css .= '#mb-whp-contact .whp-contact-content { transform: none !important; }';
            } elseif ($popup_effect === 'slide-up') {
                $custom_css .= '#mb-whp-contact .whp-contact-content { transform: translateY(30px) !important; }'
                            . '#mb-whp-contact .whp-contact-content.active { transform: translateY(0) !important; }';
            }
            
            wp_add_inline_style('whp-frontend-app', $custom_css);

            // Nút Mua ngay cùng hàng với Thêm vào giỏ hàng
            if (whp_get_setting('whp_woocommerce_cta_show_buynow_button')) {
                wp_add_inline_style('whp-frontend-app',
                    '.woocommerce div.product form.cart{display:flex;flex-wrap:nowrap;align-items:stretch;gap:8px;}'
                    . '.woocommerce div.product form.cart .quantity{float:none;margin:0;flex-shrink:0;}'
                    . '.woocommerce div.product form.cart .single_add_to_cart_button{flex:1;width:auto!important;min-width:0;}'
                    . '.woocommerce div.product form.cart a.buy-now{display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;flex:1;min-width:0;}'
                );
            }
        }

        public function include_script()
        {
            wp_enqueue_script('whp-cookie', $this->pathAsset . 'js/cookie.js', array('jquery'), time(), true);
            wp_enqueue_script('whp-frontend-js', $this->pathAsset . 'js/app.js', array('jquery', 'whp-cookie'), time(), true);
        }
        public function include_header()
        {
            add_action('wp_head', [$this, 'whp_header_code']);
        }
        public function include_body()
        {
            add_action('wp_body_open', [$this, 'whp_body_code']);
        }
        public function include_footer()
        {
            add_action('wp_footer', [$this, 'whp_contact']);
            add_action('wp_footer', [$this, 'whp_footer_code']);
        }
        public function whp_header_code()
        {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw output for admin-configured header scripts
            echo wp_unslash( whp_get_setting( 'whp_code_header' ) );
        }
        public function whp_body_code()
        {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw output for admin-configured body scripts
            echo wp_unslash( whp_get_setting( 'whp_code_body' ) );
        }
        public function whp_footer_code()
        {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- intentional raw output for admin-configured footer scripts
            echo wp_unslash( whp_get_setting( 'whp_code_footer' ) );
        }
        // start contact
        public function whp_contact()
        {
            $whp_contact_active = whp_get_setting('whp_contact_active');
            $whp_contact_phone_only = null;
            $whp_contact_phone_class = null;
            $whp_contact_phone_data_number = null;
            if ($whp_contact_active) {
                $fields = whp_get_contact_fields();
                foreach ($fields as $field) {
                    $$field = whp_get_setting($field);
                }
                $whp_contact_phone_data_number = is_array($whp_contact_phone_data) ? count($whp_contact_phone_data) : 0;
                $whp_contact_phone_class = $whp_contact_phone_data_number == 1 ? "only-call" : "";
                $whp_contact_phone_first = $whp_contact_phone_data_number == 1 ? array_shift($whp_contact_phone_data) : [];
                $whp_contact_phone_only = $whp_contact_phone_first['phone'] ?? "";
                global $field;
                require_once($this->pathViewElement . "contact.php");
            }
        }
        // end contact

        // start smtp
        public function whp_smtp()
        {
            add_action('phpmailer_init', [$this, 'whp_smtp_send_mail']);
            add_filter('wp_mail_content_type', [$this, 'whp_mail_content_type']);
            // Log SMTP state và lỗi — CHỈ khi WP_DEBUG bật
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                add_action('phpmailer_init', function($pm) {
                    $log = WP_CONTENT_DIR . '/smtp-debug.log';
                    $line = '[' . date('Y-m-d H:i:s') . '] PHPMAILER_INIT'
                        . ' Host=' . $pm->Host
                        . ' Port=' . $pm->Port
                        . ' Secure=' . $pm->SMTPSecure
                        . ' Auth=' . ($pm->SMTPAuth ? 'yes' : 'no')
                        . ' From=' . $pm->From
                        . ' To=' . implode(',', array_column($pm->getToAddresses(), 0))
                        . "\n";
                    file_put_contents($log, $line, FILE_APPEND | LOCK_EX);
                }, 9999);
                add_action('wp_mail_failed', function($error) {
                    $log = WP_CONTENT_DIR . '/smtp-debug.log';
                    $msg = $error instanceof WP_Error ? $error->get_error_message() : wp_json_encode( $error );
                    $line = '[' . date('Y-m-d H:i:s') . '] WP_MAIL_FAILED: ' . $msg . "\n";
                    file_put_contents($log, $line, FILE_APPEND | LOCK_EX);
                }, 1);
            }
        }
        public function whp_smtp_send_mail($phpmailer)
        {
            $fields = whp_get_smtp_fields();
            foreach ($fields as $field) {
                $$field = whp_get_setting($field);
            }
            if ($whp_smtp_active) {
                $phpmailer->isSMTP();
                $phpmailer->Host     = $whp_smtp_host ?? "";
                $phpmailer->Port     = $whp_smtp_port ?? "";
                $phpmailer->SMTPSecure = $whp_smtp_security ?? "";
                $phpmailer->SMTPAuth = ($whp_smtp_auth !== '0');
                $phpmailer->Username = $whp_smtp_user ?? "";
                $phpmailer->Password = $whp_smtp_password ?? "";

                $smtp_email = trim($whp_smtp_email ?? "");
                $smtp_name  = trim($whp_smtp_from_name ?? "");
                if ($smtp_email) {
                    $phpmailer->setFrom($smtp_email, $smtp_name, false);
                    $phpmailer->Sender = $smtp_email;
                }
                $phpmailer->clearReplyTos();
                $phpmailer->addReplyTo($phpmailer->From, $phpmailer->FromName);
            }
        }
        public function whp_mail_content_type()
        {
            return 'text/html';
        }
        // end smtp
        // start security
        public function whp_security()
        {
            $fields = whp_get_security_fields();
            foreach ($fields as $field) {
                $$field = whp_get_setting($field);
            }

            if ($whp_security_remove_xmlrpc) {
                add_filter('xmlrpc_enabled', '__return_false');
            }
            if ($whp_security_disable_copy) {
                add_action('wp_enqueue_scripts', [$this, 'whp_security_disable_copy']);
            }
            if ($whp_security_delete_wphead) {
                $this->whp_security_remove_wphead();
            }
            if ($whp_security_hide_wp_version) {
                $this->whp_security_hide_wp_version();
            }
            if ($whp_security_hide_theme_plugin) {
                $this->whp_security_hide_theme_plugin();
            }

            if ($whp_security_change_login_url && $whp_new_login_url) {
                $login_slug = trim($whp_new_login_url, '/');

                // Hook login_init: fire trong wp-login.php TRƯỚC khi render bất kỳ output
                // Dùng login_init thay vì init để đảm bảo intercept đúng context
                add_action('login_init', function () use ($login_slug) {
                    $action       = sanitize_key( $_GET['action'] ?? '' );
                    $skip_actions = [ 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'postpass', 'register', 'confirm_admin_email' ];
                    if ( in_array( $action, $skip_actions, true ) ) return;

                    if ( is_user_logged_in() ) {
                        // Đã đăng nhập → về admin
                        wp_safe_redirect( admin_url() );
                        exit;
                    }

                    if ( empty( $_COOKIE['_wph_ok'] ) ) {
                        // Không có cookie gate (không đến từ custom URL) → về trang chủ
                        wp_safe_redirect( home_url( '/' ) );
                        exit;
                    }
                    // Có cookie → cho qua, xóa cookie sau khi load xong
                });

                // Hook init: xử lý custom login URL + chặn wp-admin
                add_action('init', function () use ($login_slug) {
                    $uri          = $_SERVER['REQUEST_URI'] ?? '/';
                    $path         = trim( parse_url( $uri, PHP_URL_PATH ) ?? '/', '/' );
                    $action       = sanitize_key( $_GET['action'] ?? '' );
                    $skip_actions = [ 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'postpass', 'register', 'confirm_admin_email' ];
                    $is_wp_admin  = ( $path === 'wp-admin' || strpos( $path, 'wp-admin/' ) === 0 );

                    if ( ! is_user_logged_in() ) {

                        // 1. Custom URL → set cookie gate + chuyển sang wp-login.php thực
                        if ( $path === $login_slug ) {
                            if ( ! isset( $_COOKIE['_wph_ok'] ) ) {
                                setcookie( '_wph_ok', '1', 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
                                $_COOKIE['_wph_ok'] = '1';
                            }
                            $redirect_to = isset( $_GET['redirect_to'] ) ? $_GET['redirect_to'] : admin_url();
                            $login_url   = site_url( 'wp-login.php' ) . '?redirect_to=' . rawurlencode( $redirect_to );
                            if ( ! empty( $_GET['reauth'] ) ) $login_url .= '&reauth=1';
                            wp_safe_redirect( $login_url );
                            exit;
                        }

                        // 2. /wp-admin và mọi sub-path → chuyển về custom login URL
                        if ( $is_wp_admin ) {
                            wp_safe_redirect( home_url( '/' . $login_slug . '/' ) );
                            exit;
                        }

                    } else {
                        // Đã đăng nhập: vào custom URL → về admin
                        if ( $path === $login_slug && ! in_array( $action, $skip_actions, true ) ) {
                            wp_safe_redirect( admin_url() );
                            exit;
                        }
                    }
                });
            }
        }


        public function whp_security_disable_copy()
        {
            wp_enqueue_script('disableCopy', $this->pathAsset . 'js/disableCopy.js', array('jquery'), time(), true);
        }
        public function whp_security_remove_wphead()
        {
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
            remove_action('wp_head', 'feed_links', 2);
            remove_action('wp_head', 'feed_links_extra', 3);
            remove_action('wp_head', 'start_post_rel_link', 10, 0);
            remove_action('wp_head', 'parent_post_rel_link', 10, 0);
            remove_action('wp_head', 'index_rel_link');
            remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head, 10, 0');
        }
        public function whp_security_hide_wp_version()
        {
            function remove_version_info()
            {
                return '';
            }
            add_filter('the_generator', 'remove_version_info');
            function change_footer_admin()
            {
                return ' ';
            }
            add_filter('admin_footer_text', 'change_footer_admin', 9999);
            function change_footer_version()
            {
                return ' ';
            }
            add_filter('update_footer', 'change_footer_version', 9999);
        }
        public function whp_security_hide_theme_plugin()
        {
            if (is_admin()) {
                add_filter('auto_update_core', '__return_false');
                add_filter('auto_update_translation', '__return_false');
                add_action('admin_menu', [$this, 'whp_security_hide_admin_menu']);
                if (!defined('DISALLOW_FILE_EDIT'))
                    define('DISALLOW_FILE_EDIT', true);
                if (!defined('DISALLOW_FILE_MODS'))
                    define('DISALLOW_FILE_MODS', true);
                add_action('admin_menu', [$this, 'whp_security_hide_admin_menu']);
            }
        }
        public function whp_security_hide_admin_menu()
        {
            remove_menu_page('theme-editor.php');
            remove_menu_page('plugins.php');
            remove_menu_page('themes.php');
        }

        // start woocommerce cta
        public function whp_woo_cta()
        {
            $fields = whp_get_woo_cta_fields();

            foreach ($fields as $field) {
                $$field = whp_get_setting($field);
            }
            if ($whp_woocommerce_cta_text && !$whp_woocommerce_cta_show_buynow_button) {
                // Chỉ đổi text nút gốc khi KHÔNG có nút Buy-Now riêng
                // (tránh 2 nút cùng label "Mua ngay")
                add_filter(
                    'woocommerce_product_single_add_to_cart_text',
                    function () use ($whp_woocommerce_cta_text) {
                        return $whp_woocommerce_cta_text;
                    },
                    10,
                    2
                );
                add_filter('woocommerce_product_add_to_cart_text', function () use ($whp_woocommerce_cta_text) {
                    return $whp_woocommerce_cta_text;
                }, 10, 2);
            }
            if ($whp_woocommerce_cta_convert_zero_to_contact) {
                add_filter('woocommerce_get_price_html', [$this, 'whp_woo_cta_convert_zero_to_contact'], 99, 2);
            }
            if ($whp_woocommerce_cta_show_buynow_button) {
                add_action('woocommerce_after_add_to_cart_button', [$this, 'whp_woo_cta_show_buynow_button']);
                add_action('wp', [$this, 'whp_handle_buy_now']);
            }
        }

        public function whp_handle_buy_now()
        {
            if (empty($_GET['whp_buy_now'])) return;

            $product_id = absint($_GET['whp_buy_now']);
            if (!$product_id || !function_exists('WC')) return;

            $quantity = isset($_GET['quantity']) ? max(1, absint($_GET['quantity'])) : 1;

            WC()->cart->add_to_cart($product_id, $quantity);

            wp_safe_redirect(wc_get_checkout_url());
            exit;
        }

        public function whp_woo_cta_show_buynow_button()
        {
            $current_product_id = get_the_ID();
            $product = wc_get_product($current_product_id);

            if (!$product || !$product->is_type('simple')) return;

            // Dùng text từ settings, fallback về "Mua ngay"
            $btn_label = whp_get_setting('whp_woocommerce_cta_text') ?: 'Mua ngay';

            $base_url = esc_url(add_query_arg('whp_buy_now', $current_product_id, get_permalink($current_product_id)));
            printf(
                '<a href="%s" class="buy-now button alt" data-buynow="%d">%s</a>',
                $base_url,
                $current_product_id,
                esc_html($btn_label)
            );
            ?>
            <script>
            (function(){
                var btn = document.querySelector('a.buy-now[data-buynow="<?php echo (int)$current_product_id; ?>"]');
                if (!btn) return;
                var base = btn.href;
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var qtyInput = document.querySelector('form.cart input.qty');
                    var qty = qtyInput ? Math.max(1, parseInt(qtyInput.value, 10) || 1) : 1;
                    window.location.href = base + '&quantity=' + qty;
                });
            })();
            </script>
            <?php
        }
        public function whp_woo_cta_convert_zero_to_contact($price, $product)
        {
            if (!is_admin() && $product->get_price() == 0) {
                $price = '<span class="amount">' . 'Liên hệ' . '</span>';
            }
            return $price;
        }

        // end woocommerce cta


        // start extenttion
        public function whp_extention()
        {
            $fields = whp_get_extention_fields();
            foreach ($fields as $key => $field) {
                $$field = whp_get_setting($field);
            }

            if ($whp_extention_editor_type) {
                add_filter('use_block_editor_for_post', '__return_false');
            }
            if ($whp_extention_duplicate_menu) {
                add_action('admin_menu', array($this, 'whp_extention_duplicate_menu_add_menu'));
            }

            if ($whp_extention_duplicate_page_post) {

                add_filter('post_row_actions', array($this, 'whp_extention_duplicate'), 10, 2);
                add_filter('page_row_actions', array($this, 'whp_extention_duplicate'), 10, 2);
                add_action('admin_action_whp_extention_duplicate_action', array($this, 'whp_extention_duplicate_action'));
            }
            if ($whp_extention_enable_404_redirect) {

                add_action('wp', [$this, 'whp_extention_redirect_404_to_homepage'], 1);
            }
            if ($whp_extention_disable_emojis) {
                add_action('init', [$this, 'whp_extention_disable_emojis']);
            }
            if ($whp_extention_remove_query_string) {
                add_filter('script_loader_src', [$this, 'whp_extention_remove_query'], 999);
                add_filter('style_loader_src', [$this, 'whp_extention_remove_query'], 999);
            }
            if ($whp_extention_disbale_wp_embeds) {
                add_action('init', [$this, 'whp_extention_disable_embeds_code_init'], 9999);
            }
            if ($whp_extention_disbale_google_fonts) {
                add_filter('style_loader_src', function ($href) {
                    if (strpos($href, "//fonts.googleapis.com/") === false) {
                        return $href;
                    }
                    return false;
                });

                // Remove dns-prefetch for fonts.googleapis
                add_filter('wp_resource_hints', function ($urls) {

                    foreach ($urls as $key => $url) {
                        if ('fonts.googleapis.com' === $url) {
                            unset($urls[$key]);
                        }
                    }
                    return $urls;
                });
            }

            // Heartbeat: tắt hoàn toàn trên frontend (không ảnh hưởng admin)
            if ( ! empty( $whp_extention_disable_heartbeat_frontend ) ) {
                add_action( 'init', function () {
                    if ( ! is_admin() ) {
                        wp_deregister_script( 'heartbeat' );
                    }
                }, 1 );
            }

            // Heartbeat: giảm tần suất admin 15s → 60s
            if ( ! empty( $whp_extention_heartbeat_limit_admin ) ) {
                add_filter( 'heartbeat_settings', function ( $settings ) {
                    $settings['interval'] = 60;
                    return $settings;
                } );
            }

            if ($whp_extention_custom_login_theme) {

                if ($whp_extention_custom_login_logo) {

                    add_action('login_head', function () use ($whp_extention_custom_login_logo) {
                        $url = esc_url($whp_extention_custom_login_logo);
                        echo '<style type="text/css">
#login h1 a, .login h1 a {
    background-image: url(\'' . $url . '\') !important;
    background-repeat: no-repeat !important;
    background-size: contain !important;
    background-position: center bottom !important;
    width: 320px !important;
    height: 80px !important;
    display: block !important;
}
body.login { background-color: #f0f0f1; }
</style>';
                    });
                }
                if ($whp_extention_custom_link) {
                    add_filter('login_headerurl', function () use ($whp_extention_custom_link) {
                        return esc_url($whp_extention_custom_link);
                    });
                }
                $new_tab = whp_get_option('whp_extention_custom_link_new_tab');
                if ($new_tab === '1') {
                    add_filter('login_headertext', function ($text) {
                        return $text;
                    });
                    add_action('login_head', function () {
                        echo '<style>.login h1 a { target-new: tab; }</style>
<script>document.addEventListener("DOMContentLoaded",function(){var a=document.querySelector(".login h1 a");if(a)a.setAttribute("target","_blank");});</script>';
                    });
                }
            }

            if ($whp_extention_disbale_dashicons) {
                add_action('wp_enqueue_scripts', function () {
                    if (!is_user_logged_in()) {
                        wp_dequeue_style('dashicons');
                        wp_deregister_style('dashicons');
                    }
                });
            }

            if ($whp_extention_notification) {
                add_action('admin_init', [$this, 'whp_disable_notification']);
            }

            // Phone filter hooks moved to whp_advance() — key lives in whp_get_woo_advance_fields()

            if ($whp_extention_svg) {

                add_filter('upload_mimes', [$this, 'whp_extention_allow_svg_upload']);

                // WordPress 5.1+ uses finfo/mime_content_type to verify actual file type,
                // which returns 'image/svg+xml' but WP's internal allowlist rejects it.
                // This filter bypasses that check for SVG files specifically.
                add_filter('wp_check_filetype_and_ext', [$this, 'whp_fix_svg_filetype_check'], 10, 4);

                add_filter('wp_handle_upload_prefilter', [$this, 'whp_validate_uploaded_svg']);

                add_action('admin_head', function () {
                    echo '<style type="text/css">
                         .media-icon img[src$=".svg"], img[src$=".svg"].attachment-post-thumbnail {
                      width: 100% !important;
                      height: auto !important;
                    }</style>';
                });
            }
        }
        function whp_validate_uploaded_svg($file)
        {
            // Only validate if it is an SVG file
            $file_type = isset($file['type']) ? $file['type'] : '';
            $file_name = isset($file['name']) ? $file['name'] : '';
            $is_svg = (strpos($file_type, 'image/svg') !== false || strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) === 'svg');

            if (!$is_svg) {
                return $file;
            }

            if (!isset($file['tmp_name']) || !file_exists($file['tmp_name']) || !is_readable($file['tmp_name'])) {
                return $file;
            }

            $file_content = file_get_contents($file['tmp_name']);

            if (empty($file_content)) {
                $file['error'] = __('Tập tin SVG không có nội dung.', 'wp-helper-lite');
                return $file;
            }

            $trimmed_content = trim($file_content);

            if (stripos($trimmed_content, '<svg') === false) {
                $file['error'] = __('Tập tin không phải là định dạng SVG hợp lệ.', 'wp-helper-lite');
                return $file;
            }

            if (strpos($file_content, '<script') !== false) {
                $file['error'] = __('Tập tin SVG chứa mã script không hợp lệ.', 'wp-helper-lite');
                return $file;
            }

            return $file;
        }
        public function whp_extention_allow_svg_upload($mimes)
        {
            $mimes['svg'] = 'image/svg+xml';
            return $mimes;
        }

        public function whp_fix_svg_filetype_check($data, $file, $filename, $mimes)
        {
            if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'svg') {
                $data['ext']  = 'svg';
                $data['type'] = 'image/svg+xml';
            }
            return $data;
        }

        public function whp_extention_field_filter_order($field)
        {
            $field['phone'] = __('Số điện thoại', 'whp');
            return $field;
        }

        public function whp_extention_filter_order_in_table($query)
        {
            if (!is_admin() || !isset($_GET['post_type']) || $_GET['post_type'] !== 'shop_order') {
                return;
            }

            if (isset($_GET['phone']) && $_GET['phone'] !== '') {
                $phone = sanitize_text_field($_GET['phone']);
                $meta_query = array(
                    array(
                        'key' => '_billing_phone',
                        'value' => $phone,
                        'compare' => 'LIKE',
                    ),
                );
                $query->set('meta_query', $meta_query);
            }
        }

        public function whp_extention_display_order($column)
        {
            global $post;

            if ('phone' === $column && 'shop_order' === get_post_type($post)) {
                $billing_phone = get_post_meta($post->ID, '_billing_phone', true);

                if ($billing_phone && strlen($billing_phone) > 0) {
                    echo esc_html($billing_phone);
                }
            }
        }

        public function whp_extention_show_field_order()
        {
            if (!isset($_GET['post_type']) || $_GET['post_type'] !== 'shop_order') {
                return;
            }
            $phone = isset($_GET['phone']) ? sanitize_text_field($_GET['phone']) : '';
            ?>
            <input type="text" name="phone" value="<?php echo esc_attr($phone); ?>"
                   placeholder="<?php esc_attr_e('Lọc theo số điện thoại', 'whp'); ?>"
                   style="min-width:160px;">
            <?php
        }

        // ── HPOS-compatible methods ─────────────────────────────────────────────

        public function whp_extention_display_order_hpos($column_name, $order)
        {
            if ('phone' !== $column_name) {
                return;
            }
            echo esc_html($order->get_billing_phone());
        }

        public function whp_extention_show_field_order_hpos()
        {
            $phone = isset($_GET['phone']) ? sanitize_text_field($_GET['phone']) : '';
            ?>
            <input type="text" id="whp-order-phone-filter" name="phone"
                   value="<?php echo esc_attr($phone); ?>"
                   placeholder="<?php esc_attr_e('Lọc theo số điện thoại', 'whp'); ?>"
                   style="min-width:160px;">
            <?php
        }

        public function whp_extention_filter_order_hpos_clauses($clauses, $query, $query_args)
        {
            if (!is_admin() || empty($_GET['phone'])) {
                return $clauses;
            }
            // Only apply on the HPOS orders list page
            if (!isset($_GET['page']) || $_GET['page'] !== 'wc-orders') {
                return $clauses;
            }
            global $wpdb;
            $phone = sanitize_text_field($_GET['phone']);
            $like  = '%' . $wpdb->esc_like($phone) . '%';
            $clauses['where'] .= $wpdb->prepare(
                " AND {$wpdb->prefix}wc_orders.billing_phone LIKE %s",
                $like
            );
            return $clauses;
        }

        // ── end phone filter ────────────────────────────────────────────────────

        // start extention duplicate menu
        public function whp_extention_duplicate_menu_add_menu()
        {
            add_theme_page(
                'Nhân bản Menu',
                'Nhân bản Menu',
                'edit_theme_options',
                'duplicate-menu',
                array($this, 'whp_extention_duplicate_menu')
            );
        }

        public function whp_extention_duplicate_menu()
        {

            $nav_menus = wp_get_nav_menus();
            require_once($this->pathViewAdmin . 'duplicate-menu.php');
        }

        function whp_extention_duplicate_menu_action($id = null, $name = null)
        {

            // sanity check
            if (empty($id) || empty($name)) {
                return false;
            }

            $id = intval($id);
            $name = sanitize_text_field($name);
            $source = wp_get_nav_menu_object($id);
            $source_items = wp_get_nav_menu_items($id);
            $new_id = wp_create_nav_menu($name);

            if (!$new_id) {
                return false;
            }

            // key is the original db ID, val is the new
            $rel = array();

            $i = 1;
            foreach ($source_items as $menu_item) {
                $args = array(
                    'menu-item-db-id'       => $menu_item->db_id,
                    'menu-item-object-id'   => $menu_item->object_id,
                    'menu-item-object'      => $menu_item->object,
                    'menu-item-position'    => $i,
                    'menu-item-type'        => $menu_item->type,
                    'menu-item-title'       => $menu_item->title,
                    'menu-item-url'         => $menu_item->url,
                    'menu-item-description' => $menu_item->description,
                    'menu-item-attr-title'  => $menu_item->attr_title,
                    'menu-item-target'      => $menu_item->target,
                    'menu-item-classes'     => implode(' ', $menu_item->classes),
                    'menu-item-xfn'         => $menu_item->xfn,
                    'menu-item-status'      => $menu_item->post_status
                );

                $parent_id = wp_update_nav_menu_item($new_id, 0, $args);

                $rel[$menu_item->db_id] = $parent_id;

                // did it have a parent? if so, we need to update with the NEW ID
                if ($menu_item->menu_item_parent) {
                    $args['menu-item-parent-id'] = $rel[$menu_item->menu_item_parent];
                    $parent_id = wp_update_nav_menu_item($new_id, $parent_id, $args);
                }

                // allow developers to run any custom functionality they'd like
                do_action('duplicate_menu_item', $menu_item, $args);

                $i++;
            }

            return $new_id;
        }
        // end extention duplicate menu


        // start extention disable emojis
        function whp_extention_disable_emojis()
        {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('tiny_mce_plugins', [$this, 'whp_extention_disable_emojis_tinymce']);
            add_filter('wp_resource_hints', [$this, 'whp_extention_disable_emojis_prefetch'], 10, 2);
        }

        function whp_extention_disable_emojis_tinymce($plugins)
        {
            if (is_array($plugins)) {
                return array_diff($plugins, array('wpemoji'));
            } else {
                return array();
            }
        }

        function whp_extention_disable_emojis_prefetch($urls, $relation_type)
        {
            if ('dns-prefetch' == $relation_type) {
                /** This filter is documented in wp-includes/formatting.php */
                $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
                $urls = array_diff($urls, array($emoji_svg_url));
            }

            return $urls;
        }

        function whp_extention_disable_embeds_code_init()
        {

            remove_action('rest_api_init', 'wp_oembed_register_route');

            add_filter('embed_oembed_discover', '__return_false');

            remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

            remove_action('wp_head', 'wp_oembed_add_discovery_links');

            remove_action('wp_head', 'wp_oembed_add_host_js');

            add_filter('tiny_mce_plugins', [$this, 'whp_extention_disable_embeds_tiny']);

            add_filter('rewrite_rules_array', [$this, 'whp_extention_disable_embeds_rewrites']);

            remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result', 10);
        }

        function whp_extention_disable_embeds_rewrites($rules)
        {
            foreach ($rules as $rule => $rewrite) {
                if (false !== strpos($rewrite, 'embed=true')) {
                    unset($rules[$rule]);
                }
            }
            return $rules;
        }
        function whp_extention_disable_embeds_tiny($plugins)
        {
            return array_diff($plugins, array('wpembed'));
        }
        // end extention disable emojis

        // start  extention rediriect 404 to homepage
        function whp_extention_redirect_404_to_homepage()
        {
            global $wp_query;
            if ($wp_query->is_404) {
                wp_redirect(get_bloginfo('url'), 301);
                exit;
            }
        }
        // end  extention rediriect 404 to homepage


        // start extention remove query
        function whp_extention_remove_query($src)
        {
            if (strpos($src, '?v=')) {
                $src = remove_query_arg('v', $src);
            }
            if (strpos($src, '?ver=')) {
                $src = remove_query_arg('ver', $src);
            }

            return $src;
        }
        // end extention remove query


        // start extention logo
        public function whp_extention_logo($url)
        {

            $url = esc_url($url);
            $custom_css = "#login h1 a {

            background: url('" + $url + "') no-repeat !important;
            }";
            wp_add_inline_style('login_css', $custom_css);
        }
        // end extention logo

        // start extention duplicate post and page
        public function whp_extention_duplicate_action()
        {

            $nonce = sanitize_text_field($_REQUEST['nonce']);

            $post_id = (isset($_GET['post']) ? intval($_GET['post']) : intval($_POST['post']));

            $post = get_post($post_id);
            $current_user_id = get_current_user_id();
            if (wp_verify_nonce($nonce, 'dt-duplicate-page-' . $post_id)) {
                if (current_user_can('manage_options') || current_user_can('edit_others_posts')) {
                    $this->whp_extention_duplicate_edit_post_and_page($post_id);
                } else if (current_user_can('contributor') && $current_user_id == $post->post_author) {
                    $this->whp_extention_duplicate_edit_post_and_page($post_id, 'pending');
                } else if (current_user_can('edit_posts') && $current_user_id == $post->post_author) {
                    $this->whp_extention_duplicate_edit_post_and_page($post_id);
                } else {
                    wp_die(__('Bạn không có quyền truy cập.', 'duplicate-page'));
                }
            } else {
                wp_die(__('Đã xảy ra lỗi vui lòng thử lại!!!', 'duplicate-page'));
            }
        }
        // end extention duplicate post and page
        public function whp_extention_duplicate_edit_post_and_page($post_id, $post_status_update = '')
        {

            global $wpdb;
            $opt = get_option('duplicate_page_options');
            $suffix = isset($opt['duplicate_post_suffix']) && !empty($opt['duplicate_post_suffix']) ? ' -- ' . esc_attr($opt['duplicate_post_suffix']) : '';
            if ($post_status_update == '') {
                $post_status = !empty($opt['duplicate_post_status']) ? esc_attr($opt['duplicate_post_status']) : 'draft';
            } else {
                $post_status =  $post_status_update;
            }
            $redirectit = !empty($opt['duplicate_post_redirect']) ? esc_attr($opt['duplicate_post_redirect']) : 'to_list';
            if (!(isset($_GET['post']) || isset($_POST['post']) || (isset($_REQUEST['action']) && 'dt_duplicate_post_as_draft' == sanitize_text_field($_REQUEST['action'])))) {
                wp_die(__('No post to duplicate has been supplied!', 'duplicate-page'));
            }

            $returnpage = '';

            $post = get_post($post_id);

            $current_user = wp_get_current_user();

            $new_post_author = $current_user->ID;

            if (isset($post) && $post != null) {
                /*
                   * new post data array
                   */
                $args = array(
                    'comment_status' => $post->comment_status,
                    'ping_status' => $post->ping_status,
                    'post_author' => $new_post_author,
                    'post_content' => (isset($opt['duplicate_post_editor']) && $opt['duplicate_post_editor'] == 'gutenberg') ? wp_slash($post->post_content) : $post->post_content,
                    'post_excerpt' => $post->post_excerpt,
                    'post_parent' => $post->post_parent,
                    'post_password' => $post->post_password,
                    'post_status' => $post_status,
                    'post_title' => $post->post_title . $suffix,
                    'post_type' => $post->post_type,
                    'to_ping' => $post->to_ping,
                    'menu_order' => $post->menu_order,
                );
                /*
                   * insert the post by wp_insert_post() function
                   */
                $new_post_id = wp_insert_post($args);
                if (is_wp_error($new_post_id)) {
                    wp_die(__($new_post_id->get_error_message(), 'duplicate-page'));
                }

                /*
                   * get all current post terms ad set them to the new post draft
                   */
                $taxonomies = array_map('sanitize_text_field', get_object_taxonomies($post->post_type));
                if (!empty($taxonomies) && is_array($taxonomies)) :
                    foreach ($taxonomies as $taxonomy) {
                        $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                        wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                    }
                endif;
                /*
                   * duplicate all post meta
                   */
                $post_meta_keys = get_post_custom_keys($post_id);
                if (!empty($post_meta_keys)) {
                    foreach ($post_meta_keys as $meta_key) {
                        $meta_values = get_post_custom_values($meta_key, $post_id);
                        foreach ($meta_values as $meta_value) {
                            $meta_value = maybe_unserialize($meta_value);
                            update_post_meta($new_post_id, $meta_key, wp_slash($meta_value));
                        }
                    }
                }

                /**
                 * Elementor compatibility fixes
                 */
                if (is_plugin_active('elementor/elementor.php')) {
                    $css = Elementor\Core\Files\CSS\Post::create($new_post_id);
                    $css->update();
                }
                /*
                   * finally, redirecting to your choice
                   */
                if ($post->post_type != 'post') {
                    $returnpage = '?post_type=' . $post->post_type;
                }

                if (!empty($redirectit) && $redirectit == 'to_list') {
                    wp_redirect(esc_url_raw(admin_url('edit.php' . $returnpage)));
                } elseif (!empty($redirectit) && $redirectit == 'to_page') {
                    wp_redirect(esc_url_raw(admin_url('post.php?action=edit&post=' . $new_post_id)));
                } else {
                    wp_redirect(esc_url_raw(admin_url('edit.php' . $returnpage)));
                }
                exit;
            } else {
                wp_die(__('Lỗi! Thao tác thất bại: ', 'duplicate-page') . $post_id);
            }
        }

        public function whp_disable_notification()
        {

            global $wp_filter;

            if (isset($wp_filter['admin_notices'])) {
                unset($wp_filter['admin_notices']);
            }

            if (isset($wp_filter['all_admin_notices'])) {
                unset($wp_filter['all_admin_notices']);
            }
        }
        // end extenttion


        // start ecommerce
        function whp_ecommerce()
        {
            // Hook chính: cuối meta section (theme chuẩn)
            add_action('woocommerce_product_meta_end', [$this, 'whp_ecommerce_show']);
            // Fallback: Elementor / Astra đôi khi không gọi meta.php → dùng summary hook (sau add-to-cart priority 30)
            add_action('woocommerce_single_product_summary', [$this, 'whp_ecommerce_show'], 35);
        }
        public function whp_ecommerce_show()
        {
            static $already_shown = false;
            if ($already_shown) return;
            $already_shown = true;

            global $product;
            if (!$product || !is_a($product, 'WC_Product')) {
                $product = wc_get_product(get_the_ID());
            }
            if (!$product) return;
            $product_id = $product->get_id();
            $fields = whp_get_woo_ecommerce_fields();
            $arr = [];
            foreach ($fields as $key => $field) {
                $value = whp_get_setting($field);
                if ($value) {
                    array_push($arr, $field);
                }
            }

            $brand_colors = [
                'Tiki'   => '#189eff',
                'Shopee' => '#ee4d2d',
                'Lazada' => '#f57224',
                'Sendo'  => '#d0021b',
            ];

            $items_html = '';
            foreach ($arr as $key => $item) {
                $brand = explode('_', $item);
                $brand_uc = ucfirst($brand[3]);
                $icon = esc_url(whp_get_image_url($brand_uc) . '.svg');
                if (!empty($product_meta = get_post_meta($product_id, 'product-ecommerce-' . $item, true))) {
                    $link  = esc_url($product_meta);
                    $name  = esc_html($brand_uc);
                    $color = isset($brand_colors[$brand_uc]) ? $brand_colors[$brand_uc] : '#555';
                    $items_html .= sprintf(
                        '<li><a href="%s" target="_blank" class="whp-eco-btn" style="background:%s;" title="%s">
                            <img src="%s" alt="%s">
                        </a></li>',
                        $link, $color, $name, $icon, $name
                    );
                }
            }

            if (!$items_html) return;

            static $css_printed = false;
            if (!$css_printed) {
                $css_printed = true;
                echo '<style>
.whp-eco-wrap{display:flex;align-items:center;gap:10px;padding:10px 0;flex-wrap:wrap;}
.whp-eco-label{font-size:13px;color:#64748b;font-weight:500;white-space:nowrap;}
.whp-eco-wrap ul{display:flex!important;flex-wrap:wrap;gap:8px;margin:0!important;padding:0!important;list-style:none!important;}
.whp-eco-wrap li{margin:0!important;padding:0!important;}
.whp-eco-btn{display:inline-flex!important;align-items:center;justify-content:center;padding:5px;border-radius:10px;text-decoration:none!important;transition:transform .15s,opacity .15s;box-shadow:0 2px 8px rgba(0,0,0,.18);}
.whp-eco-btn:hover{transform:translateY(-2px);opacity:.88;}
.whp-eco-btn img{width:36px;height:36px;object-fit:contain;border:none!important;border-radius:8px!important;box-shadow:none!important;flex-shrink:0;}
</style>';
            }

            $xhtml  = '<div class="whp-eco-wrap">';
            $xhtml .= '<span class="whp-eco-label">Có bán trên:</span>';
            $xhtml .= '<ul class="mb-ecommerce-buttons whp-lst-n">' . $items_html . '</ul>';
            $xhtml .= '</div>';

            $allowed_html = [
                'style' => [],
                'div'   => ['class' => true],
                'span'  => ['class' => true],
                'ul'    => ['class' => true],
                'li'    => [],
                'a'     => ['href' => true, 'target' => true, 'class' => true, 'style' => true],
                'img'   => ['src' => true, 'alt' => true],
            ];
            echo wp_kses($xhtml, $allowed_html);
        }
        // set up in admim
        public function whp_woo_admin_ecommerce()
        {
            $arr = [];
            $fields = whp_get_woo_ecommerce_fields();
            foreach ($fields  as $key => $item) {
                $title = ucfirst($item);

                $value = whp_get_setting($item);
                if ($value) {
                    $arr += [$title => $item];
                }
            }
            if (count($arr)) {
                add_action('add_meta_boxes',  function () use ($arr) {
                    add_meta_box('ecommerce', 'Liên kết sàn thương mại', function () use ($arr) {
                        foreach ($arr as $key => $item) {
                            $brand = explode('_', $item);
                            $brand_uc = ucfirst($brand[3]);
                            woocommerce_wp_text_input(
                                array(
                                    'id' => "product-ecommerce-{$item}",
                                    'placeholder' => __("Nhập link sản phẩm sàn {$brand_uc}", 'wphp-wc'),
                                    'label' => __("Link sàn {$brand_uc}", 'wphp-wc')
                                )
                            );
                        }
                    }, 'product');
                });
            }
            add_action('woocommerce_process_product_meta', [$this, 'whp_woocommerce_ecommerce_setting_update']);
        }

        public function whp_woocommerce_ecommerce_setting_update()
        {
            global $post;
            $postID = $post->ID;
            $fields  = whp_get_woo_ecommerce_fields();
            foreach ($fields as $key) {
                $name  = "product-ecommerce-{$key}";
                $value = isset($_POST[$name]) ? sanitize_url($_POST[$name]) : '';
                if ($value) {
                    update_post_meta($postID, $name, esc_url_raw($value));
                } else {
                    delete_post_meta($postID, $name);
                }
            }
        }
        // end ecommerce


        // start checkout
        public function whp_checkout()
        {
            add_filter('woocommerce_checkout_fields', [$this, 'whp_checkout_setting'], 30, 1);
        }
        public function whp_checkout_setting($fields)
        {
            $removeFields = [];
            $settingFields = whp_get_woo_payment_fields();
            foreach ($settingFields as $key => $field) {
                $$field = whp_get_option($field);
            }
            if ($whp_woocommerce_payment_fullname) {
                array_push($removeFields, 'first_name');
                $fields['billing']['billing_last_name'] = array(
                    'label'         => __('Họ và tên', 'wphp-wc'),
                    'placeholder'   => __('Nhập đầy đủ họ và tên của bạn', 'wphp-wc'),
                    'required'      => true,
                    'class'         => array('form-row-wide'),
                    'clear'         => true
                );
                $fields['shipping']['shipping_last_name'] = array(
                    'label'         => __('Họ và tên', 'wphp-wc'),
                    'placeholder'   => __('Nhập đầy đủ họ và tên của người nhận', 'wphp-wc'),
                    'required'      => true,
                    'class'         => array('form-row-wide'),
                    'clear'         => true
                );
            }
            if ($whp_woocommerce_payment_address) {
                array_push($removeFields, 'address_2');
                // Force address_1 full-width when address_2 is hidden
                if (isset($fields['billing']['billing_address_1'])) {
                    $fields['billing']['billing_address_1']['class'] = ['form-row-wide'];
                    $fields['billing']['billing_address_1']['clear'] = true;
                }
            }
            if ($whp_woocommerce_payment_country) {
                array_push($removeFields, 'country');
            }
            if ($whp_woocommerce_payment_company) {
                array_push($removeFields, 'company');
            }
            if ($whp_woocommerce_payment_zipcode) {
                array_push($removeFields, 'postcode');
            }
            if ($whp_woocommerce_payment_province) {
                array_push($removeFields, 'city');
            }
            array_push($removeFields, 'state');
            foreach ($removeFields as $field) {
                unset($fields['billing']['billing_' . $field]);
                unset($fields['shipping']['shipping_' . $field]);
            }
            $fields['billing']['billing_phone']['placeholder'] = __('Nhập số điện thoại', 'wp-helper-premium');
            $fields['billing']['billing_email']['placeholder'] = __('Nhập email', 'wp-helper-premium');
            // Tỉnh thành quận huyện
            $fields['billing']['billing_last_name']['priority'] = 10;
            $fields['billing']['billing_phone']['priority'] = 20;
            $fields['billing']['billing_email']['priority'] = 30;
            $fields['billing']['billing_address_1']['priority'] = 70;
            return $fields;
        }
        // end checkout


        // start advance
        public function whp_advance()
        {
            $fields = whp_get_woo_advance_fields();
            // unset($fields, [4, 5]);
            foreach ($fields as $key => $field) {
                $$field = whp_get_setting($field);
            }

            if ($whp_woocommerce_advance_enable_notice) {
                add_action('wp_enqueue_scripts', [$this, 'whp_notice']);
                add_action('wp_footer', [$this, 'whp_show_notice'], 0);
            }
            if ($whp_woocommerce_advance_enable_vat) {

                add_action('woocommerce_after_checkout_billing_form', [$this, 'whp_create_vat']);
                add_action('woocommerce_checkout_process', [$this, 'whp_validate_vat']);
                add_action('woocommerce_checkout_update_order_meta', [$this, 'whp_update_vat']);
                add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'whp_admin_order_vat_display'], 10, 1);
                add_action('woocommerce_email_order_meta', [$this, 'whp_email_order_vat_display'], 10, 4);
            }
            if ($whp_woocommerce_advance_enable_compact_desc) {
                add_action('wp_footer', [$this, 'whp_compact_desc'], 0);
                add_filter('the_content', [$this, 'the_content_product'], 100);
            }
            if ($whp_extention_filter_order_by_phone) {
                // ── Legacy CPT mode (WooCommerce < 7.1) ─────────────────────────
                add_filter('manage_edit-shop_order_columns', [$this, 'whp_extention_field_filter_order'], 10, 1);
                add_action('manage_shop_order_posts_custom_column', [$this, 'whp_extention_display_order'], 10, 1);
                add_action('restrict_manage_posts', [$this, 'whp_extention_show_field_order']);
                add_action('pre_get_posts', [$this, 'whp_extention_filter_order_in_table'], 99, 1);

                // ── HPOS mode (WooCommerce 7.1+ High-Performance Order Storage) ─
                add_filter('manage_woocommerce_page_wc-orders_columns', [$this, 'whp_extention_field_filter_order'], 10, 1);
                add_action('manage_woocommerce_page_wc-orders_custom_column', [$this, 'whp_extention_display_order_hpos'], 10, 2);
                add_action('woocommerce_order_list_table_restrict_manage_orders', [$this, 'whp_extention_show_field_order_hpos']);
                add_filter('woocommerce_orders_table_query_clauses', [$this, 'whp_extention_filter_order_hpos_clauses'], 10, 3);
            }
        }


        public function whp_notice()
        {
            wp_enqueue_style('wp-hp-wc-notification-style', $this->pathAsset . 'css/mb-hp-wc-notification.css', [], time());
            wp_enqueue_script('wp-hp-wc-notification-js', $this->pathAsset . 'js/mb-hp-wc-notification.js', array(), time(), true);
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => 10,
                'ignore_sticky_posts' => 1,
            );
            $ids = array();
            $loop = new WP_Query($args);
            if ($loop->have_posts()) {
                while ($loop->have_posts()) : $loop->the_post();
                    global $product;
                    array_push($ids, get_the_ID());
                endwhile;
                wp_reset_query();
            }
            $data_notification = [];
            foreach ($ids as $item) :
                $product =  wc_get_product($item);
                $product_name = $product->get_name();
                $permalink = $product->get_permalink();
                $image = wp_get_attachment_url($product->get_image_id());
                $image = ($image) ? $image : whp_get_image_url('assets/fe/img/placeholder-image.jpg');
                $obj = [
                    'product_name'  =>  $product_name,
                    'permalink'     =>  $permalink,
                    'images'        =>  $image
                ];
                array_push($data_notification, $obj);
            endforeach;
            wp_localize_script('wp-hp-wc-notification-js', 'notification', $data_notification);
        }

        public function whp_show_notice()
        {
            $result =  '<div id="mbwp-message-purchased"></div>';
            $allowed_html = array(
                'div' => array('id' => 'mbwp-message-purchased')
            );
            echo wp_kses($result, $allowed_html);
        }

        public function whp_create_vat()
        {
            require_once($this->pathViewElement . "vat.php");
        }

        public function whp_validate_vat()
        {
            if (isset($_POST['mb_hpwc_invoice_vat_input']) && !empty($_POST['mb_hpwc_invoice_vat_input'])) {
                if (empty($_POST['billing_vat_company'])) {
                    wc_add_notice(__('Hãy nhập tên công ty'), 'error');
                }
                if (empty($_POST['billing_vat_tax_code'])) {
                    wc_add_notice(__('Hãy nhập mã số thuế'), 'error');
                }
                if (empty($_POST['billing_vat_company_address'])) {
                    wc_add_notice(__('Hãy nhập địa chỉ công ty'), 'error');
                }
            }
        }

        public function whp_update_vat($order_id)
        {
            if (isset($_POST['mb_hpwc_invoice_vat_input']) && !empty($_POST['mb_hpwc_invoice_vat_input'])) {
                update_post_meta($order_id, 'mb_hpwc_invoice_vat_input', intval($_POST['mb_hpwc_invoice_vat_input']));
                if (isset($_POST['billing_vat_company']) && !empty($_POST['billing_vat_company'])) {
                    update_post_meta($order_id, 'billing_vat_company', sanitize_text_field($_POST['billing_vat_company']));
                }
                if (isset($_POST['billing_vat_tax_code']) && !empty($_POST['billing_vat_tax_code'])) {
                    update_post_meta($order_id, 'billing_vat_tax_code', sanitize_text_field($_POST['billing_vat_tax_code']));
                }
                if (isset($_POST['billing_vat_company_address']) && !empty($_POST['billing_vat_company_address'])) {
                    update_post_meta($order_id, 'billing_vat_company_address', sanitize_text_field($_POST['billing_vat_company_address']));
                }
            }
        }

        public function whp_admin_order_vat_display( $order ) {
            $vat_requested = get_post_meta( $order->get_id(), 'mb_hpwc_invoice_vat_input', true );
            if ( ! $vat_requested ) return;
            $company = get_post_meta( $order->get_id(), 'billing_vat_company', true );
            $tax     = get_post_meta( $order->get_id(), 'billing_vat_tax_code', true );
            $address = get_post_meta( $order->get_id(), 'billing_vat_company_address', true );
            echo '<div style="margin-top:12px;padding:10px 12px;background:#fefce8;border:1px solid #fde68a;border-radius:6px;font-size:13px">';
            echo '<strong style="color:#92400e;display:block;margin-bottom:6px">&#128196; Yêu cầu xuất hóa đơn VAT</strong>';
            if ( $company ) echo '<div><span style="color:#78716c">Tên công ty:</span> <strong>' . esc_html( $company ) . '</strong></div>';
            if ( $tax )     echo '<div><span style="color:#78716c">Mã số thuế:</span> <strong>' . esc_html( $tax ) . '</strong></div>';
            if ( $address ) echo '<div><span style="color:#78716c">Địa chỉ:</span> <strong>' . esc_html( $address ) . '</strong></div>';
            echo '</div>';
        }

        public function whp_email_order_vat_display( $order, $sent_to_admin, $plain_text, $email ) {
            $vat_requested = get_post_meta( $order->get_id(), 'mb_hpwc_invoice_vat_input', true );
            if ( ! $vat_requested ) return;
            $company = get_post_meta( $order->get_id(), 'billing_vat_company', true );
            $tax     = get_post_meta( $order->get_id(), 'billing_vat_tax_code', true );
            $address = get_post_meta( $order->get_id(), 'billing_vat_company_address', true );
            if ( $plain_text ) {
                echo "\n--- Yêu cầu xuất hóa đơn VAT ---\n";
                if ( $company ) echo 'Tên công ty: ' . $company . "\n";
                if ( $tax )     echo 'Mã số thuế: ' . $tax . "\n";
                if ( $address ) echo 'Địa chỉ: ' . $address . "\n";
                return;
            }
            echo '<table cellpadding="0" cellspacing="0" style="width:100%;margin-top:20px;margin-bottom:20px;border-collapse:collapse">';
            echo '<tr><td colspan="2" style="background:#fefce8;border:1px solid #fde68a;padding:10px 14px;border-radius:6px 6px 0 0">';
            echo '<strong style="color:#92400e;font-size:14px">&#128196; Yêu cầu xuất hóa đơn VAT</strong></td></tr>';
            if ( $company ) {
                echo '<tr><td style="padding:8px 14px;border:1px solid #fde68a;border-top:none;color:#78716c;width:40%">Tên công ty</td>';
                echo '<td style="padding:8px 14px;border:1px solid #fde68a;border-top:none;border-left:none;font-weight:bold">' . esc_html( $company ) . '</td></tr>';
            }
            if ( $tax ) {
                echo '<tr><td style="padding:8px 14px;border:1px solid #fde68a;border-top:none;color:#78716c">Mã số thuế</td>';
                echo '<td style="padding:8px 14px;border:1px solid #fde68a;border-top:none;border-left:none;font-weight:bold">' . esc_html( $tax ) . '</td></tr>';
            }
            if ( $address ) {
                echo '<tr><td style="padding:8px 14px;border:1px solid #fde68a;border-top:none;color:#78716c">Địa chỉ công ty</td>';
                echo '<td style="padding:8px 14px;border:1px solid #fde68a;border-top:none;border-left:none;font-weight:bold">' . esc_html( $address ) . '</td></tr>';
            }
            echo '</table>';
        }

        //wc_enqueue_js("jQuery('#tab-description').addClass('compact-active')");
        public function the_content_product($content)
        {
            if (is_product()) {
                $content .= '<div class="whp_readmore_producu_desc"><span><svg class="whp-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>Xem thêm</span></div>';
            }
            return $content;
        }

        public function whp_compact_desc()
        {
            if (is_product()) {


            ?>

                <style>
                    #tab-description {
                        max-height: 400px;
                        overflow: hidden;
                        position: relative;
                        transition: max-height 0.4s ease;
                    }

                    .whp_readmore_producu_desc {
                        text-align: center;
                        cursor: pointer;
                        position: absolute;
                        z-index: 9999;
                        bottom: 0;
                        width: 100%;
                        padding-bottom: 16px;
                        background: transparent;
                    }

                    .whp_readmore_producu_desc:before {
                        height: 80px;
                        margin-top: -80px;
                        content: "";
                        background: linear-gradient(to bottom, rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%);
                        display: block;
                    }

                    .whp_readmore_producu_desc span {
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        padding: 9px 22px;
                        background: #1a1a2e;
                        color: #fff;
                        font-size: 13px;
                        font-weight: 600;
                        letter-spacing: 0.05em;
                        text-transform: uppercase;
                        border-radius: 50px;
                        border: none;
                        box-shadow: 0 4px 14px rgba(0,0,0,0.18);
                        transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
                        user-select: none;
                    }

                    .whp_readmore_producu_desc:hover span {
                        background: #16213e;
                        transform: translateY(-1px);
                        box-shadow: 0 6px 18px rgba(0,0,0,0.24);
                    }

                    .whp_readmore_producu_desc:active span {
                        transform: translateY(0);
                        box-shadow: 0 2px 8px rgba(0,0,0,0.16);
                    }

                    .whp_readmore_producu_desc .whp-icon {
                        width: 14px;
                        height: 14px;
                        display: inline-block;
                        flex-shrink: 0;
                    }

                    #tab-description.whp-expanded .whp_readmore_producu_desc {
                        position: static;
                        padding-top: 12px;
                    }

                    #tab-description.whp-expanded .whp_readmore_producu_desc:before {
                        display: none;
                    }
                </style>

                <script>
                    (function() {
                        var SVG_DOWN = '<svg class="whp-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
                        var SVG_UP   = '<svg class="whp-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>';

                        jQuery('.whp_readmore_producu_desc').click(function() {
                            var $tab = jQuery('#tab-description');
                            var isExpanded = $tab.hasClass('whp-expanded');
                            if (isExpanded) {
                                $tab.css('max-height', '400px').removeClass('whp-expanded');
                                jQuery(this).html('<span>' + SVG_DOWN + 'Xem thêm</span>');
                            } else {
                                $tab.css('max-height', $tab[0].scrollHeight + 'px').addClass('whp-expanded');
                                jQuery(this).html('<span>' + SVG_UP + 'Thu gọn</span>');
                            }
                        });
                    }());
                </script>


<?php    }
        }

        public function whp_extention_duplicate($actions, $post)
        {

            if ($post->post_type == 'acf-field-group') {
                return $actions;
            }

            if (current_user_can('edit_posts')) {
                $actions['duplicate'] =
                    isset($post) ? '<a href="admin.php?action=whp_extention_duplicate_action&amp;post=' . intval($post->ID) . '&amp;nonce=' . wp_create_nonce('dt-duplicate-page-' . intval($post->ID)) . '" title="' . __('Sao chép', 'duplicate-page') . '" rel="permalink">' . __('Sao chép', 'duplicate-page') . '</a>' : '';
            }

            return $actions;
        }

        // end advance


        // start gateway wallet
        public function whp_gateway_wallet()
        {
            add_filter('woocommerce_payment_gateways', [$this, 'whp_gateway_wallet_setting']);
        }
        public function whp_gateway_wallet_setting($gateways)
        {

            $fields = whp_get_woo_wallet_fields();

            foreach ($fields as $key => $field) {

                $$field = whp_get_setting($field);
            }
            if ($whp_woocommerce_wallet_momo) {
                $name =  "class.wps-wallet-momo";
                $fileURL = plugin_dir_path(__FILE__) . "wallet/{$name}.php";
                if (file_exists($fileURL)) {
                    require_once($fileURL);
                    $gateways[] = 'MB_WHP_Wallet_MoMo';
                }
            }
            if ($whp_woocommerce_wallet_zalopay) {

                $name =  "class.wps-wallet-zalopay";
                $fileURL = plugin_dir_path(__FILE__) . "wallet/{$name}.php";
                if (file_exists($fileURL)) {
                    require_once($fileURL);
                    $gateways[] = 'MB_WHP_Wallet_ZaloPay';
                }
            }
            if ($whp_woocommerce_wallet_vnpay) {
                $name =  "class.wps-wallet-vnpay";
                $fileURL = plugin_dir_path(__FILE__) . "wallet/{$name}.php";
                if (file_exists($fileURL)) {
                    require_once($fileURL);
                    $gateways[] = 'MB_WHP_Wallet_VNPAY';
                }
            }
            if ($whp_woocommerce_wallet_shopeepay) {
                $name =  "class.wps-wallet-shopeepay";
                $fileURL = plugin_dir_path(__FILE__) . "wallet/{$name}.php";
                if (file_exists($fileURL)) {
                    require_once($fileURL);
                    $gateways[] = 'MB_WHP_Wallet_ShopeePay';
                }
            }
            return $gateways;
        }
        // end gateway wallet

        // maintenance

        public function whp_maintenance()
        {
            $fields = whp_get_maintenance_fields();
            foreach ($fields as $key => $field) {
                $$field = whp_get_setting($field);
            }

            // Đọc từ whp_setting array (format mới); chỉ fallback sang individual option nếu key chưa tồn tại
            $_whp_setting_arr = get_option( 'whp_setting', [] );
            $whp_maintenance_active = array_key_exists( 'whp_maintenance_active', $_whp_setting_arr )
                ? $_whp_setting_arr['whp_maintenance_active']
                : get_option( 'whp_maintenance_active', '' );

            // Đọc nội dung từ whp_setting hoặc individual options
            $whp_maintenance_heading     = whp_get_option('whp_maintenance_heading')     ?: get_option('whp_maintenance_heading', '');
            $whp_maintenance_heading_sub = whp_get_option('whp_maintenance_heading_sub') ?: get_option('whp_maintenance_heading_sub', '');
            $whp_maintenance_desc        = whp_get_option('whp_maintenance_desc')        ?: get_option('whp_maintenance_desc', '');
            $whp_maintenance_title       = whp_get_option('whp_maintenance_title')       ?: get_option('whp_maintenance_title', '');
            $whp_maintenance_logo        = whp_get_option('whp_maintenance_logo')        ?: '';
            $whp_maintenance_countdown   = whp_get_option('whp_maintenance_countdown')   ?: '';
            $whp_maintenance_phone       = whp_get_option('whp_maintenance_phone')       ?: '';
            $whp_maintenance_email       = whp_get_option('whp_maintenance_email')       ?: '';
            $whp_maintenance_facebook    = whp_get_option('whp_maintenance_facebook')    ?: '';
            $whp_maintenance_youtube     = whp_get_option('whp_maintenance_youtube')     ?: '';
            $whp_maintenance_zalo        = whp_get_option('whp_maintenance_zalo')        ?: '';
            $whp_maintenance_tiktok      = whp_get_option('whp_maintenance_tiktok')      ?: '';

            $tpl_map = [
                'dark'         => 'maintenance_mode.php',
                'light'        => 'maintenance_mode_light.php',
                'gradient'     => 'maintenance_mode_gradient.php',
                'construction' => 'maintenance_mode_construction.php',
                'cyberpunk'    => 'maintenance_mode_cyberpunk.php',
                'corporate'    => 'maintenance_mode_corporate.php',
            ];

            // ── PATH 1: PREVIEW MODE ──────────────────────────────────────────
            // Tách hoàn toàn khỏi maintenance logic — chỉ cần param + quyền admin
            if ( isset( $_GET['wpaap_maintenance_preview'] ) && '1' === $_GET['wpaap_maintenance_preview'] ) {
                $is_valid_preview = false;

                // Kiểm tra HMAC (dùng cho iframe — không cần cookie)
                $pt_ts   = isset( $_GET['pt_ts'] ) ? intval( $_GET['pt_ts'] ) : 0;
                $pt_h    = isset( $_GET['pt_h'] )  ? sanitize_text_field( $_GET['pt_h'] ) : '';
                if ( $pt_ts > 0 && $pt_h ) {
                    $expected = hash_hmac( 'sha256', 'wpaap_preview:' . $pt_ts, wp_salt( 'auth' ) );
                    if ( ( time() - $pt_ts ) < 7200 && hash_equals( $expected, $pt_h ) ) {
                        $is_valid_preview = true;
                    }
                }

                // Fallback: admin đang đăng nhập (truy cập trực tiếp)
                if ( ! $is_valid_preview && current_user_can( 'manage_options' ) ) {
                    $is_valid_preview = true;
                }

                if ( $is_valid_preview && ! is_admin() ) {
                    $tpl_key = isset( $_GET['wpaap_tpl_preview'] )
                        ? sanitize_key( $_GET['wpaap_tpl_preview'] )
                        : ( whp_get_option( 'whp_maintenance_template' ) ?: 'dark' );
                    $tpl_file = $tpl_map[ $tpl_key ] ?? 'maintenance_mode.php';
                    require( $this->pathViewPages . $tpl_file );
                    exit;
                }
                return; // preview param có nhưng không hợp lệ → không làm gì
            }

            // ── PATH 2: MAINTENANCE ACTIVE ────────────────────────────────────
            if ( $whp_maintenance_active ) {
                global $pagenow;
                $is_admin_user = is_user_logged_in() && current_user_can( 'manage_options' );
                $is_clear_cache = isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === 'clear-cache.php';
                $is_auto_login = isset($_SERVER['SCRIPT_NAME']) && basename($_SERVER['SCRIPT_NAME']) === 'auto-login.php';
                // Quản trị viên duyệt frontend → bypass hoàn toàn
                if ( $pagenow !== 'wp-login.php' && ! is_admin() && ! $is_admin_user && ! $is_clear_cache && ! $is_auto_login ) {
                    // Track maintenance visit stats
                    $today     = gmdate( 'Y_m_d' );
                    $stats_key = 'wpaap_maint_stats_' . $today;
                    $stats     = get_option( $stats_key, [ 'hits' => 0, 'unique_ips' => [] ] );
                    $stats['hits']++;
                    $ip_hash = hash( 'sha256', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1' );
                    if ( ! in_array( $ip_hash, $stats['unique_ips'], true ) ) {
                        $stats['unique_ips'][] = $ip_hash;
                        if ( count( $stats['unique_ips'] ) > 500 ) {
                            array_shift( $stats['unique_ips'] );
                        }
                    }
                    update_option( $stats_key, $stats, false );

                    status_header( 503 );
                    $tpl_key  = whp_get_option( 'whp_maintenance_template' ) ?: 'dark';
                    $tpl_file = $tpl_map[ $tpl_key ] ?? 'maintenance_mode.php';
                    require( $this->pathViewPages . $tpl_file );
                    exit;
                }
            }
        }
        // popup preview — HMAC signed URL, renders standalone popup page
        public function whp_popup_preview()
        {
            if ( ! isset( $_GET['wpaap_popup_preview'] ) || '1' !== $_GET['wpaap_popup_preview'] ) return;

            $pt_ts = intval( $_GET['pt_ts'] ?? 0 );
            $pt_h  = sanitize_text_field( $_GET['pt_h']  ?? '' );
            $is_valid = false;
            if ( $pt_ts > 0 && $pt_h ) {
                $expected = hash_hmac( 'sha256', 'wpaap_popup_preview:' . $pt_ts, wp_salt( 'auth' ) );
                if ( ( time() - $pt_ts ) < 7200 && hash_equals( $expected, $pt_h ) ) {
                    $is_valid = true;
                }
            }
            if ( ! $is_valid && current_user_can( 'manage_options' ) ) $is_valid = true;
            if ( ! $is_valid ) return;

            // Đọc từ GET params (real-time preview) hoặc fallback về DB
            $type        = isset($_GET['pp_type'])   ? sanitize_key($_GET['pp_type'])        : (whp_get_option('whp_popup_type') ?: '0');
            $form_source = isset($_GET['pp_fsrc'])   ? sanitize_key($_GET['pp_fsrc'])        : (whp_get_option('whp_popup_form_source') ?: 'email');
            $form_id     = isset($_GET['pp_fid'])    ? intval($_GET['pp_fid'])                : intval(whp_get_option('whp_popup_form_id') ?: 0);
            $title       = isset($_GET['pp_title'])  ? sanitize_text_field($_GET['pp_title']) : (whp_get_option('whp_popup_title') ?: 'Đăng ký nhận ưu đãi');
            $sub         = isset($_GET['pp_sub'])    ? sanitize_text_field($_GET['pp_sub'])   : (whp_get_option('whp_popup_sub_title') ?: 'Nhận ngay ưu đãi độc quyền từ chúng tôi');
            $btn         = !empty($_GET['pp_btn'])   ? sanitize_text_field($_GET['pp_btn'])   : (whp_get_option('whp_popup_button') ?: 'Đăng ký ngay');
            $image       = whp_get_option('whp_popup_image_banner') ?: '';
            $fb          = whp_get_option('whp_popup_facebook');
            $yt          = whp_get_option('whp_popup_youtube');
            $ig          = whp_get_option('whp_popup_instagram');
            $tt          = whp_get_option('whp_popup_tiktok');

            // Lấy tên + shortcode form nếu chọn form có sẵn
            $form_shortcode = '';
            $form_name = '';
            if ( $form_source === 'form' && $form_id > 0 ) {
                $form_post = get_post( $form_id );
                if ( $form_post ) {
                    $form_name = $form_post->post_title;
                    switch ( $form_post->post_type ) {
                        case 'wpcf7_contact_form': $form_shortcode = '[contact-form-7 id="' . $form_id . '"]'; break;
                        case 'wpforms':            $form_shortcode = '[wpforms id="' . $form_id . '"]'; break;
                        case 'frm_form':           $form_shortcode = '[formidable id="' . $form_id . '"]'; break;
                        case 'fluentform':         $form_shortcode = '[fluentform id="' . $form_id . '"]'; break;
                    }
                }
            }
            ?><!DOCTYPE html>
<html><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="<?php echo esc_url($this->pathAsset . 'css/popup.css'); ?>?ver=<?php echo time(); ?>">
<style>
html,body{margin:0;padding:0;min-height:100vh;background:#f0f2f8;}
/* Fake website background */
.whp-preview-site{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;}
.whp-preview-nav{background:#fff;height:52px;display:flex;align-items:center;padding:0 24px;gap:20px;box-shadow:0 1px 3px rgba(0,0,0,.08);}
.whp-preview-nav-logo{width:80px;height:10px;border-radius:5px;background:#e2e8f0;}
.whp-preview-nav-links{display:flex;gap:14px;}
.whp-preview-nav-links span{width:36px;height:8px;border-radius:4px;background:#e2e8f0;}
.whp-preview-nav-btn{margin-left:auto;width:60px;height:26px;border-radius:6px;background:#dbeafe;}
.whp-preview-hero{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 50%,#a78bfa 100%);padding:40px 24px 50px;display:flex;flex-direction:column;align-items:center;gap:12px;}
.whp-preview-hero-t{width:180px;height:14px;border-radius:7px;background:rgba(255,255,255,.45);}
.whp-preview-hero-s{width:240px;height:9px;border-radius:4.5px;background:rgba(255,255,255,.3);}
.whp-preview-hero-s2{width:200px;height:9px;border-radius:4.5px;background:rgba(255,255,255,.22);}
.whp-preview-hero-btn{margin-top:6px;width:90px;height:30px;border-radius:8px;background:rgba(255,255,255,.35);}
.whp-preview-body{padding:24px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;}
.whp-preview-card{background:#fff;border-radius:10px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,.06);}
.whp-preview-card-img{height:48px;border-radius:6px;background:linear-gradient(135deg,#e0e7ff,#ede9fe);margin-bottom:10px;}
.whp-preview-card-t{height:8px;border-radius:4px;background:#e2e8f0;margin-bottom:7px;}
.whp-preview-card-t2{height:7px;border-radius:3.5px;background:#f1f5f9;width:75%;}
/* Banner preview */
.whp-banner-preview{position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:99999;padding:20px;}
.whp-banner-box{border-radius:12px;overflow:hidden;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.35);position:relative;}
.whp-banner-close{position:absolute;top:10px;right:12px;font-size:24px;color:#fff;z-index:3;text-shadow:0 1px 4px rgba(0,0,0,.5);cursor:pointer;}
.whp-banner-placeholder{width:100%;min-height:260px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px;opacity:.7;}
</style>
</head><body>
<!-- Fake website background -->
<div class="whp-preview-site">
    <div class="whp-preview-nav">
        <div class="whp-preview-nav-logo"></div>
        <div class="whp-preview-nav-links"><span></span><span></span><span></span><span></span></div>
        <div class="whp-preview-nav-btn"></div>
    </div>
    <div class="whp-preview-hero">
        <div class="whp-preview-hero-t"></div>
        <div class="whp-preview-hero-s"></div>
        <div class="whp-preview-hero-s2"></div>
        <div class="whp-preview-hero-btn"></div>
    </div>
    <div class="whp-preview-body">
        <?php for($i=0;$i<6;$i++): ?>
        <div class="whp-preview-card"><div class="whp-preview-card-img"></div><div class="whp-preview-card-t"></div><div class="whp-preview-card-t2"></div></div>
        <?php endfor; ?>
    </div>
</div>
<?php if ( $type == '1' ): ?>
    <div class="whp-banner-preview">
        <div class="whp-banner-box">
            <span class="whp-banner-close">×</span>
            <?php if ( $image ): ?>
                <img src="<?php echo esc_url($image); ?>" alt="Banner" style="width:100%;display:block;">
            <?php else: ?>
                <div class="whp-banner-placeholder">Chưa có ảnh banner</div>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div id="whp-popup" class="whp-popup">
        <div class="whp-popup-background"></div>
        <div class="center<?php echo ( $form_source === 'form' && $form_shortcode ) ? ' whp-form-mode' : ''; ?>">
            <div class="modal-box show-modal">
                <div class="icon-close">×</div>
                <?php if ( $form_source === 'form' && $form_shortcode ): ?>
                    <?php if ( $title || $sub ): ?>
                    <div class="whp-popup-header">
                        <div class="whp-popup-header-icon">
                            <span class="fas fa-comments"></span>
                        </div>
                        <div class="whp-popup-header-body">
                            <?php if ( $title ): ?><h2><?php echo esc_html( $title ); ?></h2><?php endif; ?>
                            <?php if ( $sub ):   ?><p><?php echo esc_html( $sub );   ?></p><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="whp-embedded-form">
                        <?php echo do_shortcode( $form_shortcode ); ?>
                    </div>
                <?php elseif ( $form_source === 'form' ): ?>
                    <div style="padding:12px 0;text-align:center;">
                        <div style="font-size:40px;margin-bottom:12px;">📋</div>
                        <div style="font-size:18px;font-weight:700;color:#0f172a;margin-bottom:8px;">Form chưa được chọn</div>
                        <div style="font-size:14px;color:#64748b;line-height:1.5;">Vui lòng chọn form trong cài đặt và lưu lại.</div>
                    </div>
                <?php else: ?>
                    <div class="icon-letter-1">
                        <span class="fas fa-envelope"></span>
                    </div>
                    <header><?php echo esc_html($title); ?></header>
                    <p><?php echo esc_html($sub); ?></p>
                    <form id="whp-form">
                        <input type="email" placeholder="Nhập email của bạn..." disabled>
                        <button type="button" id="whp-button-popup"><?php echo esc_html($btn); ?></button>
                    </form>
                    <?php if ($fb||$yt||$ig||$tt): ?>
                    <div class="icons">
                        <?php if($fb):?><a href="#"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                        <?php if($yt):?><a href="#"><i class="fab fa-youtube"></i></a><?php endif; ?>
                        <?php if($ig):?><a href="#"><i class="fab fa-instagram"></i></a><?php endif; ?>
                        <?php if($tt):?><a href="#"><i class="fab fa-tiktok"></i></a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<script>
(function(){
    function fitToFrame(){
        var popup  = document.getElementById('whp-popup');
        var center = popup && popup.querySelector('.center');
        var modal  = center && center.querySelector('.modal-box');
        if(!modal) return;
        // Reset trước khi tính lại
        center.style.transform = '';
        popup.style.alignItems = '';
        popup.style.paddingTop = '';
        var vh = window.innerHeight;
        var mh = modal.scrollHeight;
        var margin = 40; // px breathing room trên + dưới
        if(mh + margin * 2 > vh){
            var scale = (vh - margin * 2) / mh;
            center.style.transformOrigin = 'top center';
            center.style.transform = 'scale(' + scale.toFixed(4) + ')';
            popup.style.alignItems = 'flex-start';
            var topPad = Math.max(margin, Math.round((vh - mh * scale) / 2));
            popup.style.paddingTop = topPad + 'px';
        }
    }
    window.addEventListener('load', fitToFrame);
    window.addEventListener('resize', fitToFrame);
})();
</script>
</body></html><?php
            exit;
        }

        // popup
        public function whp_popup()
        {
            $fields = whp_get_popup_fields();

            foreach ($fields as $key => $field) {
                $$field = whp_get_option($field);
            }
            $whp_maintenance_active = whp_get_option('whp_maintenance_active');
            if ($whp_popup_active && !is_admin() && !$whp_maintenance_active) {
                add_action('wp_enqueue_scripts', function () {
                    wp_enqueue_script('whp-form-ajax', $this->pathAsset . 'js/ajax.js', array('jquery', 'whp-cookie'), time(), true);
                    wp_localize_script('whp-form-ajax', 'whp_popup_ajax', [
                        'url'     => admin_url('admin-ajax.php'),
                        'nonce'   => wp_create_nonce('whp_popup_subscribe_nonce'),
                        'isAdmin' => current_user_can('manage_options') ? 1 : 0,
                        'delay'   => intval(whp_get_option('whp_popup_delay') ?? 8),
                    ]);
                    wp_enqueue_style('whp-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', array(), null);
                });
                // pop up newsletter
                if ($whp_popup_type == 0) {
                    add_action('wp_footer', function () use ($fields) {
                        foreach ($fields as $key => $field) {
                            $$field = whp_get_option($field);
                        }
                        require($this->pathViewPages . 'popup-newsletter.php');
                    });
                }
                // pop up banner
                if ($whp_popup_type == 1) {
                    add_action('wp_footer', function () {
                        $whp_popup_image_banner  = whp_get_option('whp_popup_image_banner');
                        $whp_popup_link_redirect = whp_get_option('whp_popup_link_redirect');
                        require($this->pathViewPages . 'popup-banner.php');
                    });
                }
            }
        }

        // reponsive

        public function whp_reponsive()
        {
            $fields = whp_get_responsive_fields();

            foreach ($fields as $key => $field) {
                $$field = whp_get_option($field);
            }
            $content = '<style>';
            if ($whp_reponsive_mobile) {
                $content .= $whp_reponsive_mobile;
                //die($content);
            }
            if ($whp_reponsive_desktop) {
                $content .= $whp_reponsive_desktop;
            }
            if ($whp_reponsive_tablet) {
                $content .= $whp_reponsive_tablet;
            }
            $content .= ' </style>';
            add_action('wp_head', function () use ($content) {
                echo $content;
            });
        }

        // ─── THANK YOU PAGE ─────────────────────────────────────────────────

        public function whp_woo_thankyou()
        {
            if (!whp_get_setting('whp_woo_thankyou_enable')) return;

            add_filter('template_include', [$this, 'whp_thankyou_template'], 20);
            add_action('woocommerce_checkout_order_created', [$this, 'whp_thankyou_schedule_cancel']);
            add_action('woocommerce_store_api_checkout_order_processed', [$this, 'whp_thankyou_schedule_cancel']);
            add_action('whp_maybe_cancel_order', [$this, 'whp_cron_cancel_order'], 10, 1);
            add_action('wp_ajax_nopriv_whp_confirm_transfer', [$this, 'whp_ajax_confirm_transfer']);
            add_action('wp_ajax_whp_confirm_transfer', [$this, 'whp_ajax_confirm_transfer']);
            add_action('wp_ajax_nopriv_whp_cancel_order_expired', [$this, 'whp_ajax_cancel_expired']);
            add_action('wp_ajax_whp_cancel_order_expired', [$this, 'whp_ajax_cancel_expired']);
            add_action('wp_ajax_nopriv_whp_support_request', [$this, 'whp_ajax_support_request']);
            add_action('wp_ajax_whp_support_request', [$this, 'whp_ajax_support_request']);
            add_action('wp_ajax_nopriv_whp_upload_receipt', [$this, 'whp_ajax_upload_receipt']);
            add_action('wp_ajax_whp_upload_receipt', [$this, 'whp_ajax_upload_receipt']);
            add_action('wp_enqueue_scripts', [$this, 'whp_thankyou_enqueue']);
        }

        public function whp_thankyou_template($template)
        {
            global $wp;
            if (empty($wp->query_vars['order-received'])) return $template;
            $custom = MB_WHP_PATH_VIEW . 'frontend/pages/thankyou-template.php';
            return file_exists($custom) ? $custom : $template;
        }

        public function whp_thankyou_enqueue()
        {
            if (!is_wc_endpoint_url('order-received')) return;
            wp_enqueue_style('whp-thankyou', $this->pathAsset . 'css/mb-hp-thankyou.css', [], '1.0.0');

            // Google Fonts — enqueue only when a web font is selected
            $ty_google_font = [
                'inter'      => 'Inter:wght@400;500;600;700',
                'roboto'     => 'Roboto:wght@400;500;700',
                'be-vietnam' => 'Be+Vietnam+Pro:wght@400;500;600;700',
                'montserrat' => 'Montserrat:wght@400;500;600;700',
            ];
            $ty_font = whp_get_setting('whp_woo_thankyou_font') ?: 'inter';
            if (isset($ty_google_font[$ty_font])) {
                $gf_url = 'https://fonts.googleapis.com/css2?family=' . $ty_google_font[$ty_font] . '&display=swap';
                wp_enqueue_style('whp-thankyou-font', $gf_url, [], null);
            }

            wp_enqueue_script('whp-thankyou', $this->pathAsset . 'js/mb-hp-thankyou.js', ['jquery'], '1.0.0', true);

            $order_id = absint(get_query_var('order-received'));
            $order    = $order_id ? wc_get_order($order_id) : null;
            if (!$order) return;

            $wallet_info  = $this->whp_get_wallet_info($order);
            $countdown_en = whp_get_setting('whp_woo_thankyou_countdown_enable');
            $expire_at    = 0;
            if ($countdown_en && $wallet_info && $order->has_status('pending')) {
                $expire_at = (int) $order->get_meta('_whp_payment_expires');
            }

            wp_localize_script('whp-thankyou', 'whpThankyou', [
                'ajax_url'         => admin_url('admin-ajax.php'),
                'order_id'         => $order_id,
                'expire_at'        => $expire_at,
                'transfer_nonce'   => wp_create_nonce('whp_confirm_transfer_' . $order_id),
                'cancel_nonce'     => wp_create_nonce('whp_cancel_expired_' . $order_id),
                'support_nonce'    => wp_create_nonce('whp_support_request_' . $order_id),
                'acct_no'          => $wallet_info ? esc_js($wallet_info['number']) : '',
                'transfer_content' => $wallet_info ? esc_js($wallet_info['transfer_content']) : '',
                'order_total'      => $wallet_info ? esc_js(strip_tags($order->get_formatted_order_total())) : '',
                'show_copy_acct'   => (bool) whp_get_setting('whp_woo_thankyou_copy_account'),
                'show_copy_content'=> (bool) whp_get_setting('whp_woo_thankyou_copy_content'),
                'has_wallet'       => (bool) $wallet_info,
                'order_url'        => wc_get_account_endpoint_url('orders'),
                'shop_url'         => get_permalink(wc_get_page_id('shop')) ?: home_url('/'),
                'aipay_active'     => (bool) whp_get_setting('whp_aipay_enable'),
                'ocr_active'       => (bool) whp_get_setting('whp_aipay_ocr_enable'),
                'has_receipt'      => (bool) $order->get_meta('_whp_transfer_receipt'),
                'ai_result'        => $order->get_meta('_whp_ai_verify_result') ?: null,
                'order_status'     => $order->get_status(),
            ]);
        }

        private function whp_get_wallet_info($order)
        {
            $method_id = $order->get_payment_method();
            $map = [
                'MB_WHP_Wallet_MoMo'     => ['label' => 'MoMo',      'acct' => 'account_number', 'name' => 'account_name',  'qr' => 'image_url'],
                'MB_WHP_Wallet_ZaloPay'  => ['label' => 'ZaloPay',   'acct' => 'number_zalopay', 'name' => 'name_zalopay',  'qr' => 'zalopay_image_url'],
                'MB_WHP_Wallet_VNPAY'    => ['label' => 'VNPAY',     'acct' => 'number_vnpay',   'name' => 'name_vnpay',    'qr' => 'vnpay_image_url'],
                'MB_WHP_Wallet_ShopeePay'=> ['label' => 'ShopeePay', 'acct' => 'number_shopee',  'name' => 'name_shopee',   'qr' => 'shopeepay_image_url'],
            ];
            if (!isset($map[$method_id])) return null;
            $m  = $map[$method_id];
            $gw = WC()->payment_gateways()->payment_gateways()[$method_id] ?? null;
            if (!$gw) return null;
            return [
                'label'            => $m['label'],
                'method_id'        => $method_id,
                'number'           => $gw->get_option($m['acct']),
                'name'             => $gw->get_option($m['name']),
                'qr_url'           => $gw->get_option($m['qr']),
                'transfer_content' => 'DH' . $order->get_order_number(),
            ];
        }

        public function whp_thankyou_schedule_cancel($order)
        {
            if (!whp_get_setting('whp_woo_thankyou_countdown_enable')) return;
            $minutes = (int) (whp_get_setting('whp_woo_thankyou_countdown_minutes') ?: 30);
            $order_id = is_object($order) ? $order->get_id() : (int) $order;
            $expire_at = time() + ($minutes * 60);
            $wc_order = wc_get_order($order_id);
            if ($wc_order) {
                $wc_order->update_meta_data('_whp_payment_expires', $expire_at);
                $wc_order->save();
            }
            wp_schedule_single_event($expire_at, 'whp_maybe_cancel_order', [$order_id]);
        }

        public function whp_cron_cancel_order($order_id)
        {
            $order = wc_get_order($order_id);
            if (!$order || !$order->has_status('pending')) return;
            $order->update_status('cancelled', __('Tự động hủy: hết thời gian thanh toán.', 'wphp-wc'));
        }

        public function whp_ajax_confirm_transfer()
        {
            $order_id = absint($_POST['order_id'] ?? 0);
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'whp_confirm_transfer_' . $order_id)) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }
            $order = wc_get_order($order_id);
            if (!$order) wp_send_json_error(['message' => 'Order not found']);

            $sender_name  = sanitize_text_field($_POST['sender_name'] ?? '');
            $bank         = sanitize_text_field($_POST['bank'] ?? '');
            $last4        = preg_replace('/\D/', '', $_POST['last4'] ?? '');
            $last4        = substr($last4, -4);
            $notes        = sanitize_textarea_field($_POST['notes'] ?? '');
            $receipt_url  = esc_url_raw($_POST['receipt_url'] ?? '');
            if ( $receipt_url && ! wp_http_validate_url( $receipt_url ) ) {
                $receipt_url = '';
            }

            // Save structured data as order meta
            if ($sender_name) $order->update_meta_data('_whp_transfer_sender',  $sender_name);
            if ($bank)        $order->update_meta_data('_whp_transfer_bank',    $bank);
            if ($last4)       $order->update_meta_data('_whp_transfer_last4',   $last4);
            if ($receipt_url) $order->update_meta_data('_whp_transfer_receipt', $receipt_url);
            if ($notes)       $order->update_meta_data('_whp_transfer_notes',   $notes);
            $order->update_meta_data('_whp_transfer_confirmed_at', current_time('mysql', true));

            // Move order from pending → on-hold so countdown stops and admin knows to verify
            if ($order->has_status('pending')) {
                $order->update_status('on-hold', sprintf('Khách hàng xác nhận đã chuyển khoản lúc %s.', current_time('d/m/Y H:i:s')));
            } else {
                $order->save();
            }

            $note_parts = [];
            if ($sender_name) $note_parts[] = "Người chuyển: {$sender_name}";
            if ($bank)        $note_parts[] = "Ngân hàng: {$bank}";
            if ($last4)       $note_parts[] = "4 số cuối TK: {$last4}";
            if ($notes)       $note_parts[] = "Ghi chú: {$notes}";
            if ($receipt_url) $note_parts[] = 'Biên lai: <a href="' . esc_url($receipt_url) . '" target="_blank">&#128247; Xem ảnh biên lai</a>';
            if ($note_parts)  $order->add_order_note(implode('<br>', $note_parts));

            $this->whp_dispatch_transfer_notifications($order, $sender_name, $bank, $last4, $receipt_url);

            wp_send_json_success(['message' => 'Confirmed']);
        }

        public function whp_ajax_upload_receipt()
        {
            $order_id = absint($_POST['order_id'] ?? 0);
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'whp_confirm_transfer_' . $order_id)) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }
            if (empty($_FILES['receipt']['name'])) {
                wp_send_json_error(['message' => 'No file']);
            }

            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if ($_FILES['receipt']['size'] > 5 * 1024 * 1024) {
                wp_send_json_error(['message' => 'File too large (max 5MB)']);
            }
            // Validate actual file content — never trust browser-supplied type
            $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
            $real_type = $finfo ? finfo_file($finfo, $_FILES['receipt']['tmp_name']) : mime_content_type($_FILES['receipt']['tmp_name']);
            if ($finfo) finfo_close($finfo);
            if (!in_array($real_type, $allowed, true)) {
                wp_send_json_error(['message' => 'Invalid file type']);
            }

            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $overrides = ['test_form' => false, 'test_size' => true];
            $uploaded  = wp_handle_upload($_FILES['receipt'], $overrides);

            if (isset($uploaded['error'])) {
                wp_send_json_error(['message' => $uploaded['error']]);
            }

            wp_send_json_success(['url' => $uploaded['url']]);
        }

        private function whp_build_transfer_email_html($order_num, $customer, $phone, $total, $sender_name, $bank, $last4, $notes, $receipt_url, $order_link)
        {
            $rows = [
                ['Mã đơn hàng',  "#<strong>{$order_num}</strong>"],
                ['Khách hàng',   esc_html($customer)],
                ['SĐT',          esc_html($phone)],
                ['Tổng tiền',    "<strong style='color:#e11d48'>{$total}</strong>"],
            ];
            if ($sender_name) $rows[] = ['Người chuyển', esc_html($sender_name)];
            if ($bank)        $rows[] = ['Ngân hàng',    esc_html($bank)];
            if ($last4)       $rows[] = ['4 số cuối TK', '<strong>' . esc_html($last4) . '</strong>'];
            if ($notes)       $rows[] = ['Ghi chú',      esc_html($notes)];

            $rows_html = '';
            foreach ($rows as [$label, $value]) {
                $rows_html .= "
                <tr>
                  <td style='padding:10px 14px;background:#f8fafc;font-size:13px;color:#64748b;white-space:nowrap;border-bottom:1px solid #e2e8f0;width:140px'>{$label}</td>
                  <td style='padding:10px 14px;font-size:13px;color:#0f172a;border-bottom:1px solid #e2e8f0'>{$value}</td>
                </tr>";
            }
            if ($receipt_url) {
                $rows_html .= "
                <tr>
                  <td style='padding:10px 14px;background:#f8fafc;font-size:13px;color:#64748b;white-space:nowrap;width:140px'>Ảnh biên lai</td>
                  <td style='padding:10px 14px'>
                    <a href='" . esc_url($receipt_url) . "' target='_blank' style='display:inline-block;background:#1d4ed8;color:#fff;text-decoration:none;padding:7px 16px;border-radius:6px;font-size:13px;font-weight:600'>
                      &#128247;&nbsp; Xem ảnh biên lai
                    </a>
                  </td>
                </tr>";
            }

            return "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body style='margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f1f5f9;padding:32px 0'>
<tr><td align='center'>
<table width='560' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08)'>

  <!-- Header -->
  <tr><td style='background:linear-gradient(135deg,#1e40af,#3b82f6);padding:28px 32px;text-align:center'>
    <p style='margin:0 0 4px;font-size:11px;color:rgba(255,255,255,.7);letter-spacing:2px;text-transform:uppercase'>Thông báo từ cửa hàng</p>
    <h1 style='margin:0;font-size:22px;color:#fff;font-weight:700'>Khách xác nhận đã chuyển khoản</h1>
    <p style='margin:8px 0 0;font-size:14px;color:rgba(255,255,255,.85)'>Đơn hàng <strong>#" . esc_html($order_num) . "</strong> — Cần xác minh</p>
  </td></tr>

  <!-- Alert banner -->
  <tr><td style='padding:0'>
    <div style='background:#fef9c3;border-bottom:1px solid #fcd34d;padding:12px 24px;display:flex;align-items:center;text-align:center'>
      <p style='margin:0;font-size:13px;color:#92400e'>&#9888; Vui lòng kiểm tra ảnh biên lai và xác nhận đơn hàng trong vòng sớm nhất</p>
    </div>
  </td></tr>

  <!-- Info table (receipt row included when present) -->
  <tr><td style='padding:24px 24px 0'>
    <table width='100%' cellpadding='0' cellspacing='0' style='border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;border-collapse:collapse'>
      {$rows_html}
    </table>
  </td></tr>

  <!-- CTA button -->
  <tr><td style='padding:24px;text-align:center'>
    <a href='" . esc_url($order_link) . "' style='display:inline-block;background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff;font-size:14px;font-weight:700;padding:14px 36px;border-radius:10px;text-decoration:none;letter-spacing:.3px'>
      Xem &amp; Xác nhận đơn hàng
    </a>
  </td></tr>

  <!-- Footer -->
  <tr><td style='border-top:1px solid #f1f5f9;padding:16px 24px;text-align:center'>
    <p style='margin:0;font-size:11px;color:#94a3b8'>Email tự động từ hệ thống WP Helper · Không trả lời email này</p>
  </td></tr>

</table>
</td></tr></table>
</body></html>";
        }

        private function whp_dispatch_transfer_notifications($order, $sender_name = '', $bank = '', $last4 = '', $receipt_url = '')
        {
            $order_num  = $order->get_order_number();
            $order_id   = $order->get_id();
            $customer   = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
            $phone      = $order->get_billing_phone();
            $total      = $order->get_formatted_order_total();
            $order_link = admin_url('post.php?post=' . $order_id . '&action=edit');
            $notes      = $order->get_meta('_whp_transfer_notes');

            $html_body = $this->whp_build_transfer_email_html(
                $order_num, $customer, $phone, $total,
                $sender_name, $bank, $last4, $notes, $receipt_url, $order_link
            );
            $smtp_from    = whp_get_setting('whp_smtp_email') ?: get_option('admin_email');
            $smtp_name    = whp_get_setting('whp_smtp_from_name') ?: get_bloginfo('name');
            $html_headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $smtp_name . ' <' . $smtp_from . '>',
            ];

            // --- Legacy thank you page settings (email) ---
            $ty_settings = [];
            foreach (whp_get_woo_thankyou_fields() as $f) {
                $ty_settings[$f] = whp_get_setting($f);
            }

            if (!empty($ty_settings['whp_woo_thankyou_transfer_email'])) {
                $to      = get_option('admin_email');
                $subject = "Xác nhận chuyển khoản - Đơn hàng #{$order_num}";
                wp_mail($to, $subject, $html_body, $html_headers);
            }

            // --- AI Payment module notifications ---
            if (!whp_get_setting('whp_aipay_enable')) return;

            $detail = "Đơn hàng: #{$order_num}\nKhách: {$customer}\nSĐT: {$phone}\nTổng: {$total}"
                    . ($sender_name ? "\nNgười CK: {$sender_name}" : '')
                    . ($bank        ? "\nNgân hàng: {$bank}"       : '')
                    . ($last4       ? "\n4 số cuối: {$last4}"      : '')
                    . ($receipt_url ? "\nBiên lai: {$receipt_url}" : '');

            // Telegram
            $tg_token   = whp_get_setting('whp_aipay_telegram_token');
            $tg_chat_id = whp_get_setting('whp_aipay_telegram_chat_id');
            if ($tg_token && $tg_chat_id && whp_get_setting('whp_aipay_telegram_transfer')) {
                $msg = "💸 <b>Xác nhận CK (AI Payment)</b>\n" . $detail;
                $url = "https://api.telegram.org/bot{$tg_token}/sendMessage?chat_id={$tg_chat_id}&text=" . rawurlencode($msg) . "&parse_mode=html";
                wp_remote_get($url, ['timeout' => 10, 'blocking' => false]);
            }

            // Discord
            $dc_webhook = whp_get_setting('whp_aipay_discord_webhook');
            if ($dc_webhook && whp_get_setting('whp_aipay_discord_transfer')) {
                wp_remote_post($dc_webhook, [
                    'timeout'  => 10,
                    'blocking' => false,
                    'headers'  => ['Content-Type' => 'application/json'],
                    'body'     => wp_json_encode(['content' => "💸 **Xác nhận CK** — {$detail}"]),
                ]);
            }

            // Custom webhook
            $wh_url    = whp_get_setting('whp_aipay_webhook_url');
            $wh_method = whp_get_setting('whp_aipay_webhook_method') ?: 'POST';
            if ($wh_url) {
                $payload = [
                    'event'       => 'transfer_confirm',
                    'order_id'    => $order_id,
                    'order_num'   => $order_num,
                    'customer'    => $customer,
                    'phone'       => $phone,
                    'total'       => $order->get_total(),
                    'sender_name' => $sender_name,
                    'bank'        => $bank,
                    'last4'       => $last4,
                    'receipt_url' => $receipt_url,
                ];
                $args = ['timeout' => 10, 'blocking' => false, 'body' => $payload];
                if ($wh_method === 'GET') {
                    wp_remote_get(add_query_arg($payload, $wh_url), $args);
                } else {
                    wp_remote_post($wh_url, $args);
                }
            }

            // Email from AI payment settings
            $ai_email = whp_get_setting('whp_aipay_email_address') ?: get_option('admin_email');
            if ($ai_email && whp_get_setting('whp_aipay_email_transfer')) {
                $subject = "Xác nhận chuyển khoản - Đơn hàng #{$order_num}";
                wp_mail($ai_email, $subject, $html_body, $html_headers);
            }
        }

        public function whp_ajax_cancel_expired()
        {
            $order_id = absint($_POST['order_id'] ?? 0);
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'whp_cancel_expired_' . $order_id)) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }
            $this->whp_cron_cancel_order($order_id);
            wp_send_json_success(['message' => 'Cancelled']);
        }

        public function whp_ajax_support_request()
        {
            $order_id = absint($_POST['order_id'] ?? 0);
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'whp_support_request_' . $order_id)) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }
            $name    = sanitize_text_field($_POST['name'] ?? '');
            $phone   = sanitize_text_field($_POST['phone'] ?? '');
            $message = sanitize_textarea_field($_POST['message'] ?? '');
            if (!$name || !$message) wp_send_json_error(['message' => 'Missing fields']);

            $order = wc_get_order($order_id);
            if ($order) {
                $order->add_order_note(sprintf(
                    "Yêu cầu hỗ trợ từ khách hàng:\nTên: %s\nSĐT: %s\nNội dung: %s",
                    $name, $phone, $message
                ));
            }
            wp_send_json_success(['message' => 'Support request received']);
        }
    }
}
