<?php ?>

<div id="whp-popup" class="whp-hidden">
    <div class="whp-popup-background"></div>
    <div class="center modal-box ">
        <div class="icon-close">
            <div class="fas fa-times"></div>
        </div>
        <div class="icon-letter-1">
            <div class="fas fa-envelope icon1"></div>
        </div>

        <header><?= $whp_popup_title ?? 'Mắt Bão Pop-up' ?></header>
        <p>
            <?= $whp_popup_sub_title ??  'Mắt Bão Pop-up ' ?>
        </p>
        <form method="POST">
            <div class="icon-letter-2">
                <div class="fas fa-envelope icon2"></div>
            </div>

            <input type="email" name="email" required placeholder="matbao.net">
            <button id="whp-button-popup"> <?= $whp_popup_button ??  'Gửi' ?></button>
        </form>
        <div class="icons">
            <?php
            if ($whp_popup_facebook) {
            ?>
                <a href="<?= $whp_popup_facebook ?>"> <i class="fab fa-facebook-f"></i></a>
            <?php
            }
            ?>
            <?php

            if ($whp_popup_tiktok) { ?>
                <a href="<?= $whp_popup_tiktok ?>"> <i class="fab fa-tiktok"></i></a>
            <?php  }
            ?>

            <?php
            if ($whp_popup_instagram) { ?>
                <a href="<?= $whp_popup_instagram ?>"> <i class="fab fa-instagram"></i></a>
            <?php  }

            ?>
            <?php
            if ($whp_popup_youtube) { ?>
                <a href="<?= $whp_popup_youtube ?>"> <i class="fab fa-youtube"></i></a>
            <?php    }
            ?>


        </div>
    </div>
</div>