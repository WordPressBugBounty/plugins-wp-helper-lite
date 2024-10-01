<?php

class MB_WHP_Wallet_MoMo extends WC_Payment_Gateway
{
    /**
     * Class constructor, more about it in Step 3
     */
    private $instructions;
    private $image_url;
    private $account_name;
    private $account_number;
    public function __construct()
    {

        $this->id                 = 'MB_WHP_Wallet_MoMo';
        $this->icon               = whp_get_icon('logo-momo.svg');
        $this->has_fields         = false;
        $this->method_title       = __('Ví điện tử Momo', 'whp-setting');
        $this->method_description = __('Cho phép thanh toán qua ví điện tử Momo', 'whp-setting');
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);

        $this->title        = $this->get_option('title');
        $this->description  = $this->get_option('description');
        $this->instructions = $this->get_option('instructions', $this->description);
        $this->image_url = $this->get_option('image_url');
        $this->account_name = $this->get_option('account_name');

        $this->account_number = $this->get_option('account_number');

        add_action('woocommerce_email_before_order_table', [$this, 'email_instructions'], 10, 3);
    }
    /**
     * Plugin options, we deal with it in Step 3 too
     */


    public function init_form_fields()
    {
        $this->form_fields = apply_filters('wc_offline_form_fields', array(
            'enabled' => array(
                'title'   => __('Bật/Tắt', 'wphp-wc'),
                'type'    => 'checkbox',
                'label'   => __('Bật phương thức thanh toán', 'wphp-wc'),
                'default' => 'yes'
            ),
            'title' => array(
                'title'       => __('Tiêu đề', 'wphp-wc'),
                'type'        => 'text',
                'description' => __('Trường này sẽ hiện thị ở ngoài trang thanh toán', 'wphp-wc'),
                'default'     => __('Thanh toán qua MoMo', 'wphp-wc'),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __('Mô tả', 'wphp-wc'),
                'type'        => 'textarea',
                'description' => __('Nhập mô tả của phương thức.', 'wphp-wc'),
                'default'     => __('Thanh toán qua ví điện tử Momo. An toàn và nhanh chóng!', 'wphp-wc'),
                'desc_tip'    => true,
            ),
            'account_number'       => array(
                'title'       => __('Số điện thoại Momo', 'wphp-wc'),
                'type'        => 'text',
                'description' => __('Nhập số điện thoại nhận tiền', 'wphp-wc'),
                'desc_tip'    => true,
            ),
            'account_name'        => array(
                'title' => __('Tên tài khoản Momo', 'wphp-wc'),
                'type'  => 'text',
            ),
            'button_upload' => array(
                'title' => __('Hình QR Code', 'wphp-wc'),
                'type' => 'button',
                'desc_tip'      => false,
                'class'      => 'button-upload-qrcode',
                'placeholder' => '123'
            ),
            'image_url' => array(
                'type' => 'hidden',
                'class' => 'input-image-qr'
            ),
        ));
    }

    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($this->instructions && !$sent_to_admin && $this->id === $order->payment_method && $order->has_status('pending')) {
            $this->show($order->id);
        }
    }
    public function process_payment($order_id)
    {
        global $woocommerce;
        $order = new WC_Order($order_id);

        // Mark as on-hold (we're awaiting the payment)
        $order->update_status('pending', __('Chờ thanh toán', 'wphp-wc'));

        // Remove cart
        $woocommerce->cart->empty_cart();

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url($order)
        );
    }
}
