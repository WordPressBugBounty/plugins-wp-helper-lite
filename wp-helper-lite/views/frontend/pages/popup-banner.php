<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<div id="whp-popup" class="whp-hidden">
    <div class="whp-popup-background"></div>
    <div class="center modal-box " style="padding: 0px;">
        <i class="fa fa-times whp-popup-close" aria-hidden="true"></i>
        <a href="<?= esc_url($whp_popup_link_redirect ?? '#') ?>"><img src="<?= esc_url($whp_popup_image_banner ?? '') ?>" alt="" style="width:100%;display:block;"></a>

    </div>
</div>