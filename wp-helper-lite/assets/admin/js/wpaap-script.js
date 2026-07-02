function wpaapToggleKeyField(value) {
    var wrapper = document.getElementById('wpaap_api_key_wrapper');
    if (!wrapper) {
        return;
    }
    if (value === 'core') {
        wrapper.classList.add('wpaap-hidden');
    } else {
        wrapper.classList.remove('wpaap-hidden');
    }
}

jQuery(document).ready(function($) {
    // Kích hoạt trạng thái ẩn/hiện API Key (connection page)
    wpaapToggleKeyField($('#wpaap_ai_provider').val());

    // --- Floating AI Chatbot Handlers ---

    // Toggle chatbot drawer
    $('#wpaap_chatbot_toggle, #wpaap_chatbot_close').on('click', function(e) {
        e.preventDefault();
        $('#wpaap_chatbot_drawer').toggleClass('wpaap-open');
        wpaap_chatbot_scroll_bottom();
    });

    // Share submission logic for both forms
    function wpaap_send_chat_message(inputId, boxId, btnId) {
        var $input = $(inputId);
        var promptText = $input.val().trim();
        if (promptText === '') return;

        var $chatBox = $(boxId);
        var $btn = $(btnId);

        // 1. Thêm tin nhắn user vào chatbox
        var userHtml = '<div class="wpaap-chat-msg-user">' +
            '<div class="wpaap-chat-avatar-user"><span class="dashicons dashicons-admin-users"></span></div>' +
            '<div class="wpaap-chat-bubble-user">' + esc_html(promptText) + '</div>' +
            '</div>';
        $chatBox.append(userHtml);
        $input.val('');
        wpaap_scroll_box($chatBox);

        // 2. Thêm hiệu ứng loading
        var loadingHtml = '<div class="wpaap-chat-msg-ai wpaap-chat-loading-bubble">' +
            '<div class="wpaap-chat-avatar-ai"><span class="dashicons dashicons-reddit"></span></div>' +
            '<div class="wpaap-chat-bubble-ai" style="display:flex; gap:4px; align-items:center; min-height: 20px;">' +
            '<span class="wpaap-typing-dot"></span>' +
            '<span class="wpaap-typing-dot"></span>' +
            '<span class="wpaap-typing-dot"></span>' +
            '</div>' +
            '</div>';
        $chatBox.append(loadingHtml);
        wpaap_scroll_box($chatBox);

        $btn.prop('disabled', true).css('opacity', '0.6');

        // 3. Gửi AJAX lên server
        $.ajax({
            url: wpaap_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpaap_chat_ai',
                nonce: wpaap_ajax.nonce,
                prompt: promptText
            },
            success: function(response) {
                $chatBox.find('.wpaap-chat-loading-bubble').remove();
                $btn.prop('disabled', false).css('opacity', '1');

                if (response.success && response.data.response) {
                    var responseText = response.data.response;
                    var formattedText = format_chat_text(responseText);
                    var escapedAttr = responseText.replace(/"/g, '&quot;').replace(/'/g, '&#39;');

                    var aiHtml = '<div class="wpaap-chat-msg-ai">' +
                        '<div class="wpaap-chat-avatar-ai"><span class="dashicons dashicons-reddit"></span></div>' +
                        '<div class="wpaap-chat-bubble-ai">' +
                        '<div>' + formattedText + '</div>' +
                        '<div class="wpaap-chat-actions">' +
                        '<a class="wpaap-chat-copy-btn wpaap-chat-action-link" data-content="' + escapedAttr + '"><span class="dashicons dashicons-admin-page"></span> Sao chép</a>' +
                        '<a class="wpaap-chat-apply-btn wpaap-chat-action-link primary" data-content="' + escapedAttr + '"><span class="dashicons dashicons-edit"></span> Dùng làm ý tưởng soạn bài</a>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                    $chatBox.append(aiHtml);
                } else {
                    var errMsg = response.data && response.data.message ? response.data.message : 'Có lỗi xảy ra khi kết nối AI.';
                    var errorHtml = '<div class="wpaap-chat-msg-ai">' +
                        '<div class="wpaap-chat-avatar-ai" style="background:#d63638;"><span class="dashicons dashicons-warning"></span></div>' +
                        '<div class="wpaap-chat-bubble-ai" style="border-color: #fca5a5; background: #fff5f5; color: #b91c1c;">' +
                        '<strong>Lỗi kết nối AI:</strong> ' + esc_html(errMsg) +
                        '</div>' +
                        '</div>';
                    $chatBox.append(errorHtml);
                }
                wpaap_scroll_box($chatBox);
            },
            error: function() {
                $chatBox.find('.wpaap-chat-loading-bubble').remove();
                $btn.prop('disabled', false).css('opacity', '1');

                var errorHtml = '<div class="wpaap-chat-msg-ai">' +
                    '<div class="wpaap-chat-avatar-ai" style="background:#d63638;"><span class="dashicons dashicons-warning"></span></div>' +
                    '<div class="wpaap-chat-bubble-ai" style="border-color: #fca5a5; background: #fff5f5; color: #b91c1c;">' +
                    'Lỗi máy chủ: Không thể kết nối AJAX.' +
                    '</div>' +
                    '</div>';
                $chatBox.append(errorHtml);
                wpaap_scroll_box($chatBox);
            }
        });
    }

    function wpaap_scroll_box($box) {
        if ($box.length) {
            $box.scrollTop($box[0].scrollHeight);
        }
    }

    // Submit events for both forms
    $('#wpaap_chatbot_form').on('submit', function(e) {
        e.preventDefault();
        wpaap_send_chat_message('#wpaap_chatbot_input', '#wpaap_chatbot_messages', '#wpaap_chatbot_send_btn');
    });

    $('#wpaap_chat_form').on('submit', function(e) {
        e.preventDefault();
        wpaap_send_chat_message('#wpaap_chat_input', '#wpaap_chat_box', '#wpaap_chat_send_btn');
    });

    // Sao chép tin nhắn chatbot
    $(document).on('click', '.wpaap-chat-copy-btn', function(e) {
        e.preventDefault();
        var text = $(this).attr('data-content');
        var $link = $(this);
        
        navigator.clipboard.writeText(text).then(function() {
            var oldHtml = $link.html();
            $link.html('<span class="dashicons dashicons-yes" style="color: #10b981;"></span> Đã sao chép');
            setTimeout(function() {
                $link.html(oldHtml);
            }, 1500);
        });
    });

    // Dùng làm ý tưởng soạn bài (chuyển hướng nếu cần)
    $(document).on('click', '.wpaap-chat-apply-btn', function(e) {
        e.preventDefault();
        var text = $(this).attr('data-content');
        var $promptField = $('#wpaap_user_prompt');

        if ($promptField.length > 0) {
            // Đang ở trang Soạn bài
            $promptField.val(text).focus();
            $('html, body').animate({
                scrollTop: $promptField.offset().top - 100
            }, 500);
        } else {
            // Ở trang khác, lưu vào sessionStorage và chuyển hướng về trang Soạn bài
            sessionStorage.setItem('wpaap_pending_prompt', text);
            window.location.href = 'admin.php?page=wp-ai-auto-poster-writer';
        }
    });

    // Kiểm tra và nạp prompt chờ ở trang Soạn bài khi tải trang
    var pendingPrompt = sessionStorage.getItem('wpaap_pending_prompt');
    if (pendingPrompt) {
        var $promptField = $('#wpaap_user_prompt');
        if ($promptField.length > 0) {
            $promptField.val(pendingPrompt).focus();
            sessionStorage.removeItem('wpaap_pending_prompt');
            setTimeout(function() {
                $('html, body').animate({
                    scrollTop: $promptField.offset().top - 100
                }, 500);
            }, 300);
        }
    }

    // Xử lý Kiểm tra kết nối AI qua AJAX
    $(document).on('click', '.wpaap-test-conn-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var provider = $btn.data('provider');
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update wpaap-spinning" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px; vertical-align: text-bottom; margin-right: 4px; display: inline-block; animation: wpaap-rotate-cw 2s linear infinite;"></span> Đang thử...');
        
        var $row = $btn.closest('.mb-conn-row');
        var $resultBox = $row.next('.wpaap-test-result-box');
        if ($resultBox.length === 0) {
            $row.after('<div class="wpaap-test-result-box" style="display:none; background:#fafafa; border:1px solid #e2e8f0; border-radius:8px; padding:12px 18px; margin-top:-10px; margin-bottom:15px; font-size:13px; line-height:1.5;"></div>');
            $resultBox = $row.next('.wpaap-test-result-box');
        }
        $resultBox.hide().html('');

        $.ajax({
            url: wpaap_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wpaap_test_connection',
                nonce: wpaap_ajax.nonce,
                provider: provider
            },
            success: function(response) {
                $btn.prop('disabled', false).html(originalText);
                if (response.success) {
                    $resultBox.css({
                        'border-color': '#10b981',
                        'background': '#ecfdf5',
                        'color': '#065f46'
                    }).html('<strong>Kết nối thành công!</strong> Phản hồi từ AI: <br><p style="margin:5px 0 0 0; font-style:italic;">' + format_chat_text(response.data.response) + '</p>').slideDown();
                } else {
                    $resultBox.css({
                        'border-color': '#f87171',
                        'background': '#fef2f2',
                        'color': '#991b1b'
                    }).html('<strong>Kết nối thất bại!</strong> Chi tiết lỗi:<br><p style="margin:5px 0 0 0; white-space:pre-wrap; font-family:monospace; font-size:12px; background:#fff; border:1px solid #fee2e2; padding:8px; border-radius:4px; max-height: 200px; overflow-y: auto;">' + response.data.message + '</p>').slideDown();
                }
            },
            error: function() {
                $btn.prop('disabled', false).html(originalText);
                $resultBox.css({
                    'border-color': '#f87171',
                    'background': '#fef2f2',
                    'color': '#991b1b'
                }).html('<strong>Lỗi hệ thống máy chủ:</strong> Không thể kết nối AJAX (Mã lỗi 500).').slideDown();
            }
        });
    });

    function wpaap_chatbot_scroll_bottom() {
        wpaap_scroll_box($('#wpaap_chatbot_messages'));
    }

    function esc_html(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function format_chat_text(text) {
        if (!text) return '';
        var formatted = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        formatted = formatted.replace(/\*(.*?)\*/g, '<em>$1</em>');
        formatted = formatted.replace(/\n/g, '<br>');
        return formatted;
    }

    // ============================================================
    // CONNECT CONFIRM MODAL
    // Intercept .mb-conn-form (connect only) → show security popup
    // ============================================================
    var $wpaapConnModal = null;
    var $wpaapPendingForm = null;
    var wpaapConnConfirmed = false;

    function wpaapBuildConnModal() {
        if ($wpaapConnModal && $wpaapConnModal.length) return;
        var i18n    = (typeof wpaap_ajax !== 'undefined' && wpaap_ajax.conn_i18n) || {};
        var title   = i18n.title   || 'Xác nhận kết nối';
        var bullet1 = i18n.bullet1 || 'API Key được <strong>mã hóa AES-256</strong> và lưu trữ an toàn vào cơ sở dữ liệu website của bạn.';
        var bullet2 = i18n.bullet2 || 'Chúng tôi <strong>không lưu trữ hoặc chia sẻ</strong> khóa API của bạn với bất kỳ bên thứ ba nào.';
        var bullet3 = i18n.bullet3 || 'Bạn có thể <strong>ngắt kết nối và xóa</strong> API Key bất kỳ lúc nào từ trang này.';
        var cancelTxt  = i18n.cancel  || 'Hủy';
        var confirmTxt = i18n.confirm || 'Đồng ý & Kết nối';
        var closeLbl   = i18n.close   || 'Đóng';
        var svgCheck = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>';
        var html =
            '<div id="wpaap-conn-overlay" style="display:none;position:fixed;inset:0;z-index:999999;background:rgba(15,23,42,.6);align-items:center;justify-content:center;">' +
            '<div id="wpaap-conn-box" style="background:#fff;border-radius:16px;max-width:440px;width:92%;padding:32px 28px 24px;box-shadow:0 24px 64px rgba(0,0,0,.22);position:relative;">' +
            '<button id="wpaap-conn-close-x" type="button" style="position:absolute;top:14px;right:16px;background:none;border:none;cursor:pointer;color:#94a3b8;font-size:22px;line-height:1;padding:2px 6px;" aria-label="' + closeLbl + '">&times;</button>' +
            '<div style="text-align:center;margin-bottom:22px">' +
            '<div style="width:52px;height:52px;border-radius:14px;background:#eff6ff;display:inline-flex;align-items:center;justify-content:center;margin-bottom:12px">' +
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>' +
            '</div>' +
            '<h3 style="margin:0 0 4px;font-size:17px;font-weight:700;color:#0f172a">' + title + '</h3>' +
            '<p id="wpaap-conn-provider-label" style="margin:0;font-size:13px;color:#64748b"></p>' +
            '</div>' +
            '<ul style="list-style:none;padding:0;margin:0 0 22px;display:flex;flex-direction:column;gap:11px">' +
            '<li style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#374151">' +
            '<span style="flex-shrink:0;width:20px;height:20px;border-radius:50%;background:#dcfce7;display:inline-flex;align-items:center;justify-content:center;margin-top:1px">' + svgCheck + '</span>' +
            '<span>' + bullet1 + '</span>' +
            '</li>' +
            '<li style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#374151">' +
            '<span style="flex-shrink:0;width:20px;height:20px;border-radius:50%;background:#dcfce7;display:inline-flex;align-items:center;justify-content:center;margin-top:1px">' + svgCheck + '</span>' +
            '<span>' + bullet2 + '</span>' +
            '</li>' +
            '<li style="display:flex;align-items:flex-start;gap:10px;font-size:13px;color:#374151">' +
            '<span style="flex-shrink:0;width:20px;height:20px;border-radius:50%;background:#dcfce7;display:inline-flex;align-items:center;justify-content:center;margin-top:1px">' + svgCheck + '</span>' +
            '<span>' + bullet3 + '</span>' +
            '</li>' +
            '</ul>' +
            '<div style="display:flex;gap:10px">' +
            '<button id="wpaap-conn-cancel" type="button" style="flex:1;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;font-size:14px;font-weight:600;cursor:pointer;transition:background .15s">' + cancelTxt + '</button>' +
            '<button id="wpaap-conn-confirm" type="button" style="flex:2;padding:10px 16px;border:none;border-radius:8px;background:#2563eb;color:#fff;font-size:14px;font-weight:700;cursor:pointer;transition:background .15s">&#10003;&nbsp; ' + confirmTxt + '</button>' +
            '</div>' +
            '</div></div>';
        $('body').append(html);
        $wpaapConnModal = $('#wpaap-conn-overlay');

        $('#wpaap-conn-cancel, #wpaap-conn-close-x').on('click', wpaapCloseConnModal);
        $wpaapConnModal.on('click', function (e) {
            if ($(e.target).is($wpaapConnModal)) wpaapCloseConnModal();
        });
        $('#wpaap-conn-confirm').on('click', function () {
            var $form = $wpaapPendingForm; // capture before close() nulls it
            wpaapCloseConnModal();
            if ($form) {
                // native form[0].submit() skips jQuery handlers — add button name manually
                $form.append('<input type="hidden" name="wpaap_toggle_provider_connection" value="1">');
                $form[0].submit();
            }
        });
    }

    function wpaapOpenConnModal(providerLabel) {
        wpaapBuildConnModal();
        var prefix = (typeof wpaap_ajax !== 'undefined' && wpaap_ajax.conn_i18n && wpaap_ajax.conn_i18n.provider) || 'Nhà cung cấp:';
        $('#wpaap-conn-provider-label').text(providerLabel ? prefix + ' ' + providerLabel : '');
        $wpaapConnModal.css('display', 'flex');
        $('html').css('overflow', 'hidden');
    }

    function wpaapCloseConnModal() {
        if ($wpaapConnModal) $wpaapConnModal.css('display', 'none');
        $('html').css('overflow', '');
        $wpaapPendingForm = null;
    }

    $(document).on('submit', '.mb-conn-form', function (e) {
        e.preventDefault();
        $wpaapPendingForm = $(this);
        var label = $(this).closest('.mb-conn-row').find('.mb-conn-info h4').text().trim();
        wpaapOpenConnModal(label);
    });

});