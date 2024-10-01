<?php
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
            add_action('admin_menu', [$this, 'whp_admin_menu']);
            $this->whp_list_tab = whp_get_list_tab();
            $this->whp_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
            $this->whp_current_tab = whp_get_current_tab();
            $this->whp_post_type = isset($_GET['post_type']) ? sanitize_text_field($_GET['post_type']) : '';
        }


        public function include_style()
        {

            wp_enqueue_style('app', $this->pathAsset . 'css/app.css', array(), time(), 'all');
            wp_enqueue_style('responsive', $this->pathAsset . 'css/responsive.css', array(), time(), 'all');
            wp_enqueue_style('codemirror', $this->pathAsset . 'lib/codemirror/css/codemirror.css', array(), time(), 'all');
            wp_enqueue_style('codemirror-hint-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/addon/hint/show-hint.min.css', array(), time(), 'all');
        }
        public function include_script()
        {
            wp_enqueue_script('dirtyform', 'https://cdn.jsdelivr.net/jquery.dirtyforms/2.0.0/jquery.dirtyforms.min.js', array('jquery'), time(), true);
            wp_enqueue_media();
            wp_enqueue_script('app', $this->pathAsset . 'js/app.js', array('jquery'), time(), true);
            if (isset($_GET['page']) == 'mb-wphelper-reponsive') {

                wp_enqueue_script('codemirror', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/codemirror.min.js', array('jquery'), time(), true);
                wp_enqueue_script('codemirror-mode-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/mode/css/css.min.js', array('jquery'), time(), true);
                wp_enqueue_script('codemirror-hint-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/addon/hint/css-hint.min.js', array('jquery'), time(), true);
                wp_enqueue_script('codemirror-show-hint-css', 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.62.0/addon/hint/show-hint.min.js', array('jquery'), time(), true);
                //wp_enqueue_script('codemirror-config', $this->pathAsset . 'lib/codemirror/js/config.js', array('jquery'), time(), true);
                wp_enqueue_script('codemirror-config', $this->pathAsset . 'lib/codemirror/js/config.js', array('jquery'), time(), true);
            }
        }
        public function whp_admin_menu()
        {
            add_menu_page(
                __('WP Helper', 'whp'),
                __('WP Helper <span class = "menu-admin-icon">Premium</span>', 'whp'),
                '',
                'mb-wphelper',
                [$this, 'whp_dashboard'],
                $this->pathAsset . "images/icon.svg",
                '2'
            );
            $whp_list_tab = $this->whp_list_tab;
            foreach ($whp_list_tab as $itemTab) {
                //    print_r($this).print_r($itemTab['callback']);
                //     die();
                add_submenu_page(
                    'mb-wphelper',
                    $itemTab['title'],
                    $itemTab['title'],
                    'manage_options',
                    $itemTab['slug'],
                    [$this,  $itemTab['callback'],],
                );
            }
        }
        public function whp_dashboard()
        {

            require_once($this->pathView . 'dashboard/index.php');
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
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_security_fields();
            $this->whp_fields($fields, 'security');
        }
        public function whp_extention()
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

            $this->checkPlugin();

            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_woo_cta_fields();
            $this->whp_fields(
                $fields,
                'woocommerce/cta',
            );
        }

        public function whp_woocommerce_ecommerce()
        {
            $this->checkPlugin();
            $isSubmit = 0;
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

        public function whp_woocommerce_payment()
        {
            $this->checkPlugin();
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_woo_payment_fields();
            $listEcommerce = whp_get_list_ecommerce();
            $this->whp_fields(
                $fields,
                'woocommerce/payment',
            );
        }
        public function whp_woocommerce_wallet()
        {
            $this->checkPlugin();
            $isSubmit = 0;
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
        public function whp_woocommerce_advance()
        {
            $this->checkPlugin();
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_woo_advance_fields();
            $listWallet = whp_get_list_wallet();
            $this->whp_fields(
                $fields,
                'woocommerce/advance',
            );
        }
        public  function checkPlugin()
        {

            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                $html = '<div class="error"><p>Bạn cần cài đặt plugin woocommerce trước khi sử dụng.</p></div>';
                $allowed_html  = [
                    'div' => [
                        'class' => 'error',
                    ],
                    'p' => []
                ];
                echo wp_kses($html, $allowed_html);
                exit();
            }
        }
        public function whp_popup()
        {
            $isSubmit = 0;
            $itemInfo = $this->whp_current_tab;
            $fields = whp_get_popup_fields();
            $type = whp_get_popup_type_field();
            $this->whp_fields($fields, 'popup', ['type' =>  $type]);
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
                    $$field = $$field ? $$field : MB_WHP_URL . "/assets/admin/images/placeholder-image.jpg";
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
                        $$field = $$field ? $$field : MB_WHP_URL . "/assets/admin/images/placeholder-image.jpg";
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
