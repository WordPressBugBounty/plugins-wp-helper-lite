<?php

/**
 * Plugin Name: WP Helper Premium
 * Plugin URI: https://www.matbao.net/hosting/wp-helper-plugin.html
 * Description: The best WordPress All-in-One plugin. ❤ Made in Vietnam by MWP Team.
 * Version: 4.7.1
 * Requires at least: 6.7
 * Requires PHP: 7.4
 * Author: Mat Bao Corp
 * Author URI: https://www.matbao.net/hosting/wp-helper-plugin.html
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: whp
 * Domain Path: /languages
 */
/* WP Helper Premium is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. WP Helper Premium is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with WP Helper Premium. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('MB_WHP')) {
    class MB_WHP
    {
        function __construct()
        {

            $this->defineConstants();


            // Function
            require_once(MB_WHP_PATH . 'functions/whp-base-functions.php');

            // Form Manager
            require_once(MB_WHP_PATH . 'functions/wph-form-manager.php');
            require_once(MB_WHP_PATH . 'functions/wph-form-adapters.php');
            require_once(MB_WHP_PATH . 'functions/wph-form-manager-ajax.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wph-form-manager.php');

            // Email & Liên hệ — Email Log, CAPTCHA, Spam Filter
            require_once(MB_WHP_PATH . 'functions/wph-email-log.php');
            require_once(MB_WHP_PATH . 'functions/wph-captcha.php');
            require_once(MB_WHP_PATH . 'functions/wph-spam-filter.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wph-email-log.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wph-captcha.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wph-spam-filter.php');

            require_once(MB_WHP_PATH . 'functions/ajax.php');
            require_once(MB_WHP_PATH . 'functions/class.wps-frontend-setup-function.php');
            require_once(MB_WHP_PATH . 'functions/class.wps-admin-setup-function.php');
            require_once(MB_WHP_PATH . 'functions/class.wps-data-old.php');
          
            $MB_WHP_Frontend_Setup_Function = new MB_WHP_Frontend_Setup_Function();
            $MB_WHP_Admin_Setup_Function = new MB_WHP_Admin_Setup_Function();

            // Define AI Auto Poster Constants
            if (!defined('WPAAP_PLUGIN_DIR')) {
                define('WPAAP_PLUGIN_DIR', MB_WHP_PATH);
            }
            if (!defined('WPAAP_PLUGIN_URL')) {
                define('WPAAP_PLUGIN_URL', MB_WHP_URL);
            }

            // Load AI Auto Poster functions and views
            require_once(MB_WHP_PATH . 'functions/wpaap-ai-generator.php');
            require_once(MB_WHP_PATH . 'functions/wpaap-shortcode-renderer.php');
            require_once(MB_WHP_PATH . 'functions/wpaap-image-handler.php');
            require_once(MB_WHP_PATH . 'functions/wpaap-admin-menu.php');
            require_once(MB_WHP_PATH . 'functions/wpaap-ajax-handler.php');

            // Serve /feedback/ and /feedback/api from inside plugin (bypasses volume shadow)
            add_action('init', function() {
                $uri = rtrim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
                if ( $uri === '/feedback' ) {
                    header( 'Content-Type: text/html; charset=utf-8' );
                    readfile( MB_WHP_PATH . 'feedback/index.html' );
                    exit;
                }
                if ( $uri === '/feedback/api' ) {
                    include MB_WHP_PATH . 'feedback/api.php';
                    exit;
                }
            }, 1 );

            require_once(MB_WHP_PATH . 'views/admin/pages/wpaap-dashboard.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wpaap-connection.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wpaap-limits.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wpaap-admin-page.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wpaap-security-advisor.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wpaap-seo-advisor.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wpaap-maintenance-tab.php');
            require_once(MB_WHP_PATH . 'views/admin/pages/wpaap-ai-payment-tab.php');
        }
        public function defineConstants()
        {
            define('MB_WHP_PATH', plugin_dir_path(__FILE__));
            define('MB_WHP_PATH_SIDEBAR', plugin_dir_path(__FILE__) . "sidebar/");
            define('MB_WHP_URL', plugin_dir_url(__FILE__));
            define('MB_WHP_PATH_VIEW', plugin_dir_path(__FILE__) . "views/");
            define('MB_WHP_VERSION', '4.7.1');
        }
        public static function activate()
        {

            update_option('rewrite_rules', '');
        }
        public static function deactivate()
        {
            flush_rewrite_rules();
            unregister_post_type('mbe-slider');
        }
        public static function uninstall()
        {
        }
    }
}
if (class_exists('MB_WHP')) {
    register_activation_hook(__FILE__, array('MB_WHP', 'activate'));
    register_deactivation_hook(__FILE__, array('MB_WHP', 'deactivate'));
    register_uninstall_hook(__FILE__, array('MB_WHP', 'uninstall'));
    add_action('plugins_loaded', function() {
        load_plugin_textdomain('whp', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }, -1);
    add_action('plugins_loaded', function() {
        global $mb_whp;
        $mb_whp = new MB_WHP();
    }, 0);
}
