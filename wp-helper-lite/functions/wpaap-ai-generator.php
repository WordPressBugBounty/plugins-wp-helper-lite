<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
// Định nghĩa hàm kiểm tra kết nối Provider có API Key hay chưa
if ( ! function_exists( 'wpaap_is_provider_connected' ) ) {
    function wpaap_is_provider_connected( $provider ) {
        $is_connected = get_option( 'wpaap_core_connected', 'no' ) === 'yes';
        if ( ! $is_connected ) {
            return false;
        }
        
        $key = '';
        if ( $provider === 'google' ) {
            $key = get_option( 'connectors_gemini_api_key' ) ? get_option( 'connectors_gemini_api_key' ) : get_option( 'connectors_google_api_key' );
        } elseif ( $provider === 'anthropic' ) {
            $key = get_option( 'connectors_anthropic_api_key' );
        } elseif ( $provider === 'openai' ) {
            $key = get_option( 'connectors_openai_api_key' );
        }
        
        $key = trim( (string) $key );
        if ( empty( $key ) ) {
            return false;
        }
        
        return get_option( "wpaap_provider_connected_{$provider}", 'no' ) === 'yes';
    }
}

// Hàm gọi API trực tiếp không qua hệ thống Core AI WordPress 7.0
if ( ! function_exists( 'wpaap_call_ai_api_direct' ) ) {
    function wpaap_call_ai_api_direct( $model, $prompt, $options = [] ) {
        // Tự động chọn model tốt nhất trong số các model đã xác nhận hoạt động
        if ( $model === 'auto' ) {
            // flash-lite TRƯỚC: luôn 200 trong 1-4s, không bị 503 như flash/pro
            $auto_priority = ['gemini-3.5-flash', 'gemini-2.5-flash-lite', 'gemini-2.5-flash', 'gemini-2.0-flash'];
            foreach ( $auto_priority as $try_model ) {
                $result = wpaap_call_ai_api_direct( $try_model, $prompt, $options );
                if ( ! is_wp_error( $result ) ) {
                    return $result;
                }
            }
            return new WP_Error( 'auto_no_model', 'Không tìm được model Gemini khả dụng. Vui lòng chọn model cụ thể.' );
        }

        $provider = '';
        $provider_models = [
            'google' => ['gemini-3.5-flash', 'gemini-3.1-pro', 'gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.5-flash-lite', 'gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash-lite-001', 'gemini-1.5-flash', 'gemini-1.5-pro'],
            'anthropic' => ['claude-opus-4-8', 'claude-sonnet-4-6', 'claude-haiku-4-5-20251001', 'claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022', 'claude-3-5-sonnet', 'claude-3-opus', 'claude-3-haiku'],
            'openai' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo']
        ];

        foreach ( $provider_models as $prov => $mods ) {
            if ( in_array( $model, $mods ) ) {
                $provider = $prov;
                break;
            }
        }

        if ( empty( $provider ) ) {
            // Gemini model không còn trong whitelist → fallback về 3.5-flash
            if ( strpos( $model, 'gemini-' ) === 0 ) {
                update_option( 'wpaap_default_ai_model', 'gemini-3.5-flash' );
                return wpaap_call_ai_api_direct( 'gemini-3.5-flash', $prompt, $options );
            }
            return new WP_Error( 'invalid_model', 'Mô hình AI không hợp lệ: ' . esc_html( $model ) );
        }

        if ( $provider === 'google' ) {
            $api_key = get_option( 'connectors_gemini_api_key' ) ? get_option( 'connectors_gemini_api_key' ) : get_option( 'connectors_google_api_key' );
            $api_key = trim( (string) $api_key );
            if ( empty( $api_key ) ) {
                return new WP_Error( 'missing_key', 'Thiếu API Key cho Google Gemini.' );
            }
            
            // Map model name if needed
            $model_name = $model;
            
            // gemini-3.x và 2.5+ chỉ khả dụng trên v1beta — không thử v1 để tránh HTTP 400
            // gemini-2.0 và cũ hơn hỗ trợ cả v1beta lẫn v1 stable
            $thinking_models    = [ 'gemini-3.5-flash', 'gemini-3.1-pro', 'gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.5-flash-lite' ];
            $v1beta_only_models = $thinking_models;
            $urls = [
                'https://generativelanguage.googleapis.com/v1beta/models/' . $model_name . ':generateContent?key=' . $api_key,
            ];
            if ( ! in_array( $model_name, $v1beta_only_models, true ) ) {
                $urls[] = 'https://generativelanguage.googleapis.com/v1/models/' . $model_name . ':generateContent?key=' . $api_key;
            }

            // Chỉ gửi thinkingConfig cho model hỗ trợ extended thinking
            $thinking_models = [ 'gemini-3.5-flash', 'gemini-3.1-pro', 'gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.5-flash-lite' ];
            $body = [
                'contents' => [
                    [
                        'role'  => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [],
            ];
            if ( in_array( $model_name, $thinking_models, true ) ) {
                // Tắt extended thinking để tăng tốc độ phản hồi (60-120s → 10-20s)
                $body['generationConfig']['thinkingConfig'] = [ 'thinkingBudget' => 0 ];
            }
            if ( ! empty( $options['response_json'] ) ) {
                // Buộc model trả về JSON thuần (không preamble, không markdown)
                $body['generationConfig']['responseMimeType'] = 'application/json';
            }
            // 8000 tokens đủ cho bài 1800 từ JSON; 16384 quá cao làm Gemini chạy 300s+
            $body['generationConfig']['maxOutputTokens'] = ! empty( $options['max_tokens'] ) ? (int) $options['max_tokens'] : 8000;
            if ( empty( $body['generationConfig'] ) ) {
                unset( $body['generationConfig'] );
            }

            $response = null;
            $code = 0;
            $res_body = '';
            $last_wp_error = null;
            $attempts = [];

            foreach ( $urls as $url ) {
                $endpoint_type = ( strpos( $url, '/v1beta/' ) !== false ) ? 'v1beta' : 'v1';

                // v1 stable không hỗ trợ thinkingConfig và có thể không hỗ trợ responseMimeType mới
                $send_body = $body;
                if ( $endpoint_type === 'v1' ) {
                    unset( $send_body['generationConfig']['thinkingConfig'] );
                    unset( $send_body['generationConfig']['responseMimeType'] );
                    if ( isset( $send_body['generationConfig'] ) && empty( $send_body['generationConfig'] ) ) {
                        unset( $send_body['generationConfig'] );
                    }
                }

                $wp_remote_args = [
                    'headers' => [
                        'Content-Type'   => 'application/json',
                        'x-goog-api-key' => $api_key,
                    ],
                    'body'    => wp_json_encode( $send_body ),
                    'timeout' => 45,
                ];

                // Retry 1 lần: 429→sleep 10s, 503→sleep 5s. Giữ ngắn để fallback model nhanh hơn.
                $max_retries = 1;
                for ( $retry = 0; $retry <= $max_retries; $retry++ ) {
                    $url_start = microtime( true );
                    $current_response = wp_remote_post( $url, $wp_remote_args );
                    $url_elapsed = round( microtime( true ) - $url_start, 1 );

                    if ( is_wp_error( $current_response ) ) {
                        wpaap_log( "API {$endpoint_type} model={$model_name} WP_Error={$current_response->get_error_message()} elapsed={$url_elapsed}s" );
                        $last_wp_error = $current_response;
                        break;
                    }

                    $current_code = wp_remote_retrieve_response_code( $current_response );
                    $current_body = wp_remote_retrieve_body( $current_response );
                    wpaap_log( "API {$endpoint_type} model={$model_name} HTTP={$current_code} elapsed={$url_elapsed}s retry={$retry}" );

                    if ( in_array( $current_code, [ 429, 503 ], true ) && $retry < $max_retries ) {
                        $sleep_sec = $current_code === 429 ? 10 : 5;
                        wpaap_log( "HTTP {$current_code} — sleep {$sleep_sec}s trước retry " . ( $retry + 1 ) );
                        sleep( $sleep_sec );
                        continue;
                    }

                    $response = $current_response;
                    $code = $current_code;
                    $res_body = $current_body;
                    break;
                }

                $attempts[] = sprintf( '%s(%ds): HTTP %d', $endpoint_type, (int) $url_elapsed, $code ?: 0 );

                if ( $code === 200 ) {
                    break;
                }
            }

            // 404/429/503 vẫn còn sau retry → fallback chain theo thứ tự chất lượng giảm dần
            if ( in_array( $code, [ 404, 429, 503 ], true ) ) {
                $fallback_chain = [ 'gemini-3.5-flash', 'gemini-2.5-flash', 'gemini-2.0-flash' ];
                $idx = array_search( $model_name, $fallback_chain, true );
                $next = ( $idx === false ) ? $fallback_chain[0] : ( $fallback_chain[ $idx + 1 ] ?? null );
                if ( $next ) {
                    wpaap_log( "HTTP {$code} persistent — fallback: {$model_name} → {$next}" );
                    return wpaap_call_ai_api_direct( $next, $prompt, $options );
                }
            }

            if ( $code !== 200 ) {
                if ( is_wp_error( $response ) && $last_wp_error ) {
                    return $last_wp_error;
                }
                $err_details = implode( ' | ', $attempts );
                return new WP_Error( 'api_error', 'Google API trả về lỗi. Chi tiết: ' . $err_details );
            }

            $data = json_decode( $res_body, true );
            if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
                $finish_reason = $data['candidates'][0]['finishReason'] ?? '';
                if ( $finish_reason === 'MAX_TOKENS' ) {
                    wpaap_log( 'Gemini MAX_TOKENS hit — JSON bị cắt. model=' . $model_name );
                }
                return $data['candidates'][0]['content']['parts'][0]['text'];
            }

            return new WP_Error( 'parse_error', 'Không thể phân tích phản hồi từ Google Gemini.' );

        } elseif ( $provider === 'anthropic' ) {
            $api_key = trim( (string) get_option( 'connectors_anthropic_api_key' ) );
            if ( empty( $api_key ) ) {
                return new WP_Error( 'missing_key', 'Thiếu API Key cho Anthropic.' );
            }

            $model_name = $model;
            if ( $model_name === 'claude-3-5-sonnet' ) {
                $model_name = 'claude-3-5-sonnet-20241022';
            } elseif ( $model_name === 'claude-3-opus' ) {
                $model_name = 'claude-3-opus-20240229';
            } elseif ( $model_name === 'claude-3-haiku' ) {
                $model_name = 'claude-3-5-haiku-20241022';
            }

            $url = 'https://api.anthropic.com/v1/messages';
            
            $body = [
                'model' => $model_name,
                'max_tokens' => 4000,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ];

            $response = wp_remote_post( $url, [
                'headers' => [
                    'x-api-key' => $api_key,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json'
                ],
                'body' => wp_json_encode( $body ),
                'timeout' => 90
            ] );

            if ( is_wp_error( $response ) ) {
                return $response;
            }

            $code = wp_remote_retrieve_response_code( $response );
            $res_body = wp_remote_retrieve_body( $response );

            if ( $code !== 200 ) {
                return new WP_Error( 'api_error', 'Anthropic API trả về mã lỗi ' . $code . ': ' . $res_body );
            }

            $data = json_decode( $res_body, true );
            if ( isset( $data['content'][0]['text'] ) ) {
                return $data['content'][0]['text'];
            }

            return new WP_Error( 'parse_error', 'Không thể phân tích phản hồi từ Anthropic.' );

        } elseif ( $provider === 'openai' ) {
            $api_key = trim( (string) get_option( 'connectors_openai_api_key' ) );
            if ( empty( $api_key ) ) {
                return new WP_Error( 'missing_key', 'Thiếu API Key cho OpenAI.' );
            }

            $model_name = $model;

            $url = 'https://api.openai.com/v1/chat/completions';

            $body = [
                'model' => $model_name,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ]
            ];

            $response = wp_remote_post( $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $api_key,
                    'Content-Type' => 'application/json'
                ],
                'body' => wp_json_encode( $body ),
                'timeout' => 90
            ] );

            if ( is_wp_error( $response ) ) {
                return $response;
            }

            $code = wp_remote_retrieve_response_code( $response );
            $res_body = wp_remote_retrieve_body( $response );

            if ( $code !== 200 ) {
                return new WP_Error( 'api_error', 'OpenAI API trả về mã lỗi ' . $code . ': ' . $res_body );
            }

            $data = json_decode( $res_body, true );
            if ( isset( $data['choices'][0]['message']['content'] ) ) {
                return $data['choices'][0]['message']['content'];
            }

            return new WP_Error( 'parse_error', 'Không thể phân tích phản hồi từ OpenAI.' );
        }

        return new WP_Error( 'unknown', 'Có lỗi xảy ra.' );
    }
}

// Hàm chẩn đoán các model khả dụng cho API Key Google Gemini
if ( ! function_exists( 'wpaap_diagnose_gemini_models' ) ) {
    function wpaap_diagnose_gemini_models( $api_key ) {
        $api_key = trim( (string) $api_key );
        if ( empty( $api_key ) ) {
            return new WP_Error( 'missing_key', 'Thiếu API Key.' );
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . $api_key;
        
        $response = wp_remote_get( $url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $api_key
            ],
            'timeout' => 30
        ] );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code !== 200 ) {
            return new WP_Error( 'api_error', 'Mã ' . $code . ': ' . $body );
        }

        $data = json_decode( $body, true );
        if ( ! isset( $data['models'] ) || ! is_array( $data['models'] ) ) {
            return new WP_Error( 'parse_error', 'Không tìm thấy trường "models" trong phản hồi.' );
        }

        $models = [];
        foreach ( $data['models'] as $m ) {
            if ( isset( $m['name'] ) ) {
                $name = str_replace( 'models/', '', $m['name'] );
                $supported = '';
                if ( isset( $m['supportedGenerationMethods'] ) && is_array( $m['supportedGenerationMethods'] ) ) {
                    if ( in_array( 'generateContent', $m['supportedGenerationMethods'] ) ) {
                        $supported = ' (Có hỗ trợ)';
                    } else {
                        $supported = ' (Không hỗ trợ)';
                    }
                }
                $models[] = '- ' . $name . $supported;
            }
        }

        return $models;
    }
}

// 3. Hàm gọi Core AI hoặc API độc lập bên ngoài để sinh nội dung
/**
 * Kiểm tra chất lượng bài viết do AI sinh ra.
 * Trả về mảng các vấn đề; mảng rỗng = bài đạt chuẩn.
 */
function wpaap_render_sections_to_html( array $sections ) {
    $html = '';
    foreach ( $sections as $section ) {
        $type = isset( $section['type'] ) ? $section['type'] : '';
        switch ( $type ) {
            case 'summary':
                $html .= '[TOP_SUMMARY]' . "\n" . ( isset( $section['content'] ) ? $section['content'] : '' ) . "\n[/TOP_SUMMARY]\n";
                break;
            case 'toc':
                $toc = '[TOC]';
                if ( isset( $section['items'] ) && is_array( $section['items'] ) ) {
                    foreach ( $section['items'] as $item ) {
                        $toc .= "\n" . ( isset( $item['text'] ) ? $item['text'] : '' );
                        if ( isset( $item['sub'] ) && is_array( $item['sub'] ) ) {
                            foreach ( $item['sub'] as $sub ) {
                                $toc .= "\n  " . ( isset( $sub['text'] ) ? $sub['text'] : '' );
                            }
                        }
                    }
                }
                $html .= $toc . "\n[/TOC]\n";
                break;
            case 'h2':
                $id   = isset( $section['id'] )   ? ' id="' . esc_attr( $section['id'] ) . '"' : '';
                $text = isset( $section['text'] ) ? $section['text'] : '';
                $html .= "<h2{$id}>" . esc_html( $text ) . "</h2>\n";
                break;
            case 'h3':
                $id   = isset( $section['id'] )   ? ' id="' . esc_attr( $section['id'] ) . '"' : '';
                $text = isset( $section['text'] ) ? $section['text'] : '';
                $html .= "<h3{$id}>" . esc_html( $text ) . "</h3>\n";
                break;
            case 'text':
                $html .= ( isset( $section['html'] ) ? $section['html'] : '' ) . "\n";
                break;
            case 'image':
                $title   = isset( $section['title'] )   ? $section['title']   : '';
                $alt     = isset( $section['alt'] )     ? $section['alt']     : '';
                $caption = isset( $section['caption'] ) ? $section['caption'] : '';
                $prompt  = isset( $section['prompt'] )  ? $section['prompt']  : '';
                $html   .= "[AI_IMAGE]\nTitle: {$title}\nAlt: {$alt}\nCaption: {$caption}\nPrompt: {$prompt}\n[/AI_IMAGE]\n";
                break;
            case 'box':
                $style   = strtolower( isset( $section['style'] ) ? $section['style'] : 'info' );
                $map     = array( 'tip' => 'TIP_BOX', 'warning' => 'WARNING_BOX', 'expert' => 'EXPERT_BOX', 'info' => 'INFO_BOX' );
                $tag     = isset( $map[ $style ] ) ? $map[ $style ] : 'INFO_BOX';
                $title   = isset( $section['title'] )   ? $section['title']   : '';
                $content = isset( $section['content'] ) ? $section['content'] : '';
                $html   .= "[{$tag}]\nTiêu đề:\n{$title}\n\nNội dung:\n{$content}\n[/{$tag}]\n";
                break;
            case 'checklist':
                $items = isset( $section['items'] ) && is_array( $section['items'] ) ? $section['items'] : array();
                $html .= '[CHECKLIST]';
                foreach ( $items as $item ) {
                    $html .= "\n" . $item;
                }
                $html .= "\n[/CHECKLIST]\n";
                break;
            case 'table':
                $headers = isset( $section['headers'] ) && is_array( $section['headers'] ) ? $section['headers'] : array();
                $rows    = isset( $section['rows'] )    && is_array( $section['rows'] )    ? $section['rows']    : array();
                $cap     = isset( $section['caption'] ) ? '<caption>' . esc_html( $section['caption'] ) . '</caption>' : '';
                $t       = "<table>{$cap}<thead><tr>";
                foreach ( $headers as $h ) {
                    $t .= '<th>' . esc_html( $h ) . '</th>';
                }
                $t .= '</tr></thead><tbody>';
                foreach ( $rows as $row ) {
                    $t .= '<tr>';
                    if ( is_array( $row ) ) {
                        foreach ( $row as $cell ) {
                            $t .= '<td>' . esc_html( $cell ) . '</td>';
                        }
                    }
                    $t .= '</tr>';
                }
                $t    .= '</tbody></table>';
                $html .= $t . "\n";
                break;
            case 'faq':
                $items = isset( $section['items'] ) && is_array( $section['items'] ) ? $section['items'] : array();
                $html .= '<div class="wpaap-faq-section">';
                foreach ( $items as $item ) {
                    $q     = isset( $item['q'] ) ? $item['q'] : '';
                    $a     = isset( $item['a'] ) ? $item['a'] : '';
                    $html .= '<div class="wpaap-faq-item"><h3 class="wpaap-faq-question">' . esc_html( $q ) . '</h3>'
                           . '<p class="wpaap-faq-answer">' . wp_kses_post( $a ) . '</p></div>';
                }
                $html .= '</div>' . "\n";
                break;
            case 'cta':
                $content = isset( $section['content'] ) ? $section['content'] : '';
                $html   .= "[CTA]\n{$content}\n[/CTA]\n";
                break;
        }
    }
    return $html;
}

function wpaap_check_article_quality( array $data ) {
    $issues   = array();
    $sections = isset( $data['sections'] ) && is_array( $data['sections'] ) ? $data['sections'] : array();

    // Lấy content để đếm từ (từ sections hoặc content field)
    if ( ! empty( $sections ) ) {
        $content = wpaap_render_sections_to_html( $sections );
    } else {
        $content = isset( $data['content'] ) ? $data['content'] : '';
    }

    // 1. Độ dài mục tiêu 1200-1800 từ
    $plain_text = wp_strip_all_tags( $content );
    $word_count = count( preg_split( '/\s+/u', trim( $plain_text ), -1, PREG_SPLIT_NO_EMPTY ) );
    if ( $word_count < 1200 ) {
        $issues[] = "Bài viết chỉ có ~{$word_count} từ, yêu cầu tối thiểu 1200 từ.";
    }
    if ( $word_count > 1800 ) {
        $issues[] = "Bài viết có ~{$word_count} từ, vượt quá tối đa 1800 từ — hãy rút gọn.";
    }

    // 2. Tối thiểu 5 H2
    if ( ! empty( $sections ) ) {
        $h2_count = 0;
        foreach ( $sections as $s ) {
            if ( isset( $s['type'] ) && $s['type'] === 'h2' ) $h2_count++;
        }
    } else {
        preg_match_all( '/<h2[\s>]/i', $content, $h2_m );
        $h2_count = count( $h2_m[0] );
    }
    if ( $h2_count < 5 ) {
        $issues[] = "Chỉ có {$h2_count} H2, yêu cầu tối thiểu 5 H2.";
    }

    // 3. Mỗi H2 phải có ≥2 H3
    if ( ! empty( $sections ) ) {
        $h2_without_2h3 = 0;
        $h3_cur         = 0;
        $in_h2          = false;
        foreach ( $sections as $s ) {
            $t = isset( $s['type'] ) ? $s['type'] : '';
            if ( $t === 'h2' ) {
                if ( $in_h2 && $h3_cur < 2 ) $h2_without_2h3++;
                $h3_cur = 0;
                $in_h2  = true;
            } elseif ( $t === 'h3' && $in_h2 ) {
                $h3_cur++;
            }
        }
        if ( $in_h2 && $h3_cur < 2 ) $h2_without_2h3++;
    } else {
        preg_match_all( '/<(h2|h3)[\s>]/i', $content, $hx_m );
        $h2_without_2h3 = 0;
        $h3_cur         = 0;
        $in_h2          = false;
        foreach ( $hx_m[1] as $tag ) {
            $tag = strtolower( $tag );
            if ( 'h2' === $tag ) {
                if ( $in_h2 && $h3_cur < 2 ) $h2_without_2h3++;
                $h3_cur = 0;
                $in_h2  = true;
            } elseif ( 'h3' === $tag && $in_h2 ) {
                $h3_cur++;
            }
        }
        if ( $in_h2 && $h3_cur < 2 ) $h2_without_2h3++;
    }
    if ( $h2_without_2h3 > 0 ) {
        $issues[] = "Có {$h2_without_2h3} H2 chưa đủ 2 H3 con.";
    }

    // 4. Mật độ ảnh theo quy tắc 4
    if ( ! empty( $sections ) ) {
        $img_count = 0;
        foreach ( $sections as $s ) {
            if ( isset( $s['type'] ) && $s['type'] === 'image' ) $img_count++;
        }
    } else {
        $ai_c      = (int) preg_match_all( '/\[AI_IMAGE\]/i', $content );
        $img_c     = (int) preg_match_all( '/<img[\s>]/i', $content );
        $ph_c      = (int) preg_match_all( '/(?:AI|USER)_IMAGE_PLACEHOLDER/i', $content );
        $img_count = max( $ai_c, $img_c, $ph_c );
    }
    if ( $word_count >= 1500 )      $min_imgs = 4;
    elseif ( $word_count >= 1200 )  $min_imgs = 3;
    else                            $min_imgs = 2;
    if ( $img_count < $min_imgs ) {
        $issues[] = "Chỉ có {$img_count} ảnh, cần tối thiểu {$min_imgs} cho bài ~{$word_count} từ.";
    }

    // 5. Bảng so sánh
    $has_table = false;
    if ( ! empty( $sections ) ) {
        foreach ( $sections as $s ) {
            if ( isset( $s['type'] ) && $s['type'] === 'table' ) { $has_table = true; break; }
        }
    } else {
        $has_table = stripos( $content, '<table' ) !== false;
    }
    if ( ! $has_table ) {
        $issues[] = "Thiếu table section (bảng so sánh).";
    }

    // 6. Checklist
    $has_checklist = false;
    if ( ! empty( $sections ) ) {
        foreach ( $sections as $s ) {
            if ( isset( $s['type'] ) && $s['type'] === 'checklist' ) { $has_checklist = true; break; }
        }
    } else {
        $has_checklist = stripos( $content, '[CHECKLIST]' ) !== false
                      || (bool) preg_match( '/type=["\']checkbox["\']|class=["\']wpaap-checklist/i', $content );
    }
    if ( ! $has_checklist ) {
        $issues[] = "Thiếu checklist section.";
    }

    // 7. FAQ tối thiểu 5 items
    $faq_count = 0;
    if ( ! empty( $sections ) ) {
        foreach ( $sections as $s ) {
            if ( isset( $s['type'] ) && $s['type'] === 'faq' && isset( $s['items'] ) && is_array( $s['items'] ) ) {
                $faq_count = count( $s['items'] );
                break;
            }
        }
    } else {
        // Count Q: lines inside [FAQ]...[/FAQ]
        if ( preg_match( '/\[FAQ\](.*?)\[\/FAQ\]/si', $content, $faq_m ) ) {
            preg_match_all( '/^Q\s*:/mi', $faq_m[1], $q_m );
            $faq_count = count( $q_m[0] );
        }
    }
    if ( $faq_count < 5 ) {
        $issues[] = "FAQ cần tối thiểu 5 câu hỏi, hiện có {$faq_count}.";
    }

    // 8. Featured Snippet / Summary
    $has_summary = false;
    if ( ! empty( $sections ) ) {
        foreach ( $sections as $s ) {
            if ( isset( $s['type'] ) && $s['type'] === 'summary' ) { $has_summary = true; break; }
        }
    } else {
        $has_summary = stripos( $content, '[TOP_SUMMARY]' ) !== false;
    }
    if ( ! $has_summary ) {
        $issues[] = "Thiếu summary section (Featured Snippet).";
    }

    // 9. CTA
    $has_cta = false;
    if ( ! empty( $sections ) ) {
        foreach ( $sections as $s ) {
            if ( isset( $s['type'] ) && $s['type'] === 'cta' ) { $has_cta = true; break; }
        }
    } else {
        $has_cta = stripos( $content, '[CTA]' ) !== false
                || (bool) preg_match( '/class=["\'][^"\']*wpaap-cta/i', $content );
    }
    if ( ! $has_cta ) {
        $issues[] = "Thiếu cta section trước kết luận.";
    }

    // 10. Meta Description
    $meta_desc = isset( $data['meta_description'] ) ? trim( $data['meta_description'] ) : '';
    if ( empty( $meta_desc ) || mb_strlen( $meta_desc ) < 50 ) {
        $issues[] = "Thiếu hoặc quá ngắn meta_description (yêu cầu 140-160 ký tự).";
    }

    return $issues;
}

function wpaap_generate_content_with_ai( $user_prompt, $featured_image_id = 0, $content_image_ids_str = '', $categories = array(), $tags = '', $ai_model = 'gemini-3.5-flash' ) {

    $content_image_ids = array_filter( array_map( 'intval', explode( ',', $content_image_ids_str ) ) );

    $image_instruction = "";
    if ( ! empty( $content_image_ids ) ) {
        $img_count         = count( $content_image_ids );
        $image_instruction = "Người dùng đã cung cấp {$img_count} ảnh. Chèn ĐÚNG {$img_count} shortcode [AI_IMAGE]...[/AI_IMAGE] vào các vị trí phù hợp trong \"content\" (theo mật độ quy tắc 4). Để trống trường Prompt — ảnh do người dùng cung cấp. Để trống \"featured_image_prompt\".";
    } else {
        $image_instruction = "Chèn đủ shortcode [AI_IMAGE]...[/AI_IMAGE] theo mật độ quy tắc 4 (bài ~1200 từ: 3 ảnh; ~1500 từ: 3-4 ảnh; ~1800 từ: 4-5 ảnh). "
            . "Mỗi [AI_IMAGE] có đủ 4 trường Title, Alt, Caption (tiếng Việt, chứa từ khóa), Prompt (English — áp dụng quy tắc 1, 2, 3). "
            . "Trường \"featured_image_prompt\" viết prompt tiếng Anh cho ảnh đại diện bài (Diagram hoặc Infographic theo quy tắc 2+3).";
    }

    $year = date('Y');
    $system_instruction = <<<SYSPROMPT
Bạn là chuyên gia viết nội dung SEO chuẩn E-E-A-T, viết bằng tiếng Việt. Năm hiện tại: {$year}.

==================================================
AI CONTENT & LAYOUT MASTER RULES
==================================================

MỤC TIÊU
Tạo bài viết chuẩn SEO, chuyên sâu, đẹp mắt và tối ưu trải nghiệm người dùng trên WordPress.

==================================================
QUY TẮC 1: KHÔNG CHÈN ẢNH NGẪU NHIÊN — BẮT BUỘC
==================================================
Ảnh PHẢI liên quan trực tiếp đến nội dung section đang viết.
TUYỆT ĐỐI NGHIÊM CẤM: phong cảnh, thiên nhiên, chim chóc, thành phố, đường phố, tượng đài,
kiến trúc, người mẫu, ảnh stock chung chung — trừ khi bài viết đề cập trực tiếp.
Keyword và prompt phải là tiếng Anh, cụ thể theo chủ đề bài.

==================================================
QUY TẮC 2: LOẠI ẢNH THEO CHỦ ĐỀ KỸ THUẬT
==================================================
Ưu tiên ảnh thực tế, ảnh chụp màn hình, ảnh sản phẩm thật:
Hosting/Server → "web hosting server rack datacenter"
WordPress      → "wordpress dashboard admin panel screenshot"
SEO            → "seo analytics google search console"
WooCommerce    → "woocommerce online store product page"
Cloud/VPS      → "cloud server infrastructure diagram"
Bảo mật        → "website security ssl certificate"
Tốc độ web     → "website speed performance pagespeed"
Domain         → "domain name registration dns"
Email          → "email marketing newsletter campaign"
Plugin/Theme   → "wordpress plugin theme customization"
Mặc định       → dùng chủ đề chính của bài (1-3 từ tiếng Anh)

==================================================
QUY TẮC 3: FORMAT KEYWORD CHO IMAGE SEARCH
==================================================
Field "image_keywords" trong JSON PHẢI là mảng các chuỗi tiếng Anh ngắn (2-5 từ),
mô tả chính xác NỘI DUNG CỦA SECTION đó — dùng để tìm kiếm ảnh thực trên Google/Bing.
VÍ DỤ ĐÚNG: "wordpress admin dashboard", "web hosting server", "ssl certificate security"
VÍ DỤ SAI: "beautiful modern design", "landscape nature", "general concept"

==================================================
QUY TẮC 4: MẬT ĐỘ ẢNH
==================================================
Bài ~1200 từ → 3 ảnh
Bài ~1500 từ → 3-4 ảnh
Bài ~1800 từ → 4-5 ảnh
Không chèn ảnh liên tục nhau.

==================================================
QUY TẮC 5: BOX NỘI DUNG
==================================================
Mỗi H2 tối đa 1 box (tip / warning / info / expert).
Không dùng nhiều box liên tiếp nhau.

==================================================
QUY TẮC 6: FEATURED SNIPPET
==================================================
Ngay sau <h1>: dùng shortcode [TOP_SUMMARY]...[/TOP_SUMMARY], 50-80 từ, trả lời trực tiếp câu hỏi chính.

==================================================
QUY TẮC 7: CHECKLIST
==================================================
Mỗi bài bắt buộc tối thiểu 1 [CHECKLIST]...[/CHECKLIST].
Quy tắc bắt buộc:
- Dòng đầu tiên PHẢI là: Title: [tiêu đề phù hợp chủ đề, 3-50 ký tự]
- Tối thiểu 4 mục, tối đa 8 mục
- Mỗi mục phải có nội dung cụ thể, có ý nghĩa thực tế, 5-20 từ
- Tuyệt đối không tạo mục rỗng, mục chỉ có ký hiệu, hoặc mục quá ngắn (dưới 5 từ)

==================================================
QUY TẮC 8: COMPARISON TABLE
==================================================
Nếu chủ đề có thể so sánh: bắt buộc 1 bảng <table><thead>...<tbody>...</table>.

==================================================
QUY TẮC 9: FAQ
==================================================
Tối thiểu 5 cặp câu hỏi-trả lời dùng cú pháp [FAQ]...[/FAQ] (xem bên dưới).

==================================================
QUY TẮC 10: CTA
==================================================
Trước phần kết luận: 1 [CTA]...[/CTA] với lời kêu gọi hành động cụ thể.

==================================================
QUY TẮC 11: CHẤT LƯỢNG SEO
==================================================
Mục tiêu 1200-1800 từ (plain text, không tính HTML tags). Tối đa 1800 từ — không được viết dài hơn.
Tối thiểu 5 thẻ <h2>, mỗi <h2> phải có ít nhất 1 <h3> bên dưới.
Tự kiểm tra toàn bộ trước khi xuất JSON.

==================================================
QUY TẮC 12: OUTPUT JSON — BẮT BUỘC
==================================================
Trả về DUY NHẤT một JSON object hợp lệ. Không markdown, không giải thích, không code block.

CÚ PHÁP SHORTCODE BẮT BUỘC trong "content":

[TOP_SUMMARY]Đoạn tóm tắt 50-80 từ.[/TOP_SUMMARY]

[TOC]
Tiêu đề H2 1
  Tiêu đề H3 1.1
  Tiêu đề H3 1.2
Tiêu đề H2 2
[/TOC]

[AI_IMAGE]
Title: Tiêu đề ảnh tiếng Việt
Alt: Mô tả ảnh tiếng Việt chứa từ khóa
Caption: Chú thích ảnh tiếng Việt
Prompt: English prompt: subject + context + style (per rule 1-3)
[/AI_IMAGE]

[INFO_BOX]Tiêu đề:\nTên box\n\nNội dung:\nText nội dung.[/INFO_BOX]
[TIP_BOX]Tiêu đề:\nMẹo\n\nNội dung:\nText mẹo.[/TIP_BOX]
[WARNING_BOX]Tiêu đề:\nCảnh báo\n\nNội dung:\nText cảnh báo.[/WARNING_BOX]
[EXPERT_BOX]Tiêu đề:\nKinh nghiệm\n\nNội dung:\nText kinh nghiệm.[/EXPERT_BOX]

[CHECKLIST]
Title: Tiêu đề phù hợp chủ đề checklist
✓ Mục kiểm tra cụ thể thứ nhất có ý nghĩa thực tế
✓ Mục kiểm tra cụ thể thứ hai đầy đủ nội dung
✓ Mục kiểm tra cụ thể thứ ba không dưới 5 từ
✓ Mục kiểm tra thứ tư (4-8 mục tổng cộng)
[/CHECKLIST]

[FAQ]
Q: Câu hỏi 1?
A: Câu trả lời 1.
Q: Câu hỏi 2?
A: Câu trả lời 2.
[/FAQ]

[CTA]Lời kêu gọi hành động cụ thể phù hợp chủ đề.[/CTA]

Cấu trúc JSON output:
{
  "title": "H1 của bài viết",
  "seo_title": "50-60 ký tự, chứa từ khóa, hấp dẫn tăng CTR",
  "meta_description": "140-160 ký tự, chứa từ khóa, thu hút click",
  "slug": "url-slug-khong-dau-gach-ngang-viet-thuong",
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"],
  "featured_image_prompt": "English prompt for featured image (Diagram/Infographic per rule 2+3)",
  "content": "<h1>Tiêu đề bài</h1>[TOP_SUMMARY]...[/TOP_SUMMARY][TOC]...[/TOC]<h2 id=\\"..\\">...</h2>...",
  "image_keywords": ["keyword 1", "keyword 2"]
}

Slug: không dấu, viết thường, gạch ngang. Ví dụ "Hosting là gì" → "hosting-la-gi".
Trong "content": escape dấu " thành \\" — ví dụ: id=\\"section-1\\".

{$image_instruction}

CHECKLIST TRƯỚC KHI XUẤT:
✓ content có [TOP_SUMMARY], [TOC], ≥5 thẻ <h2>, mỗi h2 có ≥1 <h3>
✓ Ảnh đúng mật độ quy tắc 4, prompt đúng quy tắc 1-3
✓ Có [INFO_BOX]/[TIP_BOX]/[WARNING_BOX]/[EXPERT_BOX] (tối đa 1 mỗi h2)
✓ Có [CHECKLIST] với Title: và 4-8 mục, mỗi mục 5-20 từ, nội dung cụ thể
✓ Có <table> so sánh (nếu chủ đề có thể so sánh)
✓ Có [FAQ] với ≥5 cặp Q/A
✓ Có [CTA] trước h2 kết luận
✓ Tổng nội dung 1200-1800 từ (không vượt quá 1800 từ)
✓ meta_description 140-160 ký tự

CRITICAL JSON RULES:
- Output CHỈ JSON hợp lệ, bắt đầu bằng { kết thúc bằng }
- Trong "content": escape " thành \\" và newline thành \\n
- Không markdown, không code block, không giải thích
SYSPROMPT;
    $final_prompt = $system_instruction . "\n\nYêu cầu bài viết: " . $user_prompt;

    $ai_response = '';

    // Lỗi Timeout cURL 28 (30s): Quá trình AI suy nghĩ và viết bài thường mất hơn 30s.
    // Dùng http_request_args và http_api_curl để ghi đè mọi thiết lập timeout ngầm.
    $timeout_filter = function( $args ) {
        $args['timeout'] = 90;
        return $args;
    };
    add_filter( 'http_request_args', $timeout_filter, 999 );

    $curl_filter = function( $handle ) {
        curl_setopt( $handle, CURLOPT_TIMEOUT, 90 );
        curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 15 );
    };
    add_action( 'http_api_curl', $curl_filter, 999 );

    $is_connected = get_option( 'wpaap_core_connected', 'no' ) === 'yes';
    if ( ! $is_connected ) {
        return new WP_Error( 'ai_disconnected', 'Vui lòng cấu hình API Key và bật kết nối trong trang Kết nối AI trước khi tạo bài viết.' );
    }
    try {
        $provider_models = [
            'google' => ['gemini-3.5-flash', 'gemini-3.1-pro', 'gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.5-flash-lite', 'gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash-lite-001', 'gemini-1.5-flash', 'gemini-1.5-pro'],
            'anthropic' => ['claude-opus-4-8', 'claude-sonnet-4-6', 'claude-haiku-4-5-20251001', 'claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022', 'claude-3-5-sonnet', 'claude-3-opus', 'claude-3-haiku'],
            'openai' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo']
        ];

        $selected_provider = '';
        if ( $ai_model === 'auto' ) {
            $selected_provider = 'google'; // auto = Gemini tự chọn
        } else {
            foreach ( $provider_models as $prov => $mods ) {
                if ( in_array( $ai_model, $mods ) ) {
                    $selected_provider = $prov;
                    break;
                }
            }
        }

        if ( ! empty( $selected_provider ) ) {
            $prov_connected = wpaap_is_provider_connected( $selected_provider );
            if ( ! $prov_connected ) {
                $provider_name = $selected_provider === 'google' ? 'Google Gemini' : ($selected_provider === 'anthropic' ? 'Anthropic Claude' : 'OpenAI GPT');
                return new WP_Error( 'ai_provider_disconnected', 'Dịch vụ ' . $provider_name . ' hiện chưa được cấu hình API Key hoặc chưa bật kết nối. Vui lòng kiểm tra lại.' );
            }
        }
        
        $models_to_try = [];
        // 'auto' và 'gemini-2.5-flash' đều dùng flash-lite TRƯỚC (1-4s) để tránh 503 flash (30-114s)
        $fast_first_models = [ 'auto', 'gemini-3.5-flash', 'gemini-3.1-pro', 'gemini-2.5-flash', 'gemini-2.5-pro' ];
        if ( in_array( $ai_model, $fast_first_models, true ) ) {
            if ( wpaap_is_provider_connected( 'google' ) ) {
                $models_to_try = [ 'gemini-3.5-flash', 'gemini-2.5-flash-lite', 'gemini-2.5-flash', 'gemini-2.0-flash' ];
            }
        } else {
            foreach ( $provider_models as $prov => $mods ) {
                $prov_connected = wpaap_is_provider_connected( $prov );
                if ( $prov_connected ) {
                    if ( $prov === $selected_provider ) {
                        array_unshift( $models_to_try, $ai_model );
                        // Chỉ lấy 2 fallback tiếp theo trong cùng provider
                        $count = 0;
                        foreach ( $mods as $m ) {
                            if ( $m !== $ai_model && $count < 2 ) {
                                $models_to_try[] = $m;
                                $count++;
                            }
                        }
                    } else {
                        // Fallback sang provider khác: chỉ lấy model đầu tiên
                        if ( ! empty( $mods ) ) {
                            $models_to_try[] = $mods[0];
                        }
                    }
                }
            }
            $models_to_try = array_values( array_unique( $models_to_try ) );
        }

        $ai_response = '';
        $last_error_msg = '';
        $successful_model = '';
        $tokens_from_api = 0;

        foreach ($models_to_try as $model) {
            $current_response = '';
            
            // Gọi trực tiếp API — yêu cầu JSON để tránh model trả về plain text
            $direct_result = wpaap_call_ai_api_direct( $model, $final_prompt, [ 'response_json' => true ] );
            if ( ! is_wp_error( $direct_result ) ) {
                $current_response = $direct_result;
            } else {
                $last_error_msg = $direct_result->get_error_message();
            }

            // Nếu có kết quả, thoát vòng lặp fallback
            if ( ! empty( $current_response ) ) {
                $ai_response      = $current_response;
                $successful_model = $model;
                break;
            }
        }

    // Kiểm tra đối tượng lỗi trả về trước khi xử lý chuỗi
        if ( is_wp_error( $ai_response ) ) {
            return $ai_response;
        }

        if ( empty($ai_response) ) {
            $user_friendly_err = $last_error_msg;
            if (strpos(strtolower($last_error_msg), '429') !== false || strpos(strtolower($last_error_msg), 'too many requests') !== false || strpos(strtolower($last_error_msg), 'quota exceeded') !== false) {
                $user_friendly_err = "Toàn bộ tài khoản API của bạn đã hết sạch lượt truy vấn miễn phí (Quota Limit) trong chu kỳ này. Vui lòng nghỉ ngơi khoảng 1-2 phút để Google hồi phục lại số lượt dùng, hoặc chủ động chọn một Mô hình AI khác ở ô Lựa chọn Model!";
            }
            return new WP_Error( 'ai_all_failed', '<strong>Hệ thống đã tự động thử tất cả các Mô hình AI (Fallback) nhưng đều quá tải!</strong><br><br><strong>Gợi ý:</strong> ' . $user_friendly_err );
        }

        // Chuyển đổi dữ liệu đảm bảo an toàn tuyệt đối, tránh lỗi sập khi không phải chuỗi
        $ai_response_str = is_string( $ai_response ) ? trim( $ai_response ) : wp_json_encode( $ai_response );
        
        // Loại bỏ markdown code block nếu AI trả về dạng ```json ... ```
        $ai_response_str = preg_replace('/^```(?:json)?\s*/mi', '', $ai_response_str);
        $ai_response_str = preg_replace('/```\s*$/mi', '', $ai_response_str);
        $ai_response_str = trim( $ai_response_str );

        // Trích xuất JSON bằng strpos để tránh lỗi tham lam (greedy) của regex
        $start = strpos( $ai_response_str, '{' );
        $end = strrpos( $ai_response_str, '}' );

        if ( $start !== false && $end !== false && $end > $start ) {
            $ai_response_str = substr( $ai_response_str, $start, $end - $start + 1 );
        }

        // Bỏ các ký tự control ẩn có thể gây lỗi JSON (giữ lại \n, \r, \t)
        $ai_response_str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $ai_response_str);

        // Thử parse trực tiếp trước (Gemini với responseMimeType trả về JSON hợp lệ)
        $data = json_decode( $ai_response_str, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            // Fallback: fixer cho trường hợp AI cũ / không dùng responseMimeType
            // Escape raw newline/tab bên trong chuỗi JSON
            $fixed_json = '';
            $in_s = false; $skip = false;
            $len  = strlen( $ai_response_str );
            for ( $i = 0; $i < $len; $i++ ) {
                $c = $ai_response_str[ $i ];
                if ( $skip ) { $fixed_json .= $c; $skip = false; continue; }
                if ( $c === '\\' ) { $fixed_json .= $c; $skip = true; continue; }
                if ( $c === '"' ) { $in_s = ! $in_s; $fixed_json .= $c; continue; }
                if ( $in_s ) {
                    if ( $c === "\n" ) { $fixed_json .= "\\n"; continue; }
                    if ( $c === "\r" ) { $fixed_json .= "\\r"; continue; }
                    if ( $c === "\t" ) { $fixed_json .= "\\t"; continue; }
                }
                $fixed_json .= $c;
            }
            $data = json_decode( $fixed_json, true );
        }

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            wpaap_log( 'JSON FAIL model=' . $successful_model . ' err=' . json_last_error_msg() . ' len=' . strlen( $ai_response_str ) );
            wpaap_log( 'JSON RAW first500: ' . mb_substr( $ai_response_str, 0, 500 ) );
            $debug_str = mb_substr( $ai_response_str, 0, 200 ) . '...';
            return new WP_Error( 'json_syntax_error', 'Lỗi phân tích JSON từ AI (' . json_last_error_msg() . '). Raw: ' . esc_html( $debug_str ) );
        }

        if ( empty( $data ) || ! is_array( $data ) || ! isset( $data['title'] ) || ( ! isset( $data['content'] ) && ! isset( $data['sections'] ) ) ) {
            return new WP_Error( 'json_parse_failed', 'Cấu trúc bài viết do AI gửi về bị thiếu trường dữ liệu hoặc không hợp lệ.' );
        }

        // ── KIỂM TRA CHẤT LƯỢNG BÀI VIẾT (chỉ log, không retry để tránh gấp đôi thời gian) ──
        $quality_issues = wpaap_check_article_quality( $data );
        if ( ! empty( $quality_issues ) ) {
            wpaap_log( 'QUALITY WARN model=' . $successful_model . ' issues=' . implode( '; ', $quality_issues ) );
        }
        // ────────────────────────────────────────────────────────────────────────

        // Tính toán và lưu trữ số lượng Tokens tiêu thụ
        $total_tokens = $tokens_from_api;
        if ( $total_tokens <= 0 ) {
            $prompt_words = count( explode( ' ', $final_prompt ) );
            $response_words = count( explode( ' ', $ai_response_str ) );
            $total_tokens = round( ($prompt_words + $response_words) * 1.5 );
        }

        $provider_models = [
            'google' => ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.5-flash-lite', 'gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash-lite-001', 'gemini-1.5-flash', 'gemini-1.5-pro'],
            'anthropic' => ['claude-opus-4-8', 'claude-sonnet-4-6', 'claude-haiku-4-5-20251001', 'claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022', 'claude-3-5-sonnet', 'claude-3-opus', 'claude-3-haiku'],
            'openai' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo']
        ];
        $matched_provider = '';
        foreach ( $provider_models as $prov => $mods ) {
            if ( in_array( $successful_model, $mods ) ) {
                $matched_provider = $prov;
                break;
            }
        }
        if ( ! empty( $matched_provider ) ) {
            $used = intval( get_option( "wpaap_tokens_used_{$matched_provider}", 0 ) );
            update_option( "wpaap_tokens_used_{$matched_provider}", $used + $total_tokens );
            if ( function_exists( 'wpaap_log_token_usage' ) ) {
                wpaap_log_token_usage( $matched_provider, $total_tokens );
            }
        }

        // Với format mới: sections[] → rút image prompts → render sang HTML
        if ( isset( $data['sections'] ) && is_array( $data['sections'] ) ) {
            $img_prompts_sec  = array();
            $img_keywords_sec = array();
            foreach ( $data['sections'] as $sec ) {
                if ( isset( $sec['type'] ) && $sec['type'] === 'image' ) {
                    $img_prompts_sec[]  = isset( $sec['prompt'] ) ? $sec['prompt'] : '';
                    $alt_words          = isset( $sec['alt'] ) ? explode( ' ', $sec['alt'] ) : array();
                    $img_keywords_sec[] = implode( ' ', array_slice( $alt_words, 0, 4 ) );
                }
            }
            if ( empty( $data['image_prompts'] ) ) {
                $data['image_prompts']  = $img_prompts_sec;
            }
            if ( empty( $data['image_keywords'] ) ) {
                $data['image_keywords'] = $img_keywords_sec;
            }
            if ( ! isset( $data['content'] ) ) {
                $data['content'] = wpaap_render_sections_to_html( $data['sections'] );
            }
        }

        // Hỗ trợ field "tags" (mới) và "tags_list" (cũ)
        if ( ! isset( $data['tags_list'] ) && isset( $data['tags'] ) && is_array( $data['tags'] ) ) {
            $data['tags_list'] = $data['tags'];
        }

        // Chuyển đổi shortcodes AI (INFO_BOX, FAQ, CHECKLIST...) sang HTML trước khi lưu
        if ( function_exists( 'wpaap_render_article_shortcodes' ) ) {
            wpaap_render_article_shortcodes( $data );
        }

        $post_content = $data['content'];

        // Pre-warm Pollinations: kích hoạt generate ảnh sớm (non-blocking) TRƯỚC khi insert post
        // Mục đích: khi đến bước sideload, ảnh đã được Pollinations cache sẵn
        if ( empty( $content_image_ids ) ) {
            $early_prompts = isset( $data['image_prompts'] ) && is_array( $data['image_prompts'] ) ? $data['image_prompts'] : array();
            foreach ( $early_prompts as $early_prompt ) {
                if ( empty( $early_prompt ) ) continue;
                $early_url = 'https://image.pollinations.ai/prompt/' . rawurlencode( trim( $early_prompt ) ) . '?width=1200&height=675&nologo=true&model=flux';
                wp_remote_get( $early_url, array( 'blocking' => false, 'timeout' => 1 ) );
            }
        }

        // 1. Tiến hành tự động đăng bài lên WordPress (nội dung tạm thời)
        $post_data = array(
            'post_title'    => wp_strip_all_tags( $data['title'] ),
            'post_content'  => $data['content'],
            'post_status'   => 'pending', // Đã chuyển sang chờ duyệt theo yêu cầu
            'post_author'   => get_current_user_id(),
            'post_type'     => 'post',
            'post_category' => $categories,
            'tags_input'    => $tags,
        );

        $post_id = wp_insert_post( $post_data );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        // Dedup: nếu proxy retry tạo bài trùng title trong 60s → chỉ xóa bản MỚI HƠN (ID cao hơn)
        $post_title_clean = strtolower( wp_strip_all_tags( $data['title'] ) );
        $dup = get_posts( array(
            'post_type'      => 'post',
            'post_status'    => array( 'pending', 'draft', 'publish' ),
            'posts_per_page' => 1,
            'orderby'        => 'ID',
            'order'          => 'ASC', // lấy bản CŨ nhất (ID thấp nhất) trước
            'post__not_in'   => array( $post_id ),
            'date_query'     => array( array( 'after' => date( 'Y-m-d H:i:s', time() - 60 ) ) ),
            's'              => wp_strip_all_tags( $data['title'] ),
        ) );
        if (
            ! empty( $dup ) &&
            $dup[0]->ID < $post_id && // bản tìm được CŨ hơn ta (lower ID)
            strtolower( $dup[0]->post_title ) === $post_title_clean
        ) {
            wp_delete_post( $post_id, true ); // xóa bản MỚI (chính mình)
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'WP AI Poster - dedup: deleted newer post_id=' . $post_id . ', kept older=' . $dup[0]->ID );
            }
            return array( 'post_id' => $dup[0]->ID, 'title' => $dup[0]->post_title );
        }

        // Lưu metadata bài viết được tạo bởi AI của WP AI Auto Poster
        update_post_meta( $post_id, '_wpaap_generated_by_ai', 'yes' );
        update_post_meta( $post_id, '_wpaap_model_used', $ai_model );

        // Lưu SEO fields từ Quy tắc Bắt Buộc
        $seo_title            = isset( $data['seo_title'] )            ? sanitize_text_field( $data['seo_title'] )            : '';
        $meta_desc            = isset( $data['meta_description'] )     ? sanitize_text_field( $data['meta_description'] )     : '';
        $focus_kw             = isset( $data['focus_keyword'] )        ? sanitize_text_field( $data['focus_keyword'] )        : '';
        $slug_ai              = isset( $data['slug'] )                 ? sanitize_title( $data['slug'] )                      : '';
        $featured_img_prompt  = isset( $data['featured_image_prompt'] ) ? sanitize_text_field( $data['featured_image_prompt'] ) : '';
        $tags_list            = isset( $data['tags_list'] ) && is_array( $data['tags_list'] ) ? $data['tags_list'] : array();

        if ( $slug_ai ) {
            wp_update_post( array( 'ID' => $post_id, 'post_name' => $slug_ai ) );
        }
        if ( ! empty( $tags_list ) && empty( $tags ) ) {
            wp_set_post_tags( $post_id, $tags_list );
        }

        // Lưu SEO meta cho plugin Yoast SEO / RankMath nếu đang active
        if ( $seo_title ) {
            update_post_meta( $post_id, '_yoast_wpseo_title',       $seo_title );
            update_post_meta( $post_id, 'rank_math_title',           $seo_title );
            update_post_meta( $post_id, '_wpaap_seo_title',          $seo_title );
        }
        if ( $meta_desc ) {
            update_post_meta( $post_id, '_yoast_wpseo_metadesc',    $meta_desc );
            update_post_meta( $post_id, 'rank_math_description',     $meta_desc );
            update_post_meta( $post_id, '_wpaap_meta_description',   $meta_desc );
        }
        if ( $focus_kw ) {
            update_post_meta( $post_id, '_yoast_wpseo_focuskw',     $focus_kw );
            update_post_meta( $post_id, 'rank_math_focus_keyword',   $focus_kw );
            update_post_meta( $post_id, '_wpaap_focus_keyword',      $focus_kw );
        }
        if ( $featured_img_prompt ) {
            update_post_meta( $post_id, '_wpaap_featured_image_prompt', $featured_img_prompt );
        }

        // 2. XỬ LÝ ẢNH TRONG BÀI VIẾT (Tự upload hoặc AI vẽ)
        $post_content = $data['content'];
        $first_sideloaded_id = 0;

        if ( ! empty( $content_image_ids ) ) {
            // Người dùng tự upload ảnh: Thay thế placeholder bằng link ảnh của họ
            $index = 1;
            foreach ( $content_image_ids as $cid ) {
                $url = wp_get_attachment_url( $cid );
                if ( $url ) {
                    // Cố gắng tìm cả USER_IMAGE_PLACEHOLDER và AI_IMAGE_PLACEHOLDER đề phòng AI nhầm lẫn
                    $placeholder_user = 'USER_IMAGE_PLACEHOLDER_' . $index;
                    $placeholder_ai = 'AI_IMAGE_PLACEHOLDER_' . $index;
                    $post_content = str_replace( array($placeholder_user, $placeholder_ai), esc_url( $url ), $post_content );
                }
                $index++;
            }
            // Xóa figure còn chứa placeholder chưa được thay (thiếu ảnh user)
            $post_content = preg_replace(
                '/<figure[^>]*>.*?(?:AI|USER)_IMAGE_PLACEHOLDER_\d+.*?<\/figure>/si',
                '',
                $post_content
            );
            $post_content = preg_replace( '/(?:USER|AI)_IMAGE_PLACEHOLDER_\d+/', '', $post_content );
            wp_update_post( array( 'ID' => $post_id, 'post_content' => $post_content ) );
        } else {
            // AI tìm ảnh: lưu vào meta, xử lý nền (tránh timeout Traefik 60s)
            $image_prompts  = isset( $data['image_prompts'] )  && is_array( $data['image_prompts'] )  ? $data['image_prompts']  : array();
            $image_keywords = isset( $data['image_keywords'] ) && is_array( $data['image_keywords'] ) ? $data['image_keywords'] : array();

            // Fallback: nếu image_prompts vẫn rỗng (AI không dùng [AI_IMAGE]), dùng image_keywords làm prompt
            if ( empty( $image_prompts ) && ! empty( $image_keywords ) ) {
                $image_prompts = $image_keywords;
                wpaap_log( "IMG FALLBACK post_id=$post_id using image_keywords as prompts count=" . count( $image_prompts ) );
            }

            wpaap_log( "IMG QUEUE post_id=$post_id prompts=" . count( $image_prompts ) . ' keywords=' . count( $image_keywords ) );
            $ai_images = wpaap_generate_ai_images( $image_prompts, $image_keywords );

            if ( ! empty( $ai_images ) ) {
                update_post_meta( $post_id, '_wpaap_pending_images', $ai_images );
                update_post_meta( $post_id, '_wpaap_pending_title', wp_strip_all_tags( $data['title'] ) );
                wpaap_log( "IMG META SAVED post_id=$post_id count=" . count( $ai_images ) );
            } else {
                wpaap_log( "IMG SKIP post_id=$post_id - no images generated (prompts empty)" );
            }
            // Lưu content với placeholder; background job sẽ thay bằng ảnh thực
            wp_update_post( array( 'ID' => $post_id, 'post_content' => $post_content ) );
        }

        // 3. 🌟 GÁN ẢNH ĐẠI DIỆN: Ưu tiên ảnh user chọn -> Ảnh bài viết đầu tiên
        if ( $featured_image_id > 0 ) {
            set_post_thumbnail( $post_id, $featured_image_id );
        } elseif ( ! empty( $content_image_ids ) ) {
            set_post_thumbnail( $post_id, $content_image_ids[0] );
        } elseif ( $first_sideloaded_id > 0 ) {
            set_post_thumbnail( $post_id, $first_sideloaded_id );
        }

        return array( 'post_id' => $post_id, 'title' => $data['title'] );

    } catch ( Throwable $e ) {
        // Tự động bắt lỗi runtime để tránh lỗi sập trang web (WSOD)
        return new WP_Error( 'runtime_exception', 'Phát sinh lỗi xử lý hệ thống: ' . $e->getMessage() );
    } finally {
        // Đảm bảo gỡ filter trong mọi trường hợp
        if ( isset( $timeout_filter ) ) {
            remove_filter( 'http_request_args', $timeout_filter, 999 );
        }
        if ( isset( $curl_filter ) ) {
            remove_action( 'http_api_curl', $curl_filter, 999 );
        }
    }
}

// Hàm gọi AI Chat trực tiếp
function wpaap_call_ai_chat( $chat_prompt ) {
    $ai_model = get_option( 'wpaap_default_ai_model', 'gemini-3.5-flash' );
    
    // Tự động luân chuyển giữa các active providers
    $provider_models = [
        'google' => ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash-lite-001', 'gemini-2.5-flash-lite', 'gemini-1.5-flash'],
        'anthropic' => ['claude-3-5-sonnet', 'claude-3-opus', 'claude-3-haiku'],
        'openai' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo']
    ];
    
    $selected_provider = '';
    foreach ( $provider_models as $prov => $mods ) {
        if ( in_array( $ai_model, $mods ) ) {
            $selected_provider = $prov;
            break;
        }
    }

    if ( ! empty( $selected_provider ) ) {
        $prov_connected = wpaap_is_provider_connected( $selected_provider );
        if ( ! $prov_connected ) {
            $provider_name = $selected_provider === 'google' ? 'Google Gemini' : ($selected_provider === 'anthropic' ? 'Anthropic Claude' : 'OpenAI GPT');
            return new WP_Error( 'ai_provider_disconnected', 'Dịch vụ ' . $provider_name . ' hiện chưa được cấu hình API Key hoặc chưa bật kết nối. Vui lòng kiểm tra lại.' );
        }
    }
    
    $models_to_try = [];
    // Chỉ thêm mô hình từ các provider đã được kết nối
    foreach ( $provider_models as $prov => $mods ) {
        $prov_connected = wpaap_is_provider_connected( $prov );
        if ( $prov_connected ) {
            if ( $prov === $selected_provider ) {
                array_unshift( $models_to_try, $ai_model );
                foreach ( $mods as $m ) {
                    if ( $m !== $ai_model ) {
                        $models_to_try[] = $m;
                    }
                }
            } else {
                $models_to_try = array_merge( $models_to_try, $mods );
            }
        }
    }
    
    $models_to_try = array_values( array_unique( $models_to_try ) );
    
    $ai_response = '';
    $last_error_msg = '';
    $successful_model = '';
    $tokens_from_api = 0;

    foreach ($models_to_try as $model) {
        $current_response = '';
        
        // Gọi trực tiếp API
        $direct_result = wpaap_call_ai_api_direct( $model, $chat_prompt );
        if ( ! is_wp_error( $direct_result ) ) {
            $current_response = $direct_result;
        } else {
            $last_error_msg = $direct_result->get_error_message();
        }
        
        if ( !empty($current_response) ) {
            $ai_response = $current_response;
            $successful_model = $model;
            break;
        }
    }

    if ( empty($ai_response) ) {
        return new WP_Error( 'ai_chat_failed', 'Lỗi chat: ' . $last_error_msg );
    }

    // Tính toán và lưu trữ số lượng Tokens tiêu thụ
    $total_tokens = $tokens_from_api;
    if ( $total_tokens <= 0 ) {
        $prompt_words = count( explode( ' ', $chat_prompt ) );
        $response_words = count( explode( ' ', $ai_response ) );
        $total_tokens = round( ($prompt_words + $response_words) * 1.5 );
    }

    $matched_provider = '';
    foreach ( $provider_models as $prov => $mods ) {
        if ( in_array( $successful_model, $mods ) ) {
            $matched_provider = $prov;
            break;
        }
    }
    if ( ! empty( $matched_provider ) ) {
        $used = intval( get_option( "wpaap_tokens_used_{$matched_provider}", 0 ) );
        update_option( "wpaap_tokens_used_{$matched_provider}", $used + $total_tokens );
        if ( function_exists( 'wpaap_log_token_usage' ) ) {
            wpaap_log_token_usage( $matched_provider, $total_tokens );
        }
    }

    return trim($ai_response);
}

function wpaap_log_token_usage( $provider, $tokens ) {
    $logs = get_option( 'wpaap_token_usage_logs', [] );
    if ( ! is_array( $logs ) ) {
        $logs = [];
    }
    
    $now = current_time( 'timestamp' );
    $logs[] = [
        'timestamp' => $now,
        'provider'  => $provider,
        'tokens'    => intval( $tokens )
    ];
    
    // Prune logs older than 30 days
    $cutoff = $now - 30 * DAY_IN_SECONDS;
    $logs = array_filter( $logs, function( $entry ) use ( $cutoff ) {
        return isset( $entry['timestamp'] ) && $entry['timestamp'] >= $cutoff;
    } );
    
    update_option( 'wpaap_token_usage_logs', array_values( $logs ) );
}

function wpaap_get_token_usage_stats( $provider, $period ) {
    $logs = get_option( 'wpaap_token_usage_logs', [] );
    if ( ! is_array( $logs ) ) {
        return 0;
    }
    
    $now = current_time( 'timestamp' );
    $today_start = strtotime( 'today', $now );
    $yesterday_start = $today_start - DAY_IN_SECONDS;
    $seven_days_ago = $now - 7 * DAY_IN_SECONDS;
    $thirty_days_ago = $now - 30 * DAY_IN_SECONDS;
    
    $total = 0;
    foreach ( $logs as $entry ) {
        if ( ! isset( $entry['provider'] ) || $entry['provider'] !== $provider ) {
            continue;
        }
        
        $ts = intval( $entry['timestamp'] );
        $tokens = intval( $entry['tokens'] );
        
        if ( $period === 'today' ) {
            if ( $ts >= $today_start ) {
                $total += $tokens;
            }
        } elseif ( $period === 'yesterday' ) {
            if ( $ts >= $yesterday_start && $ts < $today_start ) {
                $total += $tokens;
            }
        } elseif ( $period === '7_days' ) {
            if ( $ts >= $seven_days_ago ) {
                $total += $tokens;
            }
        } elseif ( $period === '15_days' ) {
            $fifteen_days_ago = $now - 15 * DAY_IN_SECONDS;
            if ( $ts >= $fifteen_days_ago ) {
                $total += $tokens;
            }
        } elseif ( $period === '30_days' ) {
            if ( $ts >= $thirty_days_ago ) {
                $total += $tokens;
            }
        }
    }
    
    return $total;
}

// ─── STEP-BY-STEP AI GENERATOR FUNCTIONS ───────────────────────────────────

function wpaap_step_get_length_config( $payload ) {
    $len = $payload['article_length'] ?? 'medium';
    $map = [
        'short'  => [ 'h2_count' => '3-4', 'total_words' => '700-900',   'section_words' => '100-150', 'intro_words' => '80-120',  'label' => 'Ngắn (~800 từ)'   ],
        'medium' => [ 'h2_count' => '5-6', 'total_words' => '1200-1500', 'section_words' => '150-220', 'intro_words' => '130-180', 'label' => 'Chuẩn (~1300 từ)' ],
        'long'   => [ 'h2_count' => '6-8', 'total_words' => '1800-2200', 'section_words' => '220-320', 'intro_words' => '180-250', 'label' => 'Dài (~2000 từ)'   ],
    ];
    return $map[ $len ] ?? $map['medium'];
}

function wpaap_strip_html_codeblock( $text ) {
    $text = trim( $text );
    $text = preg_replace( '/^```(?:html|HTML)?\s*/m', '', $text );
    $text = preg_replace( '/```\s*$/m', '', $text );
    return trim( $text );
}

function wpaap_step_generate_outline( $job, &$payload ) {
    $lc     = wpaap_step_get_length_config( $payload );
    $prompt = "Bạn là chuyên gia viết nội dung SEO chuẩn E-E-A-T. Hãy lập dàn bài chi tiết gồm {$lc['h2_count']} tiêu đề H2 (mỗi tiêu đề H2 phải có ít nhất 1-2 tiêu đề H3 bên dưới) cho bài viết có chủ đề: '" . $job->prompt . "'. Độ dài bài mục tiêu: {$lc['total_words']} từ.
Trả về kết quả ở định dạng JSON duy nhất dạng mảng, ví dụ:
[
  {
    \"h2\": \"Tiêu đề H2 thứ nhất\",
    \"h3\": [\"Tiêu đề H3 1.1\", \"Tiêu đề H3 1.2\"]
  }
]";

    $response = wpaap_call_ai_api_direct( $job->ai_model, $prompt, [ 'response_json' => true ] );
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    $response_str = wpaap_clean_json_response( $response );
    $outline = json_decode( $response_str, true );
    if ( ! is_array( $outline ) || empty( $outline ) ) {
        return new WP_Error( 'outline_invalid', 'Dàn bài AI tạo ra không hợp lệ: ' . substr($response_str, 0, 150) );
    }
    
    $payload['outline'] = $outline;
    return true;
}

function wpaap_step_generate_intro( $job, &$payload ) {
    $lc          = wpaap_step_get_length_config( $payload );
    $outline_str = wp_json_encode( $payload['outline'] );
    $prompt = "Bạn là chuyên gia viết nội dung SEO chuẩn E-E-A-T.
Hãy viết phần mở đầu (Introduction) cho bài viết có chủ đề: '" . $job->prompt . "'.
Dàn bài bài viết: $outline_str.

Yêu cầu bắt buộc:
1. Bắt đầu bằng đoạn tóm tắt từ 50-80 từ tối ưu Featured Snippet, nằm giữa shortcode [TOP_SUMMARY]Đoạn tóm tắt...[/TOP_SUMMARY].
2. Viết 2-3 đoạn văn dẫn nhập lôi cuốn, có độ dài khoảng {$lc['intro_words']} từ.

Trả về chuỗi văn bản HTML thô chứa phần mở đầu này.";

    $response = wpaap_call_ai_api_direct( $job->ai_model, $prompt );
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    $payload['intro_content'] = wpaap_strip_html_codeblock( $response );
    return true;
}

function wpaap_step_generate_section( $job, &$payload, $h2_index ) {
    $outline = $payload['outline'];
    if ( ! isset( $outline[$h2_index] ) ) {
        return new WP_Error( 'section_out_of_bounds', "H2 index $h2_index out of bounds" );
    }

    $lc       = wpaap_step_get_length_config( $payload );
    $section  = $outline[$h2_index];
    $h2_title = $section['h2'] ?? '';
    $h3s      = $section['h3'] ?? [];
    $h3s_str  = implode( ', ', $h3s );

    $outline_str = '';
    foreach ( $outline as $idx => $sec ) {
        $active_marker = ($idx === $h2_index) ? ' -> (Đang viết phần này)' : '';
        $outline_str .= "- H2: " . ($sec['h2'] ?? '') . $active_marker . "\n";
    }

    $prompt = "Bạn là chuyên gia viết nội dung SEO chuẩn E-E-A-T.
Hãy viết nội dung chi tiết cho phần H2: '$h2_title' (gồm các phần con H3: $h3s_str) của bài viết chủ đề: '" . $job->prompt . "'.

Dàn bài tổng thể:
$outline_str

Yêu cầu chi tiết:
1. Viết bài bằng tiếng Việt, chuyên sâu, cung cấp thông tin thực tế, dễ hiểu. Độ dài phần này: {$lc['section_words']} từ.
2. Định dạng bài viết bằng các thẻ HTML cơ bản (thẻ <h2> cho tiêu đề chính, <h3> cho tiêu đề con, <p>, <strong>, <ul>, <li>).
3. Nếu chủ đề phù hợp, hãy chèn tối đa duy nhất 1 hộp thông tin trong phần này bằng cách dùng một trong các shortcode sau:
   - [INFO_BOX]Tiêu đề:\\n...\\n\\nNội dung:\\n...[/INFO_BOX]
   - [TIP_BOX]Tiêu đề:\\nMẹo\\n\\nNội dung:\\n...[/TIP_BOX]
   - [WARNING_BOX]Tiêu đề:\\nCảnh báo\\n\\nNội dung:\\n...[/WARNING_BOX]
   - [EXPERT_BOX]Tiêu đề:\\nKinh nghiệm\\n\\nNội dung:\\n...[/EXPERT_BOX]
4. Tự động đề xuất các vị trí chèn ảnh minh họa bằng cách đặt shortcode [AI_IMAGE] ở nơi phù hợp trong bài. Shortcode có định dạng:
[AI_IMAGE]
Title: Tiêu đề ảnh tiếng Việt
Alt: Mô tả ảnh tiếng Việt chứa từ khóa
Caption: Chú thích ảnh tiếng Việt
Prompt: English image generation prompt (subject + context + style, 15-30 words)
[/AI_IMAGE]
Mật độ: Chỉ đặt tối đa 1 [AI_IMAGE] trong phần này. Nếu đã có ảnh người dùng tự upload, chỉ chèn [AI_IMAGE][/AI_IMAGE] trống.

Trả về chuỗi thô nội dung HTML viết cho phần này (bao gồm tiêu đề <h2>).";

    $response = wpaap_call_ai_api_direct( $job->ai_model, $prompt );
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    if ( ! isset( $payload['sections_content'] ) || ! is_array( $payload['sections_content'] ) ) {
        $payload['sections_content'] = [];
    }
    $payload['sections_content'][$h2_index] = wpaap_strip_html_codeblock( $response );
    return true;
}

function wpaap_step_generate_conclusion( $job, &$payload ) {
    $prompt = "Bạn là chuyên gia viết nội dung SEO chuẩn E-E-A-T.
Hãy viết phần kết luận và các thành phần bổ sung cho bài viết chủ đề: '" . $job->prompt . "'.

Yêu cầu chi tiết:
1. Viết 1 phần kết luận gồm tiêu đề H2 (ví dụ: Kết luận, Tổng kết) kèm theo 1-2 đoạn văn (khoảng 100-150 từ).
2. Tạo 1 phần Checklist hữu ích bằng cách sử dụng shortcode [CHECKLIST] (Tối thiểu 4 mục, tối đa 8 mục, mỗi mục 5-20 từ. Bắt buộc dòng đầu tiên là Title: [Tiêu đề checklist]). Định dạng:
[CHECKLIST]
Title: Tiêu đề checklist
✓ Mục 1
✓ Mục 2
[/CHECKLIST]
3. Tạo phần Câu hỏi thường gặp FAQ gồm ít nhất 5 cặp Q/A sử dụng shortcode [FAQ]:
[FAQ]
Q: Câu hỏi 1?
A: Câu trả lời 1.
Q: Câu hỏi 2?
A: Câu trả lời 2.
[/FAQ]
4. Chèn 1 lời kêu gọi hành động CTA thích hợp ở cuối bài viết bằng cách dùng shortcode [CTA]nội dung CTA...[/CTA].

Trả về chuỗi thô văn bản chứa các thành phần trên.";

    $response = wpaap_call_ai_api_direct( $job->ai_model, $prompt );
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    $payload['conclusion_content'] = wpaap_strip_html_codeblock( $response );
    return true;
}

function wpaap_step_generate_metadata( $job, &$payload ) {
    $topic = addslashes( $job->prompt );
    $prompt = "Bạn là chuyên gia SEO. Tạo metadata cho bài viết chủ đề: \"{$topic}\".
Trả về JSON duy nhất, không có text nào khác:
{\"seo_title\":\"50-60 ký tự tiêu đề SEO\",\"meta_description\":\"140-160 ký tự mô tả\",\"slug\":\"url-slug-khong-dau\",\"tags\":[\"tag1\",\"tag2\",\"tag3\"],\"featured_image_prompt\":\"English image description\"}";

    $response = wpaap_call_ai_api_direct( $job->ai_model, $prompt, [ 'response_json' => true ] );
    if ( is_wp_error( $response ) ) {
        return $response;
    }
    
    $response_str = wpaap_clean_json_response( $response );
    // Strip ký tự control ẩn (giống repair logic ở article parser)
    $response_str = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $response_str );
    $meta = json_decode( $response_str, true );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        // Repair: escape raw newline/tab bên trong string values
        $fixed = '';
        $in_s  = false;
        $skip  = false;
        $len   = strlen( $response_str );
        for ( $i = 0; $i < $len; $i++ ) {
            $c = $response_str[ $i ];
            if ( $skip ) { $fixed .= $c; $skip = false; continue; }
            if ( $c === '\\' ) { $fixed .= $c; $skip = true; continue; }
            if ( $c === '"' ) { $in_s = ! $in_s; $fixed .= $c; continue; }
            if ( $in_s ) {
                if ( $c === "\n" ) { $fixed .= "\\n"; continue; }
                if ( $c === "\r" ) { $fixed .= "\\r"; continue; }
                if ( $c === "\t" ) { $fixed .= "\\t"; continue; }
            }
            $fixed .= $c;
        }
        $meta = json_decode( $fixed, true );
    }
    if ( ! is_array( $meta ) || ! isset( $meta['seo_title'] ) ) {
        // Retry 1 lần nếu JSON vẫn lỗi (model đôi khi trả về text thay vì JSON)
        $retry_resp = wpaap_call_ai_api_direct( $job->ai_model, $prompt . "\n\nQUAN TRỌNG: Chỉ trả về JSON object thuần, không có text nào khác.", [ 'response_json' => true ] );
        if ( ! is_wp_error( $retry_resp ) ) {
            $retry_str = wpaap_clean_json_response( $retry_resp );
            $meta      = json_decode( $retry_str, true );
        }
    }
    if ( ! is_array( $meta ) || ! isset( $meta['seo_title'] ) ) {
        return new WP_Error( 'metadata_invalid', 'Dữ liệu metadata do AI sinh ra không hợp lệ: ' . substr( $response_str, 0, 150 ) );
    }
    
    $payload['seo_title'] = $meta['seo_title'] ?? '';
    $payload['meta_description'] = $meta['meta_description'] ?? '';
    $payload['slug'] = $meta['slug'] ?? '';
    $payload['tags'] = $meta['tags'] ?? [];
    $payload['featured_image_prompt'] = $meta['featured_image_prompt'] ?? '';
    
    return true;
}

function wpaap_step_publish_post( $job, &$payload ) {
    $outline = $payload['outline'];
    $title = $payload['seo_title'] ?: $job->prompt;
    $payload['title'] = $title;
    
    // Assemble content
    $content = '';
    $content .= "<h1>" . esc_html( $title ) . "</h1>\n";
    
    // Prepend automatically generated TOC and Intro
    $content .= wpaap_build_toc_shortcode( $outline );
    $content .= $payload['intro_content'] . "\n";
    
    $sections = $payload['sections_content'] ?? [];
    ksort( $sections );
    foreach ( $sections as $sec_content ) {
        $content .= $sec_content . "\n";
    }
    
    $content .= $payload['conclusion_content'] . "\n";
    
    $payload['content'] = $content;
    
    $data = [
        'title' => $title,
        'content' => $content,
        'seo_title' => $payload['seo_title'],
        'meta_description' => $payload['meta_description'],
        'slug' => $payload['slug'],
        'tags' => $payload['tags'],
        'featured_image_prompt' => $payload['featured_image_prompt'],
        'image_prompts' => [],
        'image_keywords' => []
    ];
    
    if ( function_exists( 'wpaap_render_article_shortcodes' ) ) {
        wpaap_render_article_shortcodes( $data );
    }
    
    $payload['image_prompts'] = $data['image_prompts'] ?? [];
    
    $post_data = array(
        'post_title'    => wp_strip_all_tags( $title ),
        'post_content'  => $data['content'],
        'post_status'   => 'pending',
        'post_author'   => $job->user_id,
        'post_type'     => 'post',
        'post_category' => ! empty( $job->categories ) ? array_map( 'intval', explode( ',', $job->categories ) ) : [],
        'tags_input'    => $job->tags,
    );
    
    $post_id = wp_insert_post( $post_data );
    if ( is_wp_error( $post_id ) ) {
        return $post_id;
    }
    
    $payload['post_id'] = $post_id;
    
    update_post_meta( $post_id, '_wpaap_generated_by_ai', 'yes' );
    update_post_meta( $post_id, '_wpaap_model_used', $job->ai_model );
    
    if ( $payload['seo_title'] ) {
        update_post_meta( $post_id, '_yoast_wpseo_title',       $payload['seo_title'] );
        update_post_meta( $post_id, 'rank_math_title',           $payload['seo_title'] );
        update_post_meta( $post_id, '_wpaap_seo_title',          $payload['seo_title'] );
    }
    if ( $payload['meta_description'] ) {
        update_post_meta( $post_id, '_yoast_wpseo_metadesc',    $payload['meta_description'] );
        update_post_meta( $post_id, 'rank_math_description',     $payload['meta_description'] );
        update_post_meta( $post_id, '_wpaap_meta_description',   $payload['meta_description'] );
    }
    if ( $payload['slug'] ) {
        wp_update_post( array( 'ID' => $post_id, 'post_name' => $payload['slug'] ) );
    }
    if ( ! empty( $payload['tags'] ) && empty( $job->tags ) ) {
        wp_set_post_tags( $post_id, $payload['tags'] );
    }
    
    if ( $payload['featured_image_prompt'] ) {
        update_post_meta( $post_id, '_wpaap_featured_image_prompt', $payload['featured_image_prompt'] );
    }
    
    return true;
}

function wpaap_step_process_images( $job, &$payload ) {
    $post_id = $payload['post_id'];
    $content_image_ids = ! empty( $job->content_image_ids ) ? array_filter( array_map( 'intval', explode( ',', $job->content_image_ids ) ) ) : [];
    $featured_image_id = intval( $job->image_id );
    
    $post = get_post( $post_id );
    if ( ! $post ) {
        return new WP_Error( 'post_not_found', 'Không tìm thấy bài viết để xử lý ảnh' );
    }
    
    $post_content = $post->post_content;
    
    if ( ! empty( $content_image_ids ) ) {
        $index = 1;
        foreach ( $content_image_ids as $cid ) {
            $url = wp_get_attachment_url( $cid );
            if ( $url ) {
                $placeholder_user = 'USER_IMAGE_PLACEHOLDER_' . $index;
                $placeholder_ai = 'AI_IMAGE_PLACEHOLDER_' . $index;
                $post_content = str_replace( array($placeholder_user, $placeholder_ai), esc_url( $url ), $post_content );
            }
            $index++;
        }
        $post_content = preg_replace(
            '/<figure[^>]*>.*?(?:AI|USER)_IMAGE_PLACEHOLDER_\d+.*?<\/figure>/si',
            '',
            $post_content
        );
        $post_content = preg_replace( '/(?:USER|AI)_IMAGE_PLACEHOLDER_\d+/', '', $post_content );
        wp_update_post( array( 'ID' => $post_id, 'post_content' => $post_content ) );
        
        if ( $featured_image_id > 0 ) {
            set_post_thumbnail( $post_id, $featured_image_id );
        } else {
            set_post_thumbnail( $post_id, $content_image_ids[0] );
        }
    } else {
        $image_prompts = $payload['image_prompts'] ?? [];
        $image_keywords = [];
        
        if ( ! empty( $image_prompts ) ) {
            $ai_images = wpaap_generate_ai_images( $image_prompts, $image_keywords );
            if ( ! empty( $ai_images ) ) {
                update_post_meta( $post_id, '_wpaap_pending_images', $ai_images );
                update_post_meta( $post_id, '_wpaap_pending_title', get_the_title( $post_id ) );
                
                wpaap_do_process_pending_images( $post_id );
            }
        }
        
        if ( $featured_image_id > 0 ) {
            set_post_thumbnail( $post_id, $featured_image_id );
        }
    }
    
    return true;
}

function wpaap_build_toc_shortcode( $outline ) {
    $toc = "[TOC]\n";
    foreach ( $outline as $sec ) {
        $h2 = $sec['h2'] ?? '';
        if ( ! $h2 ) continue;
        $toc .= "$h2\n";
        $h3s = $sec['h3'] ?? [];
        if ( is_array( $h3s ) ) {
            foreach ( $h3s as $h3 ) {
                $toc .= "  $h3\n";
            }
        }
    }
    $toc .= "[/TOC]\n";
    return $toc;
}

function wpaap_clean_json_response( $res ) {
    $res_str = is_string( $res ) ? trim( $res ) : wp_json_encode( $res );
    $res_str = preg_replace('/^```(?:json)?\s*/mi', '', $res_str);
    $res_str = preg_replace('/```\s*$/mi', '', $res_str);
    $res_str = trim( $res_str );
    // Kiểm tra array [...] trước object {...} — outline trả về array nên phải ưu tiên
    $start_arr = strpos( $res_str, '[' );
    $end_arr   = strrpos( $res_str, ']' );
    $start_obj = strpos( $res_str, '{' );
    $end_obj   = strrpos( $res_str, '}' );
    $has_arr = $start_arr !== false && $end_arr !== false && $end_arr > $start_arr;
    $has_obj = $start_obj !== false && $end_obj !== false && $end_obj > $start_obj;
    if ( $has_arr && ( ! $has_obj || $start_arr < $start_obj ) ) {
        return substr( $res_str, $start_arr, $end_arr - $start_arr + 1 );
    }
    if ( $has_obj ) {
        return substr( $res_str, $start_obj, $end_obj - $start_obj + 1 );
    }
    return $res_str;
}
