jQuery(document).ready(function($){
    const date = [
        '5 phút', '10 phút', '15 phút', '20 phút', '30 phút', '45 phút', '1 giờ', '2 giờ', '3 giờ', '4 giờ', '5 giờ'
    ];
    const user = [
        'Anh Hùng','Chị Trang',
        'Anh Trung','Anh Tiến',
        'Chị Lan','Chị Hương',
        'Anh Phú','Anh Hoàng',
        'Chị My','Chị Trâm',
        'Chị Hoàng Anh',
        'Anh Tuấn','Anh Nghĩa',
        'Chú Hùng','Cô Lan', 'Anh Dũng','Chị Duyện','Anh Tài','Anh Việt','Quế Anh','Anh Minh'
    ];
    const randomData = (data) => {
        if (data) {
            return data[Math.floor(Math.random() * data.length)];
        }
    };

    function initNotification(){
        if (notification.length > 0) {
            const message_id = $('#mbwp-message-purchased');
            let randomDate = randomData(date);
            let randomUser = randomData(notification);
            let user_name  = randomData(user);
            message_id.html(
                `<div class="mbwp-message-purchase-main">
                    <button class="mbwp-notifi-close" aria-label="Đóng thông báo">&times;</button>
                    <div class="mbwp-notifi-image">
                        <a href="${randomUser['permalink']}">
                            <img src="${randomUser['images']}" alt="${randomUser['product_name']}">
                        </a>
                    </div>
                    <div class="mbwp-notifi-message-container">
                        <span class="mbwp-notifi-badge">Vừa được mua</span>
                        <a href="${randomUser['permalink']}">${randomUser['product_name']}</a>
                        <div class="mbwp-notifi-meta">
                            <span class="mbwp-notifi-name">${user_name}</span>
                            <span class="mbwp-notifi-dot">&middot;</span>
                            <small class="notifi-time">${randomDate} trước</small>
                        </div>
                    </div>
                </div>`
            );

            // Close button handler
            message_id.find('.mbwp-notifi-close').on('click', function () {
                hideNotification();
            });
        }
    }

    initNotification();

    (function loop() {
        var rand = Math.round(Math.random() * 5000) + 8000;
        setTimeout(function() {
            changeNotification();
            loop();
        }, rand);
    }());

    const changeNotification = () => {
        showNotification();
        setTimeout(function() {
            hideNotification();
        }, 3000); // duration
    };

    const showNotification = () => {
        const message_id = $('#mbwp-message-purchased');
        if (message_id.hasClass('show-effect')) {
            message_id.removeClass('show-effect');
        }
        message_id.addClass('show-effect');
    };

    const hideNotification = () => {
        const message_id = $('#mbwp-message-purchased');
        if (message_id.hasClass('show-effect')) {
            message_id.removeClass('show-effect');
        }
        setTimeout(function() {
            initNotification();
        }, 500);
    };

});
