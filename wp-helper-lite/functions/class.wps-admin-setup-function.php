<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
if (!class_exists('MB_WHP_Admin_Setup_Function')) {
    class MB_WHP_Admin_Setup_Function
    {
        private $pathView = MB_WHP_PATH_VIEW . 'admin/pages/';
        private $pathAsset = MB_WHP_URL . 'assets/admin/';
        private $whp_list_tab;
        private $whp_current_tab;
        private $whp_page;
        private $MB_WHP_Data_Old;
        private $whp_post_type;
        private $MB_WHP_Wallet_Momo;
        function __construct()
        {

            $this->MB_WHP_Data_Old = new MB_WHP_Data_Old();

            add_action('admin_enqueue_scripts', [$this, 'include_style']);
            add_action('admin_enqueue_scripts', [$this, 'include_script']);
            add_action('admin_head', [$this, 'suppress_admin_notices'], 1);
            add_action('admin_menu', [$this, 'whp_admin_menu']);
            add_action('admin_menu', function() {
                global $submenu;
                if (!empty($submenu['mb-wphelper'])) {
                    foreach ($submenu['mb-wphelper'] as $key => $item) {
                        // WordPress auto-adds the parent as first submenu entry.
                        // Keep it (so WP uses mb-wphelper as parent href) but rename to "Tổng quan".
                        if (in_array('mb-wphelper', $item, true)) {
                            $submenu['mb-wphelper'][$key][0] = __('Tổng quan', 'whp');
                            $submenu['mb-wphelper'][$key][3] = __('Tổng quan', 'whp');
                            break;
                        }
                    }
                }
            }, 9999);
            $this->whp_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
            $this->whp_post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
            add_action('init', function() {
                $this->whp_list_tab = whp_get_list_tab();
                $this->whp_current_tab = whp_get_current_tab();
            }, 0);
        }


        public function suppress_admin_notices()
        {
            $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
            if (strpos($page, 'mb-wphelper') !== false || strpos($page, 'wp-ai-auto-poster') !== false) {
                remove_all_actions('admin_notices');
                remove_all_actions('all_admin_notices');
                remove_all_actions('user_admin_notices');
                remove_all_actions('network_admin_notices');
            }
        }


        public function include_style()
        {

            wp_enqueue_style('app', $this->pathAsset . 'css/app.css', array(), time(), 'all');
            wp_enqueue_style('responsive', $this->pathAsset . 'css/responsive.css', array(), time(), 'all');
            $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
            if ($page === 'mb-wphelper-reponsive') {
                wp_enqueue_style('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.css', array(), '5.62.0', 'all');
                wp_enqueue_style('codemirror-hint-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/addon/hint/show-hint.min.css', array(), '5.62.0', 'all');
            }

            // Load AI Auto Poster CSS
            if ( strpos( $page, 'mb-wphelper-ai' ) !== false || strpos( $page, 'wp-ai-auto-poster' ) !== false ) {
                wp_enqueue_style( 'wpaap-admin-style', MB_WHP_URL . 'assets/admin/css/wpaap-style.css', array(), time(), 'all' );
            }

            // Load frontend widget CSS + FontAwesome for Live Preview on contact settings page
            if ( $page === 'mb-wphelper-contact' ) {
                wp_enqueue_style( 'whp-frontend-app', MB_WHP_URL . 'assets/frontend/css/app.css', array(), time(), 'all' );
                wp_enqueue_style( 'whp-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), null, 'all' );
            }
        }
        public function include_script()
        {
            wp_enqueue_script('dirtyform', 'https://cdn.jsdelivr.net/jquery.dirtyforms/2.0.0/jquery.dirtyforms.min.js', array('jquery'), time(), true);
            wp_enqueue_media();
            wp_enqueue_script('app', $this->pathAsset . 'js/app.js', array('jquery'), time(), true);

            // Load AI Auto Poster JS
            $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
            if ( strpos( $page, 'mb-wphelper-ai' ) !== false || strpos( $page, 'wp-ai-auto-poster' ) !== false ) {
                wp_enqueue_script( 'wpaap-admin-script', MB_WHP_URL . 'assets/admin/js/wpaap-script.js', array('jquery'), time(), true );
                $ai_connected = false;
                if ( function_exists( 'wpaap_is_provider_connected' ) ) {
                    foreach ( [ 'google', 'anthropic', 'openai' ] as $_p ) {
                        if ( wpaap_is_provider_connected( $_p ) ) { $ai_connected = true; break; }
                    }
                } else {
                    $ai_connected = get_option( 'wpaap_core_connected', 'no' ) === 'yes';
                }
                wp_localize_script( 'wpaap-admin-script', 'wpaap_ajax', array(
                    'ajax_url'     => admin_url( 'admin-ajax.php' ),
                    'nonce'        => wp_create_nonce( 'wpaap_generate_nonce' ),
                    'ai_connected' => $ai_connected ? '1' : '0',
                    'conn_i18n'    => array(
                        'title'    => __( 'Xác nhận kết nối', 'whp' ),
                        'provider' => __( 'Nhà cung cấp:', 'whp' ),
                        'bullet1'  => sprintf( __( 'API Key được %s và lưu trữ an toàn vào cơ sở dữ liệu website của bạn.', 'whp' ), '<strong>' . esc_html__( 'mã hóa AES-256', 'whp' ) . '</strong>' ),
                        'bullet2'  => sprintf( __( 'Chúng tôi %s khóa API của bạn với bất kỳ bên thứ ba nào.', 'whp' ), '<strong>' . esc_html__( 'không lưu trữ hoặc chia sẻ', 'whp' ) . '</strong>' ),
                        'bullet3'  => sprintf( __( 'Bạn có thể %s API Key bất kỳ lúc nào từ trang này.', 'whp' ), '<strong>' . esc_html__( 'ngắt kết nối và xóa', 'whp' ) . '</strong>' ),
                        'cancel'   => __( 'Hủy', 'whp' ),
                        'confirm'  => __( 'Đồng ý & Kết nối', 'whp' ),
                        'close'    => __( 'Đóng', 'whp' ),
                    ),
                ) );
            }

            if ($page === 'mb-wphelper-reponsive') {
                wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.js', array('jquery'), '5.62.0', true);
                wp_enqueue_script('codemirror-mode-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/mode/css/css.min.js', array('codemirror'), '5.62.0', true);
                wp_enqueue_script('codemirror-hint-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/addon/hint/css-hint.min.js', array('codemirror'), '5.62.0', true);
                wp_enqueue_script('codemirror-show-hint-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/addon/hint/show-hint.min.js', array('codemirror'), '5.62.0', true);
                wp_enqueue_script('codemirror-config', $this->pathAsset . 'lib/codemirror/js/config.js', array('codemirror', 'codemirror-mode-css'), '1.0', true);
            }
        }
        public function whp_admin_menu()
        {
            add_menu_page(
                __('WP Helper', 'whp'),
                __('WP Helper Premium', 'whp'),
                'manage_options',
                'mb-wphelper',
                [$this, 'whp_dashboard'],
                $this->pathAsset . "images/icon.svg",
                '2'
            );

            // Register items in the main list (including Tùy chỉnh cửa hàng nâng cao)
            foreach ($this->whp_list_tab as $itemTab) {
                add_submenu_page(
                    'mb-wphelper',
                    $itemTab['title'],
                    $itemTab['title'],
                    'manage_options',
                    $itemTab['slug'],
                    [$this, $itemTab['callback']],
                );
            }

            // Register mb-wphelper-security as hidden redirect to combined page
            add_submenu_page(null, 'Bảo mật', 'Bảo mật', 'manage_options',
                'mb-wphelper-security', [$this, 'whp_security']);

            // Register other WooCommerce tabs as hidden pages (parent slug null)
            // for safe redirection when old URLs are visited.
            foreach ( whp_get_woo_tab() as $itemTab ) {
                if ( $itemTab['slug'] === 'mb-wphelper-woocommerce-advance' ) {
                    continue;
                }
                add_submenu_page(
                    null,
                    $itemTab['title'],
                    $itemTab['title'],
                    'manage_options',
                    $itemTab['slug'],
                    [$this, $itemTab['callback']]
                );
            }
        }
        public function whp_dashboard()
        {
            require_once( $this->pathView . 'whp-dashboard.php' );
        }
        public function whp_contactChanel()
        {
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_contact_fields();
            $listPosition = whp_get_list_position();
            $this->whp_fields(
                $fields,
                'contact',
                [
                    'listPosition' => $listPosition,
                ]
            );
        }
        public function whp_code()
        {
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'Bạn không có quyền thực hiện thao tác này.', 'whp' ) );
            }
            $isSubmit = 0;
            $params = [];
            $option  = get_option('mbwp_helper', []);
            $optionNew  = get_option('whp_setting', []);
            $checkOption = whp_check_option();
            $whp_code_header = null;
            $whp_code_body = null;
            $whp_code_footer = null;
            if ($checkOption == 'old') {
            } else {
                $whp_code_header = whp_get_option('whp_code_header') ?? "";
                $whp_code_body = whp_get_option('whp_code_body') ?? "";
                $whp_code_footer = whp_get_option('whp_code_footer') ?? "";
            }
            if (isset($_POST['submit'])) {
                check_admin_referer( '_token', '_token' );
                unset($_POST['submit']);
                $isSubmit = 1;
                $params = sanitize_data($_POST);
                $params = $optionNew ? array_merge($optionNew, $params) : $params;
                $whp_code_header = $params['whp_code_header'] ?? "";
                $whp_code_body = $params['whp_code_body'] ?? "";
                $whp_code_footer = $params['whp_code_footer'] ?? "";
                update_option('whp_setting', $params);
            }
            $itemInfo = $this->whp_current_tab;
            require_once($this->pathView . 'code.php');
        }
        public function whp_smtpSetting()
        {
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_smtp_fields();
            $listSmtp = whp_get_list_smtp();
            $listSmtpSecurity = whp_get_list_smtp_security();
            $this->whp_fields(
                $fields,
                'smtp',
                [
                    'listSmtp' => $listSmtp,
                    'listSmtpSecurity' => $listSmtpSecurity,
                ]
            );
        }
        public function whp_maintenance()
        {
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_maintenance_fields();

            //  $listSmtp = whp_get_list_smtp();
            $list_layout = whp_get_list_layout_maintenance();
            $this->whp_fields(
                $fields,
                'maintenance',
                [
                    'list_layout' => $list_layout,
                ]
            );
        }
        public function whp_security()
        {
            $this->checkPlugin();
            wp_safe_redirect(admin_url('admin.php?page=mb-wphelper-extention&subtab=security'));
            exit;
        }
        public function whp_security_extention()
        {
            $this->checkPlugin();
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            require_once($this->pathView . 'security-extention-main.php');
        }

        public function whp_extention_content()
        {
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_extention_fields();
            $listEditor = whp_get_list_editor();
            $this->whp_fields(
                $fields,
                'extention',
                ['listEditor' => $listEditor]
            );
        }

        public function whp_security_content()
        {
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_security_fields();
            $this->whp_fields($fields, 'security');
        }

        public function whp_extention()
        {
            $this->checkPlugin();
            wp_safe_redirect(admin_url('admin.php?page=mb-wphelper-extention&subtab=extention'));
            exit;
        }

        public function whp_reponsive()
        {


            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_responsive_fields();
            $this->whp_fields(
                $fields,
                'reponsive',
            );
        }
        public function whp_woocommerce_cta()
        {
            if ( ! $this->checkPlugin() ) { $this->renderWoocommerceRequired(); return; }
            wp_safe_redirect(admin_url('admin.php?page=mb-wphelper-woocommerce-advance&subtab=cta'));
            exit;
        }

        public function whp_woocommerce_ecommerce()
        {
            if ( ! $this->checkPlugin() ) { $this->renderWoocommerceRequired(); return; }
            wp_safe_redirect(admin_url('admin.php?page=mb-wphelper-woocommerce-advance&subtab=ecommerce'));
            exit;
        }

        public function whp_woocommerce_payment()
        {
            if ( ! $this->checkPlugin() ) { $this->renderWoocommerceRequired(); return; }
            wp_safe_redirect(admin_url('admin.php?page=mb-wphelper-woocommerce-advance&subtab=payment'));
            exit;
        }

        public function whp_woocommerce_wallet()
        {
            if ( ! $this->checkPlugin() ) { $this->renderWoocommerceRequired(); return; }
            wp_safe_redirect(admin_url('admin.php?page=mb-wphelper-woocommerce-advance&subtab=wallet'));
            exit;
        }

        public function whp_woocommerce_advance()
        {
            if ( ! $this->checkPlugin() ) {
                $this->renderWoocommerceRequired();
                return;
            }
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            require_once($this->pathView . 'woocommerce-main.php');
        }

        public function whp_woocommerce_advance_content()
        {
            $isSubmit = 0;
            $tabs = whp_get_woo_tab();
            $this->whp_current_tab = $tabs[0];
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_woo_advance_fields();
            $this->whp_fields(
                $fields,
                'woocommerce/advance',
            );
        }

        public function whp_woocommerce_wallet_content()
        {
            $isSubmit = 0;
            $tabs = whp_get_woo_tab();
            $this->whp_current_tab = $tabs[1];
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_woo_wallet_fields();
            $listWallet = whp_get_list_wallet();
            $this->whp_fields(
                $fields,
                'woocommerce/wallet',
                [
                    'listWallet' => $listWallet,
                ]
            );
        }

        public function whp_woocommerce_payment_content()
        {
            $isSubmit = 0;
            $tabs = whp_get_woo_tab();
            $this->whp_current_tab = $tabs[2];
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_woo_payment_fields();
            $this->whp_fields(
                $fields,
                'woocommerce/payment',
            );
        }

        public function whp_woocommerce_ecommerce_content()
        {
            $isSubmit = 0;
            $tabs = whp_get_woo_tab();
            $this->whp_current_tab = $tabs[3];
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_woo_ecommerce_fields();
            $listEcommerce = whp_get_list_ecommerce();
            $this->whp_fields(
                $fields,
                'woocommerce/ecommerce',
                [
                    'listEcommerce' => $listEcommerce
                ]
            );
        }

        public function whp_woocommerce_cta_content()
        {
            $isSubmit = 0;
            $tabs = whp_get_woo_tab();
            $this->whp_current_tab = $tabs[4];
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_woo_cta_fields();
            $this->whp_fields(
                $fields,
                'woocommerce/cta',
            );
        }

        public function whp_woocommerce_thankyou_content()
        {
            $isSubmit = 0;
            $fields = whp_get_woo_thankyou_fields();
            $this->whp_fields(
                $fields,
                'woocommerce/thankyou',
            );
        }
        public function checkPlugin(): bool
        {
            if ( ! class_exists( 'WooCommerce' ) ) {
                return false;
            }
            return true;
        }

        private function renderWoocommerceRequired(): void
        {
            whp_get_shared( 'header' );
            ?>
            <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.08),0 0 0 1px #e8edf3;padding:40px 36px;max-width:560px;margin:32px auto;text-align:center;">
                <div style="width:56px;height:56px;border-radius:14px;background:#fff7ed;border:1px solid #fed7aa;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                </div>
                <h2 style="font-size:18px;font-weight:700;color:#0f172a;margin:0 0 10px;">Yêu cầu WooCommerce</h2>
                <p style="font-size:14px;color:#64748b;line-height:1.6;margin:0 0 24px;">Tính năng <strong>Cửa hàng nâng cao</strong> yêu cầu plugin <strong>WooCommerce</strong> đang hoạt động. Vui lòng cài đặt và kích hoạt WooCommerce để sử dụng.</p>
                <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>" class="button button-primary" style="font-size:13.5px;padding:8px 22px;border-radius:8px;text-decoration:none;">Cài đặt WooCommerce</a>
            </div>
            <?php
            whp_get_shared( 'footer' );
        }
        public function whp_popup()
        {
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_popup_fields();
            $type = whp_get_popup_type_field();
            $this->whp_fields($fields, 'popup', ['type' =>  $type]);
        }

        public function whp_ai_auto_poster()
        {
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            require_once($this->pathView . 'wpaap-main.php');
        }

        public function whp_filter_sidebar()
        {
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_filter_sidebar_fields();
            $this->whp_fields($fields, 'filter_sidebar');
        }

        public function whp_fields($fields, $tab = '', $data = [])
        {

            $isSubmit = 0;
            $isMail = 0;
            $checkOption = whp_check_option();
            $option  = get_option('whp_setting', []);
            $optionOld  = get_option('mbwp_helper', []);
            $itemInfo = $this->whp_current_tab;
            $currentTab = $itemInfo['callback'] ?? "";
            //     die($fields);

            foreach ($fields as $field) {
                $$field = null;
                $fieldCheck = null;
                $fieldSelect = null;
                if ($checkOption == 'old') {
                    // Change
                    switch ($currentTab) {
                        case 'whp_contactChanel':
                            $$field = $this->MB_WHP_Data_Old->contact($field);
                            break;
                        case 'whp_smtpSetting':
                            $$field = $this->MB_WHP_Data_Old->smtp($field);
                            break;
                        case 'whp_security':
                            $$field = $this->MB_WHP_Data_Old->security($field);
                            break;
                        case 'whp_extention':
                            $$field = $this->MB_WHP_Data_Old->extention($field);
                            break;
                        case 'whp_woocommerce_cta':
                            $$field = $this->MB_WHP_Data_Old->woo_cta($field);
                            break;
                        case 'whp_woocommerce_ecommerce':
                            $$field = $this->MB_WHP_Data_Old->woo_ecommerce($field);
                            break;
                        case 'whp_woocommerce_wallet':
                            $$field = $this->MB_WHP_Data_Old->woo_wallet($field);
                            break;
                        case 'whp_woocommerce_payment':
                            $$field = $this->MB_WHP_Data_Old->woo_payment($field);
                            break;
                        case 'whp_woocommerce_advance':
                            $$field = $this->MB_WHP_Data_Old->woo_advance($field);
                            break;
                        default:
                            break;
                    }
                } else {
                    $$field = whp_get_option($field) ?? "";
                }
                if ($field == 'whp_extention_custom_login_logo') {
                    $$field = whp_get_valid_login_logo($$field);
                }
                $fieldCheck = $field . "_check";
                $fieldSelect = $field . "_select";
                $$fieldCheck = $$field == '1' ? "checked" : "no_checked";

                $$fieldSelect = $$field == '1' ? "selected" : "";
            }
            if (isset($_POST['submit'])) {

                if (!wp_verify_nonce($_POST['_token'], '_token')) exit();

                unset($_POST['submit']);

                $isSubmit = 1;

                $params = sanitize_data($_POST);

                foreach ($fields as $field) {

                    $params[$field] = isset($params[$field]) ? $params[$field] : "0";

                    $$field = $params[$field] ?? "";
                    $fieldCheck = $field . "_check";
                    $fieldSelect = $field . "_select";
                    $$fieldCheck = $$field == '1' ? "checked" : "no_checked";
                    $$fieldSelect = $$field == '1' ? "selected" : "";
                    if ($field == 'whp_extention_custom_login_logo') {
                        $$field = whp_get_valid_login_logo($$field);
                    }
                }

                $allFields = whp_get_all_field();

                $params = $option ? array_merge($option, $params) : array_merge($allFields, $params);

                update_option('whp_setting', $params);
            }


            require_once($this->pathView . "{$tab}.php");
        }
    }
}
