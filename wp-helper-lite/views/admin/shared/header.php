<?php
$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
$whp_list_tab = whp_get_list_tab();
?>
<div class="whp-wrap">
    <div class="whp-header">
        <h1 class="whp-title">
            <?php echo whp_show_html(__('WP Helper <span class = "title-badge">Premium</span>', 'whp')) ?>
        </h1>

    </div>
    <div class="whp-body">
        <div class="whp-tab-title">
            <ul>
                <?php
                foreach ($whp_list_tab as $item) {
                    $title = $item['title'] ?? "";
                    $slug = $item['slug'] ?? "";
                    $link = menu_page_url($slug, false);
                    $active = $page == $slug ? "active" : "";
                ?>
                    <li class="tab-title-item <?php echo esc_attr($active); ?> whp-setting-title">
                        <a href="<?php echo esc_url($link); ?>">
                            <?php echo esc_html($title) ?>
                        </a>
                    </li>
                <?php }
                ?>
            </ul>
        </div>
        <div class="whp-content">