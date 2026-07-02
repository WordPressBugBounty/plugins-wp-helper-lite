<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Shared social-links partial for maintenance templates.
 *
 * Variables (available via require() scope):
 *   $whp_maintenance_phone, $whp_maintenance_email,
 *   $whp_maintenance_youtube, $whp_maintenance_zalo, $whp_maintenance_tiktok
 *
 * Optional theme hint set before including this file:
 *   $_social_theme = 'dark' (default) | 'light'
 */

$_t   = ( isset( $_social_theme ) && $_social_theme === 'light' ) ? 'light' : 'dark';
$_bg  = $_t === 'light' ? 'rgba(0,0,0,0.05)'      : 'rgba(255,255,255,0.07)';
$_bdr = $_t === 'light' ? 'rgba(0,0,0,0.12)'      : 'rgba(255,255,255,0.14)';
$_clr = $_t === 'light' ? '#475569'               : 'rgba(255,255,255,0.72)';

$_has = ! empty( $whp_maintenance_phone )
     || ! empty( $whp_maintenance_email )
     || ! empty( $whp_maintenance_facebook )
     || ! empty( $whp_maintenance_youtube )
     || ! empty( $whp_maintenance_zalo )
     || ! empty( $whp_maintenance_tiktok );

if ( ! $_has ) return;

/* Zalo href: if user typed a plain number, wrap in zalo.me */
function _maint_social_zalo_href( $val ) {
    if ( strpos( $val, 'http' ) === 0 ) return $val;
    return 'https://zalo.me/' . preg_replace( '/\D/', '', $val );
}

$_s = 'display:inline-flex;align-items:center;gap:7px;padding:8px 16px;'
    . 'background:' . $_bg . ';border:1px solid ' . $_bdr . ';border-radius:24px;'
    . 'text-decoration:none;color:' . $_clr . ';font-size:13px;font-weight:600;'
    . 'white-space:nowrap;transition:opacity .2s;';
?>
<div style="margin-top:22px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">

<?php if ( ! empty( $whp_maintenance_phone ) ) : ?>
<a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $whp_maintenance_phone ) ); ?>"
   style="<?php echo $_s; ?>">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0">
        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07
                 A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67
                 A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72
                 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91
                 a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45
                 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
    </svg>
    <?php echo esc_html( $whp_maintenance_phone ); ?>
</a>
<?php endif; ?>

<?php if ( ! empty( $whp_maintenance_email ) ) : ?>
<a href="mailto:<?php echo esc_attr( $whp_maintenance_email ); ?>"
   style="<?php echo $_s; ?>">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
        <polyline points="22,6 12,13 2,6"/>
    </svg>
    <?php echo esc_html( $whp_maintenance_email ); ?>
</a>
<?php endif; ?>

<?php if ( ! empty( $whp_maintenance_youtube ) ) : ?>
<a href="<?php echo esc_url( $whp_maintenance_youtube ); ?>"
   target="_blank" rel="noopener noreferrer"
   style="<?php echo $_s; ?>">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0">
        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12
                 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0
                 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505
                 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93
                 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
    </svg>
    YouTube
</a>
<?php endif; ?>

<?php if ( ! empty( $whp_maintenance_zalo ) ) : ?>
<a href="<?php echo esc_url( _maint_social_zalo_href( $whp_maintenance_zalo ) ); ?>"
   target="_blank" rel="noopener noreferrer"
   style="<?php echo $_s; ?>">
    <svg width="16" height="16" viewBox="0 0 40 40" style="flex-shrink:0">
        <circle cx="20" cy="20" r="20" fill="#0068FF"/>
        <text x="20" y="26" text-anchor="middle" fill="#fff"
              font-size="18" font-weight="bold" font-family="Arial, sans-serif">Z</text>
    </svg>
    Zalo
</a>
<?php endif; ?>

<?php if ( ! empty( $whp_maintenance_facebook ) ) : ?>
<a href="<?php echo esc_url( $whp_maintenance_facebook ); ?>"
   target="_blank" rel="noopener noreferrer"
   style="<?php echo $_s; ?>">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0">
        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125
                 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669
                 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328
                 l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
    </svg>
    Facebook
</a>
<?php endif; ?>

<?php if ( ! empty( $whp_maintenance_tiktok ) ) : ?>
<a href="<?php echo esc_url( $whp_maintenance_tiktok ); ?>"
   target="_blank" rel="noopener noreferrer"
   style="<?php echo $_s; ?>">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" style="flex-shrink:0">
        <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67
                 a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89
                 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01
                 a6.27 6.27 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34
                 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34
                 V8.82a8.18 8.18 0 0 0 4.78 1.53V6.9a4.85 4.85 0 0 1-1-.21z"/>
    </svg>
    TikTok
</a>
<?php endif; ?>

</div>
