<?php
$order_key = $_GET['key'];
$order_id  = wc_get_order_id_by_order_key($order_key);
$order = wc_get_order($order_id);

$Model = $order->get_payment_method();
$classs = new $Model();
get_header();

?>
<style>
    body,
    h1,
    h2,
    p,
    table,
    img {
        margin: 0;
        padding: 0;
    }

    .thank-you-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background-color: #f4f4f4;
        display: flex;
        padding: 20px 0px;
    }

    .thank-you-content {
        text-align: center;
        max-width: 600px;
        padding: 20px;
        border-radius: 5px;
        background-color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);

    }

    .table-order-details {
        width: 100%;
        margin-top: 20px;
        border-collapse: collapse;
    }

    .table-order-details th,
    .table-order-details td {
        padding: 10px;
        border-bottom: 1px solid #ddd;
    }

    .payment-info {
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid #ddd;
    }

    .back-to-home {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        color: #007bff;
        transition: color 0.3s ease;
        color: #fff;
        padding: 10px;
        background-color: #000;
    }

    .back-to-home:hover {
        color: red;
    }

    .order-payment-content {
        display: flex;
        justify-content: space-evenly;
    }

    .info-payment p {
        text-align: left;
    }
</style>
<div class="thank-you-container">
    <div class="thank-you-content">
        <h1>Cám ơn bạn đã đặt hàng!</h1>
        <p>Đơn đặt hàng của bạn đã được đặt thành công. Chúng tôi rất vui được phục vụ bạn!</p>
        <div class="order-payment">
            <h3>Thông tin thanh toán</h3>
            <div class="order-payment-content">
                <div class="qrcode">
                    <img src="<?php echo $classs->get_option('image_url'); ?>">
                </div>
                <div class="info-payment" sty>
                    <p><strong>Phương thức thanh toán:</strong> <?php echo $order->get_payment_method_title() ?></p>
                    <p><strong>Tên chủ thẻ:</strong> <?php echo $classs->get_option('account_name'); ?></p>
                    <p><strong>Số tài khoản:</strong> <?php echo $classs->get_option('account_number'); ?></p>
                    <p><strong>Số tiền thanh toán:</strong> <?php echo wc_price($order->get_subtotal()); ?></p>
                    <p><strong>Nội dung chuyển khoản:</strong> #<?php echo $order->get_order_number(); ?></p>
                </div>
            </div>
        </div>
        <div class="order-detail">
            <h3>Thông tin đơn hàng</h3>
            <table class="table-order-details">
                <tr>
                    <th>Tên sản phẩm:</th>
                    <th>Số lượng:</th>
                    <th>Thành thiền:</th>
                </tr>
                <?php foreach ($order->get_items() as $key => $item) { ?>

                    <tr>
                        <td> <?php echo $item['name'];   ?></td>
                        <td><?php echo $item['quantity'] ?></td>
                        <td><?php echo wc_price($item['subtotal']);  ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="2">Tổng cộng</td>
                    <td><?php echo wc_price($order->get_total() + $order->get_discount_total())  ?></td>
                </tr>
                <tr>
                    <td colspan="2">Giảm giá</td>
                    <td><?php echo wc_price($order->get_discount_total())  ?></td>
                </tr>
                <tr>
                    <td colspan="2">Thanh toán</td>
                    <td><?php echo wc_price($order->get_subtotal())  ?></td>
                </tr>
            </table>
        </div>
        <a href="/" class="back-to-home">Tiếp tục mua sắm</a>
    </div>
</div>
<?php
get_footer();
?>