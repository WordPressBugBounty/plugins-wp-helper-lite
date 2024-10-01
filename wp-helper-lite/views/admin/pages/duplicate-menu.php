<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div>
    <h2><?php _e('Duplicate Menu'); ?></h2>

    <?php if (!empty($_POST) && wp_verify_nonce($_POST['duplicate_menu_nonce'], 'duplicate_menu')) : ?>
        <?php
        $source         = intval($_POST['source']);
        $destination    = sanitize_text_field($_POST['new_menu_name']);

        // go ahead and duplicate our menu

        $new_menu_id = $this->whp_extention_duplicate_menu_action($source, $destination);
        ?>

        <div id="message" class="updated">
            <p>
                <?php if ($new_menu_id) : ?>
                    <?php _e('Menu đã nhân bản') ?>. <a href="nav-menus.php?action=edit&amp;menu=<?php echo absint($new_menu_id); ?>"><?php _e('View') ?></a>
                <?php else : ?>
                    <?php _e('There was a problem duplicating your menu. No action was taken.') ?>.
                <?php endif; ?>
            </p>
        </div>

    <?php endif; ?>


    <?php if (empty($nav_menus)) : ?>
        <p><?php _e("You haven't created any Menus yet."); ?></p>
    <?php else : ?>
        <form method="post" action="">
            <?php wp_nonce_field('_token', '_token'); ?>
            <?php wp_nonce_field('duplicate_menu', 'duplicate_menu_nonce'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="source"><?php _e('Chọn menu'); ?>:</label>
                    </th>
                    <td>
                        <select name="source">
                            <?php foreach ((array) $nav_menus as $_nav_menu) : ?>
                                <option value="<?php echo esc_attr($_nav_menu->term_id) ?>">
                                    <?php echo esc_html($_nav_menu->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span style="display:inline-block; padding:0 10px;"><?php _e('Tên menu', 'ad'); ?></span>
                        <input name="new_menu_name" type="text" id="new_menu_name" value="" class="regular-text" />
                    </td>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary" value="Nhân bản" />
            </p>
        </form>
    <?php endif; ?>
</div>