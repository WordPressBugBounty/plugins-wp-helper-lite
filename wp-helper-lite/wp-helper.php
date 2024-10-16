<?php

/**
 * Plugin Name: WP Helper Premium
 * Plugin URI: https://www.matbao.net/hosting/wp-helper-plugin.html
 * Description: The best WordPress All-in-One plugin. ❤ Made in Vietnam by MWP Team.
 * Version: 4.6.2
 * Requires at least: 5.6
 * Author: Mat Bao Corp
 * Author URI: https://www.matbao.net/hosting/wp-helper-plugin.html
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: whp
 * Domain Path: /languages
 * Test
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
          
            require_once(MB_WHP_PATH . 'functions/ajax.php');
            require_once(MB_WHP_PATH . 'functions/class.wps-frontend-setup-function.php');
            require_once(MB_WHP_PATH . 'functions/class.wps-admin-setup-function.php');
            require_once(MB_WHP_PATH . 'functions/class.wps-data-old.php');
          
            $MB_WHP_Frontend_Setup_Function = new MB_WHP_Frontend_Setup_Function();
            $MB_WHP_Admin_Setup_Function = new MB_WHP_Admin_Setup_Function();
        }
        public function defineConstants()
        {
            define('MB_WHP_PATH', plugin_dir_path(__FILE__));
            define('MB_WHP_PATH_SIDEBAR', plugin_dir_path(__FILE__) . "sidebar/");
            define('MB_WHP_URL', plugin_dir_url(__FILE__));
            define('MB_WHP_PATH_VIEW', plugin_dir_path(__FILE__) . "views/");
            define('MB_WHP_VERSION', '1.0.0');
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
    $mb_whp = new MB_WHP();
}
