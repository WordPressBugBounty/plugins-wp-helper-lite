<?php
if (!class_exists('MB_WHP_Data_Old')) {
    class MB_WHP_Data_Old
    {
        private $option;
        function __construct()
        {
            $this->option = get_option('mbwp_helper', []);
        }
        public  function contact($field)
        {
            $result = null;
            $optionOld = $this->option;
            $optionOld = $optionOld['mbwp-opt-smtp'] ?? [];
            switch ($field) {
                case 'whp_contact_active':
                    $result = whp_get_option_old('mbwp-contact-active');
                    break;
                case 'whp_contact_design_color':
                    $result = whp_get_contact_option_old('design', 'mbwp-contact-color');
                    break;
                case 'whp_contact_design_greeting':
                    $result = whp_get_contact_option_old('design', 'mbwp-contact-greeting');
                    break;
                case 'whp_contact_design_position_y':
                    $result = whp_get_contact_option_old('design', 'mbwp-helper-position-y');
                    break;
                case 'whp_contact_design_position_x':
                    $result = whp_get_contact_option_old('design', 'mbwp-contact-position');
                    break;
                case 'whp_contact_phone_title':
                    $result = whp_get_contact_option_old('phone', 'mbwp-contact-phone-title');
                    break;
                case 'whp_contact_phone_data':
                    $result = whp_get_contact_option_old('phone', 'mbwp-contact-repeater');
                    break;
                case 'whp_contact_other_title':
                    $result = whp_get_contact_option_old('other', 'mbwp-general-contact-title');
                    break;
                case 'whp_contact_other_email':
                    $result = whp_get_contact_option_old('other', 'mbwp-general-contact-email');
                case 'whp_contact_other_facebook':
                    $result = whp_get_contact_option_old('other', 'mbwp-general-contact-facebook');
                    break;
                case 'whp_contact_other_zalo':
                    $result = whp_get_contact_option_old('other', 'mbwp-general-contact-zalo');
                    break;
                case 'whp_contact_other_facebook_page':
                    $result = whp_get_contact_option_old('other', 'mbwp-general-facebook-page');
                    break;
                default:
                    # code...
                    break;
            }
            return $result;
        }
        public  function smtp($field)
        {
            $result = null;
            $optionOld = $this->option;
            $optionOld = $optionOld['mbwp-opt-smtp'] ?? [];
            switch ($field) {
                case 'whp_smtp_active':
                    $result = $optionOld['mbwp-smtp-active'] ?? 0;
                    break;
                case 'whp_smtp_setting':
                    $result = $optionOld['mbwp-smtp-setting'] ?? 0;
                    break;
                case 'whp_smtp_email':
                    $result = $optionOld['mbwp-smtp-email'] ?? 0;
                    break;
                case 'whp_smtp_from_name':
                    $result = $optionOld['mbwp-smtp-fromName'] ?? 0;
                    break;
                case 'whp_smtp_host':
                    $result = $optionOld['mbwp-smtp-host'] ?? 0;
                    break;
                case 'whp_smtp_security':
                    $result = $optionOld['mbwp-smtp-security'] ?? 0;
                    break;
                case 'whp_smtp_port':
                    $result = $optionOld['mbwp-smtp-port'] ?? 0;
                    break;
                case 'whp_smtp_user':
                    $result = $optionOld['mbwp-smtp-user'] ?? 0;
                    break;
                case 'whp_smtp_password':
                    $result = $optionOld['mbwp-smtp-password'] ?? 0;
                    break;
                default:
                    # code...
                    break;
            }
            return $result;
        }
        public  function security($field)
        {
            $result = null;
            $optionOld = $this->option;
            switch ($field) {
                case 'whp_security_remove_xmlrpc':
                    $result = $optionOld['mbwp-remove-xml-rpc'] ?? "";
                    break;
                case 'whp_security_disable_copy':
                    $result = $optionOld['mbwp-disable-copy-content'] ?? "";
                    break;
                case 'whp_security_delete_wphead':
                    $result = $optionOld['mbwp-delete-link-head'] ?? "";
                    break;
                case 'whp_security_hide_wp_version':
                    $result = $optionOld['mbwp-switcher-hide-wp-version'] ?? "";
                    break;
                case 'whp_security_hide_theme_plugin':
                    $result = $optionOld['mbwp-switcher-hide-menu-theme-plugin'] ?? "";
                    break;
                case 'whp_security_change_login_url':
                    $result = $optionOld['mbwp-login-url']['mbwp-switcher-change-url-login'] ?? "";
                    break;
                case 'whp_new_login_url':
                    $result = $optionOld['mbwp-login-url']['mbwp-login-new-url'] ?? "";
                    break;
                default:
                    # code...
                    break;
            }
            return $result;
        }
        public function extention($field)
        {
            $result = null;
            $optionOld = $this->option;
            switch ($field) {
                case 'whp_security_remove_xmlrpc':
                    $result = $optionOld['mbwp-remove-xml-rpc'] ?? "";
                    break;
                case 'whp_extention_duplicate_page_post':
                    $result = $optionOld['mbwp-opt-duplicate']['mbwp-duplicate-page-post'] ?? "";
                    break;
                case 'whp_extention_duplicate_menu':
                    $result = $optionOld['mbwp-opt-duplicate']['mbwp-duplicate-menu'] ?? "";
                    break;
                    // case 'whp_maintenance_mode':
                    //     $result = $optionOld['mbwp-remove-xml-rpc'] ?? "";
                    //     break;
                case 'whp_extention_enable_404_redirect':
                    $result = $optionOld['mbwp-redirect-404']['switcher-redirect-404'] ?? "";
                    break;
                case 'whp_extention_disable_emojis':
                    $result = $optionOld['mbwp-helper-disable-emojis']['switcher-disable-emojis'] ?? "";
                    break;
                case 'whp_extention_remove_query_string':
                    $result = $optionOld['mbwp-helper-remove-query-strings']['switcher-remove-query-strings'] ?? "";
                    break;
                case 'whp_extention_disbale_wp_embeds':
                    $result = $optionOld['mbwp-helper-disable-wp-embeds']['switcher-disable-wp-embeds'] ?? "";
                    break;
                case 'whp_extention_disbale_google_fonts':
                    $result = $optionOld['mbwp-helper-disable-google-font']['switcher-disable-google-font'] ?? "";
                    break;
                case 'whp_extention_disbale_dashicons':
                    $result = $optionOld['mbwp-helper-disable-dashicons']['switcher-disable-dashicons'] ?? "";
                    break;
                case 'whp_extention_custom_login_theme':
                    $result = $optionOld['mbwp-opt-login']['mbwp-login-active'] ?? "";
                    break;
                case 'whp_extention_custom_login_logo':
                    $result = $optionOld['mbwp-login-logo']['url'] ?? "";
                    break;
                case 'whp_extention_custom_link':
                    $result = $optionOld['mbwp-login-link'] ?? "";
                    break;
                default:
                    # code...
                    break;
            }
            return $result;
        }
        public function woo_cta($field)
        {

            $result = null;
            $optionOld = $this->option;
            switch ($field) {
                case 'whp_woocommerce_cta_text':
                    $result = get_option('btnCartName', '');


                    break;
                case 'whp_woocommerce_cta_convert_zero_to_contact':
                    $result = get_option('convertZeroToContact', '');
                    break;
                case 'whp_woocommerce_cta_show_buynow_button':
                    $result = get_option('showBuyNow', '');
                    break;
                default:
                    # code...
                    break;
            }
            return $result;
        }
        public function woo_ecommerce($field)
        {
            $result = null;
            $optionOld = $this->option;
            switch ($field) {
                case 'whp_woocommerce_ecommerce_tiki':
                    $result = get_option('tiki', '');
                    break;
                case 'whp_woocommerce_ecommerce_shopee':
                    $result = get_option('shopee', '');
                    break;
                case 'whp_woocommerce_ecommerce_lazada':
                    $result = get_option('lazada', '');
                    break;
                case 'whp_woocommerce_ecommerce_sendo':
                    $result = get_option('sendo', '');
                    break;
                default:
                    break;
            }
            return $result;
        }
        public function woo_payment($field)
        {
            $result = null;
            $optionOld = $this->option;
            switch ($field) {
                case 'whp_woocommerce_payment_fullname':
                    $result = get_option('fullname', '');
                    break;
                case 'whp_woocommerce_payment_address':
                    $result = get_option('address', '');
                    break;
                case 'whp_woocommerce_payment_country':
                    $result = get_option('country', '');
                    break;
                case 'whp_woocommerce_payment_company':
                    $result = get_option('company', '');
                    break;
                case 'whp_woocommerce_payment_zipcode':
                    $result = get_option('zipCode', '');
                    break;
                case 'whp_woocommerce_payment_province':
                    $result = get_option('province', '');
                    break;
                default:
                    # code...
                    break;
            }
            return $result;
        }
        public function woo_wallet($field)
        {
            $result = null;
            $optionOld = $this->option;
            switch ($field) {
                case 'whp_woocommerce_wallet_momo':
                    $result = get_option('momo', '');
                    break;
                case 'whp_woocommerce_wallet_zalopay':
                    $result = get_option('zaloPay', '');
                    break;
                case 'whp_woocommerce_wallet_vnpay':
                    $result = get_option('vnPay', '');
                    break;
                case 'whp_woocommerce_wallet_shopeepay':
                    $result = get_option('shopeePay', '');
                    break;
                default:
                    # code...
                    break;
            }
            return $result;
        }
        public function woo_advance($field)
        {
            $result = null;
            $optionOld = $this->option;
            switch ($field) {
                case 'whp_woocommerce_advance_enable_notice':
                    $result = get_option('notify', '');
                    break;
                case 'whp_woocommerce_advance_enable_vat':
                    $result = get_option('vat', '');
                    break;
                case 'whp_woocommerce_advance_enable_compact_desc':
                    $result = get_option('compact', '');
                    break;
                case 'whp_woocommerce_advance_enable_notify_telegram':
                    $result = get_option('telegram', '');
                    break;
                case 'whp_woocommerce_advance_telegram_token':
                    $result = get_option('telegramToken', '');
                    break;
                case 'whp_woocommerce_advance_telegram_chatid':
                    $result = get_option('telegramChatid', '');
                    break;
                default:
                    # code...
                    break;
            }
            return $result;
        }
    }
}
