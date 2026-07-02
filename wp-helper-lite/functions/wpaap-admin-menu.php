<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }


// 2. Khởi dựng Chatbot AI nổi ở chân trang quản trị của mô-đun AI Auto Poster
add_action( 'admin_footer', 'wpaap_render_global_chatbot' );
function wpaap_render_global_chatbot() {
    $page   = isset($_GET['page'])   ? sanitize_key($_GET['page'])   : '';
    $subtab = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : '';
    // Chỉ hiển thị trên trang AI Viết Bài
    if ( $page !== 'mb-wphelper-ai' || $subtab !== 'writer' ) {
        return;
    }
    ?>
    <div id="wpaap_chatbot_toggle" class="wpaap-chatbot-toggle" title="Trò chuyện với AI">
        <span class="dashicons dashicons-reddit"></span>
    </div>
    <div id="wpaap_chatbot_drawer" class="wpaap-chatbot-drawer">
        <div class="wpaap-chatbot-header">
            <div class="wpaap-chatbot-header-title">
                <span class="dashicons dashicons-reddit"></span>
                <span>Cố vấn AI (Chat Advisor)</span>
            </div>
            <button type="button" id="wpaap_chatbot_close" class="wpaap-chatbot-close-btn" title="Đóng">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
        <div id="wpaap_chatbot_messages" class="wpaap-chatbot-messages">
            <div class="wpaap-chat-msg-ai">
                <div class="wpaap-chat-avatar-ai">
                    <span class="dashicons dashicons-reddit"></span>
                </div>
                <div class="wpaap-chat-bubble-ai">
                    Xin chào! Tôi là Cố vấn AI. Hãy đặt câu hỏi để tôi gợi ý từ khóa, lên ý tưởng chủ đề hoặc phác thảo dàn bài giúp bạn soạn thảo nội dung nhanh chóng!
                </div>
            </div>
        </div>
        
        <form id="wpaap_chatbot_form" class="wpaap-chatbot-form">
            <input type="text" id="wpaap_chatbot_input" placeholder="Hỏi AI gợi ý chủ đề, tìm từ khóa..." autocomplete="off" />
            <button type="submit" id="wpaap_chatbot_send_btn" class="wpaap-chatbot-send-btn">
                Hỏi AI
            </button>
        </form>
    </div>
    <?php
}
