<?php
defined('ABSPATH') || exit;

if (!class_exists('MB_WHP_Wallet_MoMo')) {

    class MB_WHP_Wallet_MoMo extends WC_Payment_Gateway
    {
        private $image_url;
        private $account_name;
        private $account_number;

        public function __construct()
        {
            $this->id                 = 'MB_WHP_Wallet_MoMo';
            $this->icon               = whp_get_icon('logo-momo.svg');
            $this->has_fields         = false;
            $this->method_title       = __('Ví điện tử MoMo', 'wphp-wc');
            $this->method_description = __('Cho phép thanh toán qua ví điện tử MoMo', 'wphp-wc');

            $this->init_form_fields();
            $this->init_settings();

            $this->title          = $this->get_option('title');
            $this->description    = $this->get_option('description');
            $this->image_url      = $this->get_option('image_url');
            $this->account_name   = $this->get_option('account_name');
            $this->account_number = $this->get_option('account_number');

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
                    'title'    => __('Tiêu đề', 'wphp-wc'),
                    'type'     => 'text',
                    'default'  => __('Thanh toán qua MoMo', 'wphp-wc'),
                    'desc_tip' => true,
                    'description' => __('Hiển thị ở trang thanh toán', 'wphp-wc'),
                ],
                'description' => [
                    'title'    => __('Mô tả', 'wphp-wc'),
                    'type'     => 'textarea',
                    'default'  => __('Thanh toán qua ví điện tử MoMo. An toàn và nhanh chóng!', 'wphp-wc'),
                    'desc_tip' => true,
                    'description' => __('Nhập mô tả của phương thức.', 'wphp-wc'),
                ],
                'account_number' => [
                    'title'       => __('Số điện thoại MoMo', 'wphp-wc'),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'description' => __('Nhập số điện thoại nhận tiền', 'wphp-wc'),
                ],
                'account_name' => [
                    'title' => __('Tên tài khoản MoMo', 'wphp-wc'),
                    'type'  => 'text',
                ],
                'button_upload' => [
                    'title' => __('Hình QR Code', 'wphp-wc'),
                    'type'  => 'button',
                    'class' => 'button-upload-qrcode',
                ],
                'image_url' => [
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
                'MoMo',
                '#ae2070',
                $order_id
            );
        }

        public function email_instructions($order, $sent_to_admin, $plain_text = false)
        {
            if (!$sent_to_admin && $this->id === $order->get_payment_method() && $order->has_status('pending')) {
                echo '<p><strong>' . __('Thanh toán qua MoMo', 'wphp-wc') . '</strong><br>';
                echo esc_html__('Tên tài khoản: ', 'wphp-wc') . esc_html($this->account_name) . '<br>';
                echo esc_html__('Số điện thoại: ', 'wphp-wc') . esc_html($this->account_number) . '</p>';
            }
        }

        public function process_admin_options() {
            parent::process_admin_options();
            $field_key = $this->get_field_key('image_url');
            if (isset($_POST[$field_key])) {
                $settings = get_option($this->get_option_key(), []);
                $settings['image_url'] = esc_url_raw(wp_unslash($_POST[$field_key]));
                update_option($this->get_option_key(), $settings);
                $this->init_settings();
                $this->image_url = $settings['image_url'];
            }
        }

        public function process_payment($order_id)
        {
            $order = wc_get_order($order_id);
            $order->update_status('pending', __('Chờ thanh toán qua MoMo', 'wphp-wc'));
            WC()->cart->empty_cart();
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url($order),
            ];
        }
    }
}

if (!function_exists('whp_wallet_render_payment_info')) {
    function whp_wallet_render_payment_info($account_name, $account_number, $image_url, $wallet_name, $accent_color = '#333', $order_id = 0)
    {
        $transfer_content = $order_id ? 'DH' . $order_id : '';
        ?>
        <style>
        .whp-wallet-info {
            margin: 24px 0;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            max-width: 480px;
            font-family: inherit;
        }
        .whp-wallet-info__header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            background: <?php echo esc_attr($accent_color); ?>;
            color: #fff;
        }
        .whp-wallet-info__header svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }
        .whp-wallet-info__header span {
            font-weight: 700;
            font-size: 15px;
        }
        .whp-wallet-info__body {
            padding: 18px;
            background: #fff;
        }
        .whp-wallet-info__notice {
            font-size: 13.5px;
            color: #6b7280;
            margin: 0 0 14px;
        }
        .whp-wallet-info__row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .whp-wallet-info__row:last-of-type {
            margin-bottom: 0;
        }
        .whp-wallet-info__label {
            font-size: 12.5px;
            color: #9ca3af;
            min-width: 110px;
        }
        .whp-wallet-info__value {
            font-size: 15px;
            font-weight: 700;
            color: #1f2937;
        }
        .whp-wallet-info__row--highlight {
            background: #fffbeb;
            border: 1px dashed #f59e0b;
        }
        .whp-wallet-info__transfer-code {
            color: #d97706;
            font-size: 16px;
            letter-spacing: 0.04em;
        }
        .whp-wallet-info__qr {
            text-align: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f3f4f6;
        }
        .whp-wallet-info__qr img {
            max-width: 200px;
            height: auto;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .whp-wallet-info__qr p {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 8px;
        }
        </style>

        <div class="whp-wallet-info">
            <div class="whp-wallet-info__header">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="2" y="5" width="20" height="14" rx="2"/>
                    <path d="M16 12h.01"/>
                </svg>
                <span><?php echo esc_html($wallet_name); ?></span>
            </div>
            <div class="whp-wallet-info__body">
                <p class="whp-wallet-info__notice">
                    <?php esc_html_e('Vui lòng chuyển tiền đến tài khoản bên dưới và nhập đúng nội dung chuyển khoản.', 'wphp-wc'); ?>
                </p>
                <?php if ($account_name) : ?>
                <div class="whp-wallet-info__row">
                    <span class="whp-wallet-info__label"><?php esc_html_e('Tên tài khoản', 'wphp-wc'); ?></span>
                    <span class="whp-wallet-info__value"><?php echo esc_html($account_name); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($account_number) : ?>
                <div class="whp-wallet-info__row">
                    <span class="whp-wallet-info__label"><?php esc_html_e('Số điện thoại', 'wphp-wc'); ?></span>
                    <span class="whp-wallet-info__value"><?php echo esc_html($account_number); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($transfer_content) : ?>
                <div class="whp-wallet-info__row whp-wallet-info__row--highlight">
                    <span class="whp-wallet-info__label"><?php esc_html_e('Nội dung CK', 'wphp-wc'); ?></span>
                    <span class="whp-wallet-info__value whp-wallet-info__transfer-code"><?php echo esc_html($transfer_content); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($image_url) : ?>
                <div class="whp-wallet-info__qr">
                    <img src="<?php echo esc_url($image_url); ?>" alt="QR Code <?php echo esc_attr($wallet_name); ?>">
                    <p><?php esc_html_e('Quét mã QR để thanh toán', 'wphp-wc'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
