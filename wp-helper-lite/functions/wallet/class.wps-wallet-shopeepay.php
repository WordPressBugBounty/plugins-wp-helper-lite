<?php
defined('ABSPATH') || exit;

if (!class_exists('MB_WHP_Wallet_ShopeePay')) {

    class MB_WHP_Wallet_ShopeePay extends WC_Payment_Gateway
    {
        private $image_url;
        private $account_name;
        private $account_number;

        public function __construct()
        {
            $this->id                 = 'MB_WHP_Wallet_ShopeePay';
            $this->icon               = whp_get_icon('shopeepay.svg');
            $this->has_fields         = false;
            $this->method_title       = __('Ví điện tử ShopeePay', 'wphp-wc');
            $this->method_description = __('Cho phép thanh toán qua ví điện tử ShopeePay', 'wphp-wc');

            $this->init_form_fields();
            $this->init_settings();

            $this->title          = $this->get_option('title');
            $this->description    = $this->get_option('description');
            $this->image_url      = $this->get_option('shopeepay_image_url');
            $this->account_name   = $this->get_option('name_shopee');
            $this->account_number = $this->get_option('number_shopee');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('woocommerce_thankyou_' . $this->id, [$this, 'thankyou_page']);
            add_action('woocommerce_email_before_order_table', [$this, 'email_instructions'], 10, 3);
        }

        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled' => [
                    'title'   => __('Bật/Tắt', 'wphp-wc'),
                    'type'    => 'checkbox',
                    'label'   => __('Bật phương thức thanh toán', 'wphp-wc'),
                    'default' => 'yes',
                ],
                'title' => [
                    'title'       => __('Tiêu đề', 'wphp-wc'),
                    'type'        => 'text',
                    'default'     => __('Thanh toán qua ShopeePay', 'wphp-wc'),
                    'desc_tip'    => true,
                    'description' => __('Hiển thị ở trang thanh toán', 'wphp-wc'),
                ],
                'description' => [
                    'title'       => __('Mô tả', 'wphp-wc'),
                    'type'        => 'textarea',
                    'default'     => __('Thanh toán qua ví điện tử ShopeePay. An toàn và nhanh chóng!', 'wphp-wc'),
                    'desc_tip'    => true,
                    'description' => __('Nhập mô tả của phương thức.', 'wphp-wc'),
                ],
                'number_shopee' => [
                    'title'       => __('Số điện thoại ShopeePay', 'wphp-wc'),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => __('Nhập số điện thoại nhận tiền', 'wphp-wc'),
                ],
                'name_shopee' => [
                    'title' => __('Tên tài khoản ShopeePay', 'wphp-wc'),
                    'type'  => 'text',
                ],
                'button_upload' => [
                    'title' => __('Hình QR Code', 'wphp-wc'),
                    'type'  => 'button',
                    'class' => 'button-upload-qrcode',
                ],
                'shopeepay_image_url' => [
                    'type'  => 'hidden',
                    'class' => 'input-image-qr',
                ],
            ];
        }

        public function thankyou_page($order_id)
        {
            whp_wallet_render_payment_info(
                $this->account_name,
                $this->account_number,
                $this->image_url,
                'ShopeePay',
                '#ee4d2d',
                $order_id
            );
        }

        public function email_instructions($order, $sent_to_admin, $plain_text = false)
        {
            if (!$sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status('pending')) {
                echo '<p><strong>' . __('Thanh toán qua ShopeePay', 'wphp-wc') . '</strong><br>';
                echo esc_html__('Tên tài khoản: ', 'wphp-wc') . esc_html($this->account_name) . '<br>';
                echo esc_html__('Số điện thoại: ', 'wphp-wc') . esc_html($this->account_number) . '</p>';
            }
        }

        public function process_admin_options() {
            parent::process_admin_options();
            $field_key = $this->get_field_key('shopeepay_image_url');
            if (isset($_POST[$field_key])) {
                $settings = get_option($this->get_option_key(), []);
                $settings['shopeepay_image_url'] = esc_url_raw(wp_unslash($_POST[$field_key]));
                update_option($this->get_option_key(), $settings);
                $this->init_settings();
                $this->image_url = $settings['shopeepay_image_url'];
            }
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            $order->update_status('pending', __('Chờ thanh toán qua ShopeePay', 'wphp-wc'));
            WC()->cart->empty_cart();
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ];
        }
    }
}
