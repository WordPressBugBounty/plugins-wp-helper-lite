<?php
defined('ABSPATH') || exit;

if (
    !class_exists('MB_WHP_Wallet_Blocks_Integration')
    && class_exists('\Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')
) {

    class MB_WHP_Wallet_Blocks_Integration extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType
    {
        protected $name;
        private $icon_url;

        public function __construct($gateway_id, $icon_url)
        {
            $this->name = $gateway_id;
            $this->icon_url = $icon_url;
        }

        public function initialize()
        {
            $settings = get_option("woocommerce_{$this->name}_settings", null);
            // Gateway chưa từng được lưu qua trang cài đặt WooCommerce riêng
            // (WooCommerce > Thanh toán > <ví>) thì option chưa tồn tại. Ở
            // Classic Checkout, WC_Settings_API::init_settings() sẽ tự merge
            // default của form_fields trong trường hợp này — và mọi gateway
            // ví ở đây khai 'enabled' => 'yes' làm default — nên gateway vẫn
            // hiện dù chưa ai bấm lưu ở trang settings đó. Mirror lại đúng
            // hành vi này cho Block Checkout, nếu không is_active() sẽ mặc
            // định false và ví "biến mất" khỏi Block Checkout dù đã bật ở
            // trang Ví điện tử của plugin và vẫn hiện bình thường ở Classic
            // Checkout — gây lệch hành vi giữa 2 loại checkout.
            $this->settings = is_array($settings) ? $settings : ['enabled' => 'yes'];
        }

        public function is_active()
        {
            return filter_var($this->get_setting('enabled', false), FILTER_VALIDATE_BOOLEAN);
        }

        public function get_payment_method_script_handles()
        {
            $handle = 'whp-wallet-blocks';
            if (!wp_script_is($handle, 'registered')) {
                wp_register_script(
                    $handle,
                    MB_WHP_URL . 'assets/frontend/js/wallet-blocks.js',
                    ['wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-html-entities'],
                    MB_WHP_VERSION,
                    true
                );
            }
            return [$handle];
        }

        public function get_payment_method_data()
        {
            return [
                'title'       => $this->get_setting('title'),
                'description' => $this->get_setting('description'),
                'icon'        => $this->icon_url,
                'supports'    => $this->get_supported_features(),
            ];
        }
    }
}
