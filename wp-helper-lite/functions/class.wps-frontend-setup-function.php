<?php

use Symfony\Component\CssSelector\XPath\XPathExpr;

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

            add_action('wp_enqueue_scripts', [$this, 'include_style']);
            add_action('wp_enqueue_scripts', [$this, 'include_script']);
            $this->include_header();
            $this->include_body();
            $this->include_footer();
            $this->whp_smtp();
            $this->whp_security();
            $this->whp_woo_cta();
            $this->whp_ecommerce();
            $this->whp_advance();
            $this->whp_extention();
            $this->whp_woo_admin_ecommerce();
            $this->whp_checkout();
            $this->whp_gateway_wallet();
            // $this->whp_maintenance();
            $this->whp_popup();
            $this->whp_reponsive();

            add_action('wp_ajax_whp_smtp_send_mail_test', [$this, 'whp_smtp_send_mail_test']);
            add_action('wp_ajax_nopriv_whp_smtp_send_mail_test', [$this, 'whp_smtp_send_mail_test']);
            //  add_filter('template_include', [$this, 'whp_wallet_thanhyou_page'], 10, 2);
            //  add_action('get_header', [$this, 'whp_maintenance']);
            add_action('wp', [$this, 'whp_maintenance']);
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

            $to = sanitize_text_field($_POST['email']);
            $message = sanitize_text_field($_POST['content']);
            $subject = 'WP Helper - Cấu hình SMTP thành công';
            $mail = wp_mail($to, $subject, $message);

            if ($mail) {
                echo json_encode(['status' => 200, 'message' => 'Email sent successfully']);
            } else {
                echo json_encode(['status' => 500, 'message' => 'Failed to send email']);
            }
            exit();
        }
        public function include_style()
        {
            wp_enqueue_style('app', $this->pathAsset . 'css/app.css', array(), time(), 'all');
            wp_enqueue_style('app', $this->pathAsset . 'css/popup.css', array(), time(), 'all');
            $whp_contact_design_position_y = whp_get_setting('whp_contact_design_position_y');
            $whp_contact_design_color = whp_get_setting('whp_contact_design_color');
            $custom_css = "
                    #mb-whp-contact{
                            bottom: {$whp_contact_design_position_y}px;
                    }
                    .whp-contact-icon, .whp-contact-icon:before, .whp-contact-icon:after, .whp-contact-icon:before, .whp-contact-icon:before, .whp-contact-content-head {
                        background: {$whp_contact_design_color}
                    }
                    ";
            wp_add_inline_style('app', $custom_css);
        }

        public function include_script()
        {
            wp_enqueue_script('app', $this->pathAsset . 'js/app.js', array('jquery'), time(), true);
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
            $code = whp_get_setting('whp_code_header');
            echo stripslashes($code);
        }
        public function whp_body_code()
        {
            $code = whp_get_setting('whp_code_body');
            echo stripslashes($code);
        }
        public function whp_footer_code()
        {
            $code = whp_get_setting('whp_code_footer');
            echo stripslashes($code);
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
        }
        public function whp_smtp_send_mail($phpmailer)
        {
            $fields = whp_get_smtp_fields();
            foreach ($fields as $field) {
                $$field = whp_get_setting($field);
            }
            if ($whp_smtp_active) {
                $phpmailer->isSMTP();
                $phpmailer->FromName = $whp_smtp_from_name ?? "";
                $phpmailer->Host = $whp_smtp_host ?? "";
                $phpmailer->Port = $whp_smtp_port ?? "";
                $phpmailer->SMTPSecure = $whp_smtp_security ?? "";
                $phpmailer->From = $whp_smtp_email ?? "";
                $phpmailer->SMTPAuth = true;
                $phpmailer->Username = $whp_smtp_user ?? "";
                $phpmailer->Password = $whp_smtp_password ?? "";
                $phpmailer->AddReplyTo($phpmailer->From, $phpmailer->FromName);
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

            if ($whp_new_login_url) {

                $site_url = site_url();

                define('site_url', $site_url);

                add_action('init', function () use ($whp_new_login_url) {

                    $current_url   = str_replace('/', '', $_SERVER['REQUEST_URI']);

                    $url_new = $whp_new_login_url;

                    $redirectNaTo  = site_url . '/wp-admin/';
                    $adminToCheck = [
                        'wp-admin',
                        'login',
                        'wp-admin.php',
                        'wp-login.php'
                    ];
                    if (!is_user_logged_in()) {
                        if ($current_url == $url_new) {
                            wp_redirect('/wp-login.php?redirect_to=' . $redirectNaTo);
                            exit;
                        }
                        if (in_array($current_url, $adminToCheck) && $current_url  !== 'wp-login.php') {
                            wp_redirect(site_url);
                            exit;
                        }
                    } else {
                        if ($current_url == $url_new || $current_url == 'wp-login.php') {
                            wp_redirect($redirectNaTo);
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
            if ($whp_woocommerce_cta_text) {
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
            }
        }
        public  function whp_woo_cta_show_buynow_button()
        {

            $current_product_id = get_the_ID();

            $product = wc_get_product($current_product_id);

            $checkout_url = WC()->cart->get_checkout_url();

            if ($product->is_type('simple')) {
                $url =   esc_url($checkout_url . '?add-to-cart=' . $current_product_id);
                $title = esc_html('Mua ngay');
                $class = esc_html('buy-now button');
                $xhtml = sprintf('<a href="%s" class="%s">%s</a>', $url, $class, $title);
                $allowed_html = [
                    'a' => [
                        'href' => [],
                        'class' => []
                    ]
                ];

                echo wp_kses($xhtml, $allowed_html);
            }
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

            if ($whp_extention_custom_login_theme) {

                if ($whp_extention_custom_login_logo) {

                    add_action('login_head', function () use ($whp_extention_custom_login_logo) {
                        $url = esc_url($whp_extention_custom_login_logo);
                        $custom_css = "#login h1 a {

                            background: url('" . $url . "') ;
                            }";

                        echo '<style type="text/css"> ' . $custom_css . ' </style>';
                    });
                }
                if ($whp_extention_custom_link) {
                    add_filter('login_headerurl', function () use ($whp_extention_custom_link) {
                        //  echo esc_url($whp_extention_custom_link);
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

            if ($whp_extention_filter_order_by_phone) {
                add_filter('manage_edit-shop_order_columns', [$this, 'whp_extention_field_filter_order'], 10, 1);
                add_action('pre_get_posts', [$this, 'whp_extention_filter_order_in_table'], 99, 1);
                add_action('manage_shop_order_posts_custom_column', [$this, 'whp_extention_display_order'], 10, 1);
                add_action('restrict_manage_posts', [$this, 'whp_extention_show_field_order']);
            }

            if ($whp_extention_svg) {

                add_filter('upload_mimes', [$this, 'whp_extention_allow_svg_upload']);

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

            $file_content = file_get_contents($file['tmp_name']);

            if (empty($file_content)) {
                return false;
            }
            if (strpos($file_content, '<?xml') !== 0) {
                return false;
            }
            if (strpos($file_content, '<script') !== false) {
                return false;
            }

            if (strpos($file_content, '</svg>') !== (strlen($file_content) - strlen('</svg>'))) {
                return false;
            }
            return $file;
        }
        public function whp_extention_allow_svg_upload($mimes)
        {
            $mimes['svg'] = 'image/svg+xml';
            return $mimes;
        }

        public function whp_extention_field_filter_order($field)
        {
            // Đổi "Số điện thoại" thành "Phone Number" để hiển thị trên trang quản lý đơn hàng
            $field['phone'] = __('Phone Number', 'text-domain');
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

            $phone = '';

            if (isset($_GET['phone']) && $_GET['phone'] !== '') {
                // Làm sạch giá trị số điện thoại trước khi hiển thị trong trường nhập
                $phone = sanitize_text_field($_GET['phone']);
            }

?>
            <input type="text" name="phone" value="<?php echo esc_attr($phone); ?>" placeholder="<?php esc_attr_e('Tìm kiếm bằng số điện thoại', 'text-domain'); ?>">
            <?php
        }


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
            add_action('woocommerce_product_meta_end', [$this, 'whp_ecommerce_show']);
        }
        public function whp_ecommerce_show()
        {
            global $product;
            $product_id = $product->get_id();
            $fields = whp_get_woo_ecommerce_fields();
            $arr = [];
            foreach ($fields as $key => $field) {
                $value = whp_get_setting($field);
                if ($value) {
                    array_push($arr, $field);
                }
            }
            $xhtml = "<span class='tagged_as'><span class='sub_title'>Xem trên: </span>";
            $xhtml .= "<ul class = 'mb-ecommerce-buttons lst-n'>";

            foreach ($arr as $key => $item) {
                $brand = explode('_', $item);
                $brand_uc = ucfirst($brand[3]);
                $icon = whp_get_image_url($brand_uc) . '.svg';
                $icon = esc_url($icon);
                if (!empty($product_meta = get_post_meta($product_id, 'product-ecommerce-' . $item, true))) {
                    $link = esc_url($product_meta);
                    $name = esc_html($brand_uc);
                    $xhtml .= sprintf('<li><a href = "%s" target = "_blank"><img src="%s" alt="%s"></a></li>', $link, $icon, $name,  $name,);
                }
            }
            $xhtml .= ' </ul></span>';
            $allowed_html = array(
                'span' => array(
                    'class' => 'tagged_as'
                ),
                'ul' => array(
                    'class' => 'mb-ecommerce-buttons lst-n',
                    'style' => 'list-style: none !important'
                ),
                'li' => array(),
                'a' => array(
                    'href' => array(),
                    'target' => array()
                ),
                'img' => array(
                    'src' => array(),
                    'alt' => array()
                )

            );
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
                $name = "product-ecommerce-{$key}";
                $value = sanitize_text_field($_POST[$name]);
                if ($value) {
                    update_post_meta($postID, $name, esc_attr($value));
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
            }
            if ($whp_woocommerce_advance_enable_compact_desc) {
                add_action('wp_footer', [$this, 'whp_compact_desc'], 0);
                add_filter('the_content', [$this, 'the_content_product'], 100);
            }
            if ($whp_woocommerce_advance_enable_notify_telegram) {

                add_action('woocommerce_checkout_order_processed', [$this, 'whp_send_message_telegram']);
            }
        }


        public function whp_send_message_telegram($order_id)
        {

            if (!$order_id) return;
            $order = wc_get_order($order_id);
            $order_data = $order->get_data();
            $last_name = $order_data['billing']['last_name'];
            $phone = (isset($order_data['billing']['phone'])) ? $order_data['billing']['phone'] : "Chưa nhập số điện thoại";
            $paymentMethod = $order->get_payment_method_title();
            $msg = "<strong>Thông báo đơn hàng mới : #{$order_id} </strong> " . "\n";
            $msg .= "Họ tên: {$last_name}" . "\n";
            $msg .= "Số điện thoại: {$phone}" . "\n";
            $msg .= "Phương thức thanh toán: {$paymentMethod}" . "\n";
            $msg .= "<strong>Thông tin đơn hàng</strong>" . "\n";
            foreach ($order->get_items() as $item_id => $item) {
                $product_name = $item->get_name();
                $quantity = $item->get_quantity();
                $subtotal = $item->get_subtotal();
                $total =    $item->get_total();
                $subtotal = whp_format_currency_vnd($subtotal);
                $msg .= " - {$product_name} ({$quantity}) x {$subtotal}"  . "\n";
            }
            $total = $order->get_total();
            $total = number_format($total) . "đ";
            $msg .= "——————————————————————————" . "\n";
            $msg .= "Tổng đơn hàng: {$total}";
            $chatID = whp_get_setting('whp_woocommerce_advance_telegram_chatid'); // ID của Group trong Telegram
            $token = whp_get_setting('whp_woocommerce_advance_telegram_token'); // Token của con Bot gửi thông báo
            $url = "https://api.telegram.org/bot" . $token . "/sendMessage?parse_mode=html&chat_id=" . $chatID;
            $url = $url . "&text=" . urlencode($msg);
            wp_remote_get($url);
            $whp_woocommerce_advance_enable_notify_telegram = false;
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

        //wc_enqueue_js("jQuery('#tab-description').addClass('compact-active')");
        public function the_content_product($content)
        {
            if (is_product()) {
                $content .= '<div class="whp_readmore_producu_desc"><span title="Xem thêm">Xem thêm</span></div>';
            }
            return $content;
        }

        public function whp_compact_desc()
        {
            if (is_product()) {


            ?>

                <style>
                    #tab-description {
                        max-height: 200px;
                        overflow: hidden;
                        position: relative;
                    }

                    .whp_readmore_producu_desc {
                        text-align: center;
                        cursor: pointer;
                        position: absolute;
                        z-index: 9999;
                        bottom: 0;
                        width: 100%;
                        background: #fff;
                    }

                    .whp_readmore_producu_desc:before {
                        height: 55px;
                        margin-top: -45px;
                        content: "";
                        background: -moz-linear-gradient(top, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 100%);
                        background: -webkit-linear-gradient(top, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 100%);
                        background: linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 1) 100%);
                        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff00', endColorstr='#ffffff', GradientType=0);
                        display: block;
                    }

                    .whp_readmore_producu_desc span {
                        color: #6c6d70;
                        display: block;
                        border: 1px solid;
                        margin-left: auto;
                        display: flex;
                        width: 130px;
                        margin-right: auto;
                        text-transform: uppercase;
                        text-align: center;
                        justify-content: center;
                    }
                </style>

                <script>
                    jQuery('.whp_readmore_producu_desc').click(function() {
                        if (jQuery('#tab-description').css('max-height') == 'none') {
                            jQuery('#tab-description').css('max-height', '200px')
                            jQuery(this).html('<span title="Xem thêm">Xem thêm</span>')
                        } else {
                            jQuery('#tab-description').css('max-height', 'none')
                            jQuery(this).html('<span title="Thu gọn">Thu gọn</span>')
                        }

                    })
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

            if ($whp_maintenance_active) {

                global $pagenow;

                // Kiểm tra xem trang hiện tại không phải là trang quản trị và kích hoạt chế độ bảo trì
                if ($pagenow !== 'wp-login.php' && !is_admin()) {

                    wp_die('<h1>Bảo trì</h1><p>
                    Xin lỗi, chúng tôi đang bảo trì website. Vui lòng truy cập lại sau.</p>', 'Maintenance Mode');
                }
            }
        }
        // popup
        public function whp_popup()
        {
            $fields = whp_get_popup_fields();

            foreach ($fields as $key => $field) {
                $$field = whp_get_option($field);
            }
            $whp_maintenance_active = whp_get_option('whp_maintenance_active');
            if ($whp_popup_active && !is_admin() && !is_login() && !$whp_maintenance_active) {
                add_action('wp_enqueue_scripts', function () {
                    wp_enqueue_script('cookie', $this->pathAsset . 'js/cookie.js', array('jquery'), time(), true);
                });
                add_action('wp_enqueue_scripts', function () {
                    wp_enqueue_script('ajax', $this->pathAsset . 'js/ajax.js', array('jquery'), time(), true);
                    wp_localize_script('ajax', 'whp_popup_ajax', ['url' =>  admin_url('admin-ajax.php')]);
                    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', array(), null);
                });
                // pop up newsletter
                if ($whp_popup_type == 0) {

                    add_action('wp_head', function () use ($fields) {
                        foreach ($fields as $key => $field) {
                            $$field = whp_get_option($field);
                        }
                        require($this->pathViewPages . 'popup-newsletter.php');
                    });
                }
                // pop up banner
                if ($whp_popup_type == 1) {
                    require_once($this->pathViewPages . 'popup-banner.php');
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
    }
}
