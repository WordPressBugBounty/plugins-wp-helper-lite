<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Converts AI-generated shortcodes in article content to styled HTML.
 * Called from wpaap-ai-generator.php before wp_insert_post.
 *
 * Shortcodes handled:
 *   [AI_IMAGE]...[/AI_IMAGE]         → <figure> + AI_IMAGE_PLACEHOLDER_N (prompts extracted to $data['image_prompts'])
 *   [TOP_SUMMARY]...[/TOP_SUMMARY]   → featured-snippet div
 *   [INFO_BOX]...[/INFO_BOX]         → callout box (blue)
 *   [EXPERT_BOX]...[/EXPERT_BOX]     → callout box (purple)
 *   [TIP_BOX]...[/TIP_BOX]           → callout box (amber)
 *   [WARNING_BOX]...[/WARNING_BOX]   → callout box (red)
 *   [CHECKLIST]...[/CHECKLIST]       → title heading + styled <ul class="wpaap-checklist">
 *   [FAQ]...[/FAQ]                   → accordion FAQ section with heading
 *   [INTERNAL_LINK]...[/INTERNAL_LINK] → internal link suggestion
 *   [CTA]...[/CTA]                   → CTA box
 */

if ( ! function_exists( 'wpaap_render_article_shortcodes' ) ) {

    /**
     * Main entry point. Mutates $data['content'] and $data['image_prompts'] in place.
     *
     * @param array $data Reference to the AI response data array.
     */
    function wpaap_render_article_shortcodes( array &$data ) {
        $content           = isset( $data['content'] ) ? $data['content'] : '';
        $placeholder_index = 1;
        $extracted_prompts = array();

        // ── Step 0: Inject id attributes into h2/h3 that lack them ──────────
        // Must run BEFORE TOC so the TOC can produce anchor links.
        $id_counter = array();
        $content = preg_replace_callback(
            '/<(h[23])([^>]*)>(.*?)<\/(h[23])>/si',
            function ( $m ) use ( &$id_counter ) {
                $tag   = $m[1];
                $attrs = $m[2];
                $inner = $m[3];
                if ( preg_match( '/\bid=["\']/', $attrs ) ) {
                    return $m[0]; // already has id
                }
                $slug = sanitize_title( wp_strip_all_tags( $inner ) );
                if ( empty( $slug ) ) $slug = 'heading';
                if ( ! isset( $id_counter[ $slug ] ) ) {
                    $id_counter[ $slug ] = 0;
                    $final = $slug;
                } else {
                    $id_counter[ $slug ]++;
                    $final = $slug . '-' . $id_counter[ $slug ];
                }
                return '<' . $tag . ' id="' . esc_attr( $final ) . '"' . $attrs . '>' . $inner . '</' . $tag . '>';
            },
            $content
        );

        // Helper: build linked TOC from h2/h3 already in content (reads IDs injected above)
        $build_toc_from_headings = function ( $source_content ) {
            preg_match_all( '/<h([23])([^>]*)>(.*?)<\/h[23]>/si', $source_content, $hm, PREG_SET_ORDER );
            if ( empty( $hm ) ) return '';
            $html    = '<ol class="wp-toc-list">';
            $in_sub  = false;
            $has_any = false;
            foreach ( $hm as $h ) {
                $level = $h[1];
                $attrs = $h[2];
                $inner = $h[3];
                $text  = wp_strip_all_tags( $inner );
                $id    = '';
                if ( preg_match( '/\bid="([^"]*)"/i', $attrs, $im ) ) {
                    $id = $im[1];
                }
                $linked  = $id
                    ? '<a href="#' . esc_attr( $id ) . '">' . esc_html( $text ) . '</a>'
                    : esc_html( $text );
                $has_any = true;
                if ( $level === '3' ) {
                    if ( ! $in_sub ) { $html .= '<ol>'; $in_sub = true; }
                    $html .= '<li>' . $linked . '</li>';
                } else {
                    if ( $in_sub ) { $html .= '</ol></li>'; $in_sub = false; }
                    $html .= '<li>' . $linked;
                }
            }
            if ( $in_sub )      $html .= '</ol></li>';
            elseif ( $has_any ) $html .= '</li>';
            $html .= '</ol>';
            return $has_any ? $html : '';
        };

        $toc_wrap = function ( $list_html ) {
            return '<div class="wp-toc-box">'
                 . '<div class="wp-toc-header">'
                 . '<span>📋 Mục lục</span>'
                 . '<button class="wp-toc-toggle">Thu gọn ▲</button>'
                 . '</div>'
                 . $list_html . '</div>';
        };

        // 0a. [TOC]...[/TOC] → always build from headings (for reliable anchor links)
        $content = preg_replace_callback(
            '/\[TOC\](.*?)\[\/TOC\]/si',
            function ( $m ) use ( $build_toc_from_headings, $toc_wrap, &$content ) {
                $list_html = $build_toc_from_headings( $content );
                return $list_html ? $toc_wrap( $list_html ) : '';
            },
            $content
        );

        // 0b. Standalone [TOC] (no closing tag) → same
        if ( stripos( $content, '[TOC]' ) !== false ) {
            $content = preg_replace_callback(
                '/\[TOC\]/i',
                function ( $m ) use ( $build_toc_from_headings, $toc_wrap, &$content ) {
                    $list_html = $build_toc_from_headings( $content );
                    return $list_html ? $toc_wrap( $list_html ) : '';
                },
                $content
            );
        }

        // 1. [AI_IMAGE]...[/AI_IMAGE] → <figure> with numbered placeholder
        $content = preg_replace_callback(
            '/\[AI_IMAGE\](.*?)\[\/AI_IMAGE\]/si',
            function ( $m ) use ( &$placeholder_index, &$extracted_prompts ) {
                $block   = $m[1];
                $title   = wpaap_sc_field( $block, 'Title' );
                $alt     = wpaap_sc_field( $block, 'Alt' );
                $caption = wpaap_sc_field( $block, 'Caption' );
                $prompt  = wpaap_sc_field( $block, 'Prompt' );
                // Fallback: dùng Alt hoặc Title nếu AI không điền Prompt
                $extracted_prompts[] = ! empty( $prompt ) ? $prompt : ( $alt ?: $title );
                $ph       = 'AI_IMAGE_PLACEHOLDER_' . $placeholder_index++;
                $alt_attr = esc_attr( $alt ?: $title );
                $fig      = '<figure class="wp-block-image aligncenter size-full">'
                          . '<img src="' . $ph . '" alt="' . $alt_attr . '" loading="lazy" decoding="async" />';
                if ( ! empty( $caption ) ) {
                    $fig .= '<figcaption>' . esc_html( $caption ) . '</figcaption>';
                }
                $fig .= '</figure>';
                return $fig;
            },
            $content
        );

        // image_prompts từ [AI_IMAGE] blocks (luôn có vì đã fallback về Alt/Title)
        if ( ! empty( $extracted_prompts ) ) {
            $data['image_prompts'] = $extracted_prompts;
        }

        // 2. [TOP_SUMMARY]...[/TOP_SUMMARY] → featured-snippet div
        $content = preg_replace_callback(
            '/\[TOP_SUMMARY\](.*?)\[\/TOP_SUMMARY\]/si',
            function ( $m ) {
                $text = trim( $m[1] );
                return '<div class="featured-snippet"><p>' . wp_kses_post( $text ) . '</p></div>';
            },
            $content
        );

        // 3. Callout boxes: INFO_BOX, EXPERT_BOX, TIP_BOX, WARNING_BOX
        $callout_map = array(
            'INFO_BOX'    => 'info',
            'EXPERT_BOX'  => 'expert',
            'TIP_BOX'     => 'tip',
            'WARNING_BOX' => 'warning',
        );
        foreach ( $callout_map as $tag => $type ) {
            $content = preg_replace_callback(
                '/\[' . $tag . '\](.*?)\[\/' . $tag . '\]/si',
                function ( $m ) use ( $type ) {
                    return wpaap_render_callout_box( $m[1], $type );
                },
                $content
            );
        }

        // 4. [CHECKLIST]...[/CHECKLIST] → optional title heading + styled <ul>
        $content = preg_replace_callback(
            '/\[CHECKLIST\](.*?)\[\/CHECKLIST\]/si',
            function ( $m ) {
                $raw   = trim( $m[1] );
                $lines = preg_split( '/\r?\n/', $raw );

                // Extract optional "Title: ..." line (first occurrence)
                $title      = '';
                $item_lines = array();
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( empty( $line ) ) continue;
                    if ( $title === '' && preg_match( '/^Title\s*:\s*(.+)$/iu', $line, $tm ) ) {
                        $title = trim( $tm[1] );
                    } else {
                        $item_lines[] = $line;
                    }
                }

                $items = '';
                foreach ( $item_lines as $line ) {
                    $text  = trim( preg_replace( '/^[✓✔☑✅\-\*•]\s*/u', '', $line ) );
                    if ( empty( $text ) ) continue;
                    // Skip near-empty / symbol-only lines (< 3 words)
                    $words = count( preg_split( '/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY ) );
                    if ( $words < 3 ) continue;
                    $items .= '<li>' . esc_html( $text ) . '</li>';
                }
                if ( ! $items ) return '';

                $html = '';
                if ( $title ) {
                    $html .= '<h4 class="wpaap-checklist-title">' . esc_html( $title ) . '</h4>';
                }
                $html .= '<ul class="wpaap-checklist">' . $items . '</ul>';
                return $html;
            },
            $content
        );

        // 5. [FAQ]...[/FAQ] → accordion FAQ section with "Câu hỏi thường gặp" heading
        // Permissive: match với hoặc không có [/FAQ] (AI đôi khi bỏ closing tag).
        // Nếu có [/FAQ] → lấy đến đó. Nếu không → lấy đến [CHECKLIST]/[CTA]/[/CTA] hoặc hết chuỗi.
        $faq_render_cb = function ( $m ) {
                $block = trim( $m[1] );
                preg_match_all( '/Q\s*:\s*(.+?)\s*A\s*:\s*([\s\S]+?)(?=Q\s*:|$)/i', $block, $pairs, PREG_SET_ORDER );
                if ( empty( $pairs ) ) return isset( $m[0] ) ? $m[0] : '';

                static $faq_save_counter = 0;
                $html  = '<div class="wpaap-faq-section">'
                       . '<h3 class="wpaap-faq-heading">Câu hỏi thường gặp</h3>';
                $first = true;
                foreach ( $pairs as $pair ) {
                    $q = trim( $pair[1] );
                    $a = trim( $pair[2] );
                    $faq_save_counter++;
                    $uid     = 'wpaap-faq-' . $faq_save_counter;
                    $checked = $first ? ' checked' : '';
                    $html .= '<div class="wpaap-faq-item">'
                           . '<input type="checkbox" id="' . esc_attr( $uid ) . '" class="wpaap-faq-toggle"' . $checked . '>'
                           . '<label class="wpaap-faq-q" for="' . esc_attr( $uid ) . '">' . esc_html( $q )
                           . '<span class="wpaap-faq-icon" aria-hidden="true"></span></label>'
                           . '<div class="wpaap-faq-a">' . wp_kses_post( $a ) . '</div>'
                           . '</div>';
                    $first = false;
                }
                $html .= '</div>';
                return $html;
        };
        // Thử match với [/FAQ] trước (strict)
        $content = preg_replace_callback( '/\[FAQ\](.*?)\[\/FAQ\]/si', $faq_render_cb, $content );
        // Fallback: nếu vẫn còn [FAQ] mà không có [/FAQ], lấy đến shortcode kế tiếp hoặc hết chuỗi
        if ( strpos( $content, '[FAQ]' ) !== false ) {
            $content = preg_replace_callback(
                '/\[FAQ\]([\s\S]+?)(?=\[CHECKLIST\]|\[CTA\]|\[\/CTA\]|\[INFO_BOX\]|\[EXPERT_BOX\]|$)/i',
                $faq_render_cb,
                $content
            );
        }

        // 6. [INTERNAL_LINK]...[/INTERNAL_LINK] → suggestion paragraph
        $content = preg_replace_callback(
            '/\[INTERNAL_LINK\](.*?)\[\/INTERNAL_LINK\]/si',
            function ( $m ) {
                $title = trim( wp_strip_all_tags( $m[1] ) );
                return '<p class="wpaap-internal-link">📎 <strong>Đọc thêm:</strong> ' . esc_html( $title ) . '</p>';
            },
            $content
        );

        // 7. [CTA]...[/CTA] → CTA box
        $content = preg_replace_callback(
            '/\[CTA\](.*?)\[\/CTA\]/si',
            function ( $m ) {
                return '<div class="wpaap-cta">' . wp_kses_post( trim( $m[1] ) ) . '</div>';
            },
            $content
        );

        $data['content'] = $content;
    }

    /**
     * Extract a single-line field value: "FieldName: value"
     */
    function wpaap_sc_field( $block, $field ) {
        if ( preg_match( '/^' . preg_quote( $field, '/' ) . '\s*:\s*(.+)$/mi', $block, $m ) ) {
            return trim( $m[1] );
        }
        return '';
    }

    /**
     * Render a callout box (info / expert / tip / warning).
     */
    function wpaap_render_callout_box( $block, $type ) {
        static $meta = array(
            'info'    => array( 'icon' => '💡', 'default' => 'Lưu ý quan trọng' ),
            'expert'  => array( 'icon' => '🎯', 'default' => 'Kinh nghiệm thực tế' ),
            'tip'     => array( 'icon' => '⚡', 'default' => 'Mẹo tối ưu' ),
            'warning' => array( 'icon' => '⚠️', 'default' => 'Cảnh báo' ),
        );
        $icon    = isset( $meta[ $type ] ) ? $meta[ $type ]['icon']    : '💡';
        $default = isset( $meta[ $type ] ) ? $meta[ $type ]['default'] : 'Lưu ý';

        // Extract title (same line or next line after "Tiêu đề:")
        $title = '';
        if ( preg_match( '/Tiêu đề\s*:\s*(.+)/ui', $block, $tm ) ) {
            $title = trim( $tm[1] );
        }
        if ( empty( $title ) && preg_match( '/Tiêu đề\s*:\s*\r?\n\s*(.+)/ui', $block, $tm ) ) {
            $title = trim( $tm[1] );
        }
        if ( empty( $title ) ) {
            $title = $default;
        }

        // Extract body: everything after "Nội dung:" label (strip title line first)
        $body = preg_replace( '/Tiêu đề\s*:.*?(\r?\n|$)/ui', '', $block );
        if ( preg_match( '/Nội dung\s*:\s*([\s\S]*)/ui', $body, $bm ) ) {
            $body = $bm[1];
        }
        $body = trim( $body );

        return '<div class="wpaap-callout wpaap-callout--' . esc_attr( $type ) . '">'
             . '<div class="wpaap-callout__title">' . $icon . ' ' . esc_html( $title ) . '</div>'
             . '<div class="wpaap-callout__body">' . wp_kses_post( $body ) . '</div>'
             . '</div>';
    }
}

// ── Migrate old FAQ HTML (div or details) → checkbox accordion at display time ──
if ( ! function_exists( 'wpaap_migrate_old_faq_html' ) ) {
    function wpaap_migrate_old_faq_html( $content ) {
        if ( strpos( $content, 'wpaap-faq-item' ) === false ) return $content;
        // Skip if already checkbox format
        if ( strpos( $content, 'wpaap-faq-toggle' ) !== false ) return $content;
        static $mig_counter = 0;
        // 1. Migrate old <div class="wpaap-faq-item"> format
        $content = preg_replace_callback(
            '/<div class="wpaap-faq-item(?:\s+wpaap-faq-open)?">\s*<div class="wpaap-faq-q">([^<]*(?:<(?!div|\/div)[^<]*)*)<span[^>]*>[^<]*<\/span><\/div>\s*<div class="wpaap-faq-a">([\s\S]*?)<\/div>\s*<\/div>/i',
            function ( $m ) use ( &$mig_counter ) {
                $mig_counter++;
                $uid     = 'wpaap-faq-' . $mig_counter;
                $checked = strpos( $m[0], 'wpaap-faq-open' ) !== false ? ' checked' : '';
                return '<div class="wpaap-faq-item">'
                     . '<input type="checkbox" id="' . esc_attr( $uid ) . '" class="wpaap-faq-toggle"' . $checked . '>'
                     . '<label class="wpaap-faq-q" for="' . esc_attr( $uid ) . '">' . trim( $m[1] )
                     . '<span class="wpaap-faq-icon" aria-hidden="true"></span></label>'
                     . '<div class="wpaap-faq-a">' . trim( $m[2] ) . '</div>'
                     . '</div>';
            },
            $content
        );
        // 2. Migrate <details class="wpaap-faq-item"> format (from previous deploy)
        $content = preg_replace_callback(
            '/<details class="wpaap-faq-item"([^>]*)>\s*<summary class="wpaap-faq-q">([^<]*(?:<(?!\/summary)[^<]*)*)<span[^>]*>[^<]*<\/span><\/summary>\s*<div class="wpaap-faq-a">([\s\S]*?)<\/div>\s*<\/details>/i',
            function ( $m ) use ( &$mig_counter ) {
                $mig_counter++;
                $uid     = 'wpaap-faq-' . $mig_counter;
                $checked = strpos( $m[1], 'open' ) !== false ? ' checked' : '';
                return '<div class="wpaap-faq-item">'
                     . '<input type="checkbox" id="' . esc_attr( $uid ) . '" class="wpaap-faq-toggle"' . $checked . '>'
                     . '<label class="wpaap-faq-q" for="' . esc_attr( $uid ) . '">' . trim( $m[2] )
                     . '<span class="wpaap-faq-icon" aria-hidden="true"></span></label>'
                     . '<div class="wpaap-faq-a">' . trim( $m[3] ) . '</div>'
                     . '</div>';
            },
            $content
        );
        return $content;
    }
    add_filter( 'the_content', 'wpaap_migrate_old_faq_html', 7 );
}

// ── Strip markdown code fences từ nội dung đã lưu (AI đôi khi chèn ```html) ──
// Priority 6: trước migrate (7) và wpautop (10)
if ( ! function_exists( 'wpaap_strip_content_code_fences' ) ) {
    function wpaap_strip_content_code_fences( $content ) {
        if ( strpos( $content, '```' ) === false ) return $content;
        // Strip opening fence: ```html, ```HTML, ```php, ```css, etc.
        $content = preg_replace( '/^```[a-zA-Z]*\s*$/m', '', $content );
        // Strip closing fence: ``` alone on a line
        $content = preg_replace( '/^```\s*$/m', '', $content );
        return $content;
    }
    add_filter( 'the_content', 'wpaap_strip_content_code_fences', 6 );
}

// ── Fix wpautop wrapping FAQ checkbox in <p> (priority 11, sau wpautop ở 10) ──
// wpautop có thể wrap <input> inline → phá sibling CSS selector ~
if ( ! function_exists( 'wpaap_fix_faq_wpautop_damage' ) ) {
    function wpaap_fix_faq_wpautop_damage( $content ) {
        if ( strpos( $content, 'wpaap-faq-toggle' ) === false ) return $content;
        // Xóa <p>...</p> bọc xung quanh checkbox input trong FAQ item
        $content = preg_replace(
            '/<p>\s*(<input[^>]+class="[^"]*wpaap-faq-toggle[^"]*"[^>]*\/?>\s*)<\/p>/i',
            '$1',
            $content
        );
        return $content;
    }
    add_filter( 'the_content', 'wpaap_fix_faq_wpautop_damage', 11 );
}

// ── Display-time shortcode filter: FAQ + CTA + TOC in already-saved posts ─────
// Priority 9: runs before wpautop (10) so block-level HTML isn't wrapped in <p>

if ( ! function_exists( 'wpaap_content_filter_faq' ) ) {
    function wpaap_content_filter_faq( $content ) {
        if ( strpos( $content, '[FAQ]' ) === false ) return $content;
        $faq_render_cb = function ( $m ) {
            $block = trim( $m[1] );
            preg_match_all( '/Q\s*:\s*(.+?)\s*A\s*:\s*([\s\S]+?)(?=Q\s*:|$)/i', $block, $pairs, PREG_SET_ORDER );
            if ( empty( $pairs ) ) return '';
            static $faq_disp_counter = 0;
            $html  = '<div class="wpaap-faq-section"><h3 class="wpaap-faq-heading">Câu hỏi thường gặp</h3>';
            $first = true;
            foreach ( $pairs as $pair ) {
                $q = trim( $pair[1] );
                $a = trim( $pair[2] );
                $faq_disp_counter++;
                $uid     = 'wpaap-faq-d' . $faq_disp_counter;
                $checked = $first ? ' checked' : '';
                $html .= '<div class="wpaap-faq-item">'
                       . '<input type="checkbox" id="' . esc_attr( $uid ) . '" class="wpaap-faq-toggle"' . $checked . '>'
                       . '<label class="wpaap-faq-q" for="' . esc_attr( $uid ) . '">' . esc_html( $q )
                       . '<span class="wpaap-faq-icon" aria-hidden="true"></span></label>'
                       . '<div class="wpaap-faq-a">' . wp_kses_post( $a ) . '</div>'
                       . '</div>';
                $first = false;
            }
            return $html . '</div>';
        };
        $content = preg_replace_callback( '/\[FAQ\](.*?)\[\/FAQ\]/si', $faq_render_cb, $content );
        if ( strpos( $content, '[FAQ]' ) !== false ) {
            $content = preg_replace_callback(
                '/\[FAQ\]([\s\S]+?)(?=\[CHECKLIST\]|\[CTA\]|\[\/CTA\]|\[INFO_BOX\]|\[EXPERT_BOX\]|$)/i',
                $faq_render_cb,
                $content
            );
        }
        return $content;
    }
    add_filter( 'the_content', 'wpaap_content_filter_faq', 8 );
}

// ── Display-time TOC filter ────────────────────────────────────────────────────
if ( ! function_exists( 'wpaap_content_filter_toc' ) ) {
    function wpaap_content_filter_toc( $content ) {
        $has_toc = stripos( $content, '[TOC]' ) !== false;

        // Inject id attributes into headings that lack them (needed for anchor links)
        if ( $has_toc || stripos( $content, 'wp-toc-box' ) !== false ) {
            $id_ctr = array();
            $content = preg_replace_callback(
                '/<(h[23])([^>]*)>(.*?)<\/(h[23])>/si',
                function ( $m ) use ( &$id_ctr ) {
                    $tag = $m[1]; $attrs = $m[2]; $inner = $m[3];
                    if ( preg_match( '/\bid=["\']/', $attrs ) ) return $m[0];
                    $slug = sanitize_title( wp_strip_all_tags( $inner ) );
                    if ( empty( $slug ) ) $slug = 'heading';
                    if ( ! isset( $id_ctr[ $slug ] ) ) {
                        $id_ctr[ $slug ] = 0; $final = $slug;
                    } else {
                        $id_ctr[ $slug ]++; $final = $slug . '-' . $id_ctr[ $slug ];
                    }
                    return '<' . $tag . ' id="' . esc_attr( $final ) . '"' . $attrs . '>' . $inner . '</' . $tag . '>';
                },
                $content
            );
        }

        if ( ! $has_toc ) return $content;

        $build_from_headings = function ( $src ) {
            preg_match_all( '/<h([23])([^>]*)>(.*?)<\/h[23]>/si', $src, $hm, PREG_SET_ORDER );
            if ( empty( $hm ) ) return '';
            $html   = '<ol class="wp-toc-list">';
            $in_sub = false; $has_any = false;
            foreach ( $hm as $h ) {
                $level = $h[1]; $attrs = $h[2]; $inner = $h[3];
                $text  = wp_strip_all_tags( $inner );
                $id    = '';
                if ( preg_match( '/\bid="([^"]*)"/i', $attrs, $im ) ) $id = $im[1];
                $linked  = $id
                    ? '<a href="#' . esc_attr( $id ) . '">' . esc_html( $text ) . '</a>'
                    : esc_html( $text );
                $has_any = true;
                if ( $level === '3' ) {
                    if ( ! $in_sub ) { $html .= '<ol>'; $in_sub = true; }
                    $html .= '<li>' . $linked . '</li>';
                } else {
                    if ( $in_sub ) { $html .= '</ol></li>'; $in_sub = false; }
                    $html .= '<li>' . $linked;
                }
            }
            if ( $in_sub )      $html .= '</ol></li>';
            elseif ( $has_any ) $html .= '</li>';
            $html .= '</ol>';
            return $has_any ? $html : '';
        };

        $wrap = function ( $list_html ) {
            return '<div class="wp-toc-box">'
                 . '<div class="wp-toc-header"><span>📋 Mục lục</span>'
                 . '<button class="wp-toc-toggle">Thu gọn ▲</button></div>'
                 . $list_html . '</div>';
        };

        // [TOC]items[/TOC] → build from headings (ignore inline items, use anchors)
        $content = preg_replace_callback(
            '/\[TOC\](.*?)\[\/TOC\]/si',
            function ( $m ) use ( $build_from_headings, $wrap, &$content ) {
                $list = $build_from_headings( $content );
                return $list ? $wrap( $list ) : '';
            },
            $content
        );

        // Standalone [TOC]
        if ( stripos( $content, '[TOC]' ) !== false ) {
            $content = preg_replace_callback(
                '/\[TOC\]/i',
                function ( $m ) use ( $build_from_headings, $wrap, &$content ) {
                    $list = $build_from_headings( $content );
                    return $list ? $wrap( $list ) : '';
                },
                $content
            );
        }

        return $content;
    }
    add_filter( 'the_content', 'wpaap_content_filter_toc', 9 );
}

// ── Frontend CSS for AI-generated article components ────────────────────────
// Priority 5 (trước wp_print_styles ở priority 10) → theme CSS load sau → theme có thể override
add_action( 'wp_head', function () {
    if ( ! is_singular( 'post' ) ) return;
    ?>
<style id="wpaap-article-styles">
/* TOC box */
.wp-toc-box{background:#f8f9fa;border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px;margin:24px 0;font-size:15px}
.wp-toc-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;font-weight:600;color:#1a202c}
.wp-toc-toggle{background:none;border:none;cursor:pointer;font-size:13px;color:#6b7280;padding:0}
.wp-toc-list{margin:0;padding-left:20px;color:#374151}
.wp-toc-list li{margin:4px 0}
.wp-toc-list ol{padding-left:18px;margin:4px 0}
.wp-toc-list a{color:#4f46e5;text-decoration:none}
.wp-toc-list a:hover{text-decoration:underline}
/* Featured snippet */
.featured-snippet{background:#eff6ff;border-left:4px solid #3b82f6;padding:14px 18px;border-radius:0 8px 8px 0;margin:20px 0;font-size:15px;color:#1e3a5f;line-height:1.7}
/* Info/Tip/Warning/Expert boxes */
.wpaap-box{padding:14px 18px;border-radius:8px;margin:20px 0;border-left:4px solid;font-size:15px;line-height:1.7}
.wpaap-box-title{font-weight:700;margin-bottom:6px;display:flex;align-items:center;gap:6px}
.wpaap-info-box{background:#eff6ff;border-color:#3b82f6;color:#1e3a5f}
.wpaap-tip-box{background:#fffbeb;border-color:#f59e0b;color:#78350f}
.wpaap-warning-box{background:#fef2f2;border-color:#ef4444;color:#7f1d1d}
.wpaap-expert-box{background:#f5f3ff;border-color:#8b5cf6;color:#3b0764}
/* Checklist */
.wpaap-checklist-title{font-weight:700;font-size:16px;margin:20px 0 10px}
ul.wpaap-checklist{list-style:none;padding:0;margin:0 0 20px}
ul.wpaap-checklist li{display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #f1f5f9;font-size:15px;color:#374151}
ul.wpaap-checklist li:last-child{border-bottom:none}
ul.wpaap-checklist li::before{content:"✓";color:#22c55e;font-weight:700;flex-shrink:0;margin-top:1px}
/* FAQ — checkbox CSS accordion, không JS, không xung đột theme */
.wpaap-faq-section{margin:24px 0}
.wpaap-faq-heading{font-weight:700;font-size:18px;margin-bottom:14px;color:#1a202c;padding-bottom:8px;border-bottom:2px solid #3b82f6}
.wpaap-faq-item{border:1px solid #e2e8f0;border-radius:8px;margin-bottom:8px;overflow:hidden}
.wpaap-faq-toggle{display:none!important}
label.wpaap-faq-q{background:#f1f5f9;padding:12px 16px;font-weight:700;font-size:14px;color:#1e293b;display:flex!important;align-items:center;justify-content:space-between;gap:8px;cursor:pointer;user-select:none;transition:background .15s;margin:0!important;border-bottom:none}
label.wpaap-faq-q:hover{background:#e8eef5}
.wpaap-faq-toggle:checked~label.wpaap-faq-q{border-bottom:1px solid #e2e8f0;background:#e8eef5}
.wpaap-faq-icon{flex-shrink:0;font-size:11px;color:#3b82f6;line-height:1}
.wpaap-faq-icon::before{content:'▼'}
.wpaap-faq-toggle:checked~label.wpaap-faq-q .wpaap-faq-icon::before{content:'▲'}
.wpaap-faq-a{display:none;padding:12px 16px;font-size:14px;line-height:1.7;color:#334155;background:#fff}
.wpaap-faq-toggle:checked~.wpaap-faq-a{display:block}
.wpaap-faq-a p{margin:0 0 6px}.wpaap-faq-a p:last-child{margin-bottom:0}
/* CTA */
.wpaap-cta{background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;padding:24px 28px;border-radius:12px;margin:28px 0;text-align:center}
.wpaap-cta-title{font-size:20px;font-weight:700;margin-bottom:8px}
.wpaap-cta p{margin:0 0 16px;opacity:.9;font-size:15px}
.wpaap-cta-btn{display:inline-block;background:#fff;color:#4f46e5;font-weight:700;padding:10px 24px;border-radius:8px;text-decoration:none;font-size:15px}
.wpaap-cta-btn:hover{background:#f0f0ff;text-decoration:none;color:#3730a3}
</style>
<script>
document.addEventListener('DOMContentLoaded',function(){
  /* TOC toggle */
  document.querySelectorAll('.wp-toc-toggle').forEach(function(btn){
    btn.addEventListener('click',function(){
      var list=this.closest('.wp-toc-box').querySelector('.wp-toc-list');
      if(list){list.style.display=list.style.display==='none'?'':'none';this.textContent=list.style.display===''?'Thu gọn ▲':'Mở rộng ▼';}
    });
  });
  /* FAQ: chặn theme JS can thiệp label click — checkbox state vẫn toggle bình thường */
  document.querySelectorAll('label.wpaap-faq-q').forEach(function(lbl){
    lbl.addEventListener('click',function(e){e.stopImmediatePropagation();e.stopPropagation();});
  });
});
</script>
    <?php
}, 5 );
