/**
 * Đăng ký 4 gateway Ví điện tử (MoMo, ZaloPay, VNPay, ShopeePay) với
 * WooCommerce Checkout Block. Vanilla JS — plugin không có build step.
 */
( function( wp, wc ) {
    var el = wp.element.createElement;
    var registerPaymentMethod = wc.wcBlocksRegistry.registerPaymentMethod;
    var getSetting = wc.wcSettings.getSetting;
    var decodeEntities = wp.htmlEntities.decodeEntities;

    var WALLETS = [
        { id: 'MB_WHP_Wallet_MoMo', fallbackLabel: 'MoMo' },
        { id: 'MB_WHP_Wallet_ZaloPay', fallbackLabel: 'ZaloPay' },
        { id: 'MB_WHP_Wallet_VNPAY', fallbackLabel: 'VNPay' },
        { id: 'MB_WHP_Wallet_ShopeePay', fallbackLabel: 'ShopeePay' }
    ];

    WALLETS.forEach( function( wallet ) {
        var settings = getSetting( wallet.id + '_data', {} );
        if ( ! settings || ! settings.title ) {
            return;
        }
        var label = decodeEntities( settings.title || wallet.fallbackLabel );

        var Label = function() {
            return el(
                'span',
                { className: 'whp-wallet-block-label' },
                settings.icon
                    ? el( 'img', { src: settings.icon, alt: label, className: 'whp-wallet-block-icon' } )
                    : null,
                el( 'span', null, label )
            );
        };
        var Content = function() {
            return el( 'div', { className: 'whp-wallet-block-desc' }, decodeEntities( settings.description || '' ) );
        };

        registerPaymentMethod( {
            name: wallet.id,
            label: el( Label ),
            content: el( Content ),
            edit: el( Content ),
            canMakePayment: function() { return true; },
            ariaLabel: label,
            supports: {
                features: settings.supports || [ 'products' ]
            }
        } );
    } );
} )( window.wp, window.wc );
